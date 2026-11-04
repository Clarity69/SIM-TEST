<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Akun admin bawaan (aman dijalankan berulang).
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Administrator', 'password' => 'password123'],
        );

        $this->call([
            ProdiSeeder::class,
            MahasiswaSeeder::class,
        ]);
    }
}
