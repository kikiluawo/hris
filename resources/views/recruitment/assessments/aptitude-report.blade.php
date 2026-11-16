@props(['report'])

@php
    $iq = $report['estimated_iq'] ?? 100;
    // Persentase posisi pada barometer IQ (skala 60 s/d 145)
    $iqPositionPct = max(2, min(98, (($iq - 60) / 85) * 100));
@endphp

<div class="space-y-4">
    
    <div class="relative overflow-hidden rounded-2xl border border-line bg-surface p-6 shadow-sm">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-soft px-3 py-1 text-[11.5px] font-bold text-brand-soft-ink">
                        <x-icon name="sparkles" class="size-3.5" />
                        TES POTENSI KOGNITIF & BAKAT SKOLASTIK
                    </span>
                    <span class="rounded-full border border-line bg-surface-2 px-2.5 py-0.5 text-[11.5px] font-medium text-muted">
                        Norma Psikometri Baku (SD 15)
                    </span>
                </div>
                <h3 class="text-xl font-bold tracking-tight text-ink sm:text-2xl">
                    Estimasi Kapasitas Intelektual (IQ)
                </h3>
                <p class="max-w-2xl text-[13px] leading-relaxed text-subtle">
                    {{ $report['iq_description'] }}
                </p>
            </div>

            <div class="flex shrink-0 items-center gap-4 rounded-2xl border border-brand/20 bg-brand-soft/40 p-4 sm:p-5">
                <div class="text-center">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-brand">Estimasi IQ</p>
                    <p class="text-4xl font-extrabold tracking-tight text-brand sm:text-5xl">{{ $iq }}</p>
                </div>
                <div class="h-12 w-px bg-brand/20"></div>
                <div class="space-y-1">
                    <x-ui.badge :variant="$report['iq_tone'] ?? 'brand'" class="text-[12px] font-semibold">
                        {{ $report['iq_classification'] }}
                    </x-ui.badge>
                    <p class="text-[11.5px] text-muted">{{ $report['iq_percentile'] }}</p>
                    <p class="text-[11px] text-subtle font-medium">Akurasi {{ $report['accuracy_percent'] }}% ({{ $report['correct_count'] }}/{{ $report['total_questions'] }} benar)</p>
                </div>
            </div>
        </div>

        <div class="mt-6 border-t border-line/70 pt-5">
            <div class="flex items-center justify-between text-[11px] font-semibold text-muted">
                <span>Skala Distribusi Normal IQ</span>
                <span class="tabular-nums">Skor Kandidat: <strong class="text-ink">{{ $iq }}</strong></span>
            </div>

            <div class="relative mt-2 h-4 w-full">
                <div class="absolute -top-1 -translate-x-1/2 transition-all duration-500" style="left: {{ $iqPositionPct }}%;">
                    <div class="flex flex-col items-center">
                        <span class="rounded bg-ink px-1.5 py-0.5 text-[10px] font-bold text-surface shadow">{{ $iq }}</span>
                        <div class="size-0 border-x-4 border-t-4 border-x-transparent border-t-ink"></div>
                    </div>
                </div>
            </div>

            <div class="relative mt-1 flex h-4 w-full overflow-hidden rounded-full border border-line bg-surface-2 text-[9px] font-bold text-white shadow-inner">
                
                <div style="width: 23.5%;" class="flex items-center justify-center bg-rose-500/80 transition" title="Batas Bawah (< 80)">
                    <span class="hidden sm:inline">&lt; 80</span>
                </div>
                
                <div style="width: 11.7%;" class="flex items-center justify-center bg-amber-500/85 transition" title="Di Bawah Rata-Rata (80 - 89)">
                    <span class="hidden sm:inline">80-89</span>
                </div>
                
                <div style="width: 23.5%;" class="flex items-center justify-center bg-slate-400 dark:bg-slate-500 transition" title="Rata-Rata (90 - 109)">
                    <span>90 - 109</span>
                </div>
                
                <div style="width: 11.7%;" class="flex items-center justify-center bg-sky-500/90 transition" title="Di Atas Rata-Rata (110 - 119)">
                    <span class="hidden sm:inline">110-119</span>
                </div>
                
                <div style="width: 11.7%;" class="flex items-center justify-center bg-indigo-500/90 transition" title="Unggul (120 - 129)">
                    <span class="hidden sm:inline">120-129</span>
                </div>
                
                <div style="width: 17.9%;" class="flex items-center justify-center bg-emerald-500 transition" title="Sangat Unggul (130+)">
                    <span>130+</span>
                </div>
            </div>

            <div class="mt-2 grid grid-cols-6 gap-1 text-center text-[10px] text-muted">
                <div>Batas Bawah</div>
                <div>Low Avg</div>
                <div class="font-bold text-ink">Rata-Rata (Median)</div>
                <div>High Avg</div>
                <div>Superior</div>
                <div class="font-bold text-emerald-600 dark:text-emerald-400">Very Superior</div>
            </div>
        </div>
    </div>

    <div>
        <div class="mb-3 flex items-center justify-between">
            <h4 class="text-[14px] font-bold tracking-tight text-ink">Pemetaan 4 Dimensi Kognitif</h4>
            <span class="text-[11.5px] text-subtle">Akurasi per sub-bidang kemampuan</span>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($report['domains'] as $key => $domain)
                @php
                    $pct = $domain['accuracy_percent'];
                    $barColor = match ($domain['status_tone']) {
                        'success' => 'bg-emerald-500',
                        'primary' => 'bg-sky-500',
                        'warning' => 'bg-amber-500',
                        default => 'bg-rose-500',
                    };
                @endphp
                <div class="flex flex-col justify-between rounded-xl border border-line bg-surface p-4 shadow-sm transition hover:border-brand/40">
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="flex size-8 items-center justify-center rounded-lg bg-surface-2 text-ink">
                                <x-icon :name="$domain['icon']" class="size-4 text-brand" />
                            </span>
                            <x-ui.badge :variant="$domain['status_tone']" class="text-[10.5px]">
                                {{ $domain['status_label'] }}
                            </x-ui.badge>
                        </div>

                        <h5 class="mt-3 text-[13.5px] font-bold text-ink">{{ $domain['name'] }}</h5>
                        <p class="mt-1 text-[11.5px] leading-relaxed text-subtle">{{ $domain['description'] }}</p>
                    </div>

                    <div class="mt-4 border-t border-line/60 pt-3">
                        <div class="flex items-center justify-between text-[12px]">
                            <span class="font-semibold text-muted">{{ $domain['correct'] }}/{{ $domain['total'] }} Benar</span>
                            <span class="font-bold tabular-nums text-ink">{{ $pct }}%</span>
                        </div>
                        <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-surface-2">
                            <div class="h-full rounded-full {{ $barColor }} transition-all duration-500" style="width: {{ $pct }}%;"></div>
                        </div>
                        <p class="mt-2 text-[10.5px] italic text-muted">{{ $domain['insight'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        
        <div class="rounded-xl border border-line bg-surface p-5 shadow-sm">
            <div class="flex items-center gap-2">
                <span class="flex size-7 items-center justify-center rounded-lg bg-brand-soft text-brand-soft-ink">
                    <x-icon name="user" class="size-4" />
                </span>
                <h4 class="text-[14px] font-bold text-ink">Karakteristik Gaya Berpikir</h4>
            </div>

            <dl class="mt-4 space-y-3 text-[12.5px]">
                <div class="flex items-start justify-between gap-3 border-b border-line/50 pb-2.5">
                    <dt class="text-muted">Kecepatan Belajar (Learning Speed):</dt>
                    <dd class="text-right font-semibold text-ink">{{ $report['cognitive_profile']['learning_speed'] }}</dd>
                </div>
                <div class="flex items-start justify-between gap-3 border-b border-line/50 pb-2.5">
                    <dt class="text-muted">Problem Solving & Troubleshooting:</dt>
                    <dd class="text-right font-semibold text-ink">{{ $report['cognitive_profile']['problem_solving'] }}</dd>
                </div>
                <div class="flex items-start justify-between gap-3 border-b border-line/50 pb-2.5">
                    <dt class="text-muted">Domain Paling Kuat:</dt>
                    <dd class="text-right font-semibold text-emerald-600 dark:text-emerald-400">
                        {{ $report['cognitive_profile']['strongest_domain'] }}
                    </dd>
                </div>
                <div class="flex items-start justify-between gap-3">
                    <dt class="text-muted">Area Butuh Supervisi/Pengembangan:</dt>
                    <dd class="text-right font-semibold text-amber-600 dark:text-amber-400">
                        {{ $report['cognitive_profile']['area_for_growth'] }}
                    </dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl border border-line bg-surface p-5 shadow-sm">
            <div class="flex items-center gap-2">
                <span class="flex size-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                    <x-icon name="briefcase" class="size-4" />
                </span>
                <h4 class="text-[14px] font-bold text-ink">Rekomendasi Penempatan Kerja</h4>
            </div>

            <p class="mt-3 text-[12.5px] leading-relaxed text-subtle">
                {{ $report['hr_summary'] }}
            </p>

            <div class="mt-4 border-t border-line/60 pt-3">
                <p class="text-[11.5px] font-semibold uppercase tracking-wider text-muted">Posisi / Bidang yang Sangat Sesuai:</p>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    @foreach ($report['recommended_roles'] as $role)
                        <span class="rounded-lg border border-line bg-surface-2 px-2.5 py-1 text-[11.5px] font-medium text-ink">
                            {{ $role }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
