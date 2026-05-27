<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('payments-'.$this->payment->student_id),
            new Channel('payment-updates'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'PaymentCompleted';
    }

    public function broadcastWith(): array
    {
        return [
            'payment_id' => $this->payment->id,
            'student_id' => $this->payment->student_id,
            'amount' => $this->payment->gross_amount,
            'status' => $this->payment->status,
            'timestamp' => now()->toIso8601ZuluString(),
        ];
    }
}
