<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Traits\ApiResponse;
use App\Traits\CrudApiTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StudentApiController extends Controller
{
    use ApiResponse;
    use CrudApiTrait;

    protected $model = Student::class;

    protected $indexQueryRelations = ['classrooms', 'user'];

    protected $showQueryRelations = ['classrooms', 'user', 'payments', 'attendances', 'grades'];

    protected $filterFields = ['classroom_id', 'status'];

    protected $perPage = 15;

    /**
     * Override to add custom slug generation for Student
     */
    protected function validateStoreRequest(Request $request): array
    {
        $data = $request->validate($this->storeRules($request));
        $data['slug'] = Str::slug($data['name'].'-'.$data['nisn']);

        return $data;
    }

    /**
     * Override to add custom slug generation for Student updates
     */
    protected function validateUpdateRequest(Request $request): array
    {
        $data = $request->validate($this->updateRules($request));
        if (isset($data['name']) || isset($data['nisn'])) {
            $student = Student::findOrFail($request->route('id') ?? $request->route('student'));
            $name = $data['name'] ?? $student->name;
            $nisn = $data['nisn'] ?? $student->nisn;
            $data['slug'] = Str::slug($name.'-'.$nisn);
        }

        return $data;
    }

    protected function storeRules(Request $request): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email',
            'gender' => 'required|string|in:Laki-laki,Perempuan',
            'birth_place' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'nisn' => 'required|string|max:20|unique:students,nisn',
            'phone' => 'required|string|max:20',
            'religion' => 'required|string|max:50',
            'province_id' => 'required|string|max:10',
            'regency_id' => 'required|string|max:10',
            'district_id' => 'required|string|max:10',
            'village_id' => 'required|string|max:10',
            'status' => 'nullable|string|in:active,inactive',
        ];
    }

    protected function updateRules(Request $request): array
    {
        $studentId = $request->route('id') ?? $request->route('student');

        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:students,email,'.$studentId,
            'gender' => 'nullable|string|in:Laki-laki,Perempuan',
            'birth_date' => 'nullable|date',
            'nisn' => 'sometimes|required|string|max:20|unique:students,nisn,'.$studentId,
            'phone' => 'nullable|string|max:20',
            'status' => 'nullable|string|in:active,inactive',
        ];
    }

    protected function getSearchableFields(): array
    {
        return ['name', 'nisn', 'phone'];
    }
}
