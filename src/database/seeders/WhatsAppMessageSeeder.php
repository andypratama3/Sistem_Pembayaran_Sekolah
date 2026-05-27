<?php

namespace Database\Seeders;

use App\Models\WhatsAppConversation;
use App\Models\WhatsAppMessage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WhatsAppMessageSeeder extends Seeder
{
    /**
     * Seed WhatsApp message threads for seeded conversations.
     */
    public function run(): void
    {
        $conversations = WhatsAppConversation::query()->with('assignedAdmin')->take(8)->get();

        if ($conversations->isEmpty()) {
            return;
        }

        foreach ($conversations as $conversation) {
            $messages = [
                ['sender_type' => 'parent', 'content' => 'Halo admin, saya ingin menanyakan jadwal pelajaran minggu ini.'],
                ['sender_type' => 'admin', 'content' => 'Halo, untuk jadwal minggu ini sudah kami update di portal dashboard.'],
                ['sender_type' => 'parent', 'content' => 'Baik, terima kasih. Saya juga ingin menanyakan status pembayaran bulan ini.'],
                ['sender_type' => 'admin', 'content' => 'Pembayaran sudah kami terima. Bukti pembayaran tersedia di menu pembayaran.'],
            ];

            $count = 0;
            foreach ($messages as $i => $item) {
                $timestamp = now()->subDays(rand(0, 5))->subMinutes((count($messages) - $i) * 7);
                $isAdmin = $item['sender_type'] === 'admin';

                WhatsAppMessage::query()->create([
                    'id' => (string) Str::uuid(),
                    'conversation_id' => $conversation->id,
                    'sender_id' => $isAdmin ? $conversation->assigned_admin_id : null,
                    'sender_type' => $item['sender_type'],
                    'message_type' => 'text',
                    'content' => $item['content'],
                    'status' => $isAdmin ? 'delivered' : 'read',
                    'read_at' => $isAdmin ? null : $timestamp->copy()->addMinutes(10),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
                $count++;
            }

            $conversation->update([
                'message_count' => $count,
                'last_message_at' => now()->subDays(rand(0, 3))->subMinutes(rand(0, 30)),
            ]);
        }
    }
}
