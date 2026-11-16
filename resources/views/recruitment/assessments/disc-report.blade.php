@props(['report'])

@if ($report)
    @php
        $profile = $report['profile'] ?? [];
        $graphs = $report['graphs'] ?? [];
        $tally = $report['tally'] ?? [];
        $adaptation = $report['adaptation_analysis'] ?? [];
        $shifts = $adaptation['shifts'] ?? [];
        $midline = $report['midline'] ?? 14;

        $g1 = $graphs['graph_1']['scores'] ?? ['D' => 14, 'I' => 14, 'S' => 14, 'C' => 14];
        $g2 = $graphs['graph_2']['scores'] ?? ['D' => 14, 'I' => 14, 'S' => 14, 'C' => 14];
        $g3 = $graphs['graph_3']['scores'] ?? ['D' => 14, 'I' => 14, 'S' => 14, 'C' => 14];
    @endphp

    <div class="space-y-4">
        
        <div class="relative overflow-hidden rounded-2xl border border-line bg-surface p-5 sm:p-6 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="rounded-lg bg-brand-soft px-2.5 py-0.5 text-[11px] font-bold text-brand-soft-ink uppercase tracking-wider">
                            Profil Kepribadian DISC
                        </span>
                        <span class="rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300 px-2 py-0.5 text-[11px] font-semibold">
                            Tipe {{ $profile['code'] ?? 'D' }}
                        </span>
                    </div>
                    <h3 class="text-[20px] sm:text-[22px] font-bold text-ink">
                        {{ $profile['title'] ?? 'Profil DISC' }}
                    </h3>
                    <p class="text-[13px] font-medium text-muted">
                        {{ $profile['subtitle'] ?? '' }}
                    </p>
                </div>

                <div class="flex flex-col items-end gap-1 text-right">
                    <span class="text-[11px] uppercase tracking-wider font-semibold text-subtle">Indikator Adaptasi</span>
                    <span class="rounded-xl border border-line bg-surface-2 px-3 py-1 text-[12px] font-bold text-ink">
                        {{ $adaptation['effort_level'] ?? 'Normal' }}
                    </span>
                </div>
            </div>

            <p class="mt-4 text-[13.5px] leading-relaxed text-muted bg-surface-2/40 rounded-xl p-3.5 border border-line">
                {{ $profile['overview'] ?? '' }}
            </p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-5 sm:p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line pb-4">
                <div>
                    <h4 class="text-[15px] font-bold text-ink">Grafik Kepribadian & Adaptasi Lingkungan</h4>
                    <p class="text-[12px] text-muted">Membandingkan perilaku adaptasi kerja (Grafik 1), karakter asli di bawah tekanan (Grafik 2), dan persepsi diri terintegrasi (Grafik 3).</p>
                </div>
                <div class="flex items-center gap-3 text-[11.5px] font-semibold">
                    <span class="flex items-center gap-1.5"><span class="size-3 rounded-full bg-emerald-500"></span> Grafik 1: Adaptasi (Mask)</span>
                    <span class="flex items-center gap-1.5"><span class="size-3 rounded-full bg-amber-500"></span> Grafik 2: Asli (Core)</span>
                    <span class="flex items-center gap-1.5"><span class="size-3 rounded-full bg-blue-600"></span> Grafik 3: Mirror</span>
                </div>
            </div>

            <div class="mt-5 grid gap-4 lg:grid-cols-3">
                
                <div class="rounded-xl border border-emerald-200/60 bg-emerald-50/20 dark:bg-emerald-950/10 p-4">
                    <div class="text-center">
                        <span class="rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300 px-3 py-0.5 text-[11.5px] font-bold">
                            Grafik 1: Adaptasi (Mask)
                        </span>
                        <p class="mt-1 text-[11px] text-muted">Bagaimana kandidat menyesuaikan diri di kantor</p>
                    </div>

                    <div class="mt-4">
                        @include('recruitment.assessments.disc-chart-svg', ['scores' => $g1, 'color' => '#10b981', 'label' => 'Grafik 1 (Mask)'])
                    </div>

                    <div class="mt-3 grid grid-cols-4 text-center text-[12px] border-t border-line/60 pt-2 font-mono">
                        <div><span class="text-muted text-[10px] block">D</span><strong>{{ $g1['D'] }}</strong></div>
                        <div><span class="text-muted text-[10px] block">I</span><strong>{{ $g1['I'] }}</strong></div>
                        <div><span class="text-muted text-[10px] block">S</span><strong>{{ $g1['S'] }}</strong></div>
                        <div><span class="text-muted text-[10px] block">C</span><strong>{{ $g1['C'] }}</strong></div>
                    </div>
                </div>

                <div class="rounded-xl border border-amber-200/60 bg-amber-50/20 dark:bg-amber-950/10 p-4">
                    <div class="text-center">
                        <span class="rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 px-3 py-0.5 text-[11.5px] font-bold">
                            Grafik 2: Karakter Asli (Core)
                        </span>
                        <p class="mt-1 text-[11px] text-muted">Karakter alami saat di bawah tekanan / santai</p>
                    </div>

                    <div class="mt-4">
                        @include('recruitment.assessments.disc-chart-svg', ['scores' => $g2, 'color' => '#f59e0b', 'label' => 'Grafik 2 (Core)'])
                    </div>

                    <div class="mt-3 grid grid-cols-4 text-center text-[12px] border-t border-line/60 pt-2 font-mono">
                        <div><span class="text-muted text-[10px] block">D</span><strong>{{ $g2['D'] }}</strong></div>
                        <div><span class="text-muted text-[10px] block">I</span><strong>{{ $g2['I'] }}</strong></div>
                        <div><span class="text-muted text-[10px] block">S</span><strong>{{ $g2['S'] }}</strong></div>
                        <div><span class="text-muted text-[10px] block">C</span><strong>{{ $g2['C'] }}</strong></div>
                    </div>
                </div>

                <div class="rounded-xl border border-blue-200/60 bg-blue-50/20 dark:bg-blue-950/10 p-4">
                    <div class="text-center">
                        <span class="rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300 px-3 py-0.5 text-[11.5px] font-bold">
                            Grafik 3: Citra Diri (Mirror)
                        </span>
                        <p class="mt-1 text-[11px] text-muted">Integrasi perilaku yang menjadi profil utama</p>
                    </div>

                    <div class="mt-4">
                        @include('recruitment.assessments.disc-chart-svg', ['scores' => $g3, 'color' => '#2563eb', 'label' => 'Grafik 3 (Mirror)'])
                    </div>

                    <div class="mt-3 grid grid-cols-4 text-center text-[12px] border-t border-line/60 pt-2 font-mono">
                        <div><span class="text-muted text-[10px] block">D</span><strong>{{ $g3['D'] }}</strong></div>
                        <div><span class="text-muted text-[10px] block">I</span><strong>{{ $g3['I'] }}</strong></div>
                        <div><span class="text-muted text-[10px] block">S</span><strong>{{ $g3['S'] }}</strong></div>
                        <div><span class="text-muted text-[10px] block">C</span><strong>{{ $g3['C'] }}</strong></div>
                    </div>
                </div>
            </div>

            <p class="mt-3 text-[11px] text-subtle text-center">
                *Garis tengah merah putus-putus (angka 14) menandakan batas <strong>Midline</strong>. Nilai di atas 14 = Tipe Dominan (High), nilai di bawah 14 = Tipe Pasif (Low).
            </p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-5 sm:p-6 shadow-sm">
            <h4 class="text-[15px] font-bold text-ink">Analisis Adaptasi Perilaku Kerja</h4>
            <p class="mt-1 text-[12px] text-muted">
                Perbandingan bagaimana kandidat menggeser perilakunya dari karakter alami (Grafik 2) ke perilaku kerja yang diharapkan (Grafik 1).
            </p>

            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach (['D', 'I', 'S', 'C'] as $trait)
                    @php
                        $shift = $shifts[$trait] ?? [];
                        $isIncrease = ($shift['type'] ?? '') === 'increase';
                        $isDecrease = ($shift['type'] ?? '') === 'decrease';
                    @endphp
                    <div class="rounded-xl border border-line bg-surface-2/40 p-4">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-bold text-[13.5px] text-ink">{{ $shift['label'] ?? $trait }}</span>
                            @if ($isIncrease)
                                <span class="rounded-md bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300 px-2 py-0.5 text-[11px] font-bold">
                                    ↑ Naik +{{ $shift['diff'] }}
                                </span>
                            @elseif ($isDecrease)
                                <span class="rounded-md bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300 px-2 py-0.5 text-[11px] font-bold">
                                    ↓ Turun {{ $shift['diff'] }}
                                </span>
                            @else
                                <span class="rounded-md bg-surface-2 text-muted px-2 py-0.5 text-[11px] font-semibold border border-line">
                                    ≈ Selaras ({{ $shift['diff'] >= 0 ? '+' : '' }}{{ $shift['diff'] }})
                                </span>
                            @endif
                        </div>
                        <p class="mt-2 text-[12.5px] leading-relaxed text-muted">
                            {{ $shift['interpretation'] ?? '' }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            
            <div class="rounded-2xl border border-line bg-surface p-5 shadow-sm space-y-3">
                <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400">
                    <x-icon name="check-circle" class="size-5 shrink-0" />
                    <h5 class="text-[14px] font-bold">Kekuatan Utama di Tempat Kerja</h5>
                </div>
                <ul class="space-y-2 text-[12.5px] text-muted">
                    @foreach ($profile['strengths'] ?? [] as $strength)
                        <li class="flex items-start gap-2">
                            <span class="mt-1 size-1.5 shrink-0 rounded-full bg-emerald-500"></span>
                            <span>{{ $strength }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="rounded-2xl border border-line bg-surface p-5 shadow-sm space-y-3">
                <div class="flex items-center gap-2 text-amber-700 dark:text-amber-400">
                    <x-icon name="alert-triangle" class="size-5 shrink-0" />
                    <h5 class="text-[14px] font-bold">Area Pengembangan & Perhatian</h5>
                </div>
                <ul class="space-y-2 text-[12.5px] text-muted">
                    @foreach ($profile['weaknesses'] ?? [] as $weakness)
                        <li class="flex items-start gap-2">
                            <span class="mt-1 size-1.5 shrink-0 rounded-full bg-amber-500"></span>
                            <span>{{ $weakness }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="rounded-2xl border border-line bg-surface p-5 shadow-sm space-y-2">
                <div class="flex items-center gap-2 text-blue-700 dark:text-blue-400">
                    <x-icon name="message-circle" class="size-5 shrink-0" />
                    <h5 class="text-[14px] font-bold">Tips Berkomunikasi & Memimpin</h5>
                </div>
                <p class="text-[12.5px] leading-relaxed text-muted">
                    {{ $profile['communication_tips'] ?? '' }}
                </p>
                <div class="mt-2 pt-2 border-t border-line text-[12px] text-subtle">
                    <strong>Gaya kerja:</strong> {{ $profile['work_style'] ?? '' }}
                </div>
            </div>

            <div class="rounded-2xl border border-line bg-surface p-5 shadow-sm space-y-3">
                <div class="flex items-center gap-2 text-brand">
                    <x-icon name="briefcase" class="size-5 shrink-0" />
                    <h5 class="text-[14px] font-bold">Rekomendasi Posisi / Jabatan</h5>
                </div>
                <div class="flex flex-wrap gap-1.5 pt-1">
                    @foreach ($profile['recommended_roles'] ?? [] as $role)
                        <span class="rounded-lg border border-line bg-surface-2 px-2.5 py-1 text-[12px] font-semibold text-ink">
                            {{ $role }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-5 shadow-sm">
            <h5 class="text-[13.5px] font-bold text-ink">Tabel Rekapitulasi Skor Mentah DISC</h5>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full text-left text-[12.5px]">
                    <thead class="border-b border-line bg-surface-2/60 text-[11px] font-bold uppercase tracking-wider text-muted">
                        <tr>
                            <th class="px-3 py-2">Dimensi</th>
                            <th class="px-3 py-2 text-center">Most [P]</th>
                            <th class="px-3 py-2 text-center">Least [K]</th>
                            <th class="px-3 py-2 text-center">Change [P - K]</th>
                            <th class="px-3 py-2 text-center">Grafik 1 (Mask)</th>
                            <th class="px-3 py-2 text-center">Grafik 2 (Core)</th>
                            <th class="px-3 py-2 text-center">Grafik 3 (Mirror)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach (['D' => 'Dominance (D)', 'I' => 'Influence (I)', 'S' => 'Steadiness (S)', 'C' => 'Compliance (C)'] as $code => $label)
                            <tr>
                                <td class="px-3 py-2 font-semibold text-ink">{{ $label }}</td>
                                <td class="px-3 py-2 text-center tabular-nums">{{ $tally['most'][$code] ?? 0 }}</td>
                                <td class="px-3 py-2 text-center tabular-nums">{{ $tally['least'][$code] ?? 0 }}</td>
                                <td class="px-3 py-2 text-center tabular-nums font-semibold {{ ($tally['change'][$code] ?? 0) >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">
                                    {{ ($tally['change'][$code] ?? 0) > 0 ? '+' : '' }}{{ $tally['change'][$code] ?? 0 }}
                                </td>
                                <td class="px-3 py-2 text-center tabular-nums font-bold text-emerald-700 dark:text-emerald-400">{{ $g1[$code] }}</td>
                                <td class="px-3 py-2 text-center tabular-nums font-bold text-amber-700 dark:text-amber-400">{{ $g2[$code] }}</td>
                                <td class="px-3 py-2 text-center tabular-nums font-bold text-blue-700 dark:text-blue-400">{{ $g3[$code] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
