<x-card title="Kalender Akademik" :padding="false" x-data="academicCalendar({{ \Illuminate\Support\Js::from(now()->toDateString()) }})" {{ $attributes }}>
    <div class="px-5 pb-4 pt-3 sm:px-6">
        <div class="flex items-center justify-between gap-3">
            <p class="text-sm font-semibold capitalize text-slate-800 dark:text-slate-200" x-text="title" aria-live="polite">{{ now()->locale('id')->translatedFormat('F Y') }}</p>
            <div class="flex gap-1" x-cloak>
                <button type="button" x-on:click="move(-1)" class="grid size-8 place-items-center rounded-lg text-slate-500 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800" aria-label="Bulan sebelumnya"><x-icon name="chevron-left" class="size-4" /></button>
                <button type="button" x-on:click="move(1)" class="grid size-8 place-items-center rounded-lg text-slate-500 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800" aria-label="Bulan berikutnya"><x-icon name="chevron-right" class="size-4" /></button>
            </div>
        </div>
        <div class="mt-2 grid grid-cols-7 text-center text-xs" aria-label="Tanggal kalender">
            @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $hari)
                <span class="py-1 text-[10px] font-medium text-slate-500 dark:text-slate-400">{{ $hari }}</span>
            @endforeach
            <template x-for="item in days" :key="item.key">
                <div class="grid h-6 place-items-center">
                    <time x-show="item.date" x-bind:datetime="item.date" x-bind:aria-label="item.label" x-bind:aria-current="item.date === today ? 'date' : null"
                        x-text="item.day" class="grid size-6 place-items-center rounded-md tabular-nums"
                        x-bind:class="item.date === today ? 'bg-indigo-600 font-semibold text-white dark:bg-indigo-500' : 'text-slate-700 dark:text-slate-300'"></time>
                </div>
            </template>
        </div>
        <noscript><p class="py-4 text-sm text-slate-500">Hari ini: {{ now()->locale('id')->translatedFormat('d F Y') }}. Aktifkan JavaScript untuk menavigasi kalender.</p></noscript>
        <div class="mt-2 flex items-center justify-between gap-3 border-t border-slate-100 pt-2 dark:border-slate-800">
            <p class="text-[11px] text-slate-500 dark:text-slate-400">Agenda akademik belum tersedia.</p>
            <button type="button" x-on:click="reset()" class="min-h-7 shrink-0 rounded-md px-1 text-[11px] font-semibold text-indigo-700 hover:text-indigo-800 dark:text-indigo-300" x-cloak>Hari ini</button>
        </div>
    </div>
</x-card>
