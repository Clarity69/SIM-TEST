<x-app-layout title="Log Aktivitas" :breadcrumbs="['Log Aktivitas' => null]">
    <x-page-header title="Log Aktivitas" description="Riwayat siapa melakukan apa di aplikasi. Disimpan {{ \App\Models\ActivityLog::RETENSI_HARI }} hari." />

    <section class="card" aria-labelledby="judul-log">
        <h2 id="judul-log" class="sr-only">Daftar aktivitas</h2>
        <form method="GET" action="{{ route('activity.index') }}" x-data x-ref="form"
            class="grid gap-3 border-b border-slate-200 p-4 sm:grid-cols-2 lg:grid-cols-[1fr_11rem_11rem_11rem_auto] dark:border-slate-800">
            <div class="relative">
                <label for="search" class="sr-only">Cari aktivitas</label>
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                <input id="search" type="search" name="search" value="{{ $filters['search'] }}" placeholder="Cari deskripsi, data, atau pengguna…" class="form-control pl-9">
            </div>
            <x-select name="event" label="Jenis" sr-only-label placeholder="Semua jenis" :value="$filters['event']"
                :options="collect(\App\Models\ActivityLog::EVENTS)->map(fn ($e) => $e[0])->all()" x-on:change="$refs.form.submit()" />
            <x-select name="subject_type" label="Data" sr-only-label placeholder="Semua data" :value="$filters['subject_type']"
                :options="['mahasiswa' => 'Mahasiswa', 'prodi' => 'Program studi', 'pengguna' => 'Pengguna']" x-on:change="$refs.form.submit()" />
            <x-select name="user_id" label="Pengguna" sr-only-label placeholder="Semua pengguna" :value="$filters['user_id']"
                :options="$users->all()" x-on:change="$refs.form.submit()" />
            <div class="flex gap-2">
                <x-button type="submit" variant="secondary" icon="search">Cari</x-button>
                @if (array_filter($filters))
                    <x-button variant="ghost" :href="route('activity.index')">Reset</x-button>
                @endif
            </div>
        </form>

        <div class="p-5 sm:p-6">
            @if ($logs->isEmpty())
                <x-empty-state icon="history" title="Belum ada aktivitas" description="{{ array_filter($filters) ? 'Tidak ada aktivitas yang cocok dengan filter.' : 'Aktivitas akan muncul setelah pengguna menambah, mengubah, atau menghapus data.' }}" />
            @else
                @foreach ($logs->groupBy(fn ($log) => $log->created_at->toDateString()) as $tanggal => $grup)
                    <h3 class="mb-4 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 [&:not(:first-child)]:mt-8">
                        {{ \Illuminate\Support\Carbon::parse($tanggal)->isToday() ? 'Hari ini' : (\Illuminate\Support\Carbon::parse($tanggal)->isYesterday() ? 'Kemarin' : \Illuminate\Support\Carbon::parse($tanggal)->translatedFormat('l, d F Y')) }}
                    </h3>
                    <ol>
                        @foreach ($grup as $log)
                            @include('activity._item', ['log' => $log, 'namaProdi' => $namaProdi])
                        @endforeach
                    </ol>
                @endforeach
            @endif
        </div>

        @if ($logs->hasPages())
            <div class="border-t border-slate-200 px-5 py-4 dark:border-slate-800">{{ $logs->links() }}</div>
        @endif
    </section>
</x-app-layout>
