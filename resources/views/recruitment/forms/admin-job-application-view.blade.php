<x-layouts.app :title="'Formulir Lamaran: ' . $candidate->name">
    <x-ui.page-header
        :title="'Formulir Lamaran: ' . $candidate->name"
        :description="'Posisi: ' . $jobOpening->title . ' · Dikirim pada ' . ($form->submitted_at?->translatedFormat('d F Y · H:i') ?? 'Belum diserahkan')"
    >
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="arrow-left" :href="route('applicants.index')">
                Kembali ke Pelamar
            </x-ui.button>
            <x-ui.button variant="primary" icon="printer" :href="route('applicants.print-form', $application)" target="_blank">
                Cetak / Simpan PDF
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @php
        $personal = $form->personal_data ?? [];
        $family = $form->family_data ?? [];
        $education = $form->education_data ?? [];
        $work = $form->work_history ?? [];
        $additional = $form->additional_info ?? [];
    @endphp

    <div class="space-y-6">
        
        <x-ui.card>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    @if ($form->photo_path)
                        <img src="{{ asset('storage/' . $form->photo_path) }}" alt="Foto Pelamar" class="size-20 rounded-2xl object-cover border border-line shadow-sm" />
                    @else
                        <div class="flex size-20 items-center justify-center rounded-2xl border border-line bg-surface-2 text-subtle">
                            <x-icon name="user" class="size-8" />
                        </div>
                    @endif
                    <div>
                        <h2 class="text-[18px] font-bold text-ink">{{ $personal['name'] ?? $candidate->name }}</h2>
                        <p class="text-[13px] text-muted">{{ $jobOpening->title }} · {{ $jobOpening->company?->name }}</p>
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <x-ui.badge :variant="$form->isSubmitted() ? 'success' : 'warning'">
                                {{ $form->isSubmitted() ? 'Formulir Lengkap' : 'Draft / Belum Diserahkan' }}
                            </x-ui.badge>
                            <span class="text-[12px] text-subtle">Gaji yang diharapkan: <strong>Rp {{ number_format((float)($family['expected_salary'] ?? 0), 0, ',', '.') }}</strong></span>
                            <span class="text-[12px] text-subtle">· Notice: <strong>{{ $family['notice_period'] ?? '—' }}</strong></span>
                        </div>
                    </div>
                </div>

                <div class="text-right text-[12px] text-muted">
                    <p>Status Lamaran: <x-ui.badge :variant="$application->statusTone()">{{ $application->statusLabel() }}</x-ui.badge></p>
                    <p class="mt-1">Diserahkan: {{ $form->submitted_at?->format('d/m/Y H:i') ?? '—' }}</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="1. Identitas Pribadi & Kesehatan" description="Data pokok pelamar dan kondisi kesehatan">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 text-[13px]">
                <div>
                    <span class="text-subtle text-[11px] uppercase font-semibold">Nama Lengkap & Alias</span>
                    <p class="font-medium text-ink">{{ $personal['name'] ?? '—' }} {{ !empty($personal['alias']) ? '(' . $personal['alias'] . ')' : '' }}</p>
                </div>
                <div>
                    <span class="text-subtle text-[11px] uppercase font-semibold">Jenis Kelamin / Usia</span>
                    <p class="font-medium text-ink">{{ $personal['gender'] === 'pria' || $personal['gender'] === 'M' ? 'Laki-laki (M)' : 'Perempuan (F)' }} {{ !empty($personal['age']) ? '· ' . $personal['age'] . ' Tahun' : '' }}</p>
                </div>
                <div>
                    <span class="text-subtle text-[11px] uppercase font-semibold">Agama / Suku</span>
                    <p class="font-medium text-ink">{{ $personal['religion'] ?? '—' }} {{ !empty($personal['race']) ? '· Suku ' . $personal['race'] : '' }}</p>
                </div>
                <div>
                    <span class="text-subtle text-[11px] uppercase font-semibold">Tempat & Tanggal Lahir</span>
                    <p class="font-medium text-ink">{{ $personal['birth_place'] ?? '—' }}, {{ !empty($personal['birth_date']) ? \Carbon\Carbon::parse($personal['birth_date'])->translatedFormat('d F Y') : '—' }}</p>
                </div>
                <div>
                    <span class="text-subtle text-[11px] uppercase font-semibold">No. KTP</span>
                    <p class="font-mono font-medium text-ink">{{ $personal['ktp_no'] ?? '—' }}</p>
                </div>
                <div>
                    <span class="text-subtle text-[11px] uppercase font-semibold">Status Pernikahan</span>
                    <p class="font-medium text-ink">{{ $personal['marital_status'] ?? '—' }}</p>
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <span class="text-subtle text-[11px] uppercase font-semibold">Alamat Tempat Tinggal</span>
                    <p class="font-medium text-ink">{{ $personal['address'] ?? '—' }}</p>
                </div>
                <div>
                    <span class="text-subtle text-[11px] uppercase font-semibold">Kontak HP / Telepon</span>
                    <p class="font-medium text-ink">{{ $personal['phone_mobile'] ?? '—' }} {{ !empty($personal['phone_home']) ? '· Rumah: ' . $personal['phone_home'] : '' }}</p>
                </div>
                <div>
                    <span class="text-subtle text-[11px] uppercase font-semibold">Email</span>
                    <p class="font-medium text-ink">{{ $personal['email'] ?? '—' }}</p>
                </div>
                <div>
                    <span class="text-subtle text-[11px] uppercase font-semibold">Kondisi Kesehatan Saat Ini</span>
                    <p class="font-medium text-ink">{{ $personal['present_health'] ?? 'Sehat' }}</p>
                </div>
                <div>
                    <span class="text-subtle text-[11px] uppercase font-semibold">Riwayat Penyakit Serius</span>
                    <p class="font-medium text-ink">{{ $personal['serious_illness'] ?? 'Tidak ada' }}</p>
                </div>
                <div>
                    <span class="text-subtle text-[11px] uppercase font-semibold">Cacat Fisik</span>
                    <p class="font-medium text-ink">{{ $personal['disabilities'] ?? 'Tidak ada' }}</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="2. Data Keluarga" description="Pasangan, orang tua, anak, dan saudara kandung">
            <div class="space-y-4 text-[13px]">
                @if (!empty($family['spouse']['name']))
                    <div class="rounded-xl border border-line bg-surface-2/30 p-3.5">
                        <span class="font-bold text-ink">Pasangan (Istri / Suami):</span>
                        <p class="text-muted mt-1">{{ $family['spouse']['name'] }} · Pekerjaan: {{ $family['spouse']['occupation'] ?? '—' }} · Tgl Lahir: {{ $family['spouse']['dob'] ?? '—' }}</p>
                    </div>
                @endif

                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-line bg-surface-2/30 p-3.5">
                        <span class="font-bold text-ink">Ayah:</span>
                        <p class="text-muted mt-1">{{ $family['father']['name'] ?? '—' }} ({{ $family['father']['occupation'] ?? '—' }})</p>
                    </div>
                    <div class="rounded-xl border border-line bg-surface-2/30 p-3.5">
                        <span class="font-bold text-ink">Ibu:</span>
                        <p class="text-muted mt-1">{{ $family['mother']['name'] ?? '—' }} ({{ $family['mother']['occupation'] ?? '—' }})</p>
                    </div>
                </div>

                @if (!empty($family['children']))
                    <div class="border-t border-line pt-3">
                        <span class="font-bold text-ink">Anak-anak:</span>
                        <div class="mt-2 space-y-1.5">
                            @foreach ($family['children'] as $child)
                                @if (!empty($child['name']))
                                    <div class="flex items-center gap-2 text-muted">
                                        <span class="size-1.5 rounded-full bg-brand"></span>
                                        <strong>{{ $child['name'] }}</strong> ({{ $child['gender'] ?? '—' }}) · TTL: {{ $child['dob'] ?? '—' }}
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (!empty($family['siblings']))
                    <div class="border-t border-line pt-3">
                        <span class="font-bold text-ink">Saudara Kandung:</span>
                        <div class="mt-2 space-y-1.5">
                            @foreach ($family['siblings'] as $sib)
                                @if (!empty($sib['name']))
                                    <div class="flex items-center gap-2 text-muted">
                                        <span class="size-1.5 rounded-full bg-brand"></span>
                                        <strong>{{ $sib['name'] }}</strong> · Pekerjaan: {{ $sib['job'] ?? '—' }}
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </x-ui.card>

        <x-ui.card title="3. Pendidikan & Kemampuan" description="Riwayat sekolah, keahlian bahasa, dan software">
            <div class="space-y-4 text-[13px]">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-line bg-surface-2/30 p-3.5">
                        <span class="font-bold text-ink">SMA:</span>
                        <p class="text-muted mt-1">{{ $education['schools']['sma']['name'] ?? '—' }} ({{ $education['schools']['sma']['from'] ?? '' }} - {{ $education['schools']['sma']['to'] ?? '' }})</p>
                        <p class="text-subtle text-[11.5px]">Jurusan/Nilai: {{ $education['schools']['sma']['achievement'] ?? '—' }}</p>
                    </div>
                    <div class="rounded-xl border border-line bg-surface-2/30 p-3.5">
                        <span class="font-bold text-ink">Universitas / Diploma:</span>
                        <p class="text-muted mt-1">{{ $education['schools']['university']['name'] ?? '—' }} ({{ $education['schools']['university']['from'] ?? '' }} - {{ $education['schools']['university']['to'] ?? '' }})</p>
                        <p class="text-subtle text-[11.5px]">IPK: {{ $education['schools']['university']['achievement'] ?? '—' }}</p>
                    </div>
                </div>

                <div class="border-t border-line pt-3">
                    <span class="font-bold text-ink">Kelebihan & Kekurangan:</span>
                    <div class="mt-2 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl bg-emerald-500/10 border border-emerald-500/20 p-3">
                            <span class="font-semibold text-emerald-800 dark:text-emerald-300">3 Kelebihan:</span>
                            <ul class="list-disc pl-4 mt-1 text-emerald-900 dark:text-emerald-200 text-[12px] space-y-0.5">
                                @foreach ($education['strengths'] ?? [] as $st)
                                    <li>{{ $st }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="rounded-xl bg-amber-500/10 border border-amber-500/20 p-3">
                            <span class="font-semibold text-amber-800 dark:text-amber-300">3 Kekurangan:</span>
                            <ul class="list-disc pl-4 mt-1 text-amber-900 dark:text-amber-200 text-[12px] space-y-0.5">
                                @foreach ($education['weaknesses'] ?? [] as $wk)
                                    <li>{{ $wk }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="border-t border-line pt-3">
                    <span class="font-bold text-ink">Penguasaan Komputer:</span>
                    <p class="text-muted mt-1 whitespace-pre-line">{{ $education['computer_skills'] ?? '—' }}</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="4. Riwayat Pekerjaan" description="Pengalaman kerja kandidat terdahulu">
            <div class="space-y-3 text-[13px]">
                @forelse ($work['jobs'] ?? [] as $job)
                    @if (!empty($job['company']))
                        <div class="rounded-xl border border-line bg-surface-2/30 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line pb-2">
                                <div>
                                    <h4 class="font-bold text-ink">{{ $job['position'] ?? '—' }}</h4>
                                    <p class="text-subtle text-[12px]">{{ $job['company'] }}</p>
                                </div>
                                <span class="rounded-full bg-surface px-2.5 py-0.5 text-[11px] font-semibold text-muted border border-line">
                                    {{ $job['period_from'] ?? '' }} - {{ $job['period_to'] ?? '' }}
                                </span>
                            </div>
                            <div class="mt-2 text-muted text-[12.5px] space-y-1">
                                <p><strong>Melapor ke:</strong> {{ $job['report_to'] ?? '—' }} · <strong>Gaji Akhir:</strong> Rp {{ $job['salary_end'] ?? '—' }}</p>
                                <p><strong>Alasan Berhenti:</strong> {{ $job['reason_leaving'] ?? '—' }}</p>
                                @if (!empty($job['responsibilities']))
                                    <p class="mt-2 text-subtle text-[12px] whitespace-pre-line">{{ $job['responsibilities'] }}</p>
                                @endif
                            </div>
                        </div>
                    @endif
                @empty
                    <p class="text-muted text-[13px]">Tidak ada riwayat kerja yang diinput.</p>
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card title="5. Informasi Tambahan & Referensi">
            <div class="space-y-4 text-[13px]">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <span class="text-subtle text-[11px] uppercase font-semibold">Tindakan Pidana:</span>
                        <p class="font-medium text-ink">{{ $additional['criminal_record'] ?? 'Tidak' }}</p>
                    </div>
                    <div>
                        <span class="text-subtle text-[11px] uppercase font-semibold">Kepemilikan SIM:</span>
                        <p class="font-medium text-ink">{{ $additional['driving_license'] ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-subtle text-[11px] uppercase font-semibold">Kendaraan Pribadi:</span>
                        <p class="font-medium text-ink">{{ $additional['vehicle_owned'] ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-subtle text-[11px] uppercase font-semibold">Bisnis Lain:</span>
                        <p class="font-medium text-ink">{{ $additional['other_business'] ?? 'Tidak ada' }}</p>
                    </div>
                </div>

                <div class="border-t border-line pt-3">
                    <span class="text-subtle text-[11px] uppercase font-semibold">Pertimbangan Kesesuaian Posisi:</span>
                    <p class="text-muted mt-1 whitespace-pre-line">{{ $additional['suitability_statement'] ?? '—' }}</p>
                </div>

                @if (!empty($additional['references']))
                    <div class="border-t border-line pt-3">
                        <span class="font-bold text-ink">Referensi Profesional:</span>
                        <div class="mt-2 grid gap-3 sm:grid-cols-2">
                            @foreach ($additional['references'] as $ref)
                                @if (!empty($ref['name']))
                                    <div class="rounded-xl border border-line bg-surface-2/20 p-3">
                                        <p class="font-bold text-ink">{{ $ref['name'] }}</p>
                                        <p class="text-muted text-[12px]">{{ $ref['position'] ?? '—' }}</p>
                                        <p class="text-subtle text-[11.5px]">Telp: {{ $ref['phone'] ?? '—' }} · Hubungan: {{ $ref['relation'] ?? '—' }}</p>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="rounded-xl border border-line bg-surface-2/30 p-3.5 text-[12px]">
                    <p><strong>Tanda Tangan Pemohon:</strong> {{ $form->signature_name }}</p>
                    <p class="text-subtle">Disetujui pada: {{ $form->signed_date ?? $form->submitted_at?->format('d/m/Y') }}</p>
                </div>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>