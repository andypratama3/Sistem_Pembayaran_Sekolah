<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppMessage;
use App\Services\WhatsAppChatService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WhatsAppWebhookSimulationSeeder extends Seeder
{
    /**
     * Seed chat data shaped like incoming Meta webhook events,
     * and project it into dashboard chat tables.
     */
    public function run(): void
    {
        /** @var WhatsAppChatService $chatService */
        $chatService = app(WhatsAppChatService::class);

        $admins = User::query()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['superadmin', 'admin', 'staff']);
            })
            ->get();

        $students = Student::query()->inRandomOrder()->take(3)->get();

        $contacts = [];
        foreach (range(0, max(2, $students->count() - 1)) as $index) {
            $student = $students->get($index);
            $contacts[] = [
                'phone' => '62812'.str_pad((string) (3000000 + $index), 7, '0', STR_PAD_LEFT),
                'name' => $student ? 'Orang Tua '.$student->name : 'Wali Siswa '.($index + 1),
                'student_id' => $student?->id,
            ];
        }

        foreach ($contacts as $contactIndex => $contact) {
            $conversation = $chatService->getOrCreateConversation($contact['phone'], $contact['name']);

            if ($contact['student_id']) {
                $conversation->update(['student_id' => $contact['student_id']]);
            }

            if (! $conversation->assigned_admin_id && $admins->isNotEmpty()) {
                $assigned = $admins[$contactIndex % $admins->count()];
                $conversation->update(['assigned_admin_id' => $assigned->id]);
            }

            $webhookEvents = [
                [
                    'message_id' => 'wamid.'.Str::lower(Str::random(26)),
                    'type' => 'text',
                    'text' => 'Halo admin, saya ingin menanyakan jadwal minggu ini.',
                    'status' => 'read',
                ],
                [
                    'message_id' => 'wamid.'.Str::lower(Str::random(26)),
                    'type' => 'interactive',
                    'text' => 'MENU_PEMBAYARAN',
                    'status' => 'delivered',
                ],
                [
                    'message_id' => 'wamid.'.Str::lower(Str::random(26)),
                    'type' => 'text',
                    'text' => 'Terima kasih, informasinya sangat membantu.',
                    'status' => 'received',
                ],
            ];

            foreach ($webhookEvents as $eventIndex => $event) {
                $timestamp = now()->subMinutes((count($webhookEvents) - $eventIndex) * 8 + $contactIndex * 5);

                $incomingMessagePayload = [
                    'phone' => $contact['phone'],
                    'type' => $event['type'],
                    'content' => json_encode([
                        'from' => $contact['phone'],
                        'id' => $event['message_id'],
                        'type' => $event['type'],
                        'text' => ['body' => $event['text']],
                        'timestamp' => $timestamp->timestamp,
                    ]),
                    'profile_name' => $contact['name'],
                    'status' => $event['status'] === 'read' ? 'replied' : 'processed',
                    'updated_at' => $timestamp,
                ];

                $incomingExists = DB::table('whatsapp_incoming_messages')
                    ->where('message_id', $event['message_id'])
                    ->exists();

                if ($incomingExists) {
                    DB::table('whatsapp_incoming_messages')
                        ->where('message_id', $event['message_id'])
                        ->update($incomingMessagePayload);
                } else {
                    DB::table('whatsapp_incoming_messages')->insert(array_merge(
                        ['id' => (string) Str::uuid(), 'message_id' => $event['message_id']],
                        $incomingMessagePayload,
                        ['created_at' => $timestamp]
                    ));
                }

                DB::table('whatsapp_message_statuses')->insert([
                    'id' => (string) Str::uuid(),
                    'message_id' => $event['message_id'],
                    'status' => $event['status'],
                    'recipient' => $contact['phone'],
                    'timestamp' => $timestamp,
                    'errors' => null,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);

                $existing = WhatsAppMessage::query()->where('whatsapp_message_id', $event['message_id'])->first();
                if (! $existing) {
                    $chatService->storeIncomingMessage(
                        $conversation,
                        $event['text'],
                        $event['type'] === 'interactive' ? 'template' : 'text',
                        null,
                        null,
                        $event['message_id']
                    );
                }
            }

            $admin = $conversation->assignedAdmin;
            if ($admin) {
                $replyTime = now()->subMinutes($contactIndex + 2);
                $alreadyReplied = WhatsAppMessage::query()
                    ->where('conversation_id', $conversation->id)
                    ->where('sender_type', 'admin')
                    ->exists();

                if (! $alreadyReplied) {
                    WhatsAppMessage::query()->create([
                        'id' => (string) Str::uuid(),
                        'conversation_id' => $conversation->id,
                        'sender_id' => $admin->id,
                        'sender_type' => 'admin',
                        'message_type' => 'text',
                        'content' => 'Halo, jadwal dan detail pembayaran sudah kami kirim. Silakan cek portal.',
                        'status' => 'delivered',
                        'created_at' => $replyTime,
                        'updated_at' => $replyTime,
                    ]);

                    $conversation->increment('message_count');
                }
            }

            $latestMessageAt = WhatsAppMessage::query()
                ->where('conversation_id', $conversation->id)
                ->max('created_at');

            $conversation->update([
                'status' => 'active',
                'last_message_at' => $latestMessageAt,
            ]);
        }

        // Ensure message_count reflects actual records after webhook simulation.
        WhatsAppConversation::query()->each(function (WhatsAppConversation $conversation) {
            $count = $conversation->messages()->count();
            $latest = $conversation->messages()->max('created_at');
            $conversation->update([
                'message_count' => $count,
                'last_message_at' => $latest,
            ]);
        });
    }
}
