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

        <x-ui.card :padded="false">
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
                    <tbody x-data="{ open: null, interview: null, testInvite: null, copiedId: null }">
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
                                        <x-ui.badge variant="neutral" title="{{ $screening['reason'] ?? 'Tinjau manual.' }}">Manual</x-ui.badge>
                                    @else
                                        <div class="flex flex-col items-start gap-1">
                                            <x-ui.badge :variant="$score >= $jobThreshold ? 'success' : ($score >= ($jobThreshold * 0.6) ? 'warning' : 'danger')">{{ $score }}/100</x-ui.badge>
                                            @if ($summary !== '')
                                                <span class="whitespace-nowrap text-[11px] text-subtle">{{ $summary }}</span>
                                            @endif
                                        </div>
                                    @endif
                                    <button
                                        type="button"
                                        class="mt-1 inline-flex items-center gap-1 text-[11.5px] font-semibold text-muted transition hover:text-ink"
                                        x-on:click="open = open === {{ $application->id }} ? null : {{ $application->id }}"
                                    >
                                        <x-icon name="chevron-down" class="size-3.5" />
                                        Detail
                                    </button>
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

                            <tr x-show="open === {{ $application->id }}" x-cloak class="border-t border-line/60 bg-surface-2/50">
                                <td colspan="7" class="px-5 py-4 sm:px-6">
                                    @if ($score === null)
                                        <div class="flex items-start gap-2.5">
                                            <x-icon name="alert-circle" class="mt-0.5 size-4 shrink-0 text-subtle" />
                                            <p class="text-[12.5px] leading-relaxed text-muted">
                                                {{ $screening['reason'] ?? 'CV ini belum dianalisis otomatis.' }}
                                                Baca CV lewat tombol CV untuk menilai manual.
                                            </p>
                                        </div>
                                    @else
                                        @php
                                            $education = $screening['education'] ?? [];
                                            $experience = $screening['experience'] ?? [];
                                        @endphp

                                        <div class="grid gap-5 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)_minmax(0,1fr)]">
                                            <div>
                                                <p class="flex items-center gap-1.5 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-subtle">
                                                    <x-icon name="sparkles" class="size-3.5" />
                                                    Keahlian · {{ $matchedSkills }}/{{ $requiredSkills }} cocok
                                                </p>
                                                <div class="mt-2 flex flex-wrap gap-1.5">
                                                    @forelse ($skills['matched'] ?? [] as $skill)
                                                        <x-ui.badge variant="success">{{ $skill }}</x-ui.badge>
                                                    @empty
                                                        <span class="text-[12px] text-subtle">Tidak ada keahlian yang cocok.</span>
                                                    @endforelse
                                                    @foreach ($skills['missing'] ?? [] as $skill)
                                                        <x-ui.badge variant="danger">{{ $skill }}</x-ui.badge>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <div>
                                                <p class="flex items-center gap-1.5 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-subtle">
                                                    <x-icon name="file-text" class="size-3.5" />
                                                    Pendidikan
                                                </p>
                                                <div class="mt-2 space-y-1.5 text-[12.5px]">
                                                    <div class="flex items-center justify-between gap-3">
                                                        <span class="text-muted">Minimal</span>
                                                        <span class="font-semibold">{{ $education['required'] ?? '—' }}</span>
                                                    </div>
                                                    <div class="flex items-center justify-between gap-3">
                                                        <span class="text-muted">Terdeteksi</span>
                                                        <span class="font-semibold">{{ $education['detected'] ?? 'Tidak terdeteksi' }}</span>
                                                    </div>
                                                    @if (($education['passed'] ?? null) !== null)
                                                        <x-ui.badge :variant="$education['passed'] ? 'success' : 'danger'">{{ $education['passed'] ? 'Memenuhi' : 'Belum memenuhi' }}</x-ui.badge>
                                                    @endif
                                                </div>
                                            </div>

                                            <div>
                                                <p class="flex items-center gap-1.5 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-subtle">
                                                    <x-icon name="clock" class="size-3.5" />
                                                    Pengalaman
                                                </p>
                                                <div class="mt-2 space-y-1.5 text-[12.5px]">
                                                    <div class="flex items-center justify-between gap-3">
                                                        <span class="text-muted">Minimal</span>
                                                        <span class="font-semibold">{{ $experience['required'] ?? 0 }} tahun</span>
                                                    </div>
                                                    <div class="flex items-center justify-between gap-3">
                                                        <span class="text-muted">Terdeteksi</span>
                                                        <span class="font-semibold">{{ $experience['detected'] !== null ? $experience['detected'] . ' tahun' : 'Tidak terdeteksi' }}</span>
                                                    </div>
                                                    @if (($experience['passed'] ?? null) !== null)
                                                        <x-ui.badge :variant="$experience['passed'] ? 'success' : 'danger'">{{ $experience['passed'] ? 'Memenuhi' : 'Belum memenuhi' }}</x-ui.badge>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        @php
                                            $textSource = $screening['text_source'] ?? null;
                                            $isScanned = ($screening['is_scanned_image'] ?? false) || $textSource === 'ocr';
                                            $sourceLabels = [
                                                'pdfparser' => 'PDF parser (Teks Asli)',
                                                'pdftotext' => 'pdftotext (Teks Asli)',
                                                'ocr' => 'OCR (CV hasil scan gambar)',
                                                'phpword' => 'Word parser (Teks Asli)',
                                                'docx-xml' => 'DOCX XML (Teks Asli)',
                                                'antiword' => 'Antiword',
                                            ];
                                        @endphp

                                        <div class="mt-3.5 flex flex-wrap items-center gap-2 border-t border-line/60 pt-3 text-[11.5px]">
                                            @if ($isScanned)
                                                <x-ui.badge variant="warning">
                                                    <x-icon name="image" class="mr-1 size-3" />
                                                    Hasil Gambar / Scan
                                                </x-ui.badge>
                                                <span class="text-amber-700 dark:text-amber-400">Berkas ini berupa scan/gambar. Teks dibaca via {{ $sourceLabels[$textSource] ?? 'OCR' }}.</span>
                                            @else
                                                <x-ui.badge variant="success">
                                                    <x-icon name="file-text" class="mr-1 size-3" />
                                                    Teks Digital (Tulisan Asli)
                                                </x-ui.badge>
                                                <span class="text-subtle">
                                                    Dokumen digital murni dibaca lewat {{ $sourceLabels[$textSource] ?? 'pembaca teks' }}
                                                    @if (($screening['text_chars'] ?? 0) > 0)
                                                        · {{ number_format($screening['text_chars'], 0, ',', '.') }} karakter
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                    @endif
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

            @if ($applications->hasPages())
                <div class="border-t border-line px-5 py-4 sm:px-6">
                    {{ $applications->links() }}
                </div>
            @endif
        </x-ui.card>
    </div>
</x-layouts.app>
