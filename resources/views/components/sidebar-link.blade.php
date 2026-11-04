@props(['href', 'icon', 'active' => false])

<a href="{{ $href }}" @if ($active) aria-current="page" @endif
    x-bind:title="$store.sidebar.collapsed ? @js(trim($slot)) : null"
    @class([
        'group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
        'bg-white/10 text-white' => $active,
        'text-primary-100 hover:bg-white/5 hover:text-white' => ! $active,
    ])
    x-bind:class="$store.sidebar.collapsed ? 'lg:justify-center lg:px-0' : ''">
    @if ($active)
        <span class="absolute left-0 top-1/2 h-5 w-[3px] -translate-y-1/2 rounded-full bg-secondary-500" aria-hidden="true"></span>
    @endif
    <x-icon :name="$icon" />
    <span x-bind:class="$store.sidebar.collapsed ? 'lg:sr-only' : ''">{{ $slot }}</span>
</a>
