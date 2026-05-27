<?php

namespace App\Http\Controllers\Dashboard;

use App\Exports\PaymentExport;
use App\Http\Controllers\ResourceController;
use App\Http\Requests\Dashboard\PaymentRequest;
use App\Models\Classroom;
use App\Models\Payment;
use App\Models\PaymentTitle;
use App\Models\Student;
use App\Services\ExportService;
use App\Services\PaymentService;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class PaymentController extends ResourceController
{
    protected static string $permissionResource = 'payments';

    protected $paymentService;

    public function __construct(
        PaymentService $paymentService,
        private ExportService $exportService
    ) {
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        $this->authorize('viewAny', Payment::class);

        if (request()->has('export')) {
            $export = new PaymentExport([], request()->all());

            return $this->exportService->download($export->headings(), $export->rows(), 'payments.xlsx');
        }

        if (request()->ajax()) {
            $query = Payment::with(['student', 'classroom', 'paymentTitle']);

            // Filters
            if (request('status')) {
                $query->where('status', request('status'));
            }
            if (request('classroom_id')) {
                $classroomIds = request('classroom_id');
                if (! is_array($classroomIds)) {
                    $classroomIds = [$classroomIds];
                }
                $query->whereIn('classroom_id', $classroomIds);
            }
            if (request('payment_title_id')) {
                $query->where('payment_title_id', request('payment_title_id'));
            }
            if (request('month')) {
                $query->whereMonth('created_at', request('month'));
            }
            if (request('year')) {
                $query->whereYear('created_at', request('year'));
            }
            if (request('search_student')) {
                $query->whereHas('student', function ($q) {
                    $q->where('name', 'like', '%'.request('search_student').'%');
                });
            }

            return DataTables::of($query)
                ->filter(function ($query) {
                    $search = request('search.value', '');
                    if (empty($search)) {
                        return;
                    }
                    $query->where(function ($q) use ($search) {
                        $q->where('order_id', 'like', "%{$search}%")
                            ->orWhere('gross_amount', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%")
                            ->orWhereHas('student', fn ($r) => $r->where('name', 'like', "%{$search}%"))
                            ->orWhereHas('classroom', fn ($r) => $r->where('name', 'like', "%{$search}%"))
                            ->orWhereHas('paymentTitle', fn ($r) => $r->where('name', 'like', "%{$search}%"));
                    });
                }, true)
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="form-check-input checkbox" value="'.$row->id.'">';
                })
                ->addColumn('student_name', function ($row) {
                    return $row->student->name ?? '-';
                })
                ->addColumn('payment_title', function ($row) {
                    return $row->paymentTitle->name ?? '-';
                })
                ->addColumn('classroom', function ($row) {
                    return $row->classroom->name ?? '-';
                })
                ->addColumn('amount', function ($row) {
                    return 'Rp '.number_format($row->gross_amount, 0, ',', '.');
                })
                ->addColumn('status', function ($row) {
                    $badges = [
                        'paid' => 'success',
                        'completed' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        'cancelled' => 'danger',
                        'expired' => 'secondary',
                    ];
                    $badge = $badges[$row->status] ?? 'info';

                    return '<span class="badge bg-'.$badge.'-subtle text-'.$badge.'">'.ucfirst($row->status).'</span>';
                })
                ->addColumn('created_at', function ($row) {
                    return $row->created_at->format('d M Y') ?? '-';
                })
                ->addColumn('action', function ($row) {
                    $viewBtn = '<a href="'.route('dashboard.payments.show', $row->id).'" class="avatar-text avatar-md">
                                    <i class="feather feather-eye"></i>
                                </a>';

                    $dropdown = '
                        <div class="dropdown">
                            <a href="javascript:void(0);" class="avatar-text avatar-md" data-bs-toggle="dropdown" data-bs-offset="25, 25">
                                <i class="feather feather-more-horizontal"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="'.route('dashboard.payments.edit', $row->id).'" class="dropdown-item">
                                    <i class="feather feather-edit-3"></i>
                                    <span>Edit</span>
                                </a>
                                <a href="javascript:void(0);" class="dropdown-item delete-btn" data-id="'.$row->id.'" data-url="'.route('dashboard.payments.destroy', $row->id).'">
                                    <i class="feather feather-trash-2"></i>
                                    <span>Hapus</span>
                                </a>
                            </div>
                        </div>';

                    return '<div class="gap-2 hstack justify-content-end">'.$viewBtn.$dropdown.'</div>';
                })
                ->rawColumns(['checkbox', 'status', 'action'])
                ->make(true);
        }

        $classrooms = Classroom::select(['id', 'name'])->get();
        $paymentTitles = PaymentTitle::select(['id', 'name'])->get();

        // Summary stats — single query instead of 4
        $statsRow = Payment::selectRaw("
            SUM(gross_amount) as total,
            SUM(CASE WHEN status IN ('paid', 'completed') THEN gross_amount ELSE 0 END) as paid,
            SUM(CASE WHEN status = 'pending' THEN gross_amount ELSE 0 END) as pending,
            SUM(CASE WHEN status IN ('failed', 'cancelled', 'expired') THEN gross_amount ELSE 0 END) as failed
        ")->first();
        $stats = [
            'total' => (float) ($statsRow->total ?? 0),
            'paid' => (float) ($statsRow->paid ?? 0),
            'pending' => (float) ($statsRow->pending ?? 0),
            'failed' => (float) ($statsRow->failed ?? 0),
        ];

        return view('dashboard.payments.index', compact('classrooms', 'paymentTitles', 'stats'));
    }

    public function show(Payment $paymentRecord)
    {
        $this->authorize('view', $paymentRecord);

        $paymentRecord->load(['student', 'classroom', 'paymentTitle']);

        return view('dashboard.payments.show', ['payment' => $paymentRecord]);
    }

    public function create()
    {
        $this->authorize('create', Payment::class);

        $students = Student::select(['id', 'name', 'nisn'])->get();
        $classrooms = Classroom::select(['id', 'name'])->get();
        $paymentTitles = PaymentTitle::select(['id', 'name'])->get();

        return view('dashboard.payments.create', compact('students', 'classrooms', 'paymentTitles'));
    }

    public function store(PaymentRequest $request)
    {
        $this->authorize('create', Payment::class);

        try {
            $data = $request->validated();
            if (empty($data['payment_title_id']) && ! empty($data['payment_title_ids'][0])) {
                $data['payment_title_id'] = $data['payment_title_ids'][0];
            }

            unset($data['payment_title_ids']);

            if (empty($data['classroom_type']) && ! empty($data['classroom_id'])) {
                $data['classroom_type'] = Classroom::find($data['classroom_id'])?->classroom_type ?? 'regular';
            }

            $data['order_id'] = 'INV-'.time().'-'.Str::upper(Str::random(4));
            $payment = $this->paymentService->create($data);

            return redirect()->route('dashboard.payments.show', $payment->id)
                ->with('success', 'Tagihan pembayaran berhasil dibuat.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal membuat tagihan: '.$e->getMessage());
        }
    }

    public function edit(Payment $paymentRecord)
    {
        $this->authorize('update', $paymentRecord);

        $students = Student::select(['id', 'name', 'nisn'])->get();
        $classrooms = Classroom::select(['id', 'name'])->get();
        $paymentTitles = PaymentTitle::select(['id', 'name'])->get();

        return view('dashboard.payments.edit', ['payment' => $paymentRecord, 'students' => $students, 'classrooms' => $classrooms, 'paymentTitles' => $paymentTitles]);
    }

    public function update(PaymentRequest $request, Payment $paymentRecord)
    {
        $this->authorize('update', $paymentRecord);

        try {
            $data = $request->validated();

            if (empty($data['classroom_type']) && ! empty($data['classroom_id'])) {
                $data['classroom_type'] = Classroom::find($data['classroom_id'])?->classroom_type ?? 'regular';
            }

            $paymentRecord->update($data);

            return redirect()->route('dashboard.payments.show', $paymentRecord->id)
                ->with('success', 'Data pembayaran berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data: '.$e->getMessage());
        }
    }

    public function destroy(Payment $paymentRecord)
    {
        $this->authorize('delete', $paymentRecord);

        if ($paymentRecord->delete()) {
            return $this->success(null, 'Data pembayaran berhasil dihapus.');
        }

        return $this->error('Gagal menghapus data.', null, 500);
    }

    public function getOutstanding(Student $studentRecord)
    {
        $outstanding = $this->paymentService->getOutstanding($studentRecord);

        return response()->json($outstanding);
    }

    public function markPaid(Payment $paymentRecord)
    {
        try {
            $paymentRecord->update([
                'status' => 'completed',
                'payment_type' => 'manual',
            ]);

            return redirect()->back()->with('success', 'Pembayaran berhasil ditandai sebagai lunas.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui status: '.$e->getMessage());
        }
    }
}
