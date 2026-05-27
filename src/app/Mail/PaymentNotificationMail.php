<?php

namespace App\Mail;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Notification $notification) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Notifikasi Pembayaran - '.config('app.name'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.notification', with: [
            'title' => $this->notification->title,
            'body' => $this->notification->message,
            'type' => 'payment',
        ]);
    }
}
