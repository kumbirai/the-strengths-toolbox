<?php

namespace App\Services;

use App\Models\Subscriber;
use Illuminate\Support\Facades\Log;

class SubscriberService
{
    /**
     * Create a new subscriber or update existing one
     */
    public function createOrUpdate(array $data): Subscriber
    {
        $email = $data['email'];

        $subscriber = Subscriber::where('email', $email)->first();

        if ($subscriber) {
            // Update existing subscriber
            $subscriber->update([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'source' => $data['source'] ?? 'ebook-signup',
                'ip_address' => $data['ip_address'] ?? request()->ip(),
                // Don't update subscribed_at if already set
                // Only update unsubscribed_at if they're resubscribing
                'unsubscribed_at' => null,
            ]);

            Log::info('Subscriber updated', [
                'email' => $email,
                'subscriber_id' => $subscriber->id,
            ]);
        } else {
            // Create new subscriber
            $subscriber = Subscriber::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $email,
                'source' => $data['source'] ?? 'ebook-signup',
                'ip_address' => $data['ip_address'] ?? request()->ip(),
                'subscribed_at' => now(),
            ]);

            Log::info('Subscriber created', [
                'email' => $email,
                'subscriber_id' => $subscriber->id,
            ]);
        }

        return $subscriber;
    }

    /**
     * Find a subscriber by email
     */
    public function findByEmail(string $email): ?Subscriber
    {
        return Subscriber::where('email', $email)->first();
    }

    /**
     * Mark a subscriber as unsubscribed
     */
    public function markAsUnsubscribed(string $email): bool
    {
        $subscriber = $this->findByEmail($email);

        if (! $subscriber) {
            return false;
        }

        $subscriber->update([
            'unsubscribed_at' => now(),
        ]);

        Log::info('Subscriber unsubscribed', [
            'email' => $email,
            'subscriber_id' => $subscriber->id,
        ]);

        return true;
    }
}
