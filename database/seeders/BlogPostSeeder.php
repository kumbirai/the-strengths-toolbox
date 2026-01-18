<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('role', 'author')->orWhere('role', 'admin')->first();

        if (! $author) {
            $author = User::first();
        }

        if (! $author) {
            $this->command->warn('No users found. Skipping blog post creation.');

            return;
        }

        $post = BlogPost::firstOrCreate(
            ['slug' => 'power-of-strengths-based-development'],
            [
                'title' => 'The Power of Strengths-Based Development',
                'excerpt' => 'Discover how focusing on strengths can transform your team\'s performance.',
                'content' => '<p>Strengths-based development is a powerful approach to team building...</p>',
                'author_id' => $author->id,
                'is_published' => true,
                'published_at' => now()->subDays(5),
            ]
        );

        $category = Category::where('slug', 'team-development')->first();
        $tag = Tag::where('slug', 'strengths')->first();

        if ($category) {
            $post->categories()->syncWithoutDetaching([$category->id]);
        }

        if ($tag) {
            $post->tags()->syncWithoutDetaching([$tag->id]);
        }
    }
}
