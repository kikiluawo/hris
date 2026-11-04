@php
    $pendingLeaves = \App\Models\LeaveRequest::with('employee')->where('status', 'pending')->latest()->take(4)->get();
    $pendingCount = \App\Models\LeaveRequest::where('status', 'pending')->count();
@endphp

<header
    class="sticky top-0 z-30 border-b border-line/80 bg-canvas/85 backdrop-blur-md"
    @keydown.window.meta.k.prevent="$refs.search.focus()"
    @keydown.window.ctrl.k.prevent="$refs.search.focus()"
>
    <div class="flex h-16 items-center gap-2 px-4 sm:gap-3 sm:px-7 lg:px-10">
        <button
            type="button"
            @click="sidebarOpen = true"
            class="-ml-1 rounded-lg p-2 text-muted transition hover:bg-surface-2 hover:text-ink lg:hidden"
            aria-label="Buka menu"
        >
            <x-icon name="menu" class="size-5" />
        </button>

        <form action="{{ route('employees.index') }}" method="GET" role="search" class="relative hidden w-full max-w-[320px] sm:block">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex w-10 items-center justify-center text-subtle">
                <x-icon name="search" class="size-[17px]" />
            </span>
            <input
                type="search"
                name="q"
                x-ref="search"
                value="{{ request()->routeIs('employees.index') ? request('q') : '' }}"
                placeholder="Cari karyawan…"
                class="h-10 w-full rounded-xl border border-line bg-surface pl-10 pr-14 text-[13.5px] shadow-sm outline-none transition placeholder:text-subtle focus:border-brand focus:ring-4 focus:ring-brand/15"
            />
            <kbd class="pointer-events-none absolute inset-y-0 right-2.5 my-auto flex h-5 items-center rounded-md border border-line bg-surface-2 px-1.5 text-[10px] font-semibold text-subtle">⌘K</kbd>
        </form>

        <div class="ml-auto flex items-center gap-1">
            <button
                type="button"
                @click="$store.theme.toggle()"
                class="flex size-10 items-center justify-center rounded-xl text-muted transition hover:bg-surface-2 hover:text-ink"
                aria-label="Ganti tema"
            >
                <x-icon name="moon" class="size-[19px] dark:hidden" />
                <x-icon name="sun" class="hidden size-[19px] dark:block" />
            </button>

            <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                <button
                    type="button"
                    @click="open = !open"
                    class="relative flex size-10 items-center justify-center rounded-xl text-muted transition hover:bg-surface-2 hover:text-ink"
                    aria-label="Notifikasi"
                >
                    <x-icon name="bell" class="size-[19px]" />
                    @if ($pendingCount > 0)
                        <span class="absolute right-2.5 top-2.5 size-2 rounded-full bg-brand ring-2 ring-canvas"></span>
                    @endif
                </button>

                <div
                    x-show="open"
                    x-cloak
                    x-transition.origin.top.right
                    class="absolute right-0 mt-2 w-[320px] overflow-hidden rounded-2xl border border-line bg-surface shadow-pop"
                >
                    <div class="flex items-center justify-between border-b border-line px-4 py-3">
                        <p class="text-[13px] font-semibold">Notifikasi</p>
                        @if ($pendingCount > 0)
                            <x-ui.badge variant="brand">{{ $pendingCount }} menunggu</x-ui.badge>
                        @endif
                    </div>

                    @forelse ($pendingLeaves as $leave)
                        <a href="{{ route('leaves.index', ['status' => 'pending']) }}" class="flex gap-3 px-4 py-3 transition hover:bg-surface-2">
                            <x-ui.avatar :name="$leave->employee->name" size="xs" class="mt-0.5" />
                            <span class="min-w-0">
                                <span class="block truncate text-[12.5px] leading-snug">
                                    <span class="font-semibold">{{ $leave->employee->name }}</span> mengajukan {{ $leave->typeLabel() }}
                                </span>
                                <span class="block text-[11px] text-subtle">{{ $leave->start_date->translatedFormat('d M') }} · {{ $leave->days }} hari</span>
                            </span>
                        </a>
                    @empty
                        <p class="px-4 py-8 text-center text-[12.5px] text-subtle">Tidak ada pengajuan baru.</p>
                    @endforelse

                    <a href="{{ route('leaves.index') }}" class="flex items-center justify-center gap-1.5 border-t border-line px-4 py-2.5 text-[12px] font-semibold text-brand transition hover:bg-surface-2">
                        Lihat semua pengajuan
                        <x-icon name="arrow-right" class="size-3.5" />
                    </a>
                </div>
            </div>

            <span class="mx-1 hidden h-6 w-px bg-line sm:block"></span>

            <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                <button type="button" @click="open = !open" class="flex items-center gap-2 rounded-xl py-1 pl-1 pr-2 transition hover:bg-surface-2">
                    <x-ui.avatar :name="auth()->user()->name" size="sm" />
                    <span class="hidden text-left md:block">
                        <span class="block max-w-[140px] truncate text-[12.5px] font-semibold leading-tight">{{ auth()->user()->name }}</span>
                        <span class="block text-[11px] text-subtle">Administrator</span>
                    </span>
                    <x-icon name="chevron-down" class="size-4 text-subtle" />
                </button>

                <div
                    x-show="open"
                    x-cloak
                    x-transition.origin.top.right
                    class="absolute right-0 mt-2 w-60 overflow-hidden rounded-2xl border border-line bg-surface shadow-pop"
                >
                    <div class="border-b border-line px-4 py-3">
                        <p class="truncate text-[13px] font-semibold">{{ auth()->user()->name }}</p>
                        <p class="truncate text-[11.5px] text-subtle">{{ auth()->user()->email }}</p>
                    </div>

                    <div class="p-1.5">
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-[13px] text-muted transition hover:bg-surface-2 hover:text-ink">
                            <x-icon name="user" class="size-4" />
                            Profil saya
                        </a>
                        <button type="button" @click="$store.theme.toggle()" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-[13px] text-muted transition hover:bg-surface-2 hover:text-ink">
                            <x-icon name="moon" class="size-4 dark:hidden" />
                            <x-icon name="sun" class="hidden size-4 dark:block" />
                            Ganti tema
                        </button>
                    </div>

                    <div class="border-t border-line p-1.5">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-[13px] font-medium text-rose-600 transition hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/10">
                                <x-icon name="logout" class="size-4" />
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
