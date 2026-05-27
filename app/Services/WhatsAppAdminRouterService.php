<?php

namespace App\Services;

use App\Models\AdminAvailability;
use App\Models\SchoolWorkHours;
use App\Models\User;
use App\Models\WhatsAppConversation;
use App\Notifications\WhatsAppMessageNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WhatsAppAdminRouterService
{
    /**
     * Route incoming message to admin if during work hours
     * Send auto-reply if outside work hours
     */
    public function routeIncomingMessage(
        WhatsAppConversation $conversation,
        string $messageText,
        string $profileName
    ): bool {
        try {
            $isWorkingHours = SchoolWorkHours::isWorkingNow();

            if (! $isWorkingHours) {
                return $this->sendOutsideHoursMessage($conversation, $messageText, $profileName);
            }

            // During work hours - assign to available admin
            return $this->assignToAvailableAdmin($conversation, $messageText, $profileName);

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('Error routing message to admin', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversation->id,
            ]);

            return false;
        }
    }

    /**
     * Assign conversation to available admin during work hours
     */
    private function assignToAvailableAdmin(
        WhatsAppConversation $conversation,
        string $messageText,
        string $profileName
    ): bool {
        $availableAdmins = AdminAvailability::getAvailableAdmins();

        if ($availableAdmins->isEmpty()) {
            // No admin available, send holding message
            return $this->sendHoldingMessage($conversation);
        }

        // Get admin with least assigned conversations
        $admin = $availableAdmins
            ->sortBy(function ($user) {
                return $user->assignedConversations()->count();
            })
            ->first();

        if (! $admin) {
            return $this->sendHoldingMessage($conversation);
        }

        // Assign to admin
        $conversation->update([
            'assigned_admin_id' => $admin->id,
            'admin_assigned_at' => now(),
            'work_hours_connected' => true,
            'status' => 'assigned_to_admin',
        ]);

        // Log activity
        DB::table('whatsapp_admin_activities')->insert([
            'conversation_id' => $conversation->id,
            'admin_id' => $admin->id,
            'action' => 'assigned',
            'details' => json_encode([
                'from_phone' => $conversation->phone_number,
                'profile_name' => $profileName,
                'first_message' => $messageText,
            ]),
            'assigned_at' => now(),
            'created_at' => now(),
        ]);

        // Send notification to admin
        $this->notifyAdminOfNewConversation($admin, $conversation, $profileName);

        Log::channel('whatsapp')->info('✅ Message routed to admin', [
            'admin' => $admin->name,
            'phone' => substr($conversation->phone_number, -4),
            'profile' => $profileName,
        ]);

        return true;
    }

    /**
     * Send message when outside work hours
     */
    private function sendOutsideHoursMessage(
        WhatsAppConversation $conversation,
        string $messageText,
        string $profileName
    ): bool {
        $todayHours = SchoolWorkHours::getTodayWorkHours();
        $nextWorkDay = $this->getNextWorkDay();

        $message = "Terima kasih atas pesan Anda, {$profileName}! 🙏\n\n";
        $message .= "Kami sedang tidak bertugas pada waktu ini.\n";

        $message .= "⏰ *Jam Kerja:*\n";
        if ($todayHours) {
            $message .= $todayHours->day_name.': '.$todayHours->work_start.' - '.$todayHours->work_end."\n\n";
        } else {
            $message .= "Senin - Jumat: 08:00 - 17:00\n\n";
        }

        $message .= "Kami akan merespon pesan Anda pada jam kerja berikutnya.\n\n";
        $message .= "Jika ada yang mendesak, silakan hubungi nomor sekolah kami.\n";

        try {
            $whatsapp = app(WhatsappMetaService::class);
            $whatsapp->sendMessage($conversation->phone_number, $message);

            // Store in database
            $conversation->update([
                'outside_hours_message' => $message,
                'status' => 'waiting_for_work_hours',
                'work_hours_connected' => false,
            ]);

            // Log message for later follow-up
            DB::table('whatsapp_admin_activities')->insert([
                'conversation_id' => $conversation->id,
                'admin_id' => null,
                'action' => 'auto_reply',
                'details' => json_encode([
                    'reason' => 'outside_work_hours',
                    'first_message' => $messageText,
                    'profile_name' => $profileName,
                ]),
                'assigned_at' => now(),
                'created_at' => now(),
            ]);

            Log::channel('whatsapp')->info('🌙 Outside hours auto-reply sent', [
                'phone' => substr($conversation->phone_number, -4),
                'profile' => $profileName,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('Failed to send outside hours message', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send holding message when no admin is available
     */
    private function sendHoldingMessage(WhatsAppConversation $conversation): bool
    {
        try {
            $message = "Terima kasih telah menghubungi kami! 🙏\n\n";
            $message .= "Semua admin kami sedang melayani pelanggan lain.\n";
            $message .= "Kami akan menghubungi Anda dalam beberapa menit.\n\n";
            $message .= 'Nomor Antrian Anda: *'.str_pad($conversation->id, 8, '0', STR_PAD_LEFT).'*';

            $whatsapp = app(WhatsappMetaService::class);
            $whatsapp->sendMessage($conversation->phone_number, $message);

            return true;

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('Failed to send holding message', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get next working day/time
     */
    private function getNextWorkDay(): ?string
    {
        for ($i = 1; $i <= 7; $i++) {
            $date = now()->addDays($i);
            $workHours = SchoolWorkHours::where('day_of_week', $date->dayOfWeek)
                ->where('is_active', true)
                ->first();

            if ($workHours) {
                return "{$workHours->day_name} ({$date->format('d/m/Y')}) - {$workHours->work_start}";
            }
        }

        return null;
    }

    /**
     * Notify admin of new conversation via notification
     */
    private function notifyAdminOfNewConversation(
        User $admin,
        WhatsAppConversation $conversation,
        string $profileName
    ): void {
        try {
            $notification = new WhatsAppMessageNotification(
                $conversation,
                $profileName
            );

            $admin->notify($notification);

            // Send SMS/Email to admin (optional)
            // $admin->notify(new SMSNotification("Pesan WhatsApp baru dari {$profileName}"));

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('Failed to notify admin', [
                'error' => $e->getMessage(),
                'admin_id' => $admin->id,
            ]);
        }
    }
}
