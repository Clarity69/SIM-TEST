<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\MataKuliah;
use App\Models\Prodi;
use App\Models\User;
use App\Services\AcademicStats;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class AcademicManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_dosen_can_be_added_and_only_active_lecturers_are_counted(): void
    {
        $prodi = Prodi::factory()->create();
        Dosen::factory()->for($prodi)->nonaktif()->create();
        $payload = Dosen::factory()->for($prodi)->make()->only(['nidn', 'nama', 'email', 'prodi_id', 'aktif']);
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('dosen.store'), $payload)->assertRedirect(route('dosen.index'));

        $this->assertDatabaseHas('dosens', ['nidn' => $payload['nidn'], 'nama' => $payload['nama']]);
        $this->assertDatabaseHas('activity_logs', ['user_id' => $user->id, 'event' => 'created', 'subject_type' => 'dosen', 'subject_label' => $payload['nama']]);
        $this->get(route('dashboard'))->assertOk()->assertViewHas('totalDosenAktif', 1);
        $this->get(route('dosen.index', ['aktif' => '0']))->assertOk()->assertViewHas('items', fn ($items) => $items->total() === 1);
    }

    public function test_editing_lecturer_status_invalidates_cached_counts(): void
    {
        $dosen = Dosen::factory()->create();
        $this->assertSame(1, app(AcademicStats::class)->get()['totalDosenAktif']);
        $payload = $dosen->only(['nidn', 'nama', 'email', 'prodi_id', 'aktif']);
        $payload['aktif'] = false;

        $this->actingAs(User::factory()->create())->put(route('dosen.update', $dosen), $payload)
            ->assertRedirect(route('dosen.index'));

        $this->assertFalse($dosen->refresh()->aktif);
        $this->assertSame(0, app(AcademicStats::class)->get()['totalDosenAktif']);
    }

    public function test_courses_can_be_added_edited_and_counted(): void
    {
        $prodi = Prodi::factory()->create();
        $payload = MataKuliah::factory()->for($prodi)->make()->only(['kode', 'nama', 'sks', 'semester', 'prodi_id', 'aktif']);
        $this->actingAs(User::factory()->create());

        $this->post(route('mata-kuliah.store'), $payload)->assertRedirect(route('mata-kuliah.index'));

        $course = MataKuliah::query()->where('kode', $payload['kode'])->sole();
        $this->assertSame(3, $course->sks);
        $this->assertDatabaseHas('activity_logs', ['event' => 'created', 'subject_type' => 'mata_kuliah', 'subject_id' => $course->id]);
        $this->get(route('dashboard'))->assertOk()->assertViewHas('totalMataKuliah', 1);
        $payload['sks'] = 4;
        $this->put(route('mata-kuliah.update', $course), $payload)->assertRedirect(route('mata-kuliah.index'));
        $this->assertSame(4, $course->refresh()->sks);
    }

    #[TestWith(['dosen', Dosen::class])]
    #[TestWith(['mata-kuliah', MataKuliah::class])]
    public function test_only_admin_can_delete_academic_records(string $resource, string $model): void
    {
        $record = $model::factory()->create();
        $this->actingAs(User::factory()->create())->delete(route($resource.'.destroy', $record))->assertForbidden();
        $this->assertModelExists($record);

        $this->actingAs(User::factory()->admin()->create())->delete(route($resource.'.destroy', $record))
            ->assertRedirect(route($resource.'.index'));

        $this->assertModelMissing($record);
        $this->assertSame(0, app(AcademicStats::class)->get()[$resource === 'dosen' ? 'totalDosenAktif' : 'totalMataKuliah']);
    }

    public function test_course_validation_rejects_duplicate_code_and_invalid_academic_fields(): void
    {
        $course = MataKuliah::factory()->create();

        $this->actingAs(User::factory()->create())->post(route('mata-kuliah.store'), [
            'kode' => $course->kode, 'nama' => '', 'sks' => 0, 'semester' => 15,
            'prodi_id' => 999999, 'aktif' => 'unexpected',
        ])->assertSessionHasErrors(['kode', 'nama', 'sks', 'semester', 'prodi_id', 'aktif']);

        $this->assertDatabaseCount('mata_kuliahs', 1);
    }

    public function test_lecturer_validation_rejects_duplicate_nidn_and_invalid_email(): void
    {
        $dosen = Dosen::factory()->create();

        $this->actingAs(User::factory()->create())->post(route('dosen.store'), [
            'nidn' => $dosen->nidn, 'nama' => 'Dosen Baru', 'email' => 'bukan-email',
            'prodi_id' => $dosen->prodi_id, 'aktif' => true,
        ])->assertSessionHasErrors(['nidn', 'email']);

        $this->assertDatabaseCount('dosens', 1);
    }

    #[TestWith(['dosen', Dosen::class])]
    #[TestWith(['mata-kuliah', MataKuliah::class])]
    public function test_academic_pages_escape_names_and_render_forms(string $resource, string $model): void
    {
        $name = '<script>alert("academic")</script>';
        $record = $model::factory()->create(['nama' => $name]);
        $this->actingAs(User::factory()->admin()->create());

        $this->get(route($resource.'.index'))->assertOk()->assertSee($name)->assertDontSee($name, false);
        $this->get(route($resource.'.create'))->assertOk();
        $this->get(route($resource.'.edit', $record))->assertOk()->assertSee($name);
    }

    #[TestWith([Dosen::class])]
    #[TestWith([MataKuliah::class])]
    public function test_prodi_with_academic_records_cannot_be_deleted(string $model): void
    {
        $dosen = $model::factory()->create();

        $this->actingAs(User::factory()->admin()->create())->delete(route('prodi.destroy', $dosen->prodi))
            ->assertSessionHas('error', 'Prodi tidak dapat dihapus karena masih digunakan dosen atau mata kuliah.');

        $this->assertModelExists($dosen->prodi);
    }

    #[TestWith(['dosen'])]
    #[TestWith(['mata-kuliah'])]
    public function test_guests_cannot_access_academic_modules(string $resource): void
    {
        $this->get(route($resource.'.index'))->assertRedirect(route('login'));
        $this->post(route($resource.'.store'), [])->assertRedirect(route('login'));
    }

    public function test_pending_schema_shows_a_safe_empty_state_and_blocks_edit_binding(): void
    {
        Cache::put(AcademicStats::CACHE_KEY, ['ready' => false, 'totalDosenAktif' => null, 'totalMataKuliah' => null]);
        $this->actingAs(User::factory()->create());

        $this->get(route('dosen.index'))->assertOk()->assertSee('Modul belum diaktifkan');
        $this->get(route('dosen.edit', 999))->assertRedirect(route('dosen.index'));
        $this->get(route('mata-kuliah.edit', 999))->assertRedirect(route('mata-kuliah.index'));
    }

    public function test_missing_tables_keep_dashboard_available_without_inventing_zero_counts(): void
    {
        Schema::drop('dosens');

        $this->actingAs(User::factory()->create())->get(route('dashboard'))
            ->assertOk()->assertViewHas('totalDosenAktif', null)->assertViewHas('totalMataKuliah', null);
    }

    #[TestWith(['12345678901'])]
    #[TestWith(['not-a-number'])]
    public function test_lecturer_number_must_be_a_ten_digit_nidn_or_sixteen_digit_nuptk(string $nidn): void
    {
        $prodi = Prodi::factory()->create();

        $this->actingAs(User::factory()->create())->post(route('dosen.store'), [
            'nidn' => $nidn, 'nama' => 'Dosen Baru', 'prodi_id' => $prodi->id, 'aktif' => true,
        ])->assertSessionHasErrors('nidn');

        $this->assertDatabaseCount('dosens', 0);
    }

    public function test_postgres_migrations_keep_new_tables_private_to_laravel(): void
    {
        config([
            'database.default' => 'pgsql',
            'database.connections.pgsql.url' => null,
            'database.connections.pgsql.host' => '127.0.0.1',
            'database.connections.pgsql.port' => 1,
        ]);
        $connection = DB::connection('pgsql');

        foreach (['dosens', 'mata_kuliahs'] as $table) {
            $path = glob(database_path('migrations/*_create_'.$table.'_table.php'))[0];
            $migration = require $path;
            $queries = $connection->pretend(fn () => $migration->up());
            $sql = array_column($queries, 'query');

            $this->assertContains('ALTER TABLE public.'.$table.' ENABLE ROW LEVEL SECURITY', $sql);
            $this->assertContains('REVOKE ALL ON TABLE public.'.$table.' FROM anon, authenticated', $sql);
        }
        config(['database.default' => 'sqlite']);
    }
}
