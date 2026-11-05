<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\User;
use App\Services\KartuMahasiswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KartuMahasiswaTest extends TestCase
{
    use RefreshDatabase;

    public function test_card_is_rendered_as_pdf(): void
    {
        $mahasiswa = Mahasiswa::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('mahasiswa.kartu', $mahasiswa))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_bulk_action_prints_selected_cards(): void
    {
        $mahasiswas = Mahasiswa::factory()->count(3)->create();

        $this->actingAs(User::factory()->create())
            ->post(route('mahasiswa.bulk'), ['aksi' => 'kartu', 'ids' => $mahasiswas->modelKeys()])
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_qr_code_is_a_png_data_uri(): void
    {
        $this->assertStringStartsWith('data:image/png;base64,', app(KartuMahasiswa::class)->qr('https://stitek.ac.id'));
    }

    public function test_verification_page_requires_valid_signature(): void
    {
        $mahasiswa = Mahasiswa::factory()->create();

        $this->get(route('kartu.verifikasi', $mahasiswa->id))->assertForbidden();
        $this->get(app(KartuMahasiswa::class)->urlVerifikasi($mahasiswa).'x')->assertForbidden();
    }

    public function test_verification_page_confirms_active_student_without_login(): void
    {
        $mahasiswa = Mahasiswa::factory()->create(['nama' => 'Sari Lestari', 'status' => 'Aktif']);

        $this->get(app(KartuMahasiswa::class)->urlVerifikasi($mahasiswa))
            ->assertOk()
            ->assertSee('Kartu valid')
            ->assertSee('Sari Lestari');
    }

    public function test_verification_page_flags_card_of_deleted_or_graduated_student(): void
    {
        $lulus = Mahasiswa::factory()->create(['status' => 'Lulus']);
        $dihapus = Mahasiswa::factory()->create();
        $dihapus->delete();
        $kartu = app(KartuMahasiswa::class);

        $this->get($kartu->urlVerifikasi($lulus))->assertOk()->assertSee('Kartu tidak berlaku — status Lulus');
        $this->get($kartu->urlVerifikasi($dihapus))->assertOk()->assertSee('data sudah dihapus');
    }
}
