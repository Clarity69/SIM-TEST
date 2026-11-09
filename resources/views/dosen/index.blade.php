<x-app-layout title="Dosen" :breadcrumbs="['Dosen' => null]">
    <x-page-header title="Dosen" eyebrow="Administrasi akademik" description="Kelola data dosen, program studi, dan status aktif.">
        @if ($ready)<x-button icon="plus" :href="route('dosen.create')">Tambah Dosen</x-button>@endif
    </x-page-header>
    @if (! $ready)
        <x-academic-module-pending />
    @else
        <x-card :padding="false">
            <form method="GET" action="{{ route('dosen.index') }}" role="search" class="flex flex-wrap items-end gap-3 border-b border-slate-100 p-5 dark:border-slate-800">
                <x-input name="search" label="Cari dosen" :value="request('search')" placeholder="Nama atau NIDN / NUPTK" class="min-w-0 flex-1" />
                <x-select name="aktif" label="Status" :value="request('aktif')" :options="[1 => 'Aktif', 0 => 'Nonaktif']" placeholder="Semua status" class="w-40" />
                <x-button type="submit" variant="secondary" icon="search">Cari</x-button>
                @if (request()->filled('search') || request()->filled('aktif'))<x-button variant="ghost" :href="route('dosen.index')">Reset</x-button>@endif
            </form>
            @if ($items->isEmpty())
                <x-empty-state icon="user" title="Belum ada data yang sesuai" description="Tambahkan data atau ubah pencarian dan filter." />
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                            <th scope="col" class="table-th">NIDN / NUPTK</th><th scope="col" class="table-th">Nama</th><th scope="col" class="table-th">Program Studi</th>
                            <th scope="col" class="table-th">Email</th>
                            <th scope="col" class="table-th">Status</th><th scope="col" class="table-th"><span class="sr-only">Aksi</span></th>
                        </tr></thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($items as $item)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                    <td class="table-td tabular-nums">{{ $item->nidn ?? '—' }}</td>
                                    <td class="table-td font-medium">{{ $item->nama }}</td>
                                    <td class="table-td">{{ $item->prodi->nama_prodi }}</td>
                                    <td class="table-td">{{ $item->email ?? '—' }}</td>
                                    <td class="table-td"><x-badge :tone="$item->aktif ? 'success' : 'neutral'">{{ $item->aktif ? 'Aktif' : 'Nonaktif' }}</x-badge></td>
                                    <td class="table-td">
                                        <div class="flex justify-end gap-2">
                                            <x-button variant="ghost" icon="pencil" :href="route('dosen.edit', $item)" :aria-label="'Edit '.$item->nama">Edit</x-button>
                                            @can('admin')
                                                <x-button variant="danger" icon="trash-2" :aria-label="'Hapus '.$item->nama"
                                                    x-on:click="$store.confirm.ask({ title: 'Hapus data?', message: 'Data akan dihapus permanen.', action: {{ \Illuminate\Support\Js::from(route('dosen.destroy', $item)) }} })">Hapus</x-button>
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
