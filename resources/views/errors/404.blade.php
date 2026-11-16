<x-layouts.public title="Halaman tidak ditemukan">
    <section class="relative overflow-hidden rounded-3xl border border-line bg-surface px-6 py-16 text-center shadow-card sm:px-10 sm:py-24">
        <div class="pointer-events-none absolute -left-24 -top-24 size-72 rounded-full bg-brand/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-28 -right-20 size-72 rounded-full bg-brand/10 blur-3xl"></div>

        <div class="relative mx-auto flex max-w-lg flex-col items-center">
            <span class="flex size-16 items-center justify-center rounded-full border border-gold/30 bg-gold-soft text-gold-strong shadow-sm">
                <x-icon name="map-pin" class="size-8" />
            </span>

            <p class="mt-6 text-[10px] font-bold uppercase tracking-[0.3em] text-gold-strong">Error 404</p>
            <h1 class="mt-3 font-display text-[28px] font-medium leading-tight tracking-tight sm:text-[34px]">
                Halaman ini sedang tidak ada di tempatnya.
            </h1>
            <p class="mt-3 max-w-md text-[13.5px] leading-relaxed text-muted">
                Tautan yang Anda buka mungkin sudah dipindahkan, kedaluwarsa, atau salah ketik. Tidak perlu khawatir, Anda bisa mulai lagi dari beranda dan menelusuri lowongan yang sedang dibuka.
            </p>

            <div class="mt-8 flex w-full flex-col items-center justify-center gap-2.5 sm:w-auto sm:flex-row">
                <x-ui.button :href="route('career.index')" variant="gold" icon="briefcase" class="w-full sm:w-auto">Lihat lowongan</x-ui.button>

                @auth
                    <x-ui.button :href="route('dashboard')" variant="secondary" icon="grid" class="w-full sm:w-auto">Buka dashboard</x-ui.button>
                @else
                    <x-ui.button variant="secondary" icon="chevron-left" class="w-full sm:w-auto" onclick="history.length > 1 ? history.back() : window.location.href = '{{ route('career.index') }}'">
                        Halaman sebelumnya
                    </x-ui.button>
                @endauth
            </div>
        </div>
    </section>
</x-layouts.public>
