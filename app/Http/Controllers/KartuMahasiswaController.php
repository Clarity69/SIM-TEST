<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Mahasiswa;
use App\Services\KartuMahasiswa;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Kartu mahasiswa ber-QR dan halaman verifikasi publik yang dibuka dari QR tersebut.
 */
class KartuMahasiswaController extends Controller
{
    public function show(Mahasiswa $mahasiswa, KartuMahasiswa $kartu): Response
    {
        ActivityLog::catat('export', "Mencetak kartu mahasiswa {$mahasiswa->nama}", $mahasiswa);
        $daftar = (new Mahasiswa)->newCollection([$mahasiswa]);

        return $kartu->pdf($daftar)->stream($kartu->namaFile($daftar));
    }

    /**
     * Dibuka dari QR kartu (signed URL, tanpa login). Hanya data kartu yang ditampilkan.
     */
    public function verifikasi(int $mahasiswa): View
    {
        $data = Mahasiswa::withTrashed()
            ->select('id', 'nim', 'nama', 'prodi_id', 'angkatan', 'status', 'foto', 'deleted_at')
            ->with('prodi:id,nama_prodi')
            ->find($mahasiswa);

        return view('mahasiswa.verifikasi', ['mahasiswa' => $data]);
    }
}
