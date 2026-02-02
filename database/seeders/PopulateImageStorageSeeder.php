<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

/**
 * Ensures storage link exists and populates Sales Courses + blog images
 * so they display correctly after migrate:fresh --seed.
 */
class PopulateImageStorageSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Ensuring storage link and populating images...');

        // Ensure public/storage symlink exists
        $link = public_path('storage');
        if (! File::exists($link)) {
            Artisan::call('storage:link');
            $this->command->line('  ✓ Storage link created');
        }

        // Download Sales Courses images (referenced in page content as /storage/sales-courses/...)
        $exitCode = Artisan::call('content:download-sales-courses-images');
        if ($exitCode === 0) {
            $this->command->line('  ✓ Sales Courses images populated');
        } else {
            $this->command->warn('  ⊘ Sales Courses image download had issues (check network)');
        }

        // Download TSA blog images and assign to posts (featured_image)
        $exitCode = Artisan::call('blog:download-tsa-images');
        if ($exitCode === 0) {
            $this->command->line('  ✓ Blog featured images populated');
        } else {
            $this->command->warn('  ⊘ Blog image download had issues (check network or image-mapping.json)');
        }

        $this->command->newLine();
    }
}
