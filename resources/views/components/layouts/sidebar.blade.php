@php
    $sidebarUser = auth()->user();
    $requestBadge = 0;
    if ($sidebarUser?->isCeo()) {
        $requestBadge = \App\Models\HiringRequest::where('status', \App\Models\HiringRequest::STATUS_PENDING_APPROVAL)
            ->whereIn('approval_route', ['ceo', 'both'])
            ->where('ceo_approval_status', 'pending')
            ->count();
    } elseif ($sidebarUser?->isCfo()) {
        $requestBadge = \App\Models\HiringRequest::where('status', \App\Models\HiringRequest::STATUS_PENDING_APPROVAL)
            ->whereIn('approval_route', ['cfo', 'both'])
            ->where('cfo_approval_status', 'pending')
            ->count();
    } else {
        $requestBadge = \App\Models\HiringRequest::where('status', \App\Models\HiringRequest::STATUS_NEW)->count();
    }

    $groups = [
        'Ruang kerja' => [
            ['label' => 'Dashboard', 'icon' => 'grid', 'route' => 'dashboard', 'match' => 'dashboard'],
            ['label' => 'Karyawan', 'icon' => 'users', 'route' => 'employees.index', 'match' => 'employees.*'],
            ['label' => 'Mutasi', 'icon' => 'transfer', 'route' => 'transfers.index', 'match' => 'transfers.*'],
            ['label' => 'Cuti & Izin', 'icon' => 'calendar', 'route' => 'leaves.index', 'match' => 'leaves.*'],
        ],
        'Organisasi' => [
            ['label' => 'Perusahaan', 'icon' => 'building', 'route' => 'companies.index', 'match' => 'companies.*'],
            ['label' => 'Cabang', 'icon' => 'map-pin', 'route' => 'branches.index', 'match' => 'branches.*'],
            ['label' => 'Departemen', 'icon' => 'sitemap', 'route' => 'departments.index', 'match' => 'departments.*'],
            ['label' => 'Jabatan', 'icon' => 'briefcase', 'route' => 'positions.index', 'match' => 'positions.*'],
        ],
        'Rekrutmen' => [
            ['label' => 'Lowongan', 'icon' => 'file-text', 'route' => 'openings.index', 'match' => 'openings.*'],
            ['label' => 'Permintaan', 'icon' => 'bell', 'route' => 'hiring-requests.index', 'match' => ['hiring-requests.index', 'hiring-requests.show']],
            ['label' => 'Pelamar', 'icon' => 'users', 'route' => 'applicants.index', 'match' => 'applicants.*'],
            ['label' => 'Tes online', 'icon' => 'clipboard', 'route' => 'assessments.index', 'match' => 'assessments.*'],
            ['label' => 'Bank soal', 'icon' => 'copy', 'route' => 'question-bank.index', 'match' => 'question-bank.*'],
        ],
    ];
@endphp

<aside
    class="fixed inset-y-0 left-0 z-50 flex w-[280px] max-w-[85vw] flex-col border-r border-line bg-surface transition-transform duration-300 ease-out -translate-x-full lg:w-[264px] lg:translate-x-0"
    :class="$store.sidebar.open ? '!translate-x-0 shadow-2xl pointer-events-auto' : '-translate-x-full pointer-events-none lg:pointer-events-auto'"
    aria-label="Navigasi utama"
>
    <div class="flex h-16 shrink-0 items-center justify-between border-b border-gold/15 bg-[#0c1e38] px-5 text-white">
        <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-2.5">
            <img
                src="{{ asset('images/majesty-logo.png') }}"
                alt="Majesty Group"
                class="h-7 w-auto shrink-0 object-contain opacity-95"
            />
            <span class="h-5 w-px shrink-0 bg-gold/30" aria-hidden="true"></span>
            <span class="truncate text-[10px] font-semibold uppercase tracking-[0.18em] text-gold">HR Portal</span>
        </a>

        <button
            type="button"
            @click="$store.sidebar.close()"
            class="flex size-9 items-center justify-center rounded-xl bg-white/10 text-white transition hover:bg-white/20 active:scale-95 lg:hidden"
            aria-label="Tutup menu"
        >
            <x-icon name="close" class="size-5" />
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4">
        @foreach ($groups as $groupLabel => $navigation)
            <p class="px-3 pb-2 text-[10.5px] font-bold uppercase tracking-[0.12em] text-subtle {{ $loop->first ? '' : 'pt-6' }}">{{ $groupLabel }}</p>

            <div class="space-y-1">
                @foreach ($navigation as $item)
                    @php $active = request()->routeIs($item['match']); @endphp
                    <a
                        href="{{ route($item['route']) }}"
                        @click="if (window.innerWidth < 1024) $store.sidebar.close()"
                        class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-[14px] font-medium transition active:scale-[0.98] {{ $active ? 'bg-brand-soft text-brand-soft-ink font-semibold' : 'text-muted hover:bg-surface-2 hover:text-ink' }}"
                        @if ($active) aria-current="page" @endif
                    >
                        <x-icon :name="$item['icon']" class="size-[19px] {{ $active ? 'text-brand' : 'text-subtle group-hover:text-muted' }}" />
                        {{ $item['label'] }}
                        @if ($item['route'] === 'hiring-requests.index' && $requestBadge > 0)
                            <span class="ml-auto rounded-full bg-brand px-1.5 py-0.5 text-[10.5px] font-bold leading-none text-brand-ink">{{ $requestBadge }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endforeach

        <p class="px-3 pb-2 pt-6 text-[10.5px] font-bold uppercase tracking-[0.12em] text-subtle">Akun</p>

        <div class="space-y-1">
            @php $profileActive = request()->routeIs('profile.*'); @endphp
            <a
                href="{{ route('profile.edit') }}"
                @click="if (window.innerWidth < 1024) $store.sidebar.close()"
                class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-[14px] font-medium transition active:scale-[0.98] {{ $profileActive ? 'bg-brand-soft text-brand-soft-ink font-semibold' : 'text-muted hover:bg-surface-2 hover:text-ink' }}"
                @if ($profileActive) aria-current="page" @endif
            >
                <x-icon name="user" class="size-[19px] {{ $profileActive ? 'text-brand' : 'text-subtle group-hover:text-muted' }}" />
                Profil & Pengaturan
            </a>
        </div>
    </nav>

    <div class="border-t border-line p-3">
        <a
            href="{{ route('profile.edit') }}"
            @click="if (window.innerWidth < 1024) $store.sidebar.close()"
            class="flex items-center gap-3 rounded-xl p-2.5 transition hover:bg-surface-2 active:scale-[0.98]"
        >
            <x-ui.avatar :name="auth()->user()->name" size="sm" />
            <span class="min-w-0 flex-1">
                <span class="flex items-center gap-1.5">
                    <span class="truncate text-[12.5px] font-semibold">{{ auth()->user()->name }}</span>
                    <span class="rounded bg-gold/20 px-1.5 py-0.5 text-[9.5px] font-bold uppercase tracking-wider text-gold-strong">{{ auth()->user()->roleLabel() }}</span>
                </span>
                <span class="block truncate text-[11px] text-subtle">{{ auth()->user()->email }}</span>
            </span>
            <x-icon name="settings" class="size-4 shrink-0 text-subtle" />
        </a>

        <div class="mt-2 flex items-center gap-2 rounded-lg px-2.5 py-1.5 text-[11px] text-subtle">
            <span class="size-1.5 rounded-full bg-amber-400"></span>
            Mode demo · data contoh
        </div>
    </div>
</aside>
