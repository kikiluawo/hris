<x-layouts.app :title="$assessment ? 'Edit tes' : 'Buat tes'">
    <div
        class="space-y-4"
        x-data="assessmentForm({{ Illuminate\Support\Js::from([
            'questions' => $initialQuestions,
            'bank' => $bankItems ?? [],
            'uploadUrl' => route('assessments.image.store'),
            'imageRoot' => App\Support\AssessmentImageStore::urlRoot(),
        ]) }})"
    >
        <form
            method="POST"
            action="{{ $assessment ? route('assessments.update', $assessment) : route('assessments.store') }}"
            novalidate
            x-on:submit="if (!validate()) { $event.preventDefault(); }"
        >
            @csrf
            @if ($assessment)
                @method('PATCH')
            @endif

            <input type="hidden" name="questions_json" :value="JSON.stringify(questions)">

            <div class="sticky top-0 z-20 -mx-4 -mt-4 mb-4 border-b border-line bg-surface/95 px-4 py-3.5 backdrop-blur sm:-mx-6 sm:px-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h1 class="truncate text-[16px] font-bold text-ink sm:text-[18px]">
                                {{ $assessment ? 'Edit: ' . $assessment->title : 'Buat Tes Online Baru' }}
                            </h1>
                            <span class="rounded-full bg-brand-soft px-2.5 py-0.5 text-[11px] font-bold text-brand-soft-ink">
                                <span x-text="questions.length"></span> Soal
                            </span>
                        </div>
                        <p class="truncate text-[11.5px] text-muted">
                            Total Poin: <strong x-text="totalPoints()"></strong> · Passing Score: <strong x-text="$el.closest('form').querySelector('[name=passing_score]')?.value || 70"></strong>%
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        
                        <div class="flex items-center rounded-xl border border-line bg-surface-2/60 p-0.5">
                            <button
                                type="button"
                                @click="formTab = 'settings'"
                                :class="formTab === 'settings' ? 'bg-surface text-ink font-bold shadow-sm' : 'text-muted hover:text-ink'"
                                class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-[12.5px] transition"
                            >
                                <x-icon name="settings" class="size-3.5" />
                                <span>Pengaturan & Waktu</span>
                            </button>

                            <button
                                type="button"
                                @click="formTab = 'questions'"
                                :class="formTab === 'questions' ? 'bg-surface text-ink font-bold shadow-sm' : 'text-muted hover:text-ink'"
                                class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-[12.5px] transition"
                            >
                                <x-icon name="clipboard" class="size-3.5" />
                                <span>Penyusun Soal (<span x-text="questions.length"></span>)</span>
                            </button>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <button
                                type="submit"
                                :disabled="saving"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand px-4 py-2 text-sm font-semibold text-brand-ink shadow-sm transition hover:bg-brand-hover focus:outline-none focus-visible:ring-4 focus-visible:ring-brand/20 disabled:pointer-events-none disabled:opacity-60"
                            >
                                <span x-show="!saving" class="flex items-center gap-2">
                                    <x-icon name="check" class="size-4" />
                                    <span>{{ $assessment ? 'Simpan Perubahan' : 'Simpan Tes' }}</span>
                                </span>
                                <span x-show="saving" x-cloak class="flex items-center gap-2">
                                    <svg class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                    <span>Menyimpan…</span>
                                </span>
                            </button>
                            <x-ui.button :href="$assessment ? route('assessments.show', $assessment) : route('assessments.index')" variant="secondary">
                                Batal
                            </x-ui.button>
                        </div>
                    </div>
                </div>
            </div>

            @if (session('error'))
                <x-ui.alert variant="danger" class="mb-4">{{ session('error') }}</x-ui.alert>
            @endif

            @if (session('status'))
                <x-ui.alert variant="success" class="mb-4">{{ session('status') }}</x-ui.alert>
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

            <div class="mb-4 space-y-3" x-show="errorMessage" x-cloak>
                <x-ui.alert variant="danger">
                    <div class="flex items-center justify-between">
                        <span x-text="errorMessage"></span>
                        <button type="button" @click="errorMessage = ''" class="text-subtle hover:text-ink">
                            <x-icon name="close" class="size-4" />
                        </button>
                    </div>
                </x-ui.alert>
            </div>

            
            
            <div x-show="formTab === 'settings'" x-cloak class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_340px] xl:items-start">
                <div class="space-y-4">
                    <x-ui.card title="Informasi Utama Tes" description="Informasi yang dilihat kandidat di undangan dan halaman tes.">
                        <div class="space-y-4">
                            <div>
                                <x-ui.label for="title">Nama Tes</x-ui.label>
                                <x-ui.input name="title" :value="$assessment?->title" placeholder="Contoh: Tes Komprehensif HRD (Kepribadian, IQ & Ketelitian)" required />
                                <x-ui.field-error name="title" />
                            </div>

                            <div class="grid gap-4 sm:grid-cols-3">
                                <div>
                                    <x-ui.label for="duration_minutes">Durasi Ujian (Menit)</x-ui.label>
                                    <x-ui.input type="number" name="duration_minutes" :value="$assessment?->duration_minutes ?? 45" min="5" max="240" required />
                                    <p class="mt-1 text-[11px] text-subtle">Waktu hitung mundur saat tes berjalan.</p>
                                    <x-ui.field-error name="duration_minutes" />
                                </div>

                                <div>
                                    <x-ui.label for="passing_score">Passing Score (%)</x-ui.label>
                                    <x-ui.input type="number" name="passing_score" :value="$assessment?->passing_score ?? 70" min="0" max="100" required />
                                    <p class="mt-1 text-[11px] text-subtle">Nilai minimum kelulusan.</p>
                                    <x-ui.field-error name="passing_score" />
                                </div>

                                <div>
                                    <x-ui.label for="status">Status Publikasi</x-ui.label>
                                    <x-ui.select name="status" :options="\App\Models\Assessment::statusOptions()" :selected="$assessment?->status ?? 'draft'" />
                                    <p class="mt-1 text-[11px] text-subtle">Hanya tes Aktif yang bisa dibuka kandidat.</p>
                                    <x-ui.field-error name="status" />
                                </div>
                            </div>

                            <div>
                                <x-ui.label for="description">Deskripsi Singkat</x-ui.label>
                                <x-ui.textarea name="description" rows="2" :value="$assessment?->description" placeholder="Jelaskan tujuan dan cakupan penilaian tes ini secara singkat." />
                                <x-ui.field-error name="description" />
                            </div>

                            <div>
                                <x-ui.label for="instructions">Petunjuk Pengerjaan Kandidat</x-ui.label>
                                <x-ui.textarea name="instructions" rows="4" :value="$assessment?->instructions" placeholder="Tuliskan aturan ujian, instruksi kamera proctoring, dan saran bagi peserta." />
                                <p class="mt-1 text-[11.5px] text-subtle">Ditampilkan di halaman pembuka sebelum kandidat menekan tombol mulai.</p>
                                <x-ui.field-error name="instructions" />
                            </div>
                        </div>
                    </x-ui.card>
                </div>

                <div class="space-y-4">
                    <x-ui.card title="Struktur Soal Saat Ini">
                        <dl class="space-y-2.5 text-[13px]">
                            <div class="flex items-center justify-between border-b border-line pb-2">
                                <dt class="font-medium text-muted">Total butir soal</dt>
                                <dd class="font-bold tabular-nums text-ink"><span x-text="questions.length"></span> butir</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-muted">Pilihan ganda (Kognitif/Ketelitian)</dt>
                                <dd class="font-semibold tabular-nums text-ink" x-text="questions.filter((q) => q.type === 'multiple_choice').length"></dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-muted">Kepribadian DISC</dt>
                                <dd class="font-semibold tabular-nums text-purple-700 dark:text-purple-300" x-text="questions.filter((q) => q.type === 'disc').length"></dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-muted">Skala / Psikotes</dt>
                                <dd class="font-semibold tabular-nums text-amber-700 dark:text-amber-300" x-text="questions.filter((q) => q.type === 'scale').length"></dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-muted">Esai</dt>
                                <dd class="font-semibold tabular-nums text-ink" x-text="questions.filter((q) => q.type === 'essay').length"></dd>
                            </div>
                        </dl>

                        <div class="mt-5 border-t border-line pt-4">
                            <x-ui.button type="button" @click="formTab = 'questions'" class="w-full" icon="clipboard">
                                Lanjut Kelola Soal (<span x-text="questions.length"></span> Soal) →
                            </x-ui.button>
                        </div>
                    </x-ui.card>
                </div>
            </div>

            
            
            <div x-show="formTab === 'questions'" x-cloak>
                
                <div class="mb-4 rounded-2xl border border-line bg-surface p-4 shadow-sm" x-show="bankOpen" x-cloak>
                    <div class="flex items-center justify-between pb-3 border-b border-line">
                        <div>
                            <h3 class="text-[14px] font-bold text-ink">Muat dari Bank Soal (163 Soal)</h3>
                            <p class="text-[12px] text-muted">Centang soal yang ingin ditambahkan ke tes ini.</p>
                        </div>
                        <x-ui.button type="button" size="sm" variant="ghost" icon="close" @click="bankOpen = false">Tutup</x-ui.button>
                    </div>

                    <div class="mt-3 grid gap-2 sm:grid-cols-3">
                        <input
                            type="search"
                            x-model="bankQuery"
                            placeholder="Cari teks soal atau dimensi…"
                            class="h-9 w-full rounded-lg border border-line bg-surface px-2.5 text-[12.5px] text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/15"
                        >
                        <select
                            x-model="bankType"
                            class="h-9 w-full rounded-lg border border-line bg-surface px-2.5 text-[12.5px] text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/15"
                        >
                            <option value="">Semua jenis</option>
                            @foreach (\App\Models\AssessmentQuestion::typeOptions() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <select
                            x-model="bankDimension"
                            class="h-9 w-full rounded-lg border border-line bg-surface px-2.5 text-[12.5px] text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/15"
                        >
                            <option value="">Semua dimensi</option>
                            <template x-for="dimension in bankDimensions" :key="dimension">
                                <option :value="dimension" x-text="dimension"></option>
                            </template>
                        </select>
                    </div>

                    <div class="mt-3 max-h-56 space-y-1.5 overflow-y-auto pr-1">
                        <template x-for="item in bankFiltered" :key="item.id">
                            <button
                                type="button"
                                x-on:click="toggleBank(item)"
                                class="flex w-full items-start gap-2.5 rounded-xl border p-2.5 text-left transition"
                                :class="inCurrentTest(item)
                                    ? 'cursor-not-allowed border-line bg-surface-2/60 opacity-60'
                                    : (bankChecked[item.id] ? 'border-brand bg-brand-soft/60' : 'border-line bg-surface hover:bg-surface-2/40')"
                            >
                                <span
                                    class="mt-0.5 flex size-4 shrink-0 items-center justify-center rounded border"
                                    :class="bankChecked[item.id] && !inCurrentTest(item) ? 'border-brand bg-brand text-brand-ink' : 'border-line bg-surface'"
                                >
                                    <x-icon name="check" class="size-3" x-show="bankChecked[item.id] && !inCurrentTest(item)" x-cloak />
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-[12.5px] font-medium" x-text="item.question"></span>
                                    <span class="mt-1 flex items-center gap-1.5 text-[10px]">
                                        <span class="rounded bg-surface-2 px-1.5 py-0.5 font-semibold text-muted" x-text="bankTypeLabel(item.type)"></span>
                                        <span class="rounded bg-surface-2 px-1.5 py-0.5 font-semibold text-muted" x-show="item.dimension" x-text="item.type === 'disc' ? 'Kepribadian DISC' : item.dimension"></span>
                                        <span class="font-semibold text-emerald-600" x-show="inCurrentTest(item)">sudah ada di tes</span>
                                    </span>
                                </span>
                            </button>
                        </template>
                    </div>

                    <div class="mt-3 flex items-center justify-between border-t border-line pt-2.5">
                        <div class="flex items-center gap-2">
                            <button type="button" @click="checkAllBank()" class="text-[11.5px] font-semibold text-brand hover:underline">Pilih semua</button>
                            <span class="text-subtle">·</span>
                            <button type="button" @click="uncheckAllBank()" class="text-[11.5px] font-semibold text-muted hover:underline">Kosongkan</button>
                        </div>
                        <x-ui.button type="button" size="sm" icon="plus" @click="addFromBank()" x-bind:disabled="bankSelectedCount === 0">
                            <span x-text="'Gunakan ' + bankSelectedCount + ' Soal Terpilih'"></span>
                        </x-ui.button>
                    </div>
                </div>

                <div class="grid gap-5 lg:grid-cols-[340px_minmax(0,1fr)] lg:items-start">
                    
                    <div class="rounded-2xl border border-line bg-surface p-4 shadow-card">
                        <div class="flex items-center justify-between pb-3 border-b border-line">
                            <div class="flex items-center gap-1.5">
                                <h3 class="text-[13.5px] font-bold text-ink">Daftar Soal</h3>
                                <span class="rounded-full bg-surface-2 px-2 py-0.5 text-[11px] font-semibold text-muted" x-text="questions.length"></span>
                            </div>
                            <x-ui.button type="button" size="sm" variant="secondary" icon="download" @click="bankOpen = !bankOpen">
                                Bank Soal
                            </x-ui.button>
                        </div>

                        <div class="mt-2.5 flex flex-wrap gap-1 border-b border-line pb-2.5 text-[11px]">
                            <button
                                type="button"
                                @click="questionFilter = 'all'"
                                :class="questionFilter === 'all' ? 'bg-brand text-brand-ink font-bold shadow-sm' : 'bg-surface-2 text-muted hover:text-ink'"
                                class="rounded-md px-2 py-1 transition"
                            >Semua</button>
                            <button
                                type="button"
                                @click="questionFilter = 'disc'"
                                :class="questionFilter === 'disc' ? 'bg-purple-600 text-white font-bold shadow-sm' : 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300'"
                                class="rounded-md px-2 py-1 transition"
                            >DISC</button>
                            <button
                                type="button"
                                @click="questionFilter = 'aptitude'"
                                :class="questionFilter === 'aptitude' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'"
                                class="rounded-md px-2 py-1 transition"
                            >Bakat IQ</button>
                            <button
                                type="button"
                                @click="questionFilter = 'accuracy'"
                                :class="questionFilter === 'accuracy' ? 'bg-emerald-600 text-white font-bold shadow-sm' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'"
                                class="rounded-md px-2 py-1 transition"
                            >Ketelitian</button>
                        </div>

                        <div class="mt-2.5">
                            <input
                                type="search"
                                x-model="questionSearch"
                                placeholder="Cari nomor / teks soal…"
                                class="h-8 w-full rounded-lg border border-line bg-surface-2/40 px-2.5 text-[12px] text-ink outline-none focus:border-brand focus:ring-1 focus:ring-brand"
                            >
                        </div>

                        <div class="mt-3 max-h-[58vh] space-y-1.5 overflow-y-auto pr-1">
                            <template x-for="item in filteredQuestionsWithIndex" :key="item._uid">
                                <div
                                    @click="selectQuestion(item._originalIndex)"
                                    class="group relative flex cursor-pointer items-start gap-2.5 rounded-xl border p-2.5 transition text-left"
                                    :class="activeQuestionIndex === item._originalIndex
                                        ? 'border-brand bg-brand-soft/70 shadow-sm ring-1 ring-brand'
                                        : 'border-line/70 bg-surface hover:border-line hover:bg-surface-2/50'"
                                >
                                    <span
                                        class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-lg text-[11px] font-bold"
                                        :class="activeQuestionIndex === item._originalIndex ? 'bg-brand text-brand-ink' : 'bg-surface-2 text-muted'"
                                        x-text="item._originalIndex + 1"
                                    ></span>

                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-[12px] font-medium text-ink" x-text="item.question.trim() || 'Pertanyaan belum diisi'"></p>
                                        <div class="mt-1 flex items-center gap-1.5 text-[10px]">
                                            <span class="rounded bg-surface-2 px-1 py-0.5 font-semibold text-subtle" x-text="bankTypeLabel(item.type)"></span>
                                            <span class="rounded bg-surface-2 px-1 py-0.5 font-semibold text-subtle" x-show="item.dimension" x-text="item.type === 'disc' ? 'DISC' : item.dimension"></span>
                                            <span class="text-muted" x-show="item.type !== 'disc' && item.type !== 'scale'">
                                                <span x-text="item.points"></span> pt
                                            </span>
                                            <span class="font-bold text-brand-soft-ink" x-show="item.type === 'multiple_choice' && item.correct_option !== null">
                                                Kunci: <span x-text="String.fromCharCode(65 + item.correct_option)"></span>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="opacity-0 group-hover:opacity-100 transition flex items-center gap-0.5 shrink-0" @click.stop>
                                        <button
                                            type="button"
                                            @click="moveQuestion(item._originalIndex, -1)"
                                            class="p-1 text-subtle hover:text-ink"
                                            title="Geser naik"
                                            x-show="item._originalIndex > 0"
                                        >
                                            <x-icon name="chevron-left" class="size-3.5 rotate-90" />
                                        </button>
                                        <button
                                            type="button"
                                            @click="moveQuestion(item._originalIndex, 1)"
                                            class="p-1 text-subtle hover:text-ink"
                                            title="Geser turun"
                                            x-show="item._originalIndex < questions.length - 1"
                                        >
                                            <x-icon name="chevron-right" class="size-3.5 rotate-90" />
                                        </button>
                                        <button
                                            type="button"
                                            @click="remove(item._originalIndex)"
                                            class="p-1 text-rose-500 hover:text-rose-700"
                                            title="Hapus soal"
                                        >
                                            <x-icon name="trash" class="size-3.5" />
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <p class="py-4 text-center text-[12px] text-subtle" x-show="filteredQuestionsWithIndex.length === 0" x-cloak>
                                Tidak ada soal yang cocok dengan filter.
                            </p>
                        </div>

                        <div class="mt-3 border-t border-line pt-3">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-subtle mb-1.5">Tambah Soal Baru:</p>
                            <div class="grid grid-cols-2 gap-1.5">
                                <x-ui.button type="button" size="sm" variant="secondary" icon="plus" @click="addQuestion('multiple_choice')">Pilihan Ganda</x-ui.button>
                                <x-ui.button type="button" size="sm" variant="secondary" icon="plus" @click="addQuestion('disc')">Soal DISC</x-ui.button>
                                <x-ui.button type="button" size="sm" variant="secondary" icon="plus" @click="addQuestion('scale')">Skala / Likert</x-ui.button>
                                <x-ui.button type="button" size="sm" variant="secondary" icon="plus" @click="addQuestion('essay')">Esai</x-ui.button>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-line bg-surface p-5 shadow-card" x-show="currentQuestion" x-cloak>
                        
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line pb-4">
                            <div class="flex items-center gap-2.5">
                                <span class="flex size-8 items-center justify-center rounded-xl bg-brand text-[13px] font-bold text-brand-ink">
                                    <span x-text="activeQuestionIndex + 1"></span>
                                </span>
                                <div>
                                    <h3 class="text-[14.5px] font-bold text-ink">
                                        Mengedit Soal Nomor <span x-text="activeQuestionIndex + 1"></span>
                                    </h3>
                                    <p class="text-[11.5px] text-muted">
                                        Total <span x-text="questions.length"></span> butir soal dalam tes ini
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <x-ui.button type="button" size="sm" variant="secondary" icon="copy" @click="duplicateQuestion(activeQuestionIndex)" title="Duplikasi soal ini">
                                    Duplikasi
                                </x-ui.button>
                                <x-ui.button type="button" size="sm" variant="secondary" icon="trash" @click="remove(activeQuestionIndex)" title="Hapus soal ini">
                                    Hapus
                                </x-ui.button>
                            </div>
                        </div>

                        <div class="mt-4 space-y-4">
                            
                            <div class="grid gap-3 sm:grid-cols-3">
                                <div>
                                    <x-ui.label>Jenis Soal</x-ui.label>
                                    <select
                                        x-model="currentQuestion.type"
                                        x-on:change="switchType(currentQuestion)"
                                        class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[13px] text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/15"
                                    >
                                        <option value="multiple_choice">Pilihan ganda</option>
                                        <option value="disc">DISC (Kepribadian)</option>
                                        <option value="scale">Skala / Psikotes</option>
                                        <option value="essay">Esai</option>
                                    </select>
                                </div>

                                <div x-show="currentQuestion.type !== 'scale' && currentQuestion.type !== 'disc'">
                                    <x-ui.label>Poin Nilai</x-ui.label>
                                    <input
                                        type="number"
                                        min="1"
                                        max="100"
                                        x-model.number="currentQuestion.points"
                                        class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[13px] text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/15"
                                    >
                                </div>

                                <div>
                                    <x-ui.label>Dimensi / Domain</x-ui.label>
                                    <input
                                        type="text"
                                        maxlength="50"
                                        x-model="currentQuestion.dimension"
                                        :placeholder="currentQuestion.type === 'disc' ? 'Urutan trait (S,I,D,C)' : 'Contoh: Verbal, Numerik, Logika, Ketelitian'"
                                        class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[13px] text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/15"
                                    >
                                </div>
                            </div>

                            <div>
                                <x-ui.label>Teks Pertanyaan</x-ui.label>
                                <textarea
                                    rows="3"
                                    x-model="currentQuestion.question"
                                    placeholder="Tuliskan pertanyaan atau pernyataan di sini…"
                                    class="w-full rounded-xl border border-line bg-surface px-3.5 py-2.5 text-[14px] text-ink shadow-sm outline-none transition placeholder:text-subtle focus:border-brand focus:ring-4 focus:ring-brand/15"
                                ></textarea>
                            </div>

                            <div>
                                <x-ui.label>Gambar Pendukung (Opsional)</x-ui.label>
                                <div class="flex flex-wrap items-center gap-2">
                                    <input
                                        type="file"
                                        accept="image/png,image/jpeg,image/webp"
                                        class="h-9 w-full max-w-[240px] cursor-pointer rounded-xl border border-line bg-surface text-[12px] text-muted file:mr-2 file:h-9 file:cursor-pointer file:rounded-l-xl file:border-0 file:bg-surface-2 file:px-3 file:text-[11.5px] file:font-semibold file:text-ink"
                                        x-on:change="pickImage(currentQuestion._uid + ':q', $el.files, (name) => currentQuestion.image_path = name); $el.value = ''"
                                    >
                                    <span class="text-[11.5px] text-subtle" x-show="imageBusy === currentQuestion._uid + ':q'" x-cloak>Mengunggah…</span>
                                    <x-ui.button type="button" variant="ghost" size="sm" x-show="currentQuestion.image_path" x-cloak x-on:click="clearImage(currentQuestion)">
                                        Hapus gambar
                                    </x-ui.button>
                                </div>
                                <div class="mt-2 flex items-center gap-2" x-show="currentQuestion.image_path" x-cloak>
                                    <img :src="imageUrl(currentQuestion.image_path)" alt="Gambar soal" class="h-16 w-16 rounded-lg border border-line bg-surface object-contain">
                                    <p class="text-[11.5px] text-subtle">Gambar tampil di atas teks pertanyaan untuk kandidat.</p>
                                </div>
                            </div>

                            
                            
                            <div class="mt-4 border-t border-line pt-4" x-show="currentQuestion.type === 'multiple_choice'">
                                <div class="flex items-center justify-between mb-2">
                                    <x-ui.label class="font-bold">Pilihan Jawaban (Tandai Radio untuk Kunci Benar):</x-ui.label>
                                    <span class="text-[11.5px] text-subtle">Minimal 2 pilihan</span>
                                </div>

                                <div class="space-y-2">
                                    <template x-for="(option, optionIndex) in currentQuestion.options" :key="optionIndex">
                                        <div class="rounded-xl border border-line bg-surface-2/30 p-2.5 transition" :class="currentQuestion.correct_option === optionIndex ? 'border-brand bg-brand-soft/40' : ''">
                                            <div class="flex items-center gap-2.5">
                                                <input
                                                    type="radio"
                                                    :name="'correct_' + currentQuestion._uid"
                                                    :value="optionIndex"
                                                    :checked="currentQuestion.correct_option === optionIndex"
                                                    @change="currentQuestion.correct_option = optionIndex"
                                                    class="size-4 shrink-0 accent-brand cursor-pointer"
                                                    title="Tandai sebagai kunci jawaban benar"
                                                >
                                                <span
                                                    class="flex size-6 shrink-0 items-center justify-center rounded-lg border text-[11px] font-bold"
                                                    :class="currentQuestion.correct_option === optionIndex ? 'border-brand bg-brand text-brand-ink' : 'border-line bg-surface text-muted'"
                                                    x-text="String.fromCharCode(65 + optionIndex)"
                                                ></span>
                                                <input
                                                    type="text"
                                                    x-model="currentQuestion.options[optionIndex]"
                                                    :placeholder="'Teks pilihan ' + String.fromCharCode(65 + optionIndex)"
                                                    class="h-9 w-full rounded-lg border border-line bg-surface px-3 text-[13.5px] text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/15"
                                                >
                                                <button
                                                    type="button"
                                                    x-on:click="removeOption(currentQuestion, optionIndex)"
                                                    x-show="currentQuestion.options.length > 2"
                                                    class="flex size-8 shrink-0 items-center justify-center rounded-lg border border-line bg-surface text-subtle transition hover:bg-surface-2 hover:text-ink"
                                                    title="Hapus opsi ini"
                                                >
                                                    <x-icon name="close" class="size-3.5" />
                                                </button>
                                            </div>

                                            <div class="mt-2 flex items-center gap-2 pl-9">
                                                <img
                                                    :src="imageUrl(currentQuestion.option_images[optionIndex])"
                                                    alt=""
                                                    class="size-8 rounded border border-line bg-surface object-contain"
                                                    x-show="currentQuestion.option_images[optionIndex]"
                                                    x-cloak
                                                >
                                                <input
                                                    type="file"
                                                    accept="image/png,image/jpeg,image/webp"
                                                    class="h-7 max-w-[200px] text-[11px] text-muted file:h-7 file:border-0 file:rounded-md file:bg-surface-2 file:px-2 file:text-[10.5px] file:font-semibold"
                                                    x-on:change="pickImage(currentQuestion._uid + ':o' + optionIndex, $el.files, (name) => currentQuestion.option_images[optionIndex] = name); $el.value = ''"
                                                >
                                                <button type="button" x-show="currentQuestion.option_images[optionIndex]" x-cloak class="text-[11px] text-rose-500 hover:underline" x-on:click="clearImage(currentQuestion, optionIndex)">Hapus</button>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <div class="mt-3 flex items-center justify-between">
                                    <x-ui.button type="button" size="sm" variant="ghost" icon="plus" @click="addOption(currentQuestion)" x-show="currentQuestion.options.length < 6">
                                        Tambah Pilihan
                                    </x-ui.button>
                                    <span class="text-[11.5px] text-subtle">
                                        Kunci saat ini: <strong class="text-brand-soft-ink" x-text="currentQuestion.correct_option !== null ? String.fromCharCode(65 + currentQuestion.correct_option) : 'Belum ditandai'"></strong>
                                    </span>
                                </div>
                            </div>

                            
                            
                            <div class="mt-4 border-t border-line pt-4" x-show="currentQuestion.type === 'disc'">
                                <div class="rounded-xl border border-purple-200 bg-purple-50/50 p-3 text-[12px] text-purple-900 dark:border-purple-800 dark:bg-purple-950/20 dark:text-purple-300 mb-3">
                                    <p class="font-bold flex items-center gap-1.5">
                                        <x-icon name="sparkles" class="size-4" /> Format Baku Soal DISC:
                                    </p>
                                    <p class="mt-1">
                                        Setiap nomor memuat 4 pernyataan karakter. Kandidat memilih 1 Paling Sesuai [P] dan 1 Paling Tidak Sesuai [K]. Poin otomatis 0.
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    @php $discDefaults = ['S', 'I', 'D', 'C']; @endphp
                                    <template x-for="(option, optionIndex) in currentQuestion.options" :key="optionIndex">
                                        <div class="flex items-center gap-2.5 rounded-xl border border-line bg-surface p-2.5">
                                            <span class="flex size-7 shrink-0 items-center justify-center rounded-lg border border-purple-200 bg-purple-50 text-[11px] font-bold text-purple-700 dark:border-purple-800 dark:bg-purple-900/40 dark:text-purple-300" x-text="String.fromCharCode(65 + optionIndex)"></span>
                                            <input
                                                type="text"
                                                x-model="currentQuestion.options[optionIndex]"
                                                :placeholder="'Pernyataan Karakter ' + String.fromCharCode(65 + optionIndex)"
                                                class="h-9 w-full rounded-lg border border-line bg-surface px-3 text-[13.5px] text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/15"
                                            >
                                            <span class="shrink-0 rounded-lg border border-line bg-surface-2 px-2 py-1 text-[11px] font-bold text-muted">
                                                Trait: <span x-text="(currentQuestion.dimension || 'S,I,D,C').split(',')[optionIndex] || '—'"></span>
                                            </span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            
                            
                            <div class="mt-4 border-t border-line pt-4" x-show="currentQuestion.type === 'essay'">
                                <div class="rounded-xl border border-blue-200 bg-blue-50/50 p-4 text-[12.5px] text-blue-900 dark:border-blue-800 dark:bg-blue-950/20 dark:text-blue-300">
                                    <p class="font-bold flex items-center gap-1.5">
                                        <x-icon name="clipboard" class="size-4" /> Soal Esai (Penilaian Manual):
                                    </p>
                                    <p class="mt-1 leading-relaxed">
                                        Kandidat akan mengetikkan jawaban bebas dalam bentuk paragraf teks. Setelah tes selesai, tim HRD memberikan skor manual pada halaman evaluasi.
                                    </p>
                                </div>
                            </div>

                            
                            
                            <div class="mt-4 border-t border-line pt-4" x-show="currentQuestion.type === 'scale'">
                                <div class="rounded-xl border border-amber-200 bg-amber-50/50 p-4 text-[12.5px] text-amber-900 dark:border-amber-800 dark:bg-amber-950/20 dark:text-amber-300">
                                    <p class="font-bold flex items-center gap-1.5">
                                        <x-icon name="clipboard" class="size-4" /> Skala / Psikotes:
                                    </p>
                                    <p class="mt-1 leading-relaxed">
                                        Kandidat memilih skala kecocokan pernyataan. Skor dihitung dan dikelompokkan berdasarkan nama dimensi yang Anda tetapkan.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-line pt-4">
                            <div class="flex items-center gap-2">
                                <x-ui.button
                                    type="button"
                                    size="sm"
                                    variant="secondary"
                                    icon="chevron-left"
                                    @click="prevQuestion()"
                                    x-bind:disabled="activeQuestionIndex <= 0"
                                >
                                    Sebelumnya
                                </x-ui.button>
                                <x-ui.button
                                    type="button"
                                    size="sm"
                                    variant="secondary"
                                    @click="nextQuestion()"
                                    x-bind:disabled="activeQuestionIndex >= questions.length - 1"
                                >
                                    Selanjutnya
                                    <x-icon name="chevron-right" class="size-3.5 ml-1" />
                                </x-ui.button>
                            </div>

                            <x-ui.button
                                type="button"
                                size="sm"
                                variant="secondary"
                                icon="plus"
                                @click="addQuestion(currentQuestion.type)"
                            >
                                Tambah Soal Setelah Ini
                            </x-ui.button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>
