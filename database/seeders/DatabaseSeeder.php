<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'admin@synrcypro.local'],
            [
                'name' => 'Administrator',
                'password' => 'password',
                'role' => 'Administrator',
                'email_verified_at' => now(),
            ]
        );
    }
}
