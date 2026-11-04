@props(['title' => null, 'description' => null, 'padding' => true])

<section {{ $attributes->merge(['class' => 'card']) }}>
    @if ($title || isset($actions))
        <header class="flex flex-wrap items-start justify-between gap-3 px-5 pt-5 sm:px-6">
            <div>
                @if ($title)
                    <h2 class="text-base font-semibold text-slate-900 dark:text-white">{{ $title }}</h2>
                @endif
                @if ($description)
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>
                @endif
            </div>
            {{ $actions ?? '' }}
        </header>
    @endif

    <div @class(['px-5 py-5 sm:px-6' => $padding])>
        {{ $slot }}
    </div>

    @isset($footer)
        <footer class="flex flex-wrap items-center justify-end gap-3 rounded-b-2xl border-t border-slate-200 bg-slate-50 px-5 py-3.5 sm:px-6 dark:border-slate-800 dark:bg-slate-900/60">
            {{ $footer }}
        </footer>
    @endisset
</section>
