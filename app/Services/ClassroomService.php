<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ClassroomService
{
    public function getFilteredQuery(?string $classroomType = null, ?string $teacherId = null, ?string $subjectId = null)
    {
        $query = Classroom::with(['students', 'academicYear'])
            ->withCount('students')
            ->latest();

        if ($classroomType) {
            $query->where('classroom_type', $classroomType);
        }

        return $query;
    }

    public function getIndexStats(): array
    {
        return [
            'totalCount' => Classroom::count(),
            'totalStudentCount' => Student::where('status', 'active')->count(),
        ];
    }

    public function getFormData(): array
    {
        return [
            'academicYears' => AcademicYear::all(),
        ];
    }

    public function createClassroom(array $data, array $teacherIds = [], array $subjectIds = []): Classroom
    {
        $data['slug'] = Str::slug($data['name']);
        $classroom = Classroom::create($data);

        return $classroom->fresh(['students', 'academicYear']);
    }

    public function updateClassroom(Classroom $classroom, array $data, ?array $teacherIds = null, ?array $subjectIds = null): Classroom
    {
        $classroom->update($data);

        return $classroom->fresh(['students', 'academicYear']);
    }

    public function deleteClassroom(Classroom $classroom): ?bool
    {
        return $classroom->delete();
    }

    public function getClassroomDetails(Classroom $classroom): array
    {
        $students = $classroom->students()->orderBy('name')->get();

        return [
            'students' => $students,
            'totalStudents' => $students->count(),
        ];
    }

    public function getStudentsList(Classroom $classroom): Collection
    {
        return $classroom->students()
            ->orderBy('name')
            ->get(['students.id', 'students.name', 'students.nisn', 'students.gender', 'students.phone']);
    }

    public function addStudents(Classroom $classroom, array $studentIds): int
    {
        $attachData = [];
        foreach ($studentIds as $studentId) {
            $attachData[$studentId] = ['classroom_type' => $classroom->classroom_type];
        }
        $classroom->students()->syncWithoutDetaching($attachData);

        return count($studentIds);
    }

    public function removeStudent(Classroom $classroom, string $studentId): void
    {
        $classroom->students()->detach($studentId);
    }
}
