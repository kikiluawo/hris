@php
    $shareSummary = \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', trim(strip_tags((string) $opening->description))), 140);
    $shareDescription = trim(sprintf(
        'Lowongan %s di %s%s. %s',
        $opening->title,
        $opening->company?->name ?? 'Majesty Group',
        $opening->department ? ' (' . $opening->department->name . ')' : '',
        $shareSummary
    ));
@endphp

<x-layouts.public :title="$opening->title" :description="$shareDescription">
    <x-slot:hero>
        <section class="relative isolate overflow-hidden bg-[#06231b] text-white">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,rgba(217,180,106,0.14),transparent_55%)]"></div>
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_right,rgba(16,185,129,0.12),transparent_55%)]"></div>
            <div class="pattern-grid pointer-events-none absolute inset-0 opacity-20"></div>

            <div class="relative mx-auto w-full max-w-[1160px] px-4 pb-12 pt-8 sm:px-6 sm:pb-14 sm:pt-10 lg:px-8">
                <a href="{{ route('career.index') }}" class="inline-flex items-center gap-1.5 text-[11.5px] font-semibold uppercase tracking-[0.16em] text-white/50 transition hover:text-gold">
                    <x-icon name="chevron-left" class="size-3.5" />
                    Semua lowongan
                </a>

                <div class="mt-7 grid gap-8 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-2 rounded-full border border-gold/30 bg-gold/10 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-gold">
                                {{ $opening->department?->name ?? 'Lowongan' }}
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-full border border-white/15 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-white/60">
                                <span class="size-1.5 rounded-full {{ $opening->is_open ? 'bg-emerald-300' : 'bg-white/40' }}"></span>
                                {{ $opening->is_open ? 'Dibuka' : 'Ditutup' }}
                            </span>
                        </div>

                        <h1 class="mt-5 max-w-3xl font-display text-[36px] font-medium leading-[1.05] tracking-tight sm:text-[50px]">
                            {{ $opening->title }}
                        </h1>

                        <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 text-[12.5px] text-white/60">
                            @if ($opening->company)
                                <span class="inline-flex items-center gap-2">
                                    <x-icon name="building" class="size-4 text-gold/70" />
                                    {{ $opening->company->name }}
                                </span>
                            @endif

                            @if ($opening->position)
                                <span class="inline-flex items-center gap-2">
                                    <x-icon name="briefcase" class="size-4 text-gold/70" />
                                    {{ $opening->position->name }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @if ($opening->is_open)
                            <x-ui.button href="#lamar" variant="gold" icon="upload">Lamar posisi ini</x-ui.button>
                        @endif
                    </div>
                </div>

                <dl class="mt-10 grid grid-cols-2 gap-5 border-t border-white/10 pt-6 sm:grid-cols-4">
                    <div>
                        <dt class="text-[10px] uppercase tracking-[0.18em] text-white/40">Pendidikan</dt>
                        <dd class="mt-1.5 font-display text-[20px] leading-none">{{ $opening->min_education ?? 'Terbuka' }}</dd>
                    </div>
                    <div class="sm:border-l sm:border-white/10 sm:pl-5">
                        <dt class="text-[10px] uppercase tracking-[0.18em] text-white/40">Pengalaman</dt>
                        <dd class="mt-1.5 font-display text-[20px] leading-none">{{ $opening->min_experience === null ? 'Terbuka' : ($opening->min_experience === 0 ? 'Tanpa syarat' : $opening->min_experience . ' tahun') }}</dd>
                    </div>
                    <div class="border-white/10 pt-4 sm:border-l sm:border-t-0 sm:pl-5 sm:pt-0">
                        <dt class="text-[10px] uppercase tracking-[0.18em] text-white/40">Keahlian</dt>
                        <dd class="mt-1.5 font-display text-[20px] leading-none">{{ $opening->required_skills ? count($opening->required_skills) . ' bidang' : 'Terbuka' }}</dd>
                    </div>
                    <div class="border-l border-white/10 pl-5 pt-4 sm:pt-0">
                        <dt class="text-[10px] uppercase tracking-[0.18em] text-white/40">Dibuka</dt>
                        <dd class="mt-1.5 font-display text-[20px] leading-none">{{ $opening->created_at->translatedFormat('d M Y') }}</dd>
                    </div>
                </dl>
            </div>
        </section>
    </x-slot:hero>

    @if (session('error'))
        <x-ui.alert variant="danger" class="mb-5">{{ session('error') }}</x-ui.alert>
    @endif

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_400px] lg:items-start">
        <article class="space-y-6">
            <div class="rounded-2xl sm:rounded-[26px] border border-line bg-surface p-5 sm:p-8 shadow-card">
                <div class="flex items-center gap-3">
                    <span class="h-px w-8 bg-gold/60"></span>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.3em] text-gold-strong">Tentang posisi</p>
                </div>

                <p class="mt-5 whitespace-pre-line text-[14px] leading-[1.85] text-muted">{{ $opening->description }}</p>
            </div>

            @if ($opening->required_skills || $opening->min_education || $opening->min_experience !== null)
                <div class="rounded-2xl sm:rounded-[26px] border border-line bg-surface p-5 sm:p-8 shadow-card">
                    <div class="flex items-center gap-3">
                        <span class="h-px w-8 bg-gold/60"></span>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.3em] text-gold-strong">Kualifikasi</p>
                    </div>

                    @if ($opening->required_skills)
                        <div class="mt-5">
                            <p class="text-[12px] font-semibold uppercase tracking-[0.1em] text-subtle">Keahlian yang dicari</p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($opening->required_skills as $skill)
                                    <span class="rounded-full border border-gold/30 bg-gold-soft/60 px-3 py-1.5 text-[12px] font-semibold text-gold-strong">{{ $skill }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        <div class="flex items-center gap-3.5 rounded-2xl border border-line bg-surface-2/40 px-4 py-3.5">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl border border-gold/25 bg-gold-soft text-gold-strong">
                                <x-icon name="file-text" class="size-[18px]" />
                            </span>
                            <div>
                                <p class="text-[10.5px] uppercase tracking-[0.14em] text-subtle">Pendidikan minimum</p>
                                <p class="mt-0.5 text-[14px] font-semibold">{{ $opening->min_education ?? 'Tidak ditentukan' }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3.5 rounded-2xl border border-line bg-surface-2/40 px-4 py-3.5">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl border border-gold/25 bg-gold-soft text-gold-strong">
                                <x-icon name="clock" class="size-[18px]" />
                            </span>
                            <div>
                                <p class="text-[10.5px] uppercase tracking-[0.14em] text-subtle">Pengalaman minimum</p>
                                <p class="mt-0.5 text-[14px] font-semibold">{{ $opening->min_experience === null ? 'Tidak ditentukan' : ($opening->min_experience === 0 ? 'Tanpa pengalaman' : $opening->min_experience . ' tahun') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="rounded-2xl sm:rounded-[26px] border border-gold/20 bg-[#06231b] p-5 sm:p-8 text-white">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.3em] text-gold">Seleksi transparan</p>
                        <h2 class="mt-2 font-display text-[24px] font-medium tracking-tight">Diproses langsung oleh tim HRD</h2>
                        <p class="mt-2 max-w-xl text-[12.5px] leading-relaxed text-white/55">
                            Lamaran Anda ditinjau berdasarkan kesesuaian pengalaman dan kualifikasi. Kami menghubungi kandidat terpilih melalui email atau WhatsApp.
                        </p>
                    </div>
                    @if ($opening->is_open)
                        <x-ui.button href="#lamar" variant="gold" size="sm" icon="upload">Kirim lamaran</x-ui.button>
                    @endif
                </div>
            </div>
        </article>

        <aside id="lamar" class="scroll-mt-24 lg:sticky lg:top-24">
            @if ($opening->is_open)
                <div class="overflow-hidden rounded-2xl sm:rounded-[26px] border border-line bg-surface shadow-card">
                    <div class="relative overflow-hidden bg-[#06231b] px-5 sm:px-6 py-5 text-white">
                        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(217,180,106,0.18),transparent_60%)]"></div>
                        <div class="relative">
                            <p class="text-[9.5px] font-semibold uppercase tracking-[0.3em] text-gold">Formulir lamaran</p>
                            <h2 class="mt-1.5 font-display text-[24px] font-medium">Kirim lamaran</h2>
                            <p class="mt-1 text-[12px] text-white/50">Lengkapi data diri dan unggah CV Anda.</p>
                        </div>
                    </div>

                    <div class="p-5 sm:p-6">
                        @if ($errors->any())
                            <x-ui.alert variant="danger" class="mb-4">
                                <ul class="list-disc space-y-0.5 pl-4">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </x-ui.alert>
                        @endif

                        <form method="POST" action="{{ route('career.apply', $opening->slug) }}" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <input type="hidden" name="source" value="{{ request('utm_source') }}">

                            <div>
                                <x-ui.label for="name">Nama lengkap</x-ui.label>
                                <x-ui.input name="name" placeholder="Nama sesuai CV" autocomplete="name" />
                                <x-ui.field-error name="name" />
                            </div>

                            <div>
                                <x-ui.label for="email">Email</x-ui.label>
                                <x-ui.input type="email" name="email" icon="mail" placeholder="nama@email.com" autocomplete="email" />
                                @if (! session('already_applied'))
                                    <x-ui.field-error name="email" />
                                @endif
                            </div>

                            <div>
                                <x-ui.label for="phone">Nomor HP / WhatsApp</x-ui.label>
                                <x-ui.input type="tel" name="phone" prefix="+62" placeholder="812 3456 7890" autocomplete="tel" inputmode="numeric" />
                                <p class="mt-1.5 text-[11.5px] text-subtle">Langsung tulis angkanya tanpa 0 di depan, contoh 812 3456 7890.</p>
                                @if (! session('already_applied'))
                                    <x-ui.field-error name="phone" />
                                @endif
                            </div>

                            <div>
                                <x-ui.label for="city">Kota</x-ui.label>
                                <x-ui.input name="city" placeholder="Kota domisili" autocomplete="address-level2" />
                                <x-ui.field-error name="city" />
                            </div>

                            <div>
                                <x-ui.label for="address">Alamat / domisili</x-ui.label>
                                <x-ui.textarea name="address" :rows="3" placeholder="Alamat lengkap saat ini" />
                                <x-ui.field-error name="address" />
                            </div>

                            <div>
                                <x-ui.label for="cv">CV</x-ui.label>
                                <div x-data="{ fileName: '' }">
                                    <input
                                        type="file"
                                        name="cv"
                                        id="cv"
                                        accept=".pdf,.doc,.docx"
                                        class="sr-only"
                                        x-ref="cv"
                                        @change="fileName = $event.target.files[0]?.name ?? ''"
                                    />
                                    <label
                                        for="cv"
                                        class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border border-dashed border-line-strong bg-surface-2/40 px-5 py-7 text-center transition hover:border-gold/60 hover:bg-gold-soft/30"
                                    >
                                        <span class="flex size-10 items-center justify-center rounded-full bg-surface text-gold-strong shadow-sm">
                                            <x-icon name="upload" class="size-[18px]" />
                                        </span>
                                        <span class="text-[13px] font-semibold" x-text="fileName || 'Pilih file CV'">Pilih file CV</span>
                                        <span class="text-[11.5px] text-subtle">PDF, DOC, atau DOCX &middot; maksimal 5 MB</span>
                                    </label>
                                    <x-ui.field-error name="cv" />
                                </div>
                            </div>

                            <x-ui.checkbox name="consent" label="Saya menyetujui data dan CV saya digunakan untuk proses rekrutmen." />
                            <x-ui.field-error name="consent" />

                            <x-ui.button type="submit" variant="gold" size="lg" class="w-full" icon="upload">Kirim lamaran</x-ui.button>
                        </form>
                    </div>
                </div>
            @else
                <div class="overflow-hidden rounded-[26px] border border-line bg-surface shadow-card">
                    <div class="bg-[#06231b] px-6 py-5 text-white">
                        <p class="text-[9.5px] font-semibold uppercase tracking-[0.3em] text-gold">Status lowongan</p>
                        <h2 class="mt-1.5 font-display text-[24px] font-medium">Lowongan ditutup</h2>
                    </div>
                    <div class="p-6">
                        <x-ui.alert variant="warning">
                            Lowongan ini sudah ditutup dan tidak lagi menerima lamaran baru.
                        </x-ui.alert>
                        <x-ui.button :href="route('career.index')" variant="secondary" class="mt-4 w-full" icon="chevron-left">Lihat lowongan lain</x-ui.button>
                    </div>
                </div>
            @endif
        </aside>
    </div>

    @if ($related->isNotEmpty())
        <section class="mt-16">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="flex items-center gap-3 text-[10px] font-semibold uppercase tracking-[0.3em] text-gold-strong">
                        <span class="h-px w-7 bg-gold/50"></span>
                        Kesempatan lain
                    </p>
                    <h2 class="mt-3 font-display text-[28px] font-medium tracking-tight">Lowongan lain</h2>
                </div>
                <a href="{{ route('career.index') }}" class="text-[12px] font-semibold text-gold-strong transition hover:text-ink">Lihat semua</a>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                @foreach ($related as $item)
                    <a href="{{ route('career.show', $item->slug) }}" class="group rounded-2xl border border-line bg-surface p-5 shadow-card transition duration-300 hover:-translate-y-0.5 hover:border-gold/50 hover:shadow-pop">
                        <p class="truncate text-[9.5px] font-bold uppercase tracking-[0.18em] text-gold-strong">{{ $item->company?->name ?? 'Majesty Group' }}</p>
                        <p class="mt-2 font-display text-[19px] font-medium leading-snug transition group-hover:text-gold-strong">{{ $item->title }}</p>
                        <p class="mt-2 text-[11.5px] text-muted">{{ $item->department?->name ?? 'Umum' }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if (session('already_applied'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const info = @json(session('already_applied'));
                if (window.Swal) {
                    window.Swal.fire({
                        icon: 'info',
                        iconColor: '#d9b46a',
                        title: '<span style="font-family: var(--font-display, serif); font-size: 22px; font-weight: 600; color: #1a2e26;">Lamaran Sudah Pernah Terkirim</span>',
                        html: `
                            <div style="text-align: left; font-size: 13.5px; line-height: 1.7; color: #4a5550;">
                                <p style="margin-bottom: 12px;">
                                    Data pendaftaran dengan Email <strong>${info.email}</strong> atau Nomor WhatsApp <strong>${info.phone}</strong> sudah pernah tercatat melamar untuk posisi <strong>${info.opening_title}</strong> di <strong>${info.company_name}</strong>.
                                </p>
                                <div style="border-radius: 16px; border: 1px solid rgba(217, 180, 106, 0.4); background: #fdfaf3; padding: 14px; margin-bottom: 12px;">
                                    <div style="display: flex; align-items: flex-start; gap: 10px;">
                                        <span style="font-size: 18px; line-height: 1;">✨</span>
                                        <div>
                                            <strong style="color: #634a19; font-size: 13px; display: block; margin-bottom: 3px;">Kabar baik untuk Anda:</strong>
                                            <p style="color: #785c25; font-size: 12.5px; margin: 0; line-height: 1.5;">
                                                Anda tetap <strong>dapat menggunakan data yang sama</strong> untuk melamar pada <strong>posisi atau lowongan lain</strong> yang sedang dibuka di Majesty Group.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <p style="font-size: 12px; color: #7f8c85; margin: 0;">
                                    Silakan jelajahi kesempatan karier lain yang sesuai dengan minat dan kualifikasi Anda!
                                </p>
                            </div>
                        `,
                        background: '#fffdfa',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showCancelButton: true,
                        confirmButtonText: 'Jelajahi Lowongan Lain',
                        cancelButtonText: 'Tutup',
                        confirmButtonColor: '#06231b',
                        cancelButtonColor: '#78716c',
                        customClass: {
                            popup: 'swal2-modern-popup',
                            confirmButton: 'swal2-modern-confirm',
                            cancelButton: 'swal2-modern-cancel',
                        },
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = info.other_openings_url;
                        }
                    });
                }
            });
        </script>
    @endif
</x-layouts.public>
