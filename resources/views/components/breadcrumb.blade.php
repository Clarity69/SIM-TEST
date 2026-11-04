@props(['items' => []])

<nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => 'min-w-0']) }}>
    <ol class="flex items-center gap-1.5 text-sm">
        <li><a href="{{ route('dashboard') }}" class="text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">Beranda</a></li>
        @foreach ($items as $label => $url)
            <li class="flex min-w-0 items-center gap-1.5">
                <x-icon name="chevron-right" class="size-3.5 text-slate-400" />
                @if ($loop->last || ! $url)
                    <span class="truncate font-semibold text-slate-900 dark:text-white" @if ($loop->last) aria-current="page" @endif>{{ $label }}</span>
                @else
                    <a href="{{ $url }}" class="truncate text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">{{ $label }}</a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
