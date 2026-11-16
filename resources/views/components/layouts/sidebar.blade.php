@php
    $newHiringRequests = \App\Models\HiringRequest::where('status', \App\Models\HiringRequest::STATUS_NEW)->count();

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
    class="fixed inset-y-0 left-0 z-40 flex w-[264px] flex-col border-r border-line bg-surface transition-transform duration-300 lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0 shadow-pop' : '-translate-x-full'"
    aria-label="Navigasi utama"
>
    <div class="flex h-16 shrink-0 items-center justify-between px-5">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <span class="flex size-9 items-center justify-center rounded-xl bg-brand text-brand-ink shadow-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="size-5" aria-hidden="true">
                    <path d="M8 6v12M16 6v12M8 12h8" />
                </svg>
            </span>
            <span class="leading-tight">
                <span class="block text-[15px] font-bold tracking-tight">HRD System</span>
                <span class="block text-[10px] font-semibold uppercase tracking-[0.14em] text-subtle">People Ops</span>
            </span>
        </a>

        <button type="button" @click="sidebarOpen = false" class="rounded-lg p-1.5 text-subtle transition hover:bg-surface-2 hover:text-ink lg:hidden" aria-label="Tutup menu">
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
                        class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13.5px] font-medium transition {{ $active ? 'bg-brand-soft text-brand-soft-ink' : 'text-muted hover:bg-surface-2 hover:text-ink' }}"
                        @if ($active) aria-current="page" @endif
                    >
                        <x-icon :name="$item['icon']" class="size-[19px] {{ $active ? 'text-brand' : 'text-subtle group-hover:text-muted' }}" />
                        {{ $item['label'] }}
                        @if ($item['route'] === 'hiring-requests.index' && $newHiringRequests > 0)
                            <span class="ml-auto rounded-full bg-brand px-1.5 py-0.5 text-[10.5px] font-bold leading-none text-brand-ink">{{ $newHiringRequests }}</span>
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
                class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13.5px] font-medium transition {{ $profileActive ? 'bg-brand-soft text-brand-soft-ink' : 'text-muted hover:bg-surface-2 hover:text-ink' }}"
                @if ($profileActive) aria-current="page" @endif
            >
                <x-icon name="user" class="size-[19px] {{ $profileActive ? 'text-brand' : 'text-subtle group-hover:text-muted' }}" />
                Profil & Pengaturan
            </a>
        </div>
    </nav>

    <div class="border-t border-line p-3">
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-xl p-2.5 transition hover:bg-surface-2">
            <x-ui.avatar :name="auth()->user()->name" size="sm" />
            <span class="min-w-0 flex-1">
                <span class="block truncate text-[12.5px] font-semibold">{{ auth()->user()->name }}</span>
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
