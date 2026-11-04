<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProdiTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_saves_kaprodi_and_defaults_blank_jabatan(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('prodi.store'), [
                'kode_prodi' => '55201',
                'singkatan' => 'TI',
                'nama_prodi' => 'Teknik Informatika',
                'kaprodi' => 'Rianindya Chandra Hardika, S.T., M.Eng',
                'kaprodi_jabatan' => '',
            ])
            ->assertRedirect(route('prodi.index'));

        $this->assertDatabaseHas('prodis', [
            'kode_prodi' => '55201',
            'kaprodi' => 'Rianindya Chandra Hardika, S.T., M.Eng',
            'kaprodi_jabatan' => 'Ketua Program Studi',
        ]);
    }

    public function test_destroy_is_refused_while_prodi_has_mahasiswa(): void
    {
        $prodi = Prodi::factory()->create();
        Mahasiswa::factory()->for($prodi)->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('prodi.destroy', $prodi))
            ->assertSessionHas('error');

        $this->assertModelExists($prodi);
    }

    public function test_show_paginates_mahasiswa_of_the_prodi(): void
    {
        $prodi = Prodi::factory()->create();
        Mahasiswa::factory()->count(16)->for($prodi)->create();

        $this->actingAs(User::factory()->create())
            ->get(route('prodi.show', $prodi))
            ->assertOk()
            ->assertViewHas('mahasiswas', fn ($page) => $page->count() === 15 && $page->total() === 16);
    }

    public function test_store_strips_spaces_from_nomor_induk_and_labels_it_by_length(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('prodi.store'), [
                'kode_prodi' => '61209',
                'nama_prodi' => 'Bisnis Digital',
                'kaprodi' => 'Abd. Rahim, S.ST., M.M.',
                'kaprodi_nidn' => '424 777 367 413 026 3',
            ])
            ->assertSessionHasNoErrors();

        $prodi = Prodi::where('kode_prodi', '61209')->sole();
        $this->assertSame('4247773674130263', $prodi->kaprodi_nidn);
        $this->assertSame('NUPTK', $prodi->labelNomorInduk());

        $prodi->update(['kaprodi_nidn' => '0627058802']);
        $this->assertSame('NIDN', $prodi->labelNomorInduk());
    }

    public function test_store_rejects_nomor_induk_with_letters(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('prodi.store'), [
                'kode_prodi' => '57201',
                'nama_prodi' => 'Sistem Informasi',
                'kaprodi_nidn' => '06270588AB',
            ])
            ->assertSessionHasErrors('kaprodi_nidn');
    }
}
