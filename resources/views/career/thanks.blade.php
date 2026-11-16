<x-layouts.public title="Lamaran terkirim">
    <div class="mx-auto max-w-xl rounded-3xl border border-line bg-surface p-8 text-center shadow-card sm:p-10">
        <span class="mx-auto flex size-16 items-center justify-center rounded-full border border-gold/30 bg-gold-soft text-gold-strong">
            <x-icon name="check-circle" class="size-8" />
        </span>

        <p class="mt-6 text-[10px] font-bold uppercase tracking-[0.3em] text-gold-strong">Lamaran diterima</p>
        <h1 class="mt-2 font-display text-[28px] font-medium tracking-tight sm:text-[32px]">Lamaran terkirim</h1>

        <p class="mt-3 text-[13.5px] leading-relaxed text-muted">
            Terima kasih, <span class="font-semibold text-ink">{{ $candidateName }}</span>. Lamaran Anda untuk posisi
            <span class="font-semibold text-ink">{{ $opening->title }}</span> sudah kami terima beserta CV yang diunggah.
        </p>

        <p class="mt-3 text-[13px] leading-relaxed text-muted">
            Tim HRD akan meninjau lamaran Anda dan menghubungi lewat email atau WhatsApp jika sesuai dengan kebutuhan.
        </p>

        <div class="mt-7 flex flex-col gap-2 sm:flex-row sm:justify-center">
            <x-ui.button :href="route('career.index')" variant="gold" icon="briefcase">Lihat lowongan lain</x-ui.button>
            <x-ui.button :href="route('career.show', $opening->slug)" variant="secondary">Kembali ke lowongan</x-ui.button>
        </div>
    </div>
</x-layouts.public>
