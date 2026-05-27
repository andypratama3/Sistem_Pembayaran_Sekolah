<?php

namespace App\Http\Controllers\Dashboard;

use App\Exports\StudentExport;
use App\Http\Controllers\ResourceController;
use App\Http\Requests\Dashboard\FilterRequest;
use App\Http\Requests\Dashboard\StudentRequest;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentClassroom;
use App\Services\ExportService;
use App\Services\FileUploadService;
use App\Services\StudentService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class StudentController extends ResourceController
{
    use ApiResponse;

    protected static string $permissionResource = 'students';

    public function __construct(
        private StudentService $studentService,
        private FileUploadService $fileUploadService,
        private ExportService $exportService
    ) {}

    /**
     * Display a listing of students (DataTables)
     */
    public function index(FilterRequest $request)
    {
        $this->authorize('viewAny', Student::class);

        if ($request->has('export')) {
            $export = new StudentExport([], $request->all());

            return $this->exportService->download($export->headings(), $export->rows(), 'students.xlsx');
        }

        if ($request->ajax()) {
            $query = Student::with(['classrooms', 'payments'])->orderBy('created_at', 'desc');

            // Apply filters from request
            if ($request->filled('classroom_id')) {
                $classroomIds = (array) $request->classroom_id;
                $query->whereHas('classrooms', function ($q) use ($classroomIds) {
                    $q->whereIn('classrooms.id', $classroomIds);
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            return DataTables::of($query)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && $request->search['value'] != '') {
                        $search = $request->search['value'];
                        $query->where(function ($q) use ($search) {
                            $q->where('name', 'like', '%'.$search.'%')
                                ->orWhere('nisn', 'like', '%'.$search.'%')
                                ->orWhere('phone', 'like', '%'.$search.'%')
                                ->orWhereHas('classrooms', function ($q) use ($search) {
                                    $q->where('name', 'like', '%'.$search.'%');
                                });
                        });
                    }
                })
                ->addColumn('checkbox', function ($student) {
                    return '<input type="checkbox" class="student-checkbox" value="'.$student->id.'">';
                })
                ->addColumn('name', function ($student) {
                    return e($student->name);
                })
                ->addColumn('nisn', function ($student) {
                    return e($student->nisn);
                })
                ->addColumn('phone', function ($student) {
                    return e($student->phone);
                })
                ->addColumn('classroom', function ($student) {
                    $classes = $student->classrooms->pluck('name')->implode(', ');

                    return $classes ?: '-';
                })
                ->addColumn('status', function ($student) {
                    $statusClass = match ($student->status) {
                        'active' => 'success',
                        'inactive' => 'secondary',
                        'graduated' => 'primary',
                        'dropped' => 'danger',
                        default => 'secondary',
                    };

                    return '<span class="badge bg-'.$statusClass.'">'.ucfirst($student->status).'</span>';
                })
                ->addColumn('action', function ($student) {
                    $viewBtn = auth()->user()->can('view', $student) ?
                        '<a href="'.route('dashboard.students.show', $student->id).'" class="avatar-text avatar-md" title="Detail">
                            <i class="feather feather-eye"></i>
                        </a>' : '';

                    // Relationship Links
                    $gradesBtn = '<a href="'.route('dashboard.grades.index', ['student_id' => $student->id]).'" class="avatar-text avatar-md bg-soft-success text-success" title="Nilai">
                        <i class="feather feather-book-open"></i>
                    </a>';

                    $attendanceBtn = '<a href="'.route('dashboard.attendances.index', ['student_id' => $student->id]).'" class="avatar-text avatar-md bg-soft-warning text-warning" title="Presensi">
                        <i class="feather feather-calendar"></i>
                    </a>';

                    $paymentsBtn = '<a href="'.route('dashboard.payments.index', ['student_id' => $student->id]).'" class="avatar-text avatar-md bg-soft-info text-info" title="Pembayaran">
                        <i class="feather feather-credit-card"></i>
                    </a>';

                    $editBtn = auth()->user()->can('update', $student) ?
                        '<li>
                            <a class="dropdown-item" href="'.route('dashboard.students.edit', $student->id).'">
                                <i class="feather feather-edit-3 me-3"></i>
                                <span>Edit</span>
                            </a>
                        </li>' : '';

                    $deleteBtn = auth()->user()->can('delete', $student) ?
                        '<li>
                            <a class="dropdown-item delete-btn text-danger" href="javascript:void(0)"
                                data-id="'.$student->id.'"
                                data-url="'.route('dashboard.students.destroy', $student->id).'">
                                <i class="feather feather-trash-2 me-3"></i>
                                <span>Delete</span>
                            </a>
                        </li>' : '';

                    $dropdown = ($editBtn || $deleteBtn) ? '
                        <div class="dropdown">
                            <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown">
                                <i class="feather feather-more-horizontal"></i>
                            </a>
                            <ul class="dropdown-menu">
                                '.$editBtn.'
                                '.($editBtn && $deleteBtn ? '<li class="dropdown-divider"></li>' : '').'
                                '.$deleteBtn.'
                            </ul>
                        </div>' : '';

                    return '<div class="gap-2 hstack justify-content-end">'
                         .$gradesBtn
                         .$attendanceBtn
                         .$paymentsBtn
                         .$viewBtn
                         .$dropdown
                         .'</div>';
                })
                ->rawColumns(['checkbox', 'status', 'action'])
                ->make(true);
        }

        $classrooms = Classroom::select(['id', 'name'])->get();

        // Calculate stats
        $totalCount = Student::count();
        $activeCount = Student::where('status', 'active')->count();
        $noClassCount = Student::whereDoesntHave('classrooms')->count();
        $newCount = Student::where('status', 'baru')->count();

        return view('dashboard.students.index', compact('classrooms', 'totalCount', 'activeCount', 'noClassCount', 'newCount'));
    }

    /**
     * Show the form for creating a new student
     */
    public function create()
    {
        $this->authorize('create', Student::class);

        $classrooms = Classroom::select(['id', 'name'])->get();
        $academicYears = AcademicYear::all();

        return view('dashboard.students.create', compact('classrooms', 'academicYears'));
    }

    /**
     * Store a newly created student
     */
    public function store(StudentRequest $request)
    {
        $this->authorize('create', Student::class);

        $data = $request->validated();
        $data['slug'] = Str::slug($data['name'].'-'.$data['nisn']);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('students/photos', 'public');
        }

        $student = $this->studentService->create($data, $data['classroom_id'] ?? null);

        return redirect()->route('dashboard.students.show', $student)->with('success', 'Siswa berhasil ditambahkan.');
    }

    /**
     * Display the specified student
     */
    public function show(Student $studentRecord)
    {
        $this->authorize('view', $studentRecord);

        $studentRecord->load([
            'classrooms',
            'attendances' => fn ($q) => $q->latest('date')->limit(30),
            'grades' => fn ($q) => $q->with('subject:id,name', 'teacher:id,name')->latest()->limit(30),
            'payments' => fn ($q) => $q->latest()->limit(20),
        ]);

        return view('dashboard.students.show', ['student' => $studentRecord]);
    }

    /**
     * Show the form for editing the specified student
     */
    public function edit(Student $studentRecord)
    {
        $this->authorize('update', $studentRecord);

        $classrooms = Classroom::select(['id', 'name'])->get();
        $academicYears = AcademicYear::all();
        $studentClassrooms = $studentRecord->classrooms->pluck('id')->toArray();

        return view('dashboard.students.edit', ['student' => $studentRecord, 'classrooms' => $classrooms, 'studentClassrooms' => $studentClassrooms, 'academicYears' => $academicYears]);
    }

    /**
     * Update the specified student
     */
    public function update(StudentRequest $request, Student $studentRecord)
    {
        $this->authorize('update', $studentRecord);

        $data = $request->validated();
        $data['slug'] = Str::slug($data['name'].'-'.$data['nisn']);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($studentRecord->photo) {
                \Storage::disk('public')->delete($studentRecord->photo);
            }

            $data['photo'] = $request->file('photo')->store('students/photos', 'public');
        }

        $this->studentService->update($studentRecord, $data);

        return redirect()->route('dashboard.students.show', $studentRecord)->with('success', 'Siswa berhasil diperbarui.');
    }

    /**
     * Update student status
     */
    public function updateStatus(Request $request, Student $studentRecord)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,graduated,dropped',
        ]);

        $studentRecord->update(['status' => $request->status]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => 'Status siswa berhasil diperbarui.'], 200);
        }

        return back()->with('success', 'Status siswa berhasil diperbarui.');
    }

    /**
     * Remove the specified student
     */
    public function destroy(Student $studentRecord)
    {
        $this->authorize('delete', $studentRecord);

        $studentRecord->delete();

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json(['message' => 'Siswa berhasil dihapus.'], 200);
        }

        return redirect()->route('dashboard.students.index')->with('success', 'Siswa berhasil dihapus.');
    }

    /**
     * Assign student to classroom
     */
    public function assignClassroom(Request $request, Student $studentRecord)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
        ]);

        $this->studentService->assignClassroom(
            $studentRecord,
            Classroom::find($request->classroom_id),
            $request->classroom_type
        );

        return back()->with('success', 'Siswa berhasil ditambahkan ke kelas.');
    }

    /**
     * Unassign student from classroom
     */
    public function unassignClassroom(Request $request, Student $studentRecord)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
        ]);

        $this->studentService->removeClassroom(
            $studentRecord,
            Classroom::find($request->classroom_id)
        );

        return back()->with('success', 'Siswa berhasil dikeluarkan dari kelas.');
    }

    public function searchAvailable(Request $request)
    {
        $query = $request->input('q', '');
        $classroomId = $request->input('classroom_id');

        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $students = Student::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
                ->orWhere('nisn', 'like', "%{$query}%");
        });

        if ($classroomId) {
            $existingStudentIds = StudentClassroom::where('classroom_id', $classroomId)
                ->pluck('student_id');
            $students->whereNotIn('id', $existingStudentIds);
        }

        $students = $students->limit(10)->get(['id', 'name', 'nisn']);

        $results = $students->map(fn ($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'nisn' => $s->nisn,
            'text' => $s->name.' (NISN: '.$s->nisn.')',
        ]);

        return response()->json(['results' => $results]);
    }
}
