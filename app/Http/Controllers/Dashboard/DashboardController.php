<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\ResourceController;
use App\Models\AcademicYear;
use App\Models\AuditLog;
use App\Models\Classroom;
use App\Models\Payment;
use App\Models\Student;
use App\Models\WhatsAppMessage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends ResourceController
{
    protected static string $permissionResource = 'dashboard';

    public function index(Request $request)
    {
        $data = $this->getDashboardData($request);

        return view('dashboard.index', $data);
    }

    public function getStats(Request $request)
    {
        $data = $this->getDashboardData($request);

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $data['stats'],
                'charts' => $data['charts'],
            ],
        ]);
    }

    private function getDashboardData(?Request $request = null): array
    {
        $request = $request ?? request();
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('superadmin');
        $isFinance = $user->hasRole('finance');

        $filterStartDate = $request->input('date_from');
        $filterEndDate = $request->input('date_to');

        $stats = $this->getBaseStats($user, $isSuperAdmin, $isFinance);

        $charts = $this->getFinanceChart($isSuperAdmin, $isFinance, $filterStartDate, $filterEndDate);

        $stats['recent_payments'] = $this->getRecentPayments($isSuperAdmin, $isFinance);

        $stats['academic_years'] = AcademicYear::all();
        $stats['recent_students'] = Student::with('classrooms:id,name')->latest()->limit(10)->get();

        $this->getRecentActivityData($stats, $filterStartDate, $filterEndDate);

        return compact(
            'user', 'isSuperAdmin', 'isFinance', 'stats', 'charts'
        );
    }

    private function getBaseStats($user, bool $isSuperAdmin, bool $isFinance): array
    {
        $stats = [
            'total_students' => Cache::remember('dashboard:total_students', 300, fn () => Student::where('status', 'active')->count()),
            'total_classrooms' => Cache::remember('dashboard:total_classrooms', 300, fn () => Classroom::count()),
        ];

        $stats['outstanding_payments'] = ($isSuperAdmin || $isFinance)
            ? Cache::remember('dashboard:outstanding_payments', 300, fn () => Payment::where('status', 'pending')->sum('gross_amount'))
            : 0;
        $stats['total_payments_month'] = ($isSuperAdmin || $isFinance)
            ? Cache::remember('dashboard:payments_month:'.now()->format('Y-m'), 300, fn () => Payment::whereIn('status', ['paid', 'completed'])
                ->whereMonth('created_at', now()->month)->sum('gross_amount'))
            : 0;
        $stats['pesan_wa_belum_dibaca'] = ($isSuperAdmin || $isFinance)
            ? WhatsAppMessage::whereNull('read_at')->count() : 0;

        return $stats;
    }

    private function getFinanceChart(bool $isSuperAdmin, bool $isFinance, ?string $filterStartDate, ?string $filterEndDate): array
    {
        $charts = [
            'finance' => [
                'labels' => collect(),
                'income' => collect(),
                'unpaid' => collect(),
                'failed' => collect(),
            ],
        ];

        if (! $isSuperAdmin && ! $isFinance) {
            return $charts;
        }

        if ($filterStartDate && $filterEndDate) {
            $start = Carbon::parse($filterStartDate)->startOfMonth();
            $end = Carbon::parse($filterEndDate)->startOfMonth();
            $finMonths = collect();
            for ($m = $start->copy(); $m->lte($end); $m->addMonth()) {
                $finMonths->push($m->copy());
            }
            if ($finMonths->isEmpty()) {
                $finMonths->push($start->copy());
            }
            $finFrom = $filterStartDate;
            $finTo = $filterEndDate;
        } else {
            $finMonths = collect(range(5, 0))->map(fn ($m) => now()->subMonths($m));
            $finFrom = now()->subMonths(5)->startOfMonth()->format('Y-m-d');
            $finTo = now()->endOfMonth()->format('Y-m-d');
        }

        $dateFormat = DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        $monthlyPayments = Payment::whereBetween('created_at', [$finFrom, $finTo])
            ->selectRaw("
                {$dateFormat} as month,
                SUM(CASE WHEN status IN ('paid', 'completed') THEN gross_amount ELSE 0 END) as lunas,
                SUM(CASE WHEN status='pending' THEN gross_amount ELSE 0 END) as belum,
                SUM(CASE WHEN status='failed' THEN gross_amount ELSE 0 END) as terlambat
            ")
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $charts['finance']['labels'] = $finMonths->map(fn ($m) => $m->format('M Y'))->values();
        $charts['finance']['income'] = $finMonths->map(fn ($m) => (float) ($monthlyPayments[$m->format('Y-m')]->lunas ?? 0))->values();
        $charts['finance']['unpaid'] = $finMonths->map(fn ($m) => (float) ($monthlyPayments[$m->format('Y-m')]->belum ?? 0))->values();
        $charts['finance']['failed'] = $finMonths->map(fn ($m) => (float) ($monthlyPayments[$m->format('Y-m')]->terlambat ?? 0))->values();

        $stats['finance_summary'] = [
            'total' => $monthlyPayments->sum(fn ($m) => ($m->lunas ?? 0) + ($m->belum ?? 0) + ($m->terlambat ?? 0)),
            'lunas' => $monthlyPayments->sum('lunas'),
            'outstanding' => $monthlyPayments->sum('belum'),
            'terlambat' => $monthlyPayments->sum('terlambat'),
        ];

        return $charts;
    }

    private function getRecentActivityData(array &$stats, ?string $filterStartDate, ?string $filterEndDate): void
    {
        $recentActivities = AuditLog::with('user:id,name');
        if ($filterStartDate && $filterEndDate) {
            $recentActivities->whereBetween('created_at', [$filterStartDate.' 00:00:00', $filterEndDate.' 23:59:59']);
        }
        $stats['recent_activities'] = $recentActivities->latest()->limit(10)->get();
    }

    private function getRecentPayments(bool $isSuperAdmin, bool $isFinance)
    {
        return ($isSuperAdmin || $isFinance)
            ? Payment::with(['student:id,name', 'paymentTitle:id,name'])->latest()->limit(8)->get()
            : collect();
    }
}
