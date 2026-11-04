<?php

namespace App\Exports;

use App\Models\Prodi;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Template impor: sheet "mahasiswa" (header + contoh) dan sheet "petunjuk"
 * (daftar prodi yang dikenali beserta nilai yang valid).
 */
class MahasiswaTemplateExport implements Export, WithMultipleSheets
{
    /**
     * @return list<SheetArrayExport>
     */
    public function sheets(): array
    {
        $contoh = ['202512001', 'Siti Rahmawati', 'Perempuan', '2006-03-12', 'TI', 2025, 'Aktif', '081234567890', 'siti@example.com', 'Jl. Merdeka 1, Bontang'];

        $petunjuk = [['kolom', 'keterangan']];
        foreach ([
            'nim' => 'wajib, unik, maks. 30 karakter (format sel teks)',
            'nama' => 'wajib',
            'jenis_kelamin' => 'Laki-laki / Perempuan (boleh L / P)',
            'tanggal_lahir' => 'opsional, format YYYY-MM-DD atau tanggal Excel',
            'prodi' => 'wajib: singkatan, kode PDDIKTI, atau nama prodi (lihat daftar di bawah)',
            'angkatan' => 'opsional, tahun masuk; kosong = 4 digit awal NIM',
            'status' => 'opsional: Aktif / Cuti / Lulus / Keluar (kosong = Aktif)',
            'telepon' => 'opsional',
            'email' => 'opsional',
            'alamat' => 'opsional',
        ] as $kolom => $keterangan) {
            $petunjuk[] = [$kolom, $keterangan];
        }
        $petunjuk[] = ['', ''];
        $petunjuk[] = ['prodi (singkatan)', 'kode PDDIKTI · nama'];
        foreach (Prodi::opsi() as $prodi) {
            $petunjuk[] = [$prodi->singkatan ?: '-', $prodi->kode_prodi.' · '.$prodi->nama_prodi];
        }

        return [
            new SheetArrayExport('mahasiswa', [MahasiswaExport::KOLOM, $contoh], textColumns: ['A', 'H']),
            new SheetArrayExport('petunjuk', $petunjuk),
        ];
    }
}
