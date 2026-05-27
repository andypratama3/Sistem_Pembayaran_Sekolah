<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class StudentService
{
    public function create(array $data, $classroomId = null): Student
    {
        $student = Student::create($data);

        if ($classroomId) {
            $classroomIds = is_array($classroomId) ? $classroomId : [$classroomId];
            foreach ($classroomIds as $id) {
                if ($id) {
                    DB::table('student_classrooms')->insert([
                        'student_id' => (string) $student->id,
                        'classroom_id' => (string) $id,
                        'classroom_type' => $data['classroom_type'] ?? null,
                        'academic_year_id' => $data['academic_year_id'] ?? null,
                        'status' => $data['status'] ?? 'active',
                        'enrolled_at' => now(),
                    ]);
                }
            }
        }

        return $student->fresh();
    }

    public function update(Student $student, array $data): Student
    {
        $student->update($data);

        return $student->fresh();
    }

    public function assignClassroom(Student $student, Classroom $classroom, ?string $classroomType = null): void
    {
        DB::table('student_classrooms')->insert([
            'student_id' => (string) $student->id,
            'classroom_id' => (string) $classroom->id,
            'classroom_type' => $classroomType,
            'academic_year_id' => (string) $classroom->academic_year_id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);
    }

    public function removeClassroom(Student $student, Classroom $classroom): void
    {
        DB::table('student_classrooms')
            ->where('student_id', (string) $student->id)
            ->where('classroom_id', (string) $classroom->id)
            ->delete();
    }

    public function nisExists(string $nisn, ?string $excludeId = null): bool
    {
        return Student::query()
            ->where('nisn', $nisn)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }
}
