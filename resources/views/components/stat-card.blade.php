@props(['label', 'value', 'icon', 'tone' => 'primary', 'trend' => null, 'delta' => null])

@php
    $tones = [
        'primary' => 'bg-primary-50 text-primary-700 dark:bg-primary-500/15 dark:text-primary-300',
        'accent' => 'bg-accent-50 text-accent-700 dark:bg-accent-500/20 dark:text-accent-200',
        'secondary' => 'bg-secondary-50 text-secondary-700 dark:bg-secondary-500/15 dark:text-secondary-300',
        'success' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'card flex flex-col gap-4 p-5']) }}>
    <div class="flex items-start justify-between gap-3">
        <span class="grid size-10 place-items-center rounded-xl {{ $tones[$tone] }}">
            <x-icon :name="$icon" />
        </span>
        @if ($trend !== null)
            @php
                $up = $trend > 0;
                $flat = $trend == 0;
            @endphp
            <span @class([
                'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold',
                'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' => $up,
                'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-300' => ! $up && ! $flat,
                'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' => $flat,
            ]) title="Dibanding 30 hari lalu">
                <x-icon :name="$flat ? 'minus' : ($up ? 'trending-up' : 'trending-down')" class="size-3.5" />
                {{ ($up ? '+' : '').number_format($trend, 1, ',', '.') }}%
            </span>
        @endif
    </div>
    <div>
        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $label }}</p>
        <p class="mt-1 text-3xl font-bold tabular-nums text-slate-900 dark:text-white">{{ number_format($value, 0, ',', '.') }}</p>
        @if ($delta !== null)
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                <span @class(['font-semibold tabular-nums', 'text-emerald-700 dark:text-emerald-400' => $delta > 0, 'text-red-700 dark:text-red-400' => $delta < 0])>{{ $delta > 0 ? '+' : '' }}{{ number_format($delta, 0, ',', '.') }}</span>
                dibanding 30 hari lalu
            </p>
        @endif
    </div>
</div>
