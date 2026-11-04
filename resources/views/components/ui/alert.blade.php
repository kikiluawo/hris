@props(['variant' => 'info'])

@php
    $tones = [
        'info' => 'border-line bg-surface-2 text-muted',
        'success' => 'border-brand-line bg-brand-soft text-brand-soft-ink',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-400/25 dark:bg-amber-400/10 dark:text-amber-300',
        'danger' => 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-400/25 dark:bg-rose-400/10 dark:text-rose-300',
    ];

    $icons = [
        'info' => 'alert-circle',
        'success' => 'check-circle',
        'warning' => 'alert-circle',
        'danger' => 'x-circle',
    ];
@endphp

<div role="alert" {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-xl border px-4 py-3 text-[13px] leading-relaxed ' . ($tones[$variant] ?? $tones['info'])]) }}>
    <x-icon :name="$icons[$variant] ?? 'alert-circle'" class="mt-0.5 size-[18px] shrink-0" />
    <div class="min-w-0">{{ $slot }}</div>
</div>
