<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_mahasiswa_is_logged_with_actor_and_subject(): void
    {
        $user = User::factory()->create(['name' => 'Operator Satu']);
        $prodi = Prodi::factory()->create();

        $this->actingAs($user)->post(route('mahasiswa.store'), [
            'nim' => '202412077',
            'nama' => 'Rina Kusuma',
            'jenis_kelamin' => 'Perempuan',
            'prodi_id' => $prodi->id,
        ])->assertRedirect();

        $mahasiswa = Mahasiswa::where('nim', '202412077')->firstOrFail();
        $this->assertDatabaseHas('activity_logs', [
            'event' => 'created',
            'user_id' => $user->id,
            'user_name' => 'Operator Satu',
            'subject_type' => 'mahasiswa',
            'subject_id' => $mahasiswa->id,
            'subject_label' => 'Rina Kusuma (202412077)',
        ]);
    }

    public function test_update_logs_only_changed_fields_with_old_and_new_values(): void
    {
        $mahasiswa = Mahasiswa::factory()->create(['nama' => 'Budi Lama', 'status' => 'Aktif']);
        $payload = $mahasiswa->only(['nim', 'jenis_kelamin', 'prodi_id']) + ['nama' => 'Budi Baru', 'status' => 'Cuti'];

        $this->actingAs(User::factory()->create())->put(route('mahasiswa.update', $mahasiswa), $payload);

        $log = ActivityLog::where('event', 'updated')->sole();
        $this->assertSame([
            'nama' => ['Budi Lama', 'Budi Baru'],
            'status' => ['Aktif', 'Cuti'],
        ], $log->properties['perubahan']);
    }

    public function test_restore_is_logged_as_restored_not_as_update(): void
    {
        $mahasiswa = Mahasiswa::factory()->create();
        $mahasiswa->delete();

        $this->actingAs(User::factory()->admin()->create())->patch(route('trash.restore', $mahasiswa));

        $this->assertSame(['restored'], ActivityLog::pluck('event')->all());
    }

    public function test_bulk_delete_writes_one_summary_entry(): void
    {
        $mahasiswas = Mahasiswa::factory()->count(3)->create();
        ActivityLog::simpanAntrean(); // tanpa user login: factory tidak menghasilkan log

        $this->actingAs(User::factory()->create())->post(route('mahasiswa.bulk'), [
            'aksi' => 'hapus',
            'ids' => $mahasiswas->modelKeys(),
        ]);

        $log = ActivityLog::sole();
        $this->assertSame('bulk', $log->event);
        $this->assertSame('Memindahkan 3 mahasiswa ke Sampah', $log->description);
    }

    public function test_actions_without_logged_in_user_are_not_logged(): void
    {
        Mahasiswa::factory()->create();
        ActivityLog::simpanAntrean();

        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_rolled_back_changes_do_not_leave_activity_entries(): void
    {
        $this->actingAs(User::factory()->create());
        DB::beginTransaction();
        Mahasiswa::factory()->create();

        DB::rollBack();
        ActivityLog::simpanAntrean();

        $this->assertDatabaseCount('activity_logs', 0);
        $this->assertDatabaseCount('mahasiswas', 0);
    }

    public function test_logout_activity_survives_account_deletion_with_actor_name(): void
    {
        $user = User::factory()->create(['name' => 'Operator Dihapus']);

        $this->actingAs($user)->delete(route('profile.destroy'), ['password' => 'password'])->assertRedirect('/');

        $this->assertDatabaseHas('activity_logs', ['event' => 'logout', 'user_id' => null, 'user_name' => 'Operator Dihapus']);
    }

    public function test_login_is_logged(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password']);

        $this->assertDatabaseHas('activity_logs', ['event' => 'login', 'user_id' => $user->id]);
    }

    public function test_operator_cannot_open_activity_log(): void
    {
        $this->actingAs(User::factory()->create())->get(route('activity.index'))->assertForbidden();
    }

    public function test_admin_can_filter_activity_log_by_event(): void
    {
        $admin = User::factory()->admin()->create();
        ActivityLog::catat('import', 'Mengimpor 5 mahasiswa dari data.xlsx', pelaku: $admin);
        ActivityLog::catat('export', 'Mengekspor data mahasiswa ke Excel', pelaku: $admin);
        ActivityLog::simpanAntrean();

        $this->actingAs($admin)
            ->get(route('activity.index', ['event' => 'import']))
            ->assertOk()
            ->assertSee('Mengimpor 5 mahasiswa dari data.xlsx')
            ->assertDontSee('Mengekspor data mahasiswa ke Excel');
    }

    public function test_mahasiswa_detail_shows_its_history(): void
    {
        $user = User::factory()->create();
        $mahasiswa = Mahasiswa::factory()->create(['status' => 'Aktif']);
        $this->actingAs($user)->put(route('mahasiswa.update', $mahasiswa), $mahasiswa->only(['nim', 'nama', 'jenis_kelamin', 'prodi_id']) + ['status' => 'Lulus']);

        $this->actingAs($user)
            ->get(route('mahasiswa.show', $mahasiswa))
            ->assertOk()
            ->assertSee('Riwayat Aktivitas')
            ->assertSee('Mengubah data mahasiswa');
    }

    public function test_log_page_renders_account_creation_with_a_scalar_role(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Operator Baru', 'email' => 'operator-baru@example.test', 'role' => User::ROLE_OPERATOR,
            'password' => 'PasswordAman123!', 'password_confirmation' => 'PasswordAman123!',
        ])->assertRedirect(route('users.index'));

        $this->get(route('activity.index'))->assertOk()
            ->assertSee('Menambahkan akun Operator Baru')->assertSee('Role akun:')->assertSee('Operator');

        $this->assertSame(User::ROLE_OPERATOR, ActivityLog::query()->where('event', 'account')->sole()->properties['role']);
    }

    public function test_log_page_still_renders_old_and_new_role_values(): void
    {
        $admin = User::factory()->admin()->create();
        $operator = User::factory()->create();
        $this->actingAs($admin)->patch(route('users.role', $operator), ['role' => User::ROLE_ADMIN])->assertRedirect();

        $this->get(route('activity.index'))->assertOk()->assertSee('Role')->assertSee('Operator')->assertSee('Admin');

        $this->assertSame([User::ROLE_OPERATOR, User::ROLE_ADMIN], ActivityLog::query()->where('event', 'account')->sole()->properties['role']);
    }
}
