{{-- Modal Profil & Ganti Password dari dropdown topbar. Buka: $store.account.open('profile' | 'password') --}}
@php
    $user = auth()->user();
    $awal = $errors->updatePassword->isNotEmpty() ? 'password' : ($errors->updateProfile->isNotEmpty() ? 'profile' : null);
@endphp

<div x-data x-init="$store.account.modal = @js($awal)">
    {{-- Profil --}}
    <div x-show="$store.account.modal === 'profile'" x-cloak class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center"
        role="dialog" aria-modal="true" aria-labelledby="modal-profil-title" x-on:keydown.escape.window="$store.account.close()">
        <div class="fixed inset-0 bg-slate-950/50 backdrop-blur-sm" x-show="$store.account.modal === 'profile'" x-transition.opacity x-on:click="$store.account.close()" aria-hidden="true"></div>

        <form method="POST" action="{{ route('profile.update') }}" class="relative w-full max-w-lg rounded-2xl bg-white shadow-xl dark:bg-slate-900"
            x-show="$store.account.modal === 'profile'" x-transition x-trap.noscroll.inert="$store.account.modal === 'profile'"
            x-data="{ submitting: false }" x-on:submit="submitting = true">
            @csrf
            @method('PATCH')
            <input type="hidden" name="_modal" value="1">

            <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                <h2 id="modal-profil-title" class="text-base font-semibold text-slate-900 dark:text-white">Profil Saya</h2>
                <button type="button" class="rounded-lg p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" x-on:click="$store.account.close()">
                    <span class="sr-only">Tutup</span><x-icon name="x" />
                </button>
            </div>

            <div class="space-y-5 px-6 py-5">
                <div class="flex items-center gap-4">
                    <x-avatar :name="$user->name" size="lg" />
                    <div class="min-w-0">
                        <p class="truncate text-base font-semibold text-slate-900 dark:text-white">{{ $user->name }}</p>
                        <p class="truncate text-sm text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                        @if ($user->hasVerifiedEmail())
                            <p class="mt-1 flex items-center gap-1 text-xs font-medium text-emerald-700 dark:text-emerald-400"><x-icon name="circle-check" class="size-3.5" /> Email terverifikasi</p>
                        @else
                            <p class="mt-1 flex items-center gap-1 text-xs font-medium text-amber-700 dark:text-amber-400"><x-icon name="circle-alert" class="size-3.5" /> Email belum diverifikasi</p>
                        @endif
                    </div>
                </div>

                <x-input name="name" id="modal_name" label="Nama lengkap" :value="$user->name" icon="user" bag="updateProfile" required autocomplete="name" />
                <x-input name="email" id="modal_email" type="email" label="Email" :value="$user->email" icon="mail" bag="updateProfile" required autocomplete="email"
                    hint="Mengubah email akan mengirim tautan verifikasi ke alamat baru." />
                <p class="text-xs text-slate-500 dark:text-slate-400">Bergabung {{ $user->created_at?->locale('id')->translatedFormat('d F Y') }} ·
                    <a href="{{ route('profile.edit') }}" class="font-semibold text-primary-700 hover:text-primary-800 dark:text-primary-300">Pengaturan lengkap</a></p>
            </div>

            <div class="flex justify-end gap-3 rounded-b-2xl border-t border-slate-200 bg-slate-50 px-6 py-3.5 dark:border-slate-800 dark:bg-slate-900/60">
                <x-button variant="secondary" x-on:click="$store.account.close()">Batal</x-button>
                <x-button type="submit" icon="save" :loading="true">Simpan</x-button>
            </div>
        </form>
    </div>

    {{-- Ganti password --}}
    <div x-show="$store.account.modal === 'password'" x-cloak class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center"
        role="dialog" aria-modal="true" aria-labelledby="modal-password-title" x-on:keydown.escape.window="$store.account.close()">
        <div class="fixed inset-0 bg-slate-950/50 backdrop-blur-sm" x-show="$store.account.modal === 'password'" x-transition.opacity x-on:click="$store.account.close()" aria-hidden="true"></div>

        <form method="POST" action="{{ route('profile.password.update') }}" class="relative w-full max-w-md rounded-2xl bg-white shadow-xl dark:bg-slate-900"
            x-show="$store.account.modal === 'password'" x-transition x-trap.noscroll.inert="$store.account.modal === 'password'"
            x-data="{ submitting: false }" x-on:submit="submitting = true">
            @csrf
            @method('PUT')
            <input type="hidden" name="_modal" value="1">

            <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                <div>
                    <h2 id="modal-password-title" class="text-base font-semibold text-slate-900 dark:text-white">Ganti Password</h2>
                    <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Minimal 8 karakter, jangan dipakai di layanan lain.</p>
                </div>
                <button type="button" class="rounded-lg p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" x-on:click="$store.account.close()">
                    <span class="sr-only">Tutup</span><x-icon name="x" />
                </button>
            </div>

            <div class="space-y-4 px-6 py-5">
                <x-input name="current_password" id="modal_current_password" type="password" label="Password saat ini" icon="lock" bag="updatePassword" required autocomplete="current-password" :reveal="true" />
                <x-input name="password" id="modal_password" type="password" label="Password baru" icon="key-round" bag="updatePassword" required autocomplete="new-password" minlength="8" :reveal="true" />
                <x-input name="password_confirmation" id="modal_password_confirmation" type="password" label="Konfirmasi password baru" icon="shield-check" bag="updatePassword" required autocomplete="new-password" :reveal="true" />
            </div>

            <div class="flex justify-end gap-3 rounded-b-2xl border-t border-slate-200 bg-slate-50 px-6 py-3.5 dark:border-slate-800 dark:bg-slate-900/60">
                <x-button variant="secondary" x-on:click="$store.account.close()">Batal</x-button>
                <x-button type="submit" icon="save" :loading="true">Ubah password</x-button>
            </div>
        </form>
    </div>
</div>
