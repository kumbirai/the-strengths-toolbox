<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotPrompt extends Model
{
    protected $fillable = [
        'name',
        'description',
        'template',
        'variables',
        'is_active',
        'is_default',
        'version',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Get active default prompt
     */
    public static function getDefault(): ?self
    {
        return static::where('is_default', true)
            ->where('is_active', true)
            ->latest('version')
            ->first();
    }

    /**
     * Render prompt with variables
     */
    public function render(array $variables = []): string
    {
        $template = $this->template;
        $allVariables = array_merge($this->variables ?? [], $variables);

        foreach ($allVariables as $key => $value) {
            $template = str_replace('{'.$key.'}', $value, $template);
        }

        return $template;
    }
}
