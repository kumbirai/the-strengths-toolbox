<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscriber extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'source',
        'ip_address',
        'subscribed_at',
        'email_verified_at',
        'unsubscribed_at',
    ];

    protected $casts = [
        'subscribed_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    /**
     * Get the subscriber's full name
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Scope a query to only include active subscribers
     */
    public function scopeActive($query)
    {
        return $query->whereNull('unsubscribed_at');
    }

    /**
     * Scope a query to only include subscribed users
     */
    public function scopeSubscribed($query)
    {
        return $query->whereNotNull('subscribed_at')
            ->whereNull('unsubscribed_at');
    }

    /**
     * Scope a query to only include unsubscribed users
     */
    public function scopeUnsubscribed($query)
    {
        return $query->whereNotNull('unsubscribed_at');
    }

    /**
     * Get the form submissions for the subscriber
     */
    public function formSubmissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class, 'email', 'email');
    }
}
