<?php

namespace App\Services;

use App\Models\Mahasiswa;
use App\Models\Prodi;
use Illuminate\Support\Carbon;
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
     *     terakhir: array{total: ?string, laki: ?string, perempuan: ?string, prodi: ?string},
     *     perProdi: list<array{id: int, label: string, nama_prodi: string, kaprodi: ?string, mahasiswas_count: int, laki_count: int, perempuan_count: int}>,
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
     * Tren dihitung per perubahan data, bukan per periode:
     * jumlah sekarang dibanding jumlah tepat sebelum perubahan terakhir (tambah/hapus)
     * di kategori tersebut. Tambah 1 data -> naik, hapus 1 data -> turun.
     *
     * Query 1: waktu perubahan terakhir per kategori (MAX created_at / deleted_at).
     * Query 2: jumlah sekarang dan jumlah sebelum perubahan terakhir, satu agregat.
     * Query 3: jumlah mahasiswa per prodi (withCount), sekaligus total prodi.
     * Query 4 + 5: 5 mahasiswa terbaru beserta prodinya (eager load).
     */
    private function compute(): array
    {
        $kategori = [
            'total' => '1 = 1',
            'laki' => "jenis_kelamin = 'Laki-laki'",
            'perempuan' => "jenis_kelamin = 'Perempuan'",
        ];

        $waktu = Mahasiswa::withTrashed()->toBase();
        foreach ($kategori as $kunci => $syarat) {
            $waktu->selectRaw("MAX(created_at) FILTER (WHERE {$syarat}) AS {$kunci}_dibuat")
                ->selectRaw("MAX(deleted_at) FILTER (WHERE {$syarat}) AS {$kunci}_dihapus");
        }
        $waktu = $waktu->first();

        $agregat = Mahasiswa::withTrashed()->toBase();
        $terakhir = [];
        foreach ($kategori as $kunci => $syarat) {
            $terakhir[$kunci] = collect([$waktu->{"{$kunci}_dibuat"}, $waktu->{"{$kunci}_dihapus"}])->filter()->max();

            $agregat->selectRaw("COUNT(*) FILTER (WHERE {$syarat} AND deleted_at IS NULL) AS {$kunci}");

            // Populasi tepat sebelum perubahan terakhir: sudah dibuat sebelum T dan belum dihapus saat T.
            $terakhir[$kunci] === null
                ? $agregat->selectRaw("0 AS {$kunci}_sebelum")
                : $agregat->selectRaw(
                    "COUNT(*) FILTER (WHERE {$syarat} AND created_at < ? AND (deleted_at IS NULL OR deleted_at >= ?)) AS {$kunci}_sebelum",
                    [$terakhir[$kunci], $terakhir[$kunci]],
                );
        }
        $agregat = $agregat->first();

        $perProdi = Prodi::query()
            ->select('id', 'kode_prodi', 'singkatan', 'nama_prodi', 'kaprodi', 'created_at')
            ->withCount([
                'mahasiswas',
                'mahasiswas as laki_count' => fn ($q) => $q->where('jenis_kelamin', 'Laki-laki'),
                'mahasiswas as perempuan_count' => fn ($q) => $q->where('jenis_kelamin', 'Perempuan'),
            ])
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
                // Prodi dihapus permanen; yang bisa dilacak hanya penambahan terakhir.
                'prodi' => $perProdi->filter(fn (Prodi $prodi) => $prodi->created_at < $perProdi->max('created_at'))->count(),
            ],
            'terakhir' => [
                'total' => $terakhir['total'] ? Carbon::parse($terakhir['total'])->toIso8601String() : null,
                'laki' => $terakhir['laki'] ? Carbon::parse($terakhir['laki'])->toIso8601String() : null,
                'perempuan' => $terakhir['perempuan'] ? Carbon::parse($terakhir['perempuan'])->toIso8601String() : null,
                'prodi' => $perProdi->max('created_at')?->toIso8601String(),
            ],
            'perProdi' => $perProdi->map(fn (Prodi $prodi) => [
                'id' => $prodi->id,
                'label' => $prodi->label(),
                'nama_prodi' => $prodi->nama_prodi,
                'kaprodi' => $prodi->kaprodi,
                'mahasiswas_count' => (int) $prodi->mahasiswas_count,
                'laki_count' => (int) $prodi->laki_count,
                'perempuan_count' => (int) $prodi->perempuan_count,
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
     * Persentase perubahan dibanding jumlah sebelum perubahan terakhir.
     * Bila sebelumnya belum ada data, data pertama dianggap pertambahan (+100%).
     */
    public static function perubahan(int $sekarang, int $sebelumnya): float
    {
        if ($sebelumnya === 0) {
            return $sekarang > 0 ? 100.0 : 0.0;
        }

        return round(($sekarang - $sebelumnya) / $sebelumnya * 100, 1);
    }
}
