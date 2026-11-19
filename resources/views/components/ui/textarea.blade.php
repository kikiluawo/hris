@props(['name', 'rows' => 4, 'value' => null])

@php
    $hasError = $errors->has($name);
@endphp

<textarea
    name="{{ $name }}"
    id="{{ $attributes->get('id', $name) }}"
    rows="{{ $rows }}"
    {{ $attributes->except(['id'])->merge([
        'class' => 'w-full rounded-xl border bg-surface px-3.5 py-3 text-[16px] sm:text-[14.5px] text-ink shadow-sm outline-none transition placeholder:text-subtle '
            . ($hasError
                ? 'border-rose-400 focus:border-rose-400 focus:ring-4 focus:ring-rose-500/15 dark:border-rose-500/60'
                : 'border-line focus:border-brand focus:ring-4 focus:ring-brand/15'),
        'aria-invalid' => $hasError ? 'true' : 'false',
    ]) }}
>{{ old($name, $value) }}</textarea>
