<x-app-layout title="Data Mahasiswa" :breadcrumbs="['Data Mahasiswa' => null]">
    <div x-data="liveTable(350)" class="space-y-6">
        <x-page-header title="Data Mahasiswa" description="Cari, saring, dan kelola data mahasiswa {{ config('stitek.singkatan') }}.">
            <form method="POST" x-bind:action="@js(route('mahasiswa.pdf.email')) + query" action="{{ route('mahasiswa.pdf.email', request()->query()) }}"
                x-data="{ submitting: false }" x-on:submit="submitting = true">
                @csrf
                <x-button type="submit" variant="secondary" icon="send" :loading="true" title="Kirim laporan PDF (sesuai filter) ke {{ auth()->user()->email }}">Kirim PDF</x-button>
            </form>
            <x-button variant="secondary" icon="printer" target="_blank"
                :href="route('mahasiswa.pdf', request()->query())" x-bind:href="{{ \Illuminate\Support\Js::from(route('mahasiswa.pdf')) }} + query">Cetak PDF</x-button>
            <x-button icon="plus" :href="route('mahasiswa.create')">Tambah Mahasiswa</x-button>
        </x-page-header>

        <section class="card" aria-label="Tabel data mahasiswa">
            <form x-ref="form" method="GET" action="{{ route('mahasiswa.index') }}" role="search"
                class="flex flex-col gap-3 border-b border-slate-200 p-4 sm:p-5 lg:flex-row lg:flex-wrap lg:items-end dark:border-slate-800"
                x-on:submit.prevent="submit()">
                <input type="hidden" name="sort" value="{{ request('sort') }}">
                <input type="hidden" name="direction" value="{{ request('direction') }}">

                <div class="relative flex-1 lg:min-w-64">
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

                <x-select name="prodi_id" label="Program studi" :sr-only-label="true" class="lg:w-52" placeholder="Semua program studi"
                    :options="$prodis->pluck('nama_prodi', 'id')" :value="request('prodi_id')" x-on:change="submit()" />

                <x-select name="jenis_kelamin" label="Jenis kelamin" :sr-only-label="true" class="lg:w-44" placeholder="Semua gender"
                    :options="array_combine(\App\Models\Mahasiswa::JENIS_KELAMIN, \App\Models\Mahasiswa::JENIS_KELAMIN)" :value="request('jenis_kelamin')" x-on:change="submit()" />

                <x-select name="status" label="Status" :sr-only-label="true" class="lg:w-40" placeholder="Semua status"
                    :options="array_combine(array_keys(\App\Models\Mahasiswa::STATUS), array_keys(\App\Models\Mahasiswa::STATUS))" :value="request('status')" x-on:change="submit()" />

                <x-select name="angkatan" label="Angkatan" :sr-only-label="true" class="lg:w-40" placeholder="Semua angkatan"
                    :options="array_combine($angkatans, $angkatans)" :value="request('angkatan')" x-on:change="submit()" />

                <div class="flex gap-2">
                    <noscript><x-button type="submit" icon="search">Cari</x-button></noscript>
                    <x-button variant="ghost" icon="refresh-cw" :href="route('mahasiswa.index')">Reset</x-button>
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
