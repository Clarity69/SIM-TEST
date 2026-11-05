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

    public function test_percentage_formula_handles_empty_and_negative_changes(): void
    {
        $this->assertSame(1.0, DashboardStats::perubahan(100, 99));
        $this->assertSame(-1.0, DashboardStats::perubahan(99, 100));
        $this->assertSame(100.0, DashboardStats::perubahan(1, 0));
        $this->assertSame(0.0, DashboardStats::perubahan(0, 0));
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

    public function test_each_add_raises_and_each_delete_lowers_trend_per_category(): void
    {
        $prodi = Prodi::factory()->create();
        Mahasiswa::factory()->count(50)->for($prodi)->lakiLaki()->create(['created_at' => now()->subDays(3)]);
        Mahasiswa::factory()->count(49)->for($prodi)->perempuan()->create(['created_at' => now()->subDays(2)]);
        $stats = app(DashboardStats::class);
        $tren = function (string $kunci) use ($stats): float {
            $s = $stats->get();

            return DashboardStats::perubahan($s[$kunci], $s['sebelum'][$kunci]);
        };
        $this->assertSame(0.0, $tren('total')); // catatan pertama: belum ada perubahan

        // Data bertanggal lama (mis. hasil seeder/impor) tetap dihitung sebagai penambahan.
        Mahasiswa::factory()->for($prodi)->perempuan()->create(['created_at' => now()->subYears(3)]);
        $this->assertSame(1.0, $tren('total'));          // 99 -> 100
        $this->assertSame(2.0, $tren('perempuan'));      // 49 -> 50
        $this->assertSame(0.0, $tren('laki'));           // tidak berubah

        Mahasiswa::where('jenis_kelamin', 'Perempuan')->first()->delete();
        $this->assertSame(-1.0, $tren('total'));         // 100 -> 99
        $this->assertSame(-2.0, $tren('perempuan'));     // 50 -> 49

        Mahasiswa::where('jenis_kelamin', 'Laki-laki')->first()->delete();
        $this->assertSame(-1.0, round($tren('total'), 0)); // 99 -> 98
        $this->assertSame(-2.0, $tren('laki'));          // 50 -> 49
        $this->assertSame(-2.0, $tren('perempuan'));     // tetap: perubahan terakhirnya hapus 1
    }

    public function test_bulk_action_counts_as_one_change(): void
    {
        $mahasiswas = Mahasiswa::factory()->count(10)->create();
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('dashboard'));

        $this->actingAs($user)->post(route('mahasiswa.bulk'), ['aksi' => 'hapus', 'ids' => $mahasiswas->take(3)->modelKeys()]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertViewHas('selisih', fn (array $selisih) => $selisih['total'] === -3)
            ->assertViewHas('tren', fn (array $tren) => $tren['total'] === -30.0);
    }

    public function test_deleted_nim_can_be_registered_again(): void
    {
        $prodi = Prodi::factory()->create();
        Mahasiswa::factory()->for($prodi)->create(['nim' => '202412001'])->delete();

        $this->actingAs(User::factory()->create())
            ->post(route('mahasiswa.store'), [
                'nim' => '202412001',
                'nama' => 'Mahasiswa Baru',
                'jenis_kelamin' => 'Perempuan',
                'prodi_id' => $prodi->id,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Mahasiswa::where('nim', '202412001')->count());
        $this->assertSame(2, Mahasiswa::withTrashed()->where('nim', '202412001')->count());
    }

    public function test_dashboard_shows_rekap_per_prodi_with_gender_split(): void
    {
        $ti = Prodi::factory()->create(['nama_prodi' => 'Teknik Informatika', 'singkatan' => 'TI', 'kaprodi' => 'Rianindya Chandra Hardika, S.T., M.Eng']);
        Prodi::factory()->create(['nama_prodi' => 'Teknik Industri', 'singkatan' => 'TIN']);
        Mahasiswa::factory()->count(3)->for($ti)->lakiLaki()->create();
        Mahasiswa::factory()->for($ti)->perempuan()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Rekapitulasi Program Studi')
            ->assertSee('Teknik Informatika: 4 dari 4 mahasiswa, 3 laki-laki dan 1 perempuan')
            ->assertSee('Kaprodi: Rianindya Chandra Hardika, S.T., M.Eng')
            ->assertSee('Teknik Industri: 0 dari 4 mahasiswa', false);
    }

    public function test_dashboard_groups_mahasiswa_per_angkatan_and_status(): void
    {
        Mahasiswa::factory()->count(2)->create(['angkatan' => 2023, 'status' => 'Aktif']);
        Mahasiswa::factory()->create(['angkatan' => 2023, 'status' => 'Cuti']);
        Mahasiswa::factory()->create(['angkatan' => 2024, 'status' => 'Aktif']);
        Mahasiswa::factory()->create(['angkatan' => 2024, 'status' => 'Aktif'])->delete();

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Mahasiswa per Angkatan &amp; Status', false)
            ->assertViewHas('perAngkatan', [
                ['angkatan' => 2023, 'status' => 'Aktif', 'jumlah' => 2],
                ['angkatan' => 2023, 'status' => 'Cuti', 'jumlah' => 1],
                ['angkatan' => 2024, 'status' => 'Aktif', 'jumlah' => 1],
            ]);
    }
}
