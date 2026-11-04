@props(['name', 'jenisKelamin' => null, 'size' => 'md'])

@php
    $inisial = collect(preg_split('/\s+/', trim($name)))->filter()->take(2)->map(fn ($kata) => mb_strtoupper(mb_substr($kata, 0, 1)))->implode('');
    $tone = match ($jenisKelamin) {
        'Perempuan' => 'bg-secondary-50 text-secondary-700 dark:bg-secondary-500/15 dark:text-secondary-300',
        'Laki-laki' => 'bg-primary-50 text-primary-700 dark:bg-primary-500/15 dark:text-primary-300',
        default => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
    };
    $sizes = ['sm' => 'size-8 text-xs', 'md' => 'size-9 text-xs', 'lg' => 'size-16 text-lg'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-grid shrink-0 place-items-center rounded-full font-semibold '.$tone.' '.$sizes[$size]]) }} aria-hidden="true">{{ $inisial }}</span>
