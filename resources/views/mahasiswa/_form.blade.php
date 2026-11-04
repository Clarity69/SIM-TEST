{{-- Form bersama create/edit. $mahasiswa null saat tambah. --}}
@php($mahasiswa ??= null)

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" x-data="{ submitting: false }" x-on:submit="submitting = true" novalidate>
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

            <x-form-section title="Data Diri" description="Identitas mahasiswa sesuai dokumen resmi. Foto opsional; otomatis dipotong persegi dan dikompres." icon="user">
                @php($fotoError = $errors->first('foto'))
                <div class="sm:col-span-2" x-data="{ preview: @js($mahasiswa?->fotoUrl()), hapus: false }">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-200">Foto</span>
                    <div class="mt-1.5 flex items-center gap-4">
                        <template x-if="preview && ! hapus">
                            <img x-bind:src="preview" alt="Pratinjau foto" class="size-20 rounded-full object-cover ring-1 ring-slate-200 dark:ring-slate-700">
                        </template>
                        <template x-if="! preview || hapus">
                            <x-avatar :name="$mahasiswa?->nama ?? 'Mahasiswa Baru'" :jenis-kelamin="$mahasiswa?->jenis_kelamin" size="lg" class="size-20" />
                        </template>
                        <div class="space-y-2">
                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus-within:ring-2 focus-within:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                                <x-icon name="upload" class="size-4" /> <span x-text="preview ? 'Ganti foto' : 'Unggah foto'">Unggah foto</span>
                                <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" class="sr-only" aria-describedby="foto-hint"
                                    x-on:change="if ($event.target.files[0]) { preview = URL.createObjectURL($event.target.files[0]); hapus = false }">
                            </label>
                            @if ($mahasiswa?->foto)
                                <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                                    <input type="checkbox" name="hapus_foto" value="1" x-model="hapus" class="size-4 rounded border-slate-300 text-red-600 focus:ring-red-500 dark:border-slate-600 dark:bg-slate-900">
                                    Hapus foto saat disimpan
                                </label>
                            @endif
                            <p id="foto-hint" class="text-xs text-slate-500 dark:text-slate-400">JPG, PNG, atau WebP, maks. 2 MB, minimal 100×100 px.</p>
                            @if ($fotoError)
                                <p class="flex items-center gap-1 text-xs font-medium text-red-600 dark:text-red-400" role="alert"><x-icon name="circle-alert" class="size-3.5" /> {{ $fotoError }}</p>
                            @endif
                        </div>
                    </div>
                </div>

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
                <x-input name="angkatan" type="number" label="Angkatan" :value="$mahasiswa?->angkatan ?? now()->year" min="2000" max="{{ now()->year + 1 }}" inputmode="numeric" hint="Tahun masuk, contoh {{ now()->year }}." />
                <x-select name="status" label="Status" required :options="array_combine(array_keys(\App\Models\Mahasiswa::STATUS), array_keys(\App\Models\Mahasiswa::STATUS))" :value="$mahasiswa?->status ?? 'Aktif'" />
            </x-form-section>
        </div>

        <x-slot:footer>
            <x-button variant="secondary" :href="$mahasiswa ? route('mahasiswa.show', $mahasiswa) : route('mahasiswa.index')">Batal</x-button>
            <x-button type="submit" icon="save" :loading="true">{{ $mahasiswa ? 'Simpan perubahan' : 'Simpan mahasiswa' }}</x-button>
        </x-slot:footer>
    </x-card>
</form>
