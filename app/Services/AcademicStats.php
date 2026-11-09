<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\MataKuliah;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class AcademicStats
{
    public const CACHE_KEY = 'academic.stats.v1';

    /** @return array{ready: bool, totalDosenAktif: ?int, totalMataKuliah: ?int} */
    public function get(): array
    {
        return Cache::remember(self::CACHE_KEY, 300, function (): array {
            $ready = Schema::hasTable('dosens') && Schema::hasTable('mata_kuliahs');

            return [
                'ready' => $ready,
                'totalDosenAktif' => $ready ? Dosen::query()->where('aktif', true)->count() : null,
                'totalMataKuliah' => $ready ? MataKuliah::query()->count() : null,
            ];
        });
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
