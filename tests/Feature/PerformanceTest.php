<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Login lewat session asli (bukan actingAs) agar user provider benar-benar dipakai.
     */
    private function login(User $user): void
    {
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect();
    }

    /**
     * @return list<string>
     */
    private function queriesFor(string $uri): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get($uri)->assertOk();
        DB::disableQueryLog();

        return collect(DB::getQueryLog())->pluck('query')->all();
    }

    public function test_logged_in_user_permissions_are_read_from_database_on_each_request(): void
    {
        $user = User::factory()->create();
        $this->login($user);

        $this->get(route('dashboard'))->assertOk();
        $this->app['auth']->forgetGuards();
        $queries = $this->queriesFor(route('dashboard'));

        $this->assertCount(1, array_filter($queries, fn ($sql) => str_contains($sql, '"users"')));
    }

    public function test_authenticated_user_is_refreshed_after_profile_update(): void
    {
        $user = User::factory()->create(['name' => 'Nama Lama']);
        $this->login($user);
        $this->get(route('dashboard'))->assertSee('Nama Lama');

        $user->update(['name' => 'Nama Baru']);
        // Request baru: guard dimulai dari awal seperti di produksi (bukan objek user di memori test).
        $this->app['auth']->forgetGuards();

        $this->get(route('dashboard'))->assertSee('Nama Baru')->assertDontSee('Nama Lama');
    }

    public function test_prodi_dropdown_is_cached_and_refreshed_when_prodi_changes(): void
    {
        $user = User::factory()->create();
        $prodi = Prodi::factory()->create(['nama_prodi' => 'Teknik Lama']);
        $this->login($user);

        $this->get(route('mahasiswa.create'))->assertSee('Teknik Lama');
        $queries = $this->queriesFor(route('mahasiswa.create'));
        $this->assertEmpty(array_filter($queries, fn ($sql) => str_contains($sql, 'from "prodis"')));

        $prodi->update(['nama_prodi' => 'Teknik Baru']);
        $this->get(route('mahasiswa.create'))->assertSee('Teknik Baru');
    }

    public function test_prerender_request_does_not_replace_previous_url_for_back_redirects(): void
    {
        $user = User::factory()->create();
        $mahasiswa = Mahasiswa::factory()->create();
        $this->login($user);

        $this->get(route('mahasiswa.edit', $mahasiswa))->assertOk();
        // Chrome memuat halaman lain di latar belakang saat kursor hover di menu.
        $this->get(route('dashboard'), ['Sec-Purpose' => 'prefetch;prerender'])->assertOk();

        $this->put(route('mahasiswa.update', $mahasiswa), ['nim' => ''])
            ->assertRedirect(route('mahasiswa.edit', $mahasiswa));
    }

    public function test_visited_list_page_is_served_from_cache_until_data_changes(): void
    {
        $user = User::factory()->create();
        $prodi = Prodi::factory()->create();
        Mahasiswa::factory()->for($prodi)->create(['nama' => 'Ani Lama']);
        $this->login($user);

        $this->get(route('mahasiswa.index'))->assertSee('Ani Lama');
        $queries = $this->queriesFor(route('mahasiswa.index'));
        $this->assertEmpty(array_filter($queries, fn ($sql) => str_contains($sql, 'from "mahasiswas"')));

        Mahasiswa::factory()->for($prodi)->create(['nama' => 'Budi Baru']);
        $this->get(route('mahasiswa.index'))->assertSee('Budi Baru');

        $prodi->update(['nama_prodi' => 'Prodi Ganti Nama']);
        $this->get(route('mahasiswa.index'))->assertSee('Prodi Ganti Nama');
    }

    public function test_program_studi_list_reuses_cached_counts_and_refreshes_after_student_changes(): void
    {
        $user = User::factory()->create();
        $prodi = Prodi::factory()->create();
        $this->actingAs($user)->get(route('prodi.index'))->assertOk();

        $queries = $this->queriesFor(route('prodi.index'));

        $this->assertEmpty(array_filter($queries, fn ($sql) => str_contains($sql, 'from "prodis"') || str_contains($sql, 'from "mahasiswas"')));
        Mahasiswa::factory()->for($prodi)->create();
        $this->get(route('prodi.index'))->assertOk()
            ->assertViewHas('prodis', fn ($items) => $items->first()->mahasiswas_count === 1);
    }
}
