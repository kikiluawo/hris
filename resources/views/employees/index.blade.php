<x-layouts.app title="Karyawan">
    <x-ui.page-header
        title="Karyawan"
        description="Kelola data seluruh anggota tim, lengkap dengan PT, cabang, departemen, dan status kepegawaian."
    >
        <x-slot:actions>
            <x-ui.button icon="plus" :href="route('employees.create')">Tambah karyawan</x-ui.button>
            <x-ui.button variant="secondary" icon="transfer" :href="route('transfers.index')">Riwayat mutasi</x-ui.button>
            <x-ui.button variant="secondary" icon="download" :href="route('employees.export', array_filter($filters))">Ekspor CSV</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form method="GET" action="{{ route('employees.index') }}" class="mb-4 rounded-2xl border border-line bg-surface p-4 shadow-card">
        <input type="hidden" name="per_page" value="{{ $perPage }}">
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_220px_220px_160px_auto] xl:items-end">
            <div>
                <x-ui.label for="q">Cari karyawan</x-ui.label>
                <x-ui.input name="q" type="search" icon="search" :value="$filters['q'] ?? null" placeholder="Nama, no. staff, email, atau KTP" />
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

            <div>
                <x-ui.label for="department">Departemen</x-ui.label>
                <x-ui.select
                    name="department"
                    :options="$departments->mapWithKeys(fn ($department) => [$department->id => $department->company->code . ' · ' . $department->name])->all()"
                    :selected="$filters['department'] ?? null"
                    placeholder="Semua departemen"
                    data-select2-clear
                />
            </div>

            <div>
                <x-ui.label for="status">Status</x-ui.label>
                <x-ui.select
                    name="status"
                    :options="$statuses"
                    :selected="$filters['status'] ?? null"
                    placeholder="Semua status"
                    data-select2-clear
                />
            </div>

            <div class="flex flex-wrap items-center gap-2 sm:col-span-2 xl:col-span-1">
                <x-ui.button type="submit" icon="filter" class="w-full sm:w-auto">Terapkan</x-ui.button>
                @if (array_filter($filters))
                    <x-ui.button variant="ghost" :href="route('employees.index')" class="w-full sm:w-auto">Reset</x-ui.button>
                @endif
            </div>
        </div>
    </form>

    <x-ui.card :padded="false">
        <div class="flex flex-wrap items-center justify-between gap-2 px-5 py-4 sm:px-6">
            <div>
                <h2 class="text-[15px] font-semibold">Daftar karyawan</h2>
                <p class="mt-0.5 text-[12.5px] text-muted">{{ $employees->total() }} karyawan ditemukan</p>
            </div>
            <span class="flex items-center gap-1.5 text-[11.5px] text-subtle">
                <span class="size-1.5 rounded-full bg-brand"></span>
                Data diperbarui otomatis
            </span>
        </div>

        <div class="overflow-x-auto border-t border-line">
            <table class="w-full min-w-[960px] text-left">
                <thead class="bg-surface-2/60">
                    <tr class="text-[10.5px] uppercase tracking-[0.08em] text-subtle">
                        <th class="px-5 py-3 font-semibold sm:px-6">Karyawan</th>
                        <th class="px-3 py-3 font-semibold">PT · Cabang</th>
                        <th class="px-3 py-3 font-semibold">Departemen</th>
                        <th class="px-3 py-3 font-semibold">Jabatan</th>
                        <th class="px-3 py-3 font-semibold">Status</th>
                        <th class="px-3 py-3 font-semibold">Bergabung</th>
                        <th class="px-5 py-3 text-right font-semibold sm:px-6">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $employee)
                        <tr class="border-t border-line/80 transition hover:bg-surface-2/40">
                            <td class="px-5 py-3.5 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <x-ui.avatar :name="$employee->name" size="md" />
                                    <div class="min-w-0 max-w-[220px]">
                                        <a href="{{ route('employees.show', $employee) }}" class="block truncate text-[13.5px] font-medium hover:text-brand">{{ $employee->name }}</a>
                                        <p class="truncate text-[11.5px] text-subtle">{{ $employee->nik }} · {{ $employee->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-3.5">
                                <p class="text-[12.5px] font-medium text-ink">{{ $employee->company->code }} · {{ $employee->company->name }}</p>
                                <p class="text-[11.5px] text-subtle">{{ $employee->branch?->name ?? 'Tanpa cabang' }}</p>
                            </td>
                            <td class="whitespace-nowrap px-3 py-3.5 text-[12.5px] text-muted">{{ $employee->department->name }}</td>
                            <td class="whitespace-nowrap px-3 py-3.5 text-[12.5px] text-muted">{{ $employee->position->name }}</td>
                            <td class="whitespace-nowrap px-3 py-3.5">
                                <x-ui.badge :variant="$employee->employmentStatusTone()">{{ $employee->employmentStatusLabel() }}</x-ui.badge>
                            </td>
                            <td class="whitespace-nowrap px-3 py-3.5 text-[12.5px] text-muted">
                                <p>{{ $employee->joined_at->translatedFormat('d M Y') }}</p>
                                @if ($employee->activeContractLabel())
                                    <p class="text-[11.5px] text-subtle">{{ $employee->activeContractLabel() }}</p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-right sm:px-6">
                                <div class="flex items-center justify-end gap-1.5">
                                    <x-ui.button variant="secondary" size="sm" icon="eye" :href="route('employees.show', $employee)">Detail</x-ui.button>
                                    <x-ui.button variant="ghost" size="icon" icon="pencil" :href="route('employees.edit', $employee)" aria-label="Edit {{ $employee->name }}" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        @php($hasFilters = filled($filters['q'] ?? null) || filled($filters['company'] ?? null) || filled($filters['department'] ?? null) || filled($filters['status'] ?? null))
                        <tr>
                            <td colspan="7">
                                <x-ui.empty-state
                                    icon="users"
                                    :title="$hasFilters ? 'Karyawan tidak ditemukan' : 'Belum ada karyawan'"
                                    :description="$hasFilters ? 'Coba ubah kata kunci atau filter yang Anda gunakan.' : 'Tambahkan karyawan pertama untuk mulai mengelola data tim.'"
                                >
                                    <x-slot:actions>
                                        @if ($hasFilters)
                                            <x-ui.button variant="secondary" :href="route('employees.index')">Hapus filter</x-ui.button>
                                        @else
                                            <x-ui.button :href="route('employees.create')" icon="plus">Tambah karyawan</x-ui.button>
                                        @endif
                                    </x-slot:actions>
                                </x-ui.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $employees->links('vendor.pagination.hr-per-page', ['perPage' => $perPage, 'action' => route('employees.index')]) }}
    </x-ui.card>
</x-layouts.app>
