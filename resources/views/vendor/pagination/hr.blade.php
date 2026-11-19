@if ($paginator->hasPages())
    <div class="flex flex-col gap-3 border-t border-line px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-[12.5px] text-muted">
            Menampilkan
            <span class="font-semibold text-ink">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</span>
            dari
            <span class="font-semibold text-ink">{{ $paginator->total() }}</span>
            data
        </p>

        <nav class="flex flex-wrap items-center gap-1" aria-label="Navigasi halaman">
            @if ($paginator->onFirstPage())
                <span class="flex size-9 cursor-not-allowed items-center justify-center rounded-lg border border-line text-subtle opacity-60">
                    <x-icon name="chevron-left" class="size-4" />
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="flex size-9 items-center justify-center rounded-lg border border-line bg-surface text-muted transition hover:bg-surface-2 hover:text-ink">
                    <x-icon name="chevron-left" class="size-4" />
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="flex size-9 items-center justify-center text-[13px] text-subtle">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="flex size-9 items-center justify-center rounded-lg bg-brand text-[13px] font-semibold text-brand-ink">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="flex size-9 items-center justify-center rounded-lg border border-line bg-surface text-[13px] text-muted transition hover:bg-surface-2 hover:text-ink">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="flex size-9 items-center justify-center rounded-lg border border-line bg-surface text-muted transition hover:bg-surface-2 hover:text-ink">
                    <x-icon name="chevron-right" class="size-4" />
                </a>
            @else
                <span class="flex size-9 cursor-not-allowed items-center justify-center rounded-lg border border-line text-subtle opacity-60">
                    <x-icon name="chevron-right" class="size-4" />
                </span>
            @endif
        </nav>
    </div>
@endif
