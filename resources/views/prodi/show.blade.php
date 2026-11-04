<x-app-layout title="{{ $prodi->nama_prodi }}" :breadcrumbs="['Program Studi' => route('prodi.index'), $prodi->nama_prodi => null]">
    <x-page-header :title="$prodi->nama_prodi" description="Kode PDDIKTI {{ $prodi->kode_prodi }}{{ $prodi->fakultas ? ' · '.$prodi->fakultas : '' }}">
        <x-button variant="secondary" icon="printer" :href="route('mahasiswa.pdf', ['prodi_id' => $prodi->id])" target="_blank">Cetak PDF</x-button>
        <x-button variant="secondary" icon="pencil" :href="route('prodi.edit', $prodi)">Edit</x-button>
    </x-page-header>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat-card label="Jumlah mahasiswa" :value="$mahasiswas->total()" icon="users" />
        <x-card class="sm:col-span-2">
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $prodi->kaprodi_jabatan }}</p>
            <p class="mt-1 text-lg font-semibold text-slate-900 dark:text-white">{{ $prodi->kaprodi ?? 'Belum diisi' }}</p>
            @if ($prodi->kaprodi_nidn) <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">NIDN {{ $prodi->kaprodi_nidn }}</p> @endif
            @unless ($prodi->kaprodi)
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Laporan PDF prodi ini akan memakai baris tanda tangan kosong sampai nama kaprodi diisi.</p>
            @endunless
        </x-card>
    </div>

    <x-card title="Mahasiswa" description="Diurutkan berdasarkan nama" :padding="false">
        @if ($mahasiswas->isEmpty())
            <x-empty-state title="Belum ada mahasiswa" description="Belum ada mahasiswa yang terdaftar pada program studi ini." />
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th scope="col" class="table-th">NIM</th>
                            <th scope="col" class="table-th">Nama</th>
                            <th scope="col" class="table-th">Jenis Kelamin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach ($mahasiswas as $mahasiswa)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="table-td font-medium tabular-nums">{{ $mahasiswa->nim }}</td>
                                <td class="table-td">
                                    <a href="{{ route('mahasiswa.show', $mahasiswa) }}" class="flex items-center gap-3 font-medium text-slate-900 hover:text-primary-700 dark:text-white dark:hover:text-primary-300">
                                        <x-avatar :name="$mahasiswa->nama" :jenis-kelamin="$mahasiswa->jenis_kelamin" size="sm" /> {{ $mahasiswa->nama }}
                                    </a>
                                </td>
                                <td class="table-td"><x-badge :tone="$mahasiswa->jenis_kelamin === 'Laki-laki' ? 'primary' : 'secondary'">{{ $mahasiswa->jenis_kelamin }}</x-badge></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($mahasiswas->hasPages())
                <div class="border-t border-slate-200 px-5 py-4 dark:border-slate-800">{{ $mahasiswas->links() }}</div>
            @endif
        @endif
    </x-card>
</x-app-layout>
