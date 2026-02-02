<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Team Development', 'slug' => 'team-development'],
            ['name' => 'Leadership', 'slug' => 'leadership'],
            ['name' => 'Sales Courses', 'slug' => 'sales-courses'],
            ['name' => 'Case Studies', 'slug' => 'case-studies'],
            ['name' => 'Business Coaching', 'slug' => 'business-coaching'],
            ['name' => 'Coaching', 'slug' => 'coaching'],
            ['name' => 'Personal Coaching', 'slug' => 'personal-coaching'],
            ['name' => 'Strengths-Based Coaching', 'slug' => 'strengths-based-coaching'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
