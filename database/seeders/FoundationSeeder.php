<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FoundationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding foundation data...');
        $this->command->newLine();

        $this->seedUsers();
        $this->seedForms();

        $this->command->newLine();
        $this->command->info('✓ Foundation data seeded successfully!');
    }

    protected function seedUsers(): void
    {
        $this->command->info('Seeding users...');

        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@thestrengthstoolbox.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Editor User',
                'email' => 'editor@thestrengthstoolbox.com',
                'password' => Hash::make('password'),
                'role' => 'editor',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Author User',
                'email' => 'author@thestrengthstoolbox.com',
                'password' => Hash::make('password'),
                'role' => 'author',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
            $this->command->line("  ✓ User: {$userData['name']} ({$userData['email']})");
        }
    }

    protected function seedForms(): void
    {
        $this->command->info('Seeding forms...');

        Form::firstOrCreate(
            ['slug' => 'contact'],
            [
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
            ]
        );

        $this->command->line('  ✓ Contact form created');
    }
}
