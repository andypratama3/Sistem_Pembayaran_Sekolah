<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Traits\ApiResponse;
use App\Traits\CrudApiTrait;
use Illuminate\Http\Request;

class ClassroomApiController extends Controller
{
    use ApiResponse;
    use CrudApiTrait;

    protected $model = Classroom::class;

    protected $indexQueryRelations = ['academicYear', 'students'];

    protected $showQueryRelations = ['academicYear', 'students', 'subjects'];

    protected $filterFields = ['academic_year_id'];

    protected $perPage = 15;

    protected function storeRules(Request $request): array
    {
        return [
            'academic_year_id' => 'required|exists:academic_years,id',
            'name' => 'required|string',
            'classroom_type' => 'required|string',
            'slug' => 'required|string|unique:classrooms',
        ];
    }

    protected function updateRules(Request $request): array
    {
        $id = $request->route('id') ?? $request->route('classroom');

        return [
            'academic_year_id' => 'sometimes|required|exists:academic_years,id',
            'name' => 'sometimes|required|string',
            'classroom_type' => 'sometimes|required|string',
            'slug' => 'sometimes|required|string|unique:classrooms,slug,'.$id,
        ];
    }

    protected function getSearchableFields(): array
    {
        return ['name', 'code'];
    }
}
