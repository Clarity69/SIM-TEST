<?php

namespace App\Models;

use App\Services\AcademicStats;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dosen extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['nidn', 'nama', 'email', 'prodi_id', 'aktif'];

    /** @var array<string, mixed> */
    protected $attributes = ['aktif' => true];

    protected static function booted(): void
    {
        static::saved(fn () => AcademicStats::forget());
        static::deleted(fn () => AcademicStats::forget());
        static::created(fn (Dosen $item) => ActivityLog::catat('created', 'Menambahkan dosen '.$item->nama, $item));
        static::updated(function (Dosen $item): void {
            if (($changes = ActivityLog::perubahan($item)) !== []) {
                ActivityLog::catat('updated', 'Mengubah dosen '.$item->nama, $item, ['perubahan' => $changes]);
            }
        });
        static::deleted(fn (Dosen $item) => ActivityLog::catat('force_deleted', 'Menghapus dosen '.$item->nama, $item));
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }
}
