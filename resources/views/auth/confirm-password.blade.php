<x-guest-layout title="Konfirmasi Password" tagline="Area aman — konfirmasi identitas Anda.">
    <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Konfirmasi password</h1>
    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Ini area aman aplikasi. Masukkan password Anda untuk melanjutkan.</p>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-8 space-y-5" x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf
        <x-input name="password" type="password" label="Password" icon="lock" required autofocus autocomplete="current-password" :reveal="true" />
        <x-button type="submit" class="w-full" icon="shield-check" :loading="true">Konfirmasi</x-button>
    </form>
</x-guest-layout>
