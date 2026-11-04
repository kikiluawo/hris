@props(['variant' => 'neutral'])

@php
    $tones = [
        'neutral' => 'border-line bg-surface-2 text-muted',
        'brand' => 'border-brand-line bg-brand-soft text-brand-soft-ink',
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-400/25 dark:bg-emerald-400/10 dark:text-emerald-300',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-400/25 dark:bg-amber-400/10 dark:text-amber-300',
        'danger' => 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-400/25 dark:bg-rose-400/10 dark:text-rose-300',
        'info' => 'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-400/25 dark:bg-sky-400/10 dark:text-sky-300',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border px-2.5 py-0.5 text-[11.5px] font-semibold ' . ($tones[$variant] ?? $tones['neutral'])]) }}>
    <span class="size-1.5 rounded-full bg-current opacity-70"></span>
    {{ $slot }}
</span>
