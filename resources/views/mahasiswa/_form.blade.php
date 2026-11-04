{{-- Form bersama create/edit. $mahasiswa null saat tambah. --}}
@php($mahasiswa ??= null)

<form method="POST" action="{{ $action }}" x-data="{ submitting: false }" x-on:submit="submitting = true" novalidate>
    @csrf
    @if ($mahasiswa) @method('PUT') @endif

    <x-card :padding="false">
        <div class="px-5 py-6 sm:px-6">
            @if ($errors->any())
                <div class="mb-6 flex gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200" role="alert">
                    <x-icon name="circle-alert" />
                    <p>Periksa kembali {{ $errors->count() }} isian yang ditandai di bawah.</p>
                </div>
            @endif

            <x-form-section title="Data Diri" description="Identitas mahasiswa sesuai dokumen resmi." icon="user">
                <x-input name="nim" label="NIM" :value="$mahasiswa?->nim" required maxlength="30" inputmode="numeric" placeholder="202412001" />
                <x-input name="nama" label="Nama lengkap" :value="$mahasiswa?->nama" required maxlength="255" autocomplete="name" />
                <x-select name="jenis_kelamin" label="Jenis kelamin" required placeholder="Pilih jenis kelamin"
                    :options="array_combine(\App\Models\Mahasiswa::JENIS_KELAMIN, \App\Models\Mahasiswa::JENIS_KELAMIN)" :value="$mahasiswa?->jenis_kelamin" />
                <x-input name="tanggal_lahir" type="date" label="Tanggal lahir" :value="$mahasiswa?->tanggal_lahir?->format('Y-m-d')" max="{{ now()->subDay()->format('Y-m-d') }}" />
            </x-form-section>

            <x-form-section title="Kontak" description="Dipakai untuk menghubungi mahasiswa." icon="phone">
                <x-input name="email" type="email" label="Email" :value="$mahasiswa?->email" icon="mail" autocomplete="email" placeholder="nama@example.com" />
                <x-input name="telepon" type="tel" label="Telepon" :value="$mahasiswa?->telepon" icon="phone" maxlength="20" autocomplete="tel" placeholder="08xxxxxxxxxx" />
                <x-textarea name="alamat" label="Alamat" :value="$mahasiswa?->alamat" class="sm:col-span-2" />
            </x-form-section>

            <x-form-section title="Akademik" description="Program studi tempat mahasiswa terdaftar." icon="graduation-cap">
                <x-select name="prodi_id" label="Program studi" required placeholder="Pilih program studi" class="sm:col-span-2"
                    :options="$prodis->mapWithKeys(fn ($p) => [$p->id => $p->nama_prodi.' ('.$p->label().')'])" :value="$mahasiswa?->prodi_id" />
            </x-form-section>
        </div>

        <x-slot:footer>
            <x-button variant="secondary" :href="$mahasiswa ? route('mahasiswa.show', $mahasiswa) : route('mahasiswa.index')">Batal</x-button>
            <x-button type="submit" icon="save" :loading="true">{{ $mahasiswa ? 'Simpan perubahan' : 'Simpan mahasiswa' }}</x-button>
        </x-slot:footer>
    </x-card>
</form>
