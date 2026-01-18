<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotMessage extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'conversation_id',
        'role',
        'message',
        'tokens_used',
    ];

    protected function casts(): array
    {
        return [
            'tokens_used' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function conversation()
    {
        return $this->belongsTo(ChatbotConversation::class, 'conversation_id');
    }

    /**
     * Scope: Get messages by role
     */
    public function scopeByRole(Builder $query, string $role): Builder
    {
        return $query->where('role', $role);
    }

    /**
     * Scope: Get recent messages
     */
    public function scopeRecent(Builder $query, int $limit = 10): Builder
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Scope: Get messages in date range
     */
    public function scopeInDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get message length
     */
    public function getLengthAttribute(): int
    {
        return strlen($this->message);
    }

    /**
     * Check if message is from user
     */
    public function isUserMessage(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Check if message is from assistant
     */
    public function isAssistantMessage(): bool
    {
        return $this->role === 'assistant';
    }
}
