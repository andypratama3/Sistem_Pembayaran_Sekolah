<?php

namespace App\Http\Controllers\Dashboard;

use App\Events\WhatsAppMessageSent;
use App\Http\Controllers\ResourceController;
use App\Http\Requests\Dashboard\WhatsAppChatMessageRequest;
use App\Models\SchoolWorkHours;
use App\Models\User;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppMessageTemplate;
use App\Services\WhatsAppChatService;
use Illuminate\Http\Request;

class WhatsAppChatController extends ResourceController
{
    protected static string $permissionResource = 'conversations';

    protected WhatsAppChatService $chatService;

    public function __construct(WhatsAppChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', WhatsAppConversation::class);

        $query = $request->input('q');
        $conversations = $query ? $this->chatService->searchConversations($query) : $this->chatService->getAdminConversations(auth()->user());

        return view('dashboard.whatsapp-chat.index', [
            'conversations' => $conversations,
            'stats' => $this->chatService->getDashboardStats(),
            'templates' => WhatsAppMessageTemplate::active()->get(),
            'query' => $query,
        ]);
    }

    public function show(WhatsAppConversation $conversationRecord, Request $request)
    {
        $this->authorize('view', $conversationRecord);

        $this->chatService->subscribeToConversation($conversationRecord, auth()->user());
        if (! $conversationRecord->assigned_admin_id) {
            $this->chatService->assignToAdmin($conversationRecord, auth()->user());
        }

        $messages = $this->chatService->getConversationThread($conversationRecord);

        // Check work hours
        $isWorkingHours = SchoolWorkHours::isWorkingNow();
        $todayWorkHours = SchoolWorkHours::getTodayWorkHours();

        if ($request->ajax()) {
            return view('dashboard.whatsapp-chat.partials.chat-pane', [
                'conversation' => $conversationRecord,
                'messages' => $messages,
                'templates' => WhatsAppMessageTemplate::active()->get(),
                'isWorkingHours' => $isWorkingHours,
                'todayWorkHours' => $todayWorkHours,
            ]);
        }

        return view('dashboard.whatsapp-chat.index', [
            'conversation' => $conversationRecord,
            'messages' => $messages,
            'conversations' => $this->chatService->getAdminConversations(auth()->user()),
            'stats' => $this->chatService->getDashboardStats(),
            'templates' => WhatsAppMessageTemplate::active()->get(),
            'isWorkingHours' => $isWorkingHours,
            'todayWorkHours' => $todayWorkHours,
        ]);
    }

    public function sendMessage(WhatsAppChatMessageRequest $request, WhatsAppConversation $conversationRecord)
    {
        $this->authorize('update', $conversationRecord);

        // ✅ Check if it's working hours OR if admin is manually overriding
        $isWorkingHours = SchoolWorkHours::isWorkingNow();
        $allowOutsideHours = $request->boolean('force_send_outside_hours', false);

        if (! $isWorkingHours && ! $allowOutsideHours) {
            return response()->json([
                'error' => 'Tidak dapat mengirim pesan di luar jam kerja. Lanjutkan? (Klik Yes untuk mengirim paksa)',
                'work_hours_restricted' => true,
            ], 403);
        }

        $validated = $request->validated();

        $message = $this->chatService->sendMessageFromAdmin(
            $conversationRecord,
            auth()->user(),
            $validated['content'] ?? null,
            $validated['message_type'] ?? 'text',
            $validated['reply_to_message_id'] ?? null,
            $request->file('media_file')
        );

        broadcast(new WhatsAppMessageSent($message, $conversationRecord))->toOthers();

        return $this->success($message->load('sender', 'replyTo'), 'Pesan terkirim');
    }

    public function assign(Request $request, WhatsAppConversation $conversationRecord)
    {
        $this->authorize('update', $conversationRecord);

        $admin = User::findOrFail($request->admin_id);
        $this->chatService->assignToAdmin($conversationRecord, $admin);

        return $this->success(null, 'Berhasil dialihkan');
    }

    public function close(WhatsAppConversation $conversationRecord, Request $request)
    {
        $this->authorize('update', $conversationRecord);

        $this->chatService->closeConversation($conversationRecord, $request->notes);

        return $this->success(null, 'Percakapan ditutup');
    }

    public function reopen(WhatsAppConversation $conversationRecord)
    {
        $this->authorize('update', $conversationRecord);

        $conversationRecord->update(['status' => 'open', 'closed_at' => null]);

        return $this->success(null, 'Percakapan dibuka kembali');
    }

    public function sendTemplate(Request $request, WhatsAppConversation $conversationRecord)
    {
        $this->authorize('update', $conversationRecord);

        $validated = $request->validate([
            'template_id' => 'required|exists:whatsapp_message_templates,id',
        ]);

        $template = WhatsAppMessageTemplate::findOrFail($validated['template_id']);
        $message = $this->chatService->sendMessageFromAdmin(
            $conversationRecord,
            auth()->user(),
            $template->content,
            'template',
            null,
            null
        );

        broadcast(new WhatsAppMessageSent($message, $conversationRecord))->toOthers();

        return $this->success($message->load('sender'), 'Template terkirim');
    }

    public function getMessages(WhatsAppConversation $conversationRecord, Request $request)
    {
        $this->authorize('view', $conversationRecord);

        $perPage = $request->integer('per_page', 20);
        $messages = $conversationRecord->messages()
            ->where('is_deleted', false)
            ->latest()
            ->paginate($perPage);

        return $this->success($messages);
    }

    public function getTemplates(Request $request)
    {
        $this->authorize('viewAny', WhatsAppMessageTemplate::class);

        $category = $request->input('category');
        $query = WhatsAppMessageTemplate::active();

        if ($category) {
            $query->where('category', $category);
        }

        return $this->success($query->get());
    }

    /**
     * PRIORITY 2: Edit message
     */
    public function editMessage(Request $request, WhatsAppMessage $messageRecord)
    {
        $this->authorize('update', $messageRecord->conversation);

        if ($messageRecord->sender_id !== auth()->id() && ! in_array($messageRecord->sender_type, ['admin', 'superadmin'])) {
            return $this->error('Unauthorized', null, 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:4096',
        ]);

        $messageRecord->update([
            'content' => $validated['content'],
            'edited_at' => now(),
        ]);

        return $this->success($messageRecord, 'Pesan diperbarui');
    }

    /**
     * PRIORITY 2: Delete message (soft delete)
     */
    public function deleteMessage(Request $request, WhatsAppMessage $messageRecord)
    {
        $this->authorize('update', $messageRecord->conversation);

        if ($messageRecord->sender_id !== auth()->id() && ! in_array($messageRecord->sender_type, ['admin', 'superadmin'])) {
            return $this->error('Unauthorized', null, 403);
        }

        $messageRecord->softDelete();

        return $this->success(null, 'Pesan dihapus');
    }

    /**
     * PRIORITY 2: Add reaction to message
     */
    public function addReaction(Request $request, WhatsAppMessage $messageRecord)
    {
        $this->authorize('update', $messageRecord->conversation);

        $validated = $request->validate([
            'emoji' => 'required|string|max:10',
        ]);

        $messageRecord->addReaction($validated['emoji'], auth()->id());

        return $this->success($messageRecord->reactions, 'Reaksi ditambahkan');
    }

    /**
     * PRIORITY 3: Search messages in conversation
     */
    public function searchMessages(Request $request, WhatsAppConversation $conversationRecord)
    {
        $this->authorize('view', $conversationRecord);

        $query = $request->input('q');

        if (! $query) {
            return $this->error('Query tidak boleh kosong', null, 422);
        }

        $likePattern = '%'.str_replace(['%', '_'], ['\%', '\_'], $query).'%';
        $messages = $conversationRecord->messages()
            ->where('is_deleted', false)
            ->whereRaw('MATCH (content) AGAINST (? IN BOOLEAN MODE)', [$query])
            ->orWhere('content', 'like', $likePattern)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return $this->success([
            'query' => $query,
            'count' => $messages->count(),
            'messages' => $messages,
        ]);
    }

    /**
     * PRIORITY 4: Mark messages as read
     */
    public function markAsRead(Request $request, WhatsAppMessage $messageRecord)
    {
        $this->authorize('update', $messageRecord->conversation);

        $messageRecord->markAsRead();

        return $this->success(null, 'Pesan ditandai sebagai terbaca');
    }

    /**
     * Mark all messages in a conversation as read
     */
    public function markConversationAsRead(WhatsAppConversation $conversationRecord)
    {
        $this->authorize('update', $conversationRecord);

        $this->chatService->subscribeToConversation($conversationRecord, auth()->user());

        return $this->success(null, 'Percakapan ditandai sebagai terbaca');
    }
}
