<?php

namespace Database\Seeders;

use App\Models\Form;
use Illuminate\Database\Seeder;

class FormSeeder extends Seeder
{
    public function run(): void
    {
        Form::create([
            'name' => 'Contact Form',
            'slug' => 'contact',
            'fields' => [
                ['name' => 'name', 'type' => 'text', 'label' => 'Name', 'required' => true],
                ['name' => 'email', 'type' => 'email', 'label' => 'Email', 'required' => true],
                ['name' => 'message', 'type' => 'textarea', 'label' => 'Message', 'required' => true],
            ],
            'email_to' => 'info@thestrengthstoolbox.com',
            'success_message' => 'Thank you for your message. We will get back to you soon.',
            'is_active' => true,
        ]);
    }
}
