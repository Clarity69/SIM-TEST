<?php

namespace App\Services;

use App\Models\Mahasiswa;
use App\Models\Prodi;
use Illuminate\Support\Facades\Cache;

use function Illuminate\Support\defer;

/**
 * Statistik dashboard: dihitung dengan sedikit query agregat lalu di-cache.
 * Cache dibersihkan oleh model event Mahasiswa/Prodi (lihat booted() di kedua model).
 *
 * Yang di-cache hanya array skalar: Laravel 13 menolak unserialize objek dari cache
 * (config cache.serializable_classes = false) untuk mencegah gadget chain attack.
 */
class DashboardStats
{
    public const CACHE_KEY = 'dashboard.stats.v2';

    public const TTL_SECONDS = 300;

    /** Jumlah terakhir per kategori + jumlah sebelum aksi terakhir (permanen, bukan TTL). */
    public const TREN_KEY = 'dashboard.tren';

    /**
     * @return array{
     *     total: int, laki: int, perempuan: int, totalProdi: int,
     *     sebelum: array{total: int, laki: int, perempuan: int, prodi: int},
     *     terakhir: array{total: ?string, laki: ?string, perempuan: ?string, prodi: ?string},
     *     perProdi: list<array{id: int, label: string, nama_prodi: string, kaprodi: ?string, mahasiswas_count: int, laki_count: int, perempuan_count: int}>,
     *     terbaru: list<array{id: int, nim: string, nama: string, jenis_kelamin: string, prodi: string, created_at: string}>,
     *     perAngkatan: list<array{angkatan: int, status: string, jumlah: int}>
     * }
     */
    public function get(): array
    {
        return Cache::remember(self::CACHE_KEY, self::TTL_SECONDS, fn () => $this->compute());
    }

    /**
     * Dipanggil setiap data mahasiswa/prodi berubah: buang cache, lalu hitung ulang setelah
     * respons terkirim (sekali per request) agar tren tercatat per aksi dan dashboard tetap hangat.
     */
    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
        defer(static fn () => app(self::class)->get(), 'dashboard-stats-refresh', always: true);
    }

    /**
     * Bandingkan jumlah sekarang dengan jumlah terakhir yang diketahui. Kategori yang berubah
     * menyimpan jumlah lamanya sebagai "sebelum" beserta waktu perubahannya.
     * Tanpa catatan sebelumnya (pertama kali), tren dianggap 0.
     *
     * @param  array<string, int>  $sekarang
     * @return array<string, array{jumlah: int, sebelum: int, waktu: ?string}>
     */
    private function catatTren(array $sekarang): array
    {
        $catatan = Cache::get(self::TREN_KEY, []);
        $baru = [];

        foreach ($sekarang as $kategori => $jumlah) {
            $lama = $catatan[$kategori] ?? null;

            $baru[$kategori] = match (true) {
                $lama === null => ['jumlah' => $jumlah, 'sebelum' => $jumlah, 'waktu' => null],
                $lama['jumlah'] !== $jumlah => ['jumlah' => $jumlah, 'sebelum' => $lama['jumlah'], 'waktu' => now()->toIso8601String()],
                default => $lama,
            };
        }

        if ($baru !== $catatan) {
            Cache::forever(self::TREN_KEY, $baru);
        }

        return $baru;
    }

    /**
     * Tren dihitung per AKSI, bukan per periode atau tanggal data: jumlah sekarang dibanding
     * jumlah tepat sebelum aksi terakhir yang mengubah kategori tersebut. Tambah 1 data -> +1
     * (naik), hapus 1 data -> -1 (turun); aksi massal/impor/seeder dihitung sebagai satu aksi.
     *
     * Jumlah terakhir yang diketahui disimpan permanen di cache (TREN_KEY). Setiap kali data
     * berubah, forget() menjadwalkan hitung ulang setelah respons terkirim, sehingga
     * perbandingan selalu terhadap kondisi sebelum aksi itu.
     *
     * Query 1: jumlah total/laki/perempuan (satu agregat).
     * Query 2: jumlah mahasiswa per prodi (withCount), sekaligus total prodi.
     * Query 3 + 4: 5 mahasiswa terbaru beserta prodinya (eager load).
     * Query 5: jumlah per angkatan × status (GROUP BY) untuk grafik dengan filter rentang angkatan.
     */
    private function compute(): array
    {
        $agregat = Mahasiswa::query()->toBase()
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw("COUNT(*) FILTER (WHERE jenis_kelamin = 'Laki-laki') AS laki")
            ->selectRaw("COUNT(*) FILTER (WHERE jenis_kelamin = 'Perempuan') AS perempuan")
            ->first();

        $perProdi = Prodi::query()
            ->select('id', 'kode_prodi', 'singkatan', 'nama_prodi', 'kaprodi')
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

        $perAngkatan = Mahasiswa::query()
            ->toBase()
            ->whereNotNull('angkatan')
            ->select('angkatan', 'status')
            ->selectRaw('COUNT(*) AS jumlah')
            ->groupBy('angkatan', 'status')
            ->orderBy('angkatan')
            ->get();

        $tren = $this->catatTren([
            'total' => (int) $agregat->total,
            'laki' => (int) $agregat->laki,
            'perempuan' => (int) $agregat->perempuan,
            'prodi' => $perProdi->count(),
        ]);

        return [
            'total' => (int) $agregat->total,
            'laki' => (int) $agregat->laki,
            'perempuan' => (int) $agregat->perempuan,
            'totalProdi' => $perProdi->count(),
            'sebelum' => array_map(fn (array $kategori) => $kategori['sebelum'], $tren),
            'terakhir' => array_map(fn (array $kategori) => $kategori['waktu'], $tren),
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
            'perAngkatan' => $perAngkatan->map(fn ($row) => [
                'angkatan' => (int) $row->angkatan,
                'status' => $row->status,
                'jumlah' => (int) $row->jumlah,
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
