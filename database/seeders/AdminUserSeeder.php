<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Only create if no users exist — safe to run multiple times
        if (User::count() === 0) {
            User::create([
                'name'              => 'Admin',
                'email'             => env('ADMIN_EMAIL', 'admin@example.com'),
                'password'          => bcrypt(env('ADMIN_PASSWORD', 'changeme')),
                'email_verified_at' => now(),
            ]);
        }
    }
}