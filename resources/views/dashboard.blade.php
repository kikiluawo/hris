<x-layouts.app title="Dashboard">
    @if (! auth()->user()->hasVerifiedEmail())
        <x-ui.alert variant="warning" class="mb-5">
            Email Anda belum terverifikasi.
            <a href="{{ route('verification.notice') }}" class="font-semibold underline">Verifikasi sekarang</a>
            agar seluruh fitur terbuka.
        </x-ui.alert>
    @endif

    <x-ui.page-header
        title="Ringkasan"
        :description="'Pantau kondisi tim dan aktivitas HR per ' . now()->translatedFormat('l, d F Y') . '.'"
    >
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="download" :href="route('employees.export')">Unduh data</x-ui.button>
            <x-ui.button icon="calendar" :href="route('leaves.index')">Tinjau cuti</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card
            label="Total karyawan"
            :value="$totalEmployees"
            icon="users"
            :delta="'+' . $newEmployees"
            footnote="baru bulan ini"
        />
        <x-ui.stat-card
            label="Kehadiran hari ini"
            :value="$attendanceRate . '%'"
            icon="clock"
            delta="+1,2%"
            footnote="vs minggu lalu"
        />
        <x-ui.stat-card
            label="Cuti menunggu"
            :value="$pendingLeaves"
            icon="calendar"
            :footnote="$pendingLeaves > 0 ? 'perlu persetujuan Anda' : 'semua pengajuan selesai'"
        />
        <x-ui.stat-card
            label="Payroll bulan ini"
            :value="\App\Support\Currency::rupiah($monthlyPayroll, true)"
            icon="wallet"
            delta="+2,1%"
            footnote="vs bulan lalu"
        />
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-3">
        <x-ui.card class="xl:col-span-2" :padded="false">
            <div class="flex flex-wrap items-start justify-between gap-3 px-5 pt-5 sm:px-6">
                <div>
                    <h2 class="text-[15px] font-semibold">Tren kehadiran</h2>
                    <p class="mt-0.5 text-[13px] text-muted">Persentase kehadiran 12 minggu terakhir</p>
                </div>
                <div class="flex items-center gap-2 text-[12px] text-muted">
                    <span class="flex items-center gap-1.5"><span class="size-2 rounded-full bg-brand"></span> Kehadiran</span>
                    <span class="rounded-lg bg-surface-2 px-2 py-1 text-[11.5px] font-semibold text-ink">{{ number_format($attendanceValues[count($attendanceValues) - 1], 1, ',', '.') }}%</span>
                </div>
            </div>

            @php
                $width = 720;
                $height = 240;
                $padLeft = 40;
                $padRight = 14;
                $padTop = 20;
                $padBottom = 32;
                $chartWidth = $width - $padLeft - $padRight;
                $chartHeight = $height - $padTop - $padBottom;
                $count = count($attendanceValues);
                $minY = 94;
                $maxY = 100;
                $rangeY = $maxY - $minY;

                $points = [];
                foreach ($attendanceValues as $index => $value) {
                    $x = $padLeft + ($chartWidth * $index / max(1, $count - 1));
                    $y = $padTop + $chartHeight - (($value - $minY) / $rangeY) * $chartHeight;
                    $points[] = [round($x, 1), round($y, 1)];
                }

                $polyline = collect($points)->map(fn ($point) => $point[0] . ',' . $point[1])->implode(' ');
                $area = $padLeft . ',' . ($padTop + $chartHeight) . ' ' . $polyline . ' ' . ($padLeft + $chartWidth) . ',' . ($padTop + $chartHeight);
                $gridValues = collect(range(0, 3))->map(fn ($step) => $minY + ($rangeY / 3) * $step);
            @endphp

            <div class="px-2 pb-1 pt-2">
                <svg viewBox="0 0 {{ $width }} {{ $height }}" class="w-full" role="img" aria-label="Grafik tren kehadiran 12 minggu terakhir">
                    <defs>
                        <linearGradient id="attendanceFill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="var(--c-brand)" stop-opacity="0.18" />
                            <stop offset="100%" stop-color="var(--c-brand)" stop-opacity="0" />
                        </linearGradient>
                    </defs>

                    @foreach ($gridValues as $value)
                        @php $gridY = $padTop + $chartHeight - (($value - $minY) / $rangeY) * $chartHeight; @endphp
                        <line x1="{{ $padLeft }}" x2="{{ $width - $padRight }}" y1="{{ $gridY }}" y2="{{ $gridY }}" stroke="var(--c-line)" stroke-width="1" stroke-dasharray="4 6" />
                        <text x="{{ $padLeft - 8 }}" y="{{ $gridY + 3 }}" text-anchor="end" font-size="9.5" fill="var(--c-subtle)">{{ number_format($value, 0) }}%</text>
                    @endforeach

                    <polygon points="{{ $area }}" fill="url(#attendanceFill)" />
                    <polyline points="{{ $polyline }}" fill="none" stroke="var(--c-brand)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />

                    <circle cx="{{ $points[$count - 1][0] }}" cy="{{ $points[$count - 1][1] }}" r="4" fill="var(--c-brand)" stroke="var(--c-surface)" stroke-width="2" />

                    @foreach ($attendanceLabels as $index => $label)
                        @if ($index % 3 === 0 || $index === $count - 1)
                            <text x="{{ $points[$index][0] }}" y="{{ $height - 10 }}" text-anchor="middle" font-size="9.5" fill="var(--c-subtle)">{{ $label }}</text>
                        @endif
                    @endforeach
                </svg>
            </div>
        </x-ui.card>

        <x-ui.card title="Sebaran perusahaan" description="Karyawan aktif per PT" :padded="false">
            <div class="space-y-4 p-5 sm:p-6">
                @foreach ($companyTotals as $company)
                    <div>
                        <div class="flex items-center justify-between text-[12.5px]">
                            <span class="font-medium">{{ $company->name }}</span>
                            <span class="text-subtle">{{ $company->employees_count }}</span>
                        </div>
                        <div class="mt-1.5 flex items-center gap-2">
                            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-surface-2">
                                <div class="h-full rounded-full bg-brand" style="width: {{ round($company->employees_count / $companyMax * 100) }}%"></div>
                            </div>
                            <span class="w-8 text-right text-[10.5px] font-semibold text-subtle">{{ $company->code }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-3">
        <x-ui.card class="xl:col-span-2" title="Pengajuan cuti terbaru" description="Aktivitas pengajuan terakhir dari tim" :padded="false">
            <x-slot:actions>
                <a href="{{ route('leaves.index') }}" class="text-[12px] font-semibold text-brand transition hover:underline">Lihat semua</a>
            </x-slot:actions>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[560px] text-left">
                    <thead>
                        <tr class="text-[10.5px] uppercase tracking-[0.08em] text-subtle">
                            <th class="px-5 py-2.5 font-semibold sm:px-6">Karyawan</th>
                            <th class="px-3 py-2.5 font-semibold">Jenis</th>
                            <th class="px-3 py-2.5 font-semibold">Tanggal</th>
                            <th class="px-5 py-2.5 text-right font-semibold sm:px-6">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentLeaves as $leave)
                            <tr class="border-t border-line/80 transition hover:bg-surface-2/40">
                                <td class="px-5 py-3 sm:px-6">
                                    <div class="flex items-center gap-2.5">
                                        <x-ui.avatar :name="$leave->employee->name" size="sm" />
                                        <div class="min-w-0">
                                            <p class="truncate text-[13px] font-medium">{{ $leave->employee->name }}</p>
                                            <p class="truncate text-[11.5px] text-subtle">{{ $leave->employee->company->code }} · {{ $leave->employee->department->name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 text-[12.5px] text-muted">{{ $leave->typeLabel() }}</td>
                                <td class="whitespace-nowrap px-3 py-3 text-[12.5px] text-muted">
                                    {{ $leave->start_date->translatedFormat('d M') }} – {{ $leave->end_date->translatedFormat('d M') }}
                                </td>
                                <td class="px-5 py-3 text-right sm:px-6">
                                    <x-ui.badge :variant="$leave->statusTone()">{{ $leave->statusLabel() }}</x-ui.badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        <x-ui.card title="Aktivitas terbaru" description="Perubahan status pengajuan" :padded="false">
            <div class="divide-y divide-line/80">
                @foreach ($activity as $leave)
                    @php
                        $activityTone = match ($leave->status) {
                            'approved' => 'bg-brand-soft text-brand-soft-ink',
                            'rejected' => 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400',
                            default => 'bg-amber-50 text-amber-700 dark:bg-amber-400/10 dark:text-amber-300',
                        };
                        $activityIcon = match ($leave->status) {
                            'approved' => 'check',
                            'rejected' => 'close',
                            default => 'clock',
                        };
                    @endphp

                    <div class="flex gap-3 px-5 py-3.5 sm:px-6">
                        <span class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full {{ $activityTone }}">
                            <x-icon :name="$activityIcon" class="size-3.5" />
                        </span>
                        <div class="min-w-0">
                            <p class="text-[12.5px] leading-snug">
                                <span class="font-semibold">{{ $leave->employee->name }}</span>
                                · {{ $leave->typeLabel() }}
                            </p>
                            <p class="mt-0.5 text-[11px] text-subtle">{{ $leave->statusLabel() }} · {{ $leave->updated_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
