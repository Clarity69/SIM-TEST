@props(['name', 'label', 'type' => 'text', 'value' => null, 'icon' => null, 'hint' => null, 'required' => false, 'bag' => 'default', 'reveal' => false, 'aside' => null])

@php
    $id = $attributes->get('id', $name);
    $error = $errors->getBag($bag)->first($name);
    $describedBy = trim(($hint ? "{$id}-hint " : '').($error ? "{$id}-error" : ''));
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'space-y-1.5']) }}>
    <div class="flex items-center justify-between gap-2">
        <label for="{{ $id }}" class="block text-sm font-medium text-slate-700 dark:text-slate-200">
            {{ $label }} @if ($required)<span class="text-red-600" aria-hidden="true">*</span>@endif
        </label>
        {{ $aside }}
    </div>
    <div class="relative" @if ($reveal) x-data="{ show: false }" @endif>
        @if ($icon)
            <x-icon :name="$icon" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
        @endif
        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}"
            @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
            @required($required)
            @if ($error) aria-invalid="true" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            @if ($reveal) x-bind:type="show ? 'text' : 'password'" @endif
            {{ $attributes->except(['class', 'id'])->merge(['class' => 'form-control '.($icon ? 'pl-9 ' : '').($reveal ? 'pr-10 ' : '').($error ? 'form-control-error' : '')]) }}>
        @if ($reveal)
            <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                x-on:click="show = ! show" x-bind:aria-pressed="show.toString()">
                <span class="sr-only" x-text="show ? 'Sembunyikan password' : 'Tampilkan password'">Tampilkan password</span>
                <x-icon name="eye" class="size-4" x-show="! show" />
                <x-icon name="eye-off" class="size-4" x-show="show" x-cloak />
            </button>
        @endif
    </div>
    @if ($hint && ! $error)
        <p id="{{ $id }}-hint" class="text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</p>
    @endif
    @if ($error)
        <p id="{{ $id }}-error" class="flex items-center gap-1 text-xs font-medium text-red-600 dark:text-red-400">
            <x-icon name="circle-alert" class="size-3.5" /> {{ $error }}
        </p>
    @endif
</div>
