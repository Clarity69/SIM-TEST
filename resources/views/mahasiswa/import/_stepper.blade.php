{{-- $langkah: 1 = unggah, 2 = pratinjau, 3 = selesai --}}
<ol class="flex items-center gap-3 text-sm" aria-label="Langkah impor">
    @foreach ([1 => 'Unggah file', 2 => 'Pratinjau & validasi', 3 => 'Selesai'] as $nomor => $label)
        <li class="flex items-center gap-2.5 {{ $loop->last ? '' : 'flex-1' }}" @if ($nomor === $langkah) aria-current="step" @endif>
            <span @class([
                'grid size-7 shrink-0 place-items-center rounded-full text-xs font-semibold',
                'bg-primary-600 text-white dark:bg-primary-500 dark:text-slate-950' => $nomor <= $langkah,
                'border border-slate-300 text-slate-500 dark:border-slate-600 dark:text-slate-400' => $nomor > $langkah,
            ])>
                @if ($nomor < $langkah) <x-icon name="check" class="size-3.5" /> @else {{ $nomor }} @endif
            </span>
            <span @class(['whitespace-nowrap font-semibold', 'text-slate-900 dark:text-white' => $nomor <= $langkah, 'text-slate-500 dark:text-slate-400' => $nomor > $langkah])>{{ $label }}</span>
            @unless ($loop->last)
                <span @class(['hidden h-0.5 flex-1 rounded-full sm:block', 'bg-primary-600 dark:bg-primary-500' => $nomor < $langkah, 'bg-slate-200 dark:bg-slate-700' => $nomor >= $langkah]) aria-hidden="true"></span>
            @endunless
        </li>
    @endforeach
</ol>
