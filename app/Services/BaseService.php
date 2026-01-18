<?php

namespace App\Services;

abstract class BaseService
{
    /**
     * Handle service errors
     */
    protected function handleError(\Exception $e, ?string $message = null): void
    {
        \Log::error($message ?? $e->getMessage(), [
            'exception' => $e,
            'service' => static::class,
        ]);

        throw new \RuntimeException($message ?? 'An error occurred in the service layer', 0, $e);
    }

    /**
     * Validate required data
     */
    protected function validateRequired(array $data, array $required): void
    {
        $missing = array_diff($required, array_keys($data));

        if (! empty($missing)) {
            throw new \InvalidArgumentException(
                'Missing required fields: '.implode(', ', $missing)
            );
        }
    }
}
