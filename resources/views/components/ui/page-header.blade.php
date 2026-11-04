@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div>
        <h1 class="text-[25px] font-semibold leading-tight tracking-tight sm:text-[27px]">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1.5 max-w-2xl text-[13.5px] text-muted">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
