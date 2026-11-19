<x-layouts.app title="Departemen">
    <x-ui.page-header
        title="Departemen"
        description="Setiap PT memiliki daftar departemennya sendiri. Nama departemen boleh sama antar PT karena terpisah per perusahaan."
    />

    <x-organization-tabs />

    @if (session('error'))
        <x-ui.alert variant="danger" class="mb-4">{{ session('error') }}</x-ui.alert>
    @endif

    @if ($errors->any())
        <x-ui.alert variant="danger" class="mb-4">
            <ul class="list-disc space-y-0.5 pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    @php
        $hasFilter = (bool) array_filter($filters);
        $queryParams = array_filter([
            ...$filters,
            'per_page' => $perPage !== 10 ? $perPage : null,
            'page' => request()->integer('page') > 1 ? request()->integer('page') : null,
        ]);
    @endphp
    <div class="grid gap-4 lg:grid-cols-[330px_minmax(0,1fr)] lg:items-start">
        <x-ui.card :title="$editing ? 'Edit departemen' : 'Tambah departemen'" description="Departemen harus terikat pada satu PT.">
            <form
                method="POST"
                action="{{ $editing ? route('departments.update', $editing) : route('departments.store') }}"
                class="space-y-4"
            >
                @csrf
                @if ($editing)
                    @method('PATCH')
                @endif

                <div>
                    <x-ui.label for="company_id">PT</x-ui.label>
                    <x-ui.select
                        name="company_id"
                        :options="$companies->pluck('name', 'id')->all()"
                        :selected="$editing?->company_id"
                        placeholder="Pilih PT"
                    />
                </div>

                <div>
                    <x-ui.label for="name">Nama departemen</x-ui.label>
                    <x-ui.input name="name" :value="$editing?->name" placeholder="Finance & Accounting" />
                </div>

                <x-ui.checkbox name="is_active" label="Departemen aktif" :checked="$editing?->is_active ?? true" />

                <div class="flex flex-wrap gap-2">
                    <x-ui.button type="submit">{{ $editing ? 'Simpan perubahan' : 'Tambah departemen' }}</x-ui.button>
                    @if ($editing)
                        <x-ui.button variant="ghost" :href="route('departments.index', $queryParams)">Batal</x-ui.button>
                    @endif
                </div>
            </form>
        </x-ui.card>

        <x-ui.card :padded="false">
            <div class="flex items-center justify-between px-5 py-4 sm:px-6">
                <div>
                    <h2 class="text-[15px] font-semibold">Daftar departemen</h2>
                    <p class="mt-0.5 text-[12.5px] text-muted">{{ $departments->total() }} departemen {{ $hasFilter ? 'ditemukan' : 'terdaftar' }}</p>
                </div>
                <x-icon name="sitemap" class="size-5 text-subtle" />
            </div>

            <form method="GET" action="{{ route('departments.index') }}" class="border-t border-line bg-surface-2/30 px-5 py-3.5 sm:px-6">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="grid gap-2.5 sm:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_190px_135px_auto] xl:items-end">
                    <div>
                        <x-ui.label for="q" class="sr-only">Cari departemen</x-ui.label>
                        <x-ui.input name="q" type="search" icon="search" :value="$filters['q'] ?? null" placeholder="Cari nama departemen atau PT..." />
                    </div>
                    <div>
                        <x-ui.label for="company" class="sr-only">Filter PT</x-ui.label>
                        <x-ui.select
                            name="company"
                            :options="$companies->pluck('name', 'id')->all()"
                            :selected="$filters['company'] ?? null"
                            placeholder="Semua PT"
                            data-select2-clear
                        />
                    </div>
                    <div>
                        <x-ui.label for="status" class="sr-only">Status</x-ui.label>
                        <x-ui.select
                            name="status"
                            :options="['active' => 'Aktif', 'inactive' => 'Nonaktif']"
                            :selected="$filters['status'] ?? null"
                            placeholder="Semua status"
                            data-select2-clear
                        />
                    </div>
                    <div class="flex gap-2">
                        <x-ui.button type="submit" icon="search" class="flex-1 xl:flex-none">Cari</x-ui.button>
                        @if ($hasFilter)
                            <x-ui.button variant="ghost" :href="route('departments.index', $perPage !== 10 ? ['per_page' => $perPage] : [])">Reset</x-ui.button>
                        @endif
                    </div>
                </div>
            </form>

            <div class="overflow-x-auto border-t border-line">
                <table class="w-full min-w-[700px] text-left">
                    <thead class="bg-surface-2/60">
                        <tr class="text-[10.5px] uppercase tracking-[0.08em] text-subtle">
                            <th class="px-5 py-3 font-semibold">Departemen</th>
                            <th class="px-3 py-3 font-semibold">PT</th>
                            <th class="px-2 py-3 text-center font-semibold">Jabatan</th>
                            <th class="px-2 py-3 text-center font-semibold">Karyawan</th>
                            <th class="px-3 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($departments as $department)
                            <tr class="border-t border-line/80 transition hover:bg-surface-2/40">
                                <td class="whitespace-nowrap px-5 py-3.5 text-[13.5px] font-medium">{{ $department->name }}</td>
                                <td class="whitespace-nowrap px-3 py-3.5 text-[12.5px] text-muted">
                                    <span class="rounded-md bg-surface-2 px-1.5 py-0.5 text-[11px] font-semibold text-muted">{{ $department->company->code }}</span>
                                    {{ $department->company->name }}
                                </td>
                                <td class="px-2 py-3.5 text-center text-[12.5px] tabular-nums text-muted">{{ $department->positions_count }}</td>
                                <td class="px-2 py-3.5 text-center text-[12.5px] tabular-nums text-muted">{{ $department->employees_count }}</td>
                                <td class="px-3 py-3.5">
                                    <x-ui.badge :variant="$department->is_active ? 'success' : 'neutral'">{{ $department->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui.badge>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-1">
                                        <x-ui.button variant="ghost" size="icon" icon="pencil" :href="route('departments.index', [...$queryParams, 'edit' => $department->id])" aria-label="Edit {{ $department->name }}" />
                                        <form method="POST" action="{{ route('departments.destroy', $department) }}" data-confirm="Departemen {{ $department->name }} akan dihapus permanen. Tindakan ini tidak bisa dibatalkan." data-confirm-title="Hapus departemen?" data-confirm-tone="danger" data-confirm-label="Ya, hapus">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button variant="ghost" size="icon" icon="trash" type="submit" aria-label="Hapus {{ $department->name }}" />
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-ui.empty-state
                                        icon="sitemap"
                                        :title="$hasFilter ? 'Departemen tidak ditemukan' : 'Belum ada departemen'"
                                        :description="$hasFilter ? 'Coba ubah kata kunci pencarian atau reset filter.' : 'Tambahkan departemen melalui formulir di samping.'"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $departments->links('vendor.pagination.hr-per-page', ['perPage' => $perPage, 'action' => route('departments.index')]) }}
        </x-ui.card>
    </div>
</x-layouts.app>
