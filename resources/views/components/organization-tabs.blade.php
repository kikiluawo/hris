@php
    $tabs = [
        ['label' => 'Perusahaan', 'route' => 'companies.index'],
        ['label' => 'Cabang', 'route' => 'branches.index'],
        ['label' => 'Departemen', 'route' => 'departments.index'],
        ['label' => 'Jabatan', 'route' => 'positions.index'],
    ];
@endphp

<div class="mb-5 inline-flex flex-wrap gap-1 rounded-xl border border-line bg-surface p-1.5 shadow-card">
    @foreach ($tabs as $tab)
        @php $active = request()->routeIs($tab['route']); @endphp
        <a
            href="{{ route($tab['route']) }}"
            class="rounded-lg px-3.5 py-2 text-[13px] font-semibold transition {{ $active ? 'bg-brand-soft text-brand-soft-ink' : 'text-muted hover:bg-surface-2 hover:text-ink' }}"
            @if ($active) aria-current="page" @endif
        >
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>
