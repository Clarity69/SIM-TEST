<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AcademicStats;
use App\Services\DashboardStats;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardPresentationTest extends TestCase
{
    public function test_dashboard_shows_empty_states_without_inventing_academic_data(): void
    {
        $this->freezeTime();
        Cache::put(AcademicStats::CACHE_KEY, ['ready' => false, 'totalDosenAktif' => null, 'totalMataKuliah' => null]);
        Cache::put(DashboardStats::CACHE_KEY, [
            'total' => 0, 'laki' => 0, 'perempuan' => 0, 'totalProdi' => 0,
            'sebelum' => ['total' => 0, 'laki' => 0, 'perempuan' => 0, 'prodi' => 0],
            'terakhir' => ['total' => null, 'laki' => null, 'perempuan' => null, 'prodi' => null],
            'perProdi' => [], 'terbaru' => [], 'perAngkatan' => [],
        ]);

        $this->actingAs(User::factory()->make(['id' => 1]))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dosen Aktif')
            ->assertSee('Mata Kuliah')
            ->assertSee('Data belum tersedia')
            ->assertSee('Belum ada data angkatan')
            ->assertSee('Belum ada mahasiswa')
            ->assertSee('Agenda akademik belum tersedia.')
            ->assertSee('Pengumuman belum tersedia')
            ->assertDontSee('12.480');
    }

    public function test_dashboard_aggregates_cached_cohorts_and_escapes_student_names(): void
    {
        $this->freezeTime();
        Cache::put(AcademicStats::CACHE_KEY, ['ready' => false, 'totalDosenAktif' => null, 'totalMataKuliah' => null]);
        Cache::put(DashboardStats::CACHE_KEY, [
            'total' => 3, 'laki' => 2, 'perempuan' => 1, 'totalProdi' => 1,
            'sebelum' => ['total' => 3, 'laki' => 2, 'perempuan' => 1, 'prodi' => 1],
            'terakhir' => ['total' => null, 'laki' => null, 'perempuan' => null, 'prodi' => null],
            'perProdi' => [],
            'terbaru' => [[
                'id' => 42, 'nim' => '2026100142', 'nama' => '<script>alert("student")</script>',
                'jenis_kelamin' => 'Perempuan', 'prodi' => 'Teknik Informatika',
                'created_at' => now()->toIso8601String(),
            ]],
            'perAngkatan' => [
                ['angkatan' => 2026, 'status' => 'Aktif', 'jumlah' => 2],
                ['angkatan' => 2026, 'status' => 'Cuti', 'jumlah' => 1],
            ],
        ]);

        $this->actingAs(User::factory()->admin()->make(['id' => 1]))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Angkatan 2026: 3 mahasiswa')
            ->assertSee('Teknik Informatika')
            ->assertSee('Data ditambahkan')
            ->assertSee('<script>alert("student")</script>')
            ->assertDontSee('<script>alert("student")</script>', false);
    }

    public function test_unavailable_statistics_are_distinct_from_zero(): void
    {
        $this->blade('<x-stat-card label="Dosen Aktif" :value="null" icon="user" />')
            ->assertSee('—')
            ->assertSee('Data belum tersedia');

        $this->blade('<x-stat-card label="Total Mahasiswa" :value="0" icon="users" />')
            ->assertSee('>0</p>', false)
            ->assertDontSee('Data belum tersedia');
    }
}
