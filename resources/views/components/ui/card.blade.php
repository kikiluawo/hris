@props(['title' => null, 'description' => null, 'padded' => true])

<div {{ $attributes->merge(['class' => 'min-w-0 rounded-2xl border border-line bg-surface shadow-card']) }}>
    @if ($title)
        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-line px-5 py-4 sm:px-6">
            <div>
                <h2 class="text-[15px] font-semibold">{{ $title }}</h2>
                @if ($description)
                    <p class="mt-0.5 text-[13px] text-muted">{{ $description }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div class="{{ $padded ? 'p-5 sm:p-6' : '' }}">
        {{ $slot }}
    </div>
</div>
