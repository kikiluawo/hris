<x-layouts.app title="Pelamar">
    <x-ui.page-header
        title="Pelamar"
        description="Lamaran masuk lewat halaman karir publik. Skor CV dihitung otomatis, lalu Anda bisa meloloskan, menjadwalkan interview, atau menolak."
    >
        <x-slot:actions>
            <x-ui.button :href="route('assessments.index')" variant="secondary" icon="clipboard">Tes online</x-ui.button>
            <x-ui.button :href="route('openings.index')" variant="secondary" icon="file-text">Kelola lowongan</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="space-y-4">
        @if (session('wa_job_form_url'))
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-emerald-900 dark:text-emerald-200">
                <div class="flex items-center gap-3">
                    <span class="flex size-9 items-center justify-center rounded-xl bg-emerald-600 text-white">
                        <x-icon name="link" class="size-5" />
                    </span>
                    <div>
                        <strong class="text-[14px]">Link Formulir Lamaran Kerja Siap Dikirim!</strong>
                        <p class="text-[12.5px] text-emerald-800/80 dark:text-emerald-300/80">Kirimkan tautan pengisian formulir lamaran kerja ke WhatsApp {{ session('wa_applicant_name') }}.</p>
                    </div>
                </div>
                <x-ui.button variant="primary" icon="link" :href="session('wa_job_form_url')" target="_blank" class="bg-emerald-600 hover:bg-emerald-700">
                    Buka WhatsApp Sekarang
                </x-ui.button>
            </div>
        @endif

        @if (session('wa_onboarding_url'))
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-emerald-900 dark:text-emerald-200">
                <div class="flex items-center gap-3">
                    <span class="flex size-9 items-center justify-center rounded-xl bg-emerald-600 text-white">
                        <x-icon name="user-check" class="size-5" />
                    </span>
                    <div>
                        <strong class="text-[14px]">Kandidat Diterima! Undangan Onboarding & Link Biodata Siap Dikirim</strong>
                        <p class="text-[12.5px] text-emerald-800/80 dark:text-emerald-300/80">Kirim pesan pemanggilan ke kantor dan formulir biodata karyawan ke WhatsApp {{ session('wa_applicant_name') }}.</p>
                    </div>
                </div>
                <x-ui.button variant="primary" icon="phone" :href="session('wa_onboarding_url')" target="_blank" class="bg-emerald-600 hover:bg-emerald-700">
                    Kirim WA Pemanggilan & Biodata
                </x-ui.button>
            </div>
        @endif

        <x-ui.card>
            <form method="GET" action="{{ route('applicants.index') }}" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_240px_220px_auto] xl:items-end">
                <div>
                    <x-ui.label for="q">Cari pelamar</x-ui.label>
                    <x-ui.input type="search" name="q" :value="$filters['q'] ?? null" icon="search" placeholder="Nama atau email" />
                </div>

                <div>
                    <x-ui.label for="job">Lowongan</x-ui.label>
                    <x-ui.select
                        name="job"
                        :options="$openings->all()"
                        :selected="$filters['job'] ?? null"
                        placeholder="Semua lowongan"
                        data-select2-clear
                    />
                </div>

                <div>
                    <x-ui.label for="status">Status</x-ui.label>
                    <x-ui.select
                        name="status"
                        :options="$statuses"
                        :selected="$filters['status'] ?? null"
                        placeholder="Semua status"
                        data-select2-clear
                    />
                </div>

                <div class="flex gap-2">
                    <x-ui.button type="submit" icon="filter">Terapkan</x-ui.button>
                    @if (array_filter($filters))
                        <x-ui.button variant="ghost" :href="route('applicants.index')">Reset</x-ui.button>
                    @endif
                </div>
            </form>
        </x-ui.card>

        <x-ui.card :padded="false" x-data="{ screeningModal: null, interview: null, testInvite: null, copiedId: null }">
            <div class="flex items-center justify-between px-5 py-4 sm:px-6">
                <div>
                    <h2 class="text-[15px] font-semibold">Daftar pelamar</h2>
                    <p class="mt-0.5 text-[12.5px] text-muted">{{ $applications->total() }} lamaran ditemukan</p>
                </div>
                <x-icon name="users" class="size-5 text-subtle" />
            </div>

            <div class="overflow-x-auto border-t border-line">
                <table class="w-full min-w-[1080px] text-left">
                    <thead class="bg-surface-2/60">
                        <tr class="text-[10.5px] uppercase tracking-[0.08em] text-subtle">
                            <th class="px-5 py-3 font-semibold">Pelamar</th>
                            <th class="px-3 py-3 font-semibold">Kontak</th>
                            <th class="px-2 py-3 font-semibold">Lowongan</th>
                            <th class="px-3 py-3 font-semibold">Skor CV</th>
                            <th class="px-3 py-3 font-semibold">Status</th>
                            <th class="px-2 py-3 font-semibold">Tanggal</th>
                            <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($applications as $application)
                            @php
                                $screening = $application->screening_data ?? [];
                                $score = $application->screening_score;
                                $skills = $screening['skills'] ?? [];
                                $matchedSkills = count($skills['matched'] ?? []);
                                $requiredSkills = count($skills['required'] ?? []);
                                $educationPassed = $screening['education']['passed'] ?? null;
                                $jobThreshold = $application->jobOpening->screening_threshold ?? 70;
                                $summary = collect([
                                    $requiredSkills > 0 ? $matchedSkills . '/' . $requiredSkills . ' skill' : null,
                                    $educationPassed === true ? 'pendidikan sesuai' : ($educationPassed === false ? 'pendidikan kurang' : null),
                                ])->filter()->implode(' · ');
                            @endphp

                            <tr class="border-t border-line/80 transition hover:bg-surface-2/40">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <x-ui.avatar :name="$application->candidate->name" size="sm" />
                                        <div class="min-w-0">
                                            <p class="whitespace-nowrap text-[13.5px] font-semibold">{{ $application->candidate->name }}</p>
                                            <p class="truncate text-[11.5px] text-subtle">{{ $application->candidate->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-3.5 text-[12.5px] text-muted">
                                    {{ $application->candidate->phone }}
                                    <p class="max-w-[180px] truncate text-[11px] text-subtle" title="{{ $application->candidate->address }}">{{ $application->candidate->city }}</p>
                                </td>
                                <td class="px-2 py-3.5 text-[12.5px]">
                                    <a href="{{ route('applicants.index', ['job' => $application->job_opening_id]) }}" class="font-medium text-brand hover:underline">{{ $application->jobOpening->title }}</a>
                                    <p class="text-[11px] text-subtle">{{ $application->jobOpening->company?->name ?? '—' }}</p>
                                </td>
                                <td class="px-3 py-3.5">
                                    @if ($score === null)
                                        <div class="flex flex-col items-start gap-1">
                                            <x-ui.badge variant="neutral" title="{{ $screening['reason'] ?? 'Tinjau manual.' }}">Manual</x-ui.badge>
                                            <button
                                                type="button"
                                                class="mt-0.5 inline-flex items-center gap-1 text-[11.5px] font-semibold text-brand transition hover:underline"
                                                x-on:click="screeningModal = {{ $application->id }}"
                                            >
                                                <x-icon name="sparkles" class="size-3.5" />
                                                Lihat Detail
                                            </button>
                                        </div>
                                    @else
                                        <div class="flex flex-col items-start gap-1">
                                            <button
                                                type="button"
                                                class="transition hover:opacity-85 text-left"
                                                x-on:click="screeningModal = {{ $application->id }}"
                                                title="Klik untuk melihat rincian screening CV"
                                            >
                                                <x-ui.badge :variant="$score >= $jobThreshold ? 'success' : ($score >= ($jobThreshold * 0.6) ? 'warning' : 'danger')">{{ $score }}/100</x-ui.badge>
                                            </button>
                                            @if ($summary !== '')
                                                <span class="whitespace-nowrap text-[11px] text-subtle">{{ $summary }}</span>
                                            @endif
                                            <button
                                                type="button"
                                                class="mt-0.5 inline-flex items-center gap-1 text-[11.5px] font-semibold text-brand transition hover:underline"
                                                x-on:click="screeningModal = {{ $application->id }}"
                                            >
                                                <x-icon name="sparkles" class="size-3.5" />
                                                Lihat Detail
                                            </button>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5">
                                    <x-ui.badge :variant="$application->statusTone()">{{ $application->statusLabel() }}</x-ui.badge>
                                    @if ($application->interview_at)
                                        <p class="mt-1 whitespace-nowrap text-[11px] text-subtle">{{ $application->interview_at->translatedFormat('d M Y · H:i') }}</p>
                                        @if ($application->status === 'interview_scheduled' && $application->isInterviewCompleted())
                                            <p class="mt-0.5 text-[10.5px] font-medium text-emerald-600 dark:text-emerald-400">Interview selesai</p>
                                        @endif
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-2 py-3.5 text-[12.5px] text-muted">
                                    {{ $application->created_at->translatedFormat('d M Y') }}
                                    <p class="text-[11px] text-subtle">{{ $application->created_at->format('H:i') }}</p>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                                        <x-ui.button variant="secondary" size="sm" icon="download" :href="route('applicants.cv', $application)" title="Unduh CV">CV</x-ui.button>

                                        @if (in_array($application->status, ['new', 'review', 'screening_failed'], true))
                                            <form method="POST" action="{{ route('applicants.status', $application) }}" data-confirm="Kandidat akan diloloskan screening CV dan siap ke tahap formulir / interview." data-confirm-title="Loloskan screening CV?" data-confirm-label="Ya, loloskan">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="decision" value="pass">
                                                <button type="submit" title="Loloskan screening CV" class="flex size-8 items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-400/25 dark:bg-emerald-400/10 dark:text-emerald-300 dark:hover:bg-emerald-400/20">
                                                    <x-icon name="check" class="size-4" />
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('applicants.status', $application) }}" data-confirm="Lamaran kandidat akan ditolak." data-confirm-title="Tolak lamaran?" data-confirm-tone="danger" data-confirm-label="Ya, tolak">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="decision" value="reject">
                                                <button type="submit" title="Tolak lamaran" class="flex size-8 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-rose-600 transition hover:bg-rose-100 dark:border-rose-400/25 dark:bg-rose-500/10 dark:text-rose-400 dark:hover:bg-rose-500/20">
                                                    <x-icon name="close" class="size-4" />
                                                </button>
                                            </form>

                                        @elseif ($application->status === 'hired')
                                            @if ($application->employeeBiodata)
                                                @php
                                                    $onboardingWa = \App\Services\WhatsAppLink::onboardingInvitation($application, $application->employeeBiodata->url());
                                                @endphp
                                                @if ($onboardingWa)
                                                    <a href="{{ $onboardingWa }}" target="_blank" title="Kirim WA Pemanggilan ke Kantor & Link Biodata" class="inline-flex items-center gap-1 rounded-lg border border-emerald-400/40 bg-emerald-50 px-2 py-1 text-[11px] font-semibold text-emerald-700 transition hover:bg-emerald-100">
                                                        <x-icon name="phone" class="size-3.5" />
                                                        WA Biodata
                                                    </a>
                                                @endif

                                                @if ($application->employeeBiodata->isSubmitted())
                                                    <a href="{{ route('applicants.view-biodata', $application) }}" title="Lihat Biodata Karyawan" class="inline-flex size-8 items-center justify-center rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 transition hover:bg-indigo-100">
                                                        <x-icon name="file-text" class="size-4" />
                                                    </a>
                                                @endif
                                            @endif

                                            <a href="{{ route('employees.create', ['application_id' => $application->id]) }}" title="Jadikan Karyawan Baru (Otomatis Pre-fill)" class="inline-flex items-center gap-1 rounded-lg border border-brand bg-brand px-2.5 py-1 text-[11px] font-bold text-white shadow-sm transition hover:bg-brand/90">
                                                <x-icon name="user-plus" class="size-3.5" />
                                                Jadikan Karyawan
                                            </a>

                                        @elseif ($application->status === 'rejected')
                                            <span class="text-[12px] font-semibold text-rose-600 dark:text-rose-400">Ditolak</span>

                                        @else
                                            {{-- Tahap 1: Interview --}}
                                            @if (! $application->isInterviewCompleted())
                                                <button
                                                    type="button"
                                                    title="{{ $application->interview_at ? 'Ubah jadwal interview' : 'Jadwalkan interview' }}"
                                                    class="flex size-8 items-center justify-center rounded-lg border border-sky-200 bg-sky-50 text-sky-700 transition hover:bg-sky-100 dark:border-sky-400/25 dark:bg-sky-400/10 dark:text-sky-300 dark:hover:bg-sky-400/20"
                                                    x-on:click="interview = interview === {{ $application->id }} ? null : {{ $application->id }}; $nextTick(() => window.refreshSelect2Soon(['interview_mode-{{ $application->id }}']))"
                                                >
                                                    <x-icon name="calendar" class="size-4" />
                                                </button>

                                                @if ($application->interview_at)
                                                    @php
                                                        $waInterview = \App\Services\WhatsAppLink::interviewInvitation($application);
                                                    @endphp
                                                    @if ($waInterview)
                                                        <a href="{{ $waInterview }}" target="_blank" title="Kirim undangan interview via WhatsApp" class="inline-flex size-8 items-center justify-center rounded-lg border border-sky-300 bg-sky-50 text-sky-700 transition hover:bg-sky-100 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-300">
                                                            <x-icon name="phone" class="size-4" />
                                                        </a>
                                                    @endif

                                                    <form method="POST" action="{{ route('applicants.complete-interview', $application) }}" data-confirm="Tandai bahwa interview dengan {{ $application->candidate->name }} sudah selesai dilakukan?" data-confirm-title="Interview Selesai?" data-confirm-label="Ya, Selesai">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" title="Tandai interview selesai" class="flex size-8 items-center justify-center rounded-lg border border-teal-200 bg-teal-50 text-teal-700 transition hover:bg-teal-100 dark:border-teal-400/25 dark:bg-teal-400/10 dark:text-teal-300">
                                                            <x-icon name="check-circle" class="size-4" />
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif

                                            {{-- Tahap 2: Formulir Lamaran (Muncul setelah dipanggil interview) --}}
                                            @if ($application->interview_at || in_array($application->status, ['interview_scheduled', 'assessment_sent', 'assessment_done', 'form_sent', 'form_completed'], true))
                                                @if ($application->jobApplicationForm)
                                                    @if (! $application->jobApplicationForm->isSubmitted())
                                                        @php
                                                            $formWa = \App\Services\WhatsAppLink::jobApplicationFormInvitation($application, $application->jobApplicationForm->url());
                                                        @endphp
                                                        @if ($formWa)
                                                            <a href="{{ $formWa }}" target="_blank" title="Kirim link Formulir Lamaran via WhatsApp" class="inline-flex size-8 items-center justify-center rounded-lg border border-emerald-300 bg-emerald-50 text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                                                                <x-icon name="link" class="size-4" />
                                                            </a>
                                                        @endif
                                                    @else
                                                        <a href="{{ route('applicants.view-form', $application) }}" title="Lihat Formulir Lamaran Kerja" class="inline-flex size-8 items-center justify-center rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 transition hover:bg-indigo-100 dark:border-indigo-400/25 dark:bg-indigo-400/10 dark:text-indigo-300">
                                                            <x-icon name="file-text" class="size-4" />
                                                        </a>
                                                    @endif
                                                @else
                                                    <form method="POST" action="{{ route('applicants.send-form', $application) }}">
                                                        @csrf
                                                        <button type="submit" title="Kirim Formulir Lamaran Kerja ke WhatsApp" class="flex size-8 items-center justify-center rounded-lg border border-amber-300 bg-amber-50 text-amber-700 transition hover:bg-amber-100 dark:border-amber-400/25 dark:bg-amber-400/10 dark:text-amber-300">
                                                            <x-icon name="file-plus" class="size-4" />
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif

                                            {{-- Tahap 3: Tes Online (Baru muncul ketika interview ditandai selesai) --}}
                                            @if ($application->isInterviewCompleted())
                                                <button
                                                    type="button"
                                                    title="{{ $application->status === 'assessment_done' ? 'Lihat hasil tes online' : 'Undang / kelola tes online' }}"
                                                    class="flex size-8 items-center justify-center rounded-lg border border-purple-200 bg-purple-50 text-purple-700 transition hover:bg-purple-100 dark:border-purple-400/25 dark:bg-purple-400/10 dark:text-purple-300 dark:hover:bg-purple-400/20"
                                                    x-on:click="testInvite = testInvite === {{ $application->id }} ? null : {{ $application->id }}; interview = null"
                                                >
                                                    <x-icon name="clipboard" class="size-4" />
                                                </button>
                                            @endif

                                            {{-- Tahap 4: Keputusan Akhir (Baru muncul ketika tes online selesai) --}}
                                            @if ($application->status === 'assessment_done')
                                                <form method="POST" action="{{ route('applicants.decide-final', $application) }}" data-confirm="Kandidat akan dinyatakan DITERIMA sebagai karyawan baru dan link Formulir Biodata Karyawan akan dibuatkan." data-confirm-title="Terima kandidat sebagai karyawan?" data-confirm-label="Ya, Terima Karyawan">
                                                    @csrf
                                                    <input type="hidden" name="decision" value="hire">
                                                    <button type="submit" title="Terima sebagai Karyawan (Hired)" class="inline-flex items-center gap-1 rounded-lg border border-emerald-300 bg-emerald-500 px-2 py-1 text-[11px] font-bold text-white shadow-sm transition hover:bg-emerald-600">
                                                        <x-icon name="check" class="size-3.5" />
                                                        Terima
                                                    </button>
                                                </form>

                                                <form method="POST" action="{{ route('applicants.decide-final', $application) }}" data-confirm="Kandidat dinyatakan belum sesuai / ditolak." data-confirm-title="Tolak / Belum Sesuai?" data-confirm-tone="danger" data-confirm-label="Ya, Belum Sesuai">
                                                    @csrf
                                                    <input type="hidden" name="decision" value="reject">
                                                    <button type="submit" title="Belum Sesuai / Tolak" class="flex size-8 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-rose-600 transition hover:bg-rose-100 dark:border-rose-400/25 dark:bg-rose-500/10 dark:text-rose-400 dark:hover:bg-rose-500/20">
                                                        <x-icon name="close" class="size-4" />
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <tr x-show="interview === {{ $application->id }}" x-cloak class="border-t border-line/60 bg-sky-50/60 dark:bg-sky-400/5">
                                <td colspan="7" class="px-5 py-5 sm:px-6">
                                    <form method="POST" action="{{ route('applicants.interview', $application) }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:items-end">
                                        @csrf
                                        @method('PATCH')

                                        <div>
                                            <x-ui.label :for="'interview_at-' . $application->id">Jadwal interview</x-ui.label>
                                            <x-ui.input
                                                type="datetime-local"
                                                name="interview_at"
                                                :id="'interview_at-' . $application->id"
                                                :value="$application->interview_at?->format('Y-m-d\TH:i')"
                                            />
                                            <x-ui.field-error name="interview_at" />
                                        </div>

                                        <div>
                                            <x-ui.label :for="'interview_mode-' . $application->id">Metode</x-ui.label>
                                            <x-ui.select
                                                name="interview_mode"
                                                :id="'interview_mode-' . $application->id"
                                                :options="$interviewModes"
                                                :selected="$application->interview_mode ?? 'onsite'"
                                            />
                                            <x-ui.field-error name="interview_mode" />
                                        </div>

                                        <div>
                                            <x-ui.label :for="'interview_location-' . $application->id">Tempat / Venue</x-ui.label>
                                            <x-ui.input
                                                name="interview_location"
                                                :id="'interview_location-' . $application->id"
                                                :value="$application->interview_location"
                                                placeholder="Misal: Kantor Majesty Jl. Yos Sudarso / Google Meet"
                                            />
                                            <x-ui.field-error name="interview_location" />
                                        </div>

                                        <div class="sm:col-span-2">
                                            <x-ui.label :for="'interview_address-' . $application->id">Alamat Lengkap</x-ui.label>
                                            <x-ui.input
                                                name="interview_address"
                                                :id="'interview_address-' . $application->id"
                                                :value="$application->interview_address"
                                                placeholder="Alamat lengkap lokasi interview (opsional)"
                                            />
                                            <x-ui.field-error name="interview_address" />
                                        </div>

                                        <div>
                                            <x-ui.label :for="'interview_maps_url-' . $application->id">Tautan Google Maps</x-ui.label>
                                            <x-ui.input
                                                name="interview_maps_url"
                                                :id="'interview_maps_url-' . $application->id"
                                                :value="$application->interview_maps_url"
                                                placeholder="https://maps.app.goo.gl/... (opsional)"
                                            />
                                            <x-ui.field-error name="interview_maps_url" />
                                        </div>

                                        <div class="sm:col-span-2 lg:col-span-3">
                                            <x-ui.label :for="'interview_notes-' . $application->id">Catatan untuk kandidat (opsional)</x-ui.label>
                                            <x-ui.textarea name="interview_notes" :id="'interview_notes-' . $application->id" :value="$application->interview_notes" :rows="2" placeholder="Misal: hadir 10 menit sebelum jadwal interview." />
                                            <x-ui.field-error name="interview_notes" />
                                        </div>

                                        <div class="flex flex-wrap items-center gap-2 sm:col-span-2 lg:col-span-3">
                                            <x-ui.button type="submit" size="sm" icon="mail">{{ $application->interview_sent_at ? 'Simpan & kirim ulang undangan' : 'Simpan & kirim undangan' }}</x-ui.button>
                                            <x-ui.button variant="ghost" size="sm" x-on:click="interview = null">Batal</x-ui.button>
                                            @if ($application->interview_sent_at)
                                                <span class="text-[11.5px] text-subtle">Undangan terakhir dikirim {{ $application->interview_sent_at->translatedFormat('d M Y H:i') }}.</span>
                                            @endif
                                        </div>
                                    </form>
                                </td>
                            </tr>

                            <tr x-show="testInvite === {{ $application->id }}" x-cloak class="border-t border-line/60 bg-purple-50/50 dark:bg-purple-950/20">
                                <td colspan="7" class="px-5 py-4 sm:px-6">
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between border-b border-purple-200/60 pb-2.5 dark:border-purple-800/40">
                                            <div>
                                                <h4 class="text-[13.5px] font-bold text-ink">Undang Tes Online: {{ $application->candidate->name }}</h4>
                                                <p class="text-[11.5px] text-muted">Pilih paket tes psikometrik atau kognitif yang ingin diberikan kepada kandidat ini.</p>
                                            </div>
                                            <button type="button" class="text-subtle hover:text-ink" x-on:click="testInvite = null">
                                                <x-icon name="close" class="size-4" />
                                            </button>
                                        </div>

                                        @if ($application->assessmentInvitations->isNotEmpty())
                                            <div>
                                                <p class="text-[11px] font-bold uppercase tracking-wider text-purple-700 dark:text-purple-300 mb-1.5">Tes yang Sudah Diundang:</p>
                                                <div class="space-y-1.5">
                                                    @foreach ($application->assessmentInvitations as $inv)
                                                        <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-line bg-surface p-2.5 text-[12px]">
                                                            <div class="flex items-center gap-2">
                                                                <span class="size-2 rounded-full bg-purple-600"></span>
                                                                <span class="font-semibold text-ink">{{ $inv->assessment->title }}</span>
                                                                <x-ui.badge :variant="$inv->statusTone()" size="sm">{{ $inv->statusLabel() }}</x-ui.badge>
                                                                <span class="text-[11px] text-subtle">Batas: {{ $inv->expires_at->translatedFormat('d M Y H:i') }}</span>
                                                            </div>
                                                            <div class="flex items-center gap-1.5">
                                                                <button
                                                                    type="button"
                                                                    class="rounded-lg border border-line bg-surface-2 px-2 py-1 text-[11px] font-medium text-ink transition hover:bg-surface"
                                                                    x-on:click="navigator.clipboard.writeText('{{ $inv->link() }}'); copiedId = {{ $inv->id }}; setTimeout(() => copiedId = null, 1800)"
                                                                >
                                                                    <span x-text="copiedId === {{ $inv->id }} ? 'Tersalin!' : 'Salin Link'"></span>
                                                                </button>
                                                                @php $invWa = \App\Services\WhatsAppLink::assessmentInvitation($inv); @endphp
                                                                @if ($invWa)
                                                                    <a href="{{ $invWa }}" target="_blank" class="rounded-lg border border-emerald-300 bg-emerald-50 px-2 py-1 text-[11px] font-medium text-emerald-700 transition hover:bg-emerald-100">
                                                                        Kirim WA
                                                                    </a>
                                                                @endif
                                                                @if ($inv->attempt)
                                                                    <a href="{{ route('assessments.attempt', $inv->attempt) }}" class="rounded-lg border border-line bg-surface-2 px-2 py-1 text-[11px] font-medium text-ink hover:text-brand">
                                                                        Lihat Hasil ({{ $inv->attempt->formattedScore() }})
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <form method="POST" action="{{ route('applicants.invite-assessment', $application) }}" class="rounded-xl border border-line bg-surface p-3.5 space-y-3">
                                            @csrf
                                            <p class="text-[11.5px] font-semibold text-ink">Buat Undangan Tes Baru:</p>
                                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                                <div>
                                                    <x-ui.label :for="'assessment_id-' . $application->id">Pilih Tes</x-ui.label>
                                                    <select
                                                        name="assessment_id"
                                                        id="assessment_id-{{ $application->id }}"
                                                        class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px] text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/15"
                                                        required
                                                    >
                                                        <option value="">Pilih paket tes…</option>
                                                        @foreach ($assessments as $test)
                                                            @php $alreadyInvited = $application->assessmentInvitations->contains('assessment_id', $test->id); @endphp
                                                            <option value="{{ $test->id }}" @disabled($alreadyInvited)>
                                                                {{ $test->title }} ({{ $test->duration_minutes }} mnt) {{ $alreadyInvited ? '— [Sudah diundang]' : '' }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div>
                                                    <x-ui.label :for="'expires_at-' . $application->id">Masa Berlaku Link (Deadline Akses)</x-ui.label>
                                                    <x-ui.input
                                                        type="datetime-local"
                                                        name="expires_at"
                                                        :id="'expires_at-' . $application->id"
                                                        :value="now()->addDays(7)->format('Y-m-d\TH:i')"
                                                        required
                                                    />
                                                    <p class="mt-1 text-[11px] text-subtle">Tenggat tanggal & jam terakhir kandidat dapat membuka dan memulai tes.</p>
                                                </div>

                                                <div class="flex items-end">
                                                    <x-ui.button type="submit" size="sm" icon="send">Buat Undangan Tes</x-ui.button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-ui.empty-state icon="users" title="Belum ada pelamar" description="Bagikan link lowongan publik agar lamaran mulai masuk." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @foreach ($applications as $application)
                @php
                    $screening = $application->screening_data ?? [];
                    $score = $application->screening_score;
                    $skills = $screening['skills'] ?? [];
                    $matchedSkillsList = $skills['matched'] ?? [];
                    $missingSkillsList = $skills['missing'] ?? [];
                    $requiredSkillsList = $skills['required'] ?? [];

                    $splitSkills = function ($items) {
                        $result = [];
                        foreach ($items as $item) {
                            if (is_string($item) && str_contains($item, ',')) {
                                $parts = array_values(array_filter(array_map('trim', explode(',', $item)), fn ($p) => $p !== ''));
                                if (count($parts) > 1) {
                                    foreach ($parts as $p) {
                                        $result[] = $p;
                                    }
                                    continue;
                                }
                            }
                            $result[] = $item;
                        }
                        return $result;
                    };

                    $flatMatched = $splitSkills($matchedSkillsList);
                    $flatMissing = $splitSkills($missingSkillsList);
                    $totalMatched = count($flatMatched);
                    $totalRequired = count($splitSkills($requiredSkillsList));
                    if ($totalRequired < ($totalMatched + count($flatMissing))) {
                        $totalRequired = $totalMatched + count($flatMissing);
                    }

                    $education = $screening['education'] ?? [];
                    $experience = $screening['experience'] ?? [];
                    $educationPassed = $education['passed'] ?? null;
                    $experiencePassed = $experience['passed'] ?? null;
                    $jobThreshold = $application->jobOpening->screening_threshold ?? 70;

                    $textSource = $screening['text_source'] ?? null;
                    $isScanned = ($screening['is_scanned_image'] ?? false) || $textSource === 'ocr';
                    $sourceLabels = [
                        'pdfparser' => 'PDF Parser (Teks Digital Murni)',
                        'pdftotext' => 'pdftotext (Teks Digital Murni)',
                        'ocr' => 'Optical Character Recognition / OCR (Hasil Scan/Foto)',
                        'phpword' => 'Word Parser (Teks Digital Murni)',
                        'docx-xml' => 'DOCX XML (Teks Digital Murni)',
                        'antiword' => 'Antiword',
                    ];
                @endphp

                <template x-teleport="body">
                    <div
                        x-show="screeningModal === {{ $application->id }}"
                        x-cloak
                        @keydown.escape.window="if (screeningModal === {{ $application->id }}) screeningModal = null"
                        class="fixed inset-0 z-[100] flex items-start justify-center overflow-y-auto p-4 sm:p-6 pt-20 sm:pt-24 pb-12"
                        role="dialog"
                        aria-modal="true"
                        @click.self="screeningModal = null"
                    >
                        <div
                            class="fixed inset-0 bg-ink/60 backdrop-blur-sm transition-opacity"
                            @click="screeningModal = null"
                            x-show="screeningModal === {{ $application->id }}"
                            x-transition:enter="ease-out duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="ease-in duration-150"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                        ></div>

                        <div
                            class="relative flex flex-col w-full max-w-3xl max-h-[calc(100vh-8.5rem)] rounded-3xl border border-line bg-surface shadow-2xl transition-all overflow-hidden"
                            x-show="screeningModal === {{ $application->id }}"
                            x-transition:enter="ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave="ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                        >
                        <div class="flex items-start justify-between gap-4 border-b border-line bg-surface-2/40 px-6 py-4.5">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <x-ui.avatar :name="$application->candidate->name" size="md" />
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-[16px] font-bold text-ink truncate">{{ $application->candidate->name }}</h3>
                                        <x-ui.badge :variant="$application->statusTone()">{{ $application->statusLabel() }}</x-ui.badge>
                                    </div>
                                    <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-[12px] text-muted">
                                        <span class="inline-flex items-center gap-1 font-medium text-brand">
                                            <x-icon name="briefcase" class="size-3.5" />
                                            {{ $application->jobOpening->title }}
                                        </span>
                                        <span>•</span>
                                        <span class="inline-flex items-center gap-1">
                                            <x-icon name="mail" class="size-3.5 text-subtle" />
                                            {{ $application->candidate->email }}
                                        </span>
                                        <span>•</span>
                                        <span class="inline-flex items-center gap-1">
                                            <x-icon name="phone" class="size-3.5 text-subtle" />
                                            {{ $application->candidate->phone }}
                                        </span>
                                        @if ($application->candidate->city)
                                            <span>•</span>
                                            <span class="inline-flex items-center gap-1">
                                                <x-icon name="map-pin" class="size-3.5 text-subtle" />
                                                {{ $application->candidate->city }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <button
                                type="button"
                                @click="screeningModal = null"
                                class="shrink-0 flex size-8 items-center justify-center rounded-xl border border-line bg-surface text-subtle hover:bg-surface-2 hover:text-ink transition"
                                title="Tutup (Esc)"
                            >
                                <x-icon name="close" class="size-4" />
                            </button>
                        </div>

                        <div class="overflow-y-auto p-6 space-y-5">
                            @if ($score === null)
                                <div class="rounded-2xl border border-amber-200/80 bg-amber-500/10 p-4.5 dark:border-amber-500/30">
                                    <div class="flex items-start gap-3.5">
                                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/20 text-amber-700 dark:text-amber-300">
                                            <x-icon name="alert-circle" class="size-5" />
                                        </span>
                                        <div>
                                            <h4 class="text-[14.5px] font-bold text-amber-950 dark:text-amber-100">Penilaian Manual Diperlukan</h4>
                                            <p class="mt-0.5 text-[12.5px] leading-relaxed text-amber-900/80 dark:text-amber-200/80">
                                                {{ $screening['reason'] ?? 'CV ini belum dianalisis otomatis atau format berkas memerlukan verifikasi manual oleh HR.' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                @php
                                    $isPassing = $score >= $jobThreshold;
                                    $isWarning = ! $isPassing && $score >= ($jobThreshold * 0.6);
                                @endphp
                                <div class="rounded-2xl border p-4.5 {{ $isPassing ? 'border-emerald-200/80 bg-emerald-500/10 dark:border-emerald-500/30' : ($isWarning ? 'border-amber-200/80 bg-amber-500/10 dark:border-amber-500/30' : 'border-rose-200/80 bg-rose-500/10 dark:border-rose-500/30') }}">
                                    <div class="flex flex-wrap items-center justify-between gap-4">
                                        <div class="flex items-center gap-3.5">
                                            <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl {{ $isPassing ? 'bg-emerald-600 text-white' : ($isWarning ? 'bg-amber-600 text-white' : 'bg-rose-600 text-white') }} shadow-sm">
                                                <span class="text-[18px] font-black leading-none">{{ $score }}</span>
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <h4 class="text-[15px] font-bold {{ $isPassing ? 'text-emerald-950 dark:text-emerald-100' : ($isWarning ? 'text-amber-950 dark:text-amber-100' : 'text-rose-950 dark:text-rose-100') }}">
                                                        {{ $isPassing ? 'Memenuhi Standar Kualifikasi' : ($isWarning ? 'Mendekati Standar Kualifikasi' : 'Belum Memenuhi Standar Screening') }}
                                                    </h4>
                                                    <span class="rounded-md px-2 py-0.5 text-[11px] font-bold {{ $isPassing ? 'bg-emerald-200/80 text-emerald-900 dark:bg-emerald-500/20 dark:text-emerald-300' : ($isWarning ? 'bg-amber-200/80 text-amber-900 dark:bg-amber-500/20 dark:text-amber-300' : 'bg-rose-200/80 text-rose-900 dark:bg-rose-500/20 dark:text-rose-300') }}">
                                                        Skor: {{ $score }}/100
                                                    </span>
                                                </div>
                                                <p class="mt-0.5 text-[12.5px] {{ $isPassing ? 'text-emerald-900/80 dark:text-emerald-200/80' : ($isWarning ? 'text-amber-900/80 dark:text-amber-200/80' : 'text-rose-900/80 dark:text-rose-200/80') }}">
                                                    Ambang batas minimum kelolosan lowongan ini adalah <strong>{{ $jobThreshold }}/100</strong>.
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-[11.5px] text-subtle">Dianalisis pada:</span>
                                            <p class="text-[12px] font-semibold text-ink">{{ $application->screened_at ? $application->screened_at->translatedFormat('d M Y, H:i') : $application->created_at->translatedFormat('d M Y, H:i') }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="rounded-2xl border border-line bg-surface p-4.5 space-y-3.5">
                                <div class="flex items-center justify-between border-b border-line/70 pb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="flex size-7 items-center justify-center rounded-lg bg-brand/10 text-brand">
                                            <x-icon name="sparkles" class="size-4" />
                                        </span>
                                        <h4 class="text-[14px] font-bold text-ink">Analisis Keahlian (Skills)</h4>
                                    </div>
                                    <span class="rounded-lg bg-surface-2 px-2.5 py-1 text-[11.5px] font-semibold text-muted">
                                        {{ $totalMatched }} dari {{ $totalRequired }} Keahlian Cocok
                                    </span>
                                </div>

                                <div class="space-y-3">
                                    <div>
                                        <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 mb-2 flex items-center gap-1.5">
                                            <x-icon name="check-circle" class="size-3.5" />
                                            Keahlian yang Ditemukan di CV ({{ count($flatMatched) }}):
                                        </p>
                                        @if (count($flatMatched) > 0)
                                            <div class="flex flex-wrap gap-2">
                                                @foreach ($flatMatched as $skill)
                                                    <div class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-300/80 bg-emerald-50 px-3 py-1.5 text-[12px] font-medium text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200 break-words whitespace-normal max-w-full text-left">
                                                        <x-icon name="check" class="size-3.5 shrink-0 text-emerald-600 dark:text-emerald-400" />
                                                        <span>{{ $skill }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="rounded-xl border border-dashed border-line bg-surface-2/40 px-3.5 py-2.5 text-[12px] text-subtle">
                                                Tidak ada keahlian yang secara langsung terdeteksi cocok dengan kata kunci kualifikasi lowongan.
                                            </div>
                                        @endif
                                    </div>

                                    @if (count($flatMissing) > 0)
                                        <div class="pt-2 border-t border-line/60">
                                            <p class="text-[11px] font-bold uppercase tracking-wider text-rose-700 dark:text-rose-400 mb-2 flex items-center gap-1.5">
                                                <x-icon name="alert-circle" class="size-3.5" />
                                                Keahlian Belum Terdeteksi di CV ({{ count($flatMissing) }}):
                                            </p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach ($flatMissing as $skill)
                                                    <div class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-3 py-1.5 text-[12px] font-medium text-rose-900 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200 break-words whitespace-normal max-w-full text-left">
                                                        <x-icon name="close" class="size-3.5 shrink-0 text-rose-500" />
                                                        <span>{{ $skill }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-2xl border border-line bg-surface p-4.5 space-y-3">
                                    <div class="flex items-center justify-between border-b border-line/70 pb-2.5">
                                        <div class="flex items-center gap-2">
                                            <span class="flex size-7 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600 dark:text-sky-400">
                                                <x-icon name="file-text" class="size-4" />
                                            </span>
                                            <h4 class="text-[13.5px] font-bold text-ink">Kualifikasi Pendidikan</h4>
                                        </div>
                                        @if ($educationPassed !== null)
                                            <span class="rounded-lg px-2 py-0.5 text-[11px] font-bold {{ $educationPassed ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-500/20 dark:text-rose-300' }}">
                                                {{ $educationPassed ? 'Memenuhi' : 'Belum Memenuhi' }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="space-y-2 text-[12.5px]">
                                        <div class="flex items-center justify-between gap-2 rounded-xl bg-surface-2/60 px-3 py-2">
                                            <span class="text-muted">Syarat Minimal Lowongan</span>
                                            <span class="font-bold text-ink">{{ $education['required'] ?? '—' }}</span>
                                        </div>
                                        <div class="flex items-center justify-between gap-2 rounded-xl bg-surface-2/60 px-3 py-2">
                                            <span class="text-muted">Terdeteksi pada CV</span>
                                            <span class="font-bold text-ink">{{ $education['detected'] ?? 'Tidak terdeteksi' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-line bg-surface p-4.5 space-y-3">
                                    <div class="flex items-center justify-between border-b border-line/70 pb-2.5">
                                        <div class="flex items-center gap-2">
                                            <span class="flex size-7 items-center justify-center rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400">
                                                <x-icon name="clock" class="size-4" />
                                            </span>
                                            <h4 class="text-[13.5px] font-bold text-ink">Pengalaman Kerja</h4>
                                        </div>
                                        @if ($experiencePassed !== null)
                                            <span class="rounded-lg px-2 py-0.5 text-[11px] font-bold {{ $experiencePassed ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-500/20 dark:text-rose-300' }}">
                                                {{ $experiencePassed ? 'Memenuhi' : 'Belum Memenuhi' }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="space-y-2 text-[12.5px]">
                                        <div class="flex items-center justify-between gap-2 rounded-xl bg-surface-2/60 px-3 py-2">
                                            <span class="text-muted">Syarat Minimal Pengalaman</span>
                                            <span class="font-bold text-ink">{{ $experience['required'] ?? 0 }} tahun</span>
                                        </div>
                                        <div class="flex items-center justify-between gap-2 rounded-xl bg-surface-2/60 px-3 py-2">
                                            <span class="text-muted">Terdeteksi pada CV</span>
                                            <span class="font-bold text-ink">{{ $experience['detected'] !== null ? $experience['detected'] . ' tahun' : 'Tidak terdeteksi' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-line bg-surface-2/40 p-4 space-y-2.5">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 text-[12px] font-semibold text-ink">
                                        <x-icon name="file-text" class="size-4 text-brand" />
                                        <span>Integritas Dokumen CV:</span>
                                        @if ($isScanned)
                                            <span class="inline-flex items-center gap-1 rounded-full border border-amber-300 bg-amber-100/80 px-2 py-0.5 text-[11px] font-bold text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/20 dark:text-amber-300">
                                                <x-icon name="image" class="size-3" /> Berkas Hasil Scan / Gambar
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full border border-emerald-300 bg-emerald-100/80 px-2 py-0.5 text-[11px] font-bold text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-500/20 dark:text-emerald-300">
                                                <x-icon name="check" class="size-3" /> Teks Digital Murni
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-[11.5px] text-muted">
                                        {{ number_format($screening['text_chars'] ?? 0, 0, ',', '.') }} karakter teks diekstrak
                                    </span>
                                </div>
                                <p class="text-[12px] leading-relaxed text-subtle">
                                    @if ($isScanned)
                                        Berkas ini terdeteksi sebagai scan gambar atau foto. Teks dianalisis menggunakan teknologi OCR ({{ $sourceLabels[$textSource] ?? 'OCR' }}).
                                    @else
                                        Dokumen pelamar tersusun dari teks digital asli yang diekstrak langsung via {{ $sourceLabels[$textSource] ?? 'pembaca teks digital' }}. Tingkat akurasi ekstraksi optimal.
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line bg-surface-2/40 px-6 py-4">
                            <a
                                href="{{ route('applicants.cv', $application) }}"
                                target="_blank"
                                class="inline-flex items-center gap-2 rounded-xl border border-line bg-surface px-3.5 py-2 text-[12.5px] font-semibold text-ink shadow-sm transition hover:bg-surface-2 hover:border-brand/40"
                            >
                                <x-icon name="download" class="size-4 text-subtle" />
                                Buka / Unduh Berkas CV
                            </a>

                            <div class="flex items-center gap-2">
                                @if (in_array($application->status, ['new', 'review', 'screening_failed'], true))
                                    <form method="POST" action="{{ route('applicants.status', $application) }}" data-confirm="Kandidat akan diloloskan screening CV dan siap ke tahap formulir / interview." data-confirm-title="Loloskan screening CV?" data-confirm-label="Ya, loloskan">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="decision" value="pass">
                                        <x-ui.button type="submit" variant="primary" size="sm" icon="check" class="bg-emerald-600 hover:bg-emerald-700">
                                            Loloskan Screening
                                        </x-ui.button>
                                    </form>

                                    <form method="POST" action="{{ route('applicants.status', $application) }}" data-confirm="Lamaran kandidat akan ditolak." data-confirm-title="Tolak lamaran?" data-confirm-tone="danger" data-confirm-label="Ya, tolak">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="decision" value="reject">
                                        <x-ui.button type="submit" variant="danger" size="sm" icon="close">
                                            Tolak
                                        </x-ui.button>
                                    </form>
                                @endif

                                <button
                                    type="button"
                                    @click="screeningModal = null"
                                    class="inline-flex items-center rounded-xl border border-line bg-surface px-4 py-2 text-[12.5px] font-semibold text-muted transition hover:bg-surface-2 hover:text-ink"
                                >
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        @endforeach

            @if ($applications->hasPages())
                <div class="border-t border-line px-5 py-4 sm:px-6">
                    {{ $applications->links() }}
                </div>
            @endif
        </x-ui.card>
    </div>
</x-layouts.app>
