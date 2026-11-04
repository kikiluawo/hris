<x-layouts.public title="Karir">
    <section class="relative overflow-hidden rounded-3xl bg-[#0a3327] px-6 py-12 text-white shadow-card sm:px-10 sm:py-16 lg:px-14">
        <div class="pattern-grid pointer-events-none absolute inset-0 opacity-60"></div>
        <div class="pointer-events-none absolute -left-24 top-0 size-[360px] rounded-full bg-emerald-400/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-20 bottom-0 size-[320px] rounded-full bg-lime-300/10 blur-3xl"></div>

        <div class="relative max-w-2xl">
            <span class="inline-flex w-fit items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1 text-[10.5px] font-semibold uppercase tracking-[0.14em] text-emerald-100/90">
                <span class="size-1.5 rounded-full bg-emerald-300"></span>
                Karier
            </span>

            <h1 class="mt-5 text-[32px] font-semibold leading-[1.1] tracking-tight sm:text-[44px]">
                Tempat yang tepat untuk langkah karier berikutnya.
            </h1>

            <p class="mt-4 max-w-xl text-[13.5px] leading-relaxed text-emerald-50/75 sm:text-[14.5px]">
                Kami mencari orang-orang yang ingin tumbuh dan memberi dampak nyata. Telusuri lowongan yang tersedia, lalu kirim lamaran beserta CV Anda dalam satu langkah.
            </p>

            <form method="GET" action="{{ route('career.index') }}" class="mt-8 flex w-full max-w-xl flex-col gap-2 sm:flex-row">
                <div class="relative flex-1">
                    <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-emerald-50/60" />
                    <input
                        type="search"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Cari posisi, perusahaan, atau departemen"
                        class="h-11 w-full rounded-xl border border-white/15 bg-white/10 pl-10 pr-4 text-[13.5px] text-white placeholder:text-emerald-50/50 focus:border-emerald-300/60 focus:bg-white/15 focus:outline-none focus:ring-4 focus:ring-emerald-300/10"
                    />
                </div>
                <button
                    type="submit"
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-white px-5 text-sm font-semibold text-[#0a3327] shadow-sm transition hover:bg-emerald-50 focus:outline-none focus-visible:ring-4 focus-visible:ring-white/25"
                >
                    Cari lowongan
                </button>
            </form>

            <div class="mt-7 flex flex-wrap items-center gap-x-5 gap-y-2 text-[12px] font-medium text-emerald-50/70">
                <span class="inline-flex items-center gap-1.5">
                    <span class="size-1.5 rounded-full bg-emerald-300"></span>
                    {{ $totalOpenings }} lowongan dibuka
                </span>
                <span>Seleksi transparan</span>
                <span>Lamaran 100% online</span>
            </div>
        </div>
    </section>

    <section class="mt-12">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-[22px] font-semibold tracking-tight">Lowongan terbaru</h2>
                <p class="mt-1 text-[13px] text-muted">
                    @if ($search !== '')
                        Hasil pencarian &ldquo;{{ $search }}&rdquo; &middot; {{ $openings->count() }} lowongan ditemukan
                    @else
                        {{ $openings->count() }} posisi menanti Anda
                    @endif
                </p>
            </div>

            @if ($search !== '')
                <a href="{{ route('career.index') }}" class="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-brand transition hover:underline">
                    <x-icon name="close" class="size-3.5" />
                    Hapus pencarian
                </a>
            @endif
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            @forelse ($openings as $opening)
                <div class="group relative flex flex-col rounded-2xl border border-line bg-surface p-5 shadow-card transition duration-200 hover:-translate-y-0.5 hover:border-brand/40 hover:shadow-pop">
                    <div class="flex items-start gap-3">
                        <x-ui.avatar :name="$opening->company?->name ?? 'HRD System'" size="lg" />

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-[12px] font-semibold text-muted">{{ $opening->company?->name ?? 'HRD System' }}</p>
                            <h3 class="mt-0.5 text-[17px] font-semibold leading-snug">
                                <a href="{{ route('career.show', $opening->slug) }}" class="transition hover:text-brand">{{ $opening->title }}</a>
                            </h3>
                        </div>

                        <a
                            href="{{ route('career.show', $opening->slug) }}"
                            class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-surface-2 text-subtle transition hover:bg-brand-soft hover:text-brand"
                            aria-label="Lihat detail {{ $opening->title }}"
                        >
                            <x-icon name="arrow-right" class="size-4" />
                        </a>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-1.5">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-line bg-surface-2 px-2.5 py-0.5 text-[11px] font-semibold text-muted">
                            <x-icon name="sitemap" class="size-3" />
                            {{ $opening->department?->name ?? 'Umum' }}
                        </span>

                        @if ($opening->position)
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-line bg-surface-2 px-2.5 py-0.5 text-[11px] font-semibold text-muted">
                                <x-icon name="briefcase" class="size-3" />
                                {{ $opening->position->name }}
                            </span>
                        @endif
                    </div>

                    <p class="mt-3 line-clamp-2 text-[13px] leading-relaxed text-muted">
                        {{ \Illuminate\Support\Str::limit(preg_replace('/\s*\n+\s*/', ' · ', strip_tags($opening->description)), 150) }}
                    </p>

                    <div class="mt-4 flex items-center justify-between gap-3 border-t border-line/70 pt-3">
                        <span class="text-[11.5px] font-medium text-subtle">Dibuka {{ $opening->created_at->diffForHumans() }}</span>
                        <x-ui.button :href="route('career.show', $opening->slug)" size="sm">Lamar sekarang</x-ui.button>
                    </div>
                </div>
            @empty
                <div class="sm:col-span-2">
                    <x-ui.card>
                        <x-ui.empty-state
                            icon="briefcase"
                            :title="$search !== '' ? 'Tidak ada lowongan yang cocok' : 'Belum ada lowongan dibuka'"
                            :description="$search !== '' ? 'Coba kata kunci lain, atau lihat seluruh lowongan yang sedang tersedia.' : 'Silakan cek kembali dalam beberapa waktu ke depan.'"
                        >
                            @if ($search !== '')
                                <x-slot:actions>
                                    <x-ui.button :href="route('career.index')" variant="secondary" size="sm">Lihat semua lowongan</x-ui.button>
                                </x-slot:actions>
                            @endif
                        </x-ui.empty-state>
                    </x-ui.card>
                </div>
            @endforelse
        </div>
    </section>

    <section class="mt-14">
        <h2 class="text-[22px] font-semibold tracking-tight">Cara melamar</h2>
        <p class="mt-1 text-[13px] text-muted">Tiga langkah sederhana, tanpa perlu membuat akun.</p>

        <div class="mt-4 grid gap-4 sm:grid-cols-3">
            @foreach ([
                ['Pilih lowongan', 'Telusuri posisi yang sesuai dengan pengalaman dan minat Anda.'],
                ['Kirim lamaran', 'Isi formulir singkat dan unggah CV dalam format PDF, DOC, atau DOCX.'],
                ['Tunggu kabar', 'Tim HRD meninjau lamaran dan menghubungi kandidat yang terpilih.'],
            ] as $index => $step)
                <div class="rounded-2xl border border-line bg-surface p-5 shadow-card">
                    <span class="flex size-9 items-center justify-center rounded-xl bg-brand-soft text-[13px] font-bold text-brand-soft-ink">{{ $index + 1 }}</span>
                    <h3 class="mt-3 text-[14px] font-semibold">{{ $step[0] }}</h3>
                    <p class="mt-1 text-[12.5px] leading-relaxed text-muted">{{ $step[1] }}</p>
                </div>
            @endforeach
        </div>
    </section>
</x-layouts.public>
