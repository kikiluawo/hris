<x-layouts.public title="Lamaran terkirim">
    <div class="mx-auto max-w-xl rounded-3xl border border-line bg-surface p-8 text-center shadow-card sm:p-10">
        <span class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-300">
            <x-icon name="check-circle" class="size-8" />
        </span>

        <h1 class="mt-5 text-[25px] font-semibold tracking-tight">Lamaran terkirim</h1>

        <p class="mt-3 text-[13.5px] leading-relaxed text-muted">
            Terima kasih, <span class="font-semibold text-ink">{{ $candidateName }}</span>. Lamaran Anda untuk posisi
            <span class="font-semibold text-ink">{{ $opening->title }}</span> sudah kami terima beserta CV yang diunggah.
        </p>

        <p class="mt-3 text-[13px] leading-relaxed text-muted">
            Tim HRD akan meninjau lamaran Anda dan menghubungi lewat email atau WhatsApp jika sesuai dengan kebutuhan.
        </p>

        <div class="mt-7 flex flex-col gap-2 sm:flex-row sm:justify-center">
            <x-ui.button :href="route('career.index')" icon="briefcase">Lihat lowongan lain</x-ui.button>
            <x-ui.button :href="route('career.show', $opening->slug)" variant="secondary">Kembali ke lowongan</x-ui.button>
        </div>
    </div>
</x-layouts.public>
