{{-- Potongan tabel: dipakai halaman index dan respons pencarian live (AJAX). --}}
@php($adaFilter = request()->filled('search') || request()->filled('prodi_id') || request()->filled('jenis_kelamin'))

@if ($mahasiswas->isEmpty())
    @if ($adaFilter)
        <x-empty-state icon="search" title="Tidak ada hasil" description="Tidak ada mahasiswa yang cocok dengan pencarian atau filter. Coba kata kunci lain atau reset filter.">
            <x-button variant="secondary" icon="refresh-cw" :href="route('mahasiswa.index')">Reset filter</x-button>
        </x-empty-state>
    @else
        <x-empty-state title="Belum ada mahasiswa" description="Data mahasiswa yang ditambahkan akan tampil di sini.">
            <x-button icon="plus" :href="route('mahasiswa.create')">Tambah Mahasiswa</x-button>
        </x-empty-state>
    @endif
@else
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
            <caption class="sr-only">Daftar mahasiswa, {{ $mahasiswas->total() }} data</caption>
            <thead class="bg-slate-50 dark:bg-slate-800/50">
                <tr>
                    <x-th-sort column="nim" label="NIM" />
                    <x-th-sort column="nama" label="Nama" />
                    <x-th-sort column="prodi" label="Program Studi" />
                    <x-th-sort column="jenis_kelamin" label="Jenis Kelamin" />
                    <th scope="col" class="table-th text-right"><span class="sr-only">Aksi</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @foreach ($mahasiswas as $mahasiswa)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                        <td class="table-td whitespace-nowrap font-medium tabular-nums text-slate-900 dark:text-white">{{ $mahasiswa->nim }}</td>
                        <td class="table-td">
                            <div class="flex items-center gap-3">
                                <x-avatar :name="$mahasiswa->nama" :jenis-kelamin="$mahasiswa->jenis_kelamin" />
                                <a href="{{ route('mahasiswa.show', $mahasiswa) }}" class="font-medium text-slate-900 hover:text-primary-700 dark:text-white dark:hover:text-primary-300">{{ $mahasiswa->nama }}</a>
                            </div>
                        </td>
                        <td class="table-td text-slate-600 dark:text-slate-300">
                            <span class="hidden md:inline">{{ $mahasiswa->prodi->nama_prodi }}</span>
                            <span class="md:hidden">{{ $mahasiswa->prodi->label() }}</span>
                        </td>
                        <td class="table-td"><x-badge :tone="$mahasiswa->jenis_kelamin === 'Laki-laki' ? 'primary' : 'secondary'">{{ $mahasiswa->jenis_kelamin }}</x-badge></td>
                        <td class="table-td">
                            <div class="flex justify-end gap-1">
                                <x-button variant="ghost" size="icon" :href="route('mahasiswa.show', $mahasiswa)" title="Lihat detail">
                                    <x-icon name="eye" class="size-4" /><span class="sr-only">Lihat {{ $mahasiswa->nama }}</span>
                                </x-button>
                                <x-button variant="ghost" size="icon" :href="route('mahasiswa.edit', $mahasiswa)" title="Edit">
                                    <x-icon name="pencil" class="size-4" /><span class="sr-only">Edit {{ $mahasiswa->nama }}</span>
                                </x-button>
                                <x-button variant="ghost" size="icon" class="text-red-600 hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-500/10" title="Hapus"
                                    x-on:click="$store.confirm.ask({{ \Illuminate\Support\Js::from(['title' => 'Hapus mahasiswa?', 'message' => 'Data '.$mahasiswa->nama.' ('.$mahasiswa->nim.') akan dihapus.', 'action' => route('mahasiswa.destroy', $mahasiswa)]) }})">
                                    <x-icon name="trash-2" class="size-4" /><span class="sr-only">Hapus {{ $mahasiswa->nama }}</span>
                                </x-button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($mahasiswas->hasPages())
        <div class="border-t border-slate-200 px-5 py-4 dark:border-slate-800">{{ $mahasiswas->links() }}</div>
    @else
        <p class="border-t border-slate-200 px-5 py-4 text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400">Menampilkan {{ $mahasiswas->total() }} data</p>
    @endif
@endif
