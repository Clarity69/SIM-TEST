@props(['name', 'label', 'options' => [], 'value' => null, 'placeholder' => null, 'required' => false, 'srOnlyLabel' => false])

@php
    $id = $attributes->get('id', $name);
    $error = $errors->first($name);
    $selected = (string) old($name, $value);
@endphp

<div {{ $attributes->only('class')->merge(['class' => $srOnlyLabel ? '' : 'space-y-1.5']) }}>
    <label for="{{ $id }}" @class(['block text-sm font-medium text-slate-700 dark:text-slate-200', 'sr-only' => $srOnlyLabel])>
        {{ $label }} @if ($required)<span class="text-red-600" aria-hidden="true">*</span>@endif
    </label>
    <select id="{{ $id }}" name="{{ $name }}" @required($required)
        @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        {{ $attributes->except(['class', 'id'])->merge(['class' => 'form-control pr-9 '.($error ? 'form-control-error' : '')]) }}>
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($selected === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @if ($error)
        <p id="{{ $id }}-error" class="flex items-center gap-1 text-xs font-medium text-red-600 dark:text-red-400">
            <x-icon name="circle-alert" class="size-3.5" /> {{ $error }}
        </p>
    @endif
</div>
