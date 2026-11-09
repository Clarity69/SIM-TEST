@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'icon' => null,
    'type' => 'button',
    'loading' => false,
])

@php
    $variants = [
        'primary' => 'bg-primary-600 text-white shadow-sm hover:bg-primary-700 dark:bg-primary-500 dark:text-slate-950 dark:hover:bg-primary-400',
        'secondary' => 'border border-slate-300 bg-white text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800',
        'danger' => 'bg-red-600 text-white shadow-sm hover:bg-red-700',
        'danger-soft' => 'bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-300 dark:hover:bg-red-500/20',
        'ghost' => 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white',
    ];
    $sizes = [
        'sm' => 'h-8 gap-1.5 px-3 text-xs',
        'md' => 'h-11 gap-2 px-4 text-sm',
        'icon' => 'size-10 shrink-0 justify-center',
    ];
    $classes = 'inline-flex shrink-0 items-center justify-center whitespace-nowrap rounded-xl font-semibold transition-colors disabled:cursor-not-allowed disabled:opacity-60 '.$variants[$variant].' '.$sizes[$size];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon) <x-icon :name="$icon" class="size-4" /> @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}"
        @if ($loading) x-bind:disabled="submitting" x-bind:aria-busy="submitting" @endif
        {{ $attributes->merge(['class' => $classes]) }}>
        @if ($loading)
            <x-icon name="loader-circle" class="size-4 animate-spin" x-show="submitting" x-cloak />
            @if ($icon) <x-icon :name="$icon" class="size-4" x-show="! submitting" /> @endif
        @elseif ($icon)
            <x-icon :name="$icon" class="size-4" />
        @endif
        {{ $slot }}
    </button>
@endif
