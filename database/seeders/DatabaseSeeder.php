<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            FoundationSeeder::class,      // Users, Forms
            TaxonomySeeder::class,        // Categories, Tags
            ContentSeeder::class,          // All pages
            BlogSeeder::class,             // Blog posts (needs Users, Categories, Tags)
            TestimonialSeeder::class,     // Testimonials
            MediaSeeder::class,            // Images (needs Users, BlogPosts)
        ]);
    }
}
