<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RecaptchaVerifier
{
    protected string $secretKey;

    protected float $minScore;

    protected bool $enabled;

    public function __construct()
    {
        $this->enabled = config('recaptcha.enabled', true)
            && (bool) config('recaptcha.secret_key');
        $this->secretKey = config('recaptcha.secret_key', '');
        $this->minScore = (float) config('recaptcha.min_score', 0.5);
    }

    /**
     * Verify a reCAPTCHA v3 token with Google.
     * Returns true when disabled or when verification succeeds and score >= min_score.
     */
    public function verify(string $token, string $action = 'submit'): bool
    {
        if (! $this->enabled || $token === '') {
            return true;
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $this->secretKey,
            'response' => $token,
            'remoteip' => request()->ip(),
        ]);

        if (! $response->successful()) {
            return false;
        }

        $body = $response->json();
        if (! ($body['success'] ?? false)) {
            return false;
        }

        $score = (float) ($body['score'] ?? 0);
        $actionMatch = ($body['action'] ?? '') === $action;

        return $actionMatch && $score >= $this->minScore;
    }
}
