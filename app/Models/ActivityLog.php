<?php

namespace App\Models;

use Closure;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Throwable;

use function Illuminate\Support\defer;

/**
 * Riwayat aktivitas pengguna.
 *
 * Entri dikumpulkan selama request lalu disimpan sekaligus (satu INSERT multi-baris)
 * SETELAH respons terkirim lewat defer(), sehingga pencatatan tidak menambah waktu
 * tunggu pengguna walau database berada jauh (Supabase Seoul).
 */
class ActivityLog extends Model
{
    use Prunable;

    public const UPDATED_AT = null;

    /** Lama penyimpanan log sebelum dibersihkan oleh `model:prune`. */
    public const RETENSI_HARI = 180;

    /**
     * Jenis aktivitas => [label, warna badge, ikon].
     *
     * @var array<string, array{0: string, 1: string, 2: string}>
     */
    public const EVENTS = [
        'created' => ['Tambah', 'success', 'plus'],
        'updated' => ['Ubah', 'primary', 'pencil'],
        'deleted' => ['Hapus', 'warning', 'trash-2'],
        'restored' => ['Pulihkan', 'success', 'rotate-ccw'],
        'force_deleted' => ['Hapus permanen', 'danger', 'trash-2'],
        'bulk' => ['Aksi massal', 'primary', 'list-checks'],
        'import' => ['Impor', 'success', 'upload'],
        'export' => ['Ekspor', 'neutral', 'download'],
        'login' => ['Login', 'neutral', 'log-in'],
        'logout' => ['Logout', 'neutral', 'log-out'],
        'account' => ['Akun', 'warning', 'shield-check'],
    ];

    /**
     * Jenis subjek yang disimpan di subject_type.
     *
     * @var array<class-string<Model>, string>
     */
    public const SUBJECTS = [
        Mahasiswa::class => 'mahasiswa',
        Prodi::class => 'prodi',
        Dosen::class => 'dosen',
        MataKuliah::class => 'mata_kuliah',
        User::class => 'pengguna',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id', 'user_name', 'event', 'subject_type', 'subject_id',
        'subject_label', 'description', 'properties', 'ip_address', 'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @var list<array<string, mixed>>
     */
    private static array $antrean = [];

    private static int $jeda = 0;

    /**
     * Catat satu aktivitas atas nama user yang sedang login (atau $pelaku).
     * Tanpa pelaku (seeder, perintah konsol) tidak ada yang dicatat.
     *
     * @param  array<string, mixed>  $properties
     */
    public static function catat(string $event, string $description, ?Model $subject = null, array $properties = [], ?User $pelaku = null, ?string $subjectLabel = null): void
    {
        $pelaku ??= Auth::user();

        if (self::$jeda > 0 || $pelaku === null) {
            return;
        }

        $entry = [
            'user_id' => $pelaku->getKey(),
            'user_name' => $pelaku->name,
            'event' => $event,
            'subject_type' => $subject ? (self::SUBJECTS[$subject::class] ?? class_basename($subject)) : null,
            'subject_id' => $subject?->getKey(),
            'subject_label' => $subjectLabel ?? ($subject ? self::labelSubjek($subject) : null),
            'description' => mb_strimwidth($description, 0, 255, '…'),
            'properties' => $properties === [] ? null : json_encode($properties, JSON_UNESCAPED_UNICODE),
            'ip_address' => Request::ip(),
            'created_at' => now(),
        ];

        $enqueue = static function () use ($entry): void {
            self::$antrean[] = $entry;
            defer(static fn () => self::simpanAntrean(), 'activity-log', always: true);
        };

        if (DB::transactionLevel() > 0) {
            DB::afterCommit($enqueue);
        } else {
            $enqueue();
        }
    }

    /**
     * Jalankan $callback tanpa mencatat aktivitas per baris (dipakai aksi massal,
     * yang mencatat satu ringkasan sendiri).
     *
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public static function tanpaCatat(Closure $callback): mixed
    {
        self::$jeda++;

        try {
            return $callback();
        } finally {
            self::$jeda--;
        }
    }

    /**
     * Simpan semua entri yang terkumpul dalam satu INSERT.
     */
    public static function simpanAntrean(): void
    {
        $baris = self::$antrean;
        self::$antrean = [];

        if ($baris === []) {
            return;
        }

        try {
            $actors = User::query()->whereKey(array_column($baris, 'user_id'))->pluck('id')->flip();

            foreach ($baris as $index => $entry) {
                if (! isset($actors[$entry['user_id']])) {
                    $baris[$index]['user_id'] = null;
                }
            }

            DB::transaction(fn () => static::query()->insert($baris));
        } catch (Throwable $e) {
            // Log aktivitas tidak boleh menggagalkan aksi pengguna yang sudah selesai.
            report($e);
        }
    }

    public static function labelSubjek(Model $subject): string
    {
        return match (true) {
            $subject instanceof Mahasiswa => "{$subject->nama} ({$subject->nim})",
            $subject instanceof Prodi => $subject->nama_prodi,
            $subject instanceof Dosen, $subject instanceof MataKuliah => $subject->nama,
            $subject instanceof User => "{$subject->name} ({$subject->email})",
            default => class_basename($subject).' #'.$subject->getKey(),
        };
    }

    /**
     * Perubahan atribut model ([kolom => [lama, baru]]) untuk event "updated".
     *
     * @param  list<string>  $abaikan
     * @return array<string, array{0: mixed, 1: mixed}>
     */
    public static function perubahan(Model $model, array $abaikan = []): array
    {
        $abaikan = [...$abaikan, 'updated_at', 'created_at', 'deleted_at', 'deleted_by', 'remember_token', 'password'];
        $hasil = [];

        foreach ($model->getChanges() as $kolom => $baru) {
            if (in_array($kolom, $abaikan, true)) {
                continue;
            }

            $lama = $model->getOriginal($kolom);
            $hasil[$kolom] = [self::nilai($lama), self::nilai($baru)];
        }

        return $hasil;
    }

    private static function nilai(mixed $nilai): mixed
    {
        return $nilai instanceof \DateTimeInterface ? $nilai->format('Y-m-d') : $nilai;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    public function jenis(): array
    {
        return self::EVENTS[$this->event] ?? [ucfirst($this->event), 'neutral', 'activity'];
    }

    /**
     * URL subjek bila datanya masih ada di aplikasi.
     */
    public function subjectUrl(): ?string
    {
        if ($this->subject_id === null || in_array($this->event, ['force_deleted'], true)) {
            return null;
        }

        return match ($this->subject_type) {
            'mahasiswa' => $this->event === 'deleted' ? null : route('mahasiswa.show', $this->subject_id),
            'prodi' => $this->event === 'deleted' ? null : route('prodi.show', $this->subject_id),
            'dosen' => route('dosen.edit', $this->subject_id),
            'mata_kuliah' => route('mata-kuliah.edit', $this->subject_id),
            default => null,
        };
    }

    /**
     * @param  array{search?: ?string, event?: ?string, user_id?: mixed, subject_type?: ?string}  $filters
     */
    #[Scope]
    protected function filter(Builder $query, array $filters): void
    {
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $operator = $query->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $pattern = '%'.addcslashes($search, '%_\\').'%';
            $query->where(fn (Builder $q) => $q->where('description', $operator, $pattern)
                ->orWhere('subject_label', $operator, $pattern)
                ->orWhere('user_name', $operator, $pattern));
        }

        if (array_key_exists($filters['event'] ?? '', self::EVENTS)) {
            $query->where('event', $filters['event']);
        }

        if (filled($filters['user_id'] ?? null)) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        if (in_array($filters['subject_type'] ?? null, self::SUBJECTS, true)) {
            $query->where('subject_type', $filters['subject_type']);
        }
    }

    /**
     * Log yang lebih tua dari masa retensi dihapus oleh `php artisan model:prune` (terjadwal harian).
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subDays(self::RETENSI_HARI));
    }
}
