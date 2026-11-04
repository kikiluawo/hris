@props(['name', 'options' => [], 'selected' => null, 'placeholder' => null])

@php
    $hasError = $errors->has($name);
    $current = (string) old($name, $selected);
@endphp

<div class="relative">
    <select
        name="{{ $name }}"
        id="{{ $attributes->get('id', $name) }}"
        data-select2-init
        @if ($placeholder) data-placeholder="{{ $placeholder }}" @endif
        {{ $attributes->except(['id'])->merge([
            'class' => 'h-11 w-full appearance-none rounded-xl border bg-surface pl-3.5 pr-10 text-[15px] text-ink shadow-sm outline-none transition '
                . ($hasError
                    ? 'border-rose-400 focus:border-rose-400 focus:ring-4 focus:ring-rose-500/15 dark:border-rose-500/60'
                    : 'border-line focus:border-brand focus:ring-4 focus:ring-brand/15'),
            'aria-invalid' => $hasError ? 'true' : 'false',
        ]) }}
    >
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $value => $label)
            <option value="{{ $value }}" @selected($current === (string) $value)>{{ $label }}</option>
        @endforeach
    </select>

    <span data-select-chevron class="pointer-events-none absolute inset-y-0 right-0 flex w-10 items-center justify-center text-subtle">
        <x-icon name="chevron-down" class="size-4" />
    </span>
</div>
