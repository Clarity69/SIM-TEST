<x-guest-layout title="Atur Ulang Password" tagline="Buat password baru yang aman.">
    <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Atur ulang password</h1>
    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Password baru minimal 8 karakter.</p>

    <form method="POST" action="{{ route('password.store') }}" class="mt-8 space-y-5" x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <x-input name="email" type="email" label="Email" icon="mail" :value="$request->email" required autocomplete="username" />
        <x-input name="password" type="password" label="Password baru" icon="lock" required autofocus autocomplete="new-password" :reveal="true" />
        <x-input name="password_confirmation" type="password" label="Konfirmasi password baru" icon="shield-check" required autocomplete="new-password" :reveal="true" />
        <x-button type="submit" class="w-full" icon="save" :loading="true">Simpan password baru</x-button>
    </form>
</x-guest-layout>
