<x-app-layout title="Sampah" :breadcrumbs="['Sampah' => null]">
    <div x-data="{ selected: [], ids: @js($mahasiswas->pluck('id')->map(fn ($id) => (string) $id)) }" class="space-y-6">
        <x-page-header title="Sampah" description="Data mahasiswa yang dihapus disimpan di sini dan dapat dipulihkan.">
            @if ($mahasiswas->total() > 0)
                <form method="POST" action="{{ route('trash.empty') }}" x-ref="emptyForm">
                    @csrf
                    @method('DELETE')
                    <x-button variant="danger-soft" icon="trash-2"
                        x-on:click="$store.confirm.ask({ title: 'Kosongkan Sampah?', message: 'Semua {{ $mahasiswas->total() }} data di Sampah akan dihapus permanen beserta fotonya. Tindakan ini tidak dapat dibatalkan.', confirmLabel: 'Kosongkan', form: $refs.emptyForm })">
                        Kosongkan sampah
                    </x-button>
                </form>
            @endif
        </x-page-header>

        <div class="flex gap-3 rounded-2xl border border-primary-200 bg-primary-50 px-4 py-3.5 text-sm text-primary-900 dark:border-primary-500/30 dark:bg-primary-500/10 dark:text-primary-100" role="note">
            <x-icon name="info" class="mt-0.5 size-[18px] shrink-0" />
            <p>Hanya admin yang dapat membuka halaman ini. Data yang dipulihkan kembali muncul di Data Mahasiswa; data yang dihapus permanen tidak dapat dikembalikan.</p>
        </div>

        <section class="card" aria-label="Data di sampah">
            {{-- Bar aksi massal: muncul bila ada baris terpilih --}}
            <form method="POST" action="{{ route('trash.bulk') }}" x-ref="bulkForm" x-show="selected.length" x-cloak
                class="flex flex-wrap items-center justify-between gap-3 border-b border-primary-100 bg-primary-50/70 px-5 py-3 dark:border-primary-500/20 dark:bg-primary-500/10">
                @csrf
                <input type="hidden" name="aksi" x-ref="aksi" value="pulihkan">
                <template x-for="id in selected" :key="id"><input type="hidden" name="ids[]" :value="id"></template>
                <p class="text-sm font-semibold text-primary-800 dark:text-primary-200"><span x-text="selected.length"></span> data dipilih</p>
                <div class="flex flex-wrap gap-2">
                    <x-button size="sm" variant="secondary" icon="archive-restore" x-on:click="$refs.aksi.value = 'pulihkan'; $refs.bulkForm.submit()">Pulihkan</x-button>
                    <x-button size="sm" variant="danger-soft" icon="trash-2"
                        x-on:click="$refs.aksi.value = 'hapus'; $store.confirm.ask({ title: 'Hapus permanen?', message: selected.length + ' data akan dihapus permanen dan tidak dapat dikembalikan.', confirmLabel: 'Hapus permanen', form: $refs.bulkForm })">Hapus permanen</x-button>
                    <x-button size="sm" variant="ghost" x-on:click="selected = []"><x-icon name="x" class="size-4" /><span class="sr-only">Batalkan pilihan</span></x-button>
                </div>
            </form>

            @if ($mahasiswas->isEmpty())
                <x-empty-state icon="archive-restore" title="Sampah kosong" description="Mahasiswa yang dihapus akan muncul di sini sebelum dihapus permanen." />
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <th scope="col" class="w-10 px-5 py-3">
                                    <input type="checkbox" class="size-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-900"
                                        aria-label="Pilih semua di halaman ini"
                                        x-bind:checked="selected.length === ids.length" x-bind:indeterminate="selected.length > 0 && selected.length < ids.length"
                                        x-on:change="selected = $event.target.checked ? [...ids] : []">
                                </th>
                                <th scope="col" class="table-th">NIM</th>
                                <th scope="col" class="table-th">Nama</th>
                                <th scope="col" class="table-th">Program Studi</th>
                                <th scope="col" class="table-th">Dihapus oleh</th>
                                <th scope="col" class="table-th">Dihapus</th>
                                <th scope="col" class="table-th text-right"><span class="sr-only">Aksi</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @foreach ($mahasiswas as $mahasiswa)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40" x-bind:class="selected.includes('{{ $mahasiswa->id }}') ? 'bg-primary-50/50 dark:bg-primary-500/5' : ''">
                                    <td class="px-5 py-3">
                                        <input type="checkbox" value="{{ $mahasiswa->id }}" x-model="selected" aria-label="Pilih {{ $mahasiswa->nama }}"
                                            class="size-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-900">
                                    </td>
                                    <td class="table-td tabular-nums text-slate-500 dark:text-slate-400">{{ $mahasiswa->nim }}</td>
                                    <td class="table-td">
                                        <div class="flex items-center gap-3">
                                            <x-avatar :name="$mahasiswa->nama" :src="$mahasiswa->fotoUrl()" size="sm" />
                                            <span class="font-medium text-slate-900 dark:text-white">{{ $mahasiswa->nama }}</span>
                                        </div>
                                    </td>
                                    <td class="table-td text-slate-600 dark:text-slate-300">{{ $mahasiswa->prodi?->nama_prodi ?? '—' }}</td>
                                    <td class="table-td text-slate-700 dark:text-slate-200">{{ $mahasiswa->penghapus?->name ?? '—' }}</td>
                                    <td class="table-td whitespace-nowrap text-slate-500 dark:text-slate-400" title="{{ $mahasiswa->deleted_at->locale('id')->translatedFormat('d F Y, H:i') }}">{{ $mahasiswa->deleted_at->locale('id')->diffForHumans() }}</td>
                                    <td class="table-td">
                                        <div class="flex justify-end gap-2">
                                            <form method="POST" action="{{ route('trash.restore', $mahasiswa) }}">
                                                @csrf
                                                @method('PATCH')
                                                <x-button type="submit" size="sm" variant="secondary" icon="archive-restore">Pulihkan</x-button>
                                            </form>
                                            <x-button size="icon" variant="danger-soft" title="Hapus permanen"
                                                x-on:click="$store.confirm.ask({{ \Illuminate\Support\Js::from(['title' => 'Hapus permanen?', 'message' => $mahasiswa->nama.' ('.$mahasiswa->nim.') akan dihapus permanen beserta fotonya.', 'action' => route('trash.destroy', $mahasiswa), 'confirmLabel' => 'Hapus permanen']) }})">
                                                <x-icon name="trash-2" class="size-4" /><span class="sr-only">Hapus permanen {{ $mahasiswa->nama }}</span>
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
                @endif
            @endif
        </section>
    </div>
</x-app-layout>
