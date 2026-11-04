<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TrashTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_cannot_open_trash(): void
    {
        $this->actingAs(User::factory()->create())->get(route('trash.index'))->assertForbidden();
    }

    public function test_admin_sees_trashed_mahasiswa_with_who_deleted_it(): void
    {
        $operator = User::factory()->create(['name' => 'Operator Satu']);
        $mahasiswa = Mahasiswa::factory()->create(['nama' => 'Dimas Saragih']);
        $this->actingAs($operator)->delete(route('mahasiswa.destroy', $mahasiswa));

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('trash.index'))
            ->assertOk()
            ->assertSee('Dimas Saragih')
            ->assertSee('Operator Satu');
    }

    public function test_restore_brings_mahasiswa_back(): void
    {
        $mahasiswa = Mahasiswa::factory()->create();
        $mahasiswa->delete();

        $this->actingAs(User::factory()->admin()->create())
            ->patch(route('trash.restore', $mahasiswa))
            ->assertSessionHas('success');

        $this->assertNotSoftDeleted($mahasiswa);
        $this->assertNull($mahasiswa->fresh()->deleted_by);
    }

    public function test_restore_is_refused_when_nim_is_taken_by_active_mahasiswa(): void
    {
        $lama = Mahasiswa::factory()->create(['nim' => '202412001']);
        $lama->delete();
        Mahasiswa::factory()->create(['nim' => '202412001']);

        $this->actingAs(User::factory()->admin()->create())
            ->patch(route('trash.restore', $lama))
            ->assertSessionHas('error');

        $this->assertSoftDeleted($lama);
    }

    public function test_force_delete_removes_row_and_photo(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('foto-mahasiswa/x.webp', 'data');
        $mahasiswa = Mahasiswa::factory()->create();
        $mahasiswa->forceFill(['foto' => 'foto-mahasiswa/x.webp'])->save();
        $mahasiswa->delete();

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('trash.destroy', $mahasiswa))
            ->assertSessionHas('success');

        $this->assertModelMissing($mahasiswa);
        Storage::disk('public')->assertMissing('foto-mahasiswa/x.webp');
    }

    public function test_bulk_restore_and_bulk_force_delete(): void
    {
        $admin = User::factory()->admin()->create();
        [$a, $b, $c] = Mahasiswa::factory()->count(3)->create()->each->delete()->all();

        $this->actingAs($admin)->post(route('trash.bulk'), ['aksi' => 'pulihkan', 'ids' => [$a->id, $b->id]])->assertSessionHas('success');
        $this->assertNotSoftDeleted($a);
        $this->assertNotSoftDeleted($b);

        $this->actingAs($admin)->post(route('trash.bulk'), ['aksi' => 'hapus', 'ids' => [$c->id]])->assertSessionHas('success');
        $this->assertModelMissing($c);
    }

    public function test_bulk_only_touches_trashed_rows(): void
    {
        $aktif = Mahasiswa::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('trash.bulk'), ['aksi' => 'hapus', 'ids' => [$aktif->id]]);

        $this->assertModelExists($aktif);
        $this->assertNotSoftDeleted($aktif);
    }

    public function test_empty_trash_force_deletes_everything_in_it(): void
    {
        Mahasiswa::factory()->count(3)->create()->each->delete();
        $aktif = Mahasiswa::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('trash.empty'))
            ->assertSessionHas('success');

        $this->assertSame(0, Mahasiswa::onlyTrashed()->count());
        $this->assertModelExists($aktif);
    }
}
