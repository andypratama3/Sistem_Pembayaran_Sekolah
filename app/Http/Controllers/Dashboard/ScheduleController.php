<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $classroomId = $request->query('classroom_id');

        if ($classroomId) {
            return redirect()->route('dashboard.classrooms.show', $classroomId)
                ->with('info', 'Fitur jadwal sedang dalam pengembangan.');
        }

        return redirect()->route('dashboard.classrooms.index')
            ->with('info', 'Fitur jadwal sedang dalam pengembangan.');
    }
}
