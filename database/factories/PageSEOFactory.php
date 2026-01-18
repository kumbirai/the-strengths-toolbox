<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\PageSEO;
use Illuminate\Database\Eloquent\Factories\Factory;

class PageSEOFactory extends Factory
{
    protected $model = PageSEO::class;

    public function definition(): array
    {
        return [
            'page_id' => Page::factory(),
            'og_title' => $this->faker->sentence(),
            'og_description' => $this->faker->paragraph(),
            'og_image' => $this->faker->imageUrl(),
            'twitter_card' => 'summary_large_image',
            'twitter_title' => $this->faker->sentence(),
            'twitter_description' => $this->faker->paragraph(),
            'twitter_image' => $this->faker->imageUrl(),
            'canonical_url' => $this->faker->url(),
            'schema_markup' => [],
        ];
    }
}
