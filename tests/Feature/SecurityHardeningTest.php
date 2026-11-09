<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_disabled_registration_rejects_both_form_and_submission(): void
    {
        config(['auth.registration_enabled' => false]);
        Mail::fake();
        Notification::fake();

        $this->get('/register')->assertNotFound();
        $this->post('/register', [
            'name' => 'Tidak Diizinkan', 'email' => 'outsider@example.com',
            'password' => 'password12345', 'password_confirmation' => 'password12345',
        ])->assertNotFound();

        $this->get('/login')->assertOk()->assertDontSee('Daftar sekarang');
        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
        Mail::assertNothingSent();
        Mail::assertNothingQueued();
        Notification::assertNothingSent();
    }

    public function test_production_seeding_does_not_create_demo_data_or_promote_existing_accounts(): void
    {
        $operator = User::factory()->create(['email' => 'admin@example.com']);
        $this->app['env'] = 'production';

        try {
            $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true])->assertSuccessful();
        } finally {
            $this->app['env'] = 'testing';
        }

        $this->assertSame(User::ROLE_OPERATOR, $operator->fresh()->role);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('prodis', 0);
        $this->assertDatabaseCount('mahasiswas', 0);
    }

    public function test_last_admin_cannot_delete_own_account_through_profile(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->from('/profile')
            ->delete('/profile', ['password' => 'password'])
            ->assertRedirect('/profile')->assertSessionHas('error');

        $this->assertModelExists($admin);
        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_can_delete_own_account_when_another_admin_remains(): void
    {
        $admin = User::factory()->admin()->create();
        $remaining = User::factory()->admin()->create();

        $this->actingAs($admin)->delete('/profile', ['password' => 'password'])->assertRedirect('/');

        $this->assertModelMissing($admin);
        $this->assertModelExists($remaining);
        $this->assertGuest();
    }

    public function test_cli_cannot_demote_last_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->artisan('user:role', ['email' => $admin->email, 'role' => 'operator'])->assertFailed();

        $this->assertSame(User::ROLE_ADMIN, $admin->fresh()->role);
    }

    public function test_trash_endpoints_reject_active_student_ids(): void
    {
        $student = Mahasiswa::factory()->create();

        $this->actingAs(User::factory()->admin()->create());
        $this->delete(route('trash.destroy', $student))->assertNotFound();
        $this->patch(route('trash.restore', $student))->assertNotFound();

        $this->assertModelExists($student);
        $this->assertNotSoftDeleted($student);
    }

    public function test_role_demotion_from_another_process_takes_effect_on_next_request(): void
    {
        $admin = User::factory()->admin()->create();
        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])->assertRedirect();
        $this->get(route('users.index'))->assertOk();

        DB::table('users')->where('id', $admin->id)->update(['role' => User::ROLE_OPERATOR]);
        $this->app['auth']->forgetGuards();

        $this->get(route('users.index'))->assertForbidden();
    }

    public function test_account_deleted_by_another_process_loses_access_on_next_request(): void
    {
        $user = User::factory()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect();
        $this->get(route('dashboard'))->assertOk();

        DB::table('users')->where('id', $user->id)->delete();
        $this->app['auth']->forgetGuards();

        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_admin_command_creates_verified_admin_with_hashed_password(): void
    {
        $this->artisan('user:create', ['email' => 'secure@example.com', '--name' => 'Admin Kampus', '--role' => 'admin'])
            ->expectsQuestion('Password akun', 'secure-password-123')
            ->assertSuccessful();

        $admin = User::where('email', 'secure@example.com')->sole();
        $this->assertTrue($admin->isAdmin());
        $this->assertNotNull($admin->email_verified_at);
        $this->assertTrue(Hash::check('secure-password-123', $admin->password));
    }

    public function test_admin_command_does_not_promote_an_existing_account(): void
    {
        $operator = User::factory()->create(['email' => 'existing@example.com']);

        $this->artisan('user:create', ['email' => $operator->email, '--name' => 'Admin', '--role' => 'admin'])
            ->expectsQuestion('Password akun', 'secure-password-123')
            ->assertExitCode(2);

        $this->assertSame(User::ROLE_OPERATOR, $operator->fresh()->role);
    }

    public function test_admin_command_rejects_weak_password(): void
    {
        $this->artisan('user:create', ['email' => 'secure@example.com', '--name' => 'Admin', '--role' => 'admin'])
            ->expectsQuestion('Password akun', 'short')
            ->assertExitCode(2);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_admin_command_requires_interactive_password_input(): void
    {
        $this->artisan('user:create', ['email' => 'secure@example.com', '--name' => 'Admin', '--role' => 'admin', '--no-interaction' => true])
            ->assertExitCode(2);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_user_command_creates_verified_operator_by_default(): void
    {
        $this->artisan('user:create', ['email' => 'operator@example.com', '--name' => 'Operator'])
            ->expectsQuestion('Password akun', 'secure-password-123')
            ->assertSuccessful();

        $user = User::where('email', 'operator@example.com')->sole();
        $this->assertSame(User::ROLE_OPERATOR, $user->role);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_user_command_rejects_unknown_role(): void
    {
        $this->artisan('user:create', ['email' => 'operator@example.com', '--name' => 'Operator', '--role' => 'superadmin'])
            ->expectsQuestion('Password akun', 'secure-password-123')
            ->assertExitCode(2);

        $this->assertDatabaseCount('users', 0);
    }
}
