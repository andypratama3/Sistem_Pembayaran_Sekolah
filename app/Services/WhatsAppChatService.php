<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppMessageTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsAppChatService
{
    /**
     * Get or create conversation for a phone number
     */
    public function getOrCreateConversation(string $phoneNumber, ?string $profileName = null): WhatsAppConversation
    {
        // Ensure phone number format
        $phoneNumber = $this->normalizePhoneNumber($phoneNumber);

        $conversation = WhatsAppConversation::where('phone_number', $phoneNumber)->first();

        if (! $conversation) {
            $conversation = WhatsAppConversation::create([
                'id' => Str::uuid(),
                'phone_number' => $phoneNumber,
                'profile_name' => $profileName ?? 'Contact',
                'status' => 'active',
                'message_count' => 0,
            ]);

            // Try to link to a student if phone number matches
            $this->linkToStudent($conversation);
        } elseif ($profileName && ! $conversation->profile_name) {
            $conversation->update(['profile_name' => $profileName]);
        }

        return $conversation;
    }

    /**
     * Try to link conversation to a student based on phone number
     */
    public function linkToStudent(WhatsAppConversation $conversation): void
    {
        $normalizedPhone = $this->normalizePhoneNumber($conversation->phone_number);

        $student = Student::where('phone', $conversation->phone_number)
            ->orWhere('phone', $normalizedPhone)
            ->first();

        if ($student) {
            $conversation->update(['student_id' => $student->id]);
        }
    }

    /**
     * Store incoming message from WhatsApp
     */
    public function storeIncomingMessage(
        WhatsAppConversation $conversation,
        string $content,
        string $messageType = 'text',
        ?string $mediaUrl = null,
        ?string $mediaType = null,
        ?string $whatsappMessageId = null
    ): WhatsAppMessage {
        $message = WhatsAppMessage::create([
            'id' => Str::uuid(),
            'conversation_id' => $conversation->id,
            'sender_type' => 'parent',
            'message_type' => $messageType,
            'content' => $content,
            'media_url' => $mediaUrl,
            'media_type' => $mediaType,
            'whatsapp_message_id' => $whatsappMessageId,
            'status' => 'delivered',
        ]);

        // Update conversation stats
        $conversation->increment('message_count');
        $conversation->update(['last_message_at' => now()]);

        return $message;
    }

    /**
     * Send message from admin to parent
     */
    public function sendMessageFromAdmin(
        WhatsAppConversation $conversation,
        User $admin,
        string $content,
        string $messageType = 'text',
        ?string $mediaUrl = null,
        ?string $mediaType = null,
        ?string $replyToMessageId = null
    ): WhatsAppMessage {
        $messageData = [
            'id' => Str::uuid(),
            'conversation_id' => $conversation->id,
            'sender_id' => $admin->id,
            'sender_type' => 'admin',
            'message_type' => $messageType,
            'content' => $content,
            'media_url' => $mediaUrl,
            'media_type' => $mediaType,
            'status' => 'sent',
        ];

        if ($replyToMessageId) {
            $messageData['reply_to_message_id'] = $replyToMessageId;
        }

        $message = WhatsAppMessage::create($messageData);

        // Update conversation stats
        $conversation->increment('message_count');
        $conversation->update(['last_message_at' => now()]);

        // Integrate dengan WhatsappMetaService yang sudah ada
        try {
            $metaService = app(WhatsappMetaService::class);
            $result = $metaService->sendMessage($conversation->phone_number, $content, $mediaUrl);
            \Log::info('WhatsApp message sent via Meta API', [
                'conversation_id' => $conversation->id,
                'phone' => $conversation->phone_number,
                'message_id' => $message->id,
                'result' => $result,
            ]);
            // Update message status after successful send
            $message->update(['status' => 'sent']);
        } catch (\Exception $e) {
            \Log::error('WhatsAppChatService::sendMessageFromAdmin failed: '.$e->getMessage(), [
                'conversation_id' => $conversation->id,
                'phone' => $conversation->phone_number,
                'message_id' => $message->id,
                'error' => $e->getTraceAsString(),
            ]);
            // Update message status to pending/failed
            $message->update(['status' => 'pending']);
        }

        return $message;
    }

    /**
     * Get conversation thread with messages
     */
    public function getConversationThread(WhatsAppConversation $conversation, int $perPage = 50)
    {
        return $conversation->messages()
            ->latest('created_at')
            ->paginate($perPage);
    }

    /**
     * Assign conversation to admin
     */
    public function assignToAdmin(WhatsAppConversation $conversation, User $admin): void
    {
        if (! $admin->hasRole('admin') && ! $admin->hasRole('staff')) {
            throw new \InvalidArgumentException('User is not an admin or staff member');
        }

        $conversation->update([
            'assigned_admin_id' => $admin->id,
        ]);

        Log::channel('whatsapp')->info('Conversation assigned', [
            'conversation_id' => $conversation->id,
            'assigned_to' => $admin->name,
        ]);
    }

    /**
     * Unassign conversation from admin
     */
    public function unassignFromAdmin(WhatsAppConversation $conversation): void
    {
        $conversation->update([
            'assigned_admin_id' => null,
        ]);
    }

    /**
     * Close conversation
     */
    public function closeConversation(WhatsAppConversation $conversation, ?string $notes = null): void
    {
        $conversation->update([
            'status' => 'closed',
            'notes' => $notes,
        ]);
    }

    /**
     * Reopen conversation
     */
    public function reopenConversation(WhatsAppConversation $conversation): void
    {
        $conversation->update([
            'status' => 'active',
        ]);
    }

    /**
     * Get all active conversations for admin dashboard
     */
    public function getAdminConversations(?User $admin = null)
    {
        $query = WhatsAppConversation::active()
            ->with(['student', 'assignedAdmin', 'latestMessage'])
            ->orderBy('last_message_at', 'DESC')
            ->orderBy('created_at', 'DESC');

        if ($admin) {
            // Show assigned conversations and unassigned
            $query->where(function ($q) use ($admin) {
                $q->where('assigned_admin_id', $admin->id)
                    ->orWhereNull('assigned_admin_id');
            });
        }

        return $query->paginate(20);
    }

    /**
     * Subscribe to conversation updates (for real-time chat)
     */
    public function subscribeToConversation(WhatsAppConversation $conversation, User $user): void
    {
        // Mark all unread messages as read
        $conversation->messages()
            ->where('sender_type', 'parent')
            ->where('status', '!=', 'read')
            ->update([
                'status' => 'read',
                'read_at' => now(),
            ]);

        Log::info('User subscribed to conversation', [
            'user_id' => $user->id,
            'conversation_id' => $conversation->id,
        ]);
    }

    /**
     * Get message templates by category
     */
    public function getTemplatesByCategory(string $category)
    {
        return WhatsAppMessageTemplate::active()
            ->byCategory($category)
            ->get();
    }

    /**
     * Normalize phone number format
     */
    private function normalizePhoneNumber(string $phoneNumber): string
    {
        // Remove all non-digit characters except +
        $normalized = preg_replace('/[^\d+]/', '', $phoneNumber);

        // If doesn't start with +, ensure it starts with 62 (Indonesia)
        if (! str_starts_with($normalized, '+')) {
            if (str_starts_with($normalized, '0')) {
                $normalized = '62'.substr($normalized, 1);
            } elseif (! str_starts_with($normalized, '62')) {
                $normalized = '62'.$normalized;
            }
        }

        return $normalized;
    }

    /**
     * Search conversations
     */
    public function searchConversations(string $query)
    {
        return WhatsAppConversation::where('phone_number', 'LIKE', "%{$query}%")
            ->orWhere('profile_name', 'LIKE', "%{$query}%")
            ->orWhereHas('student', function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('nisn', 'LIKE', "%{$query}%");
            })
            ->with(['student', 'assignedAdmin', 'latestMessage'])
            ->orderBy('last_message_at', 'DESC')
            ->paginate(20);
    }

    /**
     * Get statistics for dashboard
     */
    public function getDashboardStats(): array
    {
        return [
            'total_conversations' => WhatsAppConversation::active()->count(),
            'unassigned_conversations' => WhatsAppConversation::unassigned()->count(),
            'total_messages' => WhatsAppMessage::count(),
            'unread_messages' => WhatsAppMessage::unread()->fromParents()->count(),
        ];
    }
}
