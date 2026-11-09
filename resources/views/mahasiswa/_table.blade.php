{{-- Potongan tabel: dipakai halaman index dan respons pencarian live (AJAX). --}}
@php($adaFilter = request()->hasAny(['search', 'prodi_id', 'jenis_kelamin', 'status', 'angkatan']) && collect(request()->only(['search', 'prodi_id', 'jenis_kelamin', 'status', 'angkatan']))->filter()->isNotEmpty())

@if ($mahasiswas->isEmpty())
    @if ($mahasiswas->total() > 0)
        <x-empty-state icon="search" title="Halaman ini tidak berisi data" description="Data mahasiswa masih tersedia. Kembali ke halaman pertama untuk melihatnya.">
            <x-button variant="secondary" icon="arrow-left" :href="route('mahasiswa.index', array_merge(request()->except('page'), ['page' => 1]))">Ke halaman pertama</x-button>
        </x-empty-state>
    @elseif ($adaFilter)
        <x-empty-state icon="search" title="Tidak ada hasil" description="Tidak ada mahasiswa yang cocok dengan pencarian atau filter. Coba kata kunci lain atau reset filter.">
            <x-button variant="secondary" icon="refresh-cw" :href="route('mahasiswa.index')">Reset filter</x-button>
        </x-empty-state>
    @else
        <x-empty-state title="Belum ada mahasiswa" description="Data mahasiswa yang ditambahkan akan tampil di sini.">
            <x-button icon="plus" :href="route('mahasiswa.create')">Tambah Mahasiswa</x-button>
        </x-empty-state>
    @endif
@else
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-4 py-4 sm:px-5 dark:border-slate-800">
        <div class="flex items-center gap-2.5">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Daftar mahasiswa</h2>
            <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium tabular-nums text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ number_format($mahasiswas->total(), 0, ',', '.') }} data</span>
        </div>
        <span class="text-xs text-slate-500 dark:text-slate-400">{{ $mahasiswas->firstItem() }}–{{ $mahasiswas->lastItem() }} dari {{ number_format($mahasiswas->total(), 0, ',', '.') }} mahasiswa</span>
    </div>

    <div class="md:hidden">
        <label class="flex min-h-12 items-center gap-3 border-b border-slate-100 px-4 text-xs font-medium text-slate-600 dark:border-slate-800 dark:text-slate-300">
            <input type="checkbox" x-bind:checked="allOnPageSelected()" x-bind:indeterminate="someOnPageSelected()" x-on:change="toggleAllOnPage($event.target.checked)" class="size-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-900">
            Pilih semua di halaman ini
        </label>
        <ul class="divide-y divide-slate-100 dark:divide-slate-800" aria-label="Daftar mahasiswa versi mobile">
            @foreach ($mahasiswas as $mahasiswa)
                <li class="p-4" x-bind:class="selected.includes('{{ $mahasiswa->id }}') ? 'bg-primary-50/60 dark:bg-primary-500/5' : ''">
                    <div class="flex items-center gap-3">
                        <label class="grid min-h-11 min-w-6 place-items-center">
                            <input type="checkbox" value="{{ $mahasiswa->id }}" x-model="selected" aria-label="Pilih {{ $mahasiswa->nama }}" class="size-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-900">
                        </label>
                        <x-avatar :name="$mahasiswa->nama" :src="$mahasiswa->fotoUrl()" :jenis-kelamin="$mahasiswa->jenis_kelamin" />
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('mahasiswa.show', $mahasiswa) }}" class="block break-words text-sm font-semibold leading-relaxed text-slate-900 hover:text-primary-700 dark:text-white dark:hover:text-primary-300">{{ $mahasiswa->nama }}</a>
                            <p class="mt-0.5 text-xs tabular-nums text-slate-500 dark:text-slate-400">{{ $mahasiswa->nim }}</p>
                        </div>
                        <x-badge :tone="\App\Models\Mahasiswa::STATUS[$mahasiswa->status] ?? 'neutral'">{{ $mahasiswa->status }}</x-badge>
                    </div>
                    <p class="mt-3 text-xs leading-relaxed text-slate-500 dark:text-slate-400">{{ $mahasiswa->prodi->nama_prodi }} <span aria-hidden="true">·</span> Angkatan {{ $mahasiswa->angkatan ?? '—' }}</p>
                    <div class="mt-3 flex items-center justify-between gap-2 border-t border-slate-100 pt-2 dark:border-slate-800">
                        <a href="{{ route('mahasiswa.show', $mahasiswa) }}" class="inline-flex min-h-11 items-center gap-1.5 text-xs font-semibold text-primary-700 dark:text-primary-300">Lihat detail <span class="sr-only">{{ $mahasiswa->nama }}</span><x-icon name="arrow-up-right" class="size-3.5" /></a>
                        <div class="flex items-center gap-2">
                            <x-button variant="ghost" size="sm" :href="route('mahasiswa.edit', $mahasiswa)" icon="pencil" class="min-h-11">Edit <span class="sr-only">{{ $mahasiswa->nama }}</span></x-button>
                            <x-button variant="danger-soft" size="icon" class="min-h-11 min-w-11" x-on:click="$store.confirm.ask({{ \Illuminate\Support\Js::from(['title' => 'Hapus mahasiswa?', 'message' => $mahasiswa->nama.' ('.$mahasiswa->nim.') akan dipindahkan ke Sampah dan dapat dipulihkan oleh admin.', 'action' => route('mahasiswa.destroy', $mahasiswa)]) }})">
                                <x-icon name="trash-2" class="size-4" /><span class="sr-only">Hapus {{ $mahasiswa->nama }}</span>
                            </x-button>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="hidden overflow-x-auto md:block">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
            <caption class="sr-only">Daftar mahasiswa, {{ $mahasiswas->total() }} data</caption>
            <thead class="bg-slate-50 dark:bg-slate-800/50">
                <tr>
                    <th scope="col" class="w-10 py-3 pl-5">
                        <input type="checkbox" aria-label="Pilih semua di halaman ini"
                            class="size-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-900"
                            x-bind:checked="allOnPageSelected()" x-bind:indeterminate="someOnPageSelected()"
                            x-on:change="toggleAllOnPage($event.target.checked)">
                    </th>
                    <x-th-sort column="nim" label="NIM" />
                    <x-th-sort column="nama" label="Nama" />
                    <x-th-sort column="prodi" label="Program Studi" />
                    <x-th-sort column="angkatan" label="Angkatan" />
                    <x-th-sort column="status" label="Status" />
                    <th scope="col" class="table-th text-right"><span class="sr-only">Aksi</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800" data-page-ids="{{ json_encode($mahasiswas->pluck('id')->map(fn ($id) => (string) $id)) }}">
                @foreach ($mahasiswas as $mahasiswa)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40" x-bind:class="selected.includes('{{ $mahasiswa->id }}') ? 'bg-primary-50/60 dark:bg-primary-500/5' : ''">
                        <td class="py-3 pl-5">
                            <input type="checkbox" value="{{ $mahasiswa->id }}" x-model="selected" aria-label="Pilih {{ $mahasiswa->nama }}"
                                class="size-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-900">
                        </td>
                        <td class="table-td whitespace-nowrap font-medium tabular-nums text-slate-900 dark:text-white">{{ $mahasiswa->nim }}</td>
                        <td class="table-td">
                            <div class="flex items-center gap-3">
                                <x-avatar :name="$mahasiswa->nama" :jenis-kelamin="$mahasiswa->jenis_kelamin" :src="$mahasiswa->fotoUrl()" />
                                <div class="min-w-0">
                                    <a href="{{ route('mahasiswa.show', $mahasiswa) }}" class="font-medium text-slate-900 hover:text-primary-700 dark:text-white dark:hover:text-primary-300">{{ $mahasiswa->nama }}</a>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $mahasiswa->jenis_kelamin }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="table-td text-slate-600 dark:text-slate-300">
                            <span class="hidden md:inline">{{ $mahasiswa->prodi->nama_prodi }}</span>
                            <span class="md:hidden">{{ $mahasiswa->prodi->label() }}</span>
                        </td>
                        <td class="table-td tabular-nums text-slate-700 dark:text-slate-200">{{ $mahasiswa->angkatan ?? '—' }}</td>
                        <td class="table-td"><x-badge :tone="\App\Models\Mahasiswa::STATUS[$mahasiswa->status] ?? 'neutral'">{{ $mahasiswa->status }}</x-badge></td>
                        <td class="table-td">
                            <div class="flex justify-end gap-1">
                                <x-button variant="ghost" size="icon" :href="route('mahasiswa.show', $mahasiswa)" title="Lihat detail">
                                    <x-icon name="eye" class="size-4" /><span class="sr-only">Lihat {{ $mahasiswa->nama }}</span>
                                </x-button>
                                <x-button variant="ghost" size="icon" :href="route('mahasiswa.edit', $mahasiswa)" title="Edit">
                                    <x-icon name="pencil" class="size-4" /><span class="sr-only">Edit {{ $mahasiswa->nama }}</span>
                                </x-button>
                                <x-button variant="danger-soft" size="icon" title="Hapus"
                                    x-on:click="$store.confirm.ask({{ \Illuminate\Support\Js::from(['title' => 'Hapus mahasiswa?', 'message' => $mahasiswa->nama.' ('.$mahasiswa->nim.') akan dipindahkan ke Sampah dan dapat dipulihkan oleh admin.', 'action' => route('mahasiswa.destroy', $mahasiswa)]) }})">
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
        <div class="border-t border-slate-200 px-5 py-4 dark:border-slate-800">{{ $mahasiswas->onEachSide(1)->links() }}</div>
    @else
        <p class="border-t border-slate-200 px-5 py-4 text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400">Menampilkan {{ $mahasiswas->total() }} data</p>
    @endif
@endif
