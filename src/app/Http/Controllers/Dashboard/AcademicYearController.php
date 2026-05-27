<?php

namespace App\Http\Controllers\Dashboard;

use App\Exports\AcademicYearExport;
use App\Http\Controllers\ResourceController;
use App\Http\Requests\Dashboard\StoreAcademicYearRequest;
use App\Http\Requests\Dashboard\UpdateAcademicYearRequest;
use App\Models\AcademicYear;
use App\Services\ConfigurationSyncService;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AcademicYearController extends ResourceController
{
    public function __construct(
        private ExportService $exportService
    ) {}

    protected static string $permissionResource = 'academic_years';

    public function index(Request $request)
    {
        $this->authorize('viewAny', AcademicYear::class);

        if ($request->has('export')) {
            $export = new AcademicYearExport([], $request->all());

            return $this->exportService->download($export->headings(), $export->rows(), 'academic_years.xlsx');
        }

        if ($request->ajax()) {
            $years = AcademicYear::latest();

            return DataTables::of($years)
                ->filter(function ($query) {
                    $search = request('search.value', '');
                    if (empty($search)) {
                        return;
                    }
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
                }, true)
                ->addColumn('checkbox', function ($year) {
                    return '<input type="checkbox" class="select-row" value="'.$year->id.'">';
                })
                ->addColumn('name', function ($year) {
                    return e($year->name);
                })
                ->addColumn('start_date', function ($year) {
                    return $year->start_date->format('d M Y');
                })
                ->addColumn('end_date', function ($year) {
                    return $year->end_date->format('d M Y');
                })
                ->addColumn('is_active', function ($year) {
                    return $year->is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Tidak Aktif</span>';
                })
                ->addColumn('action', function ($year) {
                    $editBtn = '<a href="'.route('dashboard.academic-years.edit', $year).'" class="avatar-text avatar-md"><i class="feather feather-edit-3"></i></a>';
                    $deleteBtn = '<a href="javascript:void(0)" class="avatar-text avatar-md text-danger delete-btn" data-url="'.route('dashboard.academic-years.destroy', $year).'"><i class="feather feather-trash-2"></i></a>';

                    return '<div class="gap-2 hstack justify-content-end">'.$editBtn.$deleteBtn.'</div>';
                })
                ->rawColumns(['checkbox', 'is_active', 'action'])
                ->make(true);
        }

        $totalCount = AcademicYear::count();
        $activeCount = AcademicYear::where('is_active', true)->count();
        $inactiveCount = AcademicYear::where('is_active', false)->count();

        return view('dashboard.academic-years.index', compact('totalCount', 'activeCount', 'inactiveCount'));
    }

    public function create()
    {
        $this->authorize('create', AcademicYear::class);

        return view('dashboard.academic-years.create');
    }

    public function store(StoreAcademicYearRequest $request)
    {
        $this->authorize('create', AcademicYear::class);

        $data = $request->validated();

        if ($data['is_active'] ?? false) {
            AcademicYear::where('is_active', true)->update(['is_active' => false]);
        }

        $academicYear = AcademicYear::create($data);

        // Trigger sync untuk memastikan semua data terkait terupdate
        app(ConfigurationSyncService::class)->syncAcademicYearConfig($academicYear);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Tahun Akademik berhasil ditambahkan dan tersinkron.',
                'data' => $academicYear,
            ], 201);
        }

        return redirect()->route('dashboard.academic-years.index')->with('success', 'Tahun Akademik berhasil ditambahkan dan tersinkron.');
    }

    public function edit(AcademicYear $academicYearRecord)
    {
        $this->authorize('update', $academicYearRecord);

        return view('dashboard.academic-years.edit', ['academicYear' => $academicYearRecord]);
    }

    public function update(UpdateAcademicYearRequest $request, AcademicYear $academicYearRecord)
    {
        $this->authorize('update', $academicYearRecord);

        $data = $request->validated();

        if (($data['is_active'] ?? false) && ! $academicYearRecord->is_active) {
            AcademicYear::where('is_active', true)->update(['is_active' => false]);
        }

        $academicYearRecord->update($data);

        // Trigger sync untuk memastikan semua data terkait terupdate
        app(ConfigurationSyncService::class)->syncAcademicYearConfig($academicYearRecord);

        return redirect()->route('dashboard.academic-years.index')->with('success', 'Tahun Akademik berhasil diperbarui dan tersinkron.');
    }

    public function destroy(AcademicYear $academicYearRecord)
    {
        $this->authorize('delete', $academicYearRecord);

        $academicYearRecord->delete();

        return $this->success(null, 'Tahun Akademik berhasil dihapus.');
    }
}
