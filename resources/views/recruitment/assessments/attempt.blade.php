<x-layouts.app :title="'Hasil tes · ' . $attempt->candidateName()">
    <x-ui.page-header
        title="Hasil tes"
        :description="$attempt->candidateName() . ' · ' . $assessment->title"
    >
        <x-slot:actions>
            <x-ui.badge :variant="$attempt->statusTone()">{{ $attempt->statusLabel() }}</x-ui.badge>
            @if ($attempt->isFullyGraded() && $attempt->score !== null)
                <x-ui.badge :variant="$passed ? 'success' : 'danger'">{{ $passed ? 'Lulus' : 'Belum lulus' }}</x-ui.badge>
            @elseif (! $attempt->isInProgress())
                <x-ui.badge variant="warning">Menunggu penilaian</x-ui.badge>
            @endif
            <x-ui.button :href="route('assessments.show', $assessment)" variant="secondary" icon="chevron-left">Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if (session('status'))
        <x-ui.alert variant="success" class="mb-4">{{ session('status') }}</x-ui.alert>
    @endif

    @if (session('error'))
        <x-ui.alert variant="danger" class="mb-4">{{ session('error') }}</x-ui.alert>
    @endif

    @if ($attempt->isInProgress())
        <x-ui.alert variant="warning" class="mb-4">
            Tes masih berjalan. Jawaban di bawah adalah yang sudah tersimpan; penilaian esai bisa dilakukan setelah kandidat selesai atau waktu habis.
        </x-ui.alert>
    @endif

    @if ($attempt->status === 'ended')
        <x-ui.alert variant="danger" class="mb-4">
            <span class="font-semibold">Tes dihentikan.</span> {{ $attempt->endReasonLabel() }}. Skor dihitung dari jawaban yang tersimpan sebelum tes berakhir.
        </x-ui.alert>
    @endif

    @php
        $reportsCount = collect([$discReport, $aptitudeReport, $accuracyReport])->filter()->count();
    @endphp

    <div class="mb-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @if ($reportsCount > 1)
            
            <x-ui.stat-card
                label="Profil DISC"
                :value="$discReport ? ($discReport['profile']['code'] ?? 'DISC') : '—'"
                icon="user"
                :footnote="$discReport ? ($discReport['profile']['title'] ?? 'Kepribadian') : 'Tidak diujikan'"
            />
            <x-ui.stat-card
                label="Estimasi IQ"
                :value="$aptitudeReport ? $aptitudeReport['estimated_iq'] : '—'"
                icon="sparkles"
                :footnote="$aptitudeReport ? $aptitudeReport['iq_classification'] : 'Tidak diujikan'"
            />
            <x-ui.stat-card
                label="Ketelitian Kerja"
                :value="$accuracyReport ? ($accuracyReport['accuracy_rate'] . '%') : '—'"
                icon="check"
                :footnote="$accuracyReport ? ($accuracyReport['quadrant']['code'] . ' · ' . $accuracyReport['quadrant']['title']) : 'Tidak diujikan'"
            />
            <x-ui.stat-card
                label="Pelanggaran & Waktu"
                :value="$attempt->violation_count . '× Pelanggaran'"
                icon="shield"
                :footnote="$assessment->duration_minutes . 'm · ' . $attempt->answeredCount() . '/' . $questions->count() . ' Terjawab'"
            />
        @elseif ($accuracyReport)
            <x-ui.stat-card label="Akurasi Ketelitian" :value="$accuracyReport['accuracy_rate'] . '%'" icon="check" :footnote="$accuracyReport['accuracy_evaluation']['badge'] . ' · ' . $accuracyReport['correct_count'] . '/' . $accuracyReport['attempted_count'] . ' Benar'" />
            <x-ui.stat-card label="Kuadran Kerja" :value="$accuracyReport['quadrant']['code']" icon="sparkles" :footnote="$accuracyReport['quadrant']['title']" />
            <x-ui.stat-card label="Kecepatan" :value="$accuracyReport['speed_per_minute'] . ' soal/m'" icon="clock" :footnote="$accuracyReport['speed_evaluation']['label']" />
            <x-ui.stat-card label="Kekeliruan" :value="$accuracyReport['error_count'] . ' salah'" icon="shield" :footnote="'Pelanggaran: ' . $attempt->violation_count . '×'" />
        @elseif ($aptitudeReport)
            <x-ui.stat-card label="Estimasi IQ" :value="$aptitudeReport['estimated_iq']" icon="sparkles" :footnote="$aptitudeReport['iq_classification']" />
            <x-ui.stat-card label="Akurasi Kognitif" :value="$aptitudeReport['accuracy_percent'] . '%'" icon="grid" :footnote="$aptitudeReport['correct_count'] . '/' . $aptitudeReport['total_questions'] . ' Soal Benar'" />
            <x-ui.stat-card label="Pelanggaran" :value="$attempt->violation_count . '×'" icon="shield" :footnote="$attempt->violation_count > 0 ? 'Lihat log di bawah' : 'Tidak ada pelanggaran'" />
            <x-ui.stat-card label="Durasi tes" :value="$assessment->duration_minutes . ' menit'" icon="clock" :footnote="'Nilai lulus ' . $assessment->passing_score . '%'" />
        @else
            <x-ui.stat-card label="Skor" :value="$attempt->scoreLabel() . ($attempt->score !== null ? '%' : '')" icon="grid" :footnote="$attempt->isFullyGraded() ? 'Semua soal dinilai' : 'Menunggu penilaian'" />
            <x-ui.stat-card label="Terjawab" :value="$attempt->answeredCount() . '/' . $questions->count()" icon="clipboard" />
            <x-ui.stat-card label="Pelanggaran" :value="$attempt->violation_count . '×'" icon="shield" :footnote="$attempt->violation_count > 0 ? 'Lihat log di bawah' : 'Tidak ada pelanggaran'" />
            <x-ui.stat-card label="Durasi tes" :value="$assessment->duration_minutes . ' menit'" icon="clock" :footnote="'Nilai lulus ' . $assessment->passing_score . '%'" />
        @endif
    </div>

    @if ($discReport)
        <div class="mb-5">
            @include('recruitment.assessments.disc-report', ['report' => $discReport])
        </div>
    @endif

    @if ($aptitudeReport)
        <div class="mb-5">
            @include('recruitment.assessments.aptitude-report', ['report' => $aptitudeReport])
        </div>
    @endif

    @if ($accuracyReport)
        <div class="mb-5">
            @include('recruitment.assessments.accuracy-report', ['report' => $accuracyReport])
        </div>
    @endif

    <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_330px] xl:items-start">
        <div class="space-y-4">
            <x-ui.card title="Jawaban kandidat" description="Pilihan ganda dinilai otomatis. Esai dinilai per soal, soal skala dinilai per dimensi, lalu simpan penilaian.">
                <form method="POST" action="{{ route('assessments.grade', $attempt) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    @foreach ($questions as $question)
                        @php
                            $answer = $answers->get($question->id);
                            $isMcq = $question->isMultipleChoice();
                            $isScale = $question->isScale();
                            $isDisc = $question->isDisc();
                            $chosen = $answer?->selected_option;
                            $most = $answer?->most_option;
                            $least = $answer?->least_option;
                        @endphp

                        <div class="rounded-xl border border-line p-4">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div class="flex items-start gap-2.5">
                                    <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-surface-2 text-[11px] font-bold text-muted">{{ $loop->iteration }}</span>
                                    <p class="whitespace-pre-line text-[13.5px] font-medium leading-relaxed">{{ $question->question }}</p>
                                </div>
                                <div class="flex shrink-0 items-center gap-1.5">
                                    <x-ui.badge :variant="$isMcq ? 'brand' : ($isScale ? 'warning' : ($isDisc ? 'success' : 'info'))">{{ $question->typeLabel() }}</x-ui.badge>
                                    @if ($isScale)
                                        <x-ui.badge variant="neutral">{{ $question->dimensionLabel() }}</x-ui.badge>
                                    @elseif ($isDisc)
                                        <x-ui.badge variant="neutral">Kepribadian</x-ui.badge>
                                    @else
                                        @if ($question->dimension)
                                            <x-ui.badge variant="neutral">{{ $question->dimension }}</x-ui.badge>
                                        @endif
                                        <x-ui.badge variant="neutral">{{ $question->points }} poin</x-ui.badge>
                                    @endif
                                </div>
                            </div>

                            @if ($question->hasImage())
                                <img
                                    src="{{ App\Support\AssessmentImageStore::url($question->image_path) }}"
                                    alt="Gambar soal"
                                    class="mt-3 max-h-64 w-full rounded-xl border border-line bg-surface object-contain"
                                >
                            @endif

                            @if ($isDisc)
                                <ul class="mt-3 space-y-1.5 pl-8">
                                    @foreach ($question->options ?? [] as $optionIndex => $option)
                                        @php
                                            $optText = is_array($option) ? ($option['text'] ?? '') : $option;
                                            $optTrait = is_array($option) ? ($option['trait'] ?? '') : (['D','I','S','C'][$optionIndex % 4] ?? '');
                                            $isMost = $most === $optionIndex;
                                            $isLeast = $least === $optionIndex;
                                        @endphp
                                        <li class="flex flex-wrap items-center justify-between gap-2 rounded-lg px-3 py-2 text-[13px] {{ $isMost ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-300 font-semibold border border-emerald-300/40' : ($isLeast ? 'bg-rose-50 text-rose-800 dark:bg-rose-400/10 dark:text-rose-300 font-semibold border border-rose-300/40' : 'bg-surface text-muted border border-line/40') }}">
                                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                                <span class="flex size-5 shrink-0 items-center justify-center rounded-full border border-line bg-surface text-[10.5px] font-bold text-muted">
                                                    {{ chr(65 + $optionIndex) }}
                                                </span>
                                                <span class="truncate sm:whitespace-normal">{{ $optText }}</span>
                                                <span class="rounded bg-surface-2 px-1.5 py-0.5 text-[10px] font-mono text-subtle shrink-0">Dimensi {{ $optTrait }}</span>
                                            </div>
                                            <div class="flex items-center gap-1.5 shrink-0">
                                                @if ($isMost)
                                                    <span class="rounded-md bg-emerald-600 text-white px-2 py-0.5 text-[11px] font-bold">✓ Paling (Most)</span>
                                                @endif
                                                @if ($isLeast)
                                                    <span class="rounded-md bg-rose-600 text-white px-2 py-0.5 text-[11px] font-bold">✗ Kurang (Least)</span>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>

                                <div class="mt-2 flex flex-wrap items-center gap-2 pl-8">
                                    @if ($most === null && $least === null)
                                        <x-ui.badge variant="neutral">Tidak dijawab</x-ui.badge>
                                    @elseif ($most === null || $least === null)
                                        <x-ui.badge variant="warning">Jawaban belum lengkap</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="success">Terisi Paling & Kurang</x-ui.badge>
                                    @endif
                                </div>
                            @elseif ($isMcq || $isScale)
                                <ul class="mt-3 space-y-1.5 pl-8">
                                    @foreach ($question->options ?? [] as $optionIndex => $option)
                                        @php
                                            $isChosen = $chosen === $optionIndex;
                                            $isCorrect = $isMcq && $question->correct_option === $optionIndex;
                                            $chosenWrong = $isMcq && $isChosen && ! $isCorrect;
                                            $chosenScale = $isScale && $isChosen;
                                            $optionImage = $question->optionImage($optionIndex);
                                        @endphp
                                        <li class="flex flex-wrap items-center gap-2 rounded-lg px-2 py-1 text-[13px] {{ $isCorrect ? 'bg-emerald-50 font-semibold text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-300' : ($chosenWrong ? 'bg-rose-50 text-rose-700 dark:bg-rose-400/10 dark:text-rose-300' : ($chosenScale ? 'bg-brand-soft font-semibold text-brand-soft-ink' : 'text-muted')) }}">
                                            <span class="flex size-5 shrink-0 items-center justify-center rounded-full border text-[10.5px] font-bold {{ $isCorrect ? 'border-emerald-300 bg-emerald-100 text-emerald-800 dark:border-emerald-400/30 dark:bg-emerald-400/15 dark:text-emerald-300' : 'border-line bg-surface' }}">
                                                {{ chr(65 + $optionIndex) }}
                                            </span>
                                            @if ($optionImage)
                                                <img src="{{ App\Support\AssessmentImageStore::url($optionImage) }}" alt="Gambar pilihan {{ chr(65 + $optionIndex) }}" class="size-12 rounded-lg border border-line bg-surface object-contain">
                                            @endif
                                            <span class="flex-1">{{ $option }}</span>
                                            @if ($isChosen)
                                                <span class="shrink-0 text-[10.5px] font-bold uppercase tracking-wide">Dipilih</span>
                                            @endif
                                            @if ($isCorrect)
                                                <x-icon name="check" class="size-3.5 shrink-0" />
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>

                                <div class="mt-2 flex flex-wrap items-center gap-2 pl-8">
                                    @if ($chosen === null)
                                        <x-ui.badge variant="neutral">Tidak dijawab</x-ui.badge>
                                    @elseif ($isScale)
                                        <x-ui.badge variant="success">Dipilih kandidat</x-ui.badge>
                                    @elseif ($answer?->is_correct)
                                        <x-ui.badge variant="success">Benar · {{ $answer->points_awarded }} poin</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="danger">Salah · 0 poin</x-ui.badge>
                                    @endif
                                </div>
                            @else
                                <div class="mt-3 pl-8">
                                    @if ($answer?->answer)
                                        <div class="whitespace-pre-line rounded-xl border border-line bg-surface-2/50 px-3.5 py-3 text-[13px] leading-relaxed text-muted">{{ $answer->answer }}</div>
                                    @else
                                        <p class="text-[12.5px] italic text-subtle">Tidak dijawab.</p>
                                    @endif

                                    @if (! $attempt->isInProgress() && $answer)
                                        <div class="mt-2.5 flex flex-wrap items-center gap-2">
                                            <label class="flex items-center gap-2 text-[12px] font-semibold text-muted">
                                                Poin
                                                <input
                                                    type="number"
                                                    name="points[{{ $answer->id }}]"
                                                    value="{{ old('points.' . $answer->id, $answer->points_awarded) }}"
                                                    min="0"
                                                    max="{{ $question->points }}"
                                                    step="0.5"
                                                    inputmode="decimal"
                                                    class="h-9 w-24 rounded-lg border border-line bg-surface px-2.5 text-[13px] text-ink outline-none focus:border-brand focus:ring-2 focus:ring-brand/15"
                                                >
                                            </label>
                                            <span class="text-[11.5px] text-subtle">maks. {{ $question->points }} poin</span>
                                        </div>
                                    @elseif (! $attempt->isInProgress() && ! $answer)
                                        <p class="mt-2 text-[12px] text-subtle">Tidak ada jawaban untuk dinilai.</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach

                    @if ($dimensionNames->isNotEmpty() && ! $attempt->isInProgress())
                        @php $oldDimensions = old('dimensions', []); @endphp
                        <div
                            x-data="{
                                refill() {
                                    this.$root.querySelectorAll('[data-auto-score]').forEach((input) => {
                                        if (input.dataset.autoScore !== '') {
                                            input.value = input.dataset.autoScore;
                                        }
                                    });
                                }
                            }"
                            class="rounded-xl border border-amber-200 bg-amber-50/60 p-4 dark:border-amber-400/25 dark:bg-amber-400/10"
                        >
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <p class="text-[13px] font-semibold">Penilaian psikotes per dimensi</p>
                                    <p class="mt-1 text-[12px] leading-relaxed text-muted">Terisi otomatis dari jawaban kandidat: pilihan pertama bernilai 0, pilihan terakhir 100, lalu dirata-rata per dimensi. Angka masih bisa Anda ubah sebelum disimpan.</p>
                                </div>
                                <x-ui.button type="button" variant="secondary" size="sm" icon="refresh" x-on:click="refill()">Isi ulang dari jawaban</x-ui.button>
                            </div>

                            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                @foreach ($dimensionNames as $dimension)
                                    @php
                                        $report = $dimensionReport[$dimension] ?? ['score' => null, 'answered' => 0, 'total' => 0];
                                        $value = $oldDimensions[$dimension] ?? ($dimensionScores[$dimension] ?? $report['score'] ?? '');
                                    @endphp
                                    <label class="block rounded-lg border border-line bg-surface px-3 py-2.5">
                                        <span class="flex items-baseline justify-between gap-2">
                                            <span class="text-[12px] font-semibold text-muted">{{ $dimension }}</span>
                                            <span class="text-[11px] text-subtle">{{ $report['answered'] }}/{{ $report['total'] }} terjawab</span>
                                        </span>
                                        <input
                                            type="number"
                                            name="dimensions[{{ $dimension }}]"
                                            value="{{ $value }}"
                                            data-auto-score="{{ $report['score'] ?? '' }}"
                                            min="0"
                                            max="100"
                                            step="0.01"
                                            inputmode="decimal"
                                            placeholder="0–100"
                                            class="mt-1 h-9 w-full rounded-lg border border-line bg-surface px-2.5 text-[13px] text-ink outline-none focus:border-brand focus:ring-2 focus:ring-brand/15"
                                        >
                                    </label>
                                @endforeach
                            </div>

                            <label class="mt-3 block">
                                <span class="text-[12px] font-semibold text-muted">Catatan penilaian</span>
                                <textarea
                                    name="notes"
                                    rows="3"
                                    maxlength="2000"
                                    placeholder="Kesan HRD, kecenderungan jawaban, atau rekomendasi..."
                                    class="mt-1 w-full rounded-xl border border-line bg-surface px-3 py-2.5 text-[13px] text-ink shadow-sm outline-none transition placeholder:text-subtle focus:border-brand focus:ring-4 focus:ring-brand/15"
                                >{{ old('notes', $attempt->review_notes) }}</textarea>
                            </label>
                        </div>
                    @endif

                    @if (! $attempt->isInProgress())
                        <div class="flex flex-wrap items-center justify-end gap-2 border-t border-line pt-4">
                            <p class="mr-auto text-[12px] text-subtle">Skor akhir dihitung ulang setelah penilaian disimpan.</p>
                            <x-ui.button type="submit" icon="check">Simpan penilaian</x-ui.button>
                        </div>
                    @endif
                </form>
            </x-ui.card>
        </div>

        <div class="space-y-4">
            <x-ui.card title="Ringkasan hasil tes">
                <dl class="space-y-2.5 text-[13px]">
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-muted">Mulai</dt>
                        <dd class="text-right font-medium">{{ $attempt->started_at->translatedFormat('d M Y H:i') }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-muted">Batas waktu</dt>
                        <dd class="text-right font-medium">{{ $attempt->ends_at->translatedFormat('d M Y H:i') }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-muted">Selesai</dt>
                        <dd class="text-right font-medium">{{ $attempt->submitted_at?->translatedFormat('d M Y H:i') ?? '—' }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-muted">Alasan berakhir</dt>
                        <dd class="text-right font-medium">{{ $attempt->endReasonLabel() ?? '—' }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-muted">Skor</dt>
                        <dd class="text-right font-semibold tabular-nums">{{ $attempt->scoreLabel() }}{{ $attempt->score !== null ? '%' : '' }}</dd>
                    </div>
                </dl>
            </x-ui.card>

            <x-ui.card title="Foto Pengawasan Kamera" description="{{ $attempt->snapshots->count() }} foto terekam selama ujian." :padded="false">
                @if ($attempt->snapshots->isEmpty())
                    <p class="px-5 py-6 text-[13px] text-muted">Belum ada foto snapshot terekam.</p>
                @else
                    <div class="p-3" x-data="{ preview: null, previewTime: null, previewTrigger: null }">
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            @foreach ($attempt->snapshots as $snapshot)
                                <button
                                    type="button"
                                    x-on:click="preview = '{{ route('assessments.attempts.snapshot', $snapshot) }}'; previewTime = '{{ $snapshot->captured_at->translatedFormat('H:i:s') }}'; previewTrigger = '{{ $snapshot->triggerLabel() }}'"
                                    class="group relative aspect-[4/3] w-full overflow-hidden rounded-lg border border-line bg-surface-2 transition hover:ring-2 hover:ring-brand focus:outline-none"
                                >
                                    <img
                                        src="{{ route('assessments.attempts.snapshot', $snapshot) }}"
                                        alt="Foto pengawasan {{ $snapshot->captured_at->translatedFormat('H:i:s') }}"
                                        loading="lazy"
                                        class="size-full object-cover transition duration-200 group-hover:scale-105"
                                    >
                                    <span class="absolute bottom-1 left-1 rounded bg-black/75 px-1.5 py-0.5 text-[9.5px] font-medium text-white backdrop-blur">
                                        {{ $snapshot->captured_at->format('H:i:s') }}
                                    </span>
                                    <span class="absolute top-1 right-1 rounded px-1 py-0.5 text-[8.5px] font-bold uppercase tracking-wider {{ $snapshot->triggerTone() === 'danger' ? 'bg-rose-600 text-white' : ($snapshot->triggerTone() === 'success' ? 'bg-emerald-600 text-white' : 'bg-surface/90 text-ink backdrop-blur') }}">
                                        {{ $snapshot->triggerLabel() }}
                                    </span>
                                </button>
                            @endforeach
                        </div>

                        <template x-teleport="body">
                            <div
                                x-show="preview"
                                x-cloak
                                @keydown.escape.window="preview = null"
                                class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 bg-black/80 backdrop-blur-md"
                                role="dialog"
                                aria-modal="true"
                                x-on:click.self="preview = null"
                            >
                                <div
                                    x-show="preview"
                                    x-transition:enter="ease-out duration-200"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="ease-in duration-150"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    class="relative flex flex-col w-full max-w-2xl max-h-[90vh] overflow-hidden rounded-3xl border border-white/20 bg-surface shadow-2xl"
                                    x-on:click.stop
                                >
                                    <div class="flex items-center justify-between border-b border-line bg-surface-2/60 px-5 py-3.5">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <span class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-brand/10 text-brand">
                                                <x-icon name="camera" class="size-4" />
                                            </span>
                                            <div class="min-w-0">
                                                <h4 class="text-[13.5px] font-bold text-ink">Foto Pengawasan Kamera</h4>
                                                <p class="text-[11px] text-muted truncate">
                                                    Terekam <span class="font-semibold text-ink" x-text="previewTime"></span>
                                                    <template x-if="previewTrigger">
                                                        <span> · Trigger: <span class="font-semibold text-brand" x-text="previewTrigger"></span></span>
                                                    </template>
                                                </p>
                                            </div>
                                        </div>
                                        <button
                                            type="button"
                                            x-on:click="preview = null"
                                            class="flex size-8 shrink-0 items-center justify-center rounded-xl border border-line bg-surface text-subtle hover:bg-surface-2 hover:text-ink transition"
                                            title="Tutup (Esc)"
                                        >
                                            <x-icon name="close" class="size-4" />
                                        </button>
                                    </div>
                                    <div class="flex flex-1 items-center justify-center bg-black/95 p-3 sm:p-5 overflow-hidden">
                                        <img :src="preview" alt="Foto pengawasan kamera" class="max-h-[70vh] w-auto max-w-full rounded-2xl object-contain shadow-lg">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                @endif
            </x-ui.card>

            <x-ui.card title="Log pengawasan" description="Catatan aktivitas selama tes." :padded="false">
                @if ($attempt->events->isEmpty())
                    <p class="px-5 py-6 text-[13px] text-muted">Belum ada aktivitas tercatat.</p>
                @else
                    <ol class="divide-y divide-line">
                        @foreach ($attempt->events as $event)
                            <li class="flex items-start gap-3 px-5 py-3">
                                <span class="mt-1.5 size-2 shrink-0 rounded-full {{ $event->tone() === 'danger' ? 'bg-rose-500' : ($event->tone() === 'warning' ? 'bg-amber-500' : ($event->tone() === 'success' ? 'bg-emerald-500' : 'bg-subtle')) }}"></span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[12.5px] font-semibold">{{ $event->typeLabel() }}</p>
                                    <p class="text-[11px] text-subtle">{{ $event->occurred_at->translatedFormat('d M Y H:i:s') }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </x-ui.card>

            <x-ui.card title="Kandidat">
                <div class="space-y-1.5 text-[13px]">
                    <p class="font-semibold">{{ $attempt->candidateName() }}</p>
                    <p class="text-muted">{{ $attempt->candidateEmail() }}</p>
                    @if ($attempt->invitation?->application?->candidate?->phone)
                        <p class="text-muted">{{ $attempt->invitation->application->candidate->phone }}</p>
                    @endif
                    <p class="pt-1 text-[12px] text-subtle">Posisi: {{ $attempt->invitation?->application?->jobOpening?->title ?? '—' }}</p>
                    <p class="text-[12px] text-subtle">Diundang: {{ $attempt->invitation?->created_at?->translatedFormat('d M Y H:i') }}</p>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
