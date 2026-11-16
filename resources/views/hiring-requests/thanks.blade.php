<x-layouts.public title="Permintaan terkirim">
    <div class="mx-auto max-w-xl rounded-3xl border border-line bg-surface p-8 text-center shadow-card sm:p-10">
        <span class="mx-auto flex size-16 items-center justify-center rounded-full border border-gold/30 bg-gold-soft text-gold-strong">
            <x-icon name="check-circle" class="size-8" />
        </span>

        <p class="mt-6 text-[10px] font-bold uppercase tracking-[0.3em] text-gold-strong">Permintaan diterima</p>
        <h1 class="mt-2 font-display text-[28px] font-medium tracking-tight sm:text-[32px]">Form berhasil dikirim</h1>

        <p class="mt-3 text-[13.5px] leading-relaxed text-muted">
            Terima kasih. Permintaan untuk posisi
            <span class="font-semibold text-ink">{{ $title }}</span>
            sudah masuk ke HRD dengan nomor
            <span class="inline-flex items-center rounded-full border border-gold/30 bg-gold-soft/60 px-2.5 py-0.5 text-[12px] font-bold text-gold-strong">{{ $reference }}</span>.
        </p>

        <p class="mt-3 text-[13px] leading-relaxed text-muted">
            HRD akan meninjau kelengkapan data. Jika ada yang perlu dikonfirmasi, HRD menghubungi pengaju melalui nomor yang dicantumkan. Lowongan baru tampil di halaman karier setelah diterbitkan HRD.
        </p>

        <div class="mt-7 flex flex-col gap-2 sm:flex-row sm:justify-center">
            <x-ui.button :href="route('hiring-requests.create', $token)" variant="gold" icon="plus">Isi permintaan lain</x-ui.button>
            <x-ui.button :href="route('career.index')" variant="secondary">Lihat halaman karier</x-ui.button>
        </div>
    </div>
</x-layouts.public>
