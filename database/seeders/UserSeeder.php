<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create specific user
        User::factory()->create([
            'name' => 'Youssef',
            'email' => 'yousse@gmail.com',
            'password' => Hash::make('123123123'),
        ]);

        // Create 10 dummy users
        for ($i = 1; $i <= 10; $i++) {
            User::factory()->create([
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'password' => Hash::make('123123123'),
            ]);
        }
    }
}
