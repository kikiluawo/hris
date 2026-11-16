<x-layouts.test :title="'Tes online · ' . $assessment->title">
    <div class="relative min-h-dvh overflow-hidden bg-[#06231b] text-emerald-50">
        <div class="pattern-grid absolute inset-0 opacity-40"></div>
        <div class="pointer-events-none absolute -left-32 top-10 size-[420px] rounded-full bg-emerald-400/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-24 bottom-0 size-[380px] rounded-full bg-gold/10 blur-3xl"></div>

        <div class="relative mx-auto flex min-h-dvh w-full max-w-5xl flex-col px-5 py-6 sm:px-8 lg:px-10">
            <header class="flex items-center justify-between gap-3">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <span class="flex size-9 items-center justify-center rounded-xl bg-gold text-[#1d150a] shadow-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="size-5" aria-hidden="true">
                            <path d="M8 6v12M16 6v12M8 12h8" />
                        </svg>
                    </span>
                    <span class="leading-tight">
                        <span class="block text-[15px] font-bold tracking-tight text-white">HRD System</span>
                        <span class="block text-[10px] font-semibold uppercase tracking-[0.14em] text-emerald-100/60">Tes online</span>
                    </span>
                </a>

                <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1 text-[10.5px] font-semibold uppercase tracking-[0.14em] text-emerald-100/80">
                    <span class="size-1.5 rounded-full bg-gold"></span>
                    Undangan resmi
                </span>
            </header>

            <main class="grid flex-1 gap-8 py-10 lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:gap-12">
                <div class="animate-rise">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-gold">Langkah seleksi</p>
                    <h1 class="mt-3 font-display text-[34px] font-semibold leading-[1.1] tracking-tight text-white sm:text-[42px]">
                        {{ $assessment->title }}
                    </h1>

                    <p class="mt-4 max-w-xl text-[14px] leading-relaxed text-emerald-50/70">
                        Anda diundang oleh HRD Majesty Group untuk mengerjakan tes berikut.
                        Tautan ini sudah tertaut ke lamaran Anda, jadi tidak perlu mengisi identitas. Baca aturannya, lalu mulai saat sudah siap.
                    </p>

                    @if ($assessment->description)
                        <p class="mt-3 max-w-xl whitespace-pre-line text-[13px] leading-relaxed text-emerald-50/55">{{ $assessment->description }}</p>
                    @endif

                    <div class="mt-6 flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/5 px-3 py-1.5 text-[12px] text-emerald-50/85">
                            <x-icon name="clock" class="size-4 text-gold" /> {{ $duration }} menit
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/5 px-3 py-1.5 text-[12px] text-emerald-50/85">
                            <x-icon name="clipboard" class="size-4 text-gold" /> {{ $assessment->questions_count }} soal
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/5 px-3 py-1.5 text-[12px] text-emerald-50/85">
                            <x-icon name="camera" class="size-4 text-gold" /> Kamera wajib aktif
                        </span>
                    </div>

                    <div class="mt-7 rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                        <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-emerald-100/60">Aturan selama tes</p>
                        <ul class="mt-3 space-y-2.5 text-[13px] leading-relaxed text-emerald-50/80">
                            <li class="flex items-start gap-2.5">
                                <span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-gold"></span>
                                Kamera harus tetap menyala selama tes. Jangan tutup atau pindah ke aplikasi lain.
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-gold"></span>
                                Jangan berpindah tab atau jendela. Pelanggaran pertama akan memberi peringatan; pelanggaran berikutnya membuat tes langsung berakhir.
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-gold"></span>
                                Waktu mulai berjalan saat Anda menekan tombol mulai, dan tetap berjalan walaupun halaman ditutup.
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-gold"></span>
                                Setiap jawaban tersimpan otomatis. Buka kembali tautan ini untuk melanjutkan sebelum waktu habis.
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-gold"></span>
                                Tautan ini pribadi untuk Anda dan hanya bisa dipakai satu kali. Jangan membagikannya ke orang lain.
                            </li>
                        </ul>
                    </div>

                    @if ($assessment->instructions)
                        <div class="mt-4 rounded-2xl border border-white/10 bg-white/[0.03] p-5">
                            <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-emerald-100/60">Petunjuk dari HRD</p>
                            <p class="mt-2 whitespace-pre-line text-[13px] leading-relaxed text-emerald-50/75">{{ $assessment->instructions }}</p>
                        </div>
                    @endif
                </div>

                <div class="lg:pl-2">
                    <div class="rounded-3xl border border-white/12 bg-white/[0.06] p-6 shadow-[0_30px_80px_-30px_rgba(0,0,0,0.7)] backdrop-blur sm:p-7">
                        @if (session('info'))
                            <div class="mb-4 rounded-xl border border-gold/30 bg-gold/10 px-4 py-3 text-[13px] text-gold-soft">{{ session('info') }}</div>
                        @endif

                        @if ($blocked)
                            <div class="flex size-12 items-center justify-center rounded-2xl bg-gold/15 text-gold">
                                <x-icon name="lock" class="size-7" />
                            </div>
                            <h2 class="mt-4 text-[20px] font-semibold text-white">Tes belum bisa dimulai</h2>
                            <p class="mt-2 text-[13px] leading-relaxed text-emerald-50/70">
                                {{ $blocked }} Hubungi HRD Majesty Group bila Anda merasa ini keliru.
                            </p>
                        @elseif ($attempt !== null && ! $attempt->isInProgress())
                            <div class="flex size-12 items-center justify-center rounded-2xl bg-gold/15 text-gold">
                                <x-icon name="check" class="size-7" />
                            </div>
                            <h2 class="mt-4 text-[20px] font-semibold text-white">Tes sudah dikerjakan</h2>
                            <p class="mt-2 text-[13px] leading-relaxed text-emerald-50/70">
                                Undangan ini sudah dipakai mengerjakan tes. Satu undangan hanya berlaku untuk satu kali pengerjaan.
                            </p>
                            <x-ui.button :href="route('assessment.finished', $attempt->token)" variant="gold" size="lg" icon="eye" class="mt-5 w-full">Lihat status tes</x-ui.button>
                        @else
                            <div
                                x-data="assessmentStart({{ Illuminate\Support\Js::from([
                                    'startUrl' => route('assessment.start', $invitation->token),
                                    'resume' => $attempt !== null,
                                ]) }})"
                            >
                                <div class="flex size-12 items-center justify-center rounded-2xl bg-gold/15 text-gold">
                                    <x-icon name="shield" class="size-7" />
                                </div>

                                <h2 class="mt-4 text-[20px] font-semibold text-white">{{ $attempt !== null ? 'Lanjutkan tes?' : 'Siap memulai?' }}</h2>
                                <p class="mt-2 text-[13px] leading-relaxed text-emerald-50/70">
                                    @if ($attempt !== null)
                                        Tes Anda masih berjalan dan waktu terus berjalan. Tekan tombol di bawah untuk melanjutkan.
                                    @else
                                        Setelah menekan tombol mulai, waktu {{ $duration }} menit langsung berjalan dan kamera akan diaktifkan.
                                    @endif
                                </p>

                                <div class="mt-5 rounded-xl border border-white/10 bg-white/5 px-4 py-3">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.12em] text-emerald-100/50">Undangan untuk</p>
                                    <p class="mt-1 text-[15px] font-semibold text-white">{{ $invitation->candidateName() }}</p>
                                    <p class="text-[12px] text-emerald-50/60">{{ $invitation->candidateEmail() }}</p>
                                    <p class="mt-2 text-[11.5px] leading-relaxed text-emerald-50/45">
                                        Bukan Anda? Jangan lanjutkan dan hubungi HRD. Tautan ini pribadi dan hanya bisa dipakai satu kali.
                                    </p>
                                </div>

                                <p class="mt-3 text-[11.5px] text-emerald-50/45">Berlaku sampai {{ $invitation->expires_at->translatedFormat('d M Y H:i') }}.</p>

                                <div class="mt-4 rounded-xl border border-white/10 bg-white/5 px-4 py-3">
                                    <p class="flex items-center gap-2 text-[12px] font-semibold text-emerald-50">
                                        <x-icon name="camera" class="size-4 text-gold" /> Izin kamera diperlukan
                                    </p>
                                    <p class="mt-1 text-[11.5px] leading-relaxed text-emerald-50/55">
                                        Saat menekan tombol, browser akan meminta izin kamera. Pilih <span class="font-semibold text-emerald-50/80">Izinkan</span>.
                                    </p>
                                </div>

                                <div class="mt-4">
                                    <label class="flex cursor-pointer items-start gap-2.5 text-[12px] leading-relaxed text-emerald-50/75">
                                        <input type="checkbox" x-model="agreed" class="mt-0.5 size-4 shrink-0 rounded border-white/30 bg-white/10 accent-gold">
                                        <span>Saya sudah membaca aturan dan bersedia mengerjakan tes dengan kamera aktif.</span>
                                    </label>
                                </div>

                                <div class="mt-4" x-show="error" x-cloak>
                                    <div class="rounded-xl border border-rose-300/30 bg-rose-400/10 px-4 py-3 text-[12.5px] leading-relaxed text-rose-100" x-text="error"></div>
                                </div>

                                <button
                                    type="button"
                                    x-on:click="begin()"
                                    x-bind:disabled="!ready || starting"
                                    class="mt-5 flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-gold px-5 text-[14px] font-bold text-[#1d150a] shadow-lg shadow-gold/20 transition hover:bg-gold-strong disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <template x-if="!starting">
                                        <span class="flex items-center gap-2">
                                            <x-icon name="play" class="size-4" />
                                            <span x-text="resume ? 'Lanjutkan tes' : 'Aktifkan kamera & mulai tes'"></span>
                                        </span>
                                    </template>
                                    <template x-if="starting">
                                        <span class="flex items-center gap-2">
                                            <svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <circle class="opacity-30" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"></circle>
                                                <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                                            </svg>
                                            Menyiapkan…
                                        </span>
                                    </template>
                                </button>

                                <p class="mt-3 text-center text-[11px] text-emerald-50/45">
                                    Dengan memulai, Anda menyetujui pemantauan kamera dan aktivitas tab selama tes.
                                </p>
                            </div>
                        @endif
                    </div>

                    <p class="mt-4 text-center text-[11px] text-emerald-50/35">
                        Butuh bantuan? Hubungi HRD Majesty Group.
                    </p>
                </div>
            </main>

            <footer class="border-t border-white/10 py-5 text-[11px] text-emerald-50/35">
                &copy; {{ date('Y') }} {{ config('app.name') }} · Tes online hanya dapat diakses lewat tautan dari HRD.
            </footer>
        </div>
    </div>
</x-layouts.test>