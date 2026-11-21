<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ExecutiveUsersSeeder::class);
        $this->call(MajestyCompanySeeder::class);
        $this->call(BranchSeeder::class);
        $this->call(PsychotestBankSeeder::class);
        $this->call(UpdateJobOpeningsTypeSeeder::class);
    }
}
