<?php

namespace Database\Factories;

use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormSubmissionFactory extends Factory
{
    protected $model = FormSubmission::class;

    public function definition(): array
    {
        return [
            'form_id' => Form::factory(),
            'user_id' => null,
            'data' => [
                'name' => $this->faker->name(),
                'email' => $this->faker->email(),
                'message' => $this->faker->paragraph(),
            ],
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'is_read' => false,
        ];
    }
}
