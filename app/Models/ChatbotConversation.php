<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'context',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatbotMessage::class, 'conversation_id');
    }

    /**
     * Scope: Get conversations by user
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Get conversations by session
     */
    public function scopeBySession(Builder $query, string $sessionId): Builder
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope: Get active conversations
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereHas('messages', function ($q) {
            $q->where('created_at', '>=', now()->subDays(30));
        });
    }

    /**
     * Scope: Get old conversations
     */
    public function scopeOld(Builder $query, int $days = 90): Builder
    {
        return $query->whereDoesntHave('messages', function ($q) use ($days) {
            $q->where('created_at', '>=', now()->subDays($days));
        });
    }

    /**
     * Get total messages count
     */
    public function getTotalMessagesAttribute(): int
    {
        return $this->messages()->count();
    }

    /**
     * Get total tokens used
     */
    public function getTotalTokensAttribute(): int
    {
        return $this->messages()->sum('tokens_used') ?? 0;
    }

    /**
     * Get last message
     */
    public function getLastMessageAttribute(): ?ChatbotMessage
    {
        return $this->messages()->latest()->first();
    }

    /**
     * Check if conversation is active
     */
    public function isActive(int $days = 30): bool
    {
        $lastMessage = $this->lastMessage;
        if (! $lastMessage) {
            return false;
        }

        return $lastMessage->created_at->isAfter(now()->subDays($days));
    }
}
