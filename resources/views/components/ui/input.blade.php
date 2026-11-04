@props(['type' => 'text', 'name', 'icon' => null, 'value' => null, 'reveal' => false])

@php
    $hasError = $errors->has($name);
    $fieldClasses = 'h-11 w-full rounded-xl border bg-surface text-[15px] text-ink shadow-sm outline-none transition placeholder:text-subtle disabled:bg-surface-2 disabled:text-muted '
        . ($hasError
            ? 'border-rose-400 focus:border-rose-400 focus:ring-4 focus:ring-rose-500/15 dark:border-rose-500/60'
            : 'border-line focus:border-brand focus:ring-4 focus:ring-brand/15');
    $padding = ' pl-11 ' . ($reveal ? 'pr-11' : 'pr-3.5');
@endphp

<div class="relative" @if ($reveal) x-data="{ show: false }" @endif>
    @if ($icon)
        <span class="pointer-events-none absolute inset-y-0 left-0 flex w-11 items-center justify-center text-subtle">
            <x-icon :name="$icon" class="size-[18px]" />
        </span>
    @endif

    <input
        type="{{ $reveal ? 'password' : $type }}"
        @if ($reveal) x-bind:type="show ? 'text' : 'password'" @endif
        name="{{ $name }}"
        id="{{ $attributes->get('id', $name) }}"
        @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
        {{ $attributes->except(['id'])->merge([
            'class' => $fieldClasses . ($icon ? $padding : ' px-3.5' . ($reveal ? ' pr-11' : '')),
            'aria-invalid' => $hasError ? 'true' : 'false',
        ]) }}
    />

    @if ($reveal)
        <button
            type="button"
            x-on:click="show = !show"
            :aria-label="show ? 'Sembunyikan sandi' : 'Tampilkan sandi'"
            class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-subtle transition hover:text-ink"
        >
            <x-icon name="eye" class="size-[18px]" x-show="!show" x-cloak />
            <x-icon name="eye-off" class="size-[18px]" x-show="show" x-cloak />
        </button>
    @endif
</div>
