<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@thestrengthstoolbox.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Editor User',
            'email' => 'editor@thestrengthstoolbox.com',
            'password' => Hash::make('password'),
            'role' => 'editor',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Author User',
            'email' => 'author@thestrengthstoolbox.com',
            'password' => Hash::make('password'),
            'role' => 'author',
            'email_verified_at' => now(),
        ]);
    }
}
