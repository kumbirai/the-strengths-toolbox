<?php

namespace App\Helpers;

class SEOValidator
{
    /**
     * Validate title tag length
     *
     * @return array ['valid' => bool, 'length' => int, 'message' => string]
     */
    public static function validateTitle(string $title): array
    {
        $length = mb_strlen($title);

        if ($length < 30) {
            return [
                'valid' => false,
                'length' => $length,
                'message' => 'Title is too short (minimum 30 characters recommended)',
            ];
        }

        if ($length > 60) {
            return [
                'valid' => false,
                'length' => $length,
                'message' => 'Title is too long (maximum 60 characters recommended)',
            ];
        }

        return [
            'valid' => true,
            'length' => $length,
            'message' => 'Title length is optimal',
        ];
    }

    /**
     * Validate meta description length
     *
     * @return array ['valid' => bool, 'length' => int, 'message' => string]
     */
    public static function validateDescription(string $description): array
    {
        $length = mb_strlen($description);

        if ($length < 120) {
            return [
                'valid' => false,
                'length' => $length,
                'message' => 'Description is too short (minimum 120 characters recommended)',
            ];
        }

        if ($length > 160) {
            return [
                'valid' => false,
                'length' => $length,
                'message' => 'Description is too long (maximum 160 characters recommended)',
            ];
        }

        return [
            'valid' => true,
            'length' => $length,
            'message' => 'Description length is optimal',
        ];
    }

    /**
     * Escape meta tag content
     */
    public static function escape(string $content): string
    {
        return htmlspecialchars($content, ENT_QUOTES, 'UTF-8', false);
    }
}
