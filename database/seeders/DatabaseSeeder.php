<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (! User::where('email', 'admin@lioracity.local')->exists()) {
            User::create([
                'fullname' => 'Liora City Admin',
                'username' => 'admin',
                'email' => 'admin@lioracity.local',
                'password' => 'password',
                'phone' => '0000000000',
                'status' => 'active',
                'type' => '5',
            ]);
        }
    }
}
