<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email    = env('ADMIN_EMAIL', 'admin@mikrotik.local');
        $password = env('ADMIN_PASSWORD', 'Admin@123456');

        // Create or update — safe to run multiple times
        User::updateOrCreate(
            ['email' => $email],
            [
                'name'              => 'Admin',
                'email'             => $email,
                'password'          => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info("Admin user ready: {$email}");
    }
}