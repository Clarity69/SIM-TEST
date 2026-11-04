<x-app-layout title="Profil Saya" :breadcrumbs="['Profil Saya' => null]">
    <x-page-header title="Profil Saya" description="Kelola informasi akun, keamanan, dan notifikasi email." />

    <div class="grid gap-6 lg:grid-cols-[13rem_1fr]">
        <nav aria-label="Bagian profil" class="lg:sticky lg:top-24 lg:self-start">
            <ul class="flex gap-1 overflow-x-auto lg:flex-col">
                @foreach (['profil' => ['user', 'Informasi profil'], 'keamanan' => ['key-round', 'Keamanan'], 'notifikasi' => ['bell', 'Notifikasi'], 'hapus-akun' => ['triangle-alert', 'Zona bahaya']] as $anchor => [$ikon, $label])
                    <li>
                        <a href="#{{ $anchor }}" class="flex items-center gap-2.5 whitespace-nowrap rounded-xl px-3 py-2 text-sm font-medium text-slate-600 hover:bg-white hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-900 dark:hover:text-white">
                            <x-icon :name="$ikon" class="size-4" /> {{ $label }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="space-y-6">
            {{-- Informasi profil --}}
            <form id="profil" method="POST" action="{{ route('profile.update') }}" class="scroll-mt-24" x-data="{ submitting: false }" x-on:submit="submitting = true">
                @csrf
                @method('PATCH')
                <x-card title="Informasi profil" description="Nama dan email yang dipakai untuk login dan menerima notifikasi.">
                    <div class="mb-6 flex items-center gap-4">
                        <x-avatar :name="$user->name" size="lg" />
                        <div>
                            <p class="text-base font-semibold text-slate-900 dark:text-white">{{ $user->name }}</p>
                            @if ($user->hasVerifiedEmail())
                                <p class="mt-0.5 flex items-center gap-1 text-xs font-medium text-emerald-700 dark:text-emerald-400"><x-icon name="circle-check" class="size-3.5" /> Email terverifikasi</p>
                            @else
                                <p class="mt-0.5 flex items-center gap-1 text-xs font-medium text-amber-700 dark:text-amber-400"><x-icon name="circle-alert" class="size-3.5" /> Email belum diverifikasi</p>
                            @endif
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-input name="name" label="Nama lengkap" :value="$user->name" icon="user" required autocomplete="name" />
                        <x-input name="email" type="email" label="Email" :value="$user->email" icon="mail" required autocomplete="email" hint="Mengubah email akan meminta verifikasi ulang." />
                    </div>

                    <x-slot:footer>
                        <x-button type="submit" icon="save" :loading="true">Simpan profil</x-button>
                    </x-slot:footer>
                </x-card>
            </form>

            @unless ($user->hasVerifiedEmail())
                <div class="flex flex-col gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 sm:flex-row sm:items-center sm:justify-between dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100" role="status">
                    <p>Email Anda belum diverifikasi. Cek kotak masuk atau kirim ulang tautan verifikasi.</p>
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <x-button type="submit" variant="secondary" size="sm" icon="send">Kirim ulang</x-button>
                    </form>
                </div>
            @endunless

            {{-- Keamanan --}}
            <form id="keamanan" method="POST" action="{{ route('profile.password.update') }}" class="scroll-mt-24" x-data="{ submitting: false }" x-on:submit="submitting = true">
                @csrf
                @method('PUT')
                <x-card title="Keamanan" description="Gunakan password minimal 8 karakter yang tidak dipakai di layanan lain.">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-input name="current_password" type="password" label="Password saat ini" icon="lock" required autocomplete="current-password" class="sm:col-span-2" />
                        <x-input name="password" type="password" label="Password baru" icon="key-round" required autocomplete="new-password" minlength="8" />
                        <x-input name="password_confirmation" type="password" label="Konfirmasi password baru" icon="shield-check" required autocomplete="new-password" />
                    </div>
                    <x-slot:footer>
                        <x-button type="submit" icon="save" :loading="true">Ubah password</x-button>
                    </x-slot:footer>
                </x-card>
            </form>

            {{-- Notifikasi --}}
            <form id="notifikasi" method="POST" action="{{ route('profile.notifications.update') }}" class="scroll-mt-24"
                x-data="{ aktif: @js((bool) $user->notify_mahasiswa_changes), submitting: false }" x-on:submit="submitting = true">
                @csrf
                @method('PATCH')
                <x-card title="Notifikasi email" description="Email dikirim lewat antrean (queue) sehingga tidak memperlambat aplikasi.">
                    <div class="flex items-start justify-between gap-6">
                        <div>
                            <p id="notif-label" class="text-sm font-medium text-slate-900 dark:text-white">Perubahan data mahasiswa</p>
                            <p id="notif-desc" class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Kirim email ke {{ $user->email }} saat data mahasiswa ditambahkan atau dihapus.@unless ($user->isAdmin()) <strong class="font-medium">Hanya berlaku untuk akun admin.</strong>@endunless</p>
                        </div>
                        <input type="hidden" name="notify_mahasiswa_changes" x-bind:value="aktif ? 1 : 0" value="{{ $user->notify_mahasiswa_changes ? 1 : 0 }}">
                        <button type="button" role="switch" x-bind:aria-checked="aktif.toString()" aria-labelledby="notif-label" aria-describedby="notif-desc"
                            x-on:click="aktif = ! aktif"
                            class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition"
                            x-bind:class="aktif ? 'bg-primary-600 dark:bg-primary-500' : 'bg-slate-300 dark:bg-slate-700'">
                            <span class="inline-block size-5 rounded-full bg-white shadow transition" x-bind:class="aktif ? 'translate-x-[22px]' : 'translate-x-0.5'"></span>
                        </button>
                    </div>
                    <x-slot:footer>
                        <x-button type="submit" icon="save" :loading="true">Simpan preferensi</x-button>
                    </x-slot:footer>
                </x-card>
            </form>

            {{-- Zona bahaya --}}
            <section id="hapus-akun" class="card scroll-mt-24 border-red-200 dark:border-red-500/30"
                x-data="{ open: @js($errors->userDeletion->isNotEmpty()) }">
                <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                    <div>
                        <h2 class="text-base font-semibold text-red-700 dark:text-red-400">Hapus akun</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Akun dan semua sesi login akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                    <x-button variant="danger" icon="trash-2" x-on:click="open = true">Hapus akun</x-button>
                </div>

                <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center"
                    role="dialog" aria-modal="true" aria-labelledby="hapus-akun-title" x-on:keydown.escape.window="open = false">
                    <div class="fixed inset-0 bg-slate-950/50 backdrop-blur-sm" x-on:click="open = false" aria-hidden="true"></div>
                    <form method="POST" action="{{ route('profile.destroy') }}" class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900"
                        x-trap.noscroll.inert="open" x-data="{ submitting: false }" x-on:submit="submitting = true">
                        @csrf
                        @method('DELETE')
                        <h2 id="hapus-akun-title" class="text-base font-semibold text-slate-900 dark:text-white">Yakin ingin menghapus akun?</h2>
                        <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-300">Masukkan password untuk mengonfirmasi penghapusan akun {{ $user->email }}.</p>
                        <x-input name="password" type="password" label="Password" id="hapus_password" bag="userDeletion" required autocomplete="current-password" class="mt-4" />
                        <div class="mt-6 flex justify-end gap-3">
                            <x-button variant="secondary" x-on:click="open = false">Batal</x-button>
                            <x-button type="submit" variant="danger" icon="trash-2" :loading="true">Hapus akun</x-button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
