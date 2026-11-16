<x-layouts.app title="Cuti & Izin">
    <x-ui.page-header
        title="Cuti & Izin"
        description="Tinjau, setujui, atau tolak pengajuan cuti dan izin dari seluruh tim."
    />

    <div class="mb-4 grid gap-3 sm:grid-cols-3">
        <div class="flex items-center gap-3 rounded-2xl border border-line bg-surface px-4 py-3.5 shadow-card">
            <span class="flex size-9 items-center justify-center rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-400/10 dark:text-amber-300">
                <x-icon name="clock" class="size-[18px]" />
            </span>
            <div>
                <p class="text-[10.5px] font-semibold uppercase tracking-[0.08em] text-subtle">Menunggu</p>
                <p class="text-[19px] font-semibold leading-tight tabular-nums">{{ $counts['pending'] }}</p>
            </div>
        </div>

        <div class="flex items-center gap-3 rounded-2xl border border-line bg-surface px-4 py-3.5 shadow-card">
            <span class="flex size-9 items-center justify-center rounded-xl bg-brand-soft text-brand-soft-ink">
                <x-icon name="check-circle" class="size-[18px]" />
            </span>
            <div>
                <p class="text-[10.5px] font-semibold uppercase tracking-[0.08em] text-subtle">Disetujui</p>
                <p class="text-[19px] font-semibold leading-tight tabular-nums">{{ $counts['approved'] }}</p>
            </div>
        </div>

        <div class="flex items-center gap-3 rounded-2xl border border-line bg-surface px-4 py-3.5 shadow-card">
            <span class="flex size-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400">
                <x-icon name="x-circle" class="size-[18px]" />
            </span>
            <div>
                <p class="text-[10.5px] font-semibold uppercase tracking-[0.08em] text-subtle">Ditolak</p>
                <p class="text-[19px] font-semibold leading-tight tabular-nums">{{ $counts['rejected'] }}</p>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('leaves.index') }}" class="mb-4 rounded-2xl border border-line bg-surface p-4 shadow-card">
        <div class="grid gap-3 sm:grid-cols-[200px_200px_auto] sm:items-end">
            <div>
                <x-ui.label for="status">Status</x-ui.label>
                <x-ui.select
                    name="status"
                    :options="['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak']"
                    :selected="$filters['status'] ?? null"
                    placeholder="Semua status"
                    data-select2-clear
                />
            </div>

            <div>
                <x-ui.label for="type">Jenis</x-ui.label>
                <x-ui.select
                    name="type"
                    :options="['annual' => 'Cuti tahunan', 'sick' => 'Cuti sakit', 'permit' => 'Izin', 'unpaid' => 'Cuti tanpa gaji']"
                    :selected="$filters['type'] ?? null"
                    placeholder="Semua jenis"
                    data-select2-clear
                />
            </div>

            <div class="flex gap-2">
                <x-ui.button type="submit" icon="filter">Terapkan</x-ui.button>
                @if (array_filter($filters))
                    <x-ui.button variant="ghost" :href="route('leaves.index')">Reset</x-ui.button>
                @endif
            </div>
        </div>
    </form>

    <x-ui.card :padded="false">
        <div class="flex flex-wrap items-center justify-between gap-2 px-5 py-4 sm:px-6">
            <div>
                <h2 class="text-[15px] font-semibold">Daftar pengajuan</h2>
                <p class="mt-0.5 text-[12.5px] text-muted">{{ $leaves->total() }} pengajuan ditemukan</p>
            </div>
        </div>

        <div class="overflow-x-auto border-t border-line">
            <table class="w-full min-w-[900px] text-left">
                <thead class="bg-surface-2/60">
                    <tr class="text-[10.5px] uppercase tracking-[0.08em] text-subtle">
                        <th class="px-5 py-3 font-semibold sm:px-6">Karyawan</th>
                        <th class="px-3 py-3 font-semibold">Jenis</th>
                        <th class="px-3 py-3 font-semibold">Tanggal</th>
                        <th class="px-3 py-3 font-semibold">Durasi</th>
                        <th class="px-3 py-3 font-semibold">Alasan</th>
                        <th class="px-3 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 text-right font-semibold sm:px-6">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leaves as $leave)
                        <tr class="border-t border-line/80 transition hover:bg-surface-2/40">
                            <td class="px-5 py-3.5 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <x-ui.avatar :name="$leave->employee->name" size="md" />
                                    <div class="min-w-0">
                                        <p class="truncate text-[13.5px] font-medium">{{ $leave->employee->name }}</p>
                                        <p class="truncate text-[11.5px] text-subtle">{{ $leave->employee->company->code }} · {{ $leave->employee->department->name }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-3.5 text-[12.5px] text-muted">{{ $leave->typeLabel() }}</td>
                            <td class="whitespace-nowrap px-3 py-3.5 text-[12.5px] text-muted">
                                {{ $leave->start_date->translatedFormat('d M Y') }}
                                @if (! $leave->start_date->equalTo($leave->end_date))
                                    <span class="text-subtle">– {{ $leave->end_date->translatedFormat('d M Y') }}</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-3.5 text-[12.5px] tabular-nums text-muted">{{ $leave->days }} hari</td>
                            <td class="max-w-[220px] px-3 py-3.5">
                                <p class="truncate text-[12.5px] text-muted" title="{{ $leave->reason }}">{{ $leave->reason }}</p>
                            </td>
                            <td class="whitespace-nowrap px-3 py-3.5">
                                <x-ui.badge :variant="$leave->statusTone()">{{ $leave->statusLabel() }}</x-ui.badge>
                            </td>
                            <td class="px-5 py-3.5 sm:px-6">
                                @if ($leave->status === 'pending')
                                    <div class="flex items-center justify-end gap-1.5">
                                        <form method="POST" action="{{ route('leaves.approve', $leave) }}" data-confirm="Pengajuan {{ $leave->typeLabel() }} oleh {{ $leave->employee->name }} akan disetujui." data-confirm-title="Setujui pengajuan?" data-confirm-label="Ya, setujui">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" title="Setujui" class="flex size-8 items-center justify-center rounded-lg border border-brand-line bg-brand-soft text-brand-soft-ink transition hover:bg-brand-soft/70">
                                                <x-icon name="check" class="size-4" />
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('leaves.reject', $leave) }}" data-confirm="Pengajuan {{ $leave->typeLabel() }} oleh {{ $leave->employee->name }} akan ditolak." data-confirm-title="Tolak pengajuan?" data-confirm-tone="danger" data-confirm-label="Ya, tolak">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" title="Tolak" class="flex size-8 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-rose-600 transition hover:bg-rose-100 dark:border-rose-400/25 dark:bg-rose-500/10 dark:text-rose-400 dark:hover:bg-rose-500/20">
                                                <x-icon name="close" class="size-4" />
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <p class="text-right text-[11px] text-subtle">Selesai</p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-ui.empty-state
                                    icon="calendar"
                                    title="Belum ada pengajuan"
                                    description="Pengajuan cuti dan izin dari tim akan muncul di sini."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $leaves->links('vendor.pagination.hr') }}
    </x-ui.card>
</x-layouts.app>
