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
                            <x-ui.button variant="ghost" :href="route('branches.index')">Batal</x-ui.button>
                        @endif
                    </div>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card :padded="false">
            <div class="flex items-center justify-between px-5 py-4 sm:px-6">
                <div>
                    <h2 class="text-[15px] font-semibold">Daftar cabang</h2>
                    <p class="mt-0.5 text-[12.5px] text-muted">{{ $branches->count() }} cabang terdaftar</p>
                </div>
                <x-icon name="map-pin" class="size-5 text-subtle" />
            </div>

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
                                        <x-ui.button variant="ghost" size="icon" icon="pencil" :href="route('branches.index', ['edit' => $branch->id])" aria-label="Edit {{ $branch->name }}" />
                                        <form method="POST" action="{{ route('branches.destroy', $branch) }}" onsubmit="return confirm('Hapus cabang {{ $branch->name }}?')">
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
                                    <x-ui.empty-state icon="map-pin" title="Belum ada cabang" description="PT tanpa cabang tidak perlu dicatat di sini." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
