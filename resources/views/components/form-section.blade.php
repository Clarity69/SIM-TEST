@props(['title', 'description' => null, 'icon' => null])

<div {{ $attributes->merge(['class' => 'grid grid-cols-1 gap-6 border-t border-slate-200 py-6 first:border-t-0 first:pt-0 lg:grid-cols-3 dark:border-slate-800']) }}>
    <div>
        <h2 class="flex items-center gap-2.5 text-sm font-semibold text-slate-900 dark:text-white">
            @if ($icon)<span class="grid size-8 shrink-0 place-items-center rounded-lg bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300"><x-icon :name="$icon" class="size-4" /></span>@endif
            {{ $title }}
        </h2>
        @if ($description)
            <p class="mt-2 max-w-xs text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ $description }}</p>
        @endif
    </div>
    <div class="grid gap-4 sm:grid-cols-2 lg:col-span-2">
        {{ $slot }}
    </div>
</div>
