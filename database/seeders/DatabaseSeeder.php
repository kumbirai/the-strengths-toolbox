<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            PageSeeder::class,
            ExistingContentPagesSeeder::class, // Phase 4: Existing content page structure
            ContentMigrationSeeder::class, // Phase 5: Populate actual content from existing website
            CategorySeeder::class,
            TagSeeder::class,
            BlogPostMigrationSeeder::class,
            MediaSeeder::class, // Restore images from optimized directory and assign to blog posts
            FormSeeder::class,
            TestimonialSeeder::class,
            PopulateImageStorageSeeder::class, // Sales Courses + blog images (download to storage, assign blog featured_image)
        ]);
    }
}
