<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotConfig extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Get configuration value
     */
    public static function get(string $key, $default = null)
    {
        $config = static::where('key', $key)->first();

        return $config ? $config->value : $default;
    }

    /**
     * Set configuration value
     */
    public static function set(string $key, $value, string $type = 'string', string $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
            ]
        );
    }

    /**
     * Get all configurations by group
     */
    public static function getByGroup(string $group): array
    {
        return static::where('group', $group)
            ->get()
            ->pluck('value', 'key')
            ->toArray();
    }
}
