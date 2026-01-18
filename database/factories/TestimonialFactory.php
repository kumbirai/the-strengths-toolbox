<?php

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Testimonial>
 */
class TestimonialFactory extends Factory
{
    protected $model = Testimonial::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'company' => $this->faker->company(),
            'testimonial' => $this->faker->paragraphs(2, true),
            'rating' => $this->faker->numberBetween(4, 5),
            'is_featured' => $this->faker->boolean(30),
            'display_order' => $this->faker->numberBetween(1, 100),
        ];
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
