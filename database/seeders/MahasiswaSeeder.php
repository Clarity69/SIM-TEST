<?php

namespace Database\Seeders;

use App\Models\Mahasiswa;
use App\Models\Prodi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class MahasiswaSeeder extends Seeder
{
    /**
     * Jumlah mahasiswa per angkatan dan peluang status (dalam persen) per angkatan.
     * Angkatan lama lebih banyak yang Lulus/Keluar; angkatan baru hampir semuanya Aktif.
     *
     * @var array<int, array{jumlah: int, status: array<string, int>}>
     */
    private const ANGKATAN = [
        2022 => ['jumlah' => 25, 'status' => ['Aktif' => 40, 'Lulus' => 40, 'Cuti' => 8, 'Keluar' => 12]],
        2023 => ['jumlah' => 25, 'status' => ['Aktif' => 80, 'Lulus' => 0, 'Cuti' => 10, 'Keluar' => 10]],
        2024 => ['jumlah' => 100, 'status' => ['Aktif' => 100, 'Lulus' => 0, 'Cuti' => 0, 'Keluar' => 0]],
        2025 => ['jumlah' => 25, 'status' => ['Aktif' => 92, 'Lulus' => 0, 'Cuti' => 4, 'Keluar' => 4]],
        2026 => ['jumlah' => 25, 'status' => ['Aktif' => 100, 'Lulus' => 0, 'Cuti' => 0, 'Keluar' => 0]],
    ];

    /**
     * Mahasiswa dummy angkatan 2022–2026 dengan NIM {angkatan}120xx, dibagi rata ke semua prodi.
     * Angkatan yang sudah punya data (termasuk di Sampah) dilewati seluruhnya, sehingga
     * seeder aman dijalankan ulang dan tidak memunculkan lagi data yang sengaja dihapus.
     */
    public function run(): void
    {
        $prodiIds = Prodi::orderBy('kode_prodi')->pluck('id');

        if ($prodiIds->isEmpty()) {
            $this->call(ProdiSeeder::class);
            $prodiIds = Prodi::orderBy('kode_prodi')->pluck('id');
        }

        foreach (self::ANGKATAN as $angkatan => ['jumlah' => $jumlah, 'status' => $peluang]) {
            if (Mahasiswa::withTrashed()->where('angkatan', $angkatan)->exists()) {
                continue;
            }

            for ($i = 0; $i < $jumlah; $i++) {
                $nim = sprintf('%d120%02d', $angkatan, $i);

                // Data diinput saat masa penerimaan (Agustus–September tahun angkatan), maksimal hari ini.
                $createdAt = Carbon::create($angkatan, 8, 1)->addDays(random_int(0, 60))->addMinutes(random_int(0, 1440));
                $createdAt = $createdAt->isFuture() ? now()->subDays(random_int(0, 30)) : $createdAt;

                Mahasiswa::factory()->create([
                    'nim' => $nim,
                    'angkatan' => $angkatan,
                    'status' => $this->acakStatus($peluang),
                    'tanggal_lahir' => fake()->dateTimeBetween(($angkatan - 20).'-01-01', ($angkatan - 18).'-08-31')->format('Y-m-d'),
                    'prodi_id' => $prodiIds[$i % $prodiIds->count()],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }
    }

    /**
     * @param  array<string, int>  $peluang
     */
    private function acakStatus(array $peluang): string
    {
        $angka = random_int(1, 100);

        foreach ($peluang as $status => $persen) {
            if (($angka -= $persen) <= 0) {
                return $status;
            }
        }

        return 'Aktif';
    }
}
