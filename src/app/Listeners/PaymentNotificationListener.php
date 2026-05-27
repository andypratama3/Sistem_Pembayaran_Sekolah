<?php

namespace App\Listeners;

use App\Events\PaymentCompleted;
use App\Jobs\SendNotificationEmailJob;
use App\Models\Notification;
use App\Models\User;

class PaymentNotificationListener
{
    /**
     * Handle the event.
     */
    public function handle(PaymentCompleted $event): void
    {
        $payment = $event->payment;
        $student = $payment->student;

        if (! $student) {
            return;
        }

        // Notify guardians
        $guardians = $student->getParents() ?? collect();

        foreach ($guardians as $guardian) {
            try {
                $this->notifyGuardianOfPayment($guardian, $payment, $student);
            } catch (\Exception $e) {
                \Log::error('PaymentNotificationListener error for guardian', [
                    'payment_id' => $payment->id,
                    'guardian_id' => $guardian->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Notify student/user
        if ($student->user) {
            try {
                $this->notifyStudentOfPayment($student->user, $payment);
            } catch (\Exception $e) {
                \Log::error('PaymentNotificationListener error for student', [
                    'payment_id' => $payment->id,
                    'student_id' => $student->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Notify finance staff for accounting
        try {
            $this->notifyFinanceStaff($payment);
        } catch (\Exception $e) {
            \Log::error('PaymentNotificationListener error for finance', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify guardian of payment completion.
     */
    protected function notifyGuardianOfPayment($guardian, $payment, $student): void
    {
        // Skip if guardian is not a proper User object (e.g., stdClass from getParents())
        if (! isset($guardian->id) || ! $guardian instanceof User) {
            return;
        }

        $amountFormatted = 'Rp '.number_format($payment->gross_amount, 0, ',', '.');

        $notification = Notification::create([
            'user_id' => $guardian->id,
            'type' => 'payment',
            'title' => '✅ Pembayaran Berhasil',
            'message' => "Pembayaran sebesar {$amountFormatted} untuk {$student->name} telah berhasil diproses.",
            'data' => json_encode([
                'payment_id' => $payment->id,
                'student_id' => $student->id,
                'amount' => $payment->gross_amount,
                'transaction_id' => $payment->transaction_id,
                'receipt_url' => route('payments.receipt', $payment->id),
            ]),
            'is_read' => false,
        ]);

        SendNotificationEmailJob::dispatch($guardian, $notification);
    }

    /**
     * Notify student of payment.
     */
    protected function notifyStudentOfPayment($studentUser, $payment): void
    {
        $amountFormatted = 'Rp '.number_format($payment->gross_amount, 0, ',', '.');

        $notification = Notification::create([
            'user_id' => $studentUser->id,
            'type' => 'payment_receipt',
            'title' => '💰 Pembayaran Diterima',
            'message' => "Pembayaran sebesar {$amountFormatted} telah diterima. Silakan cek detail di dashboard.",
            'data' => json_encode([
                'payment_id' => $payment->id,
                'amount' => $payment->gross_amount,
                'type' => $payment->type,
                'transaction_id' => $payment->transaction_id,
            ]),
            'is_read' => false,
        ]);

        SendNotificationEmailJob::dispatch($studentUser, $notification);
    }

    /**
     * Notify finance staff of payment for accounting.
     */
    protected function notifyFinanceStaff($payment): void
    {
        $financeUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'finance');
        })->get();
        $amountFormatted = 'Rp '.number_format($payment->gross_amount, 0, ',', '.');

        foreach ($financeUsers as $financeUser) {
            $notification = Notification::create([
                'user_id' => $financeUser->id,
                'type' => 'payment',
                'title' => '📝 Pembayaran Baru Tercatat',
                'message' => "Pembayaran sebesar {$amountFormatted} (ID: {$payment->transaction_id}) telah tercatat.",
                'data' => json_encode([
                    'payment_id' => $payment->id,
                    'transaction_id' => $payment->transaction_id,
                    'amount' => $payment->gross_amount,
                ]),
                'is_read' => false,
            ]);

            SendNotificationEmailJob::dispatch($financeUser, $notification);
        }
    }
}
