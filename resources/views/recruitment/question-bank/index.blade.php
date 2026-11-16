<x-layouts.app title="Bank soal">
    <x-ui.page-header
        title="Bank soal"
        description="Kumpulkan soal sekali, pakai berulang kali. Tambah soal psikotes di sini, lalu muat lewat tombol Muat dari bank saat menyusun tes."
    >
        <x-slot:actions>
            <form method="POST" action="{{ route('question-bank.sync-standard') }}" class="inline">
                @csrf
                <x-ui.button type="submit" variant="secondary" icon="refresh" title="Muat 94 soal baku standar psikotes (24 DISC, 30 Kognitif IQ & 40 Ketelitian)">
                    Muat 94 Soal Standar
                </x-ui.button>
            </form>
            <x-ui.button :href="route('question-bank.create')" icon="plus">Tambah soal</x-ui.button>
            <x-ui.button :href="route('assessments.index')" variant="secondary" icon="chevron-left">Tes online</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if (session('status'))
        <x-ui.alert variant="success" class="mb-4">{{ session('status') }}</x-ui.alert>
    @endif

    @if (session('error'))
        <x-ui.alert variant="danger" class="mb-4">{{ session('error') }}</x-ui.alert>
    @endif

    <div class="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat-card label="Total bank soal" :value="$stats['total']" icon="copy" />
        <x-ui.stat-card label="Pilihan ganda / IQ" :value="$stats['multiple_choice']" icon="check" />
        <x-ui.stat-card label="Kepribadian DISC" :value="$stats['disc']" icon="user" />
        <x-ui.stat-card label="Dimensi kompetensi" :value="$stats['dimensions']" icon="grid" />
    </div>

    <x-ui.card class="mb-4">
        <form method="GET" action="{{ route('question-bank.index') }}" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_220px_240px_auto] xl:items-end">
            <div>
                <x-ui.label for="q">Cari soal</x-ui.label>
                <x-ui.input type="search" name="q" :value="$filters['q']" icon="search" placeholder="Teks soal atau dimensi" />
            </div>

            <div>
                <x-ui.label for="type">Jenis</x-ui.label>
                <x-ui.select
                    name="type"
                    :options="\App\Models\AssessmentQuestion::typeOptions()"
                    :selected="$filters['type']"
                    placeholder="Semua jenis"
                    data-select2-clear
                />
            </div>

            <div>
                <x-ui.label for="dimension">Dimensi</x-ui.label>
                <x-ui.select
                    name="dimension"
                    :options="collect($dimensions)->mapWithKeys(fn ($dimension) => [$dimension => $dimension])->put('*', 'Tanpa dimensi')->all()"
                    :selected="$filters['dimension']"
                    placeholder="Semua dimensi"
                    data-select2-clear
                />
            </div>

            <div class="flex gap-2">
                <x-ui.button type="submit" icon="search">Terapkan</x-ui.button>
                @if ($filters['q'] !== '' || $filters['type'] !== '' || $filters['dimension'] !== '')
                    <x-ui.button :href="route('question-bank.index')" variant="ghost">Bersihkan</x-ui.button>
                @endif
            </div>
        </form>
    </x-ui.card>

    <x-ui.card :padded="false">
        <div class="flex items-center justify-between px-5 py-4 sm:px-6">
            <div>
                <h2 class="text-[15px] font-semibold">Daftar soal</h2>
                <p class="mt-0.5 text-[12.5px] text-muted">{{ $items->total() }} soal cocok dengan filter ini</p>
            </div>
            <x-icon name="copy" class="size-5 text-subtle" />
        </div>

        @if ($items->isEmpty())
            <div class="border-t border-line">
                <x-ui.empty-state
                    icon="copy"
                    title="Bank soal masih kosong"
                    description="Tambahkan soal psikotes sekali di sini. Setelah itu, setiap kali menyusun tes cukup klik Muat dari bank tanpa menulis ulang pertanyaannya."
                >
                    <x-slot:actions>
                        <x-ui.button :href="route('question-bank.create')" icon="plus">Tambah soal pertama</x-ui.button>
                    </x-slot:actions>
                </x-ui.empty-state>
            </div>
        @else
            <div class="overflow-x-auto border-t border-line">
                <table class="w-full min-w-[900px] text-left">
                    <thead class="bg-surface-2/60">
                        <tr class="text-[10.5px] uppercase tracking-[0.08em] text-subtle">
                            <th class="px-5 py-3 font-semibold">Soal</th>
                            <th class="px-3 py-3 font-semibold">Jenis</th>
                            <th class="px-3 py-3 font-semibold">Pilihan</th>
                            <th class="px-3 py-3 font-semibold">Poin</th>
                            <th class="px-3 py-3 font-semibold">Ditambahkan</th>
                            <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr class="border-t border-line/80 transition hover:bg-surface-2/40">
                                <td class="px-5 py-3.5">
                                    <p class="max-w-[420px] text-[13.5px] font-medium leading-relaxed">{{ $item->question }}</p>
                                    @if (filled($item->dimension))
                                        <p class="mt-1 text-[11.5px] text-subtle">Dimensi: <span class="font-medium text-ink">{{ $item->dimensionLabel() }}</span></p>
                                    @endif
                                    @if ($item->usesImages())
                                        <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                            @if ($item->hasImage())
                                                <img src="{{ App\Support\AssessmentImageStore::url($item->image_path) }}" alt="Gambar soal" class="size-12 rounded-lg border border-line bg-surface object-contain">
                                            @endif
                                            @foreach ($item->options ?? [] as $optionIndex => $option)
                                                @if ($optionImage = $item->optionImage($optionIndex))
                                                    <img src="{{ App\Support\AssessmentImageStore::url($optionImage) }}" alt="Gambar pilihan {{ chr(65 + $optionIndex) }}" class="size-9 rounded-lg border border-line bg-surface object-contain">
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5">
                                    <x-ui.badge :variant="$item->type === \App\Models\AssessmentQuestion::TYPE_DISC ? 'purple' : ($item->isScale() ? 'warning' : ($item->type === \App\Models\AssessmentQuestion::TYPE_ESSAY ? 'info' : 'brand'))">
                                        {{ $item->typeLabel() }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-3 py-3.5">
                                    @if ($item->options)
                                        @php $optionLabels = collect($item->options)->map(fn ($option, $index) => filled($option) ? $option : 'gambar '.chr(65 + $index))->implode(' · '); @endphp
                                        <p class="max-w-[300px] truncate text-[12.5px] text-muted">{{ $optionLabels }}</p>
                                        @if ($item->correctAnswer())
                                            <p class="mt-1 text-[11.5px] font-semibold text-brand-soft-ink">Benar: {{ $item->correctAnswer() }}</p>
                                        @endif
                                    @else
                                        <p class="text-[12.5px] text-subtle">Jawaban bebas</p>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5 text-[13px] tabular-nums text-muted">{{ $item->isScale() || $item->type === \App\Models\AssessmentQuestion::TYPE_DISC ? '—' : $item->points }}</td>
                                <td class="px-3 py-3.5 text-[12px] text-muted">
                                    <p>{{ $item->created_at->translatedFormat('d M Y') }}</p>
                                    <p class="text-[11px] text-subtle">{{ $item->creator?->name ?? 'Sistem' }}</p>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <x-ui.button size="sm" variant="secondary" :href="route('question-bank.edit', $item)" icon="pencil">Ubah</x-ui.button>
                                        <form method="POST" action="{{ route('question-bank.destroy', $item) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="flex size-8 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-rose-600 transition hover:bg-rose-100 dark:border-rose-400/25 dark:bg-rose-400/10 dark:text-rose-300"
                                                data-confirm="Hapus soal ini dari bank soal? Tes yang sudah memakai soal ini tidak berubah."
                                                data-confirm-title="Hapus soal"
                                                data-confirm-label="Ya, hapus"
                                                data-confirm-tone="danger"
                                                title="Hapus soal"
                                            >
                                                <x-icon name="trash" class="size-4" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-line px-5 py-4">
                {{ $items->links() }}
            </div>
        @endif
    </x-ui.card>
</x-layouts.app>
