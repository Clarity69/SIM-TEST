<x-guest-layout title="Verifikasi Email" tagline="Satu langkah lagi sebelum mulai.">
    <span class="grid size-12 place-items-center rounded-2xl bg-primary-50 text-primary-700 dark:bg-primary-500/15 dark:text-primary-300"><x-icon name="mail" class="size-6" /></span>
    <h1 class="mt-5 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Verifikasi email Anda</h1>
    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Kami sudah mengirim tautan verifikasi ke <strong class="text-slate-700 dark:text-slate-200">{{ auth()->user()->email }}</strong>. Klik tautan tersebut untuk membuka dashboard.</p>

    <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">Belum menerima email dalam beberapa menit? Periksa folder Spam/Promosi, lalu kirim ulang tautan. Tautan berlaku {{ config('auth.verification.expire', 60) }} menit.</p>

    @if (session('status') == 'verification-link-sent')
        <div class="mt-6 flex gap-2 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200" role="status">
            <x-icon name="circle-check" class="size-4 shrink-0 translate-y-0.5" /> Tautan verifikasi baru sudah dikirim.
        </div>
    @endif

    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
        <form method="POST" action="{{ route('verification.send') }}" class="flex-1" x-data="{ submitting: false }" x-on:submit="submitting = true">
            @csrf
            <x-button type="submit" class="w-full" icon="send" :loading="true">Kirim ulang tautan</x-button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-button type="submit" variant="secondary" class="w-full" icon="log-out">Keluar</x-button>
        </form>
    </div>
</x-guest-layout>
