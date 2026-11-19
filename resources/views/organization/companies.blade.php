<x-layouts.app title="Perusahaan">
    <x-ui.page-header
        title="Perusahaan"
        description="Kelola entitas PT tempat karyawan bernaung. Cabang dapat ditambahkan bila PT memilikinya."
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

    <div class="grid gap-4 lg:grid-cols-[330px_minmax(0,1fr)] lg:items-start">
        @php
            $hasFilter = (bool) array_filter($filters);
            $queryParams = array_filter([
                ...$filters,
                'per_page' => $perPage !== 10 ? $perPage : null,
                'page' => request()->integer('page') > 1 ? request()->integer('page') : null,
            ]);
        @endphp
        <x-ui.card :title="$editing ? 'Edit PT' : 'Tambah PT'" description="Kode PT dipakai sebagai singkatan unik.">
            <form
                method="POST"
                action="{{ $editing ? route('companies.update', $editing) : route('companies.store') }}"
                class="space-y-4"
            >
                @csrf
                @if ($editing)
                    @method('PATCH')
                @endif

                <div>
                    <x-ui.label for="code">Kode PT</x-ui.label>
                    <x-ui.input name="code" :value="$editing?->code" maxlength="10" placeholder="Contoh: NUD" />
                </div>

                <div>
                    <x-ui.label for="name">Nama PT</x-ui.label>
                    <x-ui.input name="name" :value="$editing?->name" placeholder="PT Nusantara Digital" />
                </div>

                <div>
                    <x-ui.label for="address">Alamat</x-ui.label>
                    <x-ui.input name="address" :value="$editing?->address" placeholder="Alamat kantor pusat" />
                </div>

                <div>
                    <x-ui.label for="phone">Telepon</x-ui.label>
                    <x-ui.input name="phone" :value="$editing?->phone" placeholder="021-..." />
                </div>

                <x-ui.checkbox name="is_active" label="PT aktif" :checked="$editing?->is_active ?? true" />

                <div class="flex flex-wrap gap-2">
                    <x-ui.button type="submit">{{ $editing ? 'Simpan perubahan' : 'Tambah PT' }}</x-ui.button>
                    @if ($editing)
                        <x-ui.button variant="ghost" :href="route('companies.index', $queryParams)">Batal</x-ui.button>
                    @endif
                </div>
            </form>
        </x-ui.card>

        <x-ui.card :padded="false">
            <div class="flex items-center justify-between px-5 py-4 sm:px-6">
                <div>
                    <h2 class="text-[15px] font-semibold">Daftar PT</h2>
                    <p class="mt-0.5 text-[12.5px] text-muted">{{ $companies->total() }} perusahaan {{ $hasFilter ? 'ditemukan' : 'terdaftar' }}</p>
                </div>
                <x-icon name="building" class="size-5 text-subtle" />
            </div>

            <form method="GET" action="{{ route('companies.index') }}" class="border-t border-line bg-surface-2/30 px-5 py-3.5 sm:px-6">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="grid gap-2.5 sm:grid-cols-[minmax(0,1fr)_150px_auto] sm:items-end">
                    <div>
                        <x-ui.label for="q" class="sr-only">Cari PT</x-ui.label>
                        <x-ui.input name="q" type="search" icon="search" :value="$filters['q'] ?? null" placeholder="Cari nama, kode, alamat, atau telepon PT..." />
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
                        <x-ui.button type="submit" icon="search" class="flex-1 sm:flex-none">Cari</x-ui.button>
                        @if ($hasFilter)
                            <x-ui.button variant="ghost" :href="route('companies.index', $perPage !== 10 ? ['per_page' => $perPage] : [])">Reset</x-ui.button>
                        @endif
                    </div>
                </div>
            </form>

            <div class="overflow-x-auto border-t border-line">
                <table class="w-full min-w-[640px] text-left">
                    <thead class="bg-surface-2/60">
                        <tr class="text-[10.5px] uppercase tracking-[0.08em] text-subtle">
                            <th class="px-5 py-3 font-semibold sm:px-6">PT</th>
                            <th class="px-3 py-3 text-center font-semibold">Cabang</th>
                            <th class="px-3 py-3 text-center font-semibold">Karyawan</th>
                            <th class="px-3 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($companies as $company)
                            <tr class="border-t border-line/80 transition hover:bg-surface-2/40">
                                <td class="px-5 py-3.5 sm:px-6">
                                    <div class="flex items-center gap-3">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-brand-soft text-[11px] font-bold text-brand-soft-ink">{{ $company->code }}</span>
                                        <div class="min-w-0">
                                            <p class="truncate text-[13.5px] font-medium">{{ $company->name }}</p>
                                            <p class="max-w-[240px] truncate text-[11.5px] text-subtle" title="{{ $company->address }}">{{ $company->address ?: 'Alamat belum diisi' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3.5 text-center text-[12.5px] tabular-nums text-muted">{{ $company->branches_count }}</td>
                                <td class="px-3 py-3.5 text-center text-[12.5px] tabular-nums text-muted">{{ $company->employees_count }}</td>
                                <td class="px-3 py-3.5">
                                    <x-ui.badge :variant="$company->is_active ? 'success' : 'neutral'">{{ $company->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui.badge>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-1">
                                        <x-ui.button variant="ghost" size="icon" icon="pencil" :href="route('companies.index', [...$queryParams, 'edit' => $company->id])" aria-label="Edit {{ $company->name }}" />
                                        <form method="POST" action="{{ route('companies.destroy', $company) }}" data-confirm="PT {{ $company->name }} akan dihapus permanen. Tindakan ini tidak bisa dibatalkan." data-confirm-title="Hapus PT?" data-confirm-tone="danger" data-confirm-label="Ya, hapus">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button variant="ghost" size="icon" icon="trash" type="submit" aria-label="Hapus {{ $company->name }}" />
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-ui.empty-state
                                        icon="building"
                                        :title="$hasFilter ? 'PT tidak ditemukan' : 'Belum ada PT'"
                                        :description="$hasFilter ? 'Coba ubah kata kunci pencarian atau reset filter.' : 'Tambahkan PT pertama melalui formulir di samping.'"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $companies->links('vendor.pagination.hr-per-page', ['perPage' => $perPage, 'action' => route('companies.index')]) }}
        </x-ui.card>
    </div>
</x-layouts.app>
