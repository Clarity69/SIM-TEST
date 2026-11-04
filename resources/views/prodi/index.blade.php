<x-app-layout title="Program Studi" :breadcrumbs="['Program Studi' => null]">
    <x-page-header title="Program Studi" description="Daftar program studi S1 beserta kaprodi dan jumlah mahasiswanya.">
        <x-button icon="plus" :href="route('prodi.create')">Tambah Prodi</x-button>
    </x-page-header>

    <section class="card" aria-label="Tabel program studi">
        <form method="GET" action="{{ route('prodi.index') }}" role="search" class="flex flex-col gap-3 border-b border-slate-200 p-4 sm:flex-row sm:p-5 dark:border-slate-800">
            <div class="relative flex-1">
                <label for="search" class="sr-only">Cari kode, singkatan, nama prodi, atau fakultas</label>
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                <input id="search" name="search" type="search" value="{{ request('search') }}" placeholder="Cari kode, singkatan, atau nama prodi…" class="form-control pl-9">
            </div>
            <div class="flex gap-2">
                <x-button type="submit" variant="secondary" icon="search">Cari</x-button>
                @if (request()->filled('search'))
                    <x-button variant="ghost" icon="refresh-cw" :href="route('prodi.index')">Reset</x-button>
                @endif
            </div>
        </form>

        @if ($prodis->isEmpty())
            <x-empty-state icon="graduation-cap" :title="request()->filled('search') ? 'Tidak ada hasil' : 'Belum ada program studi'"
                :description="request()->filled('search') ? 'Coba kata kunci lain.' : 'Tambahkan program studi agar mahasiswa bisa didaftarkan.'" />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th scope="col" class="table-th">Kode</th>
                            <th scope="col" class="table-th">Program Studi</th>
                            <th scope="col" class="table-th">Kaprodi</th>
                            <th scope="col" class="table-th text-right">Mahasiswa</th>
                            <th scope="col" class="table-th text-right"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach ($prodis as $prodi)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="table-td whitespace-nowrap">
                                    <span class="font-medium tabular-nums text-slate-900 dark:text-white">{{ $prodi->kode_prodi }}</span>
                                    @if ($prodi->singkatan) <x-badge tone="primary" class="ml-2">{{ $prodi->singkatan }}</x-badge> @endif
                                </td>
                                <td class="table-td">
                                    <a href="{{ route('prodi.show', $prodi) }}" class="font-medium text-slate-900 hover:text-primary-700 dark:text-white dark:hover:text-primary-300">{{ $prodi->nama_prodi }}</a>
                                    @if ($prodi->fakultas) <p class="text-xs text-slate-500 dark:text-slate-400">{{ $prodi->fakultas }}</p> @endif
                                </td>
                                <td class="table-td">
                                    @if ($prodi->kaprodi)
                                        <span class="text-slate-700 dark:text-slate-200">{{ $prodi->kaprodi }}</span>
                                        @if (str_starts_with($prodi->kaprodi_jabatan, 'Plt')) <x-badge tone="warning" class="ml-1">Plt.</x-badge> @endif
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500">Belum diisi</span>
                                    @endif
                                </td>
                                <td class="table-td text-right font-semibold tabular-nums">{{ $prodi->mahasiswas_count }}</td>
                                <td class="table-td">
                                    <div class="flex justify-end gap-1">
                                        <x-button variant="ghost" size="icon" :href="route('prodi.show', $prodi)" title="Lihat detail"><x-icon name="eye" class="size-4" /><span class="sr-only">Lihat {{ $prodi->nama_prodi }}</span></x-button>
                                        <x-button variant="ghost" size="icon" :href="route('prodi.edit', $prodi)" title="Edit"><x-icon name="pencil" class="size-4" /><span class="sr-only">Edit {{ $prodi->nama_prodi }}</span></x-button>
                                        <x-button variant="ghost" size="icon" class="text-red-600 hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-500/10" title="Hapus"
                                            x-on:click="$store.confirm.ask({{ \Illuminate\Support\Js::from(['title' => 'Hapus program studi?', 'message' => $prodi->mahasiswas_count > 0 ? $prodi->nama_prodi.' masih memiliki '.$prodi->mahasiswas_count.' mahasiswa sehingga tidak dapat dihapus.' : $prodi->nama_prodi.' akan dihapus.', 'action' => route('prodi.destroy', $prodi)]) }})">
                                            <x-icon name="trash-2" class="size-4" /><span class="sr-only">Hapus {{ $prodi->nama_prodi }}</span>
                                        </x-button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($prodis->hasPages())
                <div class="border-t border-slate-200 px-5 py-4 dark:border-slate-800">{{ $prodis->links() }}</div>
            @endif
        @endif
    </section>
</x-app-layout>
