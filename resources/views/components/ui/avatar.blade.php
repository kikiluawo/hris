@props(['name' => '', 'size' => 'md'])

@php
    $initials = collect(preg_split('/\s+/', trim($name)))
        ->filter()
        ->take(2)
        ->map(fn ($word) => mb_substr($word, 0, 1))
        ->implode('');

    $tones = [
        'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300',
        'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300',
        'bg-sky-100 text-sky-800 dark:bg-sky-500/15 dark:text-sky-300',
        'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
        'bg-violet-100 text-violet-800 dark:bg-violet-500/15 dark:text-violet-300',
        'bg-teal-100 text-teal-800 dark:bg-teal-500/15 dark:text-teal-300',
    ];

    $tone = $tones[abs(crc32($name)) % count($tones)];

    $sizes = [
        'xs' => 'size-7 text-[10px]',
        'sm' => 'size-8 text-[11px]',
        'md' => 'size-9 text-xs',
        'lg' => 'size-11 text-sm',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex shrink-0 select-none items-center justify-center rounded-full font-bold tracking-wide ' . ($sizes[$size] ?? $sizes['md']) . ' ' . $tone]) }}>
    {{ $initials !== '' ? $initials : '?' }}
</span>
