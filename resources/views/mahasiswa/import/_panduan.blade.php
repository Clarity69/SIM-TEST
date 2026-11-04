<aside class="space-y-4">
    <x-card title="Format kolom">
        <dl class="space-y-3 text-sm">
            @foreach ([
                'nim' => 'wajib, unik, maks. 30 karakter',
                'nama' => 'wajib',
                'jenis_kelamin' => 'Laki-laki / Perempuan (boleh L / P)',
                'tanggal_lahir' => 'opsional, YYYY-MM-DD',
                'prodi' => 'singkatan (TI, SI, …), kode PDDIKTI, atau nama',
                'angkatan' => 'tahun; kosong = 4 digit awal NIM',
                'status' => 'Aktif / Cuti / Lulus / Keluar (kosong = Aktif)',
                'telepon, email, alamat' => 'opsional',
            ] as $kolom => $keterangan)
                <div>
                    <dt class="font-semibold text-primary-700 dark:text-primary-300">{{ $kolom }}</dt>
                    <dd class="text-slate-500 dark:text-slate-400">{{ $keterangan }}</dd>
                </div>
            @endforeach
        </dl>
    </x-card>
    <div class="flex gap-3 rounded-2xl border border-secondary-200 bg-secondary-50 p-4 text-sm text-secondary-900 dark:border-secondary-500/30 dark:bg-secondary-500/10 dark:text-secondary-100">
        <x-icon name="info" class="mt-0.5 size-4 shrink-0" />
        <p>Unduh template terlebih dulu agar urutan dan nama kolom sesuai. Hasil ekspor juga bisa diimpor ulang.</p>
    </div>
</aside>
