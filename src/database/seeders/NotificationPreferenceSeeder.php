<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationPreferenceSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // Check if preference already exists
            $exists = DB::table('notification_preferences')
                ->where('user_id', $user->id)
                ->exists();

            if (! $exists) {
                DB::table('notification_preferences')->insert([
                    'id' => Str::uuid(),
                    'user_id' => $user->id,
                    'email_notifications' => true,
                    'sms_notifications' => false,
                    'push_notifications' => false,
                    'whatsapp_notifications' => true,
                    'frequency' => 'immediate',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
