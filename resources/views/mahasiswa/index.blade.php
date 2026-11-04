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
                class="flex flex-col gap-3 border-b border-slate-200 p-4 sm:p-5 lg:flex-row lg:items-end dark:border-slate-800"
                x-on:submit.prevent="submit()">
                <input type="hidden" name="sort" value="{{ request('sort') }}">
                <input type="hidden" name="direction" value="{{ request('direction') }}">

                <div class="relative flex-1">
                    <label for="search" class="sr-only">Cari NIM atau nama</label>
                    <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" x-show="! loading" />
                    <x-icon name="loader-circle" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 animate-spin text-primary-600" x-show="loading" x-cloak />
                    <input id="search" name="search" type="search" value="{{ request('search') }}" placeholder="Cari NIM atau nama…"
                        class="form-control pl-9" autocomplete="off" x-on:input="queue()">
                </div>

                <x-select name="prodi_id" label="Program studi" :sr-only-label="true" class="lg:w-56" placeholder="Semua program studi"
                    :options="$prodis->pluck('nama_prodi', 'id')" :value="request('prodi_id')" x-on:change="submit()" />

                <x-select name="jenis_kelamin" label="Jenis kelamin" :sr-only-label="true" class="lg:w-52" placeholder="Semua jenis kelamin"
                    :options="array_combine(\App\Models\Mahasiswa::JENIS_KELAMIN, \App\Models\Mahasiswa::JENIS_KELAMIN)" :value="request('jenis_kelamin')" x-on:change="submit()" />

                <div class="flex gap-2">
                    <noscript><x-button type="submit" icon="search">Cari</x-button></noscript>
                    <x-button variant="ghost" icon="refresh-cw" :href="route('mahasiswa.index')">Reset</x-button>
                </div>
            </form>

            <div x-ref="region" data-table-region aria-live="polite" x-bind:aria-busy="loading.toString()"
                class="transition-opacity" x-bind:class="loading ? 'opacity-60' : ''">
                @include('mahasiswa._table')
            </div>
        </section>
    </div>
</x-app-layout>
