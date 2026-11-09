<x-card title="Pengumuman" {{ $attributes }}>
    <div class="flex min-h-56 flex-col items-start justify-center gap-4">
        <span class="grid size-10 place-items-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300"><x-icon name="bell" /></span>
        <div class="flex flex-col gap-2">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200">Pengumuman belum tersedia</h3>
            <p class="text-xs leading-relaxed text-slate-500 dark:text-slate-400">Informasi akademik akan ditampilkan di sini setelah pengumuman kampus tersedia.</p>
        </div>
        <a href="mailto:{{ config('stitek.kontak.email') }}" class="inline-flex min-h-9 items-center gap-1 text-xs font-semibold text-indigo-700 dark:text-indigo-300">Hubungi layanan akademik <x-icon name="arrow-up-right" class="size-3.5" /></a>
    </div>
</x-card>
