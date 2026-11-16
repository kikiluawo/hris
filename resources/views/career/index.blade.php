<x-layouts.public title="Karir">
    <x-slot:hero>
        <section class="relative isolate overflow-hidden bg-[#06231b] text-white">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,rgba(217,180,106,0.16),transparent_55%)]"></div>
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_right,rgba(16,185,129,0.14),transparent_55%)]"></div>
            <div class="pattern-grid pointer-events-none absolute inset-0 opacity-25"></div>
            <span class="pointer-events-none absolute -right-16 top-0 hidden select-none font-display text-[240px] leading-none text-white/[0.025] lg:block">Karir</span>

            <div class="relative mx-auto w-full max-w-[1160px] px-4 pb-14 pt-14 sm:px-6 sm:pb-20 sm:pt-20 lg:px-8">
                <div class="grid gap-12 lg:grid-cols-[minmax(0,1fr)_340px] lg:items-end">
                    <div>
                        <p class="flex items-center gap-3 text-[10px] font-semibold uppercase tracking-[0.34em] text-gold">
                            <span class="h-px w-8 bg-gold/60"></span>
                            Karier di Majesty Group
                        </p>

                        <h1 class="mt-6 max-w-2xl font-display text-[40px] font-medium leading-[1.04] tracking-tight sm:text-[56px] lg:text-[64px]">
                            Tumbuh di tempat<br>
                            yang menghargai <span class="italic text-gold">karya</span>.
                        </h1>

                        <p class="mt-6 max-w-xl text-[13.5px] leading-relaxed text-white/60 sm:text-[14.5px]">
                            Kami membangun tim dari orang-orang yang teliti, hangat, dan ingin bertumbuh. Temukan posisi yang sesuai, lalu kirim lamaran beserta CV Anda tanpa perlu membuat akun.
                        </p>

                        <form method="GET" action="{{ route('career.index') }}" class="mt-9 flex w-full max-w-xl flex-col gap-2 sm:flex-row">
                            <div class="relative flex-1">
                                <label for="q" class="sr-only">Cari lowongan</label>
                                <x-icon name="search" class="pointer-events-none absolute left-5 top-1/2 size-[18px] -translate-y-1/2 text-white/40" />
                                <input
                                    type="search"
                                    name="q"
                                    id="q"
                                    value="{{ $search }}"
                                    placeholder="Cari posisi, perusahaan, atau departemen"
                                    class="h-14 w-full rounded-full border border-white/15 bg-white/[0.06] pl-13 pr-5 text-[13.5px] text-white placeholder:text-white/40 focus:border-gold/60 focus:bg-white/10 focus:outline-none focus:ring-4 focus:ring-gold/10"
                                />
                            </div>
                            <button
                                type="submit"
                                class="inline-flex h-14 shrink-0 items-center justify-center rounded-full bg-gold px-7 text-[13px] font-bold text-[#1d150a] shadow-sm transition hover:bg-gold-strong focus:outline-none focus-visible:ring-4 focus-visible:ring-gold/30"
                            >
                                Cari lowongan
                            </button>
                        </form>

                        <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-2 text-[11.5px] font-medium text-white/50">
                            <span class="inline-flex items-center gap-2">
                                <span class="size-1.5 rounded-full bg-gold"></span>
                                {{ $totalOpenings }} lowongan dibuka
                            </span>
                            <span class="inline-flex items-center gap-2">
                                <span class="size-1.5 rounded-full bg-gold/60"></span>
                                Seleksi transparan
                            </span>
                            <span class="inline-flex items-center gap-2">
                                <span class="size-1.5 rounded-full bg-gold/60"></span>
                                Lamaran 100% online
                            </span>
                        </div>
                    </div>

                    <aside class="hidden lg:block">
                        <div class="rounded-[26px] border border-gold/20 bg-white/[0.03] p-7 backdrop-blur-sm">
                            <p class="text-[9.5px] font-semibold uppercase tracking-[0.32em] text-gold/80">Sekilas</p>

                            <dl class="mt-5 space-y-5">
                                <div class="flex items-end justify-between gap-4 border-b border-white/10 pb-5">
                                    <dt class="pb-1 text-[11px] uppercase tracking-[0.14em] text-white/45">Lowongan dibuka</dt>
                                    <dd class="font-display text-[36px] leading-none text-gold">{{ $totalOpenings }}</dd>
                                </div>
                                <div class="flex items-end justify-between gap-4 border-b border-white/10 pb-5">
                                    <dt class="pb-1 text-[11px] uppercase tracking-[0.14em] text-white/45">Perusahaan</dt>
                                    <dd class="font-display text-[36px] leading-none text-white">{{ $totalCompanies }}</dd>
                                </div>
                                <div class="flex items-end justify-between gap-4">
                                    <dt class="pb-1 text-[11px] uppercase tracking-[0.14em] text-white/45">Cabang</dt>
                                    <dd class="font-display text-[36px] leading-none text-white">{{ $totalBranches }}</dd>
                                </div>
                            </dl>
                        </div>
                    </aside>
                </div>

                <div class="mt-10 grid grid-cols-3 gap-4 border-t border-white/10 pt-6 lg:hidden">
                    <div>
                        <p class="font-display text-[28px] leading-none text-gold">{{ $totalOpenings }}</p>
                        <p class="mt-1.5 text-[10px] uppercase tracking-[0.12em] text-white/45">Lowongan</p>
                    </div>
                    <div class="border-l border-white/10 pl-4">
                        <p class="font-display text-[28px] leading-none text-white">{{ $totalCompanies }}</p>
                        <p class="mt-1.5 text-[10px] uppercase tracking-[0.12em] text-white/45">Perusahaan</p>
                    </div>
                    <div class="border-l border-white/10 pl-4">
                        <p class="font-display text-[28px] leading-none text-white">{{ $totalBranches }}</p>
                        <p class="mt-1.5 text-[10px] uppercase tracking-[0.12em] text-white/45">Cabang</p>
                    </div>
                </div>
            </div>
        </section>
    </x-slot:hero>

    <section id="lowongan" class="scroll-mt-24">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="flex items-center gap-3 text-[10px] font-semibold uppercase tracking-[0.3em] text-gold-strong">
                    <span class="h-px w-7 bg-gold/50"></span>
                    Kesempatan terbuka
                </p>
                <h2 class="mt-3 font-display text-[30px] font-medium tracking-tight sm:text-[38px]">Lowongan tersedia</h2>
                <p class="mt-2 text-[13px] text-muted">
                    @if ($search !== '')
                        Hasil pencarian &ldquo;{{ $search }}&rdquo; &middot; {{ $openings->count() }} lowongan ditemukan
                    @else
                        {{ $openings->count() }} posisi menanti Anda hari ini
                    @endif
                </p>
            </div>

            @if ($search !== '')
                <a href="{{ route('career.index') }}" class="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-gold-strong transition hover:text-ink">
                    <x-icon name="close" class="size-3.5" />
                    Hapus pencarian
                </a>
            @endif
        </div>

        <div class="mt-8 grid gap-5 sm:grid-cols-2">
            @forelse ($openings as $opening)
                <article class="group relative flex flex-col overflow-hidden rounded-[24px] border border-line bg-surface p-6 shadow-card transition duration-300 hover:-translate-y-1 hover:border-gold/50 hover:shadow-pop">
                    <span class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold/70 to-transparent opacity-0 transition duration-300 group-hover:opacity-100"></span>

                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="truncate text-[10px] font-bold uppercase tracking-[0.2em] text-gold-strong">
                                {{ $opening->company?->name ?? 'Majesty Group' }}
                            </p>
                            <h3 class="mt-2 font-display text-[23px] font-medium leading-snug tracking-tight">
                                <a href="{{ route('career.show', $opening->slug) }}" class="transition group-hover:text-gold-strong after:absolute after:inset-0 after:content-['']">
                                    {{ $opening->title }}
                                </a>
                            </h3>
                        </div>

                        <span class="flex size-11 shrink-0 items-center justify-center rounded-full border border-line bg-surface-2/50 font-display text-[18px] font-semibold text-gold-strong transition duration-300 group-hover:border-gold/50 group-hover:bg-gold-soft">
                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($opening->company?->name ?? 'M', 0, 1)) }}
                        </span>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-[11.5px] text-muted">
                        <span class="inline-flex items-center gap-1.5">
                            <x-icon name="sitemap" class="size-3.5 text-gold/70" />
                            {{ $opening->department?->name ?? 'Umum' }}
                        </span>

                        @if ($opening->position)
                            <span class="inline-flex items-center gap-1.5">
                                <x-icon name="briefcase" class="size-3.5 text-gold/70" />
                                {{ $opening->position->name }}
                            </span>
                        @endif

                        @if ($opening->min_education)
                            <span class="inline-flex items-center gap-1.5">
                                <x-icon name="file-text" class="size-3.5 text-gold/70" />
                                {{ $opening->min_education }}
                            </span>
                        @endif

                        @if ($opening->min_experience !== null)
                            <span class="inline-flex items-center gap-1.5">
                                <x-icon name="clock" class="size-3.5 text-gold/70" />
                                {{ $opening->min_experience === 0 ? 'Tanpa pengalaman' : $opening->min_experience . ' thn' }}
                            </span>
                        @endif
                    </div>

                    <p class="mt-4 line-clamp-2 text-[13px] leading-relaxed text-muted">
                        {{ \Illuminate\Support\Str::limit(preg_replace('/\s*\n+\s*/', ' · ', strip_tags($opening->description)), 150) }}
                    </p>

                    <div class="mt-auto flex items-center justify-between gap-3 border-t border-line/70 pt-4">
                        <span class="text-[10.5px] uppercase tracking-[0.14em] text-subtle">{{ $opening->created_at->diffForHumans() }}</span>
                        <span class="inline-flex items-center gap-2 text-[12.5px] font-bold text-gold-strong">
                            Lihat detail
                            <x-icon name="arrow-right" class="size-3.5 transition group-hover:translate-x-0.5" />
                        </span>
                    </div>
                </article>
            @empty
                <div class="sm:col-span-2">
                    <div class="relative overflow-hidden rounded-[24px] border border-dashed border-line-strong bg-surface/70 px-6 py-16 text-center">
                        <span class="mx-auto flex size-14 items-center justify-center rounded-full border border-gold/30 bg-gold-soft text-gold-strong">
                            <x-icon name="briefcase" class="size-6" />
                        </span>
                        <h3 class="mt-5 font-display text-[24px] font-medium tracking-tight">
                            {{ $search !== '' ? 'Belum ada lowongan yang cocok' : 'Belum ada lowongan dibuka' }}
                        </h3>
                        <p class="mx-auto mt-2 max-w-md text-[13px] leading-relaxed text-muted">
                            {{ $search !== '' ? 'Coba kata kunci lain, atau telusuri seluruh lowongan yang sedang tersedia.' : 'Kesempatan baru akan segera diumumkan di halaman ini. Silakan kembali beberapa waktu lagi.' }}
                        </p>
                        @if ($search !== '')
                            <x-ui.button :href="route('career.index')" variant="gold" size="sm" class="mt-6">Lihat semua lowongan</x-ui.button>
                        @endif
                    </div>
                </div>
            @endforelse
        </div>
    </section>

    <section id="cara-melamar" class="mt-16 scroll-mt-24 overflow-hidden rounded-[28px] border border-gold/20 bg-[#06231b] px-6 py-10 text-white sm:px-10 sm:py-12">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="flex items-center gap-3 text-[10px] font-semibold uppercase tracking-[0.3em] text-gold">
                    <span class="h-px w-7 bg-gold/50"></span>
                    Proses rekrutmen
                </p>
                <h2 class="mt-3 font-display text-[28px] font-medium tracking-tight sm:text-[34px]">Cara melamar</h2>
            </div>
            <p class="max-w-sm text-[12.5px] leading-relaxed text-white/50">Tiga langkah sederhana, tanpa membuat akun. Seluruh proses berlangsung online.</p>
        </div>

        <div class="mt-9 grid gap-8 sm:grid-cols-3">
            @foreach ([
                ['01', 'Pilih lowongan', 'Telusuri posisi yang sesuai dengan pengalaman dan minat Anda.'],
                ['02', 'Kirim lamaran', 'Isi formulir singkat dan unggah CV dalam format PDF, DOC, atau DOCX.'],
                ['03', 'Tunggu kabar', 'Tim HRD meninjau lamaran dan menghubungi kandidat yang terpilih.'],
            ] as $step)
                <div class="border-t border-gold/25 pt-5">
                    <span class="font-display text-[30px] leading-none text-gold">{{ $step[0] }}</span>
                    <h3 class="mt-3 text-[14px] font-semibold">{{ $step[1] }}</h3>
                    <p class="mt-1.5 text-[12.5px] leading-relaxed text-white/50">{{ $step[2] }}</p>
                </div>
            @endforeach
        </div>
    </section>
</x-layouts.public>
