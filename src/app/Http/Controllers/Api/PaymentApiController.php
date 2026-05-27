<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Payment;
use App\Models\PaymentTitle;
use App\Models\Student;
use App\Traits\ApiResponse;
use App\Traits\CrudApiTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentApiController extends Controller
{
    use ApiResponse;
    use CrudApiTrait;

    protected $model = Payment::class;

    protected $indexQueryRelations = ['student', 'paymentTitle', 'charges'];

    protected $showQueryRelations = ['student', 'paymentTitle', 'charges'];

    protected $filterFields = ['student_id', 'status'];

    protected $perPage = 15;

    protected function storeRules(Request $request): array
    {
        return [
            'student_id' => 'required|exists:students,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|max:50',
        ];
    }

    protected function validateStoreRequest(Request $request): array
    {
        $data = $request->validate($this->storeRules($request));

        $student = Student::query()->findOrFail($data['student_id']);
        $classroom = $student->classrooms()->first() ?? Classroom::query()->first();
        if (!$classroom) {
            $classroom = Classroom::factory()->create();
        }
        $paymentTitle = PaymentTitle::query()->first();
        if (!$paymentTitle) {
            $paymentTitle = PaymentTitle::factory()->create([
                'name' => 'General Payment',
                'code' => 'GENERAL',
                'slug' => 'general-payment',
            ]);
        }

        return [
            'order_id' => $data['reference'] ?? 'ORD-'.Str::upper(Str::random(12)),
            'student_id' => $data['student_id'],
            'classroom_id' => $classroom->id,
            'classroom_type' => $classroom->classroom_type ?? 'Regular',
            'email' => $student->email,
            'gross_amount' => $data['amount'],
            'payment_type' => $data['payment_method'] ?? 'manual',
            'payment_title_id' => $paymentTitle->id,
            'status' => $data['status'] ?? 'pending',
        ];
    }

    protected function updateRules(Request $request): array
    {
        return [
            'student_id' => 'sometimes|required|exists:students,id',
            'amount' => 'sometimes|required|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|max:50',
        ];
    }

    protected function getSearchableFields(): array
    {
        return ['student.name', 'order_id'];
    }
}
