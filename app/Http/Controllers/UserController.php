<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Kelola pengguna (khusus admin): daftar, ubah role, hapus akun.
 */
class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->select('id', 'name', 'email', 'role', 'email_verified_at', 'created_at')
            ->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->paginate(20);

        return view('users.index', compact('users'));
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate(['role' => ['required', Rule::in(array_keys(User::ROLES))]]);

        if ($user->is($request->user()) && $validated['role'] !== User::ROLE_ADMIN) {
            return back()->with('error', 'Anda tidak dapat menurunkan role akun sendiri.');
        }

        $lama = $user->role;
        $user->forceFill(['role' => $validated['role']])->save();

        if ($lama !== $validated['role']) {
            ActivityLog::catat('account', "Mengubah role {$user->name} menjadi ".User::ROLES[$validated['role']], $user, ['role' => [$lama, $validated['role']]]);
        }

        return back()->with('success', "Role {$user->name} diubah menjadi ".User::ROLES[$validated['role']].'.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Hapus akun sendiri lewat halaman Profil.');
        }

        if ($user->isAdmin() && User::where('role', User::ROLE_ADMIN)->count() <= 1) {
            return back()->with('error', 'Admin terakhir tidak dapat dihapus.');
        }

        ActivityLog::catat('account', "Menghapus akun {$user->name}", $user);
        $user->delete();

        return back()->with('success', "Akun {$user->name} dihapus.");
    }
}
