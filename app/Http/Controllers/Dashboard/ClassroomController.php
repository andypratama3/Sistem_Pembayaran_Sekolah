<?php

namespace App\Http\Controllers\Dashboard;

use App\Exports\ClassroomExport;
use App\Http\Controllers\ResourceController;
use App\Http\Requests\Dashboard\StoreClassroomRequest;
use App\Http\Requests\Dashboard\UpdateClassroomRequest;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Services\ClassroomService;
use App\Services\ExportService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ClassroomController extends ResourceController
{
    use ApiResponse;

    protected static string $permissionResource = 'classrooms';

    public function __construct(
        private ClassroomService $classroomService,
        private ExportService $exportService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Classroom::class);

        if ($request->has('export')) {
            $export = new ClassroomExport([], $request->all());

            return $this->exportService->download($export->headings(), $export->rows(), 'classrooms.xlsx');
        }

        if ($request->ajax() || $request->expectsJson()) {
            $classrooms = $this->classroomService->getFilteredQuery(
                classroomType: $request->filled('classroom_type') ? $request->classroom_type : null,
                teacherId: $request->filled('teacher_id') ? $request->teacher_id : null,
                subjectId: $request->filled('subject_id') ? $request->subject_id : null,
            );

            return DataTables::of($classrooms)
                ->filter(function ($query) {
                    $search = request('search.value', '');
                    if (empty($search)) {
                        return;
                    }
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%")
                            ->orWhere('classroom_type', 'like', "%{$search}%")
                            ->orWhereHas('teachers', fn ($r) => $r->where('name', 'like', "%{$search}%"))
                            ->orWhereHas('academicYear', fn ($r) => $r->where('name', 'like', "%{$search}%"));
                    });
                }, true)
                ->addColumn('checkbox', function ($item) {
                    return '<input type="checkbox" class="form-check-input checkbox" value="'.$item->id.'">';
                })
                ->addColumn('students_count', function ($item) {
                    return $item->students_count;
                })
                ->addColumn('teacher_name', function ($item) {
                    return $item->teachers->pluck('name')->implode(', ') ?: '-';
                })
                ->addColumn('academic_year', function ($item) {
                    return $item->academicYear ? $item->academicYear->name : '-';
                })
                ->addColumn('action', function ($item) {
                    $viewBtn = '<a href="'.route('dashboard.classrooms.show', $item->id).'" class="avatar-text avatar-md" title="Detail Siswa">
                                    <i class="feather feather-eye"></i>
                                </a>';

                    // Relationship Links
                    $attendanceBtn = '<a href="'.route('dashboard.attendances.index', ['classroom_id' => $item->id]).'" class="avatar-text avatar-md bg-soft-warning text-warning" title="Presensi Kelas">
                        <i class="feather feather-calendar"></i>
                    </a>';

                    $gradesBtn = '<a href="'.route('dashboard.grades.index', ['classroom_id' => $item->id]).'" class="avatar-text avatar-md bg-soft-success text-success" title="Nilai Kelas">
                        <i class="feather feather-book-open"></i>
                    </a>';

                    $scheduleBtn = '<a href="'.route('dashboard.schedules.index', ['classroom_id' => $item->id]).'" class="avatar-text avatar-md bg-soft-info text-info" title="Jadwal Pelajaran">
                        <i class="feather feather-clock"></i>
                    </a>';

                    $dropdown = '
                        <div class="dropdown">
                            <a href="javascript:void(0);" class="avatar-text avatar-md" data-bs-toggle="dropdown" data-bs-offset="25, 25">
                                <i class="feather feather-more-horizontal"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="'.route('dashboard.classrooms.edit', $item->id).'" class="dropdown-item">
                                    <i class="feather feather-edit-3 me-2"></i>
                                    <span>Edit</span>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="javascript:void(0);" class="dropdown-item delete-btn text-danger" data-id="'.$item->id.'" data-url="'.route('dashboard.classrooms.destroy', $item->id).'">
                                    <i class="feather feather-trash-2 me-2"></i>
                                    <span>Hapus</span>
                                </a>
                            </div>
                        </div>';

                    return '<div class="gap-2 hstack justify-content-end">'
                         .$attendanceBtn
                         .$gradesBtn
                         .$scheduleBtn
                         .$viewBtn
                         .$dropdown
                         .'</div>';
                })
                ->rawColumns(['checkbox', 'action'])
                ->make(true);
        }

        $academicYears = AcademicYear::all();
        $stats = $this->classroomService->getIndexStats();

        return view('dashboard.classrooms.index', array_merge(
            compact('academicYears'),
            $stats
        ));
    }

    public function create()
    {
        $this->authorize('create', Classroom::class);

        $formData = $this->classroomService->getFormData();

        return view('dashboard.classrooms.create', $formData);
    }

    public function store(StoreClassroomRequest $request)
    {
        $this->authorize('create', Classroom::class);

        $data = $request->validated();
        $teacherIds = $request->input('teacher_ids', []);
        $subjectIds = $request->input('subject_ids', []);

        $classroom = $this->classroomService->createClassroom($data, $teacherIds, $subjectIds);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Kelas berhasil ditambahkan.',
                'data' => $classroom,
            ], 201);
        }

        return redirect()->route('dashboard.classrooms.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function show(Classroom $classroomRecord)
    {
        $this->authorize('view', $classroomRecord);

        $details = $this->classroomService->getClassroomDetails($classroomRecord);

        return view('dashboard.classrooms.show', [
            'classroom' => $classroomRecord,
            'students' => $details['students'],
            'averageGrade' => $details['averageGrade'],
            'attendanceToday' => $details['attendanceToday'],
        ]);
    }

    public function getStudents(Classroom $classroomRecord)
    {
        $students = $this->classroomService->getStudentsList($classroomRecord);

        return response()->json([
            'status' => 'success',
            'students' => $students,
            'count' => $students->count(),
        ]);
    }

    public function edit(Classroom $classroomRecord)
    {
        $this->authorize('update', $classroomRecord);

        $formData = $this->classroomService->getFormData();

        return view('dashboard.classrooms.edit', array_merge(
            ['classroom' => $classroomRecord],
            $formData
        ));
    }

    public function update(UpdateClassroomRequest $request, Classroom $classroomRecord)
    {
        $this->authorize('update', $classroomRecord);

        $data = $request->validated();
        $teacherIds = $request->has('teacher_ids') ? $request->teacher_ids : [];
        $subjectIds = $request->has('subject_ids') ? $request->subject_ids : [];

        $classroom = $this->classroomService->updateClassroom($classroomRecord, $data, $teacherIds, $subjectIds);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Kelas berhasil diperbarui.',
                'data' => $classroom,
            ]);
        }

        return redirect()->route('dashboard.classrooms.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Classroom $classroomRecord)
    {
        $this->authorize('delete', $classroomRecord);

        $this->classroomService->deleteClassroom($classroomRecord);

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json(['message' => 'Kelas berhasil dihapus.'], 200);
        }

        return redirect()->route('dashboard.classrooms.index')->with('success', 'Kelas berhasil dihapus.');
    }

    /**
     * POST /classrooms/{classroom}/add-student
     */
    public function addStudent(Request $request, Classroom $classroomRecord)
    {
        $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'required|exists:students,id',
        ]);

        $count = $this->classroomService->addStudents($classroomRecord, $request->input('student_ids'));

        return response()->json([
            'status' => 'success',
            'message' => $count === 1
                ? '1 siswa berhasil ditambahkan ke kelas.'
                : $count.' siswa berhasil ditambahkan ke kelas.',
        ]);
    }

    /**
     * DELETE /classrooms/{classroom}/remove-student/{student}
     */
    public function removeStudent(Classroom $classroomRecord, Student $studentRecord)
    {
        $this->classroomService->removeStudent($classroomRecord, $studentRecord->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Siswa berhasil dikeluarkan dari kelas.',
        ]);
    }
}
