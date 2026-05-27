<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    protected $phone;

    protected $messageText;

    protected $profileName;

    public $tries = 3;

    public $timeout = 60;

    public $backoff = [10, 60];

    public function __construct($phone, $messageText, $profileName = 'Bapak/Ibu')
    {
        $this->phone = $phone;
        $this->messageText = $messageText;
        $this->profileName = $profileName;
    }

    public function handle()
    {
        try {
            Log::channel('whatsapp')->info('Processing WhatsApp message job', [
                'phone' => substr($this->phone, 0, 4).'****',
                'message_length' => strlen($this->messageText),
            ]);

            // Message processing logic goes here
            // This is async handler for WhatsApp messages

            Log::channel('whatsapp')->info('WhatsApp message processed successfully', [
                'phone' => substr($this->phone, 0, 4).'****',
            ]);

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('Process WhatsApp message failed', [
                'error' => $e->getMessage(),
                'phone' => substr($this->phone, 0, 4).'****',
            ]);

            if ($this->attempts() < $this->tries) {
                $this->release($this->backoff[$this->attempts() - 1] ?? 60);
            }
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::critical('WhatsApp message job permanently failed', [
            'phone' => substr($this->phone, 0, 4).'****',
            'error' => $exception->getMessage(),
        ]);
    }
}
