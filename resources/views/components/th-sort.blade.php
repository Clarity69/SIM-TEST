{{-- Header kolom yang bisa diurutkan; mempertahankan query string lain. --}}
@props(['column', 'label'])

@php
    $active = request('sort') === $column;
    $direction = $active && request('direction') === 'asc' ? 'desc' : 'asc';
    $url = request()->fullUrlWithQuery(['sort' => $column, 'direction' => $direction, 'page' => null]);
@endphp

<th scope="col" class="table-th" @if ($active) aria-sort="{{ request('direction') === 'asc' ? 'ascending' : 'descending' }}" @endif>
    <a href="{{ $url }}" class="group inline-flex items-center gap-1 hover:text-slate-900 dark:hover:text-white" data-sort-link>
        {{ $label }}
        <x-icon :name="$active ? (request('direction') === 'asc' ? 'arrow-up' : 'arrow-down') : 'chevrons-up-down'"
            @class(['size-3.5', 'text-slate-900 dark:text-white' => $active, 'text-slate-400 group-hover:text-slate-600' => ! $active]) />
    </a>
</th>
