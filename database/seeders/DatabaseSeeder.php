<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@hrd.local'],
            [
                'name' => 'Rina Kartika',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $this->call(MajestyCompanySeeder::class);
        $this->call(BranchSeeder::class);
    }
}
