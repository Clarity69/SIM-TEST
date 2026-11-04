<?php

namespace App\Services;

use App\Models\Mahasiswa;
use App\Models\Prodi;
use Illuminate\Support\Facades\Cache;

/**
 * Statistik dashboard: dihitung dengan sedikit query agregat lalu di-cache.
 * Cache dibersihkan oleh model event Mahasiswa/Prodi (lihat booted() di kedua model).
 *
 * Yang di-cache hanya array skalar: Laravel 13 menolak unserialize objek dari cache
 * (config cache.serializable_classes = false) untuk mencegah gadget chain attack.
 */
class DashboardStats
{
    public const CACHE_KEY = 'dashboard.stats';

    public const TTL_SECONDS = 300;

    /**
     * @return array{
     *     total: int, laki: int, perempuan: int, totalProdi: int,
     *     sebelum: array{total: int, laki: int, perempuan: int, prodi: int},
     *     perProdi: list<array{id: int, label: string, nama_prodi: string, mahasiswas_count: int}>,
     *     terbaru: list<array{id: int, nim: string, nama: string, jenis_kelamin: string, prodi: string, created_at: string}>
     * }
     */
    public function get(): array
    {
        return Cache::remember(self::CACHE_KEY, self::TTL_SECONDS, fn () => $this->compute());
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Query 1: semua angka mahasiswa (sekarang dan 30 hari lalu) dalam satu agregat.
     * Query 2: jumlah mahasiswa per prodi (withCount), sekaligus total prodi.
     * Query 3 + 4: 5 mahasiswa terbaru beserta prodinya (eager load).
     */
    private function compute(): array
    {
        $batas = now()->subDays(30);

        $agregat = Mahasiswa::query()
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw("COUNT(*) FILTER (WHERE jenis_kelamin = 'Laki-laki') AS laki")
            ->selectRaw("COUNT(*) FILTER (WHERE jenis_kelamin = 'Perempuan') AS perempuan")
            ->selectRaw('COUNT(*) FILTER (WHERE created_at <= ?) AS total_sebelum', [$batas])
            ->selectRaw("COUNT(*) FILTER (WHERE created_at <= ? AND jenis_kelamin = 'Laki-laki') AS laki_sebelum", [$batas])
            ->selectRaw("COUNT(*) FILTER (WHERE created_at <= ? AND jenis_kelamin = 'Perempuan') AS perempuan_sebelum", [$batas])
            ->toBase()
            ->first();

        $perProdi = Prodi::query()
            ->select('id', 'kode_prodi', 'singkatan', 'nama_prodi', 'created_at')
            ->withCount('mahasiswas')
            ->orderByDesc('mahasiswas_count')
            ->orderBy('nama_prodi')
            ->get();

        $terbaru = Mahasiswa::query()
            ->select('id', 'nim', 'nama', 'jenis_kelamin', 'prodi_id', 'created_at')
            ->with('prodi:id,kode_prodi,singkatan,nama_prodi')
            ->latest()
            ->limit(5)
            ->get();

        return [
            'total' => (int) $agregat->total,
            'laki' => (int) $agregat->laki,
            'perempuan' => (int) $agregat->perempuan,
            'totalProdi' => $perProdi->count(),
            'sebelum' => [
                'total' => (int) $agregat->total_sebelum,
                'laki' => (int) $agregat->laki_sebelum,
                'perempuan' => (int) $agregat->perempuan_sebelum,
                'prodi' => $perProdi->filter(fn (Prodi $prodi) => $prodi->created_at <= $batas)->count(),
            ],
            'perProdi' => $perProdi->map(fn (Prodi $prodi) => [
                'id' => $prodi->id,
                'label' => $prodi->label(),
                'nama_prodi' => $prodi->nama_prodi,
                'mahasiswas_count' => (int) $prodi->mahasiswas_count,
            ])->all(),
            'terbaru' => $terbaru->map(fn (Mahasiswa $mahasiswa) => [
                'id' => $mahasiswa->id,
                'nim' => $mahasiswa->nim,
                'nama' => $mahasiswa->nama,
                'jenis_kelamin' => $mahasiswa->jenis_kelamin,
                'prodi' => $mahasiswa->prodi->nama_prodi,
                'created_at' => $mahasiswa->created_at->toIso8601String(),
            ])->all(),
        ];
    }

    /**
     * Persentase perubahan dibanding 30 hari lalu.
     * Bila 30 hari lalu belum ada data, semua data dianggap pertambahan (+100%).
     */
    public static function perubahan(int $sekarang, int $sebelumnya): float
    {
        if ($sebelumnya === 0) {
            return $sekarang > 0 ? 100.0 : 0.0;
        }

        return round(($sekarang - $sebelumnya) / $sebelumnya * 100, 1);
    }
}
