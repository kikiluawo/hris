<x-layouts.app title="Tes online & Asesmen">
    <div x-data="{ view: 'grid', templateModal: false }">
        
        <x-ui.page-header
            title="Tes Online & Asesmen Digital"
            description="Kelola paket asesmen kerja, undang kandidat lewat tautan proctoring berkeamanan tinggi, dan dapatkan laporan psikometri (DISC, IQ & Ketelitian) secara otomatis."
        >
            <x-slot:actions>
                <x-ui.button :href="route('question-bank.index')" variant="secondary" icon="copy">Bank Soal (163)</x-ui.button>
                <x-ui.button type="button" variant="secondary" icon="sparkles" @click="templateModal = true">
                    Paket Standar HRD
                </x-ui.button>
                <x-ui.button :href="route('assessments.create')" icon="plus">Buat Tes Baru</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        @if (session('status'))
            <x-ui.alert variant="success" class="mb-4">{{ session('status') }}</x-ui.alert>
        @endif

        @if (session('error'))
            <x-ui.alert variant="danger" class="mb-4">{{ session('error') }}</x-ui.alert>
        @endif

        <div class="mb-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.stat-card label="Total Paket Tes" :value="$stats['assessments']" icon="clipboard" />
            <x-ui.stat-card label="Tes Aktif Dipublikasi" :value="$stats['published']" icon="check" />
            <x-ui.stat-card label="Undangan Terkirim" :value="$stats['invitations']" icon="send" />
            <x-ui.stat-card label="Hasil Selesai Ujian" :value="$stats['attempts']" icon="users" />
        </div>

        <div class="mb-5 flex flex-col gap-3 rounded-2xl border border-line bg-surface p-4 shadow-card sm:flex-row sm:items-center sm:justify-between">
            <form method="GET" action="{{ route('assessments.index') }}" class="flex flex-1 flex-wrap items-center gap-2.5">
                <div class="w-full sm:max-w-xs">
                    <x-ui.input type="search" name="q" :value="$filters['q']" icon="search" placeholder="Cari nama tes atau kata kunci…" />
                </div>

                <div class="w-full sm:w-48">
                    <x-ui.select
                        name="status"
                        :options="['published' => 'Aktif (Dipublikasi)', 'draft' => 'Draf', 'closed' => 'Ditutup']"
                        :selected="$filters['status']"
                        placeholder="Semua status"
                        data-select2-clear
                    />
                </div>

                <div class="flex items-center gap-1.5">
                    <x-ui.button type="submit" size="sm" icon="search">Cari</x-ui.button>
                    @if ($filters['q'] !== '' || $filters['status'] !== '')
                        <x-ui.button :href="route('assessments.index')" size="sm" variant="ghost">Reset</x-ui.button>
                    @endif
                </div>
            </form>

            <div class="flex items-center justify-between border-t border-line/60 pt-3 sm:border-0 sm:pt-0 gap-3">
                <span class="text-[12px] text-muted whitespace-nowrap">
                    <strong>{{ $assessments->total() }}</strong> tes ditemukan
                </span>

                <div class="flex items-center rounded-xl border border-line bg-surface-2/60 p-0.5">
                    <button
                        type="button"
                        @click="view = 'grid'"
                        :class="view === 'grid' ? 'bg-surface text-ink shadow-sm' : 'text-subtle hover:text-ink'"
                        class="flex size-8 items-center justify-center rounded-lg transition"
                        title="Tampilan Kartu (Grid)"
                        aria-label="Tampilan Kartu"
                    >
                        <x-icon name="grid" class="size-4" />
                    </button>
                    <button
                        type="button"
                        @click="view = 'table'"
                        :class="view === 'table' ? 'bg-surface text-ink shadow-sm' : 'text-subtle hover:text-ink'"
                        class="flex size-8 items-center justify-center rounded-lg transition"
                        title="Tampilan Tabel"
                        aria-label="Tampilan Tabel"
                    >
                        <x-icon name="menu" class="size-4" />
                    </button>
                </div>
            </div>
        </div>

        @if ($assessments->isEmpty())
            <x-ui.card :padded="false">
                <x-ui.empty-state
                    icon="clipboard"
                    title="Tidak ada tes yang cocok"
                    description="Coba ubah kata kunci pencarian, atau langsung gunakan salah satu templat paket tes standar HRD."
                >
                    <x-slot:actions>
                        <x-ui.button type="button" @click="templateModal = true" variant="secondary" icon="sparkles">Buka Templat Standar</x-ui.button>
                        <x-ui.button :href="route('assessments.create')" icon="plus">Buat tes kustom</x-ui.button>
                    </x-slot:actions>
                </x-ui.empty-state>
            </x-ui.card>
        @else
            
            <div x-show="view === 'grid'" x-cloak class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($assessments as $assessment)
                    <div class="group relative flex flex-col justify-between rounded-2xl border border-line bg-surface p-5 shadow-card transition-all duration-200 hover:-translate-y-0.5 hover:border-line-strong hover:shadow-pop">
                        
                        <div>
                            
                            <div class="flex items-center justify-between gap-2">
                                @if ($assessment->isPublished())
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                                        <span class="size-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Aktif
                                    </span>
                                @elseif ($assessment->status === 'draft')
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-0.5 text-[11px] font-semibold text-amber-700 dark:border-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                        <span class="size-1.5 rounded-full bg-amber-500"></span> Draf
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-line bg-surface-2 px-2.5 py-0.5 text-[11px] font-semibold text-subtle">
                                        Ditutup
                                    </span>
                                @endif

                                <span class="flex items-center gap-1 text-[12px] font-medium text-muted">
                                    <x-icon name="clock" class="size-3.5 text-subtle" />
                                    {{ $assessment->duration_minutes }} menit
                                </span>
                            </div>

                            <div class="mt-3.5">
                                <a href="{{ route('assessments.show', $assessment) }}" class="text-[15px] font-bold text-ink transition group-hover:text-brand">
                                    {{ $assessment->title }}
                                </a>
                                <p class="mt-1 line-clamp-2 text-[12.5px] leading-relaxed text-muted">
                                    {{ $assessment->description ?: 'Tanpa catatan atau deskripsi tambahan.' }}
                                </p>
                            </div>

                            <div class="mt-3.5 flex flex-wrap gap-1.5">
                                @foreach ($assessment->instrumentsSummary() as $inst)
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[10.5px] font-semibold
                                        {{ $inst['tone'] === 'purple' ? 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' : '' }}
                                        {{ $inst['tone'] === 'brand' ? 'bg-brand-soft text-brand-soft-ink' : '' }}
                                        {{ $inst['tone'] === 'emerald' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : '' }}
                                        {{ $inst['tone'] === 'amber' ? 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : '' }}
                                        {{ $inst['tone'] === 'neutral' ? 'bg-surface-2 text-muted border border-line' : '' }}
                                    ">
                                        <x-icon :name="$inst['icon']" class="size-3" />
                                        {{ $inst['label'] }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-5 border-t border-line/70 pt-3.5">
                            
                            <div class="mb-3.5">
                                @if ($assessment->invitations_count === 0)
                                    <p class="text-[11.5px] text-subtle">Belum ada kandidat diundang</p>
                                @else
                                    <div class="flex items-center justify-between text-[12px]">
                                        <span class="font-medium text-ink">{{ $assessment->invitations_count }} diundang</span>
                                        <span class="font-bold text-brand-soft-ink">{{ $assessment->attempts_count }} selesai</span>
                                    </div>
                                    @php $pct = min(100, round(($assessment->attempts_count / max(1, $assessment->invitations_count)) * 100)); @endphp
                                    <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-surface-2">
                                        <div class="h-full bg-brand transition-all duration-300" style="width: {{ $pct }}%"></div>
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                <x-ui.button :href="route('assessments.show', $assessment)" size="sm" class="flex-1" icon="eye">
                                    Kelola & Hasil
                                </x-ui.button>
                                @if (! $assessment->isLocked())
                                    <x-ui.button :href="route('assessments.edit', $assessment)" size="sm" variant="secondary" icon="pencil" title="Edit tes">
                                        Edit
                                    </x-ui.button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div x-show="view === 'table'" x-cloak>
                <x-ui.card :padded="false">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[880px] text-left">
                            <thead class="bg-surface-2/60">
                                <tr class="text-[10.5px] uppercase tracking-[0.08em] text-subtle">
                                    <th class="px-5 py-3.5 font-semibold">Nama Tes & Modul</th>
                                    <th class="px-3 py-3.5 font-semibold">Status</th>
                                    <th class="px-3 py-3.5 font-semibold">Soal & Durasi</th>
                                    <th class="px-3 py-3.5 font-semibold">Partisipasi Kandidat</th>
                                    <th class="px-5 py-3.5 text-right font-semibold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line/80">
                                @foreach ($assessments as $assessment)
                                    <tr class="transition hover:bg-surface-2/40">
                                        <td class="px-5 py-4">
                                            <div class="flex items-start gap-3">
                                                <div class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-xl border border-line bg-surface-2 text-ink">
                                                    @if ($assessment->hasDisc())
                                                        <x-icon name="user" class="size-4 text-purple-600 dark:text-purple-400" />
                                                    @elseif ($assessment->hasAptitude())
                                                        <x-icon name="sparkles" class="size-4 text-brand" />
                                                    @elseif ($assessment->hasAccuracy())
                                                        <x-icon name="check" class="size-4 text-emerald-600 dark:text-emerald-400" />
                                                    @else
                                                        <x-icon name="clipboard" class="size-4 text-muted" />
                                                    @endif
                                                </div>
                                                <div class="min-w-0">
                                                    <a href="{{ route('assessments.show', $assessment) }}" class="text-[14px] font-semibold text-ink hover:text-brand transition">
                                                        {{ $assessment->title }}
                                                    </a>
                                                    <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                                        @foreach ($assessment->instrumentsSummary() as $inst)
                                                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold
                                                                {{ $inst['tone'] === 'purple' ? 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' : '' }}
                                                                {{ $inst['tone'] === 'brand' ? 'bg-brand-soft text-brand-soft-ink' : '' }}
                                                                {{ $inst['tone'] === 'emerald' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : '' }}
                                                                {{ $inst['tone'] === 'amber' ? 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : '' }}
                                                                {{ $inst['tone'] === 'neutral' ? 'bg-surface-2 text-muted border border-line' : '' }}
                                                            ">
                                                                <x-icon :name="$inst['icon']" class="size-3" />
                                                                {{ $inst['label'] }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-3 py-4">
                                            @if ($assessment->isPublished())
                                                <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                                                    <span class="size-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Aktif
                                                </span>
                                            @elseif ($assessment->status === 'draft')
                                                <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-0.5 text-[11px] font-semibold text-amber-700 dark:border-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                                    <span class="size-1.5 rounded-full bg-amber-500"></span> Draf
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 rounded-full border border-line bg-surface-2 px-2.5 py-0.5 text-[11px] font-semibold text-subtle">
                                                    Ditutup
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-3 py-4 text-[12.5px] text-muted">
                                            <p class="font-medium text-ink">{{ $assessment->questions_count }} butir soal</p>
                                            <p class="text-[11.5px] text-subtle">{{ $assessment->duration_minutes }} mnt · Lulus {{ $assessment->passing_score }}%</p>
                                        </td>

                                        <td class="px-3 py-4 text-[12.5px] text-muted">
                                            @if ($assessment->invitations_count === 0)
                                                <span class="text-[11.5px] text-subtle">Belum ada undangan</span>
                                            @else
                                                <p class="font-medium text-ink">{{ $assessment->invitations_count }} diundang · <strong class="text-brand-soft-ink">{{ $assessment->attempts_count }} selesai</strong></p>
                                                <div class="mt-1 h-1.5 w-24 overflow-hidden rounded-full bg-surface-2">
                                                    @php $pct = min(100, round(($assessment->attempts_count / max(1, $assessment->invitations_count)) * 100)); @endphp
                                                    <div class="h-full bg-brand" style="width: {{ $pct }}%"></div>
                                                </div>
                                            @endif
                                        </td>

                                        <td class="px-5 py-4 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <x-ui.button size="sm" variant="secondary" :href="route('assessments.show', $assessment)" icon="eye">
                                                    Kelola
                                                </x-ui.button>
                                                @if (! $assessment->isLocked())
                                                    <x-ui.button size="sm" variant="ghost" :href="route('assessments.edit', $assessment)" icon="pencil" title="Edit tes">
                                                        Edit
                                                    </x-ui.button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>
            </div>

            @if ($assessments->hasPages())
                <div class="mt-4">
                    {{ $assessments->links() }}
                </div>
            @endif
        @endif

        <div
            x-show="templateModal"
            x-cloak
            @keydown.escape.window="templateModal = false"
            class="fixed inset-0 z-[80] flex items-center justify-center p-4 sm:p-6"
            role="dialog"
            aria-modal="true"
        >
            
            <div
                class="fixed inset-0 bg-ink/50 backdrop-blur-sm transition-opacity"
                @click="templateModal = false"
                x-show="templateModal"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
            ></div>

            <div
                class="relative w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-3xl border border-line bg-surface p-6 shadow-2xl transition-all sm:p-7"
                x-show="templateModal"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
            >
                <div class="flex items-start justify-between gap-4 border-b border-line pb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="flex size-7 items-center justify-center rounded-lg bg-brand/10 text-brand">
                                <x-icon name="sparkles" class="size-4" />
                            </span>
                            <h2 class="text-[17px] font-bold text-ink">Pilih Paket Tes Standar HRD</h2>
                        </div>
                        <p class="mt-1 text-[13px] text-muted">Aktifkan paket tes yang sudah dikalibrasi standar psikometri resmi hanya dalam 1 kali klik.</p>
                    </div>
                    <button
                        type="button"
                        @click="templateModal = false"
                        class="flex size-8 items-center justify-center rounded-lg border border-line bg-surface text-subtle hover:bg-surface-2 hover:text-ink transition"
                    >
                        <x-icon name="close" class="size-4" />
                    </button>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    
                    <div class="flex flex-col justify-between rounded-2xl border-2 border-brand/50 bg-brand-soft/20 p-5 transition hover:border-brand">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="rounded-full bg-brand px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-brand-ink">
                                    Direkomendasikan (3-in-1)
                                </span>
                                @if ($presets['comprehensive'])
                                    <span class="flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                                        <x-icon name="check" class="size-3.5" /> Sudah Tersedia
                                    </span>
                                @endif
                            </div>
                            <h3 class="mt-3 text-[16px] font-bold text-ink">Paket Komprehensif HRD (All-in-One)</h3>
                            <p class="mt-1.5 text-[12.5px] leading-relaxed text-muted">
                                Tes terpadu menggabungkan <strong>Kepribadian DISC</strong>, <strong>Bakat Kognitif IQ</strong>, dan <strong>Ketelitian Kerja</strong> sekaligus dalam satu link pengerjaan ujian.
                            </p>
                            <div class="mt-3 flex flex-wrap gap-1.5 text-[11px]">
                                <span class="rounded-md border border-line bg-surface px-2 py-0.5 font-medium text-muted">94 Butir Soal</span>
                                <span class="rounded-md border border-line bg-surface px-2 py-0.5 font-medium text-muted">45 Menit</span>
                                <span class="rounded-md border border-line bg-surface px-2 py-0.5 font-medium text-muted">3 Laporan Sekaligus</span>
                            </div>
                        </div>

                        <div class="mt-5 border-t border-line/60 pt-4">
                            @if ($presets['comprehensive'])
                                <div class="flex items-center gap-2">
                                    <x-ui.button :href="route('assessments.show', $presets['comprehensive'])" size="sm" class="flex-1" icon="eye">Buka Tes</x-ui.button>
                                    <form method="POST" action="{{ route('assessments.seed-comprehensive') }}" class="inline">
                                        @csrf
                                        <x-ui.button type="submit" size="sm" variant="secondary" icon="refresh" title="Perbarui / sinkronkan soal"></x-ui.button>
                                    </form>
                                </div>
                            @else
                                <form method="POST" action="{{ route('assessments.seed-comprehensive') }}">
                                    @csrf
                                    <x-ui.button type="submit" size="sm" class="w-full" icon="plus">Aktifkan Paket All-in-One</x-ui.button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-col justify-between rounded-2xl border border-line bg-surface p-5 transition hover:border-purple-300 dark:hover:border-purple-800">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="rounded-full bg-purple-50 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                                    Kepribadian & Adaptasi
                                </span>
                                @if ($presets['disc'])
                                    <span class="flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                                        <x-icon name="check" class="size-3.5" /> Sudah Tersedia
                                    </span>
                                @endif
                            </div>
                            <h3 class="mt-3 text-[16px] font-bold text-ink">Tes Kepribadian DISC</h3>
                            <p class="mt-1.5 text-[12.5px] leading-relaxed text-muted">
                                Format 4 pernyataan (Paling vs Kurang) memetakan profil perilaku alami, gaya komunikasi, serta 3 grafik adaptasi lingkungan kerja (Mask, Core, Mirror).
                            </p>
                            <div class="mt-3 flex flex-wrap gap-1.5 text-[11px]">
                                <span class="rounded-md border border-line bg-surface-2 px-2 py-0.5 font-medium text-muted">24 Nomor (96 Pernyataan)</span>
                                <span class="rounded-md border border-line bg-surface-2 px-2 py-0.5 font-medium text-muted">15 Menit</span>
                                <span class="rounded-md border border-line bg-surface-2 px-2 py-0.5 font-medium text-muted">3 Grafik DISC</span>
                            </div>
                        </div>

                        <div class="mt-5 border-t border-line/60 pt-4">
                            @if ($presets['disc'])
                                <div class="flex items-center gap-2">
                                    <x-ui.button :href="route('assessments.show', $presets['disc'])" size="sm" variant="secondary" class="flex-1" icon="eye">Buka Tes</x-ui.button>
                                    <form method="POST" action="{{ route('assessments.seed-disc') }}" class="inline">
                                        @csrf
                                        <x-ui.button type="submit" size="sm" variant="ghost" icon="refresh" title="Perbarui / sinkronkan soal"></x-ui.button>
                                    </form>
                                </div>
                            @else
                                <form method="POST" action="{{ route('assessments.seed-disc') }}">
                                    @csrf
                                    <x-ui.button type="submit" size="sm" variant="secondary" class="w-full" icon="plus">Aktifkan Paket DISC</x-ui.button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-col justify-between rounded-2xl border border-line bg-surface p-5 transition hover:border-blue-300 dark:hover:border-blue-800">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                    Bakat Kognitif & IQ
                                </span>
                                @if ($presets['aptitude'])
                                    <span class="flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                                        <x-icon name="check" class="size-3.5" /> Sudah Tersedia
                                    </span>
                                @endif
                            </div>
                            <h3 class="mt-3 text-[16px] font-bold text-ink">Bakat Skolastik & IQ</h3>
                            <p class="mt-1.5 text-[12.5px] leading-relaxed text-muted">
                                Mengukur daya nalar analitis melalui 4 domain kognitif (Verbal, Numerik, Logika, Spasial) dengan konversi estimasi skor IQ baku norma Wonderlic/CFIT.
                            </p>
                            <div class="mt-3 flex flex-wrap gap-1.5 text-[11px]">
                                <span class="rounded-md border border-line bg-surface-2 px-2 py-0.5 font-medium text-muted">30 Soal Pilihan Ganda</span>
                                <span class="rounded-md border border-line bg-surface-2 px-2 py-0.5 font-medium text-muted">25 Menit</span>
                                <span class="rounded-md border border-line bg-surface-2 px-2 py-0.5 font-medium text-muted">Barometer IQ</span>
                            </div>
                        </div>

                        <div class="mt-5 border-t border-line/60 pt-4">
                            @if ($presets['aptitude'])
                                <div class="flex items-center gap-2">
                                    <x-ui.button :href="route('assessments.show', $presets['aptitude'])" size="sm" variant="secondary" class="flex-1" icon="eye">Buka Tes</x-ui.button>
                                    <form method="POST" action="{{ route('assessments.seed-aptitude') }}" class="inline">
                                        @csrf
                                        <x-ui.button type="submit" size="sm" variant="ghost" icon="refresh" title="Perbarui / sinkronkan soal"></x-ui.button>
                                    </form>
                                </div>
                            @else
                                <form method="POST" action="{{ route('assessments.seed-aptitude') }}">
                                    @csrf
                                    <x-ui.button type="submit" size="sm" variant="secondary" class="w-full" icon="plus">Aktifkan Paket IQ</x-ui.button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-col justify-between rounded-2xl border border-line bg-surface p-5 transition hover:border-emerald-300 dark:hover:border-emerald-800">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                    Clerical & Accuracy
                                </span>
                                @if ($presets['accuracy'])
                                    <span class="flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                                        <x-icon name="check" class="size-3.5" /> Sudah Tersedia
                                    </span>
                                @endif
                            </div>
                            <h3 class="mt-3 text-[16px] font-bold text-ink">Ketelitian & Sikap Kerja</h3>
                            <p class="mt-1.5 text-[12.5px] leading-relaxed text-muted">
                                Pencocokan data administratif Kolom A vs B untuk mengukur kecepatan kerja, ketepatan detail, daya tahan konsentrasi, dan kuadran sikap kerja.
                            </p>
                            <div class="mt-3 flex flex-wrap gap-1.5 text-[11px]">
                                <span class="rounded-md border border-line bg-surface-2 px-2 py-0.5 font-medium text-muted">40 Butir SAMA/BEDA</span>
                                <span class="rounded-md border border-line bg-surface-2 px-2 py-0.5 font-medium text-muted">10 Menit</span>
                                <span class="rounded-md border border-line bg-surface-2 px-2 py-0.5 font-medium text-muted">Kuadran Sikap</span>
                            </div>
                        </div>

                        <div class="mt-5 border-t border-line/60 pt-4">
                            @if ($presets['accuracy'])
                                <div class="flex items-center gap-2">
                                    <x-ui.button :href="route('assessments.show', $presets['accuracy'])" size="sm" variant="secondary" class="flex-1" icon="eye">Buka Tes</x-ui.button>
                                    <form method="POST" action="{{ route('assessments.seed-accuracy') }}" class="inline">
                                        @csrf
                                        <x-ui.button type="submit" size="sm" variant="ghost" icon="refresh" title="Perbarui / sinkronkan soal"></x-ui.button>
                                    </form>
                                </div>
                            @else
                                <form method="POST" action="{{ route('assessments.seed-accuracy') }}">
                                    @csrf
                                    <x-ui.button type="submit" size="sm" variant="secondary" class="w-full" icon="plus">Aktifkan Paket Ketelitian</x-ui.button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
