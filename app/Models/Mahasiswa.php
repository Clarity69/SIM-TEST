<?php

namespace App\Models;

use App\Services\DashboardStats;
use App\Services\FotoMahasiswa;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Mahasiswa extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Kolom yang boleh dipakai untuk sorting dari query string (whitelist).
     *
     * @var list<string>
     */
    public const SORTABLE = ['nim', 'nama', 'jenis_kelamin', 'prodi', 'angkatan', 'status', 'created_at'];

    /**
     * Pilihan jenis kelamin (sama dengan enum di migration).
     *
     * @var list<string>
     */
    public const JENIS_KELAMIN = ['Laki-laki', 'Perempuan'];

    /**
     * Status akademik => warna badge.
     *
     * @var array<string, string>
     */
    public const STATUS = [
        'Aktif' => 'success',
        'Cuti' => 'warning',
        'Lulus' => 'primary',
        'Keluar' => 'neutral',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mahasiswas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nim',
        'nama',
        'jenis_kelamin',
        'tanggal_lahir',
        'alamat',
        'telepon',
        'email',
        'prodi_id',
        'angkatan',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_lahir' => 'date',
        'angkatan' => 'integer',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'Aktif',
    ];

    /**
     * Bersihkan cache statistik dashboard setiap kali data mahasiswa berubah.
     */
    protected static function booted(): void
    {
        $berubah = function (): void {
            $invalidate = static function (): void {
                DashboardStats::forget();
                self::bumpVersiDaftar();
            };

            if (DB::transactionLevel() > 0) {
                DB::afterCommit($invalidate);
            } else {
                $invalidate();
            }
        };

        static::saved($berubah);
        static::deleted($berubah);
        static::restored($berubah);
        static::forceDeleted($berubah);

        // Foto ikut dihapus hanya saat data dihapus permanen (bukan saat masuk Sampah).
        static::forceDeleted(fn (Mahasiswa $mahasiswa) => app(FotoMahasiswa::class)->hapus($mahasiswa->foto));

        // Log aktivitas (disimpan setelah respons terkirim, lihat ActivityLog::catat()).
        static::created(fn (Mahasiswa $m) => ActivityLog::catat('created', "Menambahkan mahasiswa {$m->nama}", $m));
        static::updated(function (Mahasiswa $m) {
            $perubahan = ActivityLog::perubahan($m);

            // Pemulihan dari Sampah juga memicu "updated" (deleted_at berubah); dicatat sebagai "restored".
            if ($perubahan !== []) {
                ActivityLog::catat('updated', "Mengubah data mahasiswa {$m->nama}", $m, ['perubahan' => $perubahan]);
            }
        });
        static::softDeleted(fn (Mahasiswa $m) => ActivityLog::catat('deleted', "Memindahkan {$m->nama} ke Sampah", $m));
        static::restored(fn (Mahasiswa $m) => ActivityLog::catat('restored', "Memulihkan {$m->nama} dari Sampah", $m));
        static::forceDeleted(fn (Mahasiswa $m) => ActivityLog::catat('force_deleted', "Menghapus permanen {$m->nama}", $m));
    }

    /**
     * URL foto relatif terhadap host yang sedang dibuka (tidak bergantung APP_URL).
     */
    public function fotoUrl(): ?string
    {
        return $this->foto ? asset('storage/'.$this->foto) : null;
    }

    public function penghapus(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public const VERSI_DAFTAR_KEY = 'mahasiswa.daftar.versi';

    /**
     * Versi data daftar mahasiswa untuk kunci cache halaman index/pencarian.
     */
    public static function versiDaftar(): string
    {
        return Cache::rememberForever(self::VERSI_DAFTAR_KEY, fn () => (string) now()->getTimestampMs());
    }

    /**
     * Daftar angkatan yang ada (untuk filter), di-cache per versi data.
     *
     * @return list<int>
     */
    public static function opsiAngkatan(): array
    {
        return Cache::remember('mahasiswa.angkatan.'.self::versiDaftar(), 3600, fn () => static::query()
            ->whereNotNull('angkatan')
            ->distinct()
            ->orderByDesc('angkatan')
            ->pluck('angkatan')
            ->map(fn ($tahun) => (int) $tahun)
            ->all());
    }

    /**
     * Menandai semua cache daftar mahasiswa usang (dipanggil saat mahasiswa/prodi berubah).
     */
    public static function bumpVersiDaftar(): void
    {
        Cache::forever(self::VERSI_DAFTAR_KEY, (string) now()->getTimestampMs().random_int(100, 999));
    }

    /**
     * Relasi ke model Prodi (Many-to-One).
     */
    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }

    /**
     * Pencarian, filter, dan sorting yang dipakai bersama oleh halaman index dan cetak PDF.
     *
     * @param  array{search?: ?string, prodi_id?: mixed, jenis_kelamin?: ?string, status?: ?string, angkatan?: mixed, sort?: ?string, direction?: ?string}  $filters
     */
    #[Scope]
    protected function filter(Builder $query, array $filters): void
    {
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            // ILIKE di PostgreSQL agar tidak case-sensitive dan bisa memakai index GIN trigram.
            $operator = $query->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $pattern = '%'.addcslashes($search, '%_\\').'%';

            $query->where(function (Builder $q) use ($operator, $pattern) {
                $q->where($q->qualifyColumn('nim'), $operator, $pattern)
                    ->orWhere($q->qualifyColumn('nama'), $operator, $pattern);
            });
        }

        if (filled($filters['prodi_id'] ?? null)) {
            $query->where($query->qualifyColumn('prodi_id'), (int) $filters['prodi_id']);
        }

        if (in_array($filters['jenis_kelamin'] ?? null, self::JENIS_KELAMIN, true)) {
            $query->where($query->qualifyColumn('jenis_kelamin'), $filters['jenis_kelamin']);
        }

        if (array_key_exists($filters['status'] ?? '', self::STATUS)) {
            $query->where($query->qualifyColumn('status'), $filters['status']);
        }

        if (filled($filters['angkatan'] ?? null)) {
            $query->where($query->qualifyColumn('angkatan'), (int) $filters['angkatan']);
        }

        $sort = in_array($filters['sort'] ?? null, self::SORTABLE, true) ? $filters['sort'] : 'created_at';
        $direction = ($filters['direction'] ?? null) === 'asc' ? 'asc' : 'desc';

        if ($sort === 'prodi') {
            $query->orderBy(
                Prodi::select('nama_prodi')->whereColumn('prodis.id', 'mahasiswas.prodi_id'),
                $direction,
            );
        } else {
            $query->orderBy($query->qualifyColumn($sort), $direction);
        }

        // Urutan stabil untuk pagination saat nilai kolom sort sama.
        $query->orderBy($query->qualifyColumn('id'), $direction);
    }
}
