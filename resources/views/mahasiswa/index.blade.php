<x-app-layout title="Data Mahasiswa" :breadcrumbs="['Data Mahasiswa' => null]">
    <div x-data="liveTable(350)" class="space-y-6">
        <x-page-header title="Data Mahasiswa" description="Cari, saring, dan kelola data mahasiswa {{ config('stitek.singkatan') }}.">
            <x-button variant="secondary" icon="upload" :href="route('mahasiswa.import')">Impor</x-button>

            <x-dropdown align="right" width="w-60" content-classes="py-1 bg-white dark:bg-slate-800">
                <x-slot name="trigger">
                    <x-button variant="secondary" icon="download">Ekspor <x-icon name="chevron-down" class="size-4 text-slate-400" /></x-button>
                </x-slot>
                <x-slot name="content">
                    @php($js = fn ($route) => \Illuminate\Support\Js::from(route($route)))
                    <p class="px-4 pb-1 pt-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Sesuai filter aktif</p>
                    <a href="{{ route('mahasiswa.export', request()->query()) }}" x-bind:href="{{ $js('mahasiswa.export') }} + query" data-no-prerender
                        class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-700">
                        <x-icon name="file-spreadsheet" class="size-4 text-emerald-600" /> Excel (.xlsx)
                    </a>
                    <a href="{{ route('mahasiswa.export', request()->query() + ['format' => 'csv']) }}" x-bind:href="{{ $js('mahasiswa.export') }} + (query ? query + '&' : '?') + 'format=csv'" data-no-prerender
                        class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-700">
                        <x-icon name="file-text" class="size-4 text-slate-400" /> CSV
                    </a>
                    <a href="{{ route('mahasiswa.pdf', request()->query()) }}" x-bind:href="{{ $js('mahasiswa.pdf') }} + query" target="_blank"
                        class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-700">
                        <x-icon name="printer" class="size-4 text-slate-400" /> Cetak PDF
                    </a>
                    <form method="POST" action="{{ route('mahasiswa.pdf.email', request()->query()) }}" x-bind:action="{{ $js('mahasiswa.pdf.email') }} + query">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-2.5 px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-700">
                            <x-icon name="send" class="size-4 text-slate-400" /> Kirim PDF ke {{ \Illuminate\Support\Str::limit(auth()->user()->email, 22) }}
                        </button>
                    </form>
                </x-slot>
            </x-dropdown>

            <x-button icon="plus" :href="route('mahasiswa.create')">Tambah Mahasiswa</x-button>
        </x-page-header>

        <section class="card" aria-label="Tabel data mahasiswa">
            <form x-ref="form" method="GET" action="{{ route('mahasiswa.index') }}" role="search"
                class="flex flex-col gap-3 border-b border-slate-200 p-4 sm:p-5 lg:flex-row lg:flex-wrap lg:items-end dark:border-slate-800"
                x-on:submit.prevent="submit()">
                <input type="hidden" name="sort" value="{{ request('sort') }}">
                <input type="hidden" name="direction" value="{{ request('direction') }}">

                <div class="relative flex-1 lg:min-w-56">
                    <label for="search" class="sr-only">Cari NIM atau nama</label>
                    {{-- Posisi diatur span pembungkus; animate-spin memakai transform sendiri
                         sehingga tidak boleh digabung dengan -translate-y-1/2 di elemen yang sama. --}}
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400" aria-hidden="true">
                        <x-icon name="search" class="size-4" x-show="! loading" />
                        <x-icon name="loader-circle" class="size-4 animate-spin text-primary-600" x-show="loading" x-cloak />
                    </span>
                    <input id="search" name="search" type="search" value="{{ request('search') }}" placeholder="Cari NIM atau nama…"
                        class="form-control pl-9" autocomplete="off" x-on:input="queue()">
                </div>

                <x-select name="prodi_id" label="Program studi" :sr-only-label="true" class="lg:w-48" placeholder="Semua program studi"
                    :options="$prodis->pluck('nama_prodi', 'id')" :value="request('prodi_id')" x-on:change="submit()" />

                <x-select name="jenis_kelamin" label="Jenis kelamin" :sr-only-label="true" class="lg:w-40" placeholder="Semua gender"
                    :options="array_combine(\App\Models\Mahasiswa::JENIS_KELAMIN, \App\Models\Mahasiswa::JENIS_KELAMIN)" :value="request('jenis_kelamin')" x-on:change="submit()" />

                <x-select name="status" label="Status" :sr-only-label="true" class="lg:w-36" placeholder="Semua status"
                    :options="array_combine(array_keys(\App\Models\Mahasiswa::STATUS), array_keys(\App\Models\Mahasiswa::STATUS))" :value="request('status')" x-on:change="submit()" />

                <x-select name="angkatan" label="Angkatan" :sr-only-label="true" class="lg:w-40" placeholder="Semua angkatan"
                    :options="array_combine($angkatans, $angkatans)" :value="request('angkatan')" x-on:change="submit()" />

                <div class="flex gap-2">
                    <noscript><x-button type="submit" icon="search">Cari</x-button></noscript>
                    <x-button variant="ghost" size="icon" class="size-10" :href="route('mahasiswa.index')" title="Reset filter">
                        <x-icon name="refresh-cw" class="size-4" /><span class="sr-only">Reset filter</span>
                    </x-button>
                </div>
            </form>

            {{-- Bar aksi massal: muncul bila ada baris terpilih --}}
            <form method="POST" action="{{ route('mahasiswa.bulk') }}" x-ref="bulkForm" x-show="selected.length" x-cloak
                x-transition.opacity
                class="flex flex-wrap items-center justify-between gap-3 border-b border-primary-100 bg-primary-50/70 px-4 py-3 sm:px-5 dark:border-primary-500/20 dark:bg-primary-500/10">
                @csrf
                <input type="hidden" name="aksi" x-ref="aksi" value="status">
                <template x-for="id in selected" :key="id"><input type="hidden" name="ids[]" :value="id"></template>
                <p class="text-sm font-semibold text-primary-800 dark:text-primary-200"><span x-text="selected.length"></span> mahasiswa dipilih</p>
                <div class="flex flex-wrap items-center gap-2">
                    <label for="bulk-status" class="sr-only">Status baru</label>
                    <select id="bulk-status" name="status" class="form-control h-8 w-32 py-0 text-xs">
                        @foreach (array_keys(\App\Models\Mahasiswa::STATUS) as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>
                    <x-button size="sm" variant="secondary" icon="refresh-cw" x-on:click="$refs.aksi.value = 'status'; $refs.bulkForm.submit()">Ubah status</x-button>
                    <x-button size="sm" variant="secondary" icon="download" x-on:click="$refs.aksi.value = 'export'; $refs.bulkForm.submit()">Ekspor terpilih</x-button>
                    <x-button size="sm" variant="danger-soft" icon="trash-2"
                        x-on:click="$refs.aksi.value = 'hapus'; $store.confirm.ask({ title: 'Hapus mahasiswa terpilih?', message: selected.length + ' mahasiswa akan dipindahkan ke Sampah.', confirmLabel: 'Hapus', form: $refs.bulkForm })">Hapus</x-button>
                    <x-button size="sm" variant="ghost" x-on:click="selected = []"><x-icon name="x" class="size-4" /><span class="sr-only">Batalkan pilihan</span></x-button>
                </div>
            </form>

            {{-- Bar progres tipis: tinggi tetap 2px agar layout tidak bergeser saat loading. --}}
            <div class="relative h-0.5 overflow-hidden" aria-hidden="true">
                <div x-show="loading" x-cloak class="absolute inset-y-0 left-0 w-1/3 animate-[loading-bar_1s_ease-in-out_infinite] rounded-full bg-primary-500"></div>
            </div>

            <div x-ref="region" data-table-region aria-live="polite" x-bind:aria-busy="loading.toString()"
                class="transition-opacity duration-150" x-bind:class="loading ? 'opacity-50 pointer-events-none' : ''">
                @include('mahasiswa._table')
            </div>
        </section>
    </div>
</x-app-layout>
