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
                        <x-ui.button variant="ghost" :href="route('departments.index')">Batal</x-ui.button>
                    @endif
                </div>
            </form>
        </x-ui.card>

        <x-ui.card :padded="false">
            <div class="flex items-center justify-between px-5 py-4 sm:px-6">
                <div>
                    <h2 class="text-[15px] font-semibold">Daftar departemen</h2>
                    <p class="mt-0.5 text-[12.5px] text-muted">{{ $departments->count() }} departemen terdaftar</p>
                </div>
                <x-icon name="sitemap" class="size-5 text-subtle" />
            </div>

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
                                        <x-ui.button variant="ghost" size="icon" icon="pencil" :href="route('departments.index', ['edit' => $department->id])" aria-label="Edit {{ $department->name }}" />
                                        <form method="POST" action="{{ route('departments.destroy', $department) }}" onsubmit="return confirm('Hapus departemen {{ $department->name }}?')">
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
                                    <x-ui.empty-state icon="sitemap" title="Belum ada departemen" description="Tambahkan departemen melalui formulir di samping." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
