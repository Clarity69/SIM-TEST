<x-app-layout title="{{ $mahasiswa->nama }}" :breadcrumbs="['Data Mahasiswa' => route('mahasiswa.index'), $mahasiswa->nama => null]">
    <div class="card overflow-hidden">
        <div class="h-24 bg-gradient-to-r from-primary-950 via-primary-800 to-primary-600" aria-hidden="true"></div>
        <div class="flex flex-col gap-4 px-5 pb-6 sm:flex-row sm:items-end sm:justify-between sm:px-6">
            <div class="-mt-10 flex items-end gap-4">
                <x-avatar :name="$mahasiswa->nama" :jenis-kelamin="$mahasiswa->jenis_kelamin" :src="$mahasiswa->fotoUrl()" size="lg" class="size-20 ring-4 ring-white dark:ring-slate-900" />
                <div class="pb-1">
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ $mahasiswa->nama }}</h1>
                    <p class="flex flex-wrap items-center gap-2 text-sm text-slate-500 dark:text-slate-400">{{ $mahasiswa->nim }} · {{ $mahasiswa->prodi->nama_prodi }}
                        <x-badge :tone="\App\Models\Mahasiswa::STATUS[$mahasiswa->status] ?? 'neutral'">{{ $mahasiswa->status }}</x-badge></p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <form method="POST" action="{{ route('mahasiswa.pdf.show.email', $mahasiswa) }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
                    @csrf
                    <x-button type="submit" variant="secondary" icon="send" :loading="true" title="Kirim PDF biodata ke {{ auth()->user()->email }}">Kirim PDF</x-button>
                </form>
                <x-button variant="secondary" icon="printer" :href="route('mahasiswa.pdf.show', $mahasiswa)" target="_blank">Cetak PDF</x-button>
                <x-button variant="secondary" icon="pencil" :href="route('mahasiswa.edit', $mahasiswa)">Edit</x-button>
                <x-button variant="danger-soft" icon="trash-2"
                    x-on:click="$store.confirm.ask({{ \Illuminate\Support\Js::from(['title' => 'Hapus mahasiswa?', 'message' => 'Data '.$mahasiswa->nama.' ('.$mahasiswa->nim.') akan dihapus.', 'action' => route('mahasiswa.destroy', $mahasiswa)]) }})">Hapus</x-button>
            </div>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        @php
            $grup = [
                ['Data Diri', 'user', [
                    'NIM' => $mahasiswa->nim,
                    'Nama lengkap' => $mahasiswa->nama,
                    'Jenis kelamin' => $mahasiswa->jenis_kelamin,
                    'Tanggal lahir' => $mahasiswa->tanggal_lahir?->locale('id')->translatedFormat('d F Y'),
                ]],
                ['Kontak', 'phone', [
                    'Email' => $mahasiswa->email,
                    'Telepon' => $mahasiswa->telepon,
                    'Alamat' => $mahasiswa->alamat,
                ]],
                ['Akademik', 'graduation-cap', [
                    'Program studi' => $mahasiswa->prodi->nama_prodi,
                    'Kode prodi' => $mahasiswa->prodi->kode_prodi,
                    'Angkatan' => $mahasiswa->angkatan,
                    'Status' => $mahasiswa->status,
                    'Terdaftar sejak' => $mahasiswa->created_at?->locale('id')->translatedFormat('d F Y'),
                    'Terakhir diubah' => $mahasiswa->updated_at?->locale('id')->diffForHumans(),
                ]],
            ];
        @endphp
        @foreach ($grup as [$judul, $ikon, $baris])
            <x-card>
                <h2 class="flex items-center gap-2 text-sm font-semibold text-slate-900 dark:text-white">
                    <x-icon :name="$ikon" class="size-4 text-primary-600 dark:text-primary-400" /> {{ $judul }}
                </h2>
                <dl class="mt-4 space-y-3">
                    @foreach ($baris as $label => $nilai)
                        <div>
                            <dt class="text-xs text-slate-500 dark:text-slate-400">{{ $label }}</dt>
                            <dd class="mt-0.5 text-sm font-medium text-slate-900 dark:text-white">{{ filled($nilai) ? $nilai : '—' }}</dd>
                        </div>
                    @endforeach
                </dl>
            </x-card>
        @endforeach
    </div>
</x-app-layout>
