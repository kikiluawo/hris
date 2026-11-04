<x-layouts.app title="Mutasi">
    <x-ui.page-header
        title="Mutasi karyawan"
        description="Riwayat perpindahan karyawan antar PT, cabang, departemen, dan jabatan beserta tanggal berlakunya."
    >
        <x-slot:actions>
            <x-ui.button icon="transfer" :href="route('transfers.create')">Mutasi karyawan</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="mb-4 grid gap-3 sm:grid-cols-2">
        <div class="flex items-center gap-3 rounded-2xl border border-line bg-surface px-5 py-4 shadow-card">
            <span class="flex size-10 items-center justify-center rounded-xl bg-brand-soft text-brand-soft-ink">
                <x-icon name="transfer" class="size-5" />
            </span>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-subtle">Total mutasi</p>
                <p class="text-[19px] font-semibold leading-tight">{{ $totalTransfers }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3 rounded-2xl border border-line bg-surface px-5 py-4 shadow-card">
            <span class="flex size-10 items-center justify-center rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-400/10 dark:text-amber-300">
                <x-icon name="briefcase" class="size-5" />
            </span>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-subtle">Dengan perubahan jabatan</p>
                <p class="text-[19px] font-semibold leading-tight">{{ $changedPositions }}</p>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('transfers.index') }}" class="mb-4 rounded-2xl border border-line bg-surface p-4 shadow-card">
        <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_220px_auto] md:items-end">
            <div>
                <x-ui.label for="q">Cari karyawan</x-ui.label>
                <x-ui.input name="q" type="search" icon="search" :value="$filters['q'] ?? null" placeholder="Nama, NIK, atau email" />
            </div>

            <div>
                <x-ui.label for="company">PT</x-ui.label>
                <x-ui.select
                    name="company"
                    :options="$companies->pluck('name', 'id')->all()"
                    :selected="$filters['company'] ?? null"
                    placeholder="Semua PT"
                    data-select2-clear
                />
            </div>

            <div class="flex gap-2">
                <x-ui.button type="submit" icon="filter">Terapkan</x-ui.button>
                @if (array_filter($filters))
                    <x-ui.button variant="ghost" :href="route('transfers.index')">Reset</x-ui.button>
                @endif
            </div>
        </div>
    </form>

    <x-ui.card :padded="false">
        <div class="flex flex-wrap items-center justify-between gap-2 px-5 py-4 sm:px-6">
            <div>
                <h2 class="text-[15px] font-semibold">Riwayat mutasi</h2>
                <p class="mt-0.5 text-[12.5px] text-muted">{{ $transfers->total() }} catatan ditemukan</p>
            </div>
            <span class="flex items-center gap-1.5 text-[11.5px] text-subtle">
                <span class="size-1.5 rounded-full bg-brand"></span>
                Mutasi langsung berlaku saat disimpan
            </span>
        </div>

        <div class="overflow-x-auto border-t border-line">
            <table class="w-full min-w-[980px] text-left">
                <thead class="bg-surface-2/60">
                    <tr class="text-[10.5px] uppercase tracking-[0.08em] text-subtle">
                        <th class="px-5 py-3 font-semibold sm:px-6">Karyawan</th>
                        <th class="px-3 py-3 font-semibold">Tanggal</th>
                        <th class="px-3 py-3 font-semibold">Dari</th>
                        <th class="px-3 py-3 font-semibold">Ke</th>
                        <th class="px-3 py-3 font-semibold">Perubahan</th>
                        <th class="px-5 py-3 text-right font-semibold sm:px-6"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transfers as $transfer)
                        @php
                            $otherChanges = collect(explode(', ', $transfer->changedAreas()))
                                ->reject(fn ($area) => $area === 'jabatan')
                                ->implode(', ');
                        @endphp
                        <tr class="border-t border-line/80 transition hover:bg-surface-2/40">
                            <td class="px-5 py-3.5 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <x-ui.avatar :name="$transfer->employee->name" size="md" />
                                    <div class="min-w-0">
                                        <a href="{{ route('employees.show', $transfer->employee) }}" class="block truncate text-[13.5px] font-medium hover:text-brand">{{ $transfer->employee->name }}</a>
                                        <p class="truncate text-[11.5px] text-subtle">{{ $transfer->employee->nik }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-3.5 text-[12.5px] text-muted">{{ $transfer->effective_date->translatedFormat('d M Y') }}</td>
                            <td class="px-3 py-3.5">
                                <p class="text-[12.5px] font-medium text-ink">{{ $transfer->fromCompany->code }} · {{ $transfer->fromBranch?->name ?? 'Tanpa cabang' }}</p>
                                <p class="text-[11.5px] text-subtle">{{ $transfer->fromDepartment->name }} · {{ $transfer->fromPosition->name }}</p>
                            </td>
                            <td class="px-3 py-3.5">
                                <p class="text-[12.5px] font-medium text-ink">{{ $transfer->toCompany->code }} · {{ $transfer->toBranch?->name ?? 'Tanpa cabang' }}</p>
                                <p class="text-[11.5px] text-subtle">{{ $transfer->toDepartment->name }} · {{ $transfer->toPosition->name }}</p>
                            </td>
                            <td class="px-3 py-3.5">
                                <x-ui.badge :variant="$transfer->position_changed ? 'warning' : 'neutral'">
                                    {{ $transfer->position_changed ? 'Jabatan berubah' : 'Jabatan tetap' }}
                                </x-ui.badge>
                                @if ($otherChanges)
                                    <p class="mt-1 text-[11px] text-subtle">{{ ucfirst($otherChanges) }} ikut berubah</p>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right sm:px-6">
                                <x-ui.button variant="ghost" size="sm" :href="route('employees.show', $transfer->employee)">Detail</x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-ui.empty-state
                                    icon="transfer"
                                    title="Belum ada mutasi"
                                    description="Catatan mutasi akan muncul setelah Anda memindahkan karyawan antar PT, cabang, departemen, atau jabatan."
                                >
                                    <x-slot:actions>
                                        <x-ui.button :href="route('transfers.create')">Mulai mutasi</x-ui.button>
                                    </x-slot:actions>
                                </x-ui.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $transfers->links('vendor.pagination.hr') }}
    </x-ui.card>
</x-layouts.app>
