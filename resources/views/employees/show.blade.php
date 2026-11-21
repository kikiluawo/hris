@php
    $hasRealEmail = $employee->email && ! str_ends_with($employee->email, '@noemail.majesty.local');

    $waNumber = null;
    if ($employee->phone) {
        $firstPhone = preg_split('/[\/&,]/', $employee->phone)[0] ?? '';
        $digits = preg_replace('/\D+/', '', $firstPhone);
        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }
        if (strlen($digits) >= 9) {
            $waNumber = $digits;
        }
    }

    $tenureText = '—';
    if ($employee->joined_at) {
        $today = now()->startOfDay();
        if ($employee->joined_at->lte($today)) {
            $diff = $employee->joined_at->diff($today);
            $parts = [];
            if ($diff->y > 0) {
                $parts[] = $diff->y . ' thn';
            }
            if ($diff->m > 0) {
                $parts[] = $diff->m . ' bln';
            }
            if (empty($parts)) {
                $parts[] = max(1, $diff->d) . ' hari';
            }
            $tenureText = implode(' ', $parts);
        } else {
            $tenureText = 'Mulai ' . $employee->joined_at->translatedFormat('d M Y');
        }
    }

    $ageText = null;
    if ($employee->birth_date && $employee->birth_date->isPast() && $employee->birth_date->year >= 1940) {
        $ageText = $employee->birth_date->age . ' tahun';
    }

    $contractCardMeta = function ($start, $end) {
        if (! $start && ! $end) {
            return [
                'filled' => false,
                'range' => 'Belum diisi',
                'duration' => null,
                'badge' => ['label' => 'Kosong', 'variant' => 'neutral'],
            ];
        }

        $range = ($start?->translatedFormat('d M Y') ?? '—') . ' – ' . ($end?->translatedFormat('d M Y') ?? '—');
        $duration = null;
        if ($start && $end && $end->gte($start)) {
            $months = (int) round($start->diffInDays($end) / 30);
            $duration = $months > 0 ? "{$months} bulan" : $start->diffInDays($end) . ' hari';
        }

        $today = now()->startOfDay();
        $badge = ['label' => 'Terjadwal', 'variant' => 'info'];
        if ($end && $end->lt($today)) {
            $badge = ['label' => 'Berakhir', 'variant' => 'neutral'];
        } elseif ($start && $start->lte($today) && (! $end || $end->gte($today))) {
            $daysLeft = $end ? (int) $today->diffInDays($end) : null;
            if ($daysLeft !== null && $daysLeft <= 30) {
                $badge = ['label' => "Sisa {$daysLeft} hari", 'variant' => 'danger'];
            } else {
                $badge = ['label' => 'Sedang berjalan', 'variant' => 'success'];
            }
        }

        return [
            'filled' => true,
            'range' => $range,
            'duration' => $duration,
            'badge' => $badge,
        ];
    };

    $contract1 = $contractCardMeta($employee->contract_1_start, $employee->contract_1_end);
    $contract2 = $contractCardMeta($employee->contract_2_start, $employee->contract_2_end);

    $officialDocs = [
        ['label' => 'No. KTP (NIK)', 'value' => $employee->ktp_no, 'icon' => 'shield'],
        ['label' => 'No. NPWP', 'value' => $employee->npwp_no, 'icon' => 'file-text'],
        ['label' => 'BPJS Ketenagakerjaan', 'value' => $employee->bpjs_tk_no, 'icon' => 'briefcase'],
        ['label' => 'BPJS Kesehatan', 'value' => $employee->bpjs_kes_no, 'icon' => 'user-check'],
    ];
@endphp

<x-layouts.app :title="$employee->name">
    <div
        x-data="{
            tab: 'profil',
            copied: null,
            copyText(key, value) {
                if (!value) return;
                navigator.clipboard?.writeText(value);
                this.copied = key;
                setTimeout(() => { if (this.copied === key) this.copied = null; }, 1600);
            }
        }"
        class="space-y-5"
    >
        <section class="relative overflow-hidden rounded-3xl border border-gold/20 bg-[#0c1e38] text-white shadow-card">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(217,180,106,0.18),transparent_55%)]"></div>
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_left,rgba(20,53,102,0.5),transparent_55%)]"></div>
            <div class="pattern-grid pointer-events-none absolute inset-0 opacity-20"></div>

            <div class="relative px-5 pb-6 pt-5 sm:px-7 sm:pb-7 sm:pt-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <a
                        href="{{ route('employees.index') }}"
                        class="inline-flex items-center gap-1.5 rounded-full border border-white/15 bg-white/[0.06] px-3.5 py-1.5 text-[12px] font-medium text-white/85 transition hover:border-gold/40 hover:bg-white/10 hover:text-white"
                    >
                        <x-icon name="chevron-left" class="size-3.5" />
                        Daftar Karyawan
                    </a>

                    <div class="flex flex-wrap items-center gap-2">
                        <a
                            href="{{ route('employees.edit', $employee) }}"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-white/20 bg-white/10 px-3.5 py-2 text-[12.5px] font-semibold text-white transition hover:bg-white/20"
                        >
                            <x-icon name="pencil" class="size-4" />
                            Edit Data
                        </a>
                        <a
                            href="{{ route('transfers.create', ['employee' => $employee->id]) }}"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-gold px-3.5 py-2 text-[12.5px] font-bold text-[#1d150a] shadow-sm transition hover:bg-gold-strong"
                        >
                            <x-icon name="transfer" class="size-4" />
                            Mutasi Karyawan
                        </a>
                    </div>
                </div>

                <div class="mt-6 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                        <div class="flex size-16 shrink-0 items-center justify-center rounded-2xl border border-gold/40 bg-white/10 font-display text-[22px] font-semibold text-gold shadow-sm sm:size-[72px] sm:text-[26px]">
                            {{ collect(explode(' ', trim($employee->name)))->filter()->take(2)->map(fn ($w) => strtoupper(mb_substr($w, 0, 1)))->implode('') }}
                        </div>

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h1 class="font-display text-[24px] font-semibold leading-tight tracking-tight text-white sm:text-[28px]">
                                    {{ $employee->name }}
                                </h1>
                                <span class="inline-flex items-center rounded-full border border-gold/40 bg-gold/15 px-2.5 py-0.5 text-[11px] font-semibold text-gold">
                                    {{ $employee->employmentStatusLabel() }}
                                </span>
                                @if (! $employee->is_active)
                                    <span class="inline-flex items-center rounded-full border border-rose-400/40 bg-rose-500/20 px-2.5 py-0.5 text-[11px] font-semibold text-rose-200">
                                        Nonaktif
                                    </span>
                                @endif
                            </div>

                            <p class="mt-1.5 text-[14px] font-medium text-blue-100/90">
                                {{ $employee->position->name }}
                                <span class="mx-1.5 text-gold/60">·</span>
                                {{ $employee->department->name }}
                            </p>

                            <p class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-[12.5px] text-white/65">
                                <span class="inline-flex items-center gap-1.5">
                                    <x-icon name="building" class="size-3.5 text-gold/80" />
                                    {{ $employee->company->name }}
                                </span>
                                @if ($employee->branch)
                                    <span class="text-white/30">·</span>
                                    <span class="inline-flex items-center gap-1">
                                        <x-icon name="map-pin" class="size-3.5 text-gold/80" />
                                        {{ $employee->branch->name }}
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @if ($waNumber)
                            <a
                                href="https://wa.me/{{ $waNumber }}"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-400/30 bg-emerald-500/15 px-3 py-2 text-[12px] font-semibold text-emerald-200 transition hover:bg-emerald-500/25"
                            >
                                <x-icon name="message-circle" class="size-4" />
                                WhatsApp
                            </a>
                        @endif
                        @if ($employee->phone)
                            <a
                                href="tel:{{ preg_replace('/[^0-9+]/', '', $employee->phone) }}"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-white/15 bg-white/[0.06] px-3 py-2 text-[12px] font-medium text-white/85 transition hover:bg-white/15"
                            >
                                <x-icon name="phone" class="size-4 text-gold/80" />
                                Telepon
                            </a>
                        @endif
                        @if ($hasRealEmail)
                            <a
                                href="mailto:{{ $employee->email }}"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-white/15 bg-white/[0.06] px-3 py-2 text-[12px] font-medium text-white/85 transition hover:bg-white/15"
                            >
                                <x-icon name="mail" class="size-4 text-gold/80" />
                                Email
                            </a>
                        @endif
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3 border-t border-white/10 pt-5 lg:grid-cols-4">
                    <div class="rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-3">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-[10.5px] font-semibold uppercase tracking-[0.12em] text-white/50">No. Staff</p>
                            <button
                                type="button"
                                @click="copyText('nik', @js($employee->nik))"
                                class="text-[10.5px] font-medium text-gold/80 transition hover:text-gold"
                                title="Salin No. Staff"
                            >
                                <span x-show="copied !== 'nik'">Salin</span>
                                <span x-show="copied === 'nik'" x-cloak>Disalin ✓</span>
                            </button>
                        </div>
                        <p class="mt-1 truncate text-[14px] font-bold tabular-nums text-white">{{ $employee->nik }}</p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-3">
                        <p class="text-[10.5px] font-semibold uppercase tracking-[0.12em] text-white/50">Bergabung</p>
                        <p class="mt-1 text-[14px] font-bold text-white">{{ $employee->joined_at->translatedFormat('d M Y') }}</p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-3">
                        <p class="text-[10.5px] font-semibold uppercase tracking-[0.12em] text-white/50">Masa Kerja</p>
                        <p class="mt-1 text-[14px] font-bold text-gold">{{ $tenureText }}</p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-3">
                        <p class="text-[10.5px] font-semibold uppercase tracking-[0.12em] text-white/50">Status Kontrak</p>
                        <p class="mt-1 truncate text-[14px] font-bold text-white">
                            @if ($employee->employment_status === 'permanent')
                                Karyawan Tetap
                            @elseif ($employee->activeContractLabel())
                                {{ $employee->activeContractLabel() }} Aktif
                            @else
                                {{ $employee->employmentStatusLabel() }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-5 lg:grid-cols-3 lg:items-start">
            <div class="space-y-4 lg:col-span-2">
                <div class="flex flex-wrap gap-1.5 rounded-2xl border border-line bg-surface p-1.5 shadow-card">
                    <button
                        type="button"
                        @click="tab = 'profil'"
                        :class="tab === 'profil' ? 'bg-brand text-brand-ink shadow-sm' : 'text-muted hover:bg-surface-2 hover:text-ink'"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl px-3.5 py-2.5 text-[13px] font-semibold transition sm:flex-none"
                    >
                        <x-icon name="user" class="size-4" />
                        Profil & Identitas
                    </button>

                    <button
                        type="button"
                        @click="tab = 'kontrak'"
                        :class="tab === 'kontrak' ? 'bg-brand text-brand-ink shadow-sm' : 'text-muted hover:bg-surface-2 hover:text-ink'"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl px-3.5 py-2.5 text-[13px] font-semibold transition sm:flex-none"
                    >
                        <x-icon name="file-text" class="size-4" />
                        Kontrak & Catatan
                    </button>

                    <button
                        type="button"
                        @click="tab = 'mutasi'"
                        :class="tab === 'mutasi' ? 'bg-brand text-brand-ink shadow-sm' : 'text-muted hover:bg-surface-2 hover:text-ink'"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl px-3.5 py-2.5 text-[13px] font-semibold transition sm:flex-none"
                    >
                        <x-icon name="transfer" class="size-4" />
                        Riwayat Mutasi
                        <span
                            :class="tab === 'mutasi' ? 'bg-white/20 text-white' : 'bg-surface-2 text-muted'"
                            class="rounded-full px-2 py-0.5 text-[11px] font-bold tabular-nums"
                        >
                            {{ $employee->transfers->count() }}
                        </span>
                    </button>
                </div>

                <div x-show="tab === 'profil'" class="space-y-4">
                    <x-ui.card title="Dokumen & Identitas Resmi" description="Nomor identitas resmi yang tersimpan untuk administrasi pajak dan BPJS.">
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ($officialDocs as $idx => $doc)
                                <div class="flex items-start justify-between gap-3 rounded-2xl border border-line/80 bg-surface-2/40 p-3.5">
                                    <div class="flex min-w-0 items-start gap-3">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-surface text-brand shadow-xs">
                                            <x-icon :name="$doc['icon']" class="size-4" />
                                        </span>
                                        <div class="min-w-0">
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.06em] text-subtle">{{ $doc['label'] }}</p>
                                            <p class="mt-0.5 break-all text-[13.5px] font-semibold tabular-nums text-ink">{{ $doc['value'] ?: '—' }}</p>
                                        </div>
                                    </div>

                                    @if ($doc['value'])
                                        <button
                                            type="button"
                                            @click="copyText('doc-{{ $idx }}', @js($doc['value']))"
                                            class="shrink-0 rounded-lg p-1.5 text-subtle transition hover:bg-surface hover:text-brand"
                                            title="Salin {{ $doc['label'] }}"
                                        >
                                            <x-icon name="copy" class="size-4" x-show="copied !== 'doc-{{ $idx }}'" />
                                            <x-icon name="check" class="size-4 text-emerald-600" x-show="copied === 'doc-{{ $idx }}'" x-cloak />
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <dl class="mt-5 grid gap-4 border-t border-line/80 pt-5 text-[13px] sm:grid-cols-2">
                            <div>
                                <dt class="text-[11.5px] font-medium text-subtle">Jenis Kelamin</dt>
                                <dd class="mt-1 font-semibold text-ink">{{ $employee->genderLabel() ?: '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-[11.5px] font-medium text-subtle">Tempat, Tanggal Lahir</dt>
                                <dd class="mt-1 font-semibold text-ink">
                                    {{ $employee->birthLabel() ?: '—' }}
                                    @if ($ageText)
                                        <span class="ml-1.5 rounded-full bg-surface-2 px-2 py-0.5 text-[11px] font-medium text-muted">{{ $ageText }}</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-[11.5px] font-medium text-subtle">Agama</dt>
                                <dd class="mt-1 font-semibold text-ink">{{ $employee->religion ?: '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-[11.5px] font-medium text-subtle">Status Pernikahan</dt>
                                <dd class="mt-1 font-semibold text-ink">{{ $employee->marital_status ?: '—' }}</dd>
                            </div>
                        </dl>
                    </x-ui.card>

                    <x-ui.card title="Kontak & Alamat Tinggal" description="Informasi komunikasi harian dan kontak darurat karyawan.">
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl border border-line/80 bg-surface-2/35 p-3.5">
                                <p class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.06em] text-subtle">
                                    <x-icon name="phone" class="size-3.5 text-brand" />
                                    No. Handphone
                                </p>
                                <p class="mt-1.5 break-words text-[13px] font-semibold text-ink">{{ $employee->phone ?: '—' }}</p>
                            </div>

                            <div class="rounded-2xl border border-line/80 bg-surface-2/35 p-3.5">
                                <p class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.06em] text-subtle">
                                    <x-icon name="mail" class="size-3.5 text-brand" />
                                    Email
                                </p>
                                <p class="mt-1.5 break-all text-[13px] font-semibold text-ink">{{ $hasRealEmail ? $employee->email : 'Belum diisi' }}</p>
                            </div>

                            <div class="rounded-2xl border border-line/80 bg-surface-2/35 p-3.5">
                                <p class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.06em] text-subtle">
                                    <x-icon name="alert-circle" class="size-3.5 text-amber-600" />
                                    Kontak Darurat
                                </p>
                                <p class="mt-1.5 break-words text-[13px] font-semibold text-ink">{{ $employee->emergency_phone ?: '—' }}</p>
                            </div>
                        </div>

                        <div class="mt-3 rounded-2xl border border-line/80 bg-surface-2/35 p-4">
                            <p class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.06em] text-subtle">
                                <x-icon name="map-pin" class="size-3.5 text-brand" />
                                Alamat Tempat Tinggal
                            </p>
                            <p class="mt-1.5 text-[13.5px] leading-relaxed font-medium text-ink">{{ $employee->address ?: 'Alamat belum diisi' }}</p>
                        </div>
                    </x-ui.card>

                    @php
                        $daughterList = $employee->daughterNamesList();
                        $sonList = $employee->sonNamesList();
                    @endphp
                    <x-ui.card title="Informasi Keluarga" description="Data pasangan, orang tua, serta nama dan jumlah anak.">
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl border border-line/80 bg-surface-2/35 p-3.5">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.06em] text-subtle">Istri / Suami</p>
                                <p class="mt-1 text-[13.5px] font-semibold text-ink">{{ $employee->spouse_name ?: '—' }}</p>
                            </div>
                            <div class="rounded-2xl border border-line/80 bg-surface-2/35 p-3.5">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.06em] text-subtle">Nama Ayah</p>
                                <p class="mt-1 text-[13.5px] font-semibold text-ink">{{ $employee->father_name ?: '—' }}</p>
                            </div>
                            <div class="rounded-2xl border border-line/80 bg-surface-2/35 p-3.5">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.06em] text-subtle">Nama Ibu</p>
                                <p class="mt-1 text-[13.5px] font-semibold text-ink">{{ $employee->mother_name ?: '—' }}</p>
                            </div>
                        </div>

                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-2xl border border-line/80 bg-surface-2/35 p-3.5">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.06em] text-subtle">Anak Perempuan</p>
                                    <span class="rounded-full bg-surface px-2 py-0.5 text-[11px] font-bold tabular-nums text-muted">
                                        {{ max((int) $employee->daughters_count, count($daughterList)) }}
                                    </span>
                                </div>
                                @if (! empty($daughterList))
                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                        @foreach ($daughterList as $childName)
                                            <span class="inline-flex items-center rounded-lg border border-line bg-surface px-2.5 py-1 text-[12.5px] font-medium text-ink">
                                                {{ $childName }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="mt-1.5 text-[13px] text-subtle">{{ $employee->daughters_count > 0 ? $employee->daughters_count . ' anak (nama belum diisi)' : '—' }}</p>
                                @endif
                            </div>

                            <div class="rounded-2xl border border-line/80 bg-surface-2/35 p-3.5">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.06em] text-subtle">Anak Laki-Laki</p>
                                    <span class="rounded-full bg-surface px-2 py-0.5 text-[11px] font-bold tabular-nums text-muted">
                                        {{ max((int) $employee->sons_count, count($sonList)) }}
                                    </span>
                                </div>
                                @if (! empty($sonList))
                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                        @foreach ($sonList as $childName)
                                            <span class="inline-flex items-center rounded-lg border border-line bg-surface px-2.5 py-1 text-[12.5px] font-medium text-ink">
                                                {{ $childName }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="mt-1.5 text-[13px] text-subtle">{{ $employee->sons_count > 0 ? $employee->sons_count . ' anak (nama belum diisi)' : '—' }}</p>
                                @endif
                            </div>
                        </div>
                    </x-ui.card>
                </div>

                <div x-show="tab === 'kontrak'" x-cloak class="space-y-4">
                    <x-ui.card title="Masa Kontrak Kerja" description="Ringkasan periode Kontrak I dan Kontrak II yang tercatat di sistem.">
                        <div class="grid gap-3.5 sm:grid-cols-2">
                            <div class="rounded-2xl border border-line bg-surface-2/35 p-4">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-[11.5px] font-bold uppercase tracking-[0.08em] text-muted">Kontrak I</span>
                                    <x-ui.badge :variant="$contract1['badge']['variant']">{{ $contract1['badge']['label'] }}</x-ui.badge>
                                </div>
                                <p class="mt-3 text-[15px] font-semibold text-ink">{{ $contract1['range'] }}</p>
                                <p class="mt-1 text-[12px] text-subtle">
                                    {{ $contract1['duration'] ? 'Durasi: ' . $contract1['duration'] : 'Periode kontrak pertama' }}
                                </p>
                            </div>

                            <div class="rounded-2xl border border-line bg-surface-2/35 p-4">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-[11.5px] font-bold uppercase tracking-[0.08em] text-muted">Kontrak II</span>
                                    <x-ui.badge :variant="$contract2['badge']['variant']">{{ $contract2['badge']['label'] }}</x-ui.badge>
                                </div>
                                <p class="mt-3 text-[15px] font-semibold text-ink">{{ $contract2['range'] }}</p>
                                <p class="mt-1 text-[12px] text-subtle">
                                    {{ $contract2['duration'] ? 'Durasi: ' . $contract2['duration'] : 'Periode perpanjangan kontrak' }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 rounded-2xl border border-line/80 bg-surface-2/30 p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-subtle">Keterangan / Catatan HRD</p>
                            <p class="mt-1.5 text-[13.5px] leading-relaxed text-ink">
                                {{ $employee->note ?: 'Tidak ada catatan khusus untuk karyawan ini.' }}
                            </p>
                        </div>
                    </x-ui.card>
                </div>

                <div x-show="tab === 'mutasi'" x-cloak>
                    <x-ui.card :padded="false">
                        <div class="flex flex-wrap items-center justify-between gap-2 px-5 py-4 sm:px-6">
                            <div>
                                <h2 class="text-[15px] font-semibold">Riwayat Mutasi</h2>
                                <p class="mt-0.5 text-[12.5px] text-muted">Perpindahan PT, cabang, departemen, dan jabatan karyawan</p>
                            </div>
                            <x-ui.button size="sm" icon="transfer" :href="route('transfers.create', ['employee' => $employee->id])">Catat Mutasi</x-ui.button>
                        </div>

                        <div class="border-t border-line px-5 py-5 sm:px-6">
                            @if ($employee->transfers->isEmpty())
                                <x-ui.empty-state
                                    icon="transfer"
                                    title="Belum ada mutasi tercatat"
                                    description="Karyawan ini belum memiliki riwayat perpindahan PT, cabang, atau jabatan di sistem."
                                >
                                    <x-slot:actions>
                                        <x-ui.button variant="secondary" icon="transfer" :href="route('transfers.create', ['employee' => $employee->id])">Mutasi karyawan ini</x-ui.button>
                                    </x-slot:actions>
                                </x-ui.empty-state>
                            @else
                                <ol class="relative space-y-6 border-l border-line pl-6">
                                    @foreach ($employee->transfers as $transfer)
                                        <li class="relative">
                                            <span class="absolute -left-[31px] top-1 flex size-[18px] items-center justify-center rounded-full border-2 border-surface bg-brand">
                                                <x-icon name="transfer" class="size-2.5 text-brand-ink" />
                                            </span>

                                            <div class="flex flex-wrap items-center gap-2">
                                                <p class="text-[13px] font-semibold">{{ $transfer->effective_date->translatedFormat('d F Y') }}</p>
                                                <span class="rounded-md bg-surface-2 px-1.5 py-0.5 text-[11px] font-semibold text-muted">
                                                    {{ $transfer->fromCompany->code }} → {{ $transfer->toCompany->code }}
                                                </span>
                                                <x-ui.badge :variant="$transfer->position_changed ? 'warning' : 'neutral'">
                                                    {{ $transfer->position_changed ? 'Jabatan berubah' : 'Jabatan tetap' }}
                                                </x-ui.badge>
                                            </div>

                                            <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                                <div class="rounded-xl border border-line bg-surface-2/40 p-3">
                                                    <p class="text-[10.5px] font-semibold uppercase tracking-[0.08em] text-subtle">Sebelum</p>
                                                    <p class="mt-1 text-[12.5px] font-medium">{{ $transfer->fromCompany->name }}</p>
                                                    <p class="text-[12px] text-muted">{{ $transfer->fromBranch?->name ?? 'Tanpa cabang' }}</p>
                                                    <p class="text-[12px] text-muted">{{ $transfer->fromDepartment->name }} · {{ $transfer->fromPosition->name }}</p>
                                                </div>

                                                <div class="rounded-xl border border-brand-line bg-brand-soft/50 p-3">
                                                    <p class="text-[10.5px] font-semibold uppercase tracking-[0.08em] text-brand">Sesudah</p>
                                                    <p class="mt-1 text-[12.5px] font-medium">{{ $transfer->toCompany->name }}</p>
                                                    <p class="text-[12px] text-muted">{{ $transfer->toBranch?->name ?? 'Tanpa cabang' }}</p>
                                                    <p class="text-[12px] text-muted">{{ $transfer->toDepartment->name }} · {{ $transfer->toPosition->name }}</p>
                                                </div>
                                            </div>

                                            @if ($transfer->reason || $transfer->notes)
                                                <p class="mt-2 text-[12px] text-muted">
                                                    @if ($transfer->reason)
                                                        <span class="font-medium text-ink">Alasan:</span> {{ $transfer->reason }}
                                                    @endif
                                                    @if ($transfer->notes)
                                                        <span class="text-subtle">— {{ $transfer->notes }}</span>
                                                    @endif
                                                </p>
                                            @endif
                                        </li>
                                    @endforeach
                                </ol>
                            @endif
                        </div>
                    </x-ui.card>
                </div>
            </div>

            <aside class="space-y-4 lg:sticky lg:top-24">
                <x-ui.card title="Penempatan Saat Ini" description="Struktur organisasi aktif karyawan.">
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-brand-soft text-brand-soft-ink">
                                <x-icon name="building" class="size-4" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-[11px] font-medium text-subtle">Perusahaan (PT)</p>
                                <p class="text-[13px] font-semibold text-ink">{{ $employee->company->name }}</p>
                                <p class="text-[11px] text-muted">Kode: {{ $employee->company->code }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-surface-2 text-muted">
                                <x-icon name="map-pin" class="size-4" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-[11px] font-medium text-subtle">Cabang</p>
                                <p class="text-[13px] font-semibold text-ink">{{ $employee->branch?->name ?? 'Kantor Pusat / Tanpa cabang' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-surface-2 text-muted">
                                <x-icon name="sitemap" class="size-4" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-[11px] font-medium text-subtle">Departemen</p>
                                <p class="text-[13px] font-semibold text-ink">{{ $employee->department->name }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-surface-2 text-muted">
                                <x-icon name="briefcase" class="size-4" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-[11px] font-medium text-subtle">Jabatan</p>
                                <p class="text-[13px] font-semibold text-ink">{{ $employee->position->name }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 space-y-2.5 border-t border-line pt-4 text-[13px]">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-subtle">Status Kepegawaian</span>
                            <x-ui.badge :variant="$employee->employmentStatusTone()">{{ $employee->employmentStatusLabel() }}</x-ui.badge>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-subtle">Tanggal Bergabung</span>
                            <span class="font-semibold text-ink">{{ $employee->joined_at->translatedFormat('d F Y') }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-subtle">Gaji Bulanan</span>
                            <span class="font-semibold tabular-nums text-ink">{{ $employee->salary ? \App\Support\Currency::rupiah($employee->salary) : '—' }}</span>
                        </div>
                    </div>
                </x-ui.card>

                @if ($employee->note)
                    <div class="rounded-2xl border border-gold/30 bg-gold-soft/40 p-4 text-[12.5px] leading-relaxed dark:border-gold/25 dark:bg-gold/10">
                        <p class="mb-1.5 flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-[0.08em] text-gold-strong dark:text-gold">
                            <x-icon name="file-text" class="size-3.5" />
                            Catatan Penting
                        </p>
                        <p class="text-ink">{{ $employee->note }}</p>
                    </div>
                @endif

                <div class="rounded-2xl border border-line bg-surface-2/50 p-4 text-[12.5px] leading-relaxed text-muted">
                    <p class="mb-1.5 flex items-center gap-2 font-semibold text-ink">
                        <x-icon name="alert-circle" class="size-4 text-brand" />
                        Tentang Mutasi
                    </p>
                    <p>Mutasi langsung memperbarui penempatan karyawan dan menyimpan riwayat sebelum & sesudah secara otomatis.</p>
                </div>
            </aside>
        </div>
    </div>
</x-layouts.app>
