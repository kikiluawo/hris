<x-layouts.app :title="'Biodata Karyawan: ' . ($personal['full_name'] ?? $candidate->name)">
    <x-ui.page-header
        :title="'Biodata Karyawan: ' . ($personal['full_name'] ?? $candidate->name)"
        :description="'PT. ' . ($company->name ?? '—') . ' · Status Onboarding: ' . ($biodata->isSubmitted() ? 'Lengkap & Diserahkan' : 'Draft')"
    >
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="arrow-left" :href="route('applicants.index')">
                Kembali ke Pelamar
            </x-ui.button>
            <x-ui.button variant="secondary" icon="printer" :href="route('applicants.print-biodata', $application)" target="_blank">
                Cetak Biodata
            </x-ui.button>
            <x-ui.button variant="primary" icon="user-plus" :href="route('employees.create', ['application_id' => $application->id])">
                Jadikan Karyawan Baru
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="space-y-6">
        
        <x-ui.card>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex size-14 items-center justify-center rounded-2xl bg-brand/10 text-brand">
                        <x-icon name="user-check" class="size-7" />
                    </div>
                    <div>
                        <h2 class="text-[18px] font-bold text-ink">{{ $personal['full_name'] ?? $candidate->name }}</h2>
                        <p class="text-[13px] text-muted">{{ $jobOpening->title }} · {{ $company->name ?? '—' }}</p>
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <x-ui.badge :variant="$biodata->isSubmitted() ? 'success' : 'warning'">
                                {{ $biodata->isSubmitted() ? 'Biodata Diserahkan' : 'Draft / Menunggu Kandidat' }}
                            </x-ui.badge>
                            @if (!empty($personal['ktp_no']))
                                <span class="text-[12px] text-subtle">NIK: <strong>{{ $personal['ktp_no'] }}</strong></span>
                            @endif
                            @if (!empty($bankAccounts['bca']['number']))
                                <span class="text-[12px] text-subtle">· BCA: <strong>{{ $bankAccounts['bca']['number'] }}</strong> ({{ $bankAccounts['bca']['branch'] ?? '' }})</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.button variant="primary" size="sm" icon="user-plus" :href="route('employees.create', ['application_id' => $application->id])">
                        Jadikan Karyawan Baru
                    </x-ui.button>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="1. Identitas Pribadi" description="Data pokok kependudukan dan berkas kartu identitas">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 text-[13px]">
                <div>
                    <span class="text-subtle text-[11px] uppercase font-semibold">Nama Lengkap & Panggilan</span>
                    <p class="font-medium text-ink">{{ $personal['full_name'] ?? '—' }} {{ !empty($personal['nickname']) ? '("' . $personal['nickname'] . '")' : '' }}</p>
                </div>
                <div>
                    <span class="text-subtle text-[11px] uppercase font-semibold">Tempat & Tanggal Lahir</span>
                    <p class="font-medium text-ink">{{ $personal['birth_place'] ?? '—' }}, {{ !empty($personal['birth_date']) ? \Carbon\Carbon::parse($personal['birth_date'])->translatedFormat('d F Y') : '—' }}</p>
                </div>
                <div>
                    <span class="text-subtle text-[11px] uppercase font-semibold">Jenis Kelamin / Gol. Darah</span>
                    <p class="font-medium text-ink">{{ ($personal['gender'] ?? '') === 'pria' || ($personal['gender'] ?? '') === 'M' ? 'Pria' : 'Wanita' }} {{ !empty($personal['blood_type']) ? '· Gol. ' . $personal['blood_type'] : '' }}</p>
                </div>
                <div>
                    <span class="text-subtle text-[11px] uppercase font-semibold">Agama / Suku / Warga Negara</span>
                    <p class="font-medium text-ink">{{ $personal['religion'] ?? '—' }} · {{ $personal['race'] ?? '—' }} · {{ $personal['nationality'] ?? 'Indonesia' }}</p>
                </div>
                <div>
                    <span class="text-subtle text-[11px] uppercase font-semibold">Status Pernikahan</span>
                    <p class="font-medium text-ink">{{ $personal['marital_status'] ?? '—' }}</p>
                </div>
                <div>
                    <span class="text-subtle text-[11px] uppercase font-semibold">Anak ke- / Bersaudara</span>
                    <p class="font-medium text-ink">{{ !empty($personal['birth_order']) ? 'Anak ke-' . $personal['birth_order'] . ' dari ' . ($personal['siblings_count'] ?? '?') . ' bersaudara' : '—' }}</p>
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <span class="text-subtle text-[11px] uppercase font-semibold">Alamat Tempat Tinggal Sekarang</span>
                    <p class="font-medium text-ink">{{ $personal['current_address'] ?? '—' }}</p>
                </div>
                <div>
                    <span class="text-subtle text-[11px] uppercase font-semibold">Nomor KTP</span>
                    <p class="font-mono font-medium text-ink">{{ $personal['ktp_no'] ?? '—' }}</p>
                </div>
                <div>
                    <span class="text-subtle text-[11px] uppercase font-semibold">Nomor NPWP</span>
                    <p class="font-mono font-medium text-ink">{{ $personal['npwp_no'] ?? '—' }}</p>
                </div>
                <div>
                    <span class="text-subtle text-[11px] uppercase font-semibold">BPJS Ketenagakerjaan</span>
                    <p class="font-mono font-medium text-ink">{{ $personal['bpjs_tk_no'] ?? '—' }}</p>
                </div>
                <div>
                    <span class="text-subtle text-[11px] uppercase font-semibold">Kontak HP / Email</span>
                    <p class="font-medium text-ink">{{ $personal['phone_mobile'] ?? '—' }} · {{ $personal['email'] ?? '—' }}</p>
                </div>
                <div>
                    <span class="text-subtle text-[11px] uppercase font-semibold">Hobi / Kegemaran</span>
                    <p class="font-medium text-ink">{{ $personal['hobby'] ?? '—' }}</p>
                </div>
            </div>

            <div class="mt-6 border-t border-line pt-4">
                <span class="text-[12px] font-bold text-ink uppercase tracking-wider">Berkas Lampiran Dokumen:</span>
                <div class="mt-3 flex flex-wrap gap-2.5">
                    @foreach (['ktp' => 'Kartu KTP', 'kk' => 'Kartu Keluarga (KK)', 'npwp' => 'Kartu NPWP', 'bpjs' => 'BPJS Ketenagakerjaan'] as $docKey => $docLabel)
                        @if (!empty($attachments[$docKey]))
                            <a href="{{ asset('storage/' . $attachments[$docKey]) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-xl border border-line bg-surface-2 px-3 py-1.5 text-[12px] font-semibold text-brand hover:bg-surface">
                                <x-icon name="paperclip" class="size-3.5" />
                                {{ $docLabel }} (Buka File)
                            </a>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-xl border border-line/60 bg-surface-2/40 px-3 py-1.5 text-[12px] text-subtle">
                                {{ $docLabel }} (Belum diunggah)
                            </span>
                        @endif
                    @endforeach
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="2. Informasi Rekening Bank" description="Data bank untuk payroll penggajian">
            <div class="grid gap-4 sm:grid-cols-2 text-[13px]">
                <div class="rounded-xl border border-blue-500/20 bg-blue-50/15 p-4">
                    <span class="font-bold text-blue-900 dark:text-blue-300">Rekening Bank BCA:</span>
                    <p class="mt-2 text-ink">No. Rekening: <strong>{{ $bankAccounts['bca']['number'] ?? '—' }}</strong></p>
                    <p class="text-muted text-[12px]">Kantor Cabang: {{ $bankAccounts['bca']['branch'] ?? '—' }}</p>
                    <p class="text-muted text-[12px]">Atas Nama: {{ $bankAccounts['bca']['holder'] ?? ($personal['full_name'] ?? '—') }}</p>
                </div>

                <div class="rounded-xl border border-line bg-surface-2/30 p-4">
                    <span class="font-bold text-ink">Rekening Bank Lainnya:</span>
                    <p class="mt-2 text-ink">Bank: <strong>{{ $bankAccounts['other']['bank_name'] ?? '—' }}</strong> · No: <strong>{{ $bankAccounts['other']['number'] ?? '—' }}</strong></p>
                    <p class="text-muted text-[12px]">Cabang: {{ $bankAccounts['other']['branch'] ?? '—' }} · A/N: {{ $bankAccounts['other']['holder'] ?? '—' }}</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="3. Riwayat Pendidikan Formal & Informal">
            <div class="space-y-4 text-[13px]">
                <div>
                    <h4 class="font-bold text-ink mb-2">Pendidikan Formal</h4>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-[12.5px]">
                            <thead class="border-b border-line text-[11px] uppercase tracking-wider text-subtle bg-surface-2/40">
                                <tr>
                                    <th class="py-2 px-3">Sekolah / Kampus</th>
                                    <th class="py-2 px-3">Jurusan</th>
                                    <th class="py-2 px-3">Periode</th>
                                    <th class="py-2 px-3">Status</th>
                                    <th class="py-2 px-3">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line/60">
                                @forelse ($eduFormal as $f)
                                    @if (!empty($f['school']))
                                        <tr>
                                            <td class="py-2.5 px-3 font-medium">{{ $f['school'] }}</td>
                                            <td class="py-2.5 px-3">{{ $f['major'] ?? '—' }}</td>
                                            <td class="py-2.5 px-3">{{ $f['period'] ?? '—' }}</td>
                                            <td class="py-2.5 px-3">{{ $f['graduated'] ?? 'Tamat' }}</td>
                                            <td class="py-2.5 px-3 text-subtle">{{ $f['notes'] ?? '—' }}</td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr><td colspan="5" class="py-3 px-3 text-center text-muted">Tidak ada data.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if (!empty($eduInformal))
                    <div class="border-t border-line pt-4">
                        <h4 class="font-bold text-ink mb-2">Pendidikan Informal / Pelatihan</h4>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-[12.5px]">
                                <thead class="border-b border-line text-[11px] uppercase tracking-wider text-subtle bg-surface-2/40">
                                    <tr>
                                        <th class="py-2 px-3">Institusi</th>
                                        <th class="py-2 px-3">Jenis Kursus</th>
                                        <th class="py-2 px-3">Periode</th>
                                        <th class="py-2 px-3">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line/60">
                                    @foreach ($eduInformal as $inf)
                                        @if (!empty($inf['institute']))
                                            <tr>
                                                <td class="py-2.5 px-3 font-medium">{{ $inf['institute'] }}</td>
                                                <td class="py-2.5 px-3">{{ $inf['training'] ?? '—' }}</td>
                                                <td class="py-2.5 px-3">{{ $inf['period'] ?? '—' }}</td>
                                                <td class="py-2.5 px-3 text-subtle">{{ $inf['notes'] ?? '—' }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </x-ui.card>

        <div class="grid gap-6 lg:grid-cols-2">
            <x-ui.card title="4. Pengalaman Kerja">
                <div class="space-y-3 text-[12.5px]">
                    @forelse ($workExperiences as $we)
                        @if (!empty($we['company']))
                            <div class="rounded-xl border border-line bg-surface-2/30 p-3">
                                <p class="font-bold text-ink">{{ $we['position'] ?? '—' }}</p>
                                <p class="text-muted">{{ $we['company'] }} ({{ $we['period'] ?? '' }})</p>
                                <p class="text-subtle text-[11.5px] mt-1">{{ $we['address'] ?? '' }} · {{ $we['notes'] ?? '' }}</p>
                            </div>
                        @endif
                    @empty
                        <p class="text-muted">Tidak ada riwayat kerja.</p>
                    @endforelse
                </div>
            </x-ui.card>

            <x-ui.card title="5. Tempat Tinggal Sejak Lahir (Kronologis)">
                <div class="space-y-2.5 text-[12.5px]">
                    @forelse ($residences as $r)
                        @if (!empty($r['address']) || !empty($r['period']))
                            <div class="flex items-center gap-2 rounded-xl border border-line bg-surface-2/20 p-2.5">
                                <span class="rounded bg-surface px-1.5 py-0.5 font-mono text-[11px] text-muted">{{ $r['period'] ?? '—' }}</span>
                                <span class="font-medium text-ink">{{ $r['address'] ?? '' }}</span>
                                <span class="text-subtle text-[11.5px]">{{ $r['city'] ?? '' }}</span>
                            </div>
                        @endif
                    @empty
                        <p class="text-muted">Tidak ada data tempat tinggal.</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>

        <x-ui.card title="6. Keterangan Keluarga & 3 Sahabat Karib">
            <div class="space-y-4 text-[13px]">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-line bg-surface-2/30 p-3.5">
                        <span class="font-bold text-ink">Bapak:</span> {{ $family['father']['name'] ?? '—' }} ({{ $family['father']['job'] ?? '—' }})
                        <p class="text-subtle text-[11.5px]">HP: {{ $family['father']['phone'] ?? '—' }}</p>
                    </div>
                    <div class="rounded-xl border border-line bg-surface-2/30 p-3.5">
                        <span class="font-bold text-ink">Ibu:</span> {{ $family['mother']['name'] ?? '—' }} ({{ $family['mother']['job'] ?? '—' }})
                        <p class="text-subtle text-[11.5px]">HP: {{ $family['mother']['phone'] ?? '—' }}</p>
                    </div>
                </div>

                @if (!empty($family['spouse']['name']))
                    <div class="rounded-xl border border-line bg-surface-2/30 p-3.5">
                        <span class="font-bold text-ink">Istri / Suami:</span> {{ $family['spouse']['name'] }} ({{ $family['spouse']['job'] ?? '—' }}) · HP: {{ $family['spouse']['phone'] ?? '—' }}
                    </div>
                @endif

                <div class="border-t border-line pt-4">
                    <h4 class="font-bold text-ink mb-2">3 Sahabat Karib / Kawan Terdekat (Yang Masih Hidup):</h4>
                    <div class="grid gap-3 sm:grid-cols-3">
                        @foreach ($family['friends'] ?? [] as $idx => $fr)
                            @if (!empty($fr['name']))
                                <div class="rounded-xl border border-line bg-surface-2/30 p-3 text-[12.5px]">
                                    <p class="font-bold text-ink">#{{ $idx + 1 }} {{ $fr['name'] }}</p>
                                    <p class="text-muted text-[12px]">{{ $fr['phone'] ?? '—' }}</p>
                                    <p class="text-subtle text-[11.5px] mt-1">{{ $fr['job_school'] ?? '—' }} · {{ $fr['notes'] ?? '' }}</p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="7. Pengalaman Organisasi & Pernyataan">
            <div class="space-y-4 text-[13px]">
                @if (!empty($organizations))
                    <div class="space-y-2">
                        @foreach ($organizations as $org)
                            @if (!empty($org['name']))
                                <div class="flex items-center justify-between rounded-xl border border-line bg-surface-2/20 p-2.5 text-[12.5px]">
                                    <div>
                                        <strong class="text-ink">{{ $org['name'] }}</strong> ({{ $org['place'] ?? '' }})
                                        <p class="text-subtle text-[11.5px]">Jabatan: {{ $org['position'] ?? 'Anggota' }}</p>
                                    </div>
                                    <span class="font-mono text-muted text-[11.5px]">{{ $org['period'] ?? '' }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

                <div class="rounded-xl border border-line bg-surface-2/30 p-4 text-[12.5px]">
                    <p><strong>Pernyataan Karyawan:</strong> Keterangan telah dibuat dengan sebenar-benarnya dan penuh tanggung jawab.</p>
                    <p class="mt-1 text-subtle">Ditandatangani di {{ $biodata->signed_city ?? 'Jakarta' }}, tanggal {{ $biodata->signed_date ?? $biodata->submitted_at?->format('d/m/Y') }} oleh <strong>{{ $biodata->signed_name ?? $personal['full_name'] }}</strong></p>
                </div>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>