@props(['href' => null, 'variant' => 'primary', 'size' => 'md', 'type' => 'button', 'icon' => null])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-xl font-semibold transition-colors duration-150 focus:outline-none focus-visible:ring-4 disabled:pointer-events-none disabled:opacity-50';

    $variants = [
        'primary' => 'bg-brand text-brand-ink shadow-sm hover:bg-brand-hover focus-visible:ring-brand/20',
        'secondary' => 'border border-line bg-surface text-ink shadow-sm hover:bg-surface-2 focus-visible:ring-brand/15',
        'soft' => 'bg-brand-soft text-brand-soft-ink hover:bg-brand-soft/70 focus-visible:ring-brand/15',
        'ghost' => 'text-muted hover:bg-surface-2 hover:text-ink focus-visible:ring-brand/15',
        'danger' => 'bg-rose-600 text-white shadow-sm hover:bg-rose-700 focus-visible:ring-rose-500/25',
    ];

    $sizes = [
        'sm' => 'h-9 px-3 text-[13px]',
        'md' => 'h-10 px-4 text-sm',
        'lg' => 'h-11 px-5 text-sm',
        'icon' => 'size-10 p-0',
    ];

    $classes = $base . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
    $navigate = $attributes->get('target') === '_blank'
        ? "window.open(this.dataset.href, '_blank')"
        : 'window.location.href = this.dataset.href';
@endphp

<button
    type="{{ $href ? 'button' : $type }}"
    @if ($href) data-href="{{ $href }}" onclick="{{ $navigate }}" @endif
    {{ $attributes->except('target')->merge(['class' => $classes]) }}
>
    @if ($icon)
        <x-icon :name="$icon" class="size-4" />
    @endif
    {{ $slot }}
</button>
