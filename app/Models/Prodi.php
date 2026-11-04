<?php

namespace App\Models;

use App\Services\DashboardStats;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        static::saved(fn () => DashboardStats::forget());
        static::deleted(fn () => DashboardStats::forget());
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
