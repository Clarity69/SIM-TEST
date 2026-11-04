<x-app-layout title="Impor Mahasiswa" :breadcrumbs="['Data Mahasiswa' => route('mahasiswa.index'), 'Impor' => null]">
    <x-page-header title="Impor Data Mahasiswa" description="Unggah file Excel (.xlsx) atau CSV sesuai template, maksimal {{ \App\Services\ImportMahasiswa::MAKS_BARIS }} baris.">
        <x-button variant="secondary" icon="file-down" :href="route('mahasiswa.import.template')" data-no-prerender>Unduh template</x-button>
    </x-page-header>

    @include('mahasiswa.import._stepper', ['langkah' => 1])

    <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <form method="POST" action="{{ route('mahasiswa.import.upload') }}" enctype="multipart/form-data"
            x-data="{ submitting: false, nama: null, seret: false }" x-on:submit="submitting = true">
            @csrf
            <x-card :padding="false">
                <label for="file"
                    class="m-5 flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed px-6 py-14 text-center transition sm:m-6"
                    x-bind:class="seret ? 'border-primary-500 bg-primary-50 dark:bg-primary-500/10' : 'border-slate-300 hover:border-primary-400 dark:border-slate-700'"
                    x-on:dragover.prevent="seret = true" x-on:dragleave.prevent="seret = false"
                    x-on:drop.prevent="seret = false; $refs.file.files = $event.dataTransfer.files; nama = $event.dataTransfer.files[0]?.name">
                    <span class="grid size-14 place-items-center rounded-2xl bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300"><x-icon name="file-spreadsheet" class="size-7" /></span>
                    <span class="mt-4 text-base font-semibold text-slate-900 dark:text-white" x-text="nama ?? 'Pilih atau seret file ke sini'">Pilih atau seret file ke sini</span>
                    <span class="mt-1 text-sm text-slate-500 dark:text-slate-400">.xlsx, .xls, atau .csv · maks. 5 MB</span>
                    <input id="file" x-ref="file" type="file" name="file" accept=".xlsx,.xls,.csv" class="sr-only" required
                        x-on:change="nama = $event.target.files[0]?.name" @error('file') aria-invalid="true" aria-describedby="file-error" @enderror>
                </label>
                @error('file')
                    <p id="file-error" class="mx-6 -mt-2 mb-4 flex items-center gap-1 text-sm font-medium text-red-600 dark:text-red-400" role="alert"><x-icon name="circle-alert" class="size-4" /> {{ $message }}</p>
                @enderror

                <x-slot:footer>
                    <x-button variant="secondary" :href="route('mahasiswa.index')">Batal</x-button>
                    <x-button type="submit" icon="upload" :loading="true">Unggah & periksa</x-button>
                </x-slot:footer>
            </x-card>
        </form>

        @include('mahasiswa.import._panduan')
    </div>
</x-app-layout>
