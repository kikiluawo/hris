<x-layouts.app title="Cabang">
    <x-ui.page-header
        title="Cabang"
        description="Cabang bersifat opsional dan selalu terikat pada satu PT. PT tanpa cabang tidak perlu dicatat di sini."
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
    <div class="space-y-4">
        <x-ui.card :title="$editing ? 'Edit cabang' : 'Tambah cabang'" description="Nama cabang harus unik dalam satu PT.">
            <form
                method="POST"
                action="{{ $editing ? route('branches.update', $editing) : route('branches.store') }}"
                class="space-y-4"
            >
                @csrf
                @if ($editing)
                    @method('PATCH')
                @endif

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
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
                        <x-ui.label for="name">Nama cabang</x-ui.label>
                        <x-ui.input name="name" :value="$editing?->name" placeholder="Cabang Bandung" />
                    </div>

                    <div>
                        <x-ui.label for="code">Kode cabang</x-ui.label>
                        <x-ui.input name="code" :value="$editing?->code" placeholder="MAW004-CHERYBTM" />
                    </div>

                    <div>
                        <x-ui.label for="city">Kota</x-ui.label>
                        <x-ui.input name="city" :value="$editing?->city" placeholder="Bandung" />
                    </div>

                    <div>
                        <x-ui.label for="brand">Brand</x-ui.label>
                        <x-ui.input name="brand" :value="$editing?->brand" placeholder="Chery" />
                    </div>

                    <div>
                        <x-ui.label for="type">Tipe</x-ui.label>
                        <x-ui.input name="type" :value="$editing?->type" placeholder="3S" />
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line pt-4">
                    <x-ui.checkbox name="is_active" label="Cabang aktif" :checked="$editing?->is_active ?? true" />

                    <div class="flex flex-wrap gap-2">
                        <x-ui.button type="submit">{{ $editing ? 'Simpan perubahan' : 'Tambah cabang' }}</x-ui.button>
                        @if ($editing)
                            <x-ui.button variant="ghost" :href="route('branches.index', $queryParams)">Batal</x-ui.button>
                        @endif
                    </div>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card :padded="false">
            <div class="flex items-center justify-between px-5 py-4 sm:px-6">
                <div>
                    <h2 class="text-[15px] font-semibold">Daftar cabang</h2>
                    <p class="mt-0.5 text-[12.5px] text-muted">{{ $branches->total() }} cabang {{ $hasFilter ? 'ditemukan' : 'terdaftar' }}</p>
                </div>
                <x-icon name="map-pin" class="size-5 text-subtle" />
            </div>

            <form method="GET" action="{{ route('branches.index') }}" class="border-t border-line bg-surface-2/30 px-5 py-3.5 sm:px-6">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_220px_150px_auto] lg:items-end">
                    <div>
                        <x-ui.label for="q" class="sr-only">Cari cabang</x-ui.label>
                        <x-ui.input name="q" type="search" icon="search" :value="$filters['q'] ?? null" placeholder="Cari nama, kode, kota, brand, tipe, atau PT..." />
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
                        <x-ui.button type="submit" icon="search" class="flex-1 lg:flex-none">Cari</x-ui.button>
                        @if ($hasFilter)
                            <x-ui.button variant="ghost" :href="route('branches.index', $perPage !== 10 ? ['per_page' => $perPage] : [])">Reset</x-ui.button>
                        @endif
                    </div>
                </div>
            </form>

            <div class="overflow-x-auto border-t border-line">
                <table class="w-full min-w-[820px] text-left">
                    <thead class="bg-surface-2/60">
                        <tr class="text-[10.5px] uppercase tracking-[0.08em] text-subtle">
                            <th class="px-5 py-3 font-semibold">Cabang</th>
                            <th class="px-3 py-3 font-semibold">PT</th>
                            <th class="px-2 py-3 font-semibold">Brand / Tipe</th>
                            <th class="px-2 py-3 font-semibold">Kota</th>
                            <th class="px-2 py-3 text-center font-semibold">Karyawan</th>
                            <th class="px-3 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($branches as $branch)
                            <tr class="border-t border-line/80 transition hover:bg-surface-2/40">
                                <td class="px-5 py-3.5 text-[13.5px] font-medium">
                                    <p class="whitespace-nowrap">{{ $branch->name }}</p>
                                    @if ($branch->code)
                                        <p class="mt-0.5 text-[11px] font-normal text-subtle">{{ $branch->code }}</p>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-3.5 text-[12.5px] text-muted">
                                    <span class="rounded-md bg-surface-2 px-1.5 py-0.5 text-[11px] font-semibold text-muted">{{ $branch->company->code }}</span>
                                    {{ $branch->company->name }}
                                </td>
                                <td class="whitespace-nowrap px-2 py-3.5 text-[12.5px] text-muted">
                                    {{ $branch->brand ?: '—' }}
                                    @if ($branch->type)
                                        <p class="text-[11px] text-subtle">{{ $branch->type }}</p>
                                    @endif
                                </td>
                                <td class="px-2 py-3.5 text-[12.5px] text-muted">{{ $branch->city ?: '—' }}</td>
                                <td class="px-2 py-3.5 text-center text-[12.5px] tabular-nums text-muted">{{ $branch->employees_count }}</td>
                                <td class="px-3 py-3.5">
                                    <x-ui.badge :variant="$branch->is_active ? 'success' : 'neutral'">{{ $branch->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui.badge>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-1">
                                        <x-ui.button variant="ghost" size="icon" icon="pencil" :href="route('branches.index', [...$queryParams, 'edit' => $branch->id])" aria-label="Edit {{ $branch->name }}" />
                                        <form method="POST" action="{{ route('branches.destroy', $branch) }}" data-confirm="Cabang {{ $branch->name }} akan dihapus permanen. Tindakan ini tidak bisa dibatalkan." data-confirm-title="Hapus cabang?" data-confirm-tone="danger" data-confirm-label="Ya, hapus">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button variant="ghost" size="icon" icon="trash" type="submit" aria-label="Hapus {{ $branch->name }}" />
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-ui.empty-state
                                        icon="map-pin"
                                        :title="$hasFilter ? 'Cabang tidak ditemukan' : 'Belum ada cabang'"
                                        :description="$hasFilter ? 'Coba ubah kata kunci pencarian atau reset filter.' : 'PT tanpa cabang tidak perlu dicatat di sini.'"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $branches->links('vendor.pagination.hr-per-page', ['perPage' => $perPage, 'action' => route('branches.index')]) }}
        </x-ui.card>
    </div>
</x-layouts.app>
