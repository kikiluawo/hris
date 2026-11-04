<x-layouts.app :title="$employee->name">
    <x-ui.page-header
        :title="$employee->name"
        :description="$employee->position->name . ' · ' . $employee->department->name . ' · ' . $employee->company->name"
    >
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="chevron-left" :href="route('employees.index')">Kembali</x-ui.button>
            <x-ui.button variant="secondary" icon="pencil" :href="route('employees.edit', $employee)">Edit data</x-ui.button>
            <x-ui.button icon="transfer" :href="route('transfers.create', ['employee' => $employee->id])">Mutasi karyawan</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-4 lg:grid-cols-3 lg:items-start">
        <div class="space-y-4 lg:col-span-2">
            <x-ui.card>
                <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <x-ui.avatar :name="$employee->name" size="lg" />
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-[17px] font-semibold leading-tight">{{ $employee->name }}</h2>
                                <x-ui.badge :variant="$employee->employmentStatusTone()">{{ $employee->employmentStatusLabel() }}</x-ui.badge>
                            </div>
                            <p class="mt-1 text-[13px] text-muted">{{ $employee->position->name }}</p>
                            <p class="text-[12.5px] text-subtle">{{ $employee->email }}</p>
                        </div>
                    </div>

                    <dl class="grid grid-cols-2 gap-x-6 gap-y-2 text-[12.5px] sm:text-right">
                        <div>
                            <dt class="text-subtle">No. staff</dt>
                            <dd class="font-medium tabular-nums">{{ $employee->nik }}</dd>
                        </div>
                        <div>
                            <dt class="text-subtle">No handphone</dt>
                            <dd class="font-medium">{{ $employee->phone ?: '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </x-ui.card>

            @php
                $personal = [
                    'Jenis kelamin' => $employee->genderLabel(),
                    'Tempat, tanggal lahir' => $employee->birthLabel(),
                    'Agama' => $employee->religion,
                    'Status pernikahan' => $employee->marital_status,
                    'No. KTP' => $employee->ktp_no,
                    'No. NPWP' => $employee->npwp_no,
                    'No. BPJS TK' => $employee->bpjs_tk_no,
                    'BPJS Kes' => $employee->bpjs_kes_no,
                ];

                $family = [
                    'Istri / suami' => $employee->spouse_name,
                    'Ayah' => $employee->father_name,
                    'Ibu' => $employee->mother_name,
                    'Anak' => $employee->childrenLabel(),
                ];

                $contractRange = fn ($start, $end) => ($start || $end)
                    ? ($start?->translatedFormat('d M Y') ?? '—') . ' – ' . ($end?->translatedFormat('d M Y') ?? '—')
                    : null;
            @endphp

            <x-ui.card title="Data pribadi" description="Identitas dan dokumen resmi karyawan.">
                <dl class="grid gap-x-6 gap-y-4 text-[13px] sm:grid-cols-2">
                    @foreach ($personal as $label => $value)
                        <div>
                            <dt class="text-subtle">{{ $label }}</dt>
                            <dd class="mt-0.5 break-words font-medium">{{ $value ?: '—' }}</dd>
                        </div>
                    @endforeach
                </dl>
            </x-ui.card>

            <x-ui.card title="Kontak & alamat" description="Cara menghubungi karyawan dan kontak darurat.">
                <dl class="grid gap-x-6 gap-y-4 text-[13px] sm:grid-cols-2">
                    <div>
                        <dt class="text-subtle">No handphone</dt>
                        <dd class="mt-0.5 font-medium">{{ $employee->phone ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-subtle">Email</dt>
                        <dd class="mt-0.5 break-words font-medium">{{ $employee->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-subtle">No tlp darurat</dt>
                        <dd class="mt-0.5 font-medium">{{ $employee->emergency_phone ?: '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-subtle">Alamat tempat tinggal</dt>
                        <dd class="mt-0.5 font-medium">{{ $employee->address ?: '—' }}</dd>
                    </div>
                </dl>
            </x-ui.card>

            <x-ui.card title="Keluarga" description="Data pasangan, orang tua, dan anak.">
                <dl class="grid gap-x-6 gap-y-4 text-[13px] sm:grid-cols-2">
                    @foreach ($family as $label => $value)
                        <div>
                            <dt class="text-subtle">{{ $label }}</dt>
                            <dd class="mt-0.5 font-medium">{{ $value ?: '—' }}</dd>
                        </div>
                    @endforeach
                </dl>
            </x-ui.card>

            <x-ui.card title="Kontrak & catatan" description="Masa berlaku kontrak kerja.">
                @if ($employee->activeContractLabel())
                    <x-slot:actions>
                        <x-ui.badge variant="warning">{{ $employee->activeContractLabel() }} berjalan</x-ui.badge>
                    </x-slot:actions>
                @endif

                <dl class="grid gap-x-6 gap-y-4 text-[13px] sm:grid-cols-2">
                    <div>
                        <dt class="text-subtle">Kontrak I</dt>
                        <dd class="mt-0.5 font-medium">{{ $contractRange($employee->contract_1_start, $employee->contract_1_end) ?? 'Belum diisi' }}</dd>
                    </div>
                    <div>
                        <dt class="text-subtle">Kontrak II</dt>
                        <dd class="mt-0.5 font-medium">{{ $contractRange($employee->contract_2_start, $employee->contract_2_end) ?? 'Belum diisi' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-subtle">Ket</dt>
                        <dd class="mt-0.5 font-medium">{{ $employee->note ?: '—' }}</dd>
                    </div>
                </dl>
            </x-ui.card>

            <x-ui.card :padded="false">
                <div class="flex flex-wrap items-center justify-between gap-2 px-5 py-4 sm:px-6">
                    <div>
                        <h2 class="text-[15px] font-semibold">Riwayat mutasi</h2>
                        <p class="mt-0.5 text-[12.5px] text-muted">Perpindahan PT, cabang, departemen, dan jabatan</p>
                    </div>
                    <x-ui.badge variant="brand">{{ $employee->transfers->count() }} catatan</x-ui.badge>
                </div>

                <div class="border-t border-line px-5 py-5 sm:px-6">
                    @if ($employee->transfers->isEmpty())
                        <x-ui.empty-state
                            icon="transfer"
                            title="Belum ada mutasi"
                            description="Karyawan ini masih berada di penempatan awalnya."
                        >
                            <x-slot:actions>
                                <x-ui.button variant="secondary" :href="route('transfers.create', ['employee' => $employee->id])">Mutasi karyawan ini</x-ui.button>
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

        <aside class="space-y-4 lg:sticky lg:top-24">
            <x-ui.card title="Penempatan saat ini" description="Data organisasi yang berlaku sekarang.">
                <dl class="space-y-3 text-[13px]">
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-subtle">PT</dt>
                        <dd class="text-right font-medium">{{ $employee->company->name }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-subtle">Cabang</dt>
                        <dd class="text-right font-medium">{{ $employee->branch?->name ?? 'Tanpa cabang' }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-subtle">Departemen</dt>
                        <dd class="text-right font-medium">{{ $employee->department->name }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-subtle">Jabatan</dt>
                        <dd class="text-right font-medium">{{ $employee->position->name }}</dd>
                    </div>
                </dl>

                <div class="mt-4 space-y-3 border-t border-line pt-4 text-[13px]">
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-subtle">Status</dt>
                        <dd><x-ui.badge :variant="$employee->employmentStatusTone()">{{ $employee->employmentStatusLabel() }}</x-ui.badge></dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-subtle">Bergabung</dt>
                        <dd class="text-right font-medium">{{ $employee->joined_at->translatedFormat('d F Y') }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-subtle">Gaji bulanan</dt>
                        <dd class="text-right font-medium tabular-nums">{{ \App\Support\Currency::rupiah($employee->salary) }}</dd>
                    </div>
                </div>
            </x-ui.card>

            <div class="rounded-2xl border border-line bg-surface-2/50 p-4 text-[12.5px] leading-relaxed text-muted">
                <p class="mb-2 flex items-center gap-2 font-semibold text-ink">
                    <x-icon name="alert-circle" class="size-4 text-brand" />
                    Tentang mutasi
                </p>
                <p>Mutasi langsung mengubah penempatan karyawan dan menyimpan snapshot sebelum & sesudah ke riwayat.</p>
            </div>
        </aside>
    </div>
</x-layouts.app>
