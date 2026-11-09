<x-app-layout title="Tambah Pengguna" :breadcrumbs="['Pengguna' => route('users.index'), 'Tambah' => null]">
    <x-page-header title="Tambah Pengguna" eyebrow="Pengaturan akses" description="Buat akun agar pengguna dapat masuk tanpa registrasi publik.">
        <x-button :href="route('users.index')" variant="secondary" icon="arrow-left">Kembali</x-button>
    </x-page-header>

    <form method="POST" action="{{ route('users.store') }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf
        <x-card :padding="false" :sticky-footer="true">
            @if ($errors->any())
                <div role="alert" class="m-5 rounded-xl bg-red-50 p-4 text-sm text-red-700 dark:bg-red-500/10 dark:text-red-300">
                    Periksa kembali isian yang ditandai sebelum menyimpan pengguna.
                </div>
            @endif

            <div class="space-y-6 p-5 sm:p-6">
                <div>
                    <h2 class="text-base font-semibold text-slate-900 dark:text-white">Informasi akun</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kolom bertanda * wajib diisi.</p>
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-input name="name" label="Nama lengkap" icon="user" :required="true" maxlength="255" autocomplete="name" />
                    <x-input name="email" label="Email" type="email" icon="mail" :required="true" maxlength="255" autocomplete="off" />
                    <x-select name="role" label="Role" :options="\App\Models\User::ROLES" :value="\App\Models\User::ROLE_OPERATOR" :required="true" />
                    <p class="self-end text-sm text-slate-500 dark:text-slate-400">Pilih Operator untuk pengelolaan data mahasiswa. Admin juga dapat mengelola akun dan akses pengguna.</p>
                </div>

                <div class="border-t border-slate-200 pt-6 dark:border-slate-800">
                    <h2 class="text-base font-semibold text-slate-900 dark:text-white">Keamanan</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Gunakan password minimal 12 karakter dan sampaikan kepada pemilik akun secara pribadi.</p>
                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                        <x-input name="password" label="Password" type="password" :required="true" :reveal="true" minlength="12" autocomplete="new-password" />
                        <x-input name="password_confirmation" label="Konfirmasi password" type="password" :required="true" :reveal="true" minlength="12" autocomplete="new-password" />
                    </div>
                </div>

                <p class="rounded-xl bg-primary-50 p-4 text-sm text-primary-800 dark:bg-primary-500/10 dark:text-primary-200">Akun yang dibuat admin langsung aktif dan emailnya ditandai terverifikasi. Pastikan alamat email sudah benar sebelum menyimpan.</p>
            </div>

            <x-slot:footer>
                <x-button :href="route('users.index')" variant="secondary">Batal</x-button>
                <x-button type="submit" icon="user-plus" :loading="true">Simpan Pengguna</x-button>
            </x-slot:footer>
        </x-card>
    </form>
</x-app-layout>
