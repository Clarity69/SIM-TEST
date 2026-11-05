<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\Prodi;
use Database\Seeders\MahasiswaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MahasiswaSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeds_angkatan_2022_to_2026_with_matching_nim(): void
    {
        Prodi::factory()->count(3)->create();

        $this->seed(MahasiswaSeeder::class);

        $this->assertSame(
            [2022 => 25, 2023 => 25, 2024 => 100, 2025 => 25, 2026 => 25],
            Mahasiswa::query()->selectRaw('angkatan, COUNT(*) AS jumlah')->groupBy('angkatan')->orderBy('angkatan')->pluck('jumlah', 'angkatan')->map(fn ($n) => (int) $n)->all(),
        );
        $this->assertSame(0, Mahasiswa::query()->get(['nim', 'angkatan'])->reject(fn ($m) => str_starts_with($m->nim, $m->angkatan.'120'))->count());
        $this->assertSame(0, Mahasiswa::query()->where('angkatan', '>', 2022)->where('status', 'Lulus')->count());
    }

    public function test_angkatan_that_already_has_data_is_skipped(): void
    {
        Prodi::factory()->create();
        Mahasiswa::factory()->create(['angkatan' => 2024, 'nim' => '202412099'])->delete();

        $this->seed(MahasiswaSeeder::class);
        $this->seed(MahasiswaSeeder::class);

        $this->assertSame(1, Mahasiswa::withTrashed()->where('angkatan', 2024)->count());
        $this->assertSame(100, Mahasiswa::query()->count());
    }
}
