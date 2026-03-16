<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;


class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@system.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('admin123'),
                'is_admin' => true,
                'role' => User::ROLE_ICT,
            ]
        );
    }
}
