<x-app-layout title="Mata Kuliah" :breadcrumbs="['Mata Kuliah' => null]">
    <x-page-header title="Mata Kuliah" eyebrow="Administrasi akademik" description="Kelola data mata kuliah, program studi, dan status aktif.">
        @if ($ready)<x-button icon="plus" :href="route('mata-kuliah.create')">Tambah Mata Kuliah</x-button>@endif
    </x-page-header>
    @if (! $ready)
        <x-academic-module-pending />
    @else
        <x-card :padding="false">
            <form method="GET" action="{{ route('mata-kuliah.index') }}" role="search" class="flex flex-wrap items-end gap-3 border-b border-slate-100 p-5 dark:border-slate-800">
                <x-input name="search" label="Cari mata kuliah" :value="request('search')" placeholder="Nama atau Kode" class="min-w-0 flex-1" />
                <x-select name="aktif" label="Status" :value="request('aktif')" :options="[1 => 'Aktif', 0 => 'Nonaktif']" placeholder="Semua status" class="w-40" />
                <x-button type="submit" variant="secondary" icon="search">Cari</x-button>
                @if (request()->filled('search') || request()->filled('aktif'))<x-button variant="ghost" :href="route('mata-kuliah.index')">Reset</x-button>@endif
            </form>
            @if ($items->isEmpty())
                <x-empty-state icon="file-text" title="Belum ada data yang sesuai" description="Tambahkan data atau ubah pencarian dan filter." />
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                            <th scope="col" class="table-th">Kode</th><th scope="col" class="table-th">Nama</th><th scope="col" class="table-th">Program Studi</th>
                            <th scope="col" class="table-th">SKS / Semester</th>
                            <th scope="col" class="table-th">Status</th><th scope="col" class="table-th"><span class="sr-only">Aksi</span></th>
                        </tr></thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($items as $item)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                    <td class="table-td tabular-nums">{{ $item->kode ?? '—' }}</td>
                                    <td class="table-td font-medium">{{ $item->nama }}</td>
                                    <td class="table-td">{{ $item->prodi->nama_prodi }}</td>
                                    <td class="table-td">{{ $item->sks }} SKS · Semester {{ $item->semester }}</td>
                                    <td class="table-td"><x-badge :tone="$item->aktif ? 'success' : 'neutral'">{{ $item->aktif ? 'Aktif' : 'Nonaktif' }}</x-badge></td>
                                    <td class="table-td">
                                        <div class="flex justify-end gap-2">
                                            <x-button variant="ghost" icon="pencil" :href="route('mata-kuliah.edit', $item)" :aria-label="'Edit '.$item->nama">Edit</x-button>
                                            @can('admin')
                                                <x-button variant="danger" icon="trash-2" :aria-label="'Hapus '.$item->nama"
                                                    x-on:click="$store.confirm.ask({ title: 'Hapus data?', message: 'Data akan dihapus permanen.', action: {{ \Illuminate\Support\Js::from(route('mata-kuliah.destroy', $item)) }} })">Hapus</x-button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-5 py-4 dark:border-slate-800">{{ $items->links() }}</div>
            @endif
        </x-card>
    @endif
</x-app-layout>
