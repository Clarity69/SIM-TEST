<x-guest-layout title="Daftar" tagline="Bergabung untuk mengelola data akademik.">
    <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Buat akun baru</h1>
    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Lengkapi data di bawah. Tautan verifikasi akan dikirim ke email Anda.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5" x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf
        <x-input name="name" label="Nama lengkap" icon="user" required autofocus autocomplete="name" placeholder="Contoh: Siti Rahmawati" />
        <x-input name="email" type="email" label="Email" icon="mail" required autocomplete="username" placeholder="nama@stitek.ac.id" />
        <x-input name="password" type="password" label="Password" icon="lock" required autocomplete="new-password" :reveal="true" hint="Minimal 8 karakter." />
        <x-input name="password_confirmation" type="password" label="Konfirmasi password" icon="shield-check" required autocomplete="new-password" :reveal="true" />
        <x-button type="submit" class="w-full" icon="user-plus" :loading="true">Daftar</x-button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-500 dark:text-slate-400">
        Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-primary-700 hover:text-primary-800 dark:text-primary-300">Masuk</a>
    </p>
</x-guest-layout>
