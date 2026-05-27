<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\ResourceController;
use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Http\Request;

class SearchController extends ResourceController
{
    protected static string $permissionResource = 'search';

    public function search(Request $request)
    {
        $query = $request->input('q', '');
        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];

        // Search Students by name OR nisn
        $students = Student::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
                ->orWhere('nisn', 'like', "%{$query}%");
        })->limit(5)->get();
        foreach ($students as $student) {
            $results[] = [
                'id' => $student->id,
                'title' => $student->name.' (NISN: '.$student->nisn.')',
                'type' => 'Siswa',
                'url' => route('dashboard.students.show', $student->id),
                'icon' => 'feather-user',
                'color' => 'success',
            ];
        }

        // Search Classrooms by name OR code
        $classrooms = Classroom::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
                ->orWhere('code', 'like', "%{$query}%")
                ->orWhere('classroom_type', 'like', "%{$query}%");
        })->limit(3)->get();
        foreach ($classrooms as $classroom) {
            $results[] = [
                'id' => $classroom->id,
                'title' => $classroom->name.' / '.$classroom->code,
                'type' => 'Kelas',
                'url' => route('dashboard.classrooms.show', $classroom->id),
                'icon' => 'feather-home',
                'color' => 'warning',
            ];
        }

        return response()->json(['results' => $results]);
    }
}
