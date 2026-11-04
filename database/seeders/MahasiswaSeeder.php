<?php

namespace Database\Seeders;

use App\Models\Mahasiswa;
use App\Models\Prodi;
use Illuminate\Database\Seeder;

class MahasiswaSeeder extends Seeder
{
    /**
     * 100 mahasiswa dummy (NIM 202412000–202412099) dibagi rata ke semua prodi,
     * dengan tanggal input tersebar 90 hari terakhir agar statistik tren terisi.
     */
    public function run(): void
    {
        $prodiIds = Prodi::orderBy('kode_prodi')->pluck('id');

        if ($prodiIds->isEmpty()) {
            $this->call(ProdiSeeder::class);
            $prodiIds = Prodi::orderBy('kode_prodi')->pluck('id');
        }

        for ($i = 0; $i < 100; $i++) {
            $nim = sprintf('2024120%02d', $i);
            $createdAt = now()->subDays(random_int(0, 90))->subMinutes(random_int(0, 1440));

            if (Mahasiswa::where('nim', $nim)->exists()) {
                continue;
            }

            Mahasiswa::factory()->create([
                'nim' => $nim,
                'prodi_id' => $prodiIds[$i % $prodiIds->count()],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }
}
