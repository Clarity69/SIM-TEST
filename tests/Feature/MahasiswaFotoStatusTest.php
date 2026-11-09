<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class MahasiswaFotoStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function payload(Prodi $prodi, array $overrides = []): array
    {
        return array_merge([
            'nim' => '202512001',
            'nama' => 'Siti Rahmawati',
            'jenis_kelamin' => 'Perempuan',
            'prodi_id' => $prodi->id,
            'angkatan' => 2025,
            'status' => 'Aktif',
        ], $overrides);
    }

    public function test_uploaded_photo_is_cropped_to_400px_webp(): void
    {
        Storage::fake('public');
        $prodi = Prodi::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('mahasiswa.store'), $this->payload($prodi, ['foto' => UploadedFile::fake()->image('siti.jpg', 900, 600)]))
            ->assertSessionHasNoErrors();

        $mahasiswa = Mahasiswa::where('nim', '202512001')->sole();
        $this->assertStringEndsWith('.webp', $mahasiswa->foto);
        Storage::disk('public')->assertExists($mahasiswa->foto);
        [$lebar, $tinggi] = getimagesizefromstring(Storage::disk('public')->get($mahasiswa->foto));
        $this->assertSame([400, 400], [$lebar, $tinggi]);
    }

    public function test_replacing_photo_deletes_the_old_file(): void
    {
        Storage::fake('public');
        $prodi = Prodi::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('mahasiswa.store'), $this->payload($prodi, ['foto' => UploadedFile::fake()->image('a.jpg', 300, 300)]));
        $mahasiswa = Mahasiswa::where('nim', '202512001')->sole();
        $lama = $mahasiswa->foto;

        $this->actingAs($user)
            ->put(route('mahasiswa.update', $mahasiswa), $this->payload($prodi, ['foto' => UploadedFile::fake()->image('b.png', 300, 300)]))
            ->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing($lama);
        Storage::disk('public')->assertExists($mahasiswa->fresh()->foto);
    }

    public function test_remove_photo_checkbox_falls_back_to_initials(): void
    {
        Storage::fake('public');
        $prodi = Prodi::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('mahasiswa.store'), $this->payload($prodi, ['foto' => UploadedFile::fake()->image('a.jpg', 300, 300)]));
        $mahasiswa = Mahasiswa::where('nim', '202512001')->sole();
        $lama = $mahasiswa->foto;

        $this->actingAs($user)->put(route('mahasiswa.update', $mahasiswa), $this->payload($prodi, ['hapus_foto' => '1']));

        $this->assertNull($mahasiswa->fresh()->foto);
        Storage::disk('public')->assertMissing($lama);
    }

    public function test_rejects_non_image_and_unknown_status(): void
    {
        Storage::fake('public');
        $prodi = Prodi::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('mahasiswa.store'), $this->payload($prodi, [
                'foto' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
                'status' => 'Pindah',
            ]))
            ->assertSessionHasErrors(['foto', 'status']);
    }

    public function test_index_filters_by_status_and_angkatan(): void
    {
        $prodi = Prodi::factory()->create();
        Mahasiswa::factory()->for($prodi)->create(['nama' => 'Aktif 2024', 'status' => 'Aktif', 'angkatan' => 2024]);
        Mahasiswa::factory()->for($prodi)->create(['nama' => 'Lulus 2021', 'status' => 'Lulus', 'angkatan' => 2021]);
        Mahasiswa::factory()->for($prodi)->create(['nama' => 'Cuti 2024', 'status' => 'Cuti', 'angkatan' => 2024]);

        $this->actingAs(User::factory()->create())
            ->get(route('mahasiswa.index', ['status' => 'Aktif', 'angkatan' => 2024]))
            ->assertSee('Aktif 2024')
            ->assertDontSee('Lulus 2021')
            ->assertDontSee('Cuti 2024');
    }

    public function test_delete_records_who_moved_it_to_trash(): void
    {
        $user = User::factory()->create();
        $mahasiswa = Mahasiswa::factory()->create();

        $this->actingAs($user)->delete(route('mahasiswa.destroy', $mahasiswa));

        $this->assertSoftDeleted($mahasiswa);
        $this->assertSame($user->id, Mahasiswa::withTrashed()->find($mahasiswa->id)->deleted_by);
    }

    public function test_storage_failure_rejects_photo_without_creating_student(): void
    {
        $prodi = Prodi::factory()->create();
        $disk = Mockery::mock(FilesystemAdapter::class);
        $disk->shouldReceive('put')->once()->andReturn(false);
        Storage::shouldReceive('disk')->with('public')->andReturn($disk);

        $this->actingAs(User::factory()->create())
            ->post(route('mahasiswa.store'), $this->payload($prodi, ['foto' => UploadedFile::fake()->image('foto.jpg', 300, 300)]))
            ->assertSessionHasErrors('foto');

        $this->assertDatabaseCount('mahasiswas', 0);
    }

    public function test_failed_student_creation_cleans_up_new_photo(): void
    {
        Storage::fake('public');
        $prodi = Prodi::factory()->create();
        $version = Mahasiswa::versiDaftar();
        Mahasiswa::created(function (Mahasiswa $mahasiswa): void {
            throw new RuntimeException('Penyimpanan mahasiswa gagal.');
        });

        $this->actingAs(User::factory()->create())
            ->post(route('mahasiswa.store'), $this->payload($prodi, ['foto' => UploadedFile::fake()->image('foto.jpg', 300, 300)]))
            ->assertServerError();

        $this->assertDatabaseCount('mahasiswas', 0);
        $this->assertDatabaseCount('activity_logs', 0);
        $this->assertSame($version, Mahasiswa::versiDaftar());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_failed_update_preserves_old_photo_and_removes_replacement(): void
    {
        Storage::fake('public');
        $prodi = Prodi::factory()->create();
        $student = Mahasiswa::factory()->for($prodi)->create();
        Storage::disk('public')->put('foto-mahasiswa/old.webp', 'old photo');
        $student->forceFill(['foto' => 'foto-mahasiswa/old.webp'])->save();
        Mahasiswa::updating(function (Mahasiswa $mahasiswa): void {
            throw new RuntimeException('Perubahan mahasiswa gagal.');
        });

        $this->actingAs(User::factory()->create())
            ->put(route('mahasiswa.update', $student), $this->payload($prodi, ['nim' => $student->nim, 'foto' => UploadedFile::fake()->image('new.jpg', 300, 300)]))
            ->assertServerError();

        $this->assertSame('foto-mahasiswa/old.webp', $student->fresh()->foto);
        $this->assertSame(['foto-mahasiswa/old.webp'], Storage::disk('public')->allFiles());
    }
}
