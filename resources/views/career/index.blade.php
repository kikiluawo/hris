<x-layouts.public title="Karir">
    <x-slot:hero>
        <section class="relative isolate overflow-hidden bg-[#0c1e38] text-white">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,rgba(217,180,106,0.16),transparent_55%)]"></div>
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_right,rgba(20,53,102,0.5),transparent_55%)]"></div>
            <div class="pattern-grid pointer-events-none absolute inset-0 opacity-25"></div>

            <div class="relative mx-auto w-full max-w-[1360px] px-4 pb-14 pt-14 sm:px-6 sm:pb-20 sm:pt-20 lg:px-8">
                <div class="grid items-center gap-10 lg:grid-cols-12 lg:gap-12">
                    {{-- Kolom Kiri: Teks & CTA --}}
                    <div class="lg:col-span-7">
                        <p class="flex items-center gap-3 text-[10px] font-semibold uppercase tracking-[0.34em] text-gold">
                            <span class="h-px w-8 bg-gold/60"></span>
                            Karier di Majesty Group
                        </p>

                        <h1 class="mt-6 max-w-2xl font-display text-[40px] font-medium leading-[1.04] tracking-tight sm:text-[54px] lg:text-[62px]">
                            Tumbuh di tempat<br>
                            yang menghargai <span class="italic text-gold">karya</span>.
                        </h1>

                        <p class="mt-6 max-w-xl text-[13.5px] leading-relaxed text-white/60 sm:text-[14.5px]">
                            Kami membangun tim dari orang-orang yang teliti, hangat, dan ingin bertumbuh. Temukan posisi yang sesuai, lalu kirim lamaran beserta CV Anda tanpa perlu membuat akun.
                        </p>

                        <div class="mt-8 flex flex-wrap items-center gap-4">
                            <a
                                href="#lowongan"
                                class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-gold px-7 text-[13px] font-bold text-[#1d150a] shadow-sm transition hover:bg-gold-strong focus:outline-none focus-visible:ring-4 focus-visible:ring-gold/30"
                            >
                                <span>Telusuri Lowongan</span>
                                <x-icon name="arrow-down" class="size-4" />
                            </a>
                        </div>

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

                    {{-- Kolom Kanan: Card Highlights & Info Rekrutmen --}}
                    <div class="relative lg:col-span-5">
                        <div class="pointer-events-none absolute -inset-1 rounded-[32px] bg-gradient-to-tr from-gold/25 via-transparent to-[#1a3f73] opacity-70 blur-xl"></div>

                        <div class="relative overflow-hidden rounded-[28px] border border-white/10 bg-white/[0.04] p-6 backdrop-blur-md shadow-2xl sm:p-7">
                            <div class="flex items-center justify-between gap-3 border-b border-white/10 pb-4">
                                <div class="flex items-center gap-2.5">
                                    <span class="relative flex size-2.5">
                                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex size-2.5 rounded-full bg-emerald-400"></span>
                                    </span>
                                    <span class="text-[12px] font-bold tracking-wide text-white">Perekrutan Aktif</span>
                                </div>
                                <span class="rounded-full border border-gold/40 bg-gold/10 px-3 py-1 text-[10px] font-bold tracking-wider uppercase text-gold">
                                    Majesty Group
                                </span>
                            </div>

                            <div class="mt-5 grid grid-cols-3 gap-2.5 border-b border-white/10 pb-5 text-center">
                                <div class="rounded-2xl border border-white/5 bg-white/[0.03] p-3">
                                    <p class="font-display text-[22px] font-semibold text-gold">{{ $totalOpenings }}</p>
                                    <p class="mt-0.5 text-[10.5px] font-medium text-white/60">Posisi Buka</p>
                                </div>
                                <div class="rounded-2xl border border-white/5 bg-white/[0.03] p-3">
                                    <p class="font-display text-[22px] font-semibold text-white">5+</p>
                                    <p class="mt-0.5 text-[10.5px] font-medium text-white/60">Unit Bisnis</p>
                                </div>
                                <div class="rounded-2xl border border-white/5 bg-white/[0.03] p-3">
                                    <p class="font-display text-[22px] font-semibold text-white">100%</p>
                                    <p class="mt-0.5 text-[10.5px] font-medium text-white/60">Bebas Biaya</p>
                                </div>
                            </div>

                            <div class="mt-5 space-y-3.5">
                                <div class="flex items-start gap-3.5">
                                    <span class="flex size-8 shrink-0 items-center justify-center rounded-xl border border-gold/30 bg-gold/10 text-gold">
                                        <x-icon name="building" class="size-4" />
                                    </span>
                                    <div>
                                        <h4 class="text-[12.5px] font-semibold text-white">Multi-Industri Strategis</h4>
                                        <p class="mt-0.5 text-[11.5px] leading-relaxed text-white/50">Unit bisnis di bidang energi, workshop, perhotelan, ritel & properti.</p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3.5">
                                    <span class="flex size-8 shrink-0 items-center justify-center rounded-xl border border-gold/30 bg-gold/10 text-gold">
                                        <x-icon name="shield" class="size-4" />
                                    </span>
                                    <div>
                                        <h4 class="text-[12.5px] font-semibold text-white">Seleksi Transparan & Terarah</h4>
                                        <p class="mt-0.5 text-[11.5px] leading-relaxed text-white/50">Tinjauan lamaran langsung oleh tim HRD profesional tanpa perantara.</p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3.5">
                                    <span class="flex size-8 shrink-0 items-center justify-center rounded-xl border border-gold/30 bg-gold/10 text-gold">
                                        <x-icon name="users" class="size-4" />
                                    </span>
                                    <div>
                                        <h4 class="text-[12.5px] font-semibold text-white">Jalur Karier & Magang</h4>
                                        <p class="mt-0.5 text-[11.5px] leading-relaxed text-white/50">Terbuka untuk Full Time profesional, Fresh Graduate, dan Magang.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 border-t border-white/10 pt-4">
                                <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-white/40">Kategori Cepat:</p>
                                <div class="mt-2.5 flex flex-wrap gap-1.5">
                                    <a href="{{ route('career.index', ['department' => 'Accounting & Finance']) }}#lowongan" class="rounded-lg border border-white/10 bg-white/5 px-2.5 py-1 text-[11px] font-medium text-white/80 transition hover:border-gold/50 hover:bg-gold/10 hover:text-gold">Accounting</a>
                                    <a href="{{ route('career.index', ['department' => 'Workshop & Maintenance']) }}#lowongan" class="rounded-lg border border-white/10 bg-white/5 px-2.5 py-1 text-[11px] font-medium text-white/80 transition hover:border-gold/50 hover:bg-gold/10 hover:text-gold">Workshop</a>
                                    <a href="{{ route('career.index', ['department' => 'HRGA']) }}#lowongan" class="rounded-lg border border-white/10 bg-white/5 px-2.5 py-1 text-[11px] font-medium text-white/80 transition hover:border-gold/50 hover:bg-gold/10 hover:text-gold">HRGA</a>
                                    <a href="{{ route('career.index', ['type' => 'Internship']) }}#lowongan" class="rounded-lg border border-gold/40 bg-gold/15 px-2.5 py-1 text-[11px] font-bold text-gold transition hover:bg-gold hover:text-[#1d150a]">Internship</a>
                                </div>
                            </div>
                        </div>
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
                    @if ($hasActiveFilters)
                        Menemukan {{ $openings->count() }} lowongan sesuai kriteria filter Anda
                    @else
                        {{ $openings->count() }} posisi menanti Anda hari ini
                    @endif
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="{{ route('career.index') }}#lowongan"
                    class="rounded-full border {{ empty($selectedType) ? 'border-[#0c1e38] bg-[#0c1e38] text-white shadow-sm' : 'border-line bg-surface text-muted hover:border-gold/50' }} px-3.5 py-1.5 text-[11.5px] font-semibold transition"
                >
                    Semua ({{ $totalOpenings }})
                </a>
                <a
                    href="{{ route('career.index', ['type' => 'Full Time']) }}#lowongan"
                    class="rounded-full border {{ $selectedType === 'Full Time' ? 'border-[#0c1e38] bg-[#0c1e38] text-white shadow-sm' : 'border-line bg-surface text-muted hover:border-gold/50' }} px-3.5 py-1.5 text-[11.5px] font-semibold transition"
                >
                    Full Time
                </a>
                <a
                    href="{{ route('career.index', ['type' => 'Internship']) }}#lowongan"
                    class="rounded-full border {{ $selectedType === 'Internship' ? 'border-[#0c1e38] bg-[#0c1e38] text-white shadow-sm' : 'border-line bg-surface text-muted hover:border-gold/50' }} px-3.5 py-1.5 text-[11.5px] font-semibold transition"
                >
                    Internship
                </a>

                @if ($hasActiveFilters)
                    <a href="{{ route('career.index') }}#lowongan" class="ml-1 inline-flex items-center gap-1.5 rounded-full border border-rose-200 bg-rose-50 px-3 py-1.5 text-[11.5px] font-semibold text-rose-600 transition hover:bg-rose-100">
                        <x-icon name="close" class="size-3" />
                        Reset Filter
                    </a>
                @endif
            </div>
        </div>

        <div
            class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_300px] xl:grid-cols-[minmax(0,1fr)_320px] lg:items-start"
            x-data="{ showAll: {{ request()->boolean('all') ? 'true' : 'false' }}, limit: 9 }"
        >
            {{-- Kolom Kiri: Daftar Lowongan (3 Kolom / 3 Baris) --}}
            <div class="min-w-0">
                <div class="grid gap-4.5 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse ($openings as $opening)
                        <article
                            class="group relative flex flex-col overflow-hidden rounded-[24px] border border-line bg-surface p-6 shadow-card transition duration-300 hover:-translate-y-1 hover:border-gold/50 hover:shadow-pop"
                            x-show="showAll || {{ $loop->index }} < limit"
                            x-cloak
                        >
                            <span class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold/70 to-transparent opacity-0 transition duration-300 group-hover:opacity-100"></span>

                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="truncate text-[10px] font-bold uppercase tracking-[0.2em] text-gold-strong">
                                            {{ $opening->company?->name ?? 'Majesty Group' }}
                                        </p>
                                        <span class="inline-flex items-center rounded-full border border-gold/30 bg-gold-soft px-2 py-0.5 text-[10px] font-bold tracking-wide text-gold-strong">
                                            {{ $opening->job_type ?? 'Full Time' }}
                                        </span>
                                    </div>
                                    <h3 class="mt-2 font-display text-[21px] font-medium leading-snug tracking-tight">
                                        <a href="{{ route('career.show', $opening->slug) }}" class="transition group-hover:text-gold-strong after:absolute after:inset-0 after:content-['']">
                                            {{ $opening->title }}
                                        </a>
                                    </h3>
                                </div>

                                <span class="flex size-10 shrink-0 items-center justify-center rounded-full border border-line bg-surface-2/50 font-display text-[16px] font-semibold text-gold-strong transition duration-300 group-hover:border-gold/50 group-hover:bg-gold-soft">
                                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($opening->company?->name ?? 'M', 0, 1)) }}
                                </span>
                            </div>

                            <div class="mt-3.5 flex flex-wrap items-center gap-x-3.5 gap-y-2 text-[11.5px] text-muted">
                                <span class="inline-flex items-center gap-1.5">
                                    <x-icon name="map-pin" class="size-3.5 text-gold/70" />
                                    {{ $opening->location ?? 'Batam' }}
                                </span>

                                <span class="inline-flex items-center gap-1.5">
                                    <x-icon name="sitemap" class="size-3.5 text-gold/70" />
                                    {{ $opening->department?->name ?? 'Umum' }}
                                </span>

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

                            <p class="mt-3.5 line-clamp-2 text-[13px] leading-relaxed text-muted">
                                {{ \Illuminate\Support\Str::limit(preg_replace('/\s*\n+\s*/', ' · ', strip_tags($opening->description)), 130) }}
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
                        <div class="sm:col-span-2 lg:col-span-3">
                            <div class="relative overflow-hidden rounded-[24px] border border-dashed border-line-strong bg-surface/70 px-6 py-14 text-center">
                                <span class="mx-auto flex size-14 items-center justify-center rounded-full border border-gold/30 bg-gold-soft text-gold-strong">
                                    <x-icon name="briefcase" class="size-6" />
                                </span>
                                <h3 class="mt-5 font-display text-[22px] font-medium tracking-tight">
                                    {{ $hasActiveFilters ? 'Tidak ada lowongan yang cocok' : 'Belum ada lowongan dibuka' }}
                                </h3>
                                <p class="mx-auto mt-2 max-w-md text-[13px] leading-relaxed text-muted">
                                    {{ $hasActiveFilters ? 'Coba ubah kata kunci atau reset filter di kolom sebelah kanan.' : 'Kesempatan baru akan segera diumumkan di halaman ini. Silakan kembali beberapa waktu lagi.' }}
                                </p>
                                @if ($hasActiveFilters)
                                    <a href="{{ route('career.index') }}#lowongan" class="mt-5 inline-flex items-center gap-2 rounded-full bg-gold px-5 py-2 text-[12.5px] font-bold text-[#1d150a] transition hover:bg-gold-strong shadow-sm">
                                        <x-icon name="close" class="size-3.5" />
                                        Reset Semua Filter
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforelse
                </div>

                {{-- Tombol Lihat Semua Lowongan (Batas 3 Baris / 9 Kartu) --}}
                @if ($openings->count() > 9)
                    <div class="mt-8 flex flex-col items-center justify-center gap-2 text-center">
                        <button
                            type="button"
                            x-show="!showAll"
                            x-on:click="showAll = true"
                            class="inline-flex items-center gap-2 rounded-full border border-gold/40 bg-gold/10 px-7 py-3 text-[13px] font-bold text-gold-strong transition hover:bg-gold hover:text-[#1d150a] shadow-sm"
                        >
                            <span>Lihat semua lowongan ({{ $openings->count() }} lowongan)</span>
                            <x-icon name="chevron-down" class="size-4" />
                        </button>

                        <button
                            type="button"
                            x-show="showAll"
                            x-cloak
                            x-on:click="showAll = false; document.getElementById('lowongan').scrollIntoView({ behavior: 'smooth' })"
                            class="inline-flex items-center gap-2 rounded-full border border-line bg-surface px-6 py-2.5 text-[12.5px] font-semibold text-muted transition hover:bg-surface-2 hover:text-ink shadow-sm"
                        >
                            <span>Tampilkan lebih sedikit (batasi 3 baris / 9 lowongan)</span>
                            <x-icon name="chevron-up" class="size-4" />
                        </button>
                        <p class="text-[11.5px] text-subtle" x-text="showAll ? 'Menampilkan seluruh {{ $openings->count() }} lowongan' : 'Menampilkan 3 baris (9 dari {{ $openings->count() }} lowongan)'"></p>
                    </div>
                @endif
            </div>

            {{-- Kolom Kanan: Sidebar Filter Sesuai Mockup Gambar --}}
            <aside class="order-first lg:order-last lg:sticky lg:top-24">
                <div class="rounded-[24px] border border-line bg-surface p-6 shadow-card">
                    <div class="flex items-center justify-between border-b border-line pb-4">
                        <h3 class="text-[12px] font-bold uppercase tracking-[0.16em] text-subtle">FILTER JOB</h3>
                        <a
                            href="{{ route('career.index') }}#lowongan"
                            class="text-[12px] font-semibold text-[#3b608c] underline transition hover:text-ink {{ $hasActiveFilters ? '' : 'opacity-70' }}"
                        >
                            Clear Filter
                        </a>
                    </div>

                    <form method="GET" action="{{ route('career.index') }}#lowongan" class="mt-5 space-y-4">
                        {{-- 1. Search Role --}}
                        <div>
                            <label for="filter_q" class="block text-[12.5px] font-semibold text-ink">
                                Search Role <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative mt-1.5">
                                <input
                                    type="text"
                                    name="q"
                                    id="filter_q"
                                    value="{{ $search }}"
                                    placeholder="Type your job title"
                                    class="h-11 w-full rounded-2xl border border-line bg-surface pl-3.5 pr-10 text-[13px] text-ink placeholder:text-subtle transition focus:border-brand focus:outline-none focus:ring-4 focus:ring-brand/10"
                                />
                                <span class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-subtle">
                                    <x-icon name="search" class="size-4" />
                                </span>
                            </div>
                        </div>

                        {{-- 2. Location --}}
                        <div>
                            <label for="filter_location" class="block text-[12.5px] font-semibold text-ink">
                                Location <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative mt-1.5">
                                <select
                                    name="location"
                                    id="filter_location"
                                    onchange="this.form.submit()"
                                    class="h-11 w-full appearance-none rounded-2xl border border-line bg-surface pl-3.5 pr-10 text-[13px] text-ink transition focus:border-brand focus:outline-none focus:ring-4 focus:ring-brand/10"
                                >
                                    <option value="">All Locations</option>
                                    @foreach ($locations as $loc)
                                        <option value="{{ $loc }}" @selected($selectedLocation === $loc)>{{ $loc }}</option>
                                    @endforeach
                                </select>
                                <span class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-subtle">
                                    <x-icon name="chevron-down" class="size-4" />
                                </span>
                            </div>
                        </div>

                        {{-- 3. Business Unit --}}
                        <div>
                            <label for="filter_company" class="block text-[12.5px] font-semibold text-ink">
                                Business Unit <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative mt-1.5">
                                <select
                                    name="company_id"
                                    id="filter_company"
                                    onchange="this.form.submit()"
                                    class="h-11 w-full appearance-none rounded-2xl border border-line bg-surface pl-3.5 pr-10 text-[13px] text-ink transition focus:border-brand focus:outline-none focus:ring-4 focus:ring-brand/10"
                                >
                                    <option value="">All Business Unit</option>
                                    @foreach ($companies as $id => $name)
                                        <option value="{{ $id }}" @selected((string)$selectedCompanyId === (string)$id)>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <span class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-subtle">
                                    <x-icon name="chevron-down" class="size-4" />
                                </span>
                            </div>
                        </div>

                        {{-- 4. Department --}}
                        <div>
                            <label for="filter_department" class="block text-[12.5px] font-semibold text-ink">
                                Department <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative mt-1.5">
                                <select
                                    name="department"
                                    id="filter_department"
                                    onchange="this.form.submit()"
                                    class="h-11 w-full appearance-none rounded-2xl border border-line bg-surface pl-3.5 pr-10 text-[13px] text-ink transition focus:border-brand focus:outline-none focus:ring-4 focus:ring-brand/10"
                                >
                                    <option value="">All Departments</option>
                                    @foreach ($departments as $name)
                                        <option value="{{ $name }}" @selected($selectedDepartment === $name)>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <span class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-subtle">
                                    <x-icon name="chevron-down" class="size-4" />
                                </span>
                            </div>
                        </div>

                        {{-- 5. Employment Type --}}
                        <div>
                            <label for="filter_type" class="block text-[12.5px] font-semibold text-ink">
                                Employment Type <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative mt-1.5">
                                <select
                                    name="type"
                                    id="filter_type"
                                    onchange="this.form.submit()"
                                    class="h-11 w-full appearance-none rounded-2xl border border-line bg-surface pl-3.5 pr-10 text-[13px] text-ink transition focus:border-brand focus:outline-none focus:ring-4 focus:ring-brand/10"
                                >
                                    <option value="">All Employment Type</option>
                                    @foreach ($jobTypes as $typeOption)
                                        <option value="{{ $typeOption }}" @selected($selectedType === $typeOption)>{{ $typeOption }}</option>
                                    @endforeach
                                </select>
                                <span class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-subtle">
                                    <x-icon name="chevron-down" class="size-4" />
                                </span>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button
                                type="submit"
                                class="flex h-11 w-full items-center justify-center gap-2 rounded-2xl bg-[#234062] px-4 text-[13px] font-bold text-white shadow-sm transition hover:bg-[#1a314d] focus:outline-none focus:ring-4 focus:ring-[#234062]/20"
                            >
                                <x-icon name="search" class="size-4" />
                                <span>Cari Lowongan</span>
                            </button>
                        </div>
                    </form>
                </div>
            </aside>
        </div>
    </section>

    <section id="cara-melamar" class="mt-16 scroll-mt-24 overflow-hidden rounded-[28px] border border-gold/20 bg-[#0c1e38] px-6 py-10 text-white sm:px-10 sm:py-12">
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
