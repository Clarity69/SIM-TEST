<x-guest-layout title="Masuk">
    <span class="mb-5 inline-flex items-center gap-2 rounded-lg bg-primary-50 px-3 py-2 text-xs font-semibold text-primary-700 dark:bg-primary-500/10 dark:text-primary-300"><x-icon name="shield-check" class="size-4" />Portal administrasi kampus</span>
    <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Masuk ke akun Anda</h1>
    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Gunakan email dan password yang terdaftar.</p>

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5" x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf
        <x-input name="email" type="email" label="Email" icon="mail" required autofocus autocomplete="username" placeholder="nama@stitek.ac.id" />
        <x-input name="password" type="password" label="Password" icon="lock" required autocomplete="current-password" :reveal="true">
            <x-slot:aside>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs font-semibold text-primary-700 hover:text-primary-800 dark:text-primary-300">Lupa password?</a>
                @endif
            </x-slot:aside>
        </x-input>

        <label for="remember_me" class="flex items-center gap-2.5 text-sm text-slate-700 dark:text-slate-300">
            <input id="remember_me" type="checkbox" name="remember" class="size-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-900">
            Ingat saya
        </label>

        <x-button type="submit" class="w-full" icon="log-out" :loading="true">Masuk</x-button>
    </form>

    @if (config('auth.registration_enabled'))
    <p class="mt-8 text-center text-sm text-slate-500 dark:text-slate-400">
        Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-primary-700 hover:text-primary-800 dark:text-primary-300">Daftar sekarang</a>
    </p>
    @else
        <p class="mt-8 border-t border-slate-200 pt-6 text-center text-xs leading-relaxed text-slate-500 dark:border-slate-800 dark:text-slate-400">Belum memiliki akses? Hubungi administrator kampus untuk mendapatkan akun.</p>
    @endif
</x-guest-layout>
