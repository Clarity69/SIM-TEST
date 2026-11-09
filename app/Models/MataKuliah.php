<?php

namespace App\Models;

use App\Services\AcademicStats;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MataKuliah extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['kode', 'nama', 'sks', 'semester', 'prodi_id', 'aktif'];

    /** @var array<string, mixed> */
    protected $attributes = ['aktif' => true];

    protected static function booted(): void
    {
        static::saved(fn () => AcademicStats::forget());
        static::deleted(fn () => AcademicStats::forget());
        static::created(fn (MataKuliah $item) => ActivityLog::catat('created', 'Menambahkan mata kuliah '.$item->nama, $item));
        static::updated(function (MataKuliah $item): void {
            if (($changes = ActivityLog::perubahan($item)) !== []) {
                ActivityLog::catat('updated', 'Mengubah mata kuliah '.$item->nama, $item, ['perubahan' => $changes]);
            }
        });
        static::deleted(fn (MataKuliah $item) => ActivityLog::catat('force_deleted', 'Menghapus mata kuliah '.$item->nama, $item));
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['aktif' => 'boolean', 'sks' => 'integer', 'semester' => 'integer'];
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }
}
