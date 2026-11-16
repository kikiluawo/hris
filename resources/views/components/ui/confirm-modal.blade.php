<div
    x-data
    @keydown.escape.window="$store.confirmBox.open && $store.confirmBox.cancel()"
>
    <div
        x-show="$store.confirmBox.open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[80] flex items-end justify-center p-4 sm:items-center"
        role="dialog"
        aria-modal="true"
        aria-labelledby="confirm-box-title"
        aria-describedby="confirm-box-message"
    >
        <div class="absolute inset-0 bg-[#10160f]/45 backdrop-blur-[3px]" @click="$store.confirmBox.cancel()"></div>

        <div
            x-show="$store.confirmBox.open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-[0.97]"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-2 sm:translate-y-0 sm:scale-[0.98]"
            class="relative w-full max-w-[440px] overflow-hidden rounded-2xl border border-line bg-surface shadow-pop"
        >
            <div class="flex items-start gap-3.5 px-5 pb-4 pt-5 sm:px-6 sm:pt-6">
                <span
                    class="flex size-11 shrink-0 items-center justify-center rounded-2xl"
                    :class="{
                        'bg-brand-soft text-brand-soft-ink': $store.confirmBox.tone === 'brand',
                        'bg-amber-50 text-amber-700 dark:bg-amber-400/10 dark:text-amber-300': $store.confirmBox.tone === 'warning',
                        'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400': $store.confirmBox.tone === 'danger',
                    }"
                >
                    <x-icon name="check-circle" class="size-5" x-show="$store.confirmBox.tone === 'brand'" />
                    <x-icon name="alert-circle" class="size-5" x-show="$store.confirmBox.tone !== 'brand'" />
                </span>

                <div class="min-w-0 pt-0.5">
                    <h2 id="confirm-box-title" class="text-[15.5px] font-semibold leading-snug" x-text="$store.confirmBox.title"></h2>
                    <p id="confirm-box-message" class="mt-1.5 text-[13px] leading-relaxed text-muted" x-text="$store.confirmBox.message"></p>
                </div>
            </div>

            <div class="flex flex-col-reverse gap-2 border-t border-line bg-surface-2/40 px-5 py-3.5 sm:flex-row sm:justify-end sm:px-6">
                <button
                    type="button"
                    class="inline-flex h-10 items-center justify-center rounded-xl border border-line bg-surface px-4 text-sm font-semibold text-ink shadow-sm transition hover:bg-surface-2 focus:outline-none focus-visible:ring-4 focus-visible:ring-brand/15"
                    x-on:click="$store.confirmBox.cancel()"
                    x-text="$store.confirmBox.cancelLabel"
                >Batal</button>

                <button
                    type="button"
                    data-confirm-accept
                    class="inline-flex h-10 items-center justify-center rounded-xl px-4 text-sm font-semibold shadow-sm transition focus:outline-none focus-visible:ring-4"
                    :class="{
                        'bg-brand text-brand-ink hover:bg-brand-hover focus-visible:ring-brand/20': $store.confirmBox.tone === 'brand',
                        'bg-amber-500 text-[#1d150a] hover:bg-amber-600 focus-visible:ring-amber-400/30': $store.confirmBox.tone === 'warning',
                        'bg-rose-600 text-white hover:bg-rose-700 focus-visible:ring-rose-500/25': $store.confirmBox.tone === 'danger',
                    }"
                    x-on:click="$store.confirmBox.accept()"
                    x-text="$store.confirmBox.confirmLabel"
                >Ya, lanjutkan</button>
            </div>
        </div>
    </div>
</div>
