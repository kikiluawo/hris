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
                'is_hrd' => true,
            ]
        );

        $this->call(MajestyCompanySeeder::class);
        $this->call(BranchSeeder::class);
        $this->call(PsychotestBankSeeder::class);
    }
}
