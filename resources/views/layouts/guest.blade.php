<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    @include('layouts.partials.head')
    <title>{{ $title }} · SIM Mahasiswa {{ config('stitek.singkatan') }}</title>
</head>
<body class="h-full bg-white dark:bg-slate-950" x-data>
    <div class="grid min-h-full lg:grid-cols-[minmax(0,7fr)_minmax(0,5fr)]">
        {{-- Panel branding --}}
        <aside class="relative hidden flex-col justify-between overflow-hidden bg-gradient-to-br from-primary-950 via-primary-800 to-primary-600 p-12 text-white lg:flex xl:p-16">
            <div class="flex items-center gap-3.5">
                <img src="{{ asset('images/logo-stitek.webp') }}" alt="Logo STITEK Bontang" width="52" height="52" class="size-13 rounded-full">
                <div>
                    <p class="text-base font-semibold">{{ config('stitek.singkatan') }}</p>
                    <p class="text-xs text-primary-100">{{ config('stitek.nama') }}</p>
                </div>
            </div>

            <div class="max-w-lg space-y-5">
                <span class="inline-flex items-center gap-2 rounded-full border border-secondary-400/50 bg-secondary-500/15 px-3 py-1 text-xs font-medium text-secondary-100">
                    <span class="size-2 rounded-full bg-secondary-500" aria-hidden="true"></span> Sistem Informasi Akademik
                </span>
                <p class="text-4xl font-bold leading-tight tracking-tight text-balance">{{ $tagline }}</p>
                <p class="text-base leading-relaxed text-primary-100 text-pretty">Pantau statistik per program studi, cari dan saring data mahasiswa, lalu cetak atau kirim laporan PDF ber-kop resmi kampus.</p>
                <ul class="space-y-3.5 pt-2">
                    @foreach (['layout-dashboard' => 'Dashboard statistik real-time', 'search' => 'Pencarian & filter instan', 'printer' => 'Laporan PDF dengan kop surat'] as $ikon => $fitur)
                        <li class="flex items-center gap-3 text-sm font-medium">
                            <span class="grid size-9 place-items-center rounded-xl border border-white/15 bg-white/10"><x-icon :name="$ikon" class="size-[18px]" /></span>
                            {{ $fitur }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <address class="space-y-1 text-xs not-italic text-primary-100">
                <p>{{ config('stitek.kontak.alamat') }}</p>
                <p>{{ config('stitek.kontak.telepon') }} · {{ config('stitek.kontak.email') }}</p>
            </address>
        </aside>

        {{-- Panel form --}}
        <main class="flex flex-col justify-center px-6 py-12 sm:px-12">
            <div class="mx-auto w-full max-w-sm">
                <div class="mb-8 flex items-center gap-3 lg:hidden">
                    <img src="{{ asset('images/logo-stitek.webp') }}" alt="Logo STITEK Bontang" width="44" height="44" class="size-11 rounded-full">
                    <div>
                        <p class="text-base font-bold text-slate-900 dark:text-white">SIM Mahasiswa</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ config('stitek.nama') }}</p>
                    </div>
                </div>

                @if (session('status'))
                    <div class="mb-6 flex gap-2 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200" role="status">
                        <x-icon name="circle-check" class="size-4 shrink-0 translate-y-0.5" /> {{ session('status') }}
                    </div>
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>

    <button type="button" class="fixed right-4 top-4 grid size-9 place-items-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
        x-on:click="$store.theme.toggle()" x-bind:aria-pressed="$store.theme.dark.toString()">
        <span class="sr-only">Mode gelap</span>
        <x-icon name="moon" class="size-[18px]" x-show="! $store.theme.dark" />
        <x-icon name="sun" class="size-[18px]" x-show="$store.theme.dark" x-cloak />
    </button>
</body>
</html>
