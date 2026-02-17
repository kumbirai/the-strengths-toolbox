<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'author_id',
        'published_at',
        'is_published',
        'views_count',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'views_count' => 'integer',
        ];
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'blog_post_category');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'blog_post_tag');
    }

    public function comments()
    {
        return $this->hasMany(BlogComment::class)->whereNull('parent_id')->orderBy('created_at', 'desc');
    }

    public function allComments()
    {
        return $this->hasMany(BlogComment::class)->orderBy('created_at', 'desc');
    }

    public function approvedComments()
    {
        return $this->comments()->approved()->notSpam();
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where('published_at', '<=', now());
    }

    public function incrementViews()
    {
        $this->increment('views_count');
    }

    /**
     * Get the featured image URL
     * Handles both storage paths and public paths
     */
    public function getFeaturedImageUrlAttribute(): ?string
    {
        if (empty($this->featured_image)) {
            return null;
        }

        // Remove any double prefix first (images/images/blog/... -> images/blog/...)
        $path = $this->featured_image;
        if (str_starts_with($path, 'images/images/')) {
            $path = substr($path, 7); // Remove 'images/'
        }

        // If path starts with images/blog/, it's in public folder
        if (str_starts_with($path, 'images/blog/')) {
            return asset($path);
        }

        // Otherwise, assume it's in storage
        return asset('storage/' . $path);
    }
}
