<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company',
        'testimonial',
        'rating',
        'user_id',
        'is_featured',
        'is_published',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)
            ->orderBy('display_order');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
