@php($prodi ??= null)

<form method="POST" action="{{ $action }}" x-data="{ submitting: false }" x-on:submit="submitting = true" novalidate>
    @csrf
    @if ($prodi) @method('PUT') @endif

    <x-card :padding="false">
        <div class="px-5 py-6 sm:px-6">
            <x-form-section title="Identitas Prodi" description="Gunakan kode program studi dari PDDIKTI." icon="graduation-cap">
                <x-input name="kode_prodi" label="Kode prodi (PDDIKTI)" :value="$prodi?->kode_prodi" required maxlength="20" placeholder="55201" />
                <x-input name="singkatan" label="Singkatan" :value="$prodi?->singkatan" maxlength="10" placeholder="TI" hint="Label pendek untuk grafik dan badge." />
                <x-input name="nama_prodi" label="Nama program studi" :value="$prodi?->nama_prodi" required maxlength="255" class="sm:col-span-2" placeholder="Teknik Informatika" />
                <x-input name="fakultas" label="Fakultas" :value="$prodi?->fakultas" maxlength="255" class="sm:col-span-2" />
            </x-form-section>

            <x-form-section title="Kepala Program Studi" description="Penanda tangan laporan PDF prodi. Isi hanya dengan data resmi; boleh dikosongkan." icon="user-round">
                <x-input name="kaprodi" label="Nama dan gelar" :value="$prodi?->kaprodi" maxlength="255" class="sm:col-span-2" placeholder="Nama lengkap, S.T., M.Eng" />
                <x-input name="kaprodi_jabatan" label="Jabatan" :value="$prodi?->kaprodi_jabatan ?? 'Ketua Program Studi'" maxlength="100" hint="Contoh: Plt. Ketua Program Studi" />
                <x-input name="kaprodi_nidn" label="NIDN" :value="$prodi?->kaprodi_nidn" maxlength="20" inputmode="numeric" />
            </x-form-section>
        </div>

        <x-slot:footer>
            <x-button variant="secondary" :href="$prodi ? route('prodi.show', $prodi) : route('prodi.index')">Batal</x-button>
            <x-button type="submit" icon="save" :loading="true">{{ $prodi ? 'Simpan perubahan' : 'Simpan prodi' }}</x-button>
        </x-slot:footer>
    </x-card>
</form>
