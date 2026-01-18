<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        return [
            'filename' => 'test_'.$this->faker->uuid().'.jpg',
            'original_filename' => 'test.jpg',
            'path' => 'media/test_'.$this->faker->uuid().'.jpg',
            'mime_type' => 'image/jpeg',
            'size' => $this->faker->numberBetween(1000, 1000000),
            'disk' => 'public',
            'uploaded_by' => User::factory(),
            'alt_text' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'width' => 800,
            'height' => 600,
            'thumbnail_path' => null,
        ];
    }
}
