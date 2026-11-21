<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ExecutiveUsersSeeder extends Seeder
{
    public function run(): void
    {
        // CEO - Hadi Ong
        User::updateOrCreate(
            ['email' => 'ceo@hrd.local'],
            [
                'name' => 'Hadi Ong',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_hrd' => true,
                'role' => User::ROLE_CEO,
            ]
        );

        // CFO - Sri Rahayu
        User::updateOrCreate(
            ['email' => 'cfo@hrd.local'],
            [
                'name' => 'Sri Rahayu',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_hrd' => true,
                'role' => User::ROLE_CFO,
            ]
        );

        // Pastikan HRD default juga ada
        User::updateOrCreate(
            ['email' => 'admin@hrd.local'],
            [
                'name' => 'Rina Kartika',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_hrd' => true,
                'role' => User::ROLE_HRD,
            ]
        );
    }
}
