@props(['name', 'label', 'value' => null, 'rows' => 3, 'required' => false])

@php
    $id = $attributes->get('id', $name);
    $error = $errors->first($name);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'space-y-1.5']) }}>
    <label for="{{ $id }}" class="block text-sm font-medium text-slate-700 dark:text-slate-200">
        {{ $label }} @if ($required)<span class="text-red-600" aria-hidden="true">*</span>@endif
    </label>
    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" @required($required)
        @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        {{ $attributes->except(['class', 'id'])->merge(['class' => 'form-control '.($error ? 'form-control-error' : '')]) }}>{{ old($name, $value) }}</textarea>
    @if ($error)
        <p id="{{ $id }}-error" class="flex items-center gap-1 text-xs font-medium text-red-600 dark:text-red-400">
            <x-icon name="circle-alert" class="size-3.5" /> {{ $error }}
        </p>
    @endif
</div>
