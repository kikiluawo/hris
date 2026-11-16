<x-layouts.app :title="$assessment->title">
    @php
        $questionsData = $assessment->questions->map(function ($q, $index) {
            $traits = ($q->type === \App\Models\AssessmentQuestion::TYPE_DISC && filled($q->dimension))
                ? array_map('trim', explode(',', $q->dimension))
                : [];

            return [
                'id' => $q->id,
                'number' => $index + 1,
                'type' => $q->type,
                'type_label' => $q->typeLabel(),
                'dimension' => $q->dimension,
                'dimension_label' => $q->dimensionLabel(),
                'module' => $q->type === \App\Models\AssessmentQuestion::TYPE_DISC
                    ? 'disc'
                    : (in_array($q->dimension, ['Verbal', 'Numerik', 'Logika', 'Spasial']) ? 'aptitude' : ($q->dimension === 'Ketelitian' ? 'accuracy' : 'other')),
                'question' => $q->question,
                'options' => $q->options ?? [],
                'correct_option' => $q->correct_option,
                'traits' => $traits,
                'points' => $q->points,
                'has_image' => $q->hasImage(),
                'image_url' => $q->hasImage() ? App\Support\AssessmentImageStore::url($q->image_path) : null,
            ];
        })->values()->all();

        $discCount = $assessment->questions->where('type', \App\Models\AssessmentQuestion::TYPE_DISC)->count();
        $aptitudeCount = $assessment->questions->whereIn('dimension', ['Verbal', 'Numerik', 'Logika', 'Spasial'])->count();
        $accuracyCount = $assessment->questions->where('dimension', 'Ketelitian')->count();
        $defaultTab = ($attempts->isNotEmpty() || $assessment->invitations->isNotEmpty()) ? 'candidates' : 'questions';
    @endphp

    <div
        x-data="{
            tab: '{{ request('tab', $defaultTab) }}',
            questions: {{ Illuminate\Support\Js::from($questionsData) }},
            filter: 'all',
            search: '',
            page: 1,
            perPage: 10,
            activeNumber: null,

            get filteredQuestions() {
                let list = this.questions;

                if (this.filter !== 'all') {
                    if (['verbal', 'numerik', 'logika', 'spasial'].includes(this.filter)) {
                        list = list.filter((q) => (q.dimension || '').toLowerCase() === this.filter);
                    } else if (this.filter === 'disc') {
                        list = list.filter((q) => q.type === 'disc');
                    } else if (this.filter === 'aptitude') {
                        list = list.filter((q) => q.module === 'aptitude');
                    } else if (this.filter === 'accuracy') {
                        list = list.filter((q) => q.module === 'accuracy');
                    }
                }

                if (this.search.trim() !== '') {
                    const term = this.search.trim().toLowerCase();
                    list = list.filter((q) => q.question.toLowerCase().includes(term) || (q.dimension || '').toLowerCase().includes(term));
                }

                return list;
            },

            get totalPages() {
                return Math.ceil(this.filteredQuestions.length / this.perPage) || 1;
            },

            get paginatedQuestions() {
                const start = (this.page - 1) * this.perPage;
                return this.filteredQuestions.slice(start, start + this.perPage);
            },

            setFilter(next) {
                this.filter = next;
                this.page = 1;
            },

            jumpTo(num) {
                const idx = this.filteredQuestions.findIndex((q) => q.number === num);
                if (idx !== -1) {
                    this.page = Math.floor(idx / this.perPage) + 1;
                    this.activeNumber = num;
                    this.$nextTick(() => {
                        const el = document.getElementById('question-item-' + num);
                        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    });
                }
            }
        }"
    >
        
        <x-ui.page-header
            title="Kelola Tes"
            :description="$assessment->title . ' · ' . $assessment->duration_minutes . ' menit · ' . $assessment->questions->count() . ' soal'"
        >
            <x-slot:actions>
                <x-ui.badge :variant="$assessment->statusTone()">{{ $assessment->statusLabel() }}</x-ui.badge>
                @if (! $locked)
                    <x-ui.button :href="route('assessments.edit', $assessment)" variant="secondary" icon="pencil">Edit tes</x-ui.button>
                @endif
                <x-ui.button :href="route('assessments.index')" variant="secondary" icon="chevron-left">Kembali</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        @if (session('status'))
            <x-ui.alert variant="success" class="mb-4">{{ session('status') }}</x-ui.alert>
        @endif

        @if (session('error'))
            <x-ui.alert variant="danger" class="mb-4">{{ session('error') }}</x-ui.alert>
        @endif

        @if ($locked)
            <x-ui.alert variant="warning" class="mb-4">
                Tes ini sudah memiliki hasil pengerjaan dari kandidat. Struktur soal, durasi, dan pengaturan utama dikunci demi validitas hasil evaluasi.
            </x-ui.alert>
        @endif

        <div class="mb-5 flex flex-wrap items-center gap-2 border-b border-line pb-1">
            <button
                type="button"
                @click="tab = 'candidates'"
                :class="tab === 'candidates'
                    ? 'border-brand text-brand font-bold border-b-2 -mb-1 bg-brand-soft/40'
                    : 'text-muted hover:text-ink hover:bg-surface-2'"
                class="flex items-center gap-2 rounded-t-xl px-4 py-2.5 text-[13.5px] transition"
            >
                <x-icon name="users" class="size-4" />
                <span>Peserta & Hasil Ujian</span>
                <span class="rounded-full bg-surface-2 px-2 py-0.5 text-[11px] font-semibold text-muted">
                    {{ $attempts->count() }} selesai · {{ $assessment->invitations->count() }} diundang
                </span>
            </button>

            <button
                type="button"
                @click="tab = 'questions'"
                :class="tab === 'questions'
                    ? 'border-brand text-brand font-bold border-b-2 -mb-1 bg-brand-soft/40'
                    : 'text-muted hover:text-ink hover:bg-surface-2'"
                class="flex items-center gap-2 rounded-t-xl px-4 py-2.5 text-[13.5px] transition"
            >
                <x-icon name="clipboard" class="size-4" />
                <span>Butir Soal Tes</span>
                <span class="rounded-full bg-surface-2 px-2 py-0.5 text-[11px] font-semibold text-muted">
                    {{ $assessment->questions->count() }} butir
                </span>
            </button>

            <button
                type="button"
                @click="tab = 'settings'"
                :class="tab === 'settings'
                    ? 'border-brand text-brand font-bold border-b-2 -mb-1 bg-brand-soft/40'
                    : 'text-muted hover:text-ink hover:bg-surface-2'"
                class="flex items-center gap-2 rounded-t-xl px-4 py-2.5 text-[13.5px] transition"
            >
                <x-icon name="settings" class="size-4" />
                <span>Detail & Petunjuk</span>
            </button>
        </div>

        
        
        <div x-show="tab === 'candidates'" x-cloak>
            <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_380px] xl:items-start">
                
                <div class="space-y-4">
                    <x-ui.card title="Hasil Ujian Kandidat" :description="$attempts->count() . ' kandidat telah menyelesaikan tes'">
                        @if ($attempts->isEmpty())
                            <x-ui.empty-state
                                icon="users"
                                title="Belum ada hasil ujian"
                                description="Setelah kandidat membuka link ujian dan menyelesaikan seluruh soal, laporan skor akan langsung muncul di tabel ini."
                            />
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[700px] text-left">
                                    <thead>
                                        <tr class="border-b border-line text-[10.5px] uppercase tracking-[0.08em] text-subtle">
                                            <th class="pb-3 pr-3 font-semibold">Kandidat</th>
                                            <th class="pb-3 pr-3 font-semibold">Status</th>
                                            <th class="pb-3 pr-3 font-semibold">Skor</th>
                                            <th class="pb-3 pr-3 font-semibold">Pelanggaran</th>
                                            <th class="pb-3 pr-3 font-semibold">Mulai</th>
                                            <th class="pb-3 text-right font-semibold">Laporan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-line/80">
                                        @foreach ($attempts as $attempt)
                                            <tr class="transition hover:bg-surface-2/40">
                                                <td class="py-3.5 pr-3">
                                                    <p class="text-[13.5px] font-semibold text-ink">{{ $attempt->candidateName() }}</p>
                                                    <p class="text-[11.5px] text-subtle">
                                                        {{ $attempt->candidateEmail() }}{{ $attempt->invitation?->application?->jobOpening?->title ? ' · '.$attempt->invitation->application->jobOpening->title : '' }}
                                                    </p>
                                                </td>
                                                <td class="py-3.5 pr-3">
                                                    <x-ui.badge :variant="$attempt->statusTone()">{{ $attempt->statusLabel() }}</x-ui.badge>
                                                    @if ($attempt->end_reason)
                                                        <p class="mt-1 text-[11px] text-subtle">{{ $attempt->endReasonLabel() }}</p>
                                                    @endif
                                                </td>
                                                <td class="py-3.5 pr-3">
                                                    <p class="text-[13.5px] font-bold tabular-nums text-ink">
                                                        {{ $attempt->scoreLabel() }}{{ $attempt->score !== null ? '%' : '' }}
                                                    </p>
                                                    @if ($attempt->score !== null && ! $attempt->isFullyGraded())
                                                        <p class="text-[11px] text-amber-600 dark:text-amber-400">Menunggu penilaian esai</p>
                                                    @elseif ($attempt->score === null && ! $attempt->isInProgress())
                                                        <p class="text-[11px] text-amber-600 dark:text-amber-400">Belum dinilai</p>
                                                    @endif
                                                </td>
                                                <td class="py-3.5 pr-3">
                                                    <span class="inline-flex items-center gap-1 text-[12.5px] font-semibold tabular-nums {{ $attempt->violation_count > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-muted' }}">
                                                        @if ($attempt->violation_count > 0)
                                                            <x-icon name="alert-circle" class="size-3.5" />
                                                        @endif
                                                        {{ $attempt->violation_count }}×
                                                    </span>
                                                </td>
                                                <td class="py-3.5 pr-3 text-[12px] text-muted">
                                                    {{ $attempt->started_at->translatedFormat('d M Y H:i') }}
                                                </td>
                                                <td class="py-3.5 text-right">
                                                    <x-ui.button size="sm" variant="secondary" :href="route('assessments.attempt', $attempt)" icon="eye">
                                                        Periksa Laporan
                                                    </x-ui.button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </x-ui.card>
                </div>

                <div class="space-y-4">
                    @php $renewingId = session('renew_invitation'); @endphp

                    <x-ui.card title="Undang Pelamar" description="Pilih pelamar untuk dikirimkan link tes online proctoring pribadi.">
                        @if (! $assessment->isPublished())
                            <x-ui.alert variant="warning" class="mb-3">
                                Aktifkan tes ini terlebih dahulu agar link ujian bisa dibuka oleh kandidat.
                            </x-ui.alert>
                        @endif

                        @if ($applicants->isEmpty())
                            <x-ui.empty-state
                                icon="users"
                                title="Tidak ada pelamar baru"
                                description="Semua pelamar saat ini sudah memiliki link undangan untuk tes ini."
                            />
                        @else
                            <form
                                method="POST"
                                action="{{ route('assessments.invitations.store', $assessment) }}"
                                class="space-y-3.5"
                                x-data="{
                                    selected: {{ Illuminate\Support\Js::from(old('applications', [])) }},
                                    all: {{ $applicants->count() }},
                                    toggleAll() {
                                        const boxes = Array.from(this.$root.querySelectorAll('input[type=checkbox]'));
                                        this.selected = this.selected.length === this.all ? [] : boxes.map((box) => box.value);
                                    }
                                }"
                            >
                                @csrf
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-1.5">
                                        <x-ui.label>Pilih Kandidat ({{ $applicants->count() }})</x-ui.label>
                                        <x-ui.button type="button" size="sm" variant="ghost" x-on:click="toggleAll()">
                                            <span x-text="selected.length === all ? 'Batal Semua' : 'Pilih Semua'"></span>
                                        </x-ui.button>
                                    </div>
                                    <div class="max-h-60 space-y-1.5 overflow-y-auto rounded-xl border border-line bg-surface-2/40 p-2">
                                        @foreach ($applicants as $applicant)
                                            <label class="flex cursor-pointer items-start gap-2.5 rounded-lg border border-transparent bg-surface px-3 py-2 transition hover:border-line">
                                                <input
                                                    type="checkbox"
                                                    name="applications[]"
                                                    value="{{ $applicant->id }}"
                                                    x-model="selected"
                                                    class="mt-1 size-4 shrink-0 rounded border-line accent-brand"
                                                >
                                                <span class="min-w-0 flex-1">
                                                    <span class="flex items-center justify-between gap-2">
                                                        <span class="truncate text-[13px] font-semibold text-ink">{{ $applicant->candidate?->name ?? 'Tanpa nama' }}</span>
                                                        <x-ui.badge :variant="$applicant->statusTone()" size="sm">{{ $applicant->statusLabel() }}</x-ui.badge>
                                                    </span>
                                                    <span class="block truncate text-[11px] text-subtle">{{ $applicant->candidate?->email }} · Lowongan: {{ $applicant->jobOpening?->title ?? '—' }}</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <x-ui.field-error name="applications" />
                                </div>

                                <div>
                                    <x-ui.label for="expires_at">Masa Berlaku Link</x-ui.label>
                                    <x-ui.input name="expires_at" type="datetime-local" :value="old('expires_at', now()->addDays(7)->format('Y-m-d\TH:i'))" required />
                                    <p class="mt-1 text-[11px] text-subtle">Batas waktu kandidat dapat menekan tombol mulai tes.</p>
                                    <x-ui.field-error name="expires_at" />
                                </div>

                                <div>
                                    <x-ui.label for="duration_minutes">Durasi Khusus (Opsional)</x-ui.label>
                                    <x-ui.input name="duration_minutes" type="number" min="5" max="240" :value="old('duration_minutes')" placeholder="{{ $assessment->duration_minutes }} menit (default)" />
                                    <x-ui.field-error name="duration_minutes" />
                                </div>

                                <x-ui.button type="submit" class="w-full" icon="send" x-bind:disabled="selected.length === 0">
                                    <span x-text="selected.length > 0 ? 'Kirim Undangan ke ' + selected.length + ' Kandidat' : 'Pilih Kandidat Terlebih Dahulu'"></span>
                                </x-ui.button>
                            </form>
                        @endif
                    </x-ui.card>

                    <x-ui.card title="Daftar Undangan" :description="$assessment->invitations->count() . ' link undangan aktif'" :padded="false">
                        @if ($assessment->invitations->isEmpty())
                            <p class="px-5 py-6 text-[13px] text-muted">Belum ada undangan terkirim.</p>
                        @else
                            <div class="divide-y divide-line max-h-96 overflow-y-auto">
                                @foreach ($assessment->invitations as $invitation)
                                    @php $isRenewing = $renewingId === $invitation->id; @endphp
                                    <div class="px-5 py-3.5 text-[12.5px]" x-data="{
                                        open: {{ $isRenewing ? 'true' : 'false' }},
                                        copied: false,
                                        copy(text) {
                                            const done = () => { this.copied = true; setTimeout(() => { this.copied = false; }, 1800); };
                                            if (navigator.clipboard?.writeText) {
                                                navigator.clipboard.writeText(text).then(done).catch(() => this.legacyCopy(text, done));
                                            } else {
                                                this.legacyCopy(text, done);
                                            }
                                        },
                                        legacyCopy(text, done) {
                                            const field = document.createElement('textarea');
                                            field.value = text;
                                            field.setAttribute('readonly', '');
                                            field.style.position = 'fixed';
                                            field.style.opacity = '0';
                                            document.body.appendChild(field);
                                            field.select();
                                            try { document.execCommand('copy'); } catch (e) {}
                                            field.remove();
                                            done();
                                        }
                                    }">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <p class="truncate font-semibold text-ink">{{ $invitation->candidateName() }}</p>
                                                <p class="truncate text-[11px] text-subtle">{{ $invitation->candidateEmail() }}</p>
                                            </div>
                                            <x-ui.badge :variant="$invitation->statusTone()">{{ $invitation->statusLabel() }}</x-ui.badge>
                                        </div>

                                        <p class="mt-1 text-[11px] {{ $invitation->isExpired() ? 'font-semibold text-amber-600 dark:text-amber-400' : 'text-subtle' }}">
                                            {{ $invitation->durationMinutes() }} mnt · s/d {{ $invitation->expires_at->translatedFormat('d M H:i') }}
                                        </p>

                                        <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                            <x-ui.button type="button" size="sm" variant="secondary" icon="copy" data-link="{{ $invitation->link() }}" x-on:click="copy($el.dataset.link)">
                                                <span x-text="copied ? 'Tersalin' : 'Salin link'"></span>
                                            </x-ui.button>

                                            <x-ui.button size="sm" variant="secondary" icon="phone" :href="\App\Services\WhatsAppLink::assessmentInvitation($invitation)" target="_blank">
                                                WA
                                            </x-ui.button>

                                            @if ($invitation->attempt !== null)
                                                <x-ui.button size="sm" variant="secondary" icon="eye" :href="route('assessments.attempt', $invitation->attempt)">Hasil</x-ui.button>
                                            @else
                                                <x-ui.button type="button" size="sm" variant="ghost" icon="refresh" x-on:click="open = !open">Ubah</x-ui.button>

                                                @if (! $invitation->isRevoked())
                                                    <form method="POST" action="{{ route('assessments.invitations.revoke', [$assessment, $invitation]) }}">
                                                        @csrf
                                                        <button
                                                            type="submit"
                                                            class="text-[11.5px] text-muted hover:text-rose-600 transition px-2 py-1"
                                                            data-confirm="Cabut undangan {{ $invitation->candidateName() }}?"
                                                            data-confirm-title="Cabut undangan"
                                                            data-confirm-label="Ya, cabut"
                                                            data-confirm-tone="danger"
                                                        >Cabut</button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>

                                        <div x-show="open" x-cloak class="mt-2.5 rounded-xl border border-line bg-surface-2/40 p-2.5">
                                            <form method="POST" action="{{ route('assessments.invitations.renew', [$assessment, $invitation]) }}" class="space-y-2">
                                                @csrf
                                                <div>
                                                    <x-ui.label :for="'expires_at_'.$invitation->id" class="text-[11px]">Perpanjang Masa Berlaku</x-ui.label>
                                                    <x-ui.input
                                                        name="expires_at"
                                                        :id="'expires_at_'.$invitation->id"
                                                        type="datetime-local"
                                                        :value="$isRenewing ? old('expires_at', now()->addDays(7)->format('Y-m-d\TH:i')) : now()->addDays(7)->format('Y-m-d\TH:i')"
                                                        required
                                                    />
                                                </div>
                                                <div class="flex items-center gap-1.5">
                                                    <x-ui.button type="submit" size="sm" icon="check">Simpan</x-ui.button>
                                                    <x-ui.button type="button" size="sm" variant="ghost" x-on:click="open = false">Batal</x-ui.button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </x-ui.card>
                </div>
            </div>
        </div>

        
        
        <div x-show="tab === 'questions'" x-cloak class="space-y-4">
            
            <x-ui.card class="p-4 sm:p-5">
                <div class="flex flex-col gap-3.5">
                    
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line pb-3.5">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="mr-1 text-[12px] font-bold uppercase tracking-wider text-subtle">Modul:</span>

                            <button
                                type="button"
                                @click="setFilter('all')"
                                :class="filter === 'all' ? 'bg-brand text-brand-ink font-bold shadow-sm' : 'bg-surface-2 text-muted hover:text-ink hover:bg-surface-2/80'"
                                class="rounded-lg px-3 py-1.5 text-[12px] transition"
                            >
                                Semua ({{ $assessment->questions->count() }})
                            </button>

                            @if ($discCount > 0)
                                <button
                                    type="button"
                                    @click="setFilter('disc')"
                                    :class="filter === 'disc' ? 'bg-purple-600 text-white font-bold shadow-sm' : 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 hover:bg-purple-100'"
                                    class="rounded-lg px-3 py-1.5 text-[12px] transition"
                                >
                                    Kepribadian DISC ({{ $discCount }})
                                </button>
                            @endif

                            @if ($aptitudeCount > 0)
                                <button
                                    type="button"
                                    @click="setFilter('aptitude')"
                                    :class="filter === 'aptitude' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 hover:bg-blue-100'"
                                    class="rounded-lg px-3 py-1.5 text-[12px] transition"
                                >
                                    Bakat Kognitif & IQ ({{ $aptitudeCount }})
                                </button>
                            @endif

                            @if ($accuracyCount > 0)
                                <button
                                    type="button"
                                    @click="setFilter('accuracy')"
                                    :class="filter === 'accuracy' ? 'bg-emerald-600 text-white font-bold shadow-sm' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 hover:bg-emerald-100'"
                                    class="rounded-lg px-3 py-1.5 text-[12px] transition"
                                >
                                    Ketelitian Kerja ({{ $accuracyCount }})
                                </button>
                            @endif
                        </div>

                        <div class="w-full sm:w-64">
                            <input
                                type="search"
                                x-model="search"
                                @input="page = 1"
                                placeholder="Cari teks pertanyaan…"
                                class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px] text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/15"
                            >
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-subtle">
                                Loncat ke Nomor Soal:
                            </span>
                            <span class="text-[11.5px] text-muted">
                                Menampilkan <strong x-text="filteredQuestions.length"></strong> soal
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-1 max-h-24 overflow-y-auto p-1 rounded-xl border border-line bg-surface-2/30">
                            <template x-for="q in filteredQuestions" :key="'btn-' + q.id">
                                <button
                                    type="button"
                                    @click="jumpTo(q.number)"
                                    :class="activeNumber === q.number
                                        ? 'bg-brand text-brand-ink font-bold ring-2 ring-brand/30'
                                        : (q.type === 'disc'
                                            ? 'bg-purple-50 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 border border-purple-200 dark:border-purple-800'
                                            : (q.module === 'accuracy'
                                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800'
                                                : 'bg-surface text-muted border border-line hover:bg-surface-2 hover:text-ink'))"
                                    class="flex size-7 items-center justify-center rounded-lg text-[11px] font-semibold transition"
                                    x-text="q.number"
                                    :title="'Soal No. ' + q.number"
                                ></button>
                            </template>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <div class="space-y-3">
                <template x-for="question in paginatedQuestions" :key="'card-' + question.id">
                    <div
                        :id="'question-item-' + question.number"
                        class="rounded-2xl border bg-surface p-5 shadow-card transition-all"
                        :class="activeNumber === question.number ? 'border-brand ring-2 ring-brand/20' : 'border-line'"
                    >
                        
                        <div class="flex flex-wrap items-start justify-between gap-2 border-b border-line/60 pb-3">
                            <div class="flex items-center gap-2.5">
                                <span
                                    class="flex size-7 shrink-0 items-center justify-center rounded-xl bg-surface-2 text-[12px] font-bold text-ink"
                                    x-text="question.number"
                                ></span>
                                <span class="rounded-full bg-surface-2 px-2.5 py-0.5 text-[11px] font-semibold text-muted" x-text="question.type_label"></span>
                                <span
                                    class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold"
                                    :class="question.type === 'disc'
                                        ? 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300'
                                        : (question.module === 'accuracy'
                                            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
                                            : 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300')"
                                    x-show="question.dimension"
                                    x-text="question.dimension_label"
                                ></span>
                            </div>

                            <span class="rounded-full bg-surface-2 px-2.5 py-0.5 text-[11px] font-medium text-muted" x-show="question.type !== 'disc' && question.type !== 'scale'">
                                <span x-text="question.points"></span> poin
                            </span>
                        </div>

                        <div class="mt-3.5">
                            <p class="whitespace-pre-line text-[14px] font-medium leading-relaxed text-ink" x-text="question.question"></p>
                        </div>

                        <template x-if="question.has_image">
                            <div class="mt-3">
                                <img :src="question.image_url" alt="Gambar Soal" class="max-h-56 rounded-xl border border-line bg-surface object-contain">
                            </div>
                        </template>

                        <template x-if="question.type === 'disc'">
                            <div class="mt-4 rounded-xl border border-line bg-surface-2/30 p-3.5">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-purple-700 dark:text-purple-300 mb-2">
                                    Pilihan Jawaban (Urutan Karakter DISC):
                                </p>
                                <div class="grid gap-2">
                                    <template x-for="(opt, optIdx) in question.options" :key="'opt-' + optIdx">
                                        <div class="flex items-center gap-2.5 text-[13px] text-muted">
                                            <span
                                                class="flex size-5 shrink-0 items-center justify-center rounded-full border border-purple-200 bg-purple-50 text-[10px] font-bold text-purple-700 dark:border-purple-800 dark:bg-purple-900/40 dark:text-purple-300"
                                                x-text="String.fromCharCode(65 + optIdx)"
                                            ></span>
                                            <span class="text-ink" x-text="opt"></span>
                                            <template x-if="question.traits[optIdx]">
                                                <span class="rounded bg-surface px-1.5 py-0.5 text-[10px] font-bold text-subtle border border-line" x-text="'Karakter ' + question.traits[optIdx]"></span>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <template x-if="question.type === 'multiple_choice' || question.type === 'scale'">
                            <div class="mt-4 space-y-1.5">
                                <template x-for="(opt, optIdx) in question.options" :key="'opt-' + optIdx">
                                    <div
                                        class="flex items-center gap-2.5 rounded-xl border px-3 py-2 text-[13px] transition"
                                        :class="question.correct_option === optIdx
                                            ? 'border-brand bg-brand-soft/50 font-semibold text-brand-soft-ink'
                                            : 'border-line/70 bg-surface text-muted'"
                                    >
                                        <span
                                            class="flex size-5 shrink-0 items-center justify-center rounded-full border text-[10.5px] font-bold"
                                            :class="question.correct_option === optIdx
                                                ? 'border-brand bg-brand text-brand-ink'
                                                : 'border-line bg-surface text-muted'"
                                            x-text="String.fromCharCode(65 + optIdx)"
                                        ></span>
                                        <span class="flex-1" x-text="opt"></span>
                                        <template x-if="question.correct_option === optIdx">
                                            <span class="flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                                                <x-icon name="check" class="size-3.5" /> Jawaban Benar
                                            </span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-line bg-surface p-4">
                <p class="text-[12.5px] text-muted">
                    Menampilkan halaman <strong x-text="page"></strong> dari <strong x-text="totalPages"></strong>
                    (Total <span x-text="filteredQuestions.length"></span> butir)
                </p>

                <div class="flex items-center gap-2">
                    <x-ui.button
                        type="button"
                        size="sm"
                        variant="secondary"
                        @click="if (page > 1) { page--; window.scrollTo({ top: 200, behavior: 'smooth' }); }"
                        x-bind:disabled="page <= 1"
                        icon="chevron-left"
                    >
                        Sebelumnya
                    </x-ui.button>

                    <x-ui.button
                        type="button"
                        size="sm"
                        variant="secondary"
                        @click="if (page < totalPages) { page++; window.scrollTo({ top: 200, behavior: 'smooth' }); }"
                        x-bind:disabled="page >= totalPages"
                    >
                        Selanjutnya
                        <x-icon name="chevron-right" class="size-3.5 ml-1" />
                    </x-ui.button>
                </div>
            </div>
        </div>

        
        
        <div x-show="tab === 'settings'" x-cloak class="max-w-3xl space-y-4">
            <x-ui.card title="Konfigurasi & Petunjuk Tes">
                <div class="space-y-4">
                    <div>
                        <h3 class="text-[12px] font-bold uppercase tracking-wider text-subtle">Deskripsi Tes</h3>
                        <p class="mt-1 text-[14px] leading-relaxed text-ink">{{ $assessment->description ?: 'Tidak ada deskripsi.' }}</p>
                    </div>

                    @if ($assessment->instructions)
                        <div class="rounded-2xl border border-line bg-surface-2/40 p-4">
                            <h3 class="text-[12px] font-bold uppercase tracking-wider text-subtle">Petunjuk Pengerjaan Kandidat</h3>
                            <p class="mt-2 whitespace-pre-line text-[13px] leading-relaxed text-muted">{{ $assessment->instructions }}</p>
                        </div>
                    @endif

                    <dl class="grid gap-3 sm:grid-cols-3 pt-2">
                        <div class="rounded-xl border border-line bg-surface-2/40 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-[0.08em] text-subtle">Durasi Pengerjaan</dt>
                            <dd class="mt-1 text-[18px] font-bold text-ink">{{ $assessment->duration_minutes }} menit</dd>
                        </div>
                        <div class="rounded-xl border border-line bg-surface-2/40 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-[0.08em] text-subtle">Passing Score</dt>
                            <dd class="mt-1 text-[18px] font-bold text-ink">{{ $assessment->passing_score }}%</dd>
                        </div>
                        <div class="rounded-xl border border-line bg-surface-2/40 px-4 py-3">
                            <dt class="text-[11px] font-semibold uppercase tracking-[0.08em] text-subtle">Total Butir Soal</dt>
                            <dd class="mt-1 text-[18px] font-bold text-ink">{{ $assessment->questions->count() }} butir</dd>
                        </div>
                    </dl>

                    @if (! $locked)
                        <div class="pt-4 border-t border-line flex items-center justify-between">
                            <x-ui.button :href="route('assessments.edit', $assessment)" icon="pencil">Edit Konfigurasi & Soal</x-ui.button>
                            
                            <form method="POST" action="{{ route('assessments.destroy', $assessment) }}">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="text-[12.5px] font-semibold text-rose-600 hover:text-rose-700 transition"
                                    data-confirm="Hapus tes ini beserta semua undangannya?"
                                    data-confirm-title="Hapus tes"
                                    data-confirm-label="Ya, hapus"
                                    data-confirm-tone="danger"
                                >
                                    Hapus Tes
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
