@props(['report'])

@php
    $accuracy = $report['accuracy_rate'] ?? 0;
    $quadrant = $report['quadrant'] ?? [];
    $stability = $report['stability'] ?? [];
    $risk = $report['risk_profile'] ?? [];
@endphp

<div class="space-y-4">
    
    <div class="relative overflow-hidden rounded-2xl border border-line bg-surface p-6 shadow-sm">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-[11.5px] font-bold text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-300">
                        <x-icon name="check" class="size-3.5" />
                        TES KETELITIAN KERJA & VERIFIKASI DATA
                    </span>
                    <span class="rounded-full border border-line bg-surface-2 px-2.5 py-0.5 text-[11.5px] font-medium text-muted">
                        Clerical Checking Test
                    </span>
                </div>
                <h3 class="text-xl font-bold tracking-tight text-ink sm:text-2xl">
                    Profil Ketelitian & Sikap Kerja
                </h3>
                <p class="max-w-2xl text-[13px] leading-relaxed text-subtle">
                    {{ $quadrant['description'] ?? '' }}
                </p>
            </div>

            <div class="flex shrink-0 items-center gap-4 rounded-2xl border border-line bg-surface-2/60 p-4 sm:p-5">
                <div class="text-center">
                    <span class="inline-block rounded-xl bg-ink px-3 py-1 text-[12px] font-bold text-surface shadow-sm">
                        {{ $quadrant['code'] ?? 'Q1' }}
                    </span>
                    <p class="mt-1.5 text-[10.5px] font-bold uppercase tracking-wider text-muted">Kuadran</p>
                </div>
                <div class="h-12 w-px bg-line"></div>
                <div class="space-y-1">
                    <x-ui.badge :variant="$quadrant['tone'] ?? 'brand'" class="text-[12px] font-semibold">
                        {{ $quadrant['title'] ?? '' }}
                    </x-ui.badge>
                    <p class="text-[11.5px] font-medium text-subtle">{{ $quadrant['subtitle'] ?? '' }}</p>
                    <p class="text-[11px] font-medium text-muted">Akurasi Murni <strong class="text-ink">{{ $accuracy }}%</strong> ({{ $report['correct_count'] }}/{{ $report['attempted_count'] }} benar)</p>
                </div>
            </div>
        </div>

        <div class="mt-6 grid gap-3 border-t border-line/70 pt-5 sm:grid-cols-2 xl:grid-cols-4">
            
            <div class="rounded-xl border border-line bg-surface-2/30 p-3.5">
                <p class="text-[11px] font-semibold text-muted">Tingkat Ketelitian Murni</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold tabular-nums text-ink">{{ $accuracy }}%</span>
                    <x-ui.badge :variant="$report['accuracy_evaluation']['tone'] ?? 'brand'" class="text-[10px]">
                        {{ $report['accuracy_evaluation']['badge'] ?? '' }}
                    </x-ui.badge>
                </div>
                <p class="mt-1 text-[10.5px] text-subtle">{{ $report['accuracy_evaluation']['label'] }}</p>
            </div>

            <div class="rounded-xl border border-line bg-surface-2/30 p-3.5">
                <p class="text-[11px] font-semibold text-muted">Kecepatan Pemrosesan Data</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold tabular-nums text-ink">{{ $report['speed_per_minute'] }}</span>
                    <span class="text-[11px] font-medium text-muted">soal / menit</span>
                </div>
                <p class="mt-1 text-[10.5px] text-subtle">{{ $report['speed_evaluation']['label'] }}</p>
            </div>

            <div class="rounded-xl border border-line bg-surface-2/30 p-3.5">
                <p class="text-[11px] font-semibold text-muted">Kekeliruan / Salah Deteksi</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold tabular-nums {{ $report['error_count'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                        {{ $report['error_count'] }}
                    </span>
                    <span class="text-[11px] font-medium text-muted">kesalahan</span>
                </div>
                <p class="mt-1 text-[10.5px] text-subtle">{{ $report['error_count'] === 0 ? 'Zero defect (sempurna)' : 'Perlu pemeriksaan silang' }}</p>
            </div>

            <div class="rounded-xl border border-line bg-surface-2/30 p-3.5">
                <p class="text-[11px] font-semibold text-muted">Penyelesaian Target Soal</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold tabular-nums text-ink">{{ $report['completion_rate'] }}%</span>
                    <span class="text-[11px] font-medium text-muted">({{ $report['attempted_count'] }}/{{ $report['total_items'] }})</span>
                </div>
                <p class="mt-1 text-[10.5px] text-subtle">Selesai dalam {{ $report['duration_minutes_spent'] }} menit</p>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-line bg-surface p-5 shadow-sm">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2">
                <span class="flex size-7 items-center justify-center rounded-lg bg-brand-soft text-brand-soft-ink">
                    <x-icon name="chart-bar" class="size-4" />
                </span>
                <div>
                    <h4 class="text-[14px] font-bold text-ink">Kestabilan Kerja & Daya Tahan Konsentrasi</h4>
                    <p class="text-[11.5px] text-subtle">Memantau konsistensi ketelitian dari awal hingga akhir untuk mendeteksi kelelahan mental (*mental fatigue*)</p>
                </div>
            </div>
            <x-ui.badge :variant="$stability['status_tone'] ?? 'neutral'" class="self-start sm:self-auto">
                {{ $stability['status_label'] ?? 'Stabil' }}
            </x-ui.badge>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-3">
            @foreach ($stability['segments'] ?? [] as $key => $phase)
                @php
                    $phasePct = $phase['accuracy_percent'];
                    $phaseBarColor = match (true) {
                        $phasePct >= 90 => 'bg-emerald-500',
                        $phasePct >= 75 => 'bg-sky-500',
                        $phasePct >= 60 => 'bg-amber-500',
                        default => 'bg-rose-500',
                    };
                @endphp
                <div class="rounded-xl border border-line/70 bg-surface-2/30 p-4">
                    <div class="flex items-center justify-between text-[12px]">
                        <span class="font-bold text-ink">{{ $phase['name'] }}</span>
                        <span class="font-extrabold tabular-nums text-ink">{{ $phasePct }}%</span>
                    </div>
                    <div class="mt-2 h-2.5 w-full overflow-hidden rounded-full bg-surface-2">
                        <div class="h-full rounded-full {{ $phaseBarColor }} transition-all duration-500" style="width: {{ $phasePct }}%;"></div>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-[11px] text-subtle">
                        <span>{{ $phase['correct'] }} dari {{ $phase['attempted'] }} benar</span>
                        <span>{{ $phase['total'] }} butir</span>
                    </div>
                </div>
            @endforeach
        </div>

        <p class="mt-3 text-[12px] leading-relaxed text-subtle">
            <strong class="text-ink">Interpretasi Kestabilan:</strong> {{ $stability['description'] ?? '' }}
        </p>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        
        <div class="rounded-xl border border-line bg-surface p-5 shadow-sm">
            <div class="flex items-center gap-2">
                <span class="flex size-7 items-center justify-center rounded-lg bg-surface-2 text-ink">
                    <x-icon name="shield" class="size-4 text-brand" />
                </span>
                <h4 class="text-[14px] font-bold text-ink">Profil Risiko Integritas Data</h4>
            </div>

            <div class="mt-4 flex items-center gap-3 rounded-xl border border-line bg-surface-2/40 p-3.5">
                <div class="space-y-0.5">
                    <p class="text-[11px] font-semibold text-muted">Taraf Risiko Kekeliruan:</p>
                    <x-ui.badge :variant="$risk['tone'] ?? 'neutral'" class="text-[12px]">
                        {{ $risk['level'] ?? 'Moderat' }}
                    </x-ui.badge>
                </div>
            </div>

            <p class="mt-3 text-[12.5px] leading-relaxed text-subtle">
                {{ $risk['notes'] ?? '' }}
            </p>
        </div>

        <div class="rounded-xl border border-line bg-surface p-5 shadow-sm">
            <div class="flex items-center gap-2">
                <span class="flex size-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                    <x-icon name="briefcase" class="size-4" />
                </span>
                <h4 class="text-[14px] font-bold text-ink">Rekomendasi Penempatan Tugas</h4>
            </div>

            <p class="mt-3 text-[12.5px] leading-relaxed text-subtle">
                {{ $report['hr_summary'] ?? '' }}
            </p>

            <div class="mt-4 border-t border-line/60 pt-3">
                <p class="text-[11.5px] font-semibold uppercase tracking-wider text-muted">Posisi Kerja yang Sesuai dengan Ritme Ini:</p>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    @foreach ($report['recommended_roles'] ?? [] as $role)
                        <span class="rounded-lg border border-line bg-surface-2 px-2.5 py-1 text-[11.5px] font-medium text-ink">
                            {{ $role }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
