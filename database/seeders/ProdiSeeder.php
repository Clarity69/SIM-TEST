<?php

namespace Database\Seeders;

use App\Models\Prodi;
use Illuminate\Database\Seeder;

class ProdiSeeder extends Seeder
{
    /**
     * Program studi S1 STITEK Bontang dengan kode PDDIKTI dan kaprodi.
     *
     * Data kaprodi: TI dari repo SIM-MAHASISWA / stitek.ac.id; prodi lain diberikan
     * langsung oleh tim (5 November 2026). Nomor induk disimpan tanpa spasi;
     * 10 digit = NIDN, 16 digit = NUPTK (lihat Prodi::labelNomorInduk()).
     */
    public function run(): void
    {
        $prodis = [
            [
                'kode_prodi' => '55201',
                'singkatan' => 'TI',
                'nama_prodi' => 'Teknik Informatika',
                'kaprodi' => 'Rianindya Chandra Hardika, S.T., M.Eng',
                'kaprodi_nidn' => null,
            ],
            [
                'kode_prodi' => '57201',
                'singkatan' => 'SI',
                'nama_prodi' => 'Sistem Informasi',
                'kaprodi' => 'Irfani Zuhrufillah, S.Kom., M.Kom.',
                'kaprodi_nidn' => '0627058802',
            ],
            [
                'kode_prodi' => '20201',
                'singkatan' => 'TE',
                'nama_prodi' => 'Teknik Elektro',
                'kaprodi' => 'Akbar, S.Pd., M.Pd.',
                'kaprodi_nidn' => null,
            ],
            [
                'kode_prodi' => '25302',
                'singkatan' => 'TL',
                'nama_prodi' => 'Teknik Lingkungan',
                'kaprodi' => 'Richo Dwi Permadi, S.Si., M.Eng.',
                'kaprodi_nidn' => '8842773674130472',
            ],
            [
                'kode_prodi' => '26201',
                'singkatan' => 'TIN',
                'nama_prodi' => 'Teknik Industri',
                'kaprodi' => 'Inas Alfiyatul Umniyah, S.Si., M.Si.',
                'kaprodi_nidn' => null,
            ],
            [
                'kode_prodi' => '61209',
                'singkatan' => 'BD',
                'nama_prodi' => 'Bisnis Digital',
                'kaprodi' => 'Abd. Rahim, S.ST., M.M.',
                'kaprodi_nidn' => '4247773674130263',
            ],
        ];

        foreach ($prodis as $prodi) {
            Prodi::updateOrCreate(
                ['kode_prodi' => $prodi['kode_prodi']],
                $prodi + ['kaprodi_jabatan' => 'Ketua Program Studi'],
            );
        }
    }
}
