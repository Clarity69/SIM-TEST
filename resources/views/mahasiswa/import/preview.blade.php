@php
    $selesai = $sesi['hasil'] !== null;
    $jumlahValid = count($sesi['valid']);
    $jumlahInvalid = count($sesi['invalid']);
    // Gabungkan untuk tabel pratinjau, urut nomor baris; tampilkan maksimal 100 baris.
    $semua = collect($sesi['invalid'])->map(fn ($i) => $i + ['valid' => false])
        ->merge(collect($sesi['valid'])->map(fn ($v) => ['baris' => $v['baris'], 'data' => $v['data'] + ['prodi' => $v['prodi']], 'errors' => [], 'valid' => true]))
        ->sortBy('baris')->values();
@endphp
<x-app-layout title="Impor Mahasiswa" :breadcrumbs="['Data Mahasiswa' => route('mahasiswa.index'), 'Impor' => route('mahasiswa.import'), $selesai ? 'Selesai' : 'Pratinjau' => null]">
    <x-page-header title="Impor Data Mahasiswa" :description="$sesi['nama_file'].' · '.number_format($sesi['ukuran'] / 1024, 0, ',', '.').' KB · '.($jumlahValid + $jumlahInvalid).' baris data'">
        <x-button variant="secondary" icon="file-down" :href="route('mahasiswa.import.template')" data-no-prerender>Unduh template</x-button>
    </x-page-header>

    @include('mahasiswa.import._stepper', ['langkah' => $selesai ? 3 : 2])

    @if ($selesai)
        <x-card>
            <div class="flex flex-col items-center py-6 text-center">
                <span class="grid size-14 place-items-center rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300"><x-icon name="circle-check" class="size-8" /></span>
                <h2 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">{{ $sesi['hasil']['berhasil'] }} mahasiswa berhasil diimpor</h2>
                <p class="mt-1 max-w-md text-sm text-slate-500 dark:text-slate-400">
                    {{ $jumlahInvalid }} baris bermasalah dilewati{{ $sesi['hasil']['dilewati'] ? ' dan '.$sesi['hasil']['dilewati'].' baris dilewati karena NIM-nya ditambahkan pengguna lain saat pratinjau' : '' }}.
                </p>
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    @if ($jumlahInvalid)
                        <x-button variant="secondary" icon="download" :href="route('mahasiswa.import.report', $token)" data-no-prerender>Unduh laporan baris bermasalah</x-button>
                    @endif
                    <x-button variant="secondary" icon="upload" :href="route('mahasiswa.import')">Impor file lain</x-button>
                    <x-button icon="users" :href="route('mahasiswa.index', ['sort' => 'created_at', 'direction' => 'desc'])">Lihat Data Mahasiswa</x-button>
                </div>
            </div>
        </x-card>
    @else
        <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
            <x-card :padding="false">
                <div class="flex flex-wrap items-center justify-between gap-3 px-5 pt-5 sm:px-6">
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-sm font-semibold text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-200"><x-icon name="circle-check" class="size-4" /> {{ $jumlahValid }} baris valid</span>
                        @if ($jumlahInvalid)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-3 py-1 text-sm font-semibold text-red-800 dark:bg-red-500/15 dark:text-red-200"><x-icon name="circle-alert" class="size-4" /> {{ $jumlahInvalid }} baris bermasalah</span>
                        @endif
                    </div>
                    <a href="{{ route('mahasiswa.import') }}" class="text-sm font-semibold text-primary-700 hover:text-primary-800 dark:text-primary-300">Ganti file</a>
                </div>

                <div class="mt-4 max-h-[32rem] overflow-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                        <thead class="sticky top-0 bg-slate-50 dark:bg-slate-800">
                            <tr>
                                <th scope="col" class="table-th">Baris</th>
                                <th scope="col" class="table-th">NIM</th>
                                <th scope="col" class="table-th">Nama</th>
                                <th scope="col" class="table-th">Prodi</th>
                                <th scope="col" class="table-th">Angkatan</th>
                                <th scope="col" class="table-th">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @foreach ($semua->take(100) as $item)
                                <tr @class(['bg-red-50/70 dark:bg-red-500/5' => ! $item['valid']])>
                                    <td class="table-td tabular-nums text-slate-500 dark:text-slate-400">{{ $item['baris'] }}</td>
                                    <td class="table-td whitespace-nowrap font-medium tabular-nums text-slate-900 dark:text-white">{{ $item['data']['nim'] ?? '—' }}</td>
                                    <td class="table-td min-w-40 text-slate-700 dark:text-slate-200">{{ $item['data']['nama'] ?? '—' }}</td>
                                    <td class="table-td text-slate-600 dark:text-slate-300">{{ $item['data']['prodi'] ?: '—' }}</td>
                                    <td class="table-td tabular-nums text-slate-600 dark:text-slate-300">{{ $item['data']['angkatan'] ?? '—' }}</td>
                                    <td class="table-td min-w-56">
                                        @if ($item['valid'])
                                            <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-700 dark:text-emerald-400"><x-icon name="circle-check" class="size-3.5" /> Siap diimpor</span>
                                        @else
                                            <ul class="space-y-0.5 text-xs font-medium text-red-700 dark:text-red-400">
                                                @foreach ($item['errors'] as $error)
                                                    <li class="flex gap-1"><x-icon name="circle-alert" class="mt-px size-3.5 shrink-0" /> {{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if ($semua->count() > 100)
                        <p class="border-t border-slate-200 px-5 py-3 text-xs text-slate-500 dark:border-slate-800 dark:text-slate-400">Menampilkan 100 dari {{ $semua->count() }} baris. Semua baris valid tetap akan diimpor.</p>
                    @endif
                </div>

                <x-slot:footer>
                    <p class="mr-auto text-xs text-slate-500 dark:text-slate-400">Baris bermasalah dilewati dan bisa diunduh sebagai laporan.</p>
                    @if ($jumlahInvalid)
                        <x-button variant="secondary" icon="download" :href="route('mahasiswa.import.report', $token)" data-no-prerender>Laporan</x-button>
                    @endif
                    @if ($jumlahValid > 0)
                        <form method="POST" action="{{ route('mahasiswa.import.store', $token) }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
                            @csrf
                            <x-button type="submit" icon="upload" :loading="true">Impor {{ $jumlahValid }} baris</x-button>
                        </form>
                    @else
                        <x-button variant="secondary" icon="upload" :href="route('mahasiswa.import')">Perbaiki file & unggah ulang</x-button>
                    @endif
                </x-slot:footer>
            </x-card>

            @include('mahasiswa.import._panduan')
        </div>
    @endif
</x-app-layout>
