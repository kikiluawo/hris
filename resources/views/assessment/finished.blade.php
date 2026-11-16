<x-layouts.test :title="'Hasil tes · ' . $assessment->title">
    <div class="relative min-h-dvh overflow-hidden bg-[#06231b] text-emerald-50">
        <div class="pattern-grid absolute inset-0 opacity-40"></div>
        <div class="pointer-events-none absolute -left-32 top-10 size-[420px] rounded-full bg-emerald-400/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-24 bottom-0 size-[380px] rounded-full bg-gold/10 blur-3xl"></div>

        <div class="relative mx-auto flex min-h-dvh w-full max-w-2xl flex-col px-5 py-6 sm:px-8">
            <header class="flex items-center gap-3">
                <span class="flex size-9 items-center justify-center rounded-xl bg-gold text-[#1d150a] shadow-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="size-5" aria-hidden="true">
                        <path d="M8 6v12M16 6v12M8 12h8" />
                    </svg>
                </span>
                <span class="leading-tight">
                    <span class="block text-[15px] font-bold tracking-tight text-white">HRD System</span>
                    <span class="block text-[10px] font-semibold uppercase tracking-[0.14em] text-emerald-100/60">Tes online</span>
                </span>
            </header>

            <main class="flex flex-1 items-center py-10">
                <div class="w-full rounded-3xl border border-white/12 bg-white/[0.06] p-6 shadow-[0_30px_80px_-30px_rgba(0,0,0,0.7)] backdrop-blur sm:p-8">
                    @if (session('info'))
                        <div class="mb-5 rounded-xl border border-gold/30 bg-gold/10 px-4 py-3 text-[13px] leading-relaxed text-gold-soft">{{ session('info') }}</div>
                    @endif

                    @if ($expired)
                        <div class="flex size-12 items-center justify-center rounded-2xl bg-rose-400/15 text-rose-300">
                            <x-icon name="clock" class="size-7" />
                        </div>
                        <h1 class="mt-4 font-display text-[26px] font-semibold text-white">Tautan sudah kedaluwarsa</h1>
                        <p class="mt-2 text-[13.5px] leading-relaxed text-emerald-50/70">
                            Masa akses tes ini sudah berakhir. Jawaban yang sempat tersimpan tetap diterima HRD.
                            Hubungi HRD Majesty Group bila Anda memerlukan keterangan lebih lanjut.
                        </p>
                    @elseif ($attempt->status === 'submitted')
                        <div class="flex size-12 items-center justify-center rounded-2xl bg-emerald-400/15 text-emerald-300">
                            <x-icon name="check-circle" class="size-7" />
                        </div>
                        <h1 class="mt-4 font-display text-[26px] font-semibold text-white">Tes sudah selesai</h1>
                        <p class="mt-2 text-[13.5px] leading-relaxed text-emerald-50/70">
                            Terima kasih, <span class="font-semibold text-emerald-50">{{ $attempt->candidateName() }}</span>.
                            Jawaban Anda sudah diterima HRD pada
                            <span class="font-semibold text-emerald-50">{{ $attempt->submitted_at?->translatedFormat('d F Y, H:i') }}</span>.
                        </p>
                        <div class="mt-5 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-[12.5px] leading-relaxed text-emerald-50/60">
                            Hasil tes dinilai oleh HRD dan akan diinformasikan lewat kontak yang Anda daftarkan. Simpan halaman ini sebagai bukti pengiriman.
                        </div>
                    @elseif ($attempt->status === 'ended')
                        <div class="flex size-12 items-center justify-center rounded-2xl bg-rose-400/15 text-rose-300">
                            <x-icon name="x-circle" class="size-7" />
                        </div>
                        <h1 class="mt-4 font-display text-[26px] font-semibold text-white">Tes dihentikan</h1>
                        <p class="mt-2 text-[13.5px] leading-relaxed text-emerald-50/70">
                            Tes ini berakhir karena <span class="font-semibold text-rose-200">{{ strtolower($attempt->endReasonLabel() ?? 'aturan tes dilanggar') }}</span>.
                            Jawaban yang sudah tersimpan tetap diterima HRD.
                        </p>
                        <p class="mt-3 text-[12.5px] text-emerald-50/50">Jika menurut Anda ini keliru, hubungi HRD Majesty Group.</p>
                    @else
                        <div class="flex size-12 items-center justify-center rounded-2xl bg-gold/15 text-gold">
                            <x-icon name="clock" class="size-7" />
                        </div>
                        <h1 class="mt-4 font-display text-[26px] font-semibold text-white">Tes masih berjalan</h1>
                        <p class="mt-2 text-[13.5px] leading-relaxed text-emerald-50/70">
                            Waktu Anda belum habis. Lanjutkan mengerjakan sebelum waktu berakhir.
                        </p>
                        <x-ui.button :href="route('assessment.take', $attempt->token)" variant="gold" size="lg" icon="play" class="mt-5 w-full">Lanjutkan tes</x-ui.button>
                    @endif

                    <dl class="mt-6 space-y-2.5 border-t border-white/10 pt-5 text-[13px]">
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-emerald-50/50">Tes</dt>
                            <dd class="text-right font-medium text-emerald-50">{{ $assessment->title }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-emerald-50/50">Nama</dt>
                            <dd class="text-right font-medium text-emerald-50">{{ $attempt->candidateName() }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-emerald-50/50">Email</dt>
                            <dd class="text-right font-medium text-emerald-50">{{ $attempt->candidateEmail() }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-emerald-50/50">Mulai</dt>
                            <dd class="text-right font-medium text-emerald-50">{{ $attempt->started_at->translatedFormat('d M Y, H:i') }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-emerald-50/50">Batas waktu</dt>
                            <dd class="text-right font-medium text-emerald-50">{{ $attempt->ends_at->translatedFormat('d M Y, H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </main>

            <footer class="border-t border-white/10 py-5 text-[11px] text-emerald-50/35">
                &copy; {{ date('Y') }} {{ config('app.name') }} · Tes online hanya dapat diakses lewat tautan dari HRD.
            </footer>
        </div>
    </div>
</x-layouts.test>