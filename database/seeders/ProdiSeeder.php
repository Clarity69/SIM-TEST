<?php

namespace Database\Seeders;

use App\Models\Prodi;
use Illuminate\Database\Seeder;

class ProdiSeeder extends Seeder
{
    /**
     * Program studi S1 STITEK Bontang dengan kode PDDIKTI.
     *
     * Nama kaprodi hanya diisi dari sumber asli (repo SIM-MAHASISWA dan stitek.ac.id).
     * Yang belum diketahui dibiarkan null dan bisa dilengkapi lewat menu Program Studi.
     */
    public function run(): void
    {
        $prodis = [
            [
                'kode_prodi' => '55201',
                'singkatan' => 'TI',
                'nama_prodi' => 'Teknik Informatika',
                'kaprodi' => 'Rianindya Chandra Hardika, S.T., M.Eng',
                'kaprodi_jabatan' => 'Ketua Program Studi',
            ],
            [
                'kode_prodi' => '57201',
                'singkatan' => 'SI',
                'nama_prodi' => 'Sistem Informasi',
                'kaprodi' => 'Lapu Tombilayuk, S.Kom, M.T.',
                'kaprodi_jabatan' => 'Plt. Ketua Program Studi',
            ],
            [
                'kode_prodi' => '20201',
                'singkatan' => 'TE',
                'nama_prodi' => 'Teknik Elektro',
            ],
            [
                'kode_prodi' => '25302',
                'singkatan' => 'TL',
                'nama_prodi' => 'Teknik Lingkungan',
            ],
            [
                'kode_prodi' => '61209',
                'singkatan' => 'BD',
                'nama_prodi' => 'Bisnis Digital',
            ],
        ];

        foreach ($prodis as $prodi) {
            Prodi::updateOrCreate(['kode_prodi' => $prodi['kode_prodi']], $prodi);
        }
    }
}
