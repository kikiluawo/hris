@php
    $departmentOptions = $departments->mapWithKeys(
        fn ($department) => [$department->id => $department->company->code . ' · ' . $department->name]
    );
@endphp

<x-layouts.app title="Jabatan">
    <x-ui.page-header
        title="Jabatan"
        description="Jabatan menempel pada departemen, sehingga daftar jabatan otomatis mengikuti struktur PT dan departemennya."
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
        <x-ui.card :title="$editing ? 'Edit jabatan' : 'Tambah jabatan'" description="Nama jabatan harus unik dalam satu departemen.">
            <form
                method="POST"
                action="{{ $editing ? route('positions.update', $editing) : route('positions.store') }}"
                class="space-y-4"
            >
                @csrf
                @if ($editing)
                    @method('PATCH')
                @endif

                <div>
                    <x-ui.label for="department_id">Departemen</x-ui.label>
                    <x-ui.select
                        name="department_id"
                        :options="$departmentOptions->all()"
                        :selected="$editing?->department_id"
                        placeholder="Pilih departemen"
                    />
                </div>

                <div>
                    <x-ui.label for="name">Nama jabatan</x-ui.label>
                    <x-ui.input name="name" :value="$editing?->name" placeholder="Software Engineer" />
                </div>

                <x-ui.checkbox name="is_active" label="Jabatan aktif" :checked="$editing?->is_active ?? true" />

                <div class="flex flex-wrap gap-2">
                    <x-ui.button type="submit">{{ $editing ? 'Simpan perubahan' : 'Tambah jabatan' }}</x-ui.button>
                    @if ($editing)
                        <x-ui.button variant="ghost" :href="route('positions.index')">Batal</x-ui.button>
                    @endif
                </div>
            </form>
        </x-ui.card>

        <x-ui.card :padded="false">
            <div class="flex items-center justify-between px-5 py-4 sm:px-6">
                <div>
                    <h2 class="text-[15px] font-semibold">Daftar jabatan</h2>
                    <p class="mt-0.5 text-[12.5px] text-muted">{{ $positions->count() }} jabatan terdaftar</p>
                </div>
                <x-icon name="briefcase" class="size-5 text-subtle" />
            </div>

            <div class="overflow-x-auto border-t border-line">
                <table class="w-full min-w-[640px] text-left">
                    <thead class="bg-surface-2/60">
                        <tr class="text-[10.5px] uppercase tracking-[0.08em] text-subtle">
                            <th class="px-5 py-3 font-semibold sm:px-6">Jabatan</th>
                            <th class="px-3 py-3 font-semibold">PT · Departemen</th>
                            <th class="px-3 py-3 text-center font-semibold">Karyawan</th>
                            <th class="px-3 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($positions as $position)
                            <tr class="border-t border-line/80 transition hover:bg-surface-2/40">
                                <td class="whitespace-nowrap px-5 py-3.5 text-[13.5px] font-medium sm:px-6">{{ $position->name }}</td>
                                <td class="whitespace-nowrap px-3 py-3.5 text-[12.5px] text-muted">
                                    <span class="rounded-md bg-surface-2 px-1.5 py-0.5 text-[11px] font-semibold text-muted">{{ $position->department->company->code }}</span>
                                    {{ $position->department->name }}
                                </td>
                                <td class="px-3 py-3.5 text-center text-[12.5px] tabular-nums text-muted">{{ $position->employees_count }}</td>
                                <td class="px-3 py-3.5">
                                    <x-ui.badge :variant="$position->is_active ? 'success' : 'neutral'">{{ $position->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui.badge>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-1">
                                        <x-ui.button variant="ghost" size="icon" icon="pencil" :href="route('positions.index', ['edit' => $position->id])" aria-label="Edit {{ $position->name }}" />
                                        <form method="POST" action="{{ route('positions.destroy', $position) }}" data-confirm="Jabatan {{ $position->name }} akan dihapus permanen. Tindakan ini tidak bisa dibatalkan." data-confirm-title="Hapus jabatan?" data-confirm-tone="danger" data-confirm-label="Ya, hapus">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button variant="ghost" size="icon" icon="trash" type="submit" aria-label="Hapus {{ $position->name }}" />
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-ui.empty-state icon="briefcase" title="Belum ada jabatan" description="Tambahkan jabatan melalui formulir di samping." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
