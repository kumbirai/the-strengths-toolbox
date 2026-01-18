<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        Page::create([
            'title' => 'Home',
            'slug' => 'home',
            'content' => '<h1>Welcome to The Strengths Toolbox</h1><p>Build Strong Teams. Unlock Strong Profits.</p>',
            'excerpt' => 'Welcome to The Strengths Toolbox',
            'meta_title' => 'The Strengths Toolbox - Build Strong Teams',
            'meta_description' => 'Transform your team with strengths-based development programs.',
            'is_published' => true,
            'published_at' => now(),
        ]);

        Page::create([
            'title' => 'About Us',
            'slug' => 'about-us',
            'content' => '<h1>About The Strengths Toolbox</h1><p>We help organizations build stronger teams through proven strengths-based methodologies.</p>',
            'excerpt' => 'Learn about our mission and values',
            'meta_title' => 'About Us - The Strengths Toolbox',
            'meta_description' => 'Learn about The Strengths Toolbox and our approach to team development.',
            'is_published' => true,
            'published_at' => now(),
        ]);
    }
}
