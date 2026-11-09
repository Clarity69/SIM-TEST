@props(['href', 'icon', 'active' => false])

<a href="{{ $href }}" @if ($active) aria-current="page" @endif
    x-bind:title="$store.sidebar.collapsed ? @js(trim($slot)) : null"
    @class([
        'group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
        'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300' => $active,
        'text-slate-500 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white' => ! $active,
    ])
    x-bind:class="$store.sidebar.collapsed ? 'lg:justify-center lg:px-0' : ''">
    @if ($active)
        <span class="absolute left-0 top-1/2 h-5 w-[3px] -translate-y-1/2 rounded-full bg-indigo-600 dark:bg-indigo-400" aria-hidden="true"></span>
    @endif
    <x-icon :name="$icon" />
    <span x-bind:class="$store.sidebar.collapsed ? 'lg:sr-only' : ''">{{ $slot }}</span>
</a>
