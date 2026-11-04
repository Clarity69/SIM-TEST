@props(['tone' => 'neutral'])

@php
    $tones = [
        'neutral' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
        'primary' => 'bg-primary-50 text-primary-800 dark:bg-primary-500/15 dark:text-primary-200',
        'secondary' => 'bg-secondary-50 text-secondary-800 dark:bg-secondary-500/15 dark:text-secondary-200',
        'success' => 'bg-emerald-50 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-200',
        'warning' => 'bg-amber-50 text-amber-800 dark:bg-amber-500/15 dark:text-amber-200',
        'danger' => 'bg-red-50 text-red-800 dark:bg-red-500/15 dark:text-red-200',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold '.$tones[$tone]]) }}>{{ $slot }}</span>
