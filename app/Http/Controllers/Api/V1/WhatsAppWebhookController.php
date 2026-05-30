<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppConversation;
use App\Services\WhatsAppAdminRouterService;
use App\Services\WhatsAppBotService;
use App\Services\WhatsAppChatService;
use App\Services\WhatsappMetaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function __construct(
        private readonly WhatsAppBotService $botService,
        private readonly WhatsAppAdminRouterService $adminRouter,
        private readonly WhatsAppChatService $chatService
    ) {}

    /**
     * Verify webhook token from Meta
     * GET /api/v1/webhook/whatsapp
     */
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $expectedToken = \get_system_config('whatsapp_verify_token', config('services.whatsapp.webhook_verify_token'));

        if ($mode === 'subscribe' && $token === $expectedToken) {
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response('Forbidden', 403);
    }

    /**
     * Handle incoming webhooks from Meta
     * POST /api/v1/webhook/whatsapp
     */
    public function handle(Request $request)
    {
        // Verifikasi signature Meta
        $signature = $request->header('X-Hub-Signature-256');
        $payload = $request->getContent();
        $secret = \get_system_config('whatsapp_webhook_secret', config('services.whatsapp.webhook_secret'));
        if (empty($secret)) {
            throw new \RuntimeException('WhatsApp webhook secret not configured');
        }
        $expected = 'sha256='.hash_hmac('sha256', $payload, $secret);

        if (! hash_equals($expected, $signature ?? '')) {
            Log::warning('WhatsApp webhook signature mismatch');

            return response()->json(['error' => 'Invalid signature'], 403);
        }

        try {
            $data = $request->json()->all();

            Log::channel('whatsapp')->info('📨 Webhook received', [
                'object' => $data['object'] ?? null,
            ]);

            if (isset($data['object']) && $data['object'] === 'whatsapp_business_account' && isset($data['entry'])) {
                foreach ($data['entry'] as $entry) {
                    $this->processEntry($entry);
                }
            }

            return response()->json(['status' => 'ok'], 200);

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Webhook handle error', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);

            return response()->json(['status' => 'error'], 200);
        }
    }

    // =========================================================================
    // PRIVATE METHODS
    // =========================================================================

    private function processEntry($entry): void
    {
        if (! isset($entry['changes']) || ! is_array($entry['changes'])) {
            return;
        }

        foreach ($entry['changes'] as $change) {
            $field = $change['field'] ?? null;
            $value = $change['value'] ?? [];

            match ($field) {
                'messages' => $this->processMessages($value),
                'message_template_status_update' => $this->processTemplateStatusUpdate($value),
                'account_alerts' => $this->processAccountAlerts($value),
                'account_update' => Log::channel('whatsapp')->info('🔄 Account update', $value),
                default => Log::channel('whatsapp')->debug('Ignoring field', ['field' => $field]),
            };

            if (! empty($change['statuses'])) {
                $this->processDeliveryReceipts($change['statuses'], $value);
            }
        }
    }

    private function processMessages($value): void
    {
        if (isset($value['messages']) && is_array($value['messages'])) {
            foreach ($value['messages'] as $message) {
                $this->handleIncomingMessage($message, $value);
            }
        }

        if (isset($value['statuses']) && is_array($value['statuses'])) {
            foreach ($value['statuses'] as $status) {
                $this->handleMessageStatus($status);
            }
        }
    }

    private function handleIncomingMessage($message, $value): void
    {
        try {
            $from = $message['from'] ?? null;
            $messageId = $message['id'] ?? null;
            $timestamp = $message['timestamp'] ?? now()->timestamp;
            $type = $message['type'] ?? 'unknown';

            if (! $from || ! $messageId) {
                Log::channel('whatsapp')->warning('⚠️ Invalid message data');

                return;
            }

            $profileName = $value['contacts'][0]['profile']['name'] ?? 'Bapak/Ibu';

            Log::channel('whatsapp')->info('📬 Incoming message', [
                'from' => substr($from, 0, 4).'****',
                'name' => $profileName,
                'type' => $type,
            ]);

            $this->storeIncomingMessage([
                'message_id' => $messageId,
                'phone' => $from,
                'profile_name' => $profileName,
                'type' => $type,
                'content' => json_encode($message),
                'timestamp' => date('Y-m-d H:i:s', $timestamp),
            ]);

            $messageText = $this->extractMessageText($message, $type);

            if ($messageText !== null) {
                // Get or create conversation
                $conversation = WhatsAppConversation::firstOrCreate(
                    ['phone_number' => $from],
                    [
                        'profile_name' => $profileName,
                        'status' => 'active',
                    ]
                );

                // Store message in whatsapp_messages so dashboard shows it
                $this->chatService->storeIncomingMessage(
                    $conversation,
                    $messageText,
                    $type,
                    null,
                    null,
                    $messageId
                );

                // Try to route to admin
                $routedToAdmin = $this->adminRouter->routeIncomingMessage(
                    $conversation,
                    $messageText,
                    $profileName
                );

                // If not routed to admin (outside hours), don't process through bot
                if (! $routedToAdmin) {
                    return;
                }

                // Otherwise, process through bot service
                $this->dispatchBotHandler($from, $messageText, $profileName);
            } else {
                $this->handleUnsupportedMessageType($from, $type, $profileName);
            }

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Handle incoming message error', [
                'error' => $e->getMessage(),
                'messageId' => $messageId ?? null,
            ]);
        }
    }

    private function extractMessageText($message, string $type): ?string
    {
        return match ($type) {
            'text' => trim($message['text']['body'] ?? ''),
            'interactive' => $message['interactive']['button_reply']['id']
                            ?? $message['interactive']['list_reply']['id']
                            ?? null,
            'button' => $message['button']['payload']
                            ?? $message['button']['text']
                            ?? null,
            default => null,
        };
    }

    private function dispatchBotHandler(string $phone, string $messageText, string $profileName): void
    {
        try {
            $this->botService->handle($phone, $messageText, $profileName);
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Bot handler error', [
                'error' => $e->getMessage(),
                'phone' => substr($phone, 0, 4).'****',
            ]);
        }
    }

    private function handleUnsupportedMessageType(string $from, string $type, string $profileName): void
    {
        Log::channel('whatsapp')->info("📎 Unsupported type: {$type}", ['from' => substr($from, 0, 4).'****']);

        try {
            $whatsapp = new WhatsappMetaService;
            $whatsapp->sendMessage(
                $from,
                "Maaf, saya hanya dapat memproses pesan teks. 🙏\n\n"
                ."Silakan kirimkan *NISN* (10 digit angka) untuk mengecek informasi tagihan.\n\n"
                .'Ketik *bantuan* untuk panduan penggunaan.'
            );
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Unsupported type reply error', ['error' => $e->getMessage()]);
        }
    }

    private function handleMessageStatus($status): void
    {
        try {
            $messageId = $status['id'] ?? null;
            $statusType = $status['status'] ?? null;
            $recipient = $status['recipient_id'] ?? null;
            $timestamp = $status['timestamp'] ?? now()->timestamp;

            if (! $messageId || ! $statusType) {
                return;
            }

            Log::channel('whatsapp')->info('📊 Status update', [
                'messageId' => substr($messageId, -10),
                'status' => $statusType,
            ]);

            $this->storeMessageStatus([
                'message_id' => $messageId,
                'status' => $statusType,
                'recipient' => $recipient,
                'timestamp' => date('Y-m-d H:i:s', $timestamp),
                'errors' => isset($status['errors']) ? json_encode($status['errors']) : null,
            ]);

            if ($statusType === 'failed') {
                Log::channel('whatsapp')->error('❌ Message failed', ['errors' => $status['errors'] ?? []]);
            }

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Status update error', ['error' => $e->getMessage()]);
        }
    }

    private function processDeliveryReceipts($statuses, $value): void
    {
        foreach ($statuses as $status) {
            $this->handleMessageStatus($status);
        }
    }

    private function processTemplateStatusUpdate($value): void
    {
        Log::channel('whatsapp')->info('📋 Template status update', [
            'event' => $value['event'] ?? null,
            'template' => $value['message_template_name'] ?? null,
        ]);
    }

    private function processAccountAlerts($value): void
    {
        $alertType = $value['alert_type'] ?? null;
        Log::channel('whatsapp')->warning('🚨 Account alert', ['type' => $alertType]);

        if (in_array($alertType, ['PHONE_NUMBER_FLAGGED', 'PHONE_NUMBER_RESTRICTED'])) {
            Log::channel('whatsapp')->error('🚨 CRITICAL ALERT', ['type' => $alertType, 'details' => $value]);
        }
    }

    private function storeIncomingMessage(array $data): void
    {
        try {
            DB::table('whatsapp_incoming_messages')->insert([
                'message_id' => $data['message_id'],
                'phone' => $data['phone'],
                'profile_name' => $data['profile_name'],
                'type' => $data['type'],
                'content' => $data['content'],
                'status' => 'received',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Store message error', ['error' => $e->getMessage()]);
        }
    }

    private function storeMessageStatus(array $data): void
    {
        try {
            DB::table('whatsapp_message_statuses')->insert([
                'message_id' => $data['message_id'],
                'status' => $data['status'],
                'recipient' => $data['recipient'],
                'timestamp' => $data['timestamp'],
                'errors' => $data['errors'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Store status error', ['error' => $e->getMessage()]);
        }
    }

    public function getMessagesHistory(Request $request)
    {
        $limit = $request->input('limit', 50);
        $whatsapp = new WhatsappMetaService;
        $result = $whatsapp->getMessagesHistory($limit);

        return response()->json($result);
    }

    public function getTemplate()
    {
        try {
            $whatsapp = new WhatsappMetaService;
            $templates = $whatsapp->getTemplates();

            return response()->json($templates);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
