<?php

namespace App\Models;

use App\Services\DashboardStats;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Prodi extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kode_prodi',
        'singkatan',
        'nama_prodi',
        'fakultas',
        'kaprodi',
        'kaprodi_jabatan',
        'kaprodi_nidn',
    ];

    /**
     * Statistik dashboard memuat jumlah dan label prodi, jadi ikut dibersihkan.
     */
    protected static function booted(): void
    {
        $forget = function () {
            DashboardStats::forget();
            Cache::forget(self::OPSI_CACHE_KEY);
            Mahasiswa::bumpVersiDaftar(); // nama/singkatan prodi tampil di daftar mahasiswa
        };

        static::saved($forget);
        static::deleted($forget);
    }

    public const OPSI_CACHE_KEY = 'prodi.opsi';

    /**
     * Daftar prodi untuk dropdown (id, kode, singkatan, nama), di-cache sebagai array
     * karena jarang berubah; dibersihkan otomatis saat prodi disimpan/dihapus.
     *
     * @return Collection<int, Prodi>
     */
    public static function opsi(): Collection
    {
        $rows = Cache::rememberForever(self::OPSI_CACHE_KEY, fn () => static::query()
            ->select('id', 'kode_prodi', 'singkatan', 'nama_prodi')
            ->orderBy('nama_prodi')
            ->toBase()
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all());

        return static::hydrate($rows);
    }

    /**
     * Relasi ke model Mahasiswa (One-to-Many).
     * Satu Program Studi memiliki banyak Mahasiswa.
     */
    public function mahasiswas(): HasMany
    {
        return $this->hasMany(Mahasiswa::class);
    }

    /**
     * Label pendek untuk grafik/badge: singkatan bila ada, selain itu kode prodi.
     */
    public function label(): string
    {
        return $this->singkatan ?: $this->kode_prodi;
    }

    /**
     * Label nomor induk kaprodi: NIDN (10 digit) atau NUPTK (16 digit).
     */
    public function labelNomorInduk(): string
    {
        return strlen((string) $this->kaprodi_nidn) === 16 ? 'NUPTK' : 'NIDN';
    }
}
