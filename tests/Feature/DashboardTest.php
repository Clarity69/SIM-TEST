<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\User;
use App\Services\DashboardStats;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_counts_per_gender_and_prodi(): void
    {
        $prodi = Prodi::factory()->create(['singkatan' => 'TI']);
        Mahasiswa::factory()->count(3)->for($prodi)->lakiLaki()->create();
        Mahasiswa::factory()->count(2)->for($prodi)->perempuan()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('totalMahasiswa', 5)
            ->assertViewHas('totalLakiLaki', 3)
            ->assertViewHas('totalPerempuan', 2)
            ->assertViewHas('totalProdi', 1);
    }

    public function test_trend_compares_with_data_from_thirty_days_ago(): void
    {
        $prodi = Prodi::factory()->create();
        Mahasiswa::factory()->count(2)->for($prodi)->create(['created_at' => now()->subDays(40)]);
        Mahasiswa::factory()->count(2)->for($prodi)->create();

        $stats = app(DashboardStats::class)->get();

        $this->assertSame(2, $stats['sebelum']['total']);
        $this->assertSame(100.0, DashboardStats::perubahan($stats['total'], $stats['sebelum']['total']));
        $this->assertSame(100.0, DashboardStats::perubahan(3, 0));
        $this->assertSame(0.0, DashboardStats::perubahan(0, 0));
        $this->assertSame(-50.0, DashboardStats::perubahan(1, 2));
    }

    public function test_cached_stats_are_refreshed_after_mahasiswa_changes(): void
    {
        $prodi = Prodi::factory()->create();
        $stats = app(DashboardStats::class);

        $this->assertSame(0, $stats->get()['total']);

        $mahasiswa = Mahasiswa::factory()->for($prodi)->create();
        $this->assertSame(1, $stats->get()['total']);

        $mahasiswa->delete();
        $this->assertSame(0, $stats->get()['total']);
    }

    public function test_dashboard_renders_from_a_serializing_cache_store(): void
    {
        // Store "array" di phpunit tidak menyerialisasi; database store melakukannya seperti file/redis.
        config(['cache.default' => 'database']);
        Mahasiswa::factory()->count(2)->create();
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertViewHas('totalMahasiswa', 2);
    }
}
