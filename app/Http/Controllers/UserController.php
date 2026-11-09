<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\UserAdministration;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Kelola pengguna (khusus admin): tambah, daftar, ubah role, hapus akun.
 */
class UserController extends Controller
{
    public function create(): View
    {
        return view('users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = new User($request->safe()->only(['name', 'email', 'password']));
        $user->forceFill(['role' => $request->validated('role'), 'email_verified_at' => now()])->save();

        ActivityLog::catat('account', "Menambahkan akun {$user->name}", $user, ['role' => $user->role]);

        return redirect()->route('users.index')->with('success', "Akun {$user->name} berhasil ditambahkan.");
    }

    public function index(): View
    {
        $users = User::query()
            ->select('id', 'name', 'email', 'role', 'email_verified_at', 'created_at')
            ->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->paginate(20);

        return view('users.index', compact('users'));
    }

    public function updateRole(Request $request, User $user, UserAdministration $users): RedirectResponse
    {
        $validated = $request->validate(['role' => ['required', Rule::in(array_keys(User::ROLES))]]);

        if ($user->is($request->user()) && $validated['role'] !== User::ROLE_ADMIN) {
            return back()->with('error', 'Anda tidak dapat menurunkan role akun sendiri.');
        }

        $lama = $user->role;
        try {
            $users->changeRole($user, $validated['role']);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        if ($lama !== $validated['role']) {
            ActivityLog::catat('account', "Mengubah role {$user->name} menjadi ".User::ROLES[$validated['role']], $user, ['role' => [$lama, $validated['role']]]);
        }

        return back()->with('success', "Role {$user->name} diubah menjadi ".User::ROLES[$validated['role']].'.');
    }

    public function destroy(Request $request, User $user, UserAdministration $users): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Hapus akun sendiri lewat halaman Profil.');
        }

        try {
            $users->delete($user);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        ActivityLog::catat('account', "Menghapus akun {$user->name}", $user);

        return back()->with('success', "Akun {$user->name} dihapus.");
    }
}
