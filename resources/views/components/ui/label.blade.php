@props(['for' => null])

<label @if ($for) for="{{ $for }}" @endif {{ $attributes->merge(['class' => 'mb-1.5 block text-[13px] font-semibold text-ink']) }}>
    {{ $slot }}
</label>
