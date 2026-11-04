<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Menampilkan form edit profile.
     */
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update informasi profile.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // Dari modal di dropdown topbar: error di bag terpisah dan kembali ke halaman asal.
        $dariModal = $request->boolean('_modal');

        $validated = $request->validateWithBag($dariModal ? 'updateProfile' : 'default', [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $pesan = $user->wasChanged('email')
            ? 'Profil diperbarui. Cek email baru Anda untuk verifikasi ulang.'
            : 'Informasi profil berhasil diperbarui.';

        if ($user->wasChanged('email')) {
            $user->sendEmailVerificationNotification();
        }

        return ($dariModal ? back() : redirect()->route('profile.edit'))->with('success', $pesan);
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request)
    {
        $dariModal = $request->boolean('_modal');

        $validated = $request->validateWithBag($dariModal ? 'updatePassword' : 'default', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return ($dariModal ? back() : redirect()->route('profile.edit'))->with('success', 'Password berhasil diperbarui.');
    }

    /**
     * Preferensi notifikasi email perubahan data mahasiswa.
     */
    public function updateNotifications(Request $request)
    {
        $request->user()->update([
            'notify_mahasiswa_changes' => $request->boolean('notify_mahasiswa_changes'),
        ]);

        return redirect()->route('profile.edit')->with('success', 'Preferensi notifikasi berhasil disimpan.');
    }

    /**
     * Hapus akun user yang sedang login (wajib konfirmasi password).
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
