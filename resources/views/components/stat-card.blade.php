@props(['label', 'value', 'icon', 'tone' => 'primary', 'trend' => null, 'delta' => null, 'since' => null, 'href' => null])

@php
    $tones = [
        'primary' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300',
        'accent' => 'bg-accent-50 text-accent-700 dark:bg-accent-500/20 dark:text-accent-200',
        'secondary' => 'bg-secondary-50 text-secondary-700 dark:bg-secondary-500/15 dark:text-secondary-300',
        'success' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
    ];
    $displayValue = $value === null ? '—' : number_format($value, 0, ',', '.');
@endphp

<{{ $href ? 'a' : 'div' }} @if ($href) href="{{ $href }}" aria-label="{{ $label }}: {{ $displayValue }}. Lihat data." @endif
    {{ $attributes->merge(['class' => 'card group flex flex-col gap-3 p-5 transition-[border-color,box-shadow] hover:border-indigo-200 sm:p-5 dark:hover:border-indigo-500/40']) }}>
    <div class="flex items-center justify-between gap-3">
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ $label }}</p>
        <span class="grid size-9 shrink-0 place-items-center rounded-xl {{ $tones[$tone] }}">
            <x-icon :name="$icon" class="size-5" />
        </span>
    </div>
    <p class="text-3xl font-bold tracking-tight tabular-nums text-slate-900 dark:text-white">{{ $displayValue }}</p>
    <div class="min-h-5 text-[11px] text-slate-500 dark:text-slate-400">
        @if ($value === null)
            <span>Data belum tersedia</span>
        @elseif ($delta !== null && $since !== null)
            <span @class(['font-semibold tabular-nums', 'text-emerald-700 dark:text-emerald-400' => $delta > 0, 'text-red-700 dark:text-red-400' => $delta < 0])>
                {{ $delta > 0 ? '+' : '' }}{{ number_format($delta, 0, ',', '.') }}
                @if ($trend !== null)&nbsp;({{ $trend > 0 ? '+' : '' }}{{ number_format($trend, 1, ',', '.') }}%) @endif
            </span>
            dari perubahan terakhir
        @elseif ($delta !== null)
            <span>Belum ada perubahan tercatat</span>
        @endif
    </div>
</{{ $href ? 'a' : 'div' }}>
