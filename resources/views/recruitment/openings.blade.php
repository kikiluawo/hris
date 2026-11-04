<x-layouts.app title="Lowongan">
    <x-ui.page-header
        title="Lowongan"
        description="Buat lowongan, lalu bagikan link publiknya ke Instagram, WhatsApp, atau media sosial lain. Pelamar mengisi formulir dan mengunggah CV melalui link tersebut."
    >
        <x-slot:actions>
            <x-ui.button :href="route('applicants.index')" variant="secondary" icon="users">Lihat pelamar</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

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
        <x-ui.card
            :title="$editing ? 'Edit lowongan' : 'Buat lowongan'"
            description="Link publik dibuat otomatis dari judul dan tetap sama walaupun judul diubah, agar tautan yang sudah dibagikan tidak rusak."
        >
            <form
                method="POST"
                action="{{ $editing ? route('openings.update', $editing) : route('openings.store') }}"
                class="space-y-4"
            >
                @csrf
                @if ($editing)
                    @method('PATCH')
                @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <x-ui.label for="title">Judul lowongan</x-ui.label>
                        <x-ui.input name="title" :value="$editing?->title" placeholder="Accounting Staff" />
                        <x-ui.field-error name="title" />
                    </div>

                    <div>
                        <x-ui.label for="company_id">PT</x-ui.label>
                        <x-ui.select
                            name="company_id"
                            :options="$companies->all()"
                            :selected="$editing?->company_id"
                            placeholder="Tanpa PT tertentu"
                            data-select2-clear
                        />
                        <x-ui.field-error name="company_id" />
                    </div>

                    <div>
                        <x-ui.label for="department_id">Departemen</x-ui.label>
                        <x-ui.select
                            name="department_id"
                            :options="$departments->all()"
                            :selected="$editing?->department_id"
                            placeholder="Pilih departemen"
                            data-select2-clear
                        />
                        <x-ui.field-error name="department_id" />
                    </div>

                    <div>
                        <x-ui.label for="position_id">Jabatan</x-ui.label>
                        <x-ui.select
                            name="position_id"
                            :options="$positions->all()"
                            :selected="$editing?->position_id"
                            placeholder="Pilih jabatan"
                            data-select2-clear
                        />
                        <x-ui.field-error name="position_id" />
                    </div>

                    <div class="sm:col-span-2">
                        <x-ui.label for="description">Deskripsi & kualifikasi</x-ui.label>
                        <x-ui.textarea name="description" :value="$editing?->description" :rows="7" placeholder="Tuliskan tanggung jawab, kualifikasi, dan informasi lain yang perlu diketahui pelamar." />
                        <x-ui.field-error name="description" />
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line pt-4">
                    <x-ui.checkbox name="is_open" label="Lowongan dibuka untuk pelamar" :checked="$editing?->is_open ?? true" />

                    <div class="flex flex-wrap gap-2">
                        <x-ui.button type="submit">{{ $editing ? 'Simpan perubahan' : 'Buat lowongan' }}</x-ui.button>
                        @if ($editing)
                            <x-ui.button variant="ghost" :href="route('openings.index')">Batal</x-ui.button>
                        @endif
                    </div>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card :padded="false">
            <div class="flex items-center justify-between px-5 py-4 sm:px-6">
                <div>
                    <h2 class="text-[15px] font-semibold">Daftar lowongan</h2>
                    <p class="mt-0.5 text-[12.5px] text-muted">{{ $openings->count() }} lowongan · {{ $openings->where('is_open', true)->count() }} dibuka</p>
                </div>
                <x-icon name="file-text" class="size-5 text-subtle" />
            </div>

            <div class="overflow-x-auto border-t border-line">
                <table class="w-full min-w-[1100px] text-left">
                    <thead class="bg-surface-2/60">
                        <tr class="text-[10.5px] uppercase tracking-[0.08em] text-subtle">
                            <th class="px-5 py-3 font-semibold">Lowongan</th>
                            <th class="px-3 py-3 font-semibold">PT</th>
                            <th class="px-2 py-3 font-semibold">Departemen / Jabatan</th>
                            <th class="px-2 py-3 font-semibold">Status</th>
                            <th class="px-2 py-3 text-center font-semibold">Pelamar</th>
                            <th class="px-2 py-3 font-semibold">Link publik</th>
                            <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($openings as $opening)
                            <tr class="border-t border-line/80 transition hover:bg-surface-2/40">
                                <td class="px-5 py-3.5">
                                    <p class="text-[13.5px] font-semibold">{{ $opening->title }}</p>
                                    <p class="mt-0.5 text-[11px] text-subtle">/karir/{{ $opening->slug }}</p>
                                </td>
                                <td class="px-3 py-3.5 text-[12.5px] text-muted">{{ $opening->company?->name ?? '—' }}</td>
                                <td class="px-2 py-3.5 text-[12.5px] text-muted">
                                    {{ $opening->department?->name ?? '—' }}
                                    <p class="text-[11px] text-subtle">{{ $opening->position?->name ?? '—' }}</p>
                                </td>
                                <td class="px-2 py-3.5">
                                    <x-ui.badge :variant="$opening->is_open ? 'success' : 'neutral'">{{ $opening->is_open ? 'Dibuka' : 'Ditutup' }}</x-ui.badge>
                                </td>
                                <td class="px-2 py-3.5 text-center text-[12.5px] tabular-nums text-muted">
                                    <a href="{{ route('applicants.index', ['job' => $opening->id]) }}" class="font-semibold text-brand hover:underline">{{ $opening->applications_count }}</a>
                                </td>
                                <td class="px-2 py-3.5">
                                    <div x-data="{ copied: false }" class="flex items-center gap-1">
                                        <x-ui.button
                                            variant="ghost"
                                            size="icon"
                                            x-on:click="navigator.clipboard.writeText('{{ route('career.show', $opening->slug) }}').then(() => { copied = true; setTimeout(() => copied = false, 1500); }).catch(() => {})"
                                            aria-label="Salin link {{ $opening->title }}"
                                            title="Salin link publik"
                                        >
                                            <x-icon name="link" class="size-4" x-show="!copied" />
                                            <x-icon name="check" class="size-4 text-brand" x-show="copied" x-cloak />
                                        </x-ui.button>
                                        <x-ui.button variant="ghost" size="icon" icon="external" :href="route('career.show', $opening->slug)" target="_blank" aria-label="Buka link publik {{ $opening->title }}" />
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <form method="POST" action="{{ route('openings.toggle', $opening) }}">
                                            @csrf
                                            @method('PATCH')
                                            <x-ui.button
                                                variant="secondary"
                                                size="sm"
                                                :icon="$opening->is_open ? 'eye-off' : 'eye'"
                                                type="submit"
                                            >
                                                {{ $opening->is_open ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </x-ui.button>
                                        </form>
                                        <x-ui.button variant="ghost" size="icon" icon="pencil" :href="route('openings.index', ['edit' => $opening->id])" aria-label="Edit {{ $opening->title }}" />
                                        <form method="POST" action="{{ route('openings.destroy', $opening) }}" onsubmit="return confirm('Hapus lowongan {{ $opening->title }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button variant="ghost" size="icon" icon="trash" type="submit" aria-label="Hapus {{ $opening->title }}" />
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-ui.empty-state icon="file-text" title="Belum ada lowongan" description="Buat lowongan pertama, lalu bagikan link publiknya." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
