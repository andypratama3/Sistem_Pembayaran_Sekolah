<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use App\Models\WhatsAppConversation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WhatsAppConversationSeeder extends Seeder
{
    /**
     * Seed sample WhatsApp conversations for dashboard testing.
     */
    public function run(): void
    {
        $students = Student::query()->inRandomOrder()->take(8)->get();
        $admins = User::query()->whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'superadmin']);
        })->get();

        if ($students->isEmpty()) {
            return;
        }

        foreach ($students as $index => $student) {
            if ($index >= 8) {
                break;
            }

            $phone = '62812'.str_pad((string) ($index + 1000000), 7, '0', STR_PAD_LEFT);
            $assignedAdmin = $admins->isNotEmpty() ? $admins->random() : null;

            $conversation = WhatsAppConversation::query()->where('phone_number', $phone)->first();

            if (! $conversation) {
                WhatsAppConversation::create([
                    'id' => (string) Str::uuid(),
                    'phone_number' => $phone,
                    'profile_name' => 'Orang Tua '.$student->name,
                    'student_id' => $student->id,
                    'assigned_admin_id' => $assignedAdmin?->id,
                    'status' => $index % 5 === 0 ? 'closed' : 'active',
                    'notes' => $index % 3 === 0 ? 'Percakapan seputar pembayaran bulanan.' : null,
                    'message_count' => 0,
                    'last_message_at' => now()->subDays(rand(0, 7))->subMinutes(rand(0, 120)),
                ]);
            }
        }
    }
}
