<x-app-layout :title="($item ? 'Edit' : 'Tambah').' Dosen'" :breadcrumbs="['Dosen' => route('dosen.index'), $item ? 'Edit' : 'Tambah' => null]">
    <x-page-header :title="($item ? 'Edit' : 'Tambah').' Dosen'" description="Kolom bertanda * wajib diisi." />
    <form method="POST" action="{{ $item ? route('dosen.update', $item) : route('dosen.store') }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf
        @if ($item) @method('PUT') @endif
        <x-card>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-input name="nama" label="Nama Dosen" :value="$item?->nama" required maxlength="255" class="sm:col-span-2" />
                <x-input name="nidn" label="NIDN / NUPTK" :value="$item?->nidn" inputmode="numeric" minlength="10" maxlength="16" />
                <x-input name="email" label="Email" type="email" :value="$item?->email" maxlength="255" />
                <x-select name="prodi_id" label="Program Studi" :options="$prodis->pluck('nama_prodi', 'id')->all()" :value="$item?->prodi_id" placeholder="Pilih program studi" required />
                <x-select name="aktif" label="Status" :options="[1 => 'Aktif', 0 => 'Nonaktif']" :value="$item ? (int) $item->aktif : 1" required />
            </div>
            <x-slot:footer>
                <x-button variant="secondary" :href="route('dosen.index')">Batal</x-button>
                <x-button type="submit" icon="save" :loading="true">Simpan Dosen</x-button>
            </x-slot:footer>
        </x-card>
    </form>
</x-app-layout>
