@props(['label', 'value', 'delta' => null, 'trend' => 'up', 'footnote' => null, 'icon' => 'grid'])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-line bg-surface p-5 shadow-card']) }}>
    <div class="flex items-start justify-between gap-3">
        <p class="text-[11.5px] font-semibold uppercase tracking-[0.09em] text-subtle">{{ $label }}</p>
        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-brand-soft text-brand-soft-ink">
            <x-icon :name="$icon" class="size-[18px]" />
        </span>
    </div>

    <p class="mt-3 text-[26px] font-semibold leading-tight tracking-tight tabular-nums">{{ $value }}</p>

    <div class="mt-2 flex flex-wrap items-center gap-x-1.5 gap-y-1 text-[12.5px]">
        @if ($delta)
            <span class="inline-flex items-center gap-0.5 font-semibold {{ $trend === 'down' ? 'text-rose-600 dark:text-rose-400' : 'text-brand' }}">
                <x-icon :name="$trend === 'down' ? 'arrow-down' : 'arrow-up'" class="size-3.5" />
                {{ $delta }}
            </span>
        @endif
        @if ($footnote)
            <span class="text-subtle">{{ $footnote }}</span>
        @endif
    </div>
</div>
