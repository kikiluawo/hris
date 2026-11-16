<div
    x-data
    x-show="$store.posterBox.open"
    x-cloak
    x-on:keydown.escape.window="$store.posterBox.close()"
    class="fixed inset-0 z-[70] flex items-center justify-center p-4 sm:p-6"
    role="dialog"
    aria-modal="true"
    aria-label="Gambar lowongan"
>
    <div
        x-show="$store.posterBox.open"
        x-transition.opacity.duration.200ms
        x-on:click="$store.posterBox.close()"
        class="absolute inset-0 bg-[#10160f]/55 backdrop-blur-[3px]"
    ></div>

    <div
        x-show="$store.posterBox.open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-[0.99]"
        class="relative flex max-h-[92vh] w-full max-w-[480px] flex-col overflow-hidden rounded-2xl border border-line bg-surface shadow-pop"
    >
        <div class="flex items-start justify-between gap-4 border-b border-line px-5 py-4">
            <div class="min-w-0">
                <p class="text-[14px] font-semibold">Gambar lowongan</p>
                <p class="mt-0.5 text-[12px] leading-relaxed text-muted">Siap diposting ke WhatsApp, Instagram, atau media lain.</p>
            </div>
            <button
                type="button"
                x-on:click="$store.posterBox.close()"
                class="flex size-8 shrink-0 cursor-pointer items-center justify-center rounded-lg text-muted transition hover:bg-surface-2 hover:text-ink"
                aria-label="Tutup pratinjau gambar"
            >
                <x-icon name="close" class="size-4" />
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto bg-surface-2/40 px-5 py-5">
            <div x-show="$store.posterBox.loading" class="mx-auto flex aspect-[4/5] w-full max-w-[300px] flex-col items-center justify-center gap-3 rounded-xl border border-line bg-surface">
                <span class="size-6 animate-spin rounded-full border-2 border-line border-t-brand"></span>
                <p class="text-[12px] text-muted">Menyiapkan gambar…</p>
            </div>

            <img
                x-show="! $store.posterBox.loading && $store.posterBox.image"
                x-cloak
                x-bind:src="$store.posterBox.image"
                x-bind:alt="'Gambar lowongan ' + ($store.posterBox.job?.title ?? '')"
                class="mx-auto w-full max-w-[300px] rounded-xl border border-line shadow-card"
            />

            <p
                x-show="! $store.posterBox.loading && $store.posterBox.error"
                x-cloak
                x-text="$store.posterBox.error"
                class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-8 text-center text-[12.5px] text-rose-700"
            ></p>
        </div>

        <div class="space-y-3 border-t border-line px-5 py-4">
            <x-ui.button
                type="button"
                class="w-full"
                icon="download"
                x-bind:disabled="$store.posterBox.loading || ! $store.posterBox.image"
                x-on:click="$store.posterBox.download()"
            >
                Simpan gambar
            </x-ui.button>

            <div>
                <p class="text-[10.5px] font-semibold uppercase tracking-[0.14em] text-subtle">Link untuk melamar</p>
                <div class="mt-1.5 flex items-center gap-2">
                    <input
                        type="text"
                        readonly
                        x-bind:value="$store.posterBox.job?.url ?? ''"
                        x-on:focus="$el.select()"
                        class="h-10 w-full min-w-0 rounded-xl border border-line bg-surface-2/50 px-3 text-[12.5px] text-muted focus:border-brand focus:outline-none focus:ring-4 focus:ring-brand/10"
                    />
                    <x-ui.button type="button" variant="secondary" size="md" class="shrink-0" x-on:click="$store.posterBox.copy()">
                        <x-icon name="link" class="size-4" />
                        <span x-text="$store.posterBox.copied ? 'Tersalin' : 'Salin'"></span>
                    </x-ui.button>
                </div>
            </div>
        </div>
    </div>
</div>
