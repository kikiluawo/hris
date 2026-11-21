<x-layouts.app :title="'Permintaan ' . $hiringRequest->reference()">
    @php
        $statusTones = [
            'baru' => 'info',
            'direview' => 'warning',
            'menunggu_approval' => 'warning',
            'terbit' => 'success',
            'ditolak' => 'danger',
        ];
        $statusLabels = \App\Models\HiringRequest::statusOptions();
        $currentUser = auth()->user();
    @endphp

    <x-ui.page-header
        title="Tinjau permintaan"
        :description="$hiringRequest->reference() . ' · Masuk ' . $hiringRequest->created_at->translatedFormat('d M Y H:i')"
    >
        <x-slot:actions>
            <x-ui.badge :variant="$statusTones[$hiringRequest->status] ?? 'neutral'">{{ $statusLabels[$hiringRequest->status] ?? ucfirst($hiringRequest->status) }}</x-ui.badge>
            <x-ui.button :href="route('hiring-requests.index')" variant="secondary" icon="chevron-left">Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if (session('status'))
        <x-ui.alert variant="success" class="mb-4">{{ session('status') }}</x-ui.alert>
    @endif

    @if (session('error'))
        <x-ui.alert variant="danger" class="mb-4">{{ session('error') }}</x-ui.alert>
    @endif

    @if ($hiringRequest->status === 'terbit' && $hiringRequest->jobOpening)
        <x-ui.alert variant="success" class="mb-4">
            <p>
                Permintaan ini sudah disetujui dan terbit sebagai lowongan
                <span class="font-semibold">{{ $hiringRequest->jobOpening->title }}</span>
                pada {{ $hiringRequest->published_at?->translatedFormat('d M Y H:i') }}.
                Hanya detail lowongan yang tampil di portal publik untuk pelamar.
            </p>
            <div class="mt-2 flex flex-wrap gap-2">
                <x-ui.button size="sm" :href="route('career.show', $hiringRequest->jobOpening->slug)" target="_blank" icon="external">Lihat di halaman karir</x-ui.button>
                <x-ui.button size="sm" variant="secondary" :href="route('openings.index', ['edit' => $hiringRequest->jobOpening->id])" icon="pencil">Edit lowongan</x-ui.button>
            </div>
        </x-ui.alert>
    @endif

    @if ($hiringRequest->status === 'menunggu_approval')
        <div class="mb-4 rounded-2xl border border-amber-300/60 bg-amber-50/70 p-4 text-[13px] text-amber-900 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-200">
            <div class="flex items-start gap-3">
                <x-icon name="clock" class="mt-0.5 size-5 shrink-0 text-amber-600 dark:text-amber-300" />
                <div class="space-y-1">
                    <p class="font-bold">Permintaan sedang dalam proses persetujuan eksekutif</p>
                    <p class="text-[12.5px] leading-relaxed text-amber-800/90 dark:text-amber-300/80">
                        Lowongan akan otomatis terbit di portal karir setelah seluruh persetujuan yang disyaratkan terpenuhi.
                    </p>
                    <div class="mt-3 flex flex-wrap gap-4 pt-1">
                        @if ($hiringRequest->needsCeoApproval())
                            <div class="flex items-center gap-2">
                                <span class="font-semibold">CEO (Hadi Ong):</span>
                                @if ($hiringRequest->ceo_approval_status === 'approved')
                                    <x-ui.badge variant="success">Disetujui {{ $hiringRequest->ceo_approved_at?->translatedFormat('d M H:i') }}</x-ui.badge>
                                @elseif ($hiringRequest->ceo_approval_status === 'rejected')
                                    <x-ui.badge variant="danger">Ditolak</x-ui.badge>
                                @else
                                    <x-ui.badge variant="warning">Menunggu persetujuan</x-ui.badge>
                                @endif
                            </div>
                        @endif

                        @if ($hiringRequest->needsCfoApproval())
                            <div class="flex items-center gap-2">
                                <span class="font-semibold">CFO (Sri Rahayu):</span>
                                @if ($hiringRequest->cfo_approval_status === 'approved')
                                    <x-ui.badge variant="success">Disetujui {{ $hiringRequest->cfo_approved_at?->translatedFormat('d M H:i') }}</x-ui.badge>
                                @elseif ($hiringRequest->cfo_approval_status === 'rejected')
                                    <x-ui.badge variant="danger">Ditolak</x-ui.badge>
                                @else
                                    <x-ui.badge variant="warning">Menunggu persetujuan</x-ui.badge>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($hiringRequest->status === 'ditolak')
        <x-ui.alert variant="danger" class="mb-4">
            <p><span class="font-semibold">Permintaan ditolak.</span> {{ $hiringRequest->rejection_reason }}</p>
        </x-ui.alert>
    @endif

    <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_340px] xl:items-start">
        <div class="space-y-4">
            {{-- Form Peninjauan Data Lowongan --}}
            <x-ui.card title="Data lowongan" description="Lengkapi kekurangan dari pengaju sebelum diterbitkan ke halaman karir.">
                <form
                    method="POST"
                    action="{{ route('hiring-requests.update', $hiringRequest) }}"
                    class="space-y-6"
                    x-data="{
                        submitting: false,
                        requestType: '{{ old('request_type', $hiringRequest->request_type ?? 'baru') }}',
                        approvalRoute: '{{ old('approval_route', $hiringRequest->approval_route ?? 'direct') }}'
                    }"
                    @submit="submitting = true"
                >
                    @csrf
                    @method('PATCH')

                    @if ($errors->any())
                        <x-ui.alert variant="danger">
                            <ul class="list-disc space-y-0.5 pl-4">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </x-ui.alert>
                    @endif

                    {{-- Jenis Kebutuhan Karyawan --}}
                    <div class="space-y-3 rounded-2xl border border-line bg-surface-2/30 p-4.5">
                        <div>
                            <x-ui.label>Jenis kebutuhan karyawan <span class="text-rose-500">*</span></x-ui.label>
                            <p class="text-[12px] text-muted">Karyawan baru untuk penambahan kuota, atau pergantian untuk mengganti personil lama.</p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <label
                                class="flex items-start gap-3 rounded-xl border p-3 transition {{ $editable ? 'cursor-pointer' : 'opacity-70' }}"
                                :class="requestType === 'baru' ? 'border-brand bg-brand-soft/40 shadow-sm' : 'border-line bg-surface'"
                            >
                                <input
                                    type="radio"
                                    name="request_type"
                                    value="baru"
                                    x-model="requestType"
                                    class="mt-0.5 text-brand focus:ring-brand"
                                    {{ ! $editable ? 'disabled' : '' }}
                                />
                                <div>
                                    <p class="text-[13px] font-semibold text-ink">Karyawan Baru</p>
                                    <p class="text-[11.5px] text-muted leading-relaxed">Penambahan posisi baru / kuota ekspansi.</p>
                                </div>
                            </label>

                            <label
                                class="flex items-start gap-3 rounded-xl border p-3 transition {{ $editable ? 'cursor-pointer' : 'opacity-70' }}"
                                :class="requestType === 'pergantian' ? 'border-brand bg-brand-soft/40 shadow-sm' : 'border-line bg-surface'"
                            >
                                <input
                                    type="radio"
                                    name="request_type"
                                    value="pergantian"
                                    x-model="requestType"
                                    class="mt-0.5 text-brand focus:ring-brand"
                                    {{ ! $editable ? 'disabled' : '' }}
                                />
                                <div>
                                    <p class="text-[13px] font-semibold text-ink">Pergantian Karyawan</p>
                                    <p class="text-[11.5px] text-muted leading-relaxed">Menggantikan karyawan sebelumnya (resign/mutasi).</p>
                                </div>
                            </label>
                        </div>
                        <x-ui.field-error name="request_type" />

                        <div x-show="requestType === 'pergantian'" x-cloak class="grid gap-3 pt-2 sm:grid-cols-2">
                            <div>
                                <x-ui.label for="replaced_employee_name">Nama karyawan yang digantikan <span class="text-rose-500">*</span></x-ui.label>
                                <x-ui.input name="replaced_employee_name" :value="$hiringRequest->replaced_employee_name" placeholder="Nama karyawan lama" :disabled="! $editable" />
                                <x-ui.field-error name="replaced_employee_name" />
                            </div>

                            <div>
                                <x-ui.label for="replacement_reason">Alasan pergantian <span class="text-rose-500">*</span></x-ui.label>
                                <x-ui.input name="replacement_reason" :value="$hiringRequest->replacement_reason" placeholder="Contoh: Resign akhir bulan ini, mutasi, dll." :disabled="! $editable" />
                                <x-ui.field-error name="replacement_reason" />
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-ui.label for="title">Jabatan lowongan</x-ui.label>
                            <x-ui.input name="title" :value="$hiringRequest->title" placeholder="Contoh: Service Advisor" :disabled="! $editable" />
                            <x-ui.field-error name="title" />
                        </div>

                        <div>
                            <x-ui.label for="job_type">Tipe pekerjaan (Job Type)</x-ui.label>
                            <x-ui.select name="job_type" :options="$jobTypes" :selected="old('job_type', $hiringRequest->job_type ?? \App\Models\JobOpening::TYPE_FULL_TIME)" placeholder="Pilih tipe" :disabled="! $editable" />
                            <x-ui.field-error name="job_type" />
                        </div>

                        <div>
                            <x-ui.label for="headcount">Jumlah orang</x-ui.label>
                            <x-ui.input type="number" name="headcount" :value="$hiringRequest->headcount" min="1" max="100" inputmode="numeric" :disabled="! $editable" />
                            <x-ui.field-error name="headcount" />
                        </div>

                        <div>
                            <x-ui.label for="company_id">PT penempatan</x-ui.label>
                            <x-ui.select name="company_id" :options="$companies->all()" :selected="$hiringRequest->company_id" placeholder="Belum ditentukan" data-select2-clear :disabled="! $editable" />
                            <p class="mt-1 text-[11.5px] text-subtle">Biarkan kosong jika PT diputuskan setelah kandidat diterima.</p>
                            <x-ui.field-error name="company_id" />
                        </div>

                        <div>
                            <x-ui.label for="department_id">Departemen</x-ui.label>
                            <x-ui.select name="department_id" :options="$departments->all()" :selected="$hiringRequest->department_id" placeholder="Pilih departemen" data-select2-clear :disabled="! $editable" />
                            <x-ui.field-error name="department_id" />
                        </div>

                        <div>
                            <x-ui.label for="position_id">Jabatan internal</x-ui.label>
                            <x-ui.select name="position_id" :options="$positions->all()" :selected="$hiringRequest->position_id" placeholder="Pilih jabatan" data-select2-clear :disabled="! $editable" />
                            <x-ui.field-error name="position_id" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-ui.label for="description">Deskripsi &amp; tanggung jawab</x-ui.label>
                            <x-ui.textarea name="description" :value="$hiringRequest->description" :rows="7" placeholder="Tuliskan tanggung jawab, jam kerja, dan informasi lain yang perlu diketahui pelamar." :disabled="! $editable" />
                            <x-ui.field-error name="description" />
                        </div>
                    </div>

                    <div class="rounded-2xl border border-line bg-surface-2/40 px-4 py-3 text-[12.5px] leading-relaxed text-muted">
                        Kualifikasi ini dipakai untuk menyaring CV pelamar secara otomatis.
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-ui.label for="required_skills">Keahlian wajib</x-ui.label>
                            <x-ui.textarea name="required_skills" :value="implode(', ', $hiringRequest->required_skills ?? [])" :rows="3" placeholder="Service advisor, estimasi biaya, komunikasi" :disabled="! $editable" />
                            <p class="mt-1 text-[11.5px] text-subtle">Pisahkan dengan koma. Maksimal 20 keahlian.</p>
                            <x-ui.field-error name="required_skills" />
                        </div>

                        <div>
                            <x-ui.label for="min_education">Pendidikan minimum</x-ui.label>
                            <x-ui.select name="min_education" :options="$educationLevels" :selected="$hiringRequest->min_education" placeholder="Tidak ditentukan" data-select2-clear :disabled="! $editable" />
                            <x-ui.field-error name="min_education" />
                        </div>

                        <div>
                            <x-ui.label for="min_experience">Pengalaman minimum (tahun)</x-ui.label>
                            <x-ui.input type="number" name="min_experience" :value="$hiringRequest->min_experience" min="0" max="50" placeholder="0" :disabled="! $editable" />
                            <x-ui.field-error name="min_experience" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-ui.label for="screening_threshold">Ambang lolos screening (%)</x-ui.label>
                            <x-ui.input type="number" name="screening_threshold" :value="$hiringRequest->screening_threshold ?? 70" min="0" max="100" placeholder="70" :disabled="! $editable" />
                            <p class="mt-1 text-[11.5px] text-subtle">Pelamar dengan skor CV di bawah angka ini tidak diloloskan otomatis. Default 70.</p>
                            <x-ui.field-error name="screening_threshold" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-ui.label for="hr_notes">Catatan internal HRD</x-ui.label>
                            <x-ui.textarea name="hr_notes" :value="$hiringRequest->hr_notes" :rows="3" placeholder="Catatan ini tidak tampil ke pelamar maupun pengaju." :disabled="! $editable" />
                            <x-ui.field-error name="hr_notes" />
                        </div>
                    </div>

                    {{-- Alur Persetujuan (Hanya tampil saat form masih dapat diedit oleh HRD) --}}
                    @if ($editable && $currentUser?->isHrd())
                        <div class="rounded-2xl border border-line bg-surface-2/40 p-4.5 space-y-3">
                            <div>
                                <p class="text-[13px] font-bold text-ink">Tentukan Alur Persetujuan &amp; Penerbitan</p>
                                <p class="text-[12px] text-muted">Pilih apakah lowongan langsung diterbitkan atau memerlukan approval CEO/CFO terlebih dahulu.</p>
                            </div>

                            <div class="grid gap-2.5 sm:grid-cols-2">
                                <label class="flex items-start gap-3 rounded-xl border p-3 cursor-pointer transition" :class="approvalRoute === 'direct' ? 'border-brand bg-brand-soft/50 shadow-sm' : 'border-line bg-surface hover:border-brand/40'">
                                    <input type="radio" name="approval_route" value="direct" x-model="approvalRoute" class="mt-0.5 text-brand focus:ring-brand" />
                                    <div>
                                        <p class="text-[13px] font-semibold text-ink">Langsung Approved &amp; Terbit</p>
                                        <p class="text-[11.5px] text-muted leading-relaxed">Tanpa approval CEO/CFO. Lowongan langsung terbit di halaman karir.</p>
                                    </div>
                                </label>

                                <label class="flex items-start gap-3 rounded-xl border p-3 cursor-pointer transition" :class="approvalRoute === 'ceo' ? 'border-brand bg-brand-soft/50 shadow-sm' : 'border-line bg-surface hover:border-brand/40'">
                                    <input type="radio" name="approval_route" value="ceo" x-model="approvalRoute" class="mt-0.5 text-brand focus:ring-brand" />
                                    <div>
                                        <p class="text-[13px] font-semibold text-ink">Approval CEO (Hadi Ong)</p>
                                        <p class="text-[11.5px] text-muted leading-relaxed">Menunggu persetujuan CEO sebelum lowongan otomatis terbit.</p>
                                    </div>
                                </label>

                                <label class="flex items-start gap-3 rounded-xl border p-3 cursor-pointer transition" :class="approvalRoute === 'cfo' ? 'border-brand bg-brand-soft/50 shadow-sm' : 'border-line bg-surface hover:border-brand/40'">
                                    <input type="radio" name="approval_route" value="cfo" x-model="approvalRoute" class="mt-0.5 text-brand focus:ring-brand" />
                                    <div>
                                        <p class="text-[13px] font-semibold text-ink">Approval CFO (Sri Rahayu)</p>
                                        <p class="text-[11.5px] text-muted leading-relaxed">Menunggu persetujuan CFO sebelum lowongan otomatis terbit.</p>
                                    </div>
                                </label>

                                <label class="flex items-start gap-3 rounded-xl border p-3 cursor-pointer transition" :class="approvalRoute === 'both' ? 'border-brand bg-brand-soft/50 shadow-sm' : 'border-line bg-surface hover:border-brand/40'">
                                    <input type="radio" name="approval_route" value="both" x-model="approvalRoute" class="mt-0.5 text-brand focus:ring-brand" />
                                    <div>
                                        <p class="text-[13px] font-semibold text-ink">Approval Keduanya (CEO &amp; CFO)</p>
                                        <p class="text-[11.5px] text-muted leading-relaxed">Menunggu persetujuan Hadi Ong &amp; Sri Rahayu sebelum terbit.</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-end gap-2 border-t border-line pt-5">
                            <x-ui.button type="submit" variant="secondary" icon="check" x-bind:disabled="submitting">Simpan perubahan</x-ui.button>
                            <x-ui.button
                                type="submit"
                                variant="gold"
                                icon="upload"
                                formaction="{{ route('hiring-requests.publish', $hiringRequest) }}"
                                data-confirm="Data lowongan akan disimpan dan diproses sesuai alur persetujuan yang dipilih."
                                data-confirm-title="Proses persetujuan &amp; penerbitan?"
                                data-confirm-label="Ya, proses"
                                x-bind:disabled="submitting"
                            >
                                <span x-text="approvalRoute === 'direct' ? 'Langsung Terbitkan ke Karir' : 'Kirim untuk Approval Eksekutif'">Proses</span>
                            </x-ui.button>
                        </div>
                    @endif
                </form>
            </x-ui.card>
        </div>

        <aside class="space-y-4 xl:sticky xl:top-24">
            {{-- Panel Persetujuan Khusus Eksekutif (CEO / CFO / HRD) --}}
            @if ($hiringRequest->status === 'menunggu_approval')
                @php
                    $isCeoPending = $hiringRequest->needsCeoApproval() && $hiringRequest->ceo_approval_status === 'pending';
                    $isCfoPending = $hiringRequest->needsCfoApproval() && $hiringRequest->cfo_approval_status === 'pending';
                @endphp

                <x-ui.card title="Aksi Persetujuan Eksekutif">
                    @if ($currentUser?->isCeo() && $isCeoPending)
                        <div class="space-y-3">
                            <p class="text-[12.5px] text-muted">Halo <span class="font-semibold text-ink">Hadi Ong</span>, permintaan ini membutuhkan persetujuan Anda sebagai CEO.</p>
                            <form method="POST" action="{{ route('hiring-requests.approve', $hiringRequest) }}" class="space-y-3">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="acting_as" value="ceo">
                                <x-ui.label for="approval_notes">Catatan CEO (opsional)</x-ui.label>
                                <x-ui.textarea name="approval_notes" :rows="2" placeholder="Catatan persetujuan..." />
                                <x-ui.button type="submit" variant="gold" icon="check" class="w-full">Setujui Permintaan (CEO)</x-ui.button>
                            </form>
                        </div>
                    @elseif ($currentUser?->isCfo() && $isCfoPending)
                        <div class="space-y-3">
                            <p class="text-[12.5px] text-muted">Halo <span class="font-semibold text-ink">Sri Rahayu</span>, permintaan ini membutuhkan persetujuan Anda sebagai CFO.</p>
                            <form method="POST" action="{{ route('hiring-requests.approve', $hiringRequest) }}" class="space-y-3">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="acting_as" value="cfo">
                                <x-ui.label for="approval_notes">Catatan CFO (opsional)</x-ui.label>
                                <x-ui.textarea name="approval_notes" :rows="2" placeholder="Catatan persetujuan..." />
                                <x-ui.button type="submit" variant="gold" icon="check" class="w-full">Setujui Permintaan (CFO)</x-ui.button>
                            </form>
                        </div>
                    @elseif ($currentUser?->isHrd())
                        <div class="space-y-3">
                            <p class="text-[12px] text-muted">Anda dapat menyetujui tahap approval atas nama eksekutif bila diperlukan:</p>
                            @if ($isCeoPending)
                                <form method="POST" action="{{ route('hiring-requests.approve', $hiringRequest) }}" class="space-y-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="acting_as" value="ceo">
                                    <x-ui.button type="submit" variant="secondary" size="sm" icon="check" class="w-full">Setujui atas nama CEO (Hadi Ong)</x-ui.button>
                                </form>
                            @endif

                            @if ($isCfoPending)
                                <form method="POST" action="{{ route('hiring-requests.approve', $hiringRequest) }}" class="space-y-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="acting_as" value="cfo">
                                    <x-ui.button type="submit" variant="secondary" size="sm" icon="check" class="w-full">Setujui atas nama CFO (Sri Rahayu)</x-ui.button>
                                </form>
                            @endif
                        </div>
                    @else
                        <p class="text-[12.5px] text-muted">Status persetujuan Anda sudah tercatat atau sedang menunggu pihak eksekutif lainnya.</p>
                    @endif

                    {{-- Form Penolakan Eksekutif --}}
                    <div class="mt-4 border-t border-line pt-4" x-data="{ openReject: false }">
                        <x-ui.button type="button" variant="ghost" icon="x-circle" class="w-full text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/20" x-on:click="openReject = !openReject">
                            Tolak Permintaan Ini
                        </x-ui.button>

                        <form x-show="openReject" x-cloak method="POST" action="{{ route('hiring-requests.reject', $hiringRequest) }}" class="mt-3 space-y-2">
                            @csrf
                            @method('PATCH')
                            <x-ui.label for="rejection_reason">Alasan penolakan</x-ui.label>
                            <x-ui.textarea name="rejection_reason" :rows="3" placeholder="Sertakan alasan penolakan..." required />
                            <x-ui.button type="submit" variant="danger" size="sm" class="w-full">Kirim Penolakan</x-ui.button>
                        </form>
                    </div>
                </x-ui.card>
            @endif

            {{-- Kartu Data Pengaju --}}
            <x-ui.card title="Data pengaju">
                <dl class="space-y-3.5 text-[13px]">
                    <div class="flex items-start gap-3">
                        <x-icon name="user" class="mt-0.5 size-4 shrink-0 text-subtle" />
                        <div>
                            <dt class="text-[11px] uppercase tracking-[0.08em] text-subtle">Nama</dt>
                            <dd class="mt-0.5 font-semibold">{{ $hiringRequest->requester_name }}</dd>
                            <dd class="text-[12px] text-muted">{{ $hiringRequest->requester_position }}</dd>
                        </div>
                    </div>

                    {{-- Jenis Pengajuan & Karyawan yang Digantikan --}}
                    <div class="flex items-start gap-3 border-t border-line pt-3.5">
                        <x-icon name="briefcase" class="mt-0.5 size-4 shrink-0 text-subtle" />
                        <div>
                            <dt class="text-[11px] uppercase tracking-[0.08em] text-subtle">Jenis kebutuhan</dt>
                            <dd class="mt-1">
                                @if ($hiringRequest->isReplacement())
                                    <x-ui.badge variant="warning">Pergantian Karyawan</x-ui.badge>
                                    <p class="mt-2 text-[12px] text-muted">
                                        <span class="font-semibold text-ink">Menggantikan:</span> {{ $hiringRequest->replaced_employee_name ?: '—' }}
                                    </p>
                                    @if ($hiringRequest->replacement_reason)
                                        <p class="mt-1 text-[11.5px] text-muted leading-relaxed">
                                            <span class="font-semibold text-ink">Alasan:</span> {{ $hiringRequest->replacement_reason }}
                                        </p>
                                    @endif
                                @else
                                    <x-ui.badge variant="info">Karyawan Baru</x-ui.badge>
                                    <p class="mt-1 text-[12px] text-muted">Penambahan kuota / formasi baru.</p>
                                @endif
                            </dd>
                        </div>
                    </div>

                    @if ($hiringRequest->requesting_unit)
                        <div class="flex items-start gap-3">
                            <x-icon name="building" class="mt-0.5 size-4 shrink-0 text-subtle" />
                            <div>
                                <dt class="text-[11px] uppercase tracking-[0.08em] text-subtle">Unit / divisi</dt>
                                <dd class="mt-0.5">{{ $hiringRequest->requesting_unit }}</dd>
                            </div>
                        </div>
                    @endif

                    <div class="flex items-start gap-3">
                        <x-icon name="phone" class="mt-0.5 size-4 shrink-0 text-subtle" />
                        <div>
                            <dt class="text-[11px] uppercase tracking-[0.08em] text-subtle">HP / WhatsApp</dt>
                            <dd class="mt-0.5">{{ $hiringRequest->requester_phone }}</dd>
                        </div>
                    </div>

                    @if ($hiringRequest->requester_email)
                        <div class="flex items-start gap-3">
                            <x-icon name="mail" class="mt-0.5 size-4 shrink-0 text-subtle" />
                            <div>
                                <dt class="text-[11px] uppercase tracking-[0.08em] text-subtle">Email</dt>
                                <dd class="mt-0.5 break-all">{{ $hiringRequest->requester_email }}</dd>
                            </div>
                        </div>
                    @endif

                    @if ($hiringRequest->notes)
                        <div class="flex items-start gap-3">
                            <x-icon name="file-text" class="mt-0.5 size-4 shrink-0 text-subtle" />
                            <div>
                                <dt class="text-[11px] uppercase tracking-[0.08em] text-subtle">Catatan pengaju</dt>
                                <dd class="mt-0.5 whitespace-pre-line text-[12.5px] text-muted">{{ $hiringRequest->notes }}</dd>
                            </div>
                        </div>
                    @endif

                    <div class="flex items-start gap-3 border-t border-line pt-3.5">
                        <x-icon name="clock" class="mt-0.5 size-4 shrink-0 text-subtle" />
                        <div>
                            <dt class="text-[11px] uppercase tracking-[0.08em] text-subtle">Ditinjau HRD</dt>
                            <dd class="mt-0.5 text-[12.5px] text-muted">
                                @if ($hiringRequest->reviewed_at)
                                    {{ $hiringRequest->reviewer?->name ?? 'HRD' }} · {{ $hiringRequest->reviewed_at->translatedFormat('d M Y H:i') }}
                                @else
                                    Belum ditinjau
                                @endif
                            </dd>
                        </div>
                    </div>
                </dl>
            </x-ui.card>

            {{-- Penolakan HRD saat status masih baru atau direview --}}
            @if ($editable && $hiringRequest->status !== 'menunggu_approval' && $currentUser?->isHrd())
                <x-ui.card title="Tindakan Lainnya">
                    <form method="POST" action="{{ route('hiring-requests.reject', $hiringRequest) }}" x-data="{ open: false }">
                        @csrf
                        @method('PATCH')

                        <x-ui.button type="button" variant="secondary" icon="x-circle" class="w-full" x-on:click="open = !open">Tolak permintaan</x-ui.button>

                        <div x-show="open" x-cloak class="mt-3 space-y-2">
                            <x-ui.label for="rejection_reason">Alasan penolakan</x-ui.label>
                            <x-ui.textarea name="rejection_reason" :rows="3" placeholder="Contoh: kebutuhan sudah terpenuhi lewat rekrutmen internal." required />
                            <x-ui.field-error name="rejection_reason" />
                            <x-ui.button type="submit" variant="danger" size="sm" class="w-full">Kirim penolakan</x-ui.button>
                        </div>
                    </form>
                </x-ui.card>
            @endif
        </aside>
    </div>
</x-layouts.app>

