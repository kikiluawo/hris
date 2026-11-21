<x-layouts.public title="Terjadi kendala server · Error 500">
    <section class="relative overflow-hidden rounded-3xl border border-line bg-surface px-6 py-16 text-center shadow-card sm:px-10 sm:py-24">
        {{-- Decorative ambient glow --}}
        <div class="pointer-events-none absolute -left-24 -top-24 size-72 rounded-full bg-amber-500/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-28 -right-20 size-72 rounded-full bg-gold/10 blur-3xl"></div>

        <div class="relative mx-auto flex max-w-lg flex-col items-center">
            {{-- Icon Badge --}}
            <span class="flex size-16 items-center justify-center rounded-full border border-amber-500/30 bg-amber-500/10 text-amber-600 dark:text-amber-400 shadow-sm">
                <x-icon name="alert-circle" class="size-8" />
            </span>

            {{-- Label & Headings --}}
            <p class="mt-6 text-[10.5px] font-bold uppercase tracking-[0.3em] text-amber-600 dark:text-amber-400">Error 500 · Server Terkendala</p>
            <h1 class="mt-3 font-display text-[28px] font-medium leading-tight tracking-tight sm:text-[36px]">
                Terjadi kendala pada sistem kami.
            </h1>
            <p class="mt-3 max-w-md text-[13.5px] leading-relaxed text-muted">
                Mohon maaf atas ketidaknyamanan ini. Server sedang mengalami gangguan sementara dan permintaan Anda belum dapat diproses. Tim kami sedang menanganinya, silakan coba muat ulang beberapa saat lagi.
            </p>

            {{-- Action Buttons --}}
            <div class="mt-8 flex w-full flex-col items-center justify-center gap-2.5 sm:w-auto sm:flex-row">
                <button
                    type="button"
                    onclick="window.location.reload()"
                    class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-gold px-6 text-[13px] font-bold text-[#1d150a] shadow-sm transition hover:bg-gold-strong focus:outline-none focus:ring-4 focus:ring-gold/25 sm:w-auto cursor-pointer"
                >
                    <x-icon name="refresh" class="size-4" />
                    <span>Muat Ulang Halaman</span>
                </button>

                <a
                    href="{{ url('/') }}"
                    class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl border border-line bg-surface px-6 text-[13px] font-semibold text-ink shadow-sm transition hover:bg-surface-2 focus:outline-none focus:ring-4 focus:ring-brand/10 sm:w-auto"
                >
                    <x-icon name="chevron-left" class="size-4" />
                    <span>Kembali ke Beranda</span>
                </a>
            </div>

            {{-- Help & Contact Info --}}
            <div class="mt-10 w-full max-w-sm rounded-2xl border border-line bg-surface-2/60 p-4 text-[12px] text-subtle">
                <div class="flex items-center justify-center gap-2 text-ink">
                    <span class="size-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="font-semibold text-[11.5px]">Pusat Bantuan Majesty HR Portal</span>
                </div>
                <p class="mt-1.5 leading-relaxed">
                    Jika kendala berlanjut saat proses lamaran atau tes online, silakan hubungi tim HRD kami.
                </p>
            </div>

            {{-- Debug Info (Hanya tampil jika APP_DEBUG=true) --}}
            @if (config('app.debug') && isset($exception) && $exception->getMessage())
                <div class="mt-6 w-full text-left" x-data="{ open: false }">
                    <button
                        type="button"
                        @click="open = !open"
                        class="mx-auto flex items-center gap-1.5 text-[11.5px] font-semibold text-muted hover:text-ink transition underline cursor-pointer"
                    >
                        <span x-text="open ? 'Sembunyikan detail teknis' : 'Lihat detail teknis (Mode Debug)'"></span>
                    </button>
                    <div x-show="open" x-cloak class="mt-3 overflow-x-auto rounded-xl border border-rose-200 bg-rose-50/80 p-4 font-mono text-[11px] text-rose-800 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-200">
                        <p class="font-bold">{{ get_class($exception) }}</p>
                        <p class="mt-1">{{ $exception->getMessage() }}</p>
                        <p class="mt-1 opacity-70 text-[10px]">{{ $exception->getFile() }}:{{ $exception->getLine() }}</p>
                    </div>
                </div>
            @endif
        </div>
    </section>
</x-layouts.public>
