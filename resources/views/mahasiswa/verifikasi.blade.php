<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    @include('layouts.partials.head')
    <meta name="robots" content="noindex, nofollow">
    <title>Verifikasi Kartu · SIM Mahasiswa {{ config('stitek.singkatan') }}</title>
</head>
<body class="flex min-h-full items-center justify-center bg-slate-50 px-4 py-10 dark:bg-slate-950">
    @php
        $valid = $mahasiswa && ! $mahasiswa->trashed() && in_array($mahasiswa->status, ['Aktif', 'Cuti'], true);
    @endphp
    <main class="w-full max-w-sm">
        <div class="mb-6 flex items-center justify-center gap-3">
            <img src="{{ asset('images/logo-stitek.webp') }}" alt="" width="40" height="40" class="size-10 rounded-full">
            <div>
                <p class="text-sm font-bold text-slate-900 dark:text-white">{{ config('stitek.nama') }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Verifikasi Kartu Tanda Mahasiswa</p>
            </div>
        </div>

        <div class="card overflow-hidden">
            @if (! $mahasiswa)
                <div class="p-6 text-center">
                    <span class="mx-auto grid size-12 place-items-center rounded-full bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-300"><x-icon name="circle-alert" /></span>
                    <h1 class="mt-3 text-base font-semibold text-slate-900 dark:text-white">Data tidak ditemukan</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Mahasiswa pada kartu ini sudah tidak terdaftar di {{ config('stitek.singkatan') }}.</p>
                </div>
            @else
                <div @class(['flex items-center gap-2 px-5 py-3 text-sm font-semibold',
                    'bg-emerald-50 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-200' => $valid,
                    'bg-amber-50 text-amber-800 dark:bg-amber-500/15 dark:text-amber-200' => ! $valid])
                    role="status">
                    <x-icon :name="$valid ? 'badge-check' : 'triangle-alert'" class="size-5" />
                    {{ $valid ? 'Kartu valid — mahasiswa terdaftar' : ($mahasiswa->trashed() ? 'Kartu tidak berlaku — data sudah dihapus' : 'Kartu tidak berlaku — status '.$mahasiswa->status) }}
                </div>
                <div class="flex items-center gap-4 p-5">
                    <x-avatar :name="$mahasiswa->nama" :src="$mahasiswa->trashed() ? null : $mahasiswa->fotoUrl()" size="lg" class="size-16" />
                    <div class="min-w-0">
                        <h1 class="truncate text-base font-bold text-slate-900 dark:text-white">{{ $mahasiswa->nama }}</h1>
                        <p class="text-sm tabular-nums text-primary-700 dark:text-primary-300">{{ $mahasiswa->nim }}</p>
                    </div>
                </div>
                <dl class="divide-y divide-slate-100 border-t border-slate-100 text-sm dark:divide-slate-800 dark:border-slate-800">
                    @foreach (['Program studi' => $mahasiswa->prodi?->nama_prodi, 'Angkatan' => $mahasiswa->angkatan, 'Status' => $mahasiswa->status] as $label => $nilai)
                        <div class="flex justify-between gap-4 px-5 py-2.5">
                            <dt class="text-slate-500 dark:text-slate-400">{{ $label }}</dt>
                            <dd class="text-right font-medium text-slate-900 dark:text-white">{{ $nilai ?? '—' }}</dd>
                        </div>
                    @endforeach
                </dl>
            @endif
        </div>
        <p class="mt-4 text-center text-xs text-slate-500 dark:text-slate-400">Diperiksa {{ now()->translatedFormat('d F Y H:i') }} WITA · {{ config('stitek.kontak.telepon') }}</p>
    </main>
</body>
</html>
