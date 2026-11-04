<x-guest-layout title="Lupa Password" tagline="Lupa password? Kami bantu atur ulang.">
    <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Lupa password</h1>
    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Masukkan email akun Anda. Kami akan mengirim tautan untuk membuat password baru.</p>

    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5" x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf
        <x-input name="email" type="email" label="Email" icon="mail" required autofocus autocomplete="username" />
        <x-button type="submit" class="w-full" icon="send" :loading="true">Kirim tautan reset</x-button>
    </form>

    <p class="mt-8 text-center text-sm">
        <a href="{{ route('login') }}" class="inline-flex items-center gap-1 font-semibold text-primary-700 hover:text-primary-800 dark:text-primary-300"><x-icon name="chevron-left" class="size-4" /> Kembali ke halaman masuk</a>
    </p>
</x-guest-layout>
