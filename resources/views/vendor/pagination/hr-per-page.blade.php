@php
    $perPageValue = (int) ($perPage ?? $paginator->perPage());
    $perPageOptions = $options ?? [10, 25, 50, 100];
    $formAction = $action ?? url()->current();
@endphp

@if ($paginator->total() > 0)
    <div class="flex flex-col gap-3 border-t border-line px-5 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-wrap items-center justify-between gap-3 sm:justify-start sm:gap-4">
            <p class="text-[12.5px] text-muted">
                Menampilkan
                <span class="font-semibold text-ink">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</span>
                dari
                <span class="font-semibold text-ink">{{ $paginator->total() }}</span>
                data
            </p>

            <span class="hidden h-4 w-px bg-line sm:inline-block" aria-hidden="true"></span>

            <form method="GET" action="{{ $formAction }}" class="inline-flex items-center gap-2">
                @foreach (request()->except(['per_page', 'page', 'edit']) as $key => $param)
                    @if (is_array($param))
                        @foreach ($param as $item)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                        @endforeach
                    @elseif ($param !== null && $param !== '')
                        <input type="hidden" name="{{ $key }}" value="{{ $param }}">
                    @endif
                @endforeach

                <span class="text-[12.5px] text-muted">Tampilkan</span>
                <div class="relative inline-flex items-center">
                    <select
                        name="per_page"
                        onchange="this.form.submit()"
                        aria-label="Jumlah data per halaman"
                        class="h-8 appearance-none rounded-lg border border-line bg-surface pl-2.5 pr-7 text-[12.5px] font-semibold text-ink shadow-sm outline-none transition hover:border-brand/50 focus:border-brand focus:ring-2 focus:ring-brand/15"
                    >
                        @foreach ($perPageOptions as $option)
                            <option value="{{ $option }}" @selected($perPageValue === (int) $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 right-0 flex w-6 items-center justify-center text-subtle">
                        <x-icon name="chevron-down" class="size-3.5" />
                    </span>
                </div>
            </form>
        </div>

        @if ($paginator->hasPages())
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
        @endif
    </div>
@endif
