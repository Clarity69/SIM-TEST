<?php

namespace App\Services;

use App\Models\Mahasiswa;
use App\Models\Prodi;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Membuat laporan PDF mahasiswa (daftar dan detail). Dipakai oleh controller
 * (unduh/lihat) dan oleh MahasiswaPdfMail (lampiran email) agar isinya sama.
 */
class MahasiswaPdf
{
    public const MAKS_BARIS = 2000;

    /** @param array<string, mixed> $filters */
    public function validateSize(array $filters): void
    {
        $count = Mahasiswa::query()->filter($filters)->limit(self::MAKS_BARIS + 1)->pluck('id')->count();
        $this->ensureMaxRows($count);
    }

    private function ensureMaxRows(int $count): void
    {
        if ($count > self::MAKS_BARIS) {
            throw ValidationException::withMessages(['pdf' => 'PDF maksimal '.self::MAKS_BARIS.' mahasiswa. Persempit filter atau gunakan ekspor Excel/CSV.']);
        }
    }

    /**
     * PDF daftar mahasiswa sesuai filter aktif (search, prodi_id, jenis_kelamin, sort, direction).
     *
     * @param  array<string, mixed>  $filters
     */
    public function daftar(array $filters): DomPdf
    {
        $mahasiswas = Mahasiswa::query()
            ->select('id', 'nim', 'nama', 'jenis_kelamin', 'tanggal_lahir', 'telepon', 'email', 'prodi_id', 'angkatan', 'status', 'created_at')
            ->with('prodi:id,kode_prodi,singkatan,nama_prodi')
            ->filter($filters)
            ->limit(self::MAKS_BARIS + 1)
            ->get();

        $this->ensureMaxRows($mahasiswas->count());

        $prodi = filled($filters['prodi_id'] ?? null) ? Prodi::find((int) $filters['prodi_id']) : null;

        return Pdf::loadView('mahasiswa.pdf', [
            'mahasiswas' => $mahasiswas,
            'namaProdi' => $prodi?->nama_prodi ?? 'Semua Program Studi',
            'filter' => [
                'search' => $filters['search'] ?? null,
                'jenis_kelamin' => in_array($filters['jenis_kelamin'] ?? null, Mahasiswa::JENIS_KELAMIN, true) ? $filters['jenis_kelamin'] : null,
                'status' => array_key_exists($filters['status'] ?? '', Mahasiswa::STATUS) ? $filters['status'] : null,
                'angkatan' => filled($filters['angkatan'] ?? null) ? (int) $filters['angkatan'] : null,
            ],
            'penandatangan' => $this->penandatangan($prodi),
        ])->setPaper('a4', 'landscape');
    }

    /**
     * PDF biodata satu mahasiswa, ditandatangani kaprodi dari prodi mahasiswa tersebut.
     */
    public function detail(Mahasiswa $mahasiswa): DomPdf
    {
        $mahasiswa->loadMissing('prodi');

        return Pdf::loadView('mahasiswa.pdf-detail', [
            'mahasiswa' => $mahasiswa,
            'penandatangan' => $this->penandatangan($mahasiswa->prodi),
        ])->setPaper('a4', 'portrait');
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function namaFileDaftar(array $filters): string
    {
        $prodi = filled($filters['prodi_id'] ?? null) ? Prodi::find((int) $filters['prodi_id']) : null;

        return 'data-mahasiswa'.($prodi ? '-'.Str::slug($prodi->label()) : '').'-'.now()->format('Ymd').'.pdf';
    }

    public function namaFileDetail(Mahasiswa $mahasiswa): string
    {
        return 'biodata-'.$mahasiswa->nim.'-'.Str::slug($mahasiswa->nama).'.pdf';
    }

    /**
     * Penanda tangan: kaprodi bila laporan untuk satu prodi, Ketua STITEK bila semua prodi.
     * Nama kosong dibiarkan null (ditulis tangan), tidak diganti nama dummy.
     *
     * @return array{nama: ?string, jabatan: string, nidn: ?string, label_nomor: string}
     */
    public function penandatangan(?Prodi $prodi): array
    {
        if ($prodi === null) {
            return [
                'nama' => config('stitek.ketua.nama'),
                'jabatan' => config('stitek.ketua.jabatan'),
                'nidn' => config('stitek.ketua.nidn'),
                'label_nomor' => 'NIDN',
            ];
        }

        return [
            'nama' => $prodi->kaprodi,
            'jabatan' => ($prodi->kaprodi_jabatan ?: 'Ketua Program Studi').' '.$prodi->nama_prodi,
            'nidn' => $prodi->kaprodi_nidn,
            'label_nomor' => $prodi->labelNomorInduk(),
        ];
    }
}
