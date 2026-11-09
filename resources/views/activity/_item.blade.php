@php
    /** @var \App\Models\ActivityLog $log */
    [$label, $tone, $ikon] = $log->jenis();
    $warnaIkon = [
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-500/30',
        'primary' => 'bg-primary-50 text-primary-700 ring-primary-200 dark:bg-primary-500/15 dark:text-primary-300 dark:ring-primary-500/30',
        'warning' => 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:ring-amber-500/30',
        'danger' => 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-500/15 dark:text-red-300 dark:ring-red-500/30',
        'neutral' => 'bg-slate-100 text-slate-600 ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700',
    ][$tone];
    $kolom = [
        'nim' => 'NIM', 'nama' => 'Nama', 'jenis_kelamin' => 'Jenis kelamin', 'tanggal_lahir' => 'Tanggal lahir',
        'alamat' => 'Alamat', 'telepon' => 'Telepon', 'email' => 'Email', 'prodi_id' => 'Program studi',
        'angkatan' => 'Angkatan', 'status' => 'Status', 'foto' => 'Foto', 'kode_prodi' => 'Kode prodi',
        'singkatan' => 'Singkatan', 'nama_prodi' => 'Nama prodi', 'fakultas' => 'Fakultas', 'kaprodi' => 'Kaprodi',
        'aktif' => 'Status aktif', 'nidn' => 'NIDN / NUPTK', 'kode' => 'Kode mata kuliah', 'sks' => 'SKS', 'semester' => 'Semester',
        'kaprodi_jabatan' => 'Jabatan kaprodi', 'kaprodi_nidn' => 'NIDN kaprodi', 'role' => 'Role',
    ];
    $tampil = function (string $field, mixed $nilai) use ($namaProdi) {
        return match (true) {
            $nilai === null || $nilai === '' => '—',
            $field === 'foto' => 'foto baru',
            $field === 'aktif' => $nilai ? 'Aktif' : 'Nonaktif',
            $field === 'prodi_id' => $namaProdi[$nilai] ?? "#{$nilai}",
            $field === 'role' => \App\Models\User::ROLES[$nilai] ?? $nilai,
            default => \Illuminate\Support\Str::limit((string) $nilai, 60),
        };
    };
    $role = $log->properties['role'] ?? null;
    $perubahan = $log->properties['perubahan'] ?? (is_array($role) ? ['role' => $role] : []);
    $contoh = $log->properties['contoh'] ?? [];
    $url = $log->subjectUrl();
@endphp

<li class="group relative flex gap-4 pb-6 last:pb-0">
    <span class="absolute bottom-0 left-[17px] top-10 w-px bg-slate-200 group-last:hidden dark:bg-slate-800" aria-hidden="true"></span>
    <span class="relative grid size-9 shrink-0 place-items-center rounded-full ring-1 {{ $warnaIkon }}" aria-hidden="true">
        <x-icon :name="$ikon" class="size-4" />
    </span>
    <div class="min-w-0 flex-1 pt-0.5">
        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
            <x-badge :tone="$tone">{{ $label }}</x-badge>
            <p class="text-sm text-slate-900 dark:text-white">{{ $log->description }}</p>
        </div>

        <p class="mt-1 flex flex-wrap items-center gap-x-2 text-xs text-slate-500 dark:text-slate-400">
            @unless ($tanpaPelaku ?? false)
                <span class="font-medium text-slate-700 dark:text-slate-300">{{ $log->user_name ?? 'Sistem' }}</span>
                <span aria-hidden="true">·</span>
            @endunless
            <time datetime="{{ $log->created_at->toIso8601String() }}" title="{{ $log->created_at->translatedFormat('l, d F Y H:i:s') }} WITA">{{ $log->created_at->diffForHumans() }}</time>
            @if ($log->subject_label && ! ($tanpaSubjek ?? false))
                <span aria-hidden="true">·</span>
                @if ($url)
                    <a href="{{ $url }}" class="font-medium text-primary-700 hover:underline dark:text-primary-300">{{ $log->subject_label }}</a>
                @else
                    <span>{{ $log->subject_label }}</span>
                @endif
            @endif
            @if ($log->ip_address && ! ($tanpaPelaku ?? false))
                <span aria-hidden="true">·</span>
                <span class="tabular-nums">IP {{ $log->ip_address }}</span>
            @endif
        </p>

        @if ($perubahan !== [])
            <dl class="mt-2.5 overflow-hidden rounded-xl border border-slate-200 text-xs dark:border-slate-800">
                @foreach ($perubahan as $field => [$lama, $baru])
                    <div class="grid gap-1 px-3 py-2 odd:bg-slate-50 sm:grid-cols-[9rem_1fr] dark:odd:bg-slate-800/40">
                        <dt class="font-medium text-slate-600 dark:text-slate-300">{{ $kolom[$field] ?? $field }}</dt>
                        <dd class="flex flex-wrap items-center gap-1.5 text-slate-700 dark:text-slate-200">
                            @if ($field === 'foto')
                                {{ $baru ? 'Foto diganti' : 'Foto dihapus' }}
                            @else
                                <span class="text-slate-500 line-through decoration-slate-400 dark:text-slate-400">{{ $tampil($field, $lama) }}</span>
                                <x-icon name="arrow-right" class="size-3 text-slate-400" />
                                <span class="font-medium">{{ $tampil($field, $baru) }}</span>
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
        @elseif (is_string($role))
            <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">Role akun: {{ \App\Models\User::ROLES[$role] ?? $role }}</p>
        @elseif ($contoh !== [])
            <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">{{ implode(', ', $contoh) }}{{ count($contoh) >= 5 ? ', …' : '' }}</p>
        @endif
    </div>
</li>
