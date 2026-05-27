<?php

namespace App\Models;

use App\Traits\HasAuditLog;
use App\Traits\ModelAuditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string|null $phone_number
 * @property int|null $assigned_admin_id
 * @property Carbon|null $updated_at
 * @property Student|null $student
 * @method static firstOrCreate(array $attributes, array $values = [])
 */
class WhatsAppConversation extends Model
{
    use HasAuditLog, HasFactory, HasUuids, ModelAuditable;

    protected $table = 'whatsapp_conversations';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'phone_number',
        'profile_name',
        'student_id',
        'assigned_admin_id',
        'status',
        'notes',
        'message_count',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'notes' => 'encrypted',
    ];

    /**
     * Get all messages in this conversation
     */
    public function messages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class, 'conversation_id', 'id');
    }

    /**
     * Get the student associated with this conversation
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    /**
     * Get the assigned admin user
     */
    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_admin_id', 'id');
    }

    /**
     * Get the latest message in this conversation
     */
    public function latestMessage()
    {
        return $this->hasOne(WhatsAppMessage::class, 'conversation_id', 'id')
            ->latest('created_at');
    }

    /**
     * Get unread message count
     */
    public function getUnreadCountAttribute(): int
    {
        return $this->messages()
            ->where('sender_type', 'parent')
            ->where('status', '!=', 'read')
            ->count();
    }

    /**
     * Scope for active conversations
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for conversations assigned to admin
     */
    public function scopeAssignedToAdmin($query, $adminId)
    {
        return $query->where('assigned_admin_id', $adminId);
    }

    /**
     * Scope for conversations needing assignment
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_admin_id')->where('status', 'active');
    }
}
