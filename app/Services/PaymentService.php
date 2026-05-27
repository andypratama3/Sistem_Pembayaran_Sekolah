<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\Payment;
use App\Models\PaymentTitle;
use App\Models\Student;
use App\Models\StudentFee;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Create payment record
     */
    public function create(array $data): Payment
    {
        $student = Student::findOrFail($data['student_id']);
        $classroom = Classroom::findOrFail($data['classroom_id'] ?? $student->classrooms()->first()?->id ?? Classroom::first()?->id);
        $titleId = $data['payment_title_id'] ?? null;
        $paymentTitle = PaymentTitle::findOrFail($titleId ?? PaymentTitle::first()?->id);

        $payment = Payment::create([
            'order_id' => $data['order_id'] ?? 'ORD-'.Str::upper(Str::random(12)),
            'student_id' => $student->id,
            'classroom_id' => $classroom->id,
            'classroom_type' => $classroom->classroom_type,
            'payment_title_id' => $paymentTitle->id,
            'gross_amount' => $data['gross_amount'],
            'email' => $data['email'] ?? $student->email,
            'payment_type' => $data['payment_method'] ?? $data['payment_type'] ?? 'manual',
            'status' => $data['status'] ?? 'pending',
        ]);

        // Create StudentFee record linked to this payment
        StudentFee::create([
            'student_id' => $student->id,
            'payment_title_id' => $paymentTitle->id,
            'amount' => $data['gross_amount'],
            'due_date' => now()->addDays((int) env('PAYMENT_DUE_DAYS', 7)),
            'status' => 'unpaid',
            'academic_year' => $data['academic_year'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return $payment;
    }

    /**
     * Get outstanding payments for student
     */
    public function getOutstanding(Student $student): float
    {
        return $student->payments()
            ->whereIn('status', ['pending', 'expired'])
            ->sum('gross_amount');
    }

    /**
     * Mark payment as completed
     */
    public function markPaid(Payment $payment, ?string $transactionId = null): void
    {
        $payment->update([
            'status' => 'completed',
            'transaction_id' => $transactionId,
        ]);
    }

    /**
     * Get payments by student and status
     */
    public function getByStudentStatus(Student $student, string $status)
    {
        return $student->payments()
            ->where('status', $status)
            ->with(['paymentTitle', 'classroom'])
            ->get();
    }
}
