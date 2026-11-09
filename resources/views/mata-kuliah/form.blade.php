<x-app-layout :title="($item ? 'Edit' : 'Tambah').' Mata Kuliah'" :breadcrumbs="['Mata Kuliah' => route('mata-kuliah.index'), $item ? 'Edit' : 'Tambah' => null]">
    <x-page-header :title="($item ? 'Edit' : 'Tambah').' Mata Kuliah'" description="Kolom bertanda * wajib diisi." />
    <form method="POST" action="{{ $item ? route('mata-kuliah.update', $item) : route('mata-kuliah.store') }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf
        @if ($item) @method('PUT') @endif
        <x-card>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-input name="nama" label="Nama Mata Kuliah" :value="$item?->nama" required maxlength="255" class="sm:col-span-2" />
                <x-input name="kode" label="Kode mata kuliah" :value="$item?->kode" required maxlength="20" />
                <x-input name="sks" label="SKS" type="number" :value="$item?->sks ?? 3" min="1" max="12" required />
                <x-input name="semester" label="Semester" type="number" :value="$item?->semester ?? 1" min="1" max="14" required />
                <x-select name="prodi_id" label="Program Studi" :options="$prodis->pluck('nama_prodi', 'id')->all()" :value="$item?->prodi_id" placeholder="Pilih program studi" required />
                <x-select name="aktif" label="Status" :options="[1 => 'Aktif', 0 => 'Nonaktif']" :value="$item ? (int) $item->aktif : 1" required />
            </div>
            <x-slot:footer>
                <x-button variant="secondary" :href="route('mata-kuliah.index')">Batal</x-button>
                <x-button type="submit" icon="save" :loading="true">Simpan Mata Kuliah</x-button>
            </x-slot:footer>
        </x-card>
    </form>
</x-app-layout>
