<?php

namespace Tests\Feature;

use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_self_registered_account_becomes_operator(): void
    {
        config(['auth.registration_enabled' => true]);

        $this->post('/register', [
            'name' => 'Operator Baru',
            'email' => 'operator@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'admin', // harus diabaikan: role tidak mass-assignable
        ]);

        $this->assertSame(User::ROLE_OPERATOR, User::where('email', 'operator@example.com')->value('role'));
    }

    public function test_operator_cannot_delete_prodi_but_admin_can(): void
    {
        $prodi = Prodi::factory()->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('prodi.destroy', $prodi))
            ->assertForbidden();
        $this->assertModelExists($prodi);

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('prodi.destroy', $prodi))
            ->assertRedirect(route('prodi.index'));
        $this->assertModelMissing($prodi);
    }

    public function test_operator_does_not_see_prodi_delete_button(): void
    {
        Prodi::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('prodi.index'))
            ->assertDontSee('Hapus program studi?');

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('prodi.index'))
            ->assertSee('Hapus program studi?');
    }

    public function test_operator_is_forbidden_from_user_management(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_admin_can_promote_operator(): void
    {
        $operator = User::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->patch(route('users.role', $operator), ['role' => 'admin'])
            ->assertSessionHas('success');

        $this->assertTrue($operator->fresh()->isAdmin());
    }

    public function test_admin_cannot_demote_self_or_delete_last_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->patch(route('users.role', $admin), ['role' => 'operator'])
            ->assertSessionHas('error');
        $this->assertTrue($admin->fresh()->isAdmin());

        $this->actingAs($admin)
            ->delete(route('users.destroy', $admin))
            ->assertSessionHas('error');
        $this->assertModelExists($admin);
    }

    public function test_role_command_promotes_user(): void
    {
        $user = User::factory()->create(['email' => 'kaprodi@example.com']);

        $this->artisan('user:role', ['email' => 'kaprodi@example.com', 'role' => 'admin'])->assertSuccessful();

        $this->assertTrue($user->fresh()->isAdmin());
    }
}
