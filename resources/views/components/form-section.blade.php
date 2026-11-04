@props(['title', 'description' => null, 'icon' => null])

<div {{ $attributes->merge(['class' => 'grid gap-6 border-t border-slate-200 py-6 first:border-t-0 first:pt-0 lg:grid-cols-3 dark:border-slate-800']) }}>
    <div>
        <h3 class="flex items-center gap-2 text-sm font-semibold text-slate-900 dark:text-white">
            @if ($icon) <x-icon :name="$icon" class="size-4 text-primary-600 dark:text-primary-400" /> @endif
            {{ $title }}
        </h3>
        @if ($description)
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>
        @endif
    </div>
    <div class="grid gap-4 sm:grid-cols-2 lg:col-span-2">
        {{ $slot }}
    </div>
</div>
