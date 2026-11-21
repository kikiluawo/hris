<x-layouts.test :title="'Mengerjakan · ' . $assessment->title">
    <div
        class="min-h-dvh select-none bg-canvas pb-24 lg:pb-8"
        oncontextmenu="return false;"
        x-data="assessmentTest({{ Illuminate\Support\Js::from([
            'questions' => $questions,
            'remaining' => $remaining,
            'answerUrl' => route('assessment.answer', $attempt->token),
            'violationUrl' => route('assessment.violation', $attempt->token),
            'snapshotUrl' => route('assessment.snapshot', $attempt->token),
            'statusUrl' => route('assessment.status', $attempt->token),
            'submitUrl' => route('assessment.submit', $attempt->token),
            'finishedUrl' => route('assessment.finished', $attempt->token),
            'accessToken' => $attempt->access_token,
        ]) }})"
    >
        <header class="sticky top-0 z-30 border-b border-line bg-surface/95 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-6xl items-center gap-3 px-4 sm:px-6">
                <div class="flex min-w-0 items-center gap-2.5">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-brand text-brand-ink shadow-sm">
                        <x-icon name="clipboard" class="size-[18px]" />
                    </span>
                    <div class="min-w-0">
                        <p class="truncate text-[13px] font-semibold leading-tight">{{ $assessment->title }}</p>
                        <p class="truncate text-[11px] text-subtle">{{ $attempt->candidateName() }} · {{ $attempt->candidateEmail() }}</p>
                    </div>
                </div>

                <div class="ml-auto flex shrink-0 items-center gap-2">
                    <span class="hidden items-center gap-1.5 text-[11px] text-subtle sm:flex">
                        <span class="size-1.5 rounded-full" :class="saveState === 'error' ? 'bg-rose-500' : (saveState === 'saving' ? 'bg-amber-500' : 'bg-emerald-500')"></span>
                        <span x-text="saveLabel"></span>
                    </span>

                    <div
                        class="flex items-center gap-1.5 rounded-xl border px-2.5 py-2 transition-colors sm:gap-2 sm:px-3"
                        :class="remaining <= 60 ? 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-400/25 dark:bg-rose-400/10 dark:text-rose-300' : 'border-line bg-surface-2 text-ink'"
                    >
                        <x-icon name="clock" class="size-4" />
                        <span class="text-[12.5px] font-bold tabular-nums sm:text-[13.5px]" x-text="timeLabel"></span>
                    </div>
                </div>
            </div>

            <div class="h-1 bg-surface-2">
                <div class="h-1 bg-brand transition-[width] duration-300" :style="'width: ' + progress + '%'"></div>
            </div>
        </header>

        <main class="mx-auto grid w-full max-w-6xl gap-4 px-4 py-5 sm:px-6 lg:grid-cols-[248px_minmax(0,1fr)] lg:gap-6 lg:py-8">
            <aside class="hidden lg:block">
                <div class="sticky top-24 space-y-4">
                    <div class="rounded-2xl border border-line bg-surface p-4 shadow-card">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-subtle">Daftar soal</p>
                            <span class="text-[11px] tabular-nums text-subtle"><span x-text="answeredCount"></span>/<span x-text="total"></span></span>
                        </div>
                        <div class="mt-3 grid grid-cols-5 gap-2">
                            <template x-for="(question, index) in questions" :key="question.id">
                                <button
                                    type="button"
                                    x-on:click="jump(index)"
                                    class="flex aspect-square items-center justify-center rounded-lg border text-[12px] font-semibold transition"
                                    :class="current === index
                                        ? 'border-brand bg-brand text-brand-ink shadow-sm'
                                        : (answered(question)
                                            ? 'border-brand-line bg-brand-soft text-brand-soft-ink hover:bg-brand-soft/70'
                                            : 'border-line bg-surface text-muted hover:bg-surface-2')"
                                    x-text="index + 1"
                                ></button>
                            </template>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-3 text-[10.5px] text-subtle">
                            <span class="flex items-center gap-1.5"><span class="size-2.5 rounded border border-brand-line bg-brand-soft"></span> Terjawab</span>
                            <span class="flex items-center gap-1.5"><span class="size-2.5 rounded border border-line bg-surface"></span> Belum</span>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-line bg-surface p-4 shadow-card">
                        <p class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.1em] text-subtle">
                            <x-icon name="camera" class="size-3.5" /> Pengawasan kamera
                        </p>
                        <p class="mt-2 text-[11.5px] leading-relaxed text-subtle">Kamera Anda tampil di pojok kiri bawah. Biarkan tetap aktif dan jangan berpindah tab atau aplikasi.</p>
                    </div>

                    <button
                        type="button"
                        x-on:click="openSubmit()"
                        class="flex h-10 w-full items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50 text-[12.5px] font-semibold text-rose-700 transition hover:bg-rose-100 dark:border-rose-400/25 dark:bg-rose-400/10 dark:text-rose-300"
                    >
                        <x-icon name="check" class="size-4" /> Akhiri & kirim jawaban
                    </button>
                </div>
            </aside>

            <section class="min-w-0">
                <div class="rounded-2xl border border-line bg-surface p-5 shadow-card sm:p-6">
                    <template x-for="(question, index) in questions" :key="question.id">
                        <div x-show="current === index" x-cloak>
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-subtle">
                                    Soal <span x-text="index + 1"></span> dari <span x-text="total"></span>
                                </p>
                                <div class="flex items-center gap-1.5">
                                    <span class="rounded-full border border-line bg-surface-2 px-2.5 py-0.5 text-[10.5px] font-semibold text-muted" x-show="question.dimension && question.type !== 'disc'" x-cloak x-text="question.dimension"></span>
                                    <span class="rounded-full border border-line bg-surface-2 px-2.5 py-0.5 text-[10.5px] font-semibold text-muted" x-text="question.type === 'essay' ? 'Esai' : (question.type === 'scale' ? 'Skala' : (question.type === 'disc' ? 'DISC Kepribadian' : 'Pilihan ganda'))"></span>
                                    <span class="rounded-full border border-line bg-surface-2 px-2.5 py-0.5 text-[10.5px] font-semibold text-muted" x-show="question.type !== 'scale' && question.type !== 'disc'" x-cloak><span x-text="question.points"></span> poin</span>
                                </div>
                            </div>

                            <h2 class="mt-4 whitespace-pre-line text-[18px] font-semibold leading-relaxed sm:text-[20px]" x-text="question.question"></h2>

                            <button
                                type="button"
                                x-show="question.image"
                                x-cloak
                                x-on:click="zoom(question.image)"
                                class="mt-4 block w-full rounded-xl border border-line bg-surface-2/40 p-2 transition hover:border-line-strong"
                                title="Ketuk untuk memperbesar"
                            >
                                <img :src="question.image" alt="Gambar soal" class="mx-auto max-h-52 w-auto rounded-lg object-contain sm:max-h-72">
                            </button>

                            <div class="mt-5 space-y-2.5" x-show="question.type !== 'essay' && question.type !== 'disc'">
                                <template x-for="(option, optionIndex) in question.options" :key="optionIndex">
                                    <label
                                        class="flex cursor-pointer items-start gap-3 rounded-xl border p-3.5 transition"
                                        :class="answers[question.id].selected_option === optionIndex
                                            ? 'border-brand bg-brand-soft/70 ring-2 ring-brand/15'
                                            : 'border-line bg-surface hover:border-line-strong hover:bg-surface-2/60'"
                                    >
                                        <input
                                            type="radio"
                                            class="sr-only"
                                            :name="'question_' + question.id"
                                            :value="optionIndex"
                                            x-model.number="answers[question.id].selected_option"
                                            x-on:change="save(question)"
                                        >
                                        <span
                                            class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full border text-[12px] font-bold transition"
                                            :class="answers[question.id].selected_option === optionIndex
                                                ? 'border-brand bg-brand text-brand-ink'
                                                : 'border-line bg-surface text-muted'"
                                            x-text="String.fromCharCode(65 + optionIndex)"
                                        ></span>
                                        <span class="min-w-0 flex-1 space-y-2 pt-0.5">
                                            <span
                                                x-show="optionImage(question, optionIndex)"
                                                x-cloak
                                                class="relative block w-fit rounded-lg border border-line bg-surface-2/40 p-1.5"
                                            >
                                                <img :src="optionImage(question, optionIndex)" :alt="'Gambar pilihan ' + String.fromCharCode(65 + optionIndex)" class="max-h-32 w-auto rounded object-contain sm:max-h-40">
                                                <button
                                                    type="button"
                                                    x-on:click.prevent.stop="zoom(optionImage(question, optionIndex))"
                                                    class="absolute right-1.5 top-1.5 flex size-8 cursor-pointer items-center justify-center rounded-md border border-line bg-surface/95 text-muted shadow-sm transition hover:bg-surface hover:text-ink"
                                                    title="Perbesar gambar"
                                                    aria-label="Perbesar gambar pilihan"
                                                >
                                                    <x-icon name="search" class="size-4" />
                                                </button>
                                            </span>
                                            <span class="block text-[14px] leading-relaxed" x-show="optionText(question, optionIndex)" x-text="optionText(question, optionIndex)"></span>
                                        </span>
                                    </label>
                                </template>
                            </div>

                            <div class="mt-5 space-y-3" x-show="question.type === 'disc'" x-cloak>
                                <div class="rounded-xl border border-brand/20 bg-brand-soft/30 p-3 text-[12.5px] text-brand-soft-ink">
                                    <p class="font-semibold flex items-center gap-1.5">
                                        <x-icon name="alert-circle" class="size-4 shrink-0" /> Petunjuk Pengerjaan DISC:
                                    </p>
                                    <p class="mt-1 leading-relaxed">
                                        Pilih <strong>1 pernyataan yang PALING (P)</strong> menggambarkan diri Anda, dan <strong>1 pernyataan yang PALING TIDAK / KURANG (K)</strong> menggambarkan diri Anda. Pilihan tidak boleh berada pada baris pernyataan yang sama.
                                    </p>
                                </div>

                                <div class="overflow-hidden rounded-2xl border border-line bg-surface shadow-sm">
                                    <div class="grid grid-cols-[1fr_75px_75px] sm:grid-cols-[1fr_85px_85px] items-center border-b border-line bg-surface-2/70 px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider text-muted">
                                        <span>Pernyataan</span>
                                        <span class="text-center text-emerald-700 dark:text-emerald-400">Paling (P)</span>
                                        <span class="text-center text-rose-700 dark:text-rose-400">Kurang (K)</span>
                                    </div>

                                    <div class="divide-y divide-line">
                                        <template x-for="(option, optionIndex) in question.options" :key="optionIndex">
                                            <div
                                                class="grid grid-cols-[1fr_75px_75px] sm:grid-cols-[1fr_85px_85px] items-center px-4 py-3 transition hover:bg-surface-2/40"
                                                :class="(answers[question.id].most_option === optionIndex || answers[question.id].least_option === optionIndex) ? 'bg-brand-soft/20' : ''"
                                            >
                                                <div class="pr-3 text-[13.5px] sm:text-[14.5px] font-medium leading-relaxed text-ink" x-text="optionText(question, optionIndex)"></div>
                                                
                                                <div class="flex justify-center">
                                                    <button
                                                        type="button"
                                                        x-on:click="selectDisc(question, 'most', optionIndex)"
                                                        class="flex size-9 items-center justify-center rounded-xl border text-[12px] font-bold transition"
                                                        :class="answers[question.id].most_option === optionIndex
                                                            ? 'border-emerald-500 bg-emerald-600 text-white ring-2 ring-emerald-500/25 shadow-sm'
                                                            : 'border-line bg-surface text-muted hover:border-emerald-400 hover:text-emerald-700'"
                                                        title="Paling menggambarkan diri (Most)"
                                                    >
                                                        <span x-text="answers[question.id].most_option === optionIndex ? '✓ P' : 'P'"></span>
                                                    </button>
                                                </div>

                                                <div class="flex justify-center">
                                                    <button
                                                        type="button"
                                                        x-on:click="selectDisc(question, 'least', optionIndex)"
                                                        class="flex size-9 items-center justify-center rounded-xl border text-[12px] font-bold transition"
                                                        :class="answers[question.id].least_option === optionIndex
                                                            ? 'border-rose-500 bg-rose-600 text-white ring-2 ring-rose-500/25 shadow-sm'
                                                            : 'border-line bg-surface text-muted hover:border-rose-400 hover:text-rose-700'"
                                                        title="Paling tidak menggambarkan diri (Least)"
                                                    >
                                                        <span x-text="answers[question.id].least_option === optionIndex ? '✓ K' : 'K'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5" x-show="question.type === 'essay'" x-cloak>
                                <textarea
                                    rows="8"
                                    maxlength="20000"
                                    x-model="answers[question.id].answer"
                                    x-on:input.debounce.800ms="save(question)"
                                    x-on:blur="save(question)"
                                    placeholder="Tulis jawaban Anda di sini…"
                                    class="min-h-[210px] w-full rounded-xl border border-line bg-surface px-4 py-3 text-[15px] leading-relaxed text-ink shadow-sm outline-none transition placeholder:text-subtle focus:border-brand focus:ring-4 focus:ring-brand/15"
                                ></textarea>
                                <p class="mt-1.5 text-right text-[11px] text-subtle">
                                    <span x-text="(answers[question.id].answer || '').length"></span>/20000 karakter
                                </p>
                            </div>
                        </div>
                    </template>

                    <div class="mt-6 flex items-center justify-between gap-3 border-t border-line pt-4">
                        <button
                            type="button"
                            x-on:click="prev()"
                            x-bind:disabled="current === 0"
                            class="flex h-10 items-center gap-1.5 rounded-xl border border-line bg-surface px-3.5 text-[13px] font-semibold text-muted transition hover:bg-surface-2 disabled:pointer-events-none disabled:opacity-40"
                        >
                            <x-icon name="chevron-left" class="size-4" /> Sebelumnya
                        </button>

                        <span class="hidden text-[11px] text-subtle sm:block" x-text="saveLabel"></span>

                        <button
                            type="button"
                            x-on:click="next()"
                            class="flex h-10 items-center gap-1.5 rounded-xl bg-brand px-4 text-[13px] font-semibold text-brand-ink shadow-sm transition hover:bg-brand-hover"
                        >
                            <span x-text="current === total - 1 ? 'Selesai' : 'Selanjutnya'"></span>
                            <x-icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                </div>

                <p class="mt-3 px-1 text-center text-[11px] leading-relaxed text-subtle lg:text-left">
                    Jangan menutup, memuat ulang, atau berpindah tab. Pelanggaran pertama diberi peringatan; pelanggaran berikutnya membuat tes berakhir.
                </p>
            </section>
        </main>

        <div class="fixed inset-x-0 bottom-0 z-30 border-t border-line bg-surface/95 backdrop-blur lg:hidden">
            <div class="flex items-center gap-2 px-4 py-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
                <button
                    type="button"
                    x-on:click="prev()"
                    x-bind:disabled="current === 0"
                    class="flex size-10 shrink-0 items-center justify-center rounded-xl border border-line bg-surface text-muted transition disabled:pointer-events-none disabled:opacity-40"
                    aria-label="Soal sebelumnya"
                >
                    <x-icon name="chevron-left" class="size-4" />
                </button>

                <button
                    type="button"
                    x-on:click="paletteOpen = true"
                    class="flex h-10 flex-1 items-center justify-center gap-2 rounded-xl border border-line bg-surface-2 text-[12.5px] font-semibold text-ink"
                >
                    <x-icon name="grid" class="size-4 text-subtle" />
                    Soal <span class="tabular-nums" x-text="current + 1"></span>/<span class="tabular-nums" x-text="total"></span>
                </button>

                <button
                    type="button"
                    x-on:click="next()"
                    class="flex h-10 shrink-0 items-center gap-1.5 rounded-xl bg-brand px-3.5 text-[12.5px] font-semibold text-brand-ink shadow-sm"
                >
                    <span x-text="current === total - 1 ? 'Selesai' : 'Lanjut'"></span>
                    <x-icon name="chevron-right" class="size-4" />
                </button>
            </div>
        </div>

        <div class="fixed bottom-24 left-3 z-20 w-20 overflow-hidden rounded-xl border border-line bg-black shadow-pop lg:bottom-5 lg:left-5 lg:w-36" x-show="cameraReady" x-cloak>
            <video x-ref="camera" autoplay muted playsinline class="aspect-[4/3] w-full object-cover"></video>
            <p class="flex items-center gap-1 bg-surface px-2 py-1 text-[9.5px] font-semibold text-emerald-700 dark:text-emerald-400 lg:text-[10.5px]">
                <span class="size-1.5 rounded-full bg-emerald-500"></span> Kamera aktif
            </p>
        </div>

        <div x-show="zoomImage" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm" x-on:click.self="zoomImage = null">
            <div class="max-h-full w-full max-w-3xl overflow-auto rounded-2xl border border-line bg-surface p-4 shadow-pop">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-[12.5px] font-semibold text-muted">Gambar diperbesar</p>
                    <button type="button" x-on:click="zoomImage = null" class="flex size-9 items-center justify-center rounded-lg border border-line bg-surface text-muted" aria-label="Tutup gambar">
                        <x-icon name="close" class="size-4" />
                    </button>
                </div>
                <img :src="zoomImage" alt="Gambar diperbesar" class="mx-auto mt-3 max-h-[70vh] w-auto rounded-xl bg-surface-2/40 object-contain">
            </div>
        </div>

        <div x-show="paletteOpen" x-cloak class="fixed inset-0 z-40 flex items-end bg-black/50 backdrop-blur-sm lg:hidden" x-on:click.self="paletteOpen = false">
            <div class="max-h-[80vh] w-full overflow-y-auto rounded-t-3xl border-t border-line bg-surface p-5 pb-[calc(1.25rem+env(safe-area-inset-bottom))]">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[14px] font-semibold">Daftar soal</p>
                        <p class="text-[11.5px] text-subtle"><span x-text="answeredCount"></span> terjawab · <span x-text="unansweredCount"></span> belum</p>
                    </div>
                    <button type="button" x-on:click="paletteOpen = false" class="flex size-9 items-center justify-center rounded-lg border border-line bg-surface text-muted" aria-label="Tutup daftar soal">
                        <x-icon name="close" class="size-4" />
                    </button>
                </div>

                <div class="mt-4 grid grid-cols-6 gap-2 sm:grid-cols-8">
                    <template x-for="(question, index) in questions" :key="'mobile-' + question.id">
                        <button
                            type="button"
                            x-on:click="jump(index)"
                            class="flex aspect-square items-center justify-center rounded-lg border text-[13px] font-semibold transition"
                            :class="current === index
                                ? 'border-brand bg-brand text-brand-ink'
                                : (answered(question)
                                    ? 'border-brand-line bg-brand-soft text-brand-soft-ink'
                                    : 'border-line bg-surface text-muted')"
                            x-text="index + 1"
                        ></button>
                    </template>
                </div>

                <button type="button" x-on:click="paletteOpen = false; openSubmit()" class="mt-5 flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-brand text-[13px] font-semibold text-brand-ink">
                    <x-icon name="check" class="size-4" /> Akhiri & kirim jawaban
                </button>
            </div>
        </div>

        <div x-show="warning !== null" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-2xl border border-amber-300 bg-surface p-6 shadow-pop dark:border-amber-400/30">
                <div class="flex size-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-700 dark:bg-amber-400/15 dark:text-amber-300">
                    <x-icon name="alert-circle" class="size-7" />
                </div>
                <h2 class="mt-4 text-[19px] font-semibold">Peringatan <span x-text="warning"></span> dari 2</h2>
                <p class="mt-2 text-[13px] leading-relaxed text-muted">
                    Anda terdeteksi meninggalkan halaman tes. Jika Anda berpindah tab atau aplikasi lagi, tes akan <span class="font-semibold text-ink">langsung berakhir</span> dan tidak bisa dilanjutkan.
                </p>
                <p class="mt-2 text-[12px] leading-relaxed text-subtle">Tetap di halaman ini dan pastikan kamera menyala sampai Anda mengirim jawaban.</p>
                <button type="button" x-on:click="acknowledge()" class="mt-5 flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-brand text-[13.5px] font-semibold text-brand-ink">
                    <x-icon name="check" class="size-4" /> Saya mengerti, lanjutkan
                </button>
            </div>
        </div>

        <div x-show="confirmSubmit" x-cloak class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 p-4 backdrop-blur-sm sm:items-center" x-on:click.self="confirmSubmit = false">
            <div class="w-full max-w-md rounded-2xl border border-line bg-surface p-6 shadow-pop">
                <h2 class="text-[19px] font-semibold">Kirim jawaban sekarang?</h2>
                <p class="mt-2 text-[13px] leading-relaxed text-muted">
                    Anda sudah menjawab <span class="font-semibold text-ink" x-text="answeredCount"></span> dari <span x-text="total"></span> soal.
                    Setelah dikirim, jawaban tidak bisa diubah.
                </p>
                <p class="mt-2 rounded-xl border border-amber-200 bg-amber-50 px-3.5 py-2.5 text-[12px] leading-relaxed text-amber-800 dark:border-amber-400/25 dark:bg-amber-400/10 dark:text-amber-300" x-show="unansweredCount > 0" x-cloak>
                    Masih ada <span class="font-semibold" x-text="unansweredCount"></span> soal yang belum dijawab.
                </p>
                <div class="mt-5 grid gap-2 sm:grid-cols-2">
                    <button type="button" x-on:click="confirmSubmit = false" class="flex h-10 items-center justify-center rounded-xl border border-line bg-surface text-[13px] font-semibold text-muted transition hover:bg-surface-2">
                        Periksa lagi
                    </button>
                    <button type="button" x-on:click="submit('submitted')" x-bind:disabled="submitting" class="flex h-10 items-center justify-center gap-2 rounded-xl bg-brand text-[13px] font-semibold text-brand-ink shadow-sm transition hover:bg-brand-hover disabled:opacity-60">
                        <span x-text="submitting ? 'Mengirim…' : 'Kirim jawaban'"></span>
                    </button>
                </div>
            </div>
        </div>

        <div x-show="copyAlert" x-cloak x-transition class="fixed top-20 left-1/2 z-50 -translate-x-1/2 rounded-xl border border-rose-300 bg-rose-600 px-4 py-2.5 text-center text-[12.5px] font-semibold text-white shadow-pop">
            <p class="flex items-center gap-2">
                <x-icon name="alert-circle" class="size-4 shrink-0" />
                Menyalin atau memindahkan teks soal tidak diperbolehkan selama tes!
            </p>
        </div>

        <div x-show="cameraReady && !isFullscreen && !ended" x-cloak class="fixed inset-0 z-40 flex items-center justify-center bg-canvas/95 p-4 backdrop-blur-sm">
            <div class="w-full max-w-sm rounded-2xl border border-line bg-surface p-6 text-center shadow-pop">
                <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-amber-500/15 text-amber-600 dark:text-amber-400">
                    <x-icon name="maximize" class="size-7" />
                </div>
                <h2 class="mt-4 text-[18px] font-semibold">Mode Layar Penuh Diperlukan</h2>
                <p class="mt-2 text-[12.5px] leading-relaxed text-muted">
                    Tes ini wajib dikerjakan dalam mode layar penuh (fullscreen). Keluar dari mode ini dicatat sebagai pelanggaran aturan.
                </p>
                <div class="mt-5">
                    <button type="button" x-on:click="requestFullscreen()" class="flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-brand text-[13px] font-semibold text-brand-ink transition hover:opacity-90">
                        <x-icon name="maximize" class="size-4" /> Masuk Mode Layar Penuh
                    </button>
                </div>
            </div>
        </div>

        <div x-show="!cameraReady" x-cloak class="fixed inset-0 z-40 flex items-center justify-center bg-canvas/95 p-4 backdrop-blur-sm">
            <div class="w-full max-w-sm rounded-2xl border border-line bg-surface p-6 text-center shadow-pop">
                <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-brand-soft text-brand-soft-ink">
                    <x-icon name="camera" class="size-7" />
                </div>
                <h2 class="mt-4 text-[18px] font-semibold" x-text="cameraError ? 'Kamera tidak aktif' : 'Mengaktifkan kamera…'"></h2>
                <p class="mt-2 text-[12.5px] leading-relaxed text-muted" x-text="cameraError || 'Izinkan akses kamera saat browser meminta. Kamera wajib menyala selama tes.'"></p>
                <div class="mt-5 grid gap-2" x-show="cameraError" x-cloak>
                    <button type="button" x-on:click="retryCamera()" class="flex h-10 items-center justify-center gap-2 rounded-xl bg-brand text-[13px] font-semibold text-brand-ink">
                        <x-icon name="refresh" class="size-4" /> Coba aktifkan lagi
                    </button>
                    <button type="button" x-on:click="window.location.reload()" class="flex h-10 items-center justify-center rounded-xl border border-line bg-surface text-[13px] font-semibold text-muted">
                        Muat ulang halaman
                    </button>
                </div>
                <p class="mt-4 text-[11px] text-subtle">Waktu tes tetap berjalan selama kamera belum aktif.</p>
            </div>
        </div>

        <div x-show="ended" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center bg-[#0c1e38]/95 p-4">
            <div class="w-full max-w-md rounded-2xl border border-white/12 bg-[#081528] p-6 text-center shadow-pop">
                <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-rose-400/15 text-rose-300">
                    <x-icon name="x-circle" class="size-7" />
                </div>
                <h2 class="mt-4 text-[20px] font-semibold text-white">Tes berakhir</h2>
                <p class="mt-2 text-[13px] leading-relaxed text-slate-200/80" x-text="endedMessage"></p>
                <button type="button" x-on:click="leave()" class="mt-5 flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-gold text-[13.5px] font-bold text-[#1d150a]">
                    Kembali ke halaman tes
                </button>
            </div>
        </div>
    </div>
</x-layouts.test>
