<x-layouts.public :title="'Formulir Biodata Karyawan · ' . ($company->name ?? 'Onboarding')">
    <x-slot:hero>
        <section class="relative isolate overflow-hidden bg-[#06231b] text-white">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,rgba(217,180,106,0.18),transparent_55%)]"></div>
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_right,rgba(16,185,129,0.12),transparent_55%)]"></div>
            <div class="pattern-grid pointer-events-none absolute inset-0 opacity-20"></div>

            <div class="relative mx-auto w-full max-w-4xl px-4 pb-10 pt-8 sm:px-6 sm:pb-12 sm:pt-10">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <span class="inline-flex items-center gap-2 rounded-full border border-gold/40 bg-gold/10 px-3.5 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-gold">
                        ONBOARDING · DATA KARYAWAN BARU
                    </span>
                    <span class="text-[12px] text-white/60">
                        {{ $company->name ?? config('app.name') }}
                    </span>
                </div>

                <h1 class="mt-4 font-display text-[26px] font-semibold leading-tight text-white sm:text-[36px]">
                    Formulir Biodata Karyawan
                </h1>
                <p class="mt-1 font-display text-[15px] text-gold/90 sm:text-[17px]">
                    PT. {{ $company->name ?? '________________________' }}
                </p>

                <p class="mt-4 max-w-2xl text-[12.5px] leading-relaxed text-white/75 sm:text-[13px]">
                    Selamat bergabung! Formulir biodata ini diperlukan untuk melengkapi berkas administrasi kepegawaian, pendaftaran BPJS, dan pembukaan akun penggajian Anda.
                    <span class="block mt-1 text-emerald-300 font-medium text-[12px]">
                        ✨ Data yang telah Anda masukkan pada Formulir Lamaran Kerja sebelumnya telah otomatis terisi di bawah. Anda hanya perlu melengkapi data yang belum terisi.
                    </span>
                </p>

                <div class="mt-6 flex flex-wrap items-center gap-4 border-t border-white/10 pt-4 text-[12px] text-white/70">
                    <div>
                        <span class="text-white/40">Karyawan:</span>
                        <strong class="ml-1 text-white">{{ $personal['full_name'] ?? $application->candidate?->name }}</strong>
                    </div>
                    <div>·</div>
                    <div>
                        <span class="text-white/40">Posisi:</span>
                        <strong class="ml-1 text-white">{{ $application->jobOpening?->title }}</strong>
                    </div>
                </div>
            </div>
        </section>
    </x-slot:hero>

    <div class="mx-auto w-full max-w-4xl px-4 py-8 sm:px-6 sm:py-10">
        @if (session('status'))
            <div class="mb-6 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 p-5 text-emerald-900 dark:text-emerald-200">
                <div class="flex items-center gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500 text-white shadow-sm">
                        <x-icon name="check" class="size-6" />
                    </span>
                    <div>
                        <h3 class="text-[15px] font-bold">Biodata Karyawan Berhasil Dikirim!</h3>
                        <p class="text-[13px] text-emerald-800/80 dark:text-emerald-300/80">{{ session('status') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-rose-500/20 bg-rose-500/10 p-5 text-rose-900 dark:text-rose-200">
                <div class="flex items-start gap-3">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-rose-500 text-white">
                        <x-icon name="alert-triangle" class="size-5" />
                    </span>
                    <div>
                        <h3 class="text-[14px] font-bold">Mohon periksa kolom yang belum lengkap:</h3>
                        <ul class="mt-2 list-disc space-y-1 pl-4 text-[12.5px]">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        @if ($isSubmitted)
            <div class="rounded-3xl border border-line bg-surface p-8 text-center shadow-sm">
                <div class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400">
                    <x-icon name="check-circle" class="size-8" />
                </div>
                <h2 class="mt-4 text-[20px] font-bold text-ink">Biodata Karyawan Telah Berhasil Dikirim</h2>
                <p class="mx-auto mt-2 max-w-md text-[13.5px] text-muted">
                    Terima kasih telah melengkapi data biodata kepegawaian. Berkas Anda telah diterima oleh HRD untuk proses pembuatan kontrak kerja dan pendaftaran kepegawaian.
                </p>
                <div class="mt-6 inline-flex items-center gap-2 rounded-xl border border-line bg-surface-2 px-4 py-2 text-[12.5px] font-medium text-subtle">
                    <x-icon name="clock" class="size-4" />
                    Diserahkan pada {{ $biodata->submitted_at?->translatedFormat('d F Y · H:i') ?? '—' }} WIB
                </div>

                @if ($biodata->digital_signature)
                    <div class="mt-6 block">
                        <div class="inline-block rounded-2xl border border-line bg-surface-2/60 p-4 text-center">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                <x-icon name="check-circle" class="size-3.5" /> Tanda Tangan Digital Terverifikasi
                            </span>
                            <div class="mt-3 flex justify-center">
                                <div class="rounded-xl border border-line bg-white p-2">
                                    <img src="{{ $biodata->digital_signature }}" alt="Tanda Tangan" class="h-16 max-w-[200px] object-contain">
                                </div>
                            </div>
                            <p class="mt-2 text-[12.5px] font-semibold text-ink">{{ $biodata->signed_name }}</p>
                            <p class="text-[11px] text-subtle">{{ $biodata->signed_city }}, {{ $biodata->signed_date?->format('d/m/Y') }}</p>
                        </div>
                    </div>
                @endif
            </div>
        @else
            @php
                $initialStep = 1;
                if ($errors->any()) {
                    if ($errors->has('signed_*') || $errors->has('digital_signature')) {
                        $initialStep = 8;
                    } elseif ($errors->has('organization_history.*')) {
                        $initialStep = 7;
                    } elseif ($errors->has('family_details.*')) {
                        $initialStep = 6;
                    } elseif ($errors->has('residences_history.*')) {
                        $initialStep = 5;
                    } elseif ($errors->has('work_experiences.*')) {
                        $initialStep = 4;
                    } elseif ($errors->has('education_formal.*') || $errors->has('education_informal.*')) {
                        $initialStep = 3;
                    } elseif ($errors->has('bank_accounts.*')) {
                        $initialStep = 2;
                    } else {
                        $initialStep = 1;
                    }
                }
            @endphp

            <form
                method="POST"
                action="{{ route('employee-biodatas.store', $biodata->token) }}"
                enctype="multipart/form-data"
                novalidate
                class="space-y-6"
                x-init="initSignature()"
                @submit="validateBeforeSubmit($event)"
                @keydown.enter="handleEnter($event)"
                x-data="{
                    step: @js($initialStep),
                    maxStepVisited: @js($initialStep),
                    steps: [
                        { num: 1, title: 'Identitas', subtitle: '1. Identitas Pribadi & Lampiran' },
                        { num: 2, title: 'Rekening', subtitle: '2. Informasi Rekening Bank' },
                        { num: 3, title: 'Pendidikan', subtitle: '3. Riwayat Pendidikan Formal & Informal' },
                        { num: 4, title: 'Pengalaman', subtitle: '4. Riwayat Pengalaman Kerja' },
                        { num: 5, title: 'Tempat Tinggal', subtitle: '5. Riwayat Tempat Tinggal' },
                        { num: 6, title: 'Keluarga', subtitle: '6. Susunan Keluarga & Sahabat Karib' },
                        { num: 7, title: 'Organisasi', subtitle: '7. Pengalaman Berorganisasi' },
                        { num: 8, title: 'Tanda Tangan', subtitle: '8. Pernyataan & Tanda Tangan Digital' },
                    ],
                    nextStep() {
                        const currentContainer = this.$refs['step' + this.step];
                        if (currentContainer) {
                            const inputs = Array.from(currentContainer.querySelectorAll('input, select, textarea'));
                            const invalid = inputs.find(el => !el.checkValidity());
                            if (invalid) {
                                invalid.reportValidity();
                                invalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                invalid.focus();
                                return;
                            }
                        }
                        if (this.step < 8) {
                            this.step++;
                            this.maxStepVisited = Math.max(this.maxStepVisited, this.step);
                            this.scrollToTop();
                            if (this.step === 8) {
                                this.$nextTick(() => {
                                    this.setupCanvas();
                                    if (this.signatureData) {
                                        this.loadSignature(this.signatureData);
                                    }
                                });
                            }
                        }
                    },
                    prevStep() {
                        if (this.step > 1) {
                            this.step--;
                            this.scrollToTop();
                        }
                    },
                    goToStep(target) {
                        if (target < 1 || target > 8) return;
                        if (target > this.step) {
                            const currentContainer = this.$refs['step' + this.step];
                            if (currentContainer) {
                                const inputs = Array.from(currentContainer.querySelectorAll('input, select, textarea'));
                                const invalid = inputs.find(el => !el.checkValidity());
                                if (invalid) {
                                    invalid.reportValidity();
                                    invalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    invalid.focus();
                                    return;
                                }
                            }
                        }
                        this.step = target;
                        this.maxStepVisited = Math.max(this.maxStepVisited, this.step);
                        this.scrollToTop();
                        if (this.step === 8) {
                            this.$nextTick(() => {
                                this.setupCanvas();
                                if (this.signatureData) {
                                    this.loadSignature(this.signatureData);
                                }
                            });
                        }
                    },
                    scrollToTop() {
                        this.$nextTick(() => {
                            const topEl = this.$refs.wizardTop;
                            if (topEl) {
                                topEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            }
                        });
                    },
                    handleEnter(e) {
                        if (e.target.tagName === 'TEXTAREA') return;
                        if (this.step < 8) {
                            e.preventDefault();
                            this.nextStep();
                        }
                    },
                    eduFormal: @js(!empty($eduFormal) ? $eduFormal : [['school' => '', 'major' => '', 'period' => '', 'graduated' => 'Tamat', 'dorm' => '', 'notes' => '']]),
                    eduInformal: @js(!empty($eduInformal) ? $eduInformal : [['institute' => '', 'training' => '', 'period' => '', 'graduated' => 'Tamat', 'dorm' => '', 'notes' => '']]),
                    workExperiences: @js(!empty($workExperiences) ? $workExperiences : [['company' => '', 'position' => '', 'period' => '', 'address' => '', 'notes' => '']]),
                    residences: @js(!empty($residences) ? $residences : [['period' => '', 'address' => '', 'city' => '', 'notes' => '']]),
                    children: @js(!empty($family['children']) ? $family['children'] : [['name' => '', 'gender' => 'L', 'age' => '', 'job_school' => '', 'phone' => '', 'notes' => '']]),
                    siblings: @js(!empty($family['siblings']) ? $family['siblings'] : [['name' => '', 'gender' => 'L', 'age' => '', 'job_school' => '', 'phone' => '', 'notes' => '']]),
                    closeFriends: @js(!empty($family['friends']) ? $family['friends'] : [
                        ['name' => '', 'gender' => 'L', 'age' => '', 'job_school' => '', 'phone' => '', 'notes' => ''],
                        ['name' => '', 'gender' => 'P', 'age' => '', 'job_school' => '', 'phone' => '', 'notes' => ''],
                        ['name' => '', 'gender' => 'L', 'age' => '', 'job_school' => '', 'phone' => '', 'notes' => ''],
                    ]),
                    organizations: @js(!empty($organizations) ? $organizations : [['name' => '', 'place' => '', 'position' => '', 'period' => '', 'notes' => '']]),
                    signatureData: @js(old('digital_signature', $biodata->digital_signature ?? '')),
                    hasSignature: @js(!empty(old('digital_signature', $biodata->digital_signature))),
                    isDrawing: false,
                    signedNameInput: @js(old('signed_name', $personal['full_name'] ?? '')),
                    sigCtx: null,
                    addRow(list) {
                        list.push({});
                    },
                    removeRow(list, index) {
                        if (list.length > 1) {
                            list.splice(index, 1);
                        }
                    },
                    initSignature() {
                        const canvas = this.$refs.sigCanvas;
                        if (!canvas) return;
                        this.sigCtx = canvas.getContext('2d');
                        if (this.step === 8) {
                            this.$nextTick(() => {
                                this.setupCanvas();
                                if (this.signatureData) {
                                    this.loadSignature(this.signatureData);
                                }
                            });
                        }
                        window.addEventListener('resize', () => {
                            if (this.step === 8) {
                                if (this.signatureData) {
                                    this.setupCanvas();
                                    this.loadSignature(this.signatureData);
                                } else {
                                    this.setupCanvas();
                                }
                            }
                        });
                    },
                    setupCanvas() {
                        const canvas = this.$refs.sigCanvas;
                        if (!canvas) return;
                        const rect = canvas.getBoundingClientRect();
                        if (rect.width === 0) {
                            setTimeout(() => this.setupCanvas(), 60);
                            return;
                        }
                        const dpr = window.devicePixelRatio || 1;
                        let existingData = null;
                        if (this.hasSignature && this.sigCtx) {
                            existingData = this.signatureData || canvas.toDataURL('image/png');
                        }
                        canvas.width = rect.width * dpr;
                        canvas.height = (rect.height || 180) * dpr;
                        this.sigCtx = canvas.getContext('2d');
                        this.sigCtx.scale(dpr, dpr);
                        this.sigCtx.lineWidth = 2.5;
                        this.sigCtx.lineCap = 'round';
                        this.sigCtx.lineJoin = 'round';
                        this.sigCtx.strokeStyle = '#0f172a';
                        if (existingData) {
                            const img = new Image();
                            img.onload = () => {
                                this.sigCtx.drawImage(img, 0, 0, rect.width, rect.height || 180);
                            };
                            img.src = existingData;
                        }
                    },
                    loadSignature(dataUrl) {
                        const canvas = this.$refs.sigCanvas;
                        if (!canvas || !this.sigCtx) return;
                        const img = new Image();
                        img.onload = () => {
                            const rect = canvas.getBoundingClientRect();
                            this.sigCtx.drawImage(img, 0, 0, rect.width, rect.height || 180);
                            this.hasSignature = true;
                        };
                        img.src = dataUrl;
                    },
                    getSigCoords(e) {
                        const canvas = this.$refs.sigCanvas;
                        const rect = canvas.getBoundingClientRect();
                        let clientX, clientY;
                        if (e.touches && e.touches.length > 0) {
                            clientX = e.touches[0].clientX;
                            clientY = e.touches[0].clientY;
                        } else {
                            clientX = e.clientX;
                            clientY = e.clientY;
                        }
                        return {
                            x: clientX - rect.left,
                            y: clientY - rect.top
                        };
                    },
                    startDraw(e) {
                        if (e.cancelable && e.type.startsWith('touch')) {
                            e.preventDefault();
                        }
                        this.isDrawing = true;
                        const coords = this.getSigCoords(e);
                        this.sigCtx.beginPath();
                        this.sigCtx.moveTo(coords.x, coords.y);
                    },
                    drawing(e) {
                        if (!this.isDrawing) return;
                        if (e.cancelable && e.type.startsWith('touch')) {
                            e.preventDefault();
                        }
                        const coords = this.getSigCoords(e);
                        this.sigCtx.lineTo(coords.x, coords.y);
                        this.sigCtx.stroke();
                        this.hasSignature = true;
                    },
                    endDraw() {
                        if (this.isDrawing) {
                            this.isDrawing = false;
                            this.saveSig();
                        }
                    },
                    clearSignature() {
                        const canvas = this.$refs.sigCanvas;
                        if (!canvas || !this.sigCtx) return;
                        const rect = canvas.getBoundingClientRect();
                        const dpr = window.devicePixelRatio || 1;
                        this.sigCtx.clearRect(0, 0, rect.width * dpr, (rect.height || 180) * dpr);
                        this.hasSignature = false;
                        this.signatureData = '';
                    },
                    saveSig() {
                        const canvas = this.$refs.sigCanvas;
                        if (this.hasSignature && canvas) {
                            this.signatureData = canvas.toDataURL('image/png');
                        } else {
                            this.signatureData = '';
                        }
                    },
                    validateBeforeSubmit(e) {
                        this.saveSig();
                        if (!this.hasSignature || !this.signatureData) {
                            e.preventDefault();
                            this.step = 8;
                            if (window.Swal) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Tanda Tangan Belum Dibubuhkan',
                                    text: 'Mohon bubuhkan tanda tangan digital Anda pada Bagian 8 sebelum mengirimkan formulir biodata.',
                                    confirmButtonColor: '#059669',
                                    confirmButtonText: 'Baik, Saya Tanda Tangani'
                                });
                            } else {
                                alert('Mohon bubuhkan tanda tangan digital Anda pada Bagian 8 sebelum mengirimkan formulir biodata.');
                            }
                            this.$nextTick(() => {
                                this.setupCanvas();
                                if (this.$refs.sigCanvas) {
                                    this.$refs.sigCanvas.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                }
                            });
                            return false;
                        }
                        return true;
                    }
                }"
            >
                @csrf

                <!-- Wizard Progress & Step Tracker Header -->
                <div x-ref="wizardTop" class="rounded-2xl sm:rounded-3xl border border-line bg-surface p-4 sm:p-5 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="flex size-8 sm:size-9 items-center justify-center rounded-xl bg-brand text-white font-bold text-[13px] sm:text-[14px] shadow-sm">
                                <span x-text="step"></span>
                            </span>
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-brand" x-text="'Langkah ' + step + ' dari 8'"></span>
                                <h3 class="text-[15px] sm:text-[17px] font-bold text-ink leading-tight" x-text="steps[step - 1].subtitle"></h3>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1 text-[12px] font-bold text-emerald-700 dark:text-emerald-300">
                                <span x-text="Math.round((step / 8) * 100) + '%'"></span> Selesai
                            </span>
                        </div>
                    </div>

                    <div class="h-2 w-full rounded-full bg-surface-2 overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full transition-all duration-300 ease-out" :style="'width: ' + ((step / 8) * 100) + '%'"></div>
                    </div>

                    <div class="mt-3.5 flex items-center gap-1.5 overflow-x-auto pb-1 pt-1 -mx-2 px-2 no-scrollbar">
                        <template x-for="s in steps" :key="s.num">
                            <button
                                type="button"
                                @click="goToStep(s.num)"
                                class="group inline-flex items-center gap-1.5 shrink-0 rounded-xl px-3 py-1.5 text-[11.5px] font-medium transition cursor-pointer"
                                :class="{
                                    'bg-brand text-white shadow-sm font-semibold': step === s.num,
                                    'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-500/20': step > s.num,
                                    'bg-surface-2 text-subtle hover:text-ink border border-line': step < s.num
                                }"
                            >
                                <template x-if="step > s.num">
                                    <span class="flex size-4 items-center justify-center rounded-full bg-emerald-500 text-white">
                                        <x-icon name="check" class="size-2.5 stroke-[3]" />
                                    </span>
                                </template>
                                <template x-if="step <= s.num">
                                    <span class="flex size-4 items-center justify-center rounded-full text-[10px] font-bold"
                                        :class="step === s.num ? 'bg-white/20 text-white' : 'bg-surface text-subtle border border-line'"
                                        x-text="s.num"
                                    ></span>
                                </template>
                                <span x-text="s.title"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Bagian 1: Identitas Pribadi -->
                <div x-show="step === 1" x-ref="step1" x-cloak class="rounded-2xl sm:rounded-3xl border border-line bg-surface p-4 sm:p-6 lg:p-8 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line pb-4">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-brand">Bagian 1</span>
                            <h2 class="text-[18px] font-bold text-ink">1. Identitas Pribadi</h2>
                        </div>
                        <span class="rounded-full bg-emerald-100 dark:bg-emerald-500/20 px-3 py-1 text-[11px] font-semibold text-emerald-700 dark:text-emerald-300">Sebagian otomatis terisi</span>
                    </div>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="sm:col-span-2">
                            <x-ui.label for="full_name">Nama Lengkap <span class="text-rose-500">*</span></x-ui.label>
                            <x-ui.input id="full_name" name="personal[full_name]" :value="old('personal.full_name', $personal['full_name'] ?? '')" required />
                        </div>

                        <div>
                            <x-ui.label for="nickname">Nama Panggilan</x-ui.label>
                            <x-ui.input id="nickname" name="personal[nickname]" :value="old('personal.nickname', $personal['nickname'] ?? '')" placeholder="Nama panggilan sehari-hari" />
                        </div>

                        <div>
                            <x-ui.label for="birth_place">Tempat Lahir <span class="text-rose-500">*</span></x-ui.label>
                            <x-ui.input id="birth_place" name="personal[birth_place]" :value="old('personal.birth_place', $personal['birth_place'] ?? '')" required />
                        </div>

                        <div>
                            <x-ui.label for="birth_date">Tanggal Lahir <span class="text-rose-500">*</span></x-ui.label>
                            <x-ui.input type="date" id="birth_date" name="personal[birth_date]" :value="old('personal.birth_date', $personal['birth_date'] ?? '')" required />
                        </div>

                        <div>
                            <x-ui.label for="gender">Jenis Kelamin <span class="text-rose-500">*</span></x-ui.label>
                            <select id="gender" name="personal[gender]" required class="h-11 w-full rounded-xl border border-line bg-surface px-3.5 text-[16px] sm:text-[14px] text-ink outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/15">
                                <option value="pria" @selected(old('personal.gender', $personal['gender'] ?? '') === 'pria' || old('personal.gender', $personal['gender'] ?? '') === 'M')>Pria / Laki-laki</option>
                                <option value="wanita" @selected(old('personal.gender', $personal['gender'] ?? '') === 'wanita' || old('personal.gender', $personal['gender'] ?? '') === 'F')>Wanita / Perempuan</option>
                            </select>
                        </div>

                        <div>
                            <x-ui.label for="race">Suku</x-ui.label>
                            <x-ui.input id="race" name="personal[race]" :value="old('personal.race', $personal['race'] ?? '')" placeholder="Contoh: Jawa, Batak, Minang, dll" />
                        </div>

                        <div>
                            <x-ui.label for="nationality">Kewarganegaraan <span class="text-rose-500">*</span></x-ui.label>
                            <x-ui.input id="nationality" name="personal[nationality]" :value="old('personal.nationality', $personal['nationality'] ?? 'Indonesia')" required />
                        </div>

                        <div>
                            <x-ui.label for="religion">Agama <span class="text-rose-500">*</span></x-ui.label>
                            <select id="religion" name="personal[religion]" required class="h-11 w-full rounded-xl border border-line bg-surface px-3.5 text-[16px] sm:text-[14px] text-ink outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/15">
                                <option value="">Pilih Agama</option>
                                <option value="Islam" @selected(old('personal.religion', $personal['religion'] ?? '') === 'Islam')>Islam</option>
                                <option value="Kristen Protestan" @selected(old('personal.religion', $personal['religion'] ?? '') === 'Kristen Protestan')>Kristen Protestan</option>
                                <option value="Katolik" @selected(old('personal.religion', $personal['religion'] ?? '') === 'Katolik')>Katolik</option>
                                <option value="Hindu" @selected(old('personal.religion', $personal['religion'] ?? '') === 'Hindu')>Hindu</option>
                                <option value="Buddha" @selected(old('personal.religion', $personal['religion'] ?? '') === 'Buddha')>Buddha</option>
                                <option value="Khonghucu" @selected(old('personal.religion', $personal['religion'] ?? '') === 'Khonghucu')>Khonghucu</option>
                            </select>
                        </div>

                        <div>
                            <x-ui.label for="marital_status">Status Pernikahan <span class="text-rose-500">*</span></x-ui.label>
                            <select id="marital_status" name="personal[marital_status]" required class="h-11 w-full rounded-xl border border-line bg-surface px-3.5 text-[16px] sm:text-[14px] text-ink outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/15">
                                <option value="Tidak Kawin" @selected(old('personal.marital_status', $personal['marital_status'] ?? '') === 'Tidak Kawin' || old('personal.marital_status', $personal['marital_status'] ?? '') === 'Belum Menikah')>Tidak Kawin / Belum Menikah</option>
                                <option value="Kawin" @selected(old('personal.marital_status', $personal['marital_status'] ?? '') === 'Kawin' || old('personal.marital_status', $personal['marital_status'] ?? '') === 'Menikah')>Kawin / Menikah</option>
                                <option value="Duda" @selected(old('personal.marital_status', $personal['marital_status'] ?? '') === 'Duda')>Duda</option>
                                <option value="Janda" @selected(old('personal.marital_status', $personal['marital_status'] ?? '') === 'Janda')>Janda</option>
                            </select>
                        </div>

                        <div>
                            <x-ui.label for="blood_type">Golongan Darah</x-ui.label>
                            <select id="blood_type" name="personal[blood_type]" class="h-11 w-full rounded-xl border border-line bg-surface px-3.5 text-[16px] sm:text-[14px] text-ink outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/15">
                                <option value="">Pilih</option>
                                <option value="A" @selected(old('personal.blood_type') === 'A')>A</option>
                                <option value="B" @selected(old('personal.blood_type') === 'B')>B</option>
                                <option value="AB" @selected(old('personal.blood_type') === 'AB')>AB</option>
                                <option value="O" @selected(old('personal.blood_type') === 'O')>O</option>
                            </select>
                        </div>

                        <div>
                            <x-ui.label for="birth_order">Anak ke- dari berapa bersaudara</x-ui.label>
                            <div class="flex items-center gap-2">
                                <input type="number" id="birth_order" name="personal[birth_order]" value="{{ old('personal.birth_order', $personal['birth_order'] ?? '') }}" placeholder="Anak ke" min="1" max="20" class="h-11 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[14px] outline-none focus:border-brand" />
                                <span class="text-muted text-[13px]">dari</span>
                                <input type="number" id="siblings_count" name="personal[siblings_count]" value="{{ old('personal.siblings_count', $personal['siblings_count'] ?? '') }}" placeholder="Total" min="1" max="20" class="h-11 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[14px] outline-none focus:border-brand" />
                            </div>
                        </div>

                        <div class="sm:col-span-2 lg:col-span-3">
                            <x-ui.label for="current_address">Alamat Tempat Tinggal (Sekarang) <span class="text-rose-500">*</span></x-ui.label>
                            <x-ui.textarea id="current_address" name="personal[current_address]" :rows="2" required>{{ old('personal.current_address', $personal['current_address'] ?? '') }}</x-ui.textarea>
                        </div>

                        <div>
                            <x-ui.label for="email">Alamat Email <span class="text-rose-500">*</span></x-ui.label>
                            <x-ui.input type="email" id="email" name="personal[email]" :value="old('personal.email', $personal['email'] ?? '')" required />
                        </div>

                        <div>
                            <x-ui.label for="phone_mobile">No. HP / WhatsApp <span class="text-rose-500">*</span></x-ui.label>
                            <x-ui.input id="phone_mobile" name="personal[phone_mobile]" :value="old('personal.phone_mobile', $personal['phone_mobile'] ?? '')" required />
                        </div>

                        <div>
                            <x-ui.label for="phone_home">No. Telepon Rumah (Jika ada)</x-ui.label>
                            <x-ui.input id="phone_home" name="personal[phone_home]" :value="old('personal.phone_home', $personal['phone_home'] ?? '')" />
                        </div>

                        <div>
                            <x-ui.label for="ktp_no">Nomor KTP (NIK 16 Digit) <span class="text-rose-500">*</span></x-ui.label>
                            <x-ui.input id="ktp_no" name="personal[ktp_no]" :value="old('personal.ktp_no', $personal['ktp_no'] ?? '')" required maxlength="16" />
                        </div>

                        <div>
                            <x-ui.label for="npwp_no">Nomor NPWP (Jika ada)</x-ui.label>
                            <x-ui.input id="npwp_no" name="personal[npwp_no]" :value="old('personal.npwp_no', $personal['npwp_no'] ?? '')" placeholder="15/16 digit NPWP" />
                        </div>

                        <div>
                            <x-ui.label for="bpjs_tk_no">Nomor BPJS Ketenagakerjaan</x-ui.label>
                            <x-ui.input id="bpjs_tk_no" name="personal[bpjs_tk_no]" :value="old('personal.bpjs_tk_no', $personal['bpjs_tk_no'] ?? '')" placeholder="Jika sudah punya sebelumnya" />
                        </div>

                        <div class="sm:col-span-2 lg:col-span-3">
                            <x-ui.label for="hobby">Kegemaran / Hobi</x-ui.label>
                            <x-ui.input id="hobby" name="personal[hobby]" :value="old('personal.hobby', $personal['hobby'] ?? '')" placeholder="Contoh: Membaca, Olahraga Badminton, Musik" />
                        </div>
                    </div>

                    <div class="mt-8 border-t border-line pt-6">
                        <div class="flex items-center gap-2">
                            <x-icon name="paperclip" class="size-4 text-brand" />
                            <h3 class="text-[14px] font-bold text-ink">Lampiran Berkas Wajib (Upload Foto/PDF)</h3>
                        </div>
                        <p class="mt-1 text-[12px] text-muted">Lampirkan foto/scan kartu KTP, NPWP, BPJS Ketenagakerjaan, dan Kartu Keluarga (KK) (maks 4MB/file)</p>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-line p-3.5 bg-surface-2/30">
                                <x-ui.label for="attachment_ktp">Scan / Foto KTP <span class="text-rose-500">*</span></x-ui.label>
                                <input type="file" id="attachment_ktp" name="attachment_ktp" accept="image/*,.pdf" class="mt-1.5 block w-full text-[13px] sm:text-[12.5px] file:mr-3 file:rounded-xl file:border-0 file:bg-brand/10 file:px-3 file:py-2 file:text-[12.5px] file:font-semibold file:text-brand hover:file:bg-brand/20" />
                            </div>

                            <div class="rounded-2xl border border-line p-3.5 bg-surface-2/30">
                                <x-ui.label for="attachment_kk">Scan / Foto Kartu Keluarga (KK) <span class="text-rose-500">*</span></x-ui.label>
                                <input type="file" id="attachment_kk" name="attachment_kk" accept="image/*,.pdf" class="mt-1.5 block w-full text-[13px] sm:text-[12.5px] file:mr-3 file:rounded-xl file:border-0 file:bg-brand/10 file:px-3 file:py-2 file:text-[12.5px] file:font-semibold file:text-brand hover:file:bg-brand/20" />
                            </div>

                            <div class="rounded-2xl border border-line p-3.5 bg-surface-2/30">
                                <x-ui.label for="attachment_npwp">Scan / Foto Kartu NPWP (Jika ada)</x-ui.label>
                                <input type="file" id="attachment_npwp" name="attachment_npwp" accept="image/*,.pdf" class="mt-1.5 block w-full text-[13px] sm:text-[12.5px] file:mr-3 file:rounded-xl file:border-0 file:bg-brand/10 file:px-3 file:py-2 file:text-[12.5px] file:font-semibold file:text-brand hover:file:bg-brand/20" />
                            </div>

                            <div class="rounded-2xl border border-line p-3.5 bg-surface-2/30">
                                <x-ui.label for="attachment_bpjs">Scan / Foto Kartu BPJS Ketenagakerjaan (Jika ada)</x-ui.label>
                                <input type="file" id="attachment_bpjs" name="attachment_bpjs" accept="image/*,.pdf" class="mt-1.5 block w-full text-[13px] sm:text-[12.5px] file:mr-3 file:rounded-xl file:border-0 file:bg-brand/10 file:px-3 file:py-2 file:text-[12.5px] file:font-semibold file:text-brand hover:file:bg-brand/20" />
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center justify-between gap-3 border-t border-line pt-5">
                        <span class="text-[12px] text-subtle font-medium">Langkah 1 dari 8</span>
                        <button
                            type="button"
                            @click="nextStep()"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-brand px-6 py-3 text-[14px] font-semibold text-white hover:bg-brand-hover transition shadow-sm"
                        >
                            <span>Lanjut ke Rekening Bank</span>
                            <x-icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                </div>

                <!-- Bagian 2: Informasi Rekening Bank -->
                <div x-show="step === 2" x-ref="step2" x-cloak class="rounded-2xl sm:rounded-3xl border border-line bg-surface p-4 sm:p-6 lg:p-8 shadow-sm">
                    <div class="border-b border-line pb-4">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-brand">Bagian 2</span>
                        <h2 class="text-[18px] font-bold text-ink">2. Informasi Rekening Bank</h2>
                        <p class="text-[12px] text-muted">Untuk keperluan payroll dan penggajian karyawan</p>
                    </div>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div class="rounded-2xl border border-blue-500/20 bg-blue-50/20 dark:bg-blue-950/10 p-4 space-y-3">
                            <h3 class="text-[13.5px] font-bold text-blue-900 dark:text-blue-300">Rekening Bank BCA (Diutamakan)</h3>
                            <div>
                                <x-ui.label for="bank_bca_no">Nomor Rekening BCA</x-ui.label>
                                <x-ui.input id="bank_bca_no" name="bank_accounts[bca][number]" :value="old('bank_accounts.bca.number', $bankAccounts['bca']['number'] ?? '')" placeholder="10 digit nomor rekening" />
                            </div>
                            <div>
                                <x-ui.label for="bank_bca_branch">Kantor Cabang Pembuka</x-ui.label>
                                <x-ui.input id="bank_bca_branch" name="bank_accounts[bca][branch]" :value="old('bank_accounts.bca.branch', $bankAccounts['bca']['branch'] ?? '')" placeholder="Contoh: KCU Sudirman / KCP Daan Mogot" />
                            </div>
                            <div>
                                <x-ui.label for="bank_bca_holder">Atas Nama Rekening</x-ui.label>
                                <x-ui.input id="bank_bca_holder" name="bank_accounts[bca][holder]" :value="old('bank_accounts.bca.holder', $bankAccounts['bca']['holder'] ?? ($personal['full_name'] ?? ''))" placeholder="Wajib sesuai buku tabungan" />
                            </div>
                        </div>

                        <div class="rounded-2xl border border-line bg-surface-2/30 p-4 space-y-3">
                            <h3 class="text-[13.5px] font-bold text-ink">Rekening Bank Lainnya (Opsional)</h3>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <x-ui.label for="other_bank_name">Nama Bank</x-ui.label>
                                    <x-ui.input id="other_bank_name" name="bank_accounts[other][bank_name]" :value="old('bank_accounts.other.bank_name', $bankAccounts['other']['bank_name'] ?? '')" placeholder="Mandiri / BRI / BNI" />
                                </div>
                                <div>
                                    <x-ui.label for="other_bank_no">Nomor Rekening</x-ui.label>
                                    <x-ui.input id="other_bank_no" name="bank_accounts[other][number]" :value="old('bank_accounts.other.number', $bankAccounts['other']['number'] ?? '')" />
                                </div>
                            </div>
                            <div>
                                <x-ui.label for="other_bank_branch">Kantor Cabang</x-ui.label>
                                <x-ui.input id="other_bank_branch" name="bank_accounts[other][branch]" :value="old('bank_accounts.other.branch', $bankAccounts['other']['branch'] ?? '')" />
                            </div>
                            <div>
                                <x-ui.label for="other_bank_holder">Atas Nama Rekening</x-ui.label>
                                <x-ui.input id="other_bank_holder" name="bank_accounts[other][holder]" :value="old('bank_accounts.other.holder', $bankAccounts['other']['holder'] ?? '')" />
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center justify-between gap-3 border-t border-line pt-5">
                        <button
                            type="button"
                            @click="prevStep()"
                            class="inline-flex items-center gap-2 rounded-xl border border-line bg-surface-2 px-5 py-3 text-[14px] font-semibold text-ink hover:bg-surface-3 transition shadow-sm"
                        >
                            <x-icon name="chevron-left" class="size-4" />
                            <span>Sebelumnya</span>
                        </button>
                        <button
                            type="button"
                            @click="nextStep()"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand px-6 py-3 text-[14px] font-semibold text-white hover:bg-brand-hover transition shadow-sm"
                        >
                            <span>Lanjut ke Riwayat Pendidikan</span>
                            <x-icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                </div>

                <!-- Bagian 3: Riwayat Pendidikan -->
                <div x-show="step === 3" x-ref="step3" x-cloak class="rounded-2xl sm:rounded-3xl border border-line bg-surface p-4 sm:p-6 lg:p-8 shadow-sm">
                    <div class="border-b border-line pb-4">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-brand">Bagian 3</span>
                        <h2 class="text-[18px] font-bold text-ink">3. Riwayat Pendidikan</h2>
                    </div>

                    <div class="mt-6">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h3 class="text-[14px] font-bold text-ink">a. Pendidikan Formal</h3>
                            <button type="button" x-on:click="addRow(eduFormal)" class="inline-flex items-center gap-1.5 rounded-xl border border-brand/30 bg-brand/10 px-3 py-1.5 text-[12px] font-semibold text-brand transition hover:bg-brand/20">
                                <x-icon name="plus" class="size-3.5" />
                                Tambah Formal
                            </button>
                        </div>

                        <div class="mt-4 space-y-3">
                            <template x-for="(row, idx) in eduFormal" :key="idx">
                                <div class="rounded-2xl border border-line bg-surface-2/20 p-3.5 sm:p-4 space-y-3">
                                    <div class="flex items-center justify-between border-b border-line/60 pb-2">
                                        <span class="text-[12.5px] font-bold text-ink" x-text="'Pendidikan Formal #' + (idx + 1)"></span>
                                        <button type="button" x-on:click="removeRow(eduFormal, idx)" class="inline-flex items-center gap-1 text-[12px] text-rose-500 hover:text-rose-700">
                                            <x-icon name="trash" class="size-3.5" /> Hapus
                                        </button>
                                    </div>
                                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Nama & Alamat Sekolah / Kampus</label>
                                            <input type="text" :name="'education_formal[' + idx + '][school]'" x-model="row.school" placeholder="Nama & Alamat Sekolah" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand" />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Jurusan</label>
                                            <input type="text" :name="'education_formal[' + idx + '][major]'" x-model="row.major" placeholder="Jurusan" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand" />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Periode (Dari Thn s/d Thn)</label>
                                            <input type="text" :name="'education_formal[' + idx + '][period]'" x-model="row.period" placeholder="Contoh: 2018 - 2022" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand" />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Status Kelulusan</label>
                                            <select :name="'education_formal[' + idx + '][graduated]'" x-model="row.graduated" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand">
                                                <option value="Tamat">Tamat</option>
                                                <option value="Tidak Tamat">Tidak Tamat</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Alamat yg Dipondoki Selama Sekolah</label>
                                            <input type="text" :name="'education_formal[' + idx + '][dorm]'" x-model="row.dorm" placeholder="Alamat pondokan / kost" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand" />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Keterangan / Nilai</label>
                                            <input type="text" :name="'education_formal[' + idx + '][notes]'" x-model="row.notes" placeholder="Keterangan / Nilai" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand" />
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="mt-8 border-t border-line pt-6">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h3 class="text-[14px] font-bold text-ink">b. Pendidikan Informal / Kursus & Pelatihan</h3>
                            <button type="button" x-on:click="addRow(eduInformal)" class="inline-flex items-center gap-1.5 rounded-xl border border-brand/30 bg-brand/10 px-3 py-1.5 text-[12px] font-semibold text-brand transition hover:bg-brand/20">
                                <x-icon name="plus" class="size-3.5" />
                                Tambah Informal
                            </button>
                        </div>

                        <div class="mt-4 space-y-3">
                            <template x-for="(row, idx) in eduInformal" :key="idx">
                                <div class="rounded-2xl border border-line bg-surface-2/20 p-3.5 sm:p-4 space-y-3">
                                    <div class="flex items-center justify-between border-b border-line/60 pb-2">
                                        <span class="text-[12.5px] font-bold text-ink" x-text="'Pelatihan #' + (idx + 1)"></span>
                                        <button type="button" x-on:click="removeRow(eduInformal, idx)" class="inline-flex items-center gap-1 text-[12px] text-rose-500 hover:text-rose-700">
                                            <x-icon name="trash" class="size-3.5" /> Hapus
                                        </button>
                                    </div>
                                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Nama Institusi / Lembaga</label>
                                            <input type="text" :name="'education_informal[' + idx + '][institute]'" x-model="row.institute" placeholder="Nama Institusi" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand" />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Jenis Kursus / Pelatihan</label>
                                            <input type="text" :name="'education_informal[' + idx + '][training]'" x-model="row.training" placeholder="Jenis Kursus / Pelatihan" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand" />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Periode (Dari Thn s/d Thn)</label>
                                            <input type="text" :name="'education_informal[' + idx + '][period]'" x-model="row.period" placeholder="Tahun (Dari s/d Thn)" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand" />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Status Kelulusan</label>
                                            <select :name="'education_informal[' + idx + '][graduated]'" x-model="row.graduated" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand">
                                                <option value="Tamat">Tamat</option>
                                                <option value="Tidak Tamat">Tidak Tamat</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Alamat yg Dipondoki</label>
                                            <input type="text" :name="'education_informal[' + idx + '][dorm]'" x-model="row.dorm" placeholder="Alamat yg dipondoki" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand" />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Keterangan / Sertifikat</label>
                                            <input type="text" :name="'education_informal[' + idx + '][notes]'" x-model="row.notes" placeholder="Keterangan / Sertifikat" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand" />
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center justify-between gap-3 border-t border-line pt-5">
                        <button
                            type="button"
                            @click="prevStep()"
                            class="inline-flex items-center gap-2 rounded-xl border border-line bg-surface-2 px-5 py-3 text-[14px] font-semibold text-ink hover:bg-surface-3 transition shadow-sm"
                        >
                            <x-icon name="chevron-left" class="size-4" />
                            <span>Sebelumnya</span>
                        </button>
                        <button
                            type="button"
                            @click="nextStep()"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand px-6 py-3 text-[14px] font-semibold text-white hover:bg-brand-hover transition shadow-sm"
                        >
                            <span>Lanjut ke Pengalaman Kerja</span>
                            <x-icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                </div>

                <!-- Bagian 4: Pengalaman Kerja -->
                <div x-show="step === 4" x-ref="step4" x-cloak class="rounded-2xl sm:rounded-3xl border border-line bg-surface p-4 sm:p-6 lg:p-8 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line pb-4">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-brand">Bagian 4</span>
                            <h2 class="text-[18px] font-bold text-ink">4. Pengalaman Kerja</h2>
                        </div>
                        <button type="button" x-on:click="addRow(workExperiences)" class="inline-flex items-center gap-1.5 rounded-xl border border-brand/30 bg-brand/10 px-3 py-1.5 text-[12px] font-semibold text-brand transition hover:bg-brand/20">
                            <x-icon name="plus" class="size-3.5" />
                            Tambah Pengalaman
                        </button>
                    </div>

                    <div class="mt-4 space-y-3">
                        <template x-for="(work, idx) in workExperiences" :key="idx">
                            <div class="rounded-2xl border border-line bg-surface-2/20 p-3.5 sm:p-4 space-y-3">
                                <div class="flex items-center justify-between border-b border-line/60 pb-2">
                                    <span class="text-[12.5px] font-bold text-ink" x-text="'Pengalaman #' + (idx + 1)"></span>
                                    <button type="button" x-on:click="removeRow(workExperiences, idx)" class="inline-flex items-center gap-1 text-[12px] text-rose-500 hover:text-rose-700">
                                        <x-icon name="trash" class="size-3.5" /> Hapus
                                    </button>
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-subtle mb-1">Nama Perusahaan</label>
                                        <input type="text" :name="'work_experiences[' + idx + '][company]'" x-model="work.company" placeholder="Nama Perusahaan" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none focus:border-brand" />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-subtle mb-1">Jabatan</label>
                                        <input type="text" :name="'work_experiences[' + idx + '][position]'" x-model="work.position" placeholder="Jabatan / Posisi" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none focus:border-brand" />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-subtle mb-1">Dari Thn s/d Thn</label>
                                        <input type="text" :name="'work_experiences[' + idx + '][period]'" x-model="work.period" placeholder="Contoh: 2020 - 2023" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none focus:border-brand" />
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-[11px] font-semibold text-subtle mb-1">Alamat Kantor</label>
                                        <input type="text" :name="'work_experiences[' + idx + '][address]'" x-model="work.address" placeholder="Alamat kantor perusahaan" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none focus:border-brand" />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-subtle mb-1">Keterangan / Alasan Pindah</label>
                                        <input type="text" :name="'work_experiences[' + idx + '][notes]'" x-model="work.notes" placeholder="Alasan pindah / resign" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none focus:border-brand" />
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center justify-between gap-3 border-t border-line pt-5">
                        <button
                            type="button"
                            @click="prevStep()"
                            class="inline-flex items-center gap-2 rounded-xl border border-line bg-surface-2 px-5 py-3 text-[14px] font-semibold text-ink hover:bg-surface-3 transition shadow-sm"
                        >
                            <x-icon name="chevron-left" class="size-4" />
                            <span>Sebelumnya</span>
                        </button>
                        <button
                            type="button"
                            @click="nextStep()"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand px-6 py-3 text-[14px] font-semibold text-white hover:bg-brand-hover transition shadow-sm"
                        >
                            <span>Lanjut ke Tempat Tinggal</span>
                            <x-icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                </div>

                <!-- Bagian 5: Riwayat Tempat Tinggal -->
                <div x-show="step === 5" x-ref="step5" x-cloak class="rounded-2xl sm:rounded-3xl border border-line bg-surface p-4 sm:p-6 lg:p-8 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line pb-4">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-brand">Bagian 5</span>
                            <h2 class="text-[18px] font-bold text-ink">5. Tempat Tinggal Sejak Dilahirkan (Selengkapnya & Kronologis)</h2>
                            <p class="text-[12px] text-muted">Tuliskan riwayat tempat tinggal dari lahir hingga saat ini</p>
                        </div>
                        <button type="button" x-on:click="addRow(residences)" class="inline-flex items-center gap-1.5 rounded-xl border border-brand/30 bg-brand/10 px-3 py-1.5 text-[12px] font-semibold text-brand transition hover:bg-brand/20">
                            <x-icon name="plus" class="size-3.5" />
                            Tambah Alamat
                        </button>
                    </div>

                    <div class="mt-4 space-y-3">
                        <template x-for="(res, idx) in residences" :key="idx">
                            <div class="rounded-2xl border border-line bg-surface-2/20 p-3.5 sm:p-4 space-y-3">
                                <div class="flex items-center justify-between border-b border-line/60 pb-2">
                                    <span class="text-[12.5px] font-bold text-ink" x-text="'Tempat Tinggal #' + (idx + 1)"></span>
                                    <button type="button" x-on:click="removeRow(residences, idx)" class="inline-flex items-center gap-1 text-[12px] text-rose-500 hover:text-rose-700">
                                        <x-icon name="trash" class="size-3.5" /> Hapus
                                    </button>
                                </div>
                                <div class="grid gap-3 sm:grid-cols-4">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-subtle mb-1">Periode (Dari Thn s/d Thn)</label>
                                        <input type="text" :name="'residences_history[' + idx + '][period]'" x-model="res.period" placeholder="Contoh: 1998 - 2016" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand" />
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-[11px] font-semibold text-subtle mb-1">Nama Jalan & Nomor Rumah</label>
                                        <input type="text" :name="'residences_history[' + idx + '][address]'" x-model="res.address" placeholder="Nama Jalan & Nomor Rumah" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand" />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-subtle mb-1">Kota / Keterangan</label>
                                        <input type="text" :name="'residences_history[' + idx + '][city]'" x-model="res.city" placeholder="Kota / Keterangan" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand" />
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center justify-between gap-3 border-t border-line pt-5">
                        <button
                            type="button"
                            @click="prevStep()"
                            class="inline-flex items-center gap-2 rounded-xl border border-line bg-surface-2 px-5 py-3 text-[14px] font-semibold text-ink hover:bg-surface-3 transition shadow-sm"
                        >
                            <x-icon name="chevron-left" class="size-4" />
                            <span>Sebelumnya</span>
                        </button>
                        <button
                            type="button"
                            @click="nextStep()"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand px-6 py-3 text-[14px] font-semibold text-white hover:bg-brand-hover transition shadow-sm"
                        >
                            <span>Lanjut ke Data Keluarga</span>
                            <x-icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                </div>

                <!-- Bagian 6: Keterangan Keluarga -->
                <div x-show="step === 6" x-ref="step6" x-cloak class="rounded-2xl sm:rounded-3xl border border-line bg-surface p-4 sm:p-6 lg:p-8 shadow-sm">
                    <div class="border-b border-line pb-4">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-brand">Bagian 6</span>
                        <h2 class="text-[18px] font-bold text-ink">6. Keterangan Keluarga</h2>
                        <p class="text-[12px] text-muted">Istri/Suami, Orang Tua, Mertua, Anak, Saudara, & 3 Sahabat Karib</p>
                    </div>

                    <div class="mt-6 rounded-2xl border border-line/80 bg-surface-2/30 p-4">
                        <h3 class="text-[13.5px] font-bold text-ink">a. Istri / Suami (Kosongkan jika belum menikah)</h3>
                        <div class="mt-3 grid gap-3 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <x-ui.label>Nama Lengkap</x-ui.label>
                                <input type="text" name="family_details[spouse][name]" value="{{ old('family_details.spouse.name', $family['spouse']['name'] ?? '') }}" placeholder="Nama lengkap pasangan" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none focus:border-brand" />
                            </div>
                            <div>
                                <x-ui.label>TTL / Lahir</x-ui.label>
                                <input type="text" name="family_details[spouse][birth]" value="{{ old('family_details.spouse.birth', $family['spouse']['birth_date'] ?? '') }}" placeholder="Kota & Tanggal" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none focus:border-brand" />
                            </div>
                            <div>
                                <x-ui.label>Agama & Suku</x-ui.label>
                                <input type="text" name="family_details[spouse][religion_race]" value="{{ old('family_details.spouse.religion_race', $family['spouse']['religion'] ?? '') }}" placeholder="Agama & Suku" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none focus:border-brand" />
                            </div>
                            <div>
                                <x-ui.label>Pekerjaan</x-ui.label>
                                <input type="text" name="family_details[spouse][job]" value="{{ old('family_details.spouse.job', $family['spouse']['job'] ?? '') }}" placeholder="Pekerjaan pasangan" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none focus:border-brand" />
                            </div>
                            <div>
                                <x-ui.label>No. Telepon / HP</x-ui.label>
                                <input type="text" name="family_details[spouse][phone]" value="{{ old('family_details.spouse.phone', $family['spouse']['phone'] ?? '') }}" placeholder="08..." class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none focus:border-brand" />
                            </div>
                            <div class="sm:col-span-3">
                                <x-ui.label>Alamat Kantor & Telp</x-ui.label>
                                <input type="text" name="family_details[spouse][office]" value="{{ old('family_details.spouse.office', $family['spouse']['office_address'] ?? '') }}" placeholder="Alamat kantor tempat pasangan bekerja" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none focus:border-brand" />
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-line/80 bg-surface-2/30 p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <h3 class="text-[13.5px] font-bold text-ink">b. Bapak</h3>
                                <select name="family_details[father][status_type]" class="h-8 sm:h-7 rounded-lg border border-line bg-surface px-2 text-[12px] sm:text-[11px] text-ink outline-none focus:border-brand">
                                    <option value="Kandung" selected>Kandung</option>
                                    <option value="Angkat">Angkat</option>
                                    <option value="Tiri">Tiri</option>
                                </select>
                            </div>
                            <div>
                                <x-ui.label>Nama Lengkap <span class="text-rose-500">*</span></x-ui.label>
                                <input type="text" name="family_details[father][name]" value="{{ old('family_details.father.name', $family['father']['name'] ?? '') }}" required placeholder="Nama ayah" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none focus:border-brand" />
                            </div>
                            <div>
                                <x-ui.label>Pekerjaan</x-ui.label>
                                <input type="text" name="family_details[father][job]" value="{{ old('family_details.father.job', $family['father']['job'] ?? '') }}" placeholder="Pekerjaan ayah" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none focus:border-brand" />
                            </div>
                            <div>
                                <x-ui.label>No. HP / Telp</x-ui.label>
                                <input type="text" name="family_details[father][phone]" value="{{ old('family_details.father.phone', $family['father']['phone'] ?? '') }}" placeholder="08..." class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none focus:border-brand" />
                            </div>
                        </div>

                        <div class="rounded-2xl border border-line/80 bg-surface-2/30 p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <h3 class="text-[13.5px] font-bold text-ink">c. Ibu</h3>
                                <select name="family_details[mother][status_type]" class="h-8 sm:h-7 rounded-lg border border-line bg-surface px-2 text-[12px] sm:text-[11px] text-ink outline-none focus:border-brand">
                                    <option value="Kandung" selected>Kandung</option>
                                    <option value="Angkat">Angkat</option>
                                    <option value="Tiri">Tiri</option>
                                </select>
                            </div>
                            <div>
                                <x-ui.label>Nama Lengkap <span class="text-rose-500">*</span></x-ui.label>
                                <input type="text" name="family_details[mother][name]" value="{{ old('family_details.mother.name', $family['mother']['name'] ?? '') }}" required placeholder="Nama ibu" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none focus:border-brand" />
                            </div>
                            <div>
                                <x-ui.label>Pekerjaan</x-ui.label>
                                <input type="text" name="family_details[mother][job]" value="{{ old('family_details.mother.job', $family['mother']['job'] ?? '') }}" placeholder="Pekerjaan ibu" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none focus:border-brand" />
                            </div>
                            <div>
                                <x-ui.label>No. HP / Telp</x-ui.label>
                                <input type="text" name="family_details[mother][phone]" value="{{ old('family_details.mother.phone', $family['mother']['phone'] ?? '') }}" placeholder="08..." class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none focus:border-brand" />
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-line/80 bg-surface-2/30 p-4 space-y-3">
                            <h3 class="text-[13.5px] font-bold text-ink">d. Bapak Mertua (Jika menikah)</h3>
                            <div>
                                <x-ui.label>Nama Lengkap</x-ui.label>
                                <input type="text" name="family_details[father_in_law][name]" value="{{ old('family_details.father_in_law.name', $family['father_in_law']['name'] ?? '') }}" placeholder="Nama ayah mertua" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none focus:border-brand" />
                            </div>
                            <div>
                                <x-ui.label>Pekerjaan & Telp</x-ui.label>
                                <input type="text" name="family_details[father_in_law][job]" value="{{ old('family_details.father_in_law.job', $family['father_in_law']['job'] ?? '') }}" placeholder="Pekerjaan / Telp" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none focus:border-brand" />
                            </div>
                        </div>

                        <div class="rounded-2xl border border-line/80 bg-surface-2/30 p-4 space-y-3">
                            <h3 class="text-[13.5px] font-bold text-ink">e. Ibu Mertua (Jika menikah)</h3>
                            <div>
                                <x-ui.label>Nama Lengkap</x-ui.label>
                                <input type="text" name="family_details[mother_in_law][name]" value="{{ old('family_details.mother_in_law.name', $family['mother_in_law']['name'] ?? '') }}" placeholder="Nama ibu mertua" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none focus:border-brand" />
                            </div>
                            <div>
                                <x-ui.label>Pekerjaan & Telp</x-ui.label>
                                <input type="text" name="family_details[mother_in_law][job]" value="{{ old('family_details.mother_in_law.job', $family['mother_in_law']['job'] ?? '') }}" placeholder="Pekerjaan / Telp" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none focus:border-brand" />
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-line pt-6">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h3 class="text-[14px] font-bold text-ink">f. Anak-Anak (Kandung, Tiri, Angkat)</h3>
                            <button type="button" x-on:click="addRow(children)" class="inline-flex items-center gap-1.5 rounded-xl border border-brand/30 bg-brand/10 px-3 py-1.5 text-[12px] font-semibold text-brand transition hover:bg-brand/20">
                                <x-icon name="plus" class="size-3.5" />
                                Tambah Anak
                            </button>
                        </div>

                        <div class="mt-4 space-y-3">
                            <template x-for="(child, idx) in children" :key="idx">
                                <div class="rounded-2xl border border-line bg-surface p-3.5 sm:p-4 space-y-3">
                                    <div class="flex items-center justify-between border-b border-line/60 pb-2">
                                        <span class="text-[12.5px] font-bold text-ink" x-text="'Anak #' + (idx + 1)"></span>
                                        <button type="button" x-on:click="removeRow(children, idx)" class="inline-flex items-center gap-1 text-[12px] text-rose-500 hover:text-rose-700">
                                            <x-icon name="trash" class="size-3.5" /> Hapus
                                        </button>
                                    </div>
                                    <div class="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-5">
                                        <div class="sm:col-span-2 lg:col-span-1">
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Nama Lengkap</label>
                                            <input type="text" :name="'family_details[children][' + idx + '][name]'" x-model="child.name" placeholder="Nama anak" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand" />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Jenis Kelamin</label>
                                            <select :name="'family_details[children][' + idx + '][gender]'" x-model="child.gender" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand">
                                                <option value="L">Laki-laki (L)</option>
                                                <option value="P">Perempuan (P)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Umur (Tahun)</label>
                                            <input type="text" :name="'family_details[children][' + idx + '][age]'" x-model="child.age" placeholder="Contoh: 7" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand" />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Pekerjaan / Sekolah</label>
                                            <input type="text" :name="'family_details[children][' + idx + '][job_school]'" x-model="child.job_school" placeholder="SD / SMP / Bekerja" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand" />
                                        </div>
                                        <div class="sm:col-span-2 lg:col-span-1">
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Keterangan</label>
                                            <input type="text" :name="'family_details[children][' + idx + '][notes]'" x-model="child.notes" placeholder="Catatan" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand" />
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-line pt-6">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h3 class="text-[14px] font-bold text-ink">g. Saudara-Saudara (Kandung, Tiri, Angkat)</h3>
                            <button type="button" x-on:click="addRow(siblings)" class="inline-flex items-center gap-1.5 rounded-xl border border-brand/30 bg-brand/10 px-3 py-1.5 text-[12px] font-semibold text-brand transition hover:bg-brand/20">
                                <x-icon name="plus" class="size-3.5" />
                                Tambah Saudara
                            </button>
                        </div>

                        <div class="mt-4 space-y-3">
                            <template x-for="(sib, idx) in siblings" :key="idx">
                                <div class="rounded-2xl border border-line bg-surface p-3.5 sm:p-4 space-y-3">
                                    <div class="flex items-center justify-between border-b border-line/60 pb-2">
                                        <span class="text-[12.5px] font-bold text-ink" x-text="'Saudara #' + (idx + 1)"></span>
                                        <button type="button" x-on:click="removeRow(siblings, idx)" class="inline-flex items-center gap-1 text-[12px] text-rose-500 hover:text-rose-700">
                                            <x-icon name="trash" class="size-3.5" /> Hapus
                                        </button>
                                    </div>
                                    <div class="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-5">
                                        <div class="sm:col-span-2 lg:col-span-1">
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Nama Lengkap</label>
                                            <input type="text" :name="'family_details[siblings][' + idx + '][name]'" x-model="sib.name" placeholder="Nama saudara" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand" />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Jenis Kelamin</label>
                                            <select :name="'family_details[siblings][' + idx + '][gender]'" x-model="sib.gender" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand">
                                                <option value="L">Laki-laki (L)</option>
                                                <option value="P">Perempuan (P)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Umur (Tahun)</label>
                                            <input type="text" :name="'family_details[siblings][' + idx + '][age]'" x-model="sib.age" placeholder="Contoh: 25" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand" />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Pekerjaan / Sekolah</label>
                                            <input type="text" :name="'family_details[siblings][' + idx + '][job_school]'" x-model="sib.job_school" placeholder="Pekerjaan / Sekolah" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand" />
                                        </div>
                                        <div class="sm:col-span-2 lg:col-span-1">
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">No. Telp / Keterangan</label>
                                            <input type="text" :name="'family_details[siblings][' + idx + '][notes]'" x-model="sib.notes" placeholder="No Telp / Ket" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand" />
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-line pt-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-[14px] font-bold text-ink">h. Kawan-Kawan Terdekat / Sahabat Karib (3 Orang yang Masih Hidup) <span class="text-rose-500">*</span></h3>
                                <p class="text-[12px] text-muted">Untuk keperluan kontak darurat dan verifikasi referensi personal</p>
                            </div>
                        </div>

                        <div class="mt-4 space-y-3">
                            <template x-for="(fr, idx) in closeFriends" :key="idx">
                                <div class="rounded-2xl border border-line bg-surface-2/20 p-3.5 sm:p-4 space-y-3">
                                    <div class="flex items-center justify-between border-b border-line/60 pb-2">
                                        <span class="text-[12.5px] font-bold text-ink" x-text="'Sahabat #' + (idx + 1)"></span>
                                    </div>
                                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                                            <input type="text" :name="'family_details[friends][' + idx + '][name]'" x-model="fr.name" placeholder="Nama Lengkap" required class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand" />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">No. Telepon / HP <span class="text-rose-500">*</span></label>
                                            <input type="text" :name="'family_details[friends][' + idx + '][phone]'" x-model="fr.phone" placeholder="No. Telepon / HP" required class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand" />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Pekerjaan & Alamat <span class="text-rose-500">*</span></label>
                                            <input type="text" :name="'family_details[friends][' + idx + '][job_school]'" x-model="fr.job_school" placeholder="Pekerjaan & Alamat" required class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand" />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Kenal Sejak / Keterangan <span class="text-rose-500">*</span></label>
                                            <input type="text" :name="'family_details[friends][' + idx + '][notes]'" x-model="fr.notes" placeholder="Kenal Sejak / Keterangan" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand" />
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center justify-between gap-3 border-t border-line pt-5">
                        <button
                            type="button"
                            @click="prevStep()"
                            class="inline-flex items-center gap-2 rounded-xl border border-line bg-surface-2 px-5 py-3 text-[14px] font-semibold text-ink hover:bg-surface-3 transition shadow-sm"
                        >
                            <x-icon name="chevron-left" class="size-4" />
                            <span>Sebelumnya</span>
                        </button>
                        <button
                            type="button"
                            @click="nextStep()"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand px-6 py-3 text-[14px] font-semibold text-white hover:bg-brand-hover transition shadow-sm"
                        >
                            <span>Lanjut ke Pengalaman Organisasi</span>
                            <x-icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                </div>

                <!-- Bagian 7: Pengalaman Berorganisasi -->
                <div x-show="step === 7" x-ref="step7" x-cloak class="rounded-2xl sm:rounded-3xl border border-line bg-surface p-4 sm:p-6 lg:p-8 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line pb-4">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-brand">Bagian 7</span>
                            <h2 class="text-[18px] font-bold text-ink">7. Pengalaman Berorganisasi</h2>
                        </div>
                        <button type="button" x-on:click="addRow(organizations)" class="inline-flex items-center gap-1.5 rounded-xl border border-brand/30 bg-brand/10 px-3 py-1.5 text-[12px] font-semibold text-brand transition hover:bg-brand/20">
                            <x-icon name="plus" class="size-3.5" />
                            Tambah Organisasi
                        </button>
                    </div>

                    <div class="mt-4 space-y-3">
                        <template x-for="(org, idx) in organizations" :key="idx">
                            <div class="rounded-2xl border border-line bg-surface p-3.5 sm:p-4 space-y-3">
                                <div class="flex items-center justify-between border-b border-line/60 pb-2">
                                    <span class="text-[12.5px] font-bold text-ink" x-text="'Organisasi #' + (idx + 1)"></span>
                                    <button type="button" x-on:click="removeRow(organizations, idx)" class="inline-flex items-center gap-1 text-[12px] text-rose-500 hover:text-rose-700">
                                        <x-icon name="trash" class="size-3.5" /> Hapus
                                    </button>
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-subtle mb-1">Nama Organisasi</label>
                                        <input type="text" :name="'organization_history[' + idx + '][name]'" x-model="org.name" placeholder="Nama Organisasi" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand" />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-subtle mb-1">Tempat / Kota</label>
                                        <input type="text" :name="'organization_history[' + idx + '][place]'" x-model="org.place" placeholder="Tempat / Kota" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand" />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-subtle mb-1">Jabatan</label>
                                        <input type="text" :name="'organization_history[' + idx + '][position]'" x-model="org.position" placeholder="Jabatan" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand" />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-subtle mb-1">Dari Thn s/d Thn</label>
                                        <input type="text" :name="'organization_history[' + idx + '][period]'" x-model="org.period" placeholder="Contoh: 2020 - 2022" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13px] text-ink outline-none focus:border-brand" />
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center justify-between gap-3 border-t border-line pt-5">
                        <button
                            type="button"
                            @click="prevStep()"
                            class="inline-flex items-center gap-2 rounded-xl border border-line bg-surface-2 px-5 py-3 text-[14px] font-semibold text-ink hover:bg-surface-3 transition shadow-sm"
                        >
                            <x-icon name="chevron-left" class="size-4" />
                            <span>Sebelumnya</span>
                        </button>
                        <button
                            type="button"
                            @click="nextStep()"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand px-6 py-3 text-[14px] font-semibold text-white hover:bg-brand-hover transition shadow-sm"
                        >
                            <span>Lanjut ke Tanda Tangan & Pernyataan</span>
                            <x-icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                </div>

                <!-- Bagian 8: Pernyataan & Tanda Tangan -->
                <div x-show="step === 8" x-ref="step8" x-cloak class="rounded-2xl sm:rounded-3xl border border-line bg-surface p-4 sm:p-6 lg:p-8 shadow-sm">
                    <div class="border-b border-line pb-4">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-brand">Bagian 8</span>
                        <h2 class="text-[18px] font-bold text-ink">8. Pernyataan & Tanda Tangan</h2>
                    </div>

                    <div class="mt-5 rounded-2xl border border-amber-500/20 bg-amber-500/5 p-4 sm:p-5 text-amber-950 dark:text-amber-200">
                        <p class="text-[12.5px] leading-relaxed">
                            Keterangan tersebut di atas saya buat dengan sebenarnya dengan penuh kesadaran dan rasa tanggung jawab.
                            Apabila di kemudian hari ternyata terdapat hal-hal yang tidak benar atau dipalsukan, maka saya bersedia menerima tindakan pemutusan hubungan kerja (PHK) seketika tanpa tuntutan ganti rugi atau pesangon apapun dari pihak perusahaan.
                        </p>

                        <div class="mt-4 grid gap-4 sm:grid-cols-3 pt-3 border-t border-amber-500/15">
                            <div>
                                <x-ui.label for="signed_city">Kota Penandatanganan <span class="text-rose-500">*</span></x-ui.label>
                                <x-ui.input id="signed_city" name="signed_city" :value="old('signed_city', $personal['birth_place'] ?? 'Jakarta')" required />
                            </div>
                            <div>
                                <x-ui.label for="signed_date">Tanggal <span class="text-rose-500">*</span></x-ui.label>
                                <x-ui.input type="date" id="signed_date" name="signed_date" :value="old('signed_date', now()->format('Y-m-d'))" required />
                            </div>
                            <div>
                                <x-ui.label for="signed_name">Nama Lengkap Pembuat <span class="text-rose-500">*</span></x-ui.label>
                                <x-ui.input id="signed_name" name="signed_name" :value="old('signed_name', $personal['full_name'] ?? '')" @input="signedNameInput = $event.target.value" required />
                            </div>
                        </div>

                        <div class="mt-6 rounded-2xl border border-line bg-surface p-3.5 sm:p-5 shadow-sm">
                            <div class="flex flex-wrap items-center justify-between gap-2.5 pb-3 border-b border-line">
                                <div>
                                    <h3 class="text-[14px] font-bold text-ink flex items-center gap-2">
                                        <x-icon name="pencil" class="size-4 text-brand" />
                                        Tanda Tangan Digital (Digital Signature) <span class="text-rose-500">*</span>
                                    </h3>
                                    <p class="text-[12px] text-subtle mt-0.5">
                                        Goreskan tanda tangan Anda di area kanvas putih di bawah (bisa pakai mouse komputer atau jari di smartphone/layar sentuh).
                                    </p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <template x-if="hasSignature">
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-2.5 py-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                            <x-icon name="check-circle" class="size-3.5" /> Tanda tangan siap
                                        </span>
                                    </template>
                                    <template x-if="!hasSignature">
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/10 px-2.5 py-1 text-[11px] font-semibold text-amber-600 dark:text-amber-400">
                                            <x-icon name="alert-circle" class="size-3.5" /> Belum ditandatangani
                                        </span>
                                    </template>
                                    <button
                                        type="button"
                                        @click="clearSignature()"
                                        class="inline-flex items-center gap-1.5 rounded-xl border border-line bg-surface-2 px-3 py-1.5 text-[12px] font-medium text-muted hover:text-ink transition shadow-sm"
                                        title="Hapus dan buat ulang tanda tangan"
                                    >
                                        <x-icon name="refresh" class="size-3.5" />
                                        Hapus & Ulangi
                                    </button>
                                </div>
                            </div>

                            <div class="mt-4 relative">
                                <div class="overflow-hidden rounded-2xl border-2 border-dashed border-line bg-white shadow-inner relative select-none" style="touch-action: none;">
                                    <canvas
                                        x-ref="sigCanvas"
                                        class="w-full h-48 sm:h-52 cursor-crosshair block"
                                        @mousedown="startDraw($event)"
                                        @mousemove="drawing($event)"
                                        @mouseup="endDraw()"
                                        @mouseleave="endDraw()"
                                        @touchstart="startDraw($event)"
                                        @touchmove="drawing($event)"
                                        @touchend="endDraw()"
                                    ></canvas>

                                    <div class="pointer-events-none absolute bottom-3 left-6 right-6 flex items-center justify-between border-t border-slate-200 pt-1 text-[11px] text-slate-400">
                                        <span class="flex items-center gap-1.5">
                                            <x-icon name="pencil" class="size-3 text-slate-400" />
                                            Tanda tangan di atas garis ini
                                        </span>
                                        <span class="font-semibold text-slate-600 truncate max-w-[200px]" x-text="signedNameInput || 'Nama Karyawan'"></span>
                                    </div>
                                </div>

                                <input type="hidden" name="digital_signature" :value="signatureData">
                            </div>

                            <p class="mt-2 text-[11px] text-subtle">
                                Pastikan tanda tangan yang Anda buat jelas dan sesuai dengan tanda tangan identitas resmi Anda (KTP).
                            </p>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center justify-between gap-3 border-t border-line pt-5">
                        <button
                            type="button"
                            @click="prevStep()"
                            class="inline-flex items-center gap-2 rounded-xl border border-line bg-surface-2 px-5 py-3 text-[14px] font-semibold text-ink hover:bg-surface-3 transition shadow-sm"
                        >
                            <x-icon name="chevron-left" class="size-4" />
                            <span>Sebelumnya</span>
                        </button>
                        <x-ui.button type="submit" variant="primary" icon="send" class="w-full sm:w-auto text-[15px] sm:text-[14px] px-6 py-3.5 min-h-[48px]">
                            Kirim Formulir Biodata Karyawan
                        </x-ui.button>
                    </div>
                </div>
            </form>
        @endif
    </div>
</x-layouts.public>