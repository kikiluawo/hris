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
                        <x-ui.button variant="ghost" :href="route('companies.index')">Batal</x-ui.button>
                    @endif
                </div>
            </form>
        </x-ui.card>

        <x-ui.card :padded="false">
            <div class="flex items-center justify-between px-5 py-4 sm:px-6">
                <div>
                    <h2 class="text-[15px] font-semibold">Daftar PT</h2>
                    <p class="mt-0.5 text-[12.5px] text-muted">{{ $companies->count() }} perusahaan terdaftar</p>
                </div>
                <x-icon name="building" class="size-5 text-subtle" />
            </div>

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
                                        <x-ui.button variant="ghost" size="icon" icon="pencil" :href="route('companies.index', ['edit' => $company->id])" aria-label="Edit {{ $company->name }}" />
                                        <form method="POST" action="{{ route('companies.destroy', $company) }}" onsubmit="return confirm('Hapus PT {{ $company->name }}?')">
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
                                    <x-ui.empty-state icon="building" title="Belum ada PT" description="Tambahkan PT pertama melalui formulir di samping." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
