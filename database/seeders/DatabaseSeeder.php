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
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        // Akun admin bawaan (aman dijalankan berulang).
        // Email admin dianggap sudah terverifikasi agar bisa langsung login ke dashboard.
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Administrator', 'password' => 'password123'],
        );

        $admin->forceFill([
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => $admin->email_verified_at ?? now(),
        ])->save();

        $this->call([
            ProdiSeeder::class,
            MahasiswaSeeder::class,
        ]);
    }
}
