<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_user_creation_from_user_list(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $this->get(route('users.index'))->assertOk()->assertSee(route('users.create'));
        $this->get(route('users.create'))->assertOk()->assertSee('Konfirmasi password');
    }

    public function test_guest_cannot_open_or_submit_user_creation(): void
    {
        $this->get(route('users.create'))->assertRedirect(route('login'));
        $this->post(route('users.store'), $this->account())->assertRedirect(route('login'));
        $this->assertDatabaseCount('users', 0);
    }

    public function test_operator_cannot_open_or_submit_user_creation(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get(route('users.create'))->assertForbidden();
        $this->post(route('users.store'), $this->account())->assertForbidden();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_unverified_admin_cannot_create_users(): void
    {
        $this->actingAs(User::factory()->admin()->unverified()->create());
        $this->get(route('users.create'))->assertRedirect(route('verification.notice'));
        $this->post(route('users.store'), $this->account())->assertRedirect(route('verification.notice'));
        $this->assertDatabaseCount('users', 1);
    }

    public function test_admin_creates_active_operator_without_changing_own_session(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->post(route('users.store'), $this->account() + ['id' => $admin->id])
            ->assertRedirect(route('users.index'))->assertSessionHas('success');

        $user = User::where('email', 'new@example.test')->sole();
        $this->assertSame('Pengguna Baru', $user->name);
        $this->assertSame(User::ROLE_OPERATOR, $user->role);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue(Hash::check('secure-password-123', $user->password));
        $this->assertAuthenticatedAs($admin);
        $log = ActivityLog::where('event', 'account')->where('subject_id', $user->id)->sole();
        $this->assertSame($admin->id, $log->user_id);
        $this->assertSame(['role' => User::ROLE_OPERATOR], $log->properties);

        $this->post(route('logout'));
        $this->post(route('login'), ['email' => $user->email, 'password' => 'secure-password-123'])
            ->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($user);
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('users.index'))->assertForbidden();
    }

    public function test_admin_can_create_another_admin(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('users.store'), array_replace($this->account(), ['role' => User::ROLE_ADMIN]))
            ->assertRedirect(route('users.index'));
        $this->assertTrue(User::where('email', 'new@example.test')->sole()->isAdmin());
    }

    public function test_required_fields_are_validated(): void
    {
        $this->actingAs(User::factory()->admin()->create())->post(route('users.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'role', 'password']);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_duplicate_email_is_rejected_case_insensitively(): void
    {
        $this->actingAs(User::factory()->admin()->create(['email' => 'new@example.test']))
            ->post(route('users.store'), array_replace($this->account(), ['email' => 'NEW@example.test']))
            ->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_unknown_role_is_rejected(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('users.store'), array_replace($this->account(), ['role' => 'superadmin']))
            ->assertSessionHasErrors('role');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_short_password_is_rejected(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('users.store'), array_replace($this->account(), ['password' => 'short', 'password_confirmation' => 'short']))
            ->assertSessionHasErrors('password');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_password_confirmation_must_match(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('users.store'), array_replace($this->account(), ['password_confirmation' => 'different-password']))
            ->assertSessionHasErrors('password');
        $this->assertDatabaseCount('users', 1);
    }

    /** @return array{name: string, email: string, role: string, password: string, password_confirmation: string} */
    private function account(): array
    {
        return [
            'name' => 'Pengguna Baru',
            'email' => 'new@example.test',
            'role' => User::ROLE_OPERATOR,
            'password' => 'secure-password-123',
            'password_confirmation' => 'secure-password-123',
        ];
    }
}
