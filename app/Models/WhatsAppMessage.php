<?php

namespace App\Models;

use App\Traits\HasAuditLog;
use App\Traits\ModelAuditable;
use App\Traits\RealtimeSync;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $conversation_id
 * @property string|null $sender_id
 * @property string|null $sender_type
 * @property string|null $sender_name
 * @property string|null $content
 * @property string|null $media_url
 * @property string|null $message_type
 * @property string|null $status
 * @property Carbon|null $created_at
 */
class WhatsAppMessage extends Model
{
    use HasAuditLog, HasFactory, HasUuids, ModelAuditable, RealtimeSync;

    protected $table = 'whatsapp_messages';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'sender_type',
        'message_type',
        'content',
        'media_url',
        'media_type',
        'status',
        'whatsapp_message_id',
        'error_message',
        'retry_count',
        'read_at',
        'reply_to_message_id',
        'edited_at',
        'is_deleted',
        'reactions',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'edited_at' => 'datetime',
        'content' => 'encrypted',
        'media_url' => 'encrypted',
        'reactions' => 'json',
        'is_deleted' => 'boolean',
    ];

    /**
     * Get the conversation this message belongs to
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(WhatsAppConversation::class, 'conversation_id', 'id');
    }

    /**
     * Get the sender (admin user) if sender_type is 'admin'
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }

    /**
     * Get the message this message is replying to
     */
    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(WhatsAppMessage::class, 'reply_to_message_id', 'id');
    }

    /**
     * Get all messages replying to this message
     */
    public function replies()
    {
        return $this->hasMany(WhatsAppMessage::class, 'reply_to_message_id', 'id');
    }

    /**
     * Get sender name based on type
     */
    public function getSenderNameAttribute(): string
    {
        if ($this->sender_type === 'admin' && $this->sender) {
            return $this->sender->name;
        }

        return $this->conversation?->profile_name ?? 'Parent';
    }

    /**
     * Mark message as read
     */
    public function markAsRead(): void
    {
        if ($this->status !== 'read') {
            $this->update([
                'status' => 'read',
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Scope for unread messages
     */
    public function scopeUnread($query)
    {
        return $query->where('status', '!=', 'read');
    }

    /**
     * Scope for messages from parents
     */
    public function scopeFromParents($query)
    {
        return $query->where('sender_type', 'parent');
    }

    /**
     * Scope for messages from admins
     */
    public function scopeFromAdmins($query)
    {
        return $query->where('sender_type', 'admin');
    }

    /**
     * Get human readable status
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'sent' => 'Terkirim',
            'delivered' => 'Tersampaikan',
            'read' => 'Terbaca',
            'failed' => 'Gagal',
            default => $this->status,
        };
    }

    /**
     * Check if message is edited
     */
    public function isEdited(): bool
    {
        return $this->edited_at !== null && $this->edited_at->notEqualTo($this->created_at);
    }

    /**
     * Check if message is soft deleted
     */
    public function isSoftDeleted(): bool
    {
        return $this->is_deleted === true;
    }

    /**
     * Soft delete a message
     */
    public function softDelete(): void
    {
        $this->update(['is_deleted' => true]);
    }

    /**
     * Restore a soft deleted message
     */
    public function restore(): void
    {
        $this->update(['is_deleted' => false]);
    }

    /**
     * Add emoji reaction to message
     */
    public function addReaction(string $emoji, string $userId): void
    {
        $reactions = $this->reactions ?? [];
        if (! isset($reactions[$emoji])) {
            $reactions[$emoji] = [];
        }
        if (! in_array($userId, $reactions[$emoji])) {
            $reactions[$emoji][] = $userId;
        }
        $this->update(['reactions' => $reactions]);
    }

    /**
     * Remove emoji reaction from message
     */
    public function removeReaction(string $emoji, string $userId): void
    {
        $reactions = $this->reactions ?? [];
        if (isset($reactions[$emoji])) {
            $reactions[$emoji] = array_filter(
                $reactions[$emoji],
                fn ($id) => $id !== $userId
            );
            if (empty($reactions[$emoji])) {
                unset($reactions[$emoji]);
            }
        }
        $this->update(['reactions' => $reactions]);
    }

    /**
     * Get timestamp display with delivery status icon
     */
    public function getDeliveryIconAttribute(): string
    {
        return match ($this->status) {
            'sent' => '✓',
            'delivered' => '✓✓',
            'read' => '✓✓',
            'failed' => '✗',
            default => '',
        };
    }

    /**
     * Scope for not soft deleted messages
     */
    public function scopeNotDeleted($query)
    {
        return $query->where('is_deleted', false);
    }

    /**
     * Scope for soft deleted messages
     */
    public function scopeDeleted($query)
    {
        return $query->where('is_deleted', true);
    }
}
