<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_search(): void
    {
        $this->getJson(route('search', ['q' => 'andi']))->assertUnauthorized();
    }

    public function test_search_finds_mahasiswa_by_name_or_nim_and_prodi_by_name(): void
    {
        $prodi = Prodi::factory()->create(['nama_prodi' => 'Teknik Informatika', 'singkatan' => 'TI']);
        $andi = Mahasiswa::factory()->for($prodi)->create(['nama' => 'Andi Pratama', 'nim' => '202412011']);
        Mahasiswa::factory()->for($prodi)->create(['nama' => 'Siti Aminah', 'nim' => '202412012']);
        $user = User::factory()->create();

        $this->actingAs($user)->getJson(route('search', ['q' => 'andi']))
            ->assertOk()
            ->assertJsonCount(1, 'results')
            ->assertJsonPath('results.0.title', 'Andi Pratama')
            ->assertJsonPath('results.0.url', route('mahasiswa.show', $andi));

        $this->actingAs($user)->getJson(route('search', ['q' => '202412012']))
            ->assertJsonPath('results.0.title', 'Siti Aminah');

        $this->actingAs($user)->getJson(route('search', ['q' => 'informatika']))
            ->assertJsonFragment(['type' => 'prodi', 'title' => 'Teknik Informatika']);
    }

    public function test_search_ignores_trashed_mahasiswa_and_too_short_queries(): void
    {
        Mahasiswa::factory()->create(['nama' => 'Dewi Terhapus'])->delete();
        $user = User::factory()->create();

        $this->actingAs($user)->getJson(route('search', ['q' => 'Dewi']))->assertJsonCount(0, 'results');
        $this->actingAs($user)->getJson(route('search', ['q' => 'a']))->assertJsonCount(0, 'results');
    }

    public function test_layout_contains_command_palette(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Palet perintah')
            ->assertSee('Cari atau jalankan');
    }
}
