<x-layouts.public :title="'Job Application Form · ' . $jobOpening->title">
    <x-slot:hero>
        <section class="relative isolate overflow-hidden bg-[#06231b] text-white">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,rgba(217,180,106,0.18),transparent_55%)]"></div>
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_right,rgba(16,185,129,0.12),transparent_55%)]"></div>
            <div class="pattern-grid pointer-events-none absolute inset-0 opacity-20"></div>

            <div class="relative mx-auto w-full max-w-4xl px-4 pb-10 pt-8 sm:px-6 sm:pb-12 sm:pt-10">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <span class="inline-flex items-center gap-2 rounded-full border border-gold/40 bg-gold/10 px-3.5 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-gold">
                        CONFIDENTIAL · RAHASIA
                    </span>
                    <span class="text-[12px] text-white/60">
                        {{ $jobOpening->company?->name ?? config('app.name') }}
                    </span>
                </div>

                <h1 class="mt-4 font-display text-[28px] font-semibold leading-tight text-white sm:text-[38px]">
                    Job Application Form
                </h1>
                <p class="mt-1 font-display text-[16px] text-gold/90 sm:text-[18px]">
                    Formulir Lamaran Kerja
                </p>

                <p class="mt-4 max-w-2xl text-[12.5px] leading-relaxed text-white/75 sm:text-[13px]">
                    <span class="font-medium text-white">CONFIDENTIAL:</span> Informasi dalam formulir ini akan diperlakukan dengan sangat rahasia dan tidak akan diungkapkan kepada pihak luar tanpa izin Anda.
                    <span class="block mt-1 italic text-white/50 text-[11.5px]">
                        The information included in this form will be treated in the strictest confidence and will not be revealed to any outside party without your permission.
                    </span>
                </p>

                <div class="mt-6 flex flex-wrap items-center gap-4 border-t border-white/10 pt-4 text-[12px] text-white/70">
                    <div>
                        <span class="text-white/40">Posisi:</span>
                        <strong class="ml-1 text-white">{{ $jobOpening->title }}</strong>
                    </div>
                    <div>·</div>
                    <div>
                        <span class="text-white/40">Kandidat:</span>
                        <strong class="ml-1 text-white">{{ $candidate->name }}</strong>
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
                        <h3 class="text-[15px] font-bold">Formulir Berhasil Dikirim!</h3>
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

        @if ($form->isSubmitted())
            <div class="rounded-3xl border border-line bg-surface p-8 text-center shadow-sm">
                <div class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400">
                    <x-icon name="check-circle" class="size-8" />
                </div>
                <h2 class="mt-4 text-[20px] font-bold text-ink">Formulir Lamaran Kerja Telah Dikirim</h2>
                <p class="mx-auto mt-2 max-w-md text-[13.5px] text-muted">
                    Terima kasih telah mengisi formulir lamaran kerja secara lengkap. Tim HRD kami akan meninjau data Anda dan menghubungi Anda via WhatsApp / Email untuk tahapan berikutnya.
                </p>
                <div class="mt-6 inline-flex items-center gap-2 rounded-xl border border-line bg-surface-2 px-4 py-2 text-[12.5px] font-medium text-subtle">
                    <x-icon name="clock" class="size-4" />
                    Diserahkan pada {{ $form->submitted_at?->translatedFormat('d F Y · H:i') ?? '—' }} WIB
                </div>
            </div>
        @else
            <form
                method="POST"
                action="{{ route('application-forms.store', $form->token) }}"
                enctype="multipart/form-data"
                class="space-y-8"
                x-data="{
                    maritalStatus: @js(old('marital_status', $candidate->marital_status ?? 'single')),
                    hasSpouse: false,
                    children: @js(old('children', [
                        ['name' => '', 'dob' => '', 'gender' => ''],
                    ])),
                    siblings: @js(old('siblings', [
                        ['name' => '', 'job' => ''],
                    ])),
                    careers: @js(old('careers', [
                        ['company' => '', 'position' => '', 'period_from' => '', 'period_to' => '', 'report_to' => '', 'responsibilities' => '', 'salary_start' => '', 'salary_end' => '', 'reason_leaving' => '']
                    ])),
                    addChild() {
                        this.children.push({ name: '', dob: '', gender: '' });
                    },
                    removeChild(index) {
                        if (this.children.length > 1) {
                            this.children.splice(index, 1);
                        }
                    },
                    addSibling() {
                        this.siblings.push({ name: '', job: '' });
                    },
                    removeSibling(index) {
                        if (this.siblings.length > 1) {
                            this.siblings.splice(index, 1);
                        }
                    },
                    addCareer() {
                        this.careers.push({ company: '', position: '', period_from: '', period_to: '', report_to: '', responsibilities: '', salary_start: '', salary_end: '', reason_leaving: '' });
                    },
                    removeCareer(index) {
                        if (this.careers.length > 1) {
                            this.careers.splice(index, 1);
                        }
                    }
                }"
            >
                @csrf

                <div class="rounded-2xl sm:rounded-3xl border border-line bg-surface p-4 sm:p-6 lg:p-8 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line pb-4">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-brand">Bagian 1</span>
                            <h2 class="text-[17px] font-bold text-ink">Posisi & Foto Pelamar / Applicant Photograph</h2>
                        </div>
                        <span class="rounded-full bg-surface-2 px-3 py-1 text-[11px] font-semibold text-subtle">Langkah 1 dari 6</span>
                    </div>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div>
                            <x-ui.label for="position_applied">Posisi yang Dilamar / Position applied <span class="text-rose-500">*</span></x-ui.label>
                            <x-ui.input id="position_applied" name="position_applied" :value="old('position_applied', $jobOpening->title)" required readonly class="bg-surface-2" />
                        </div>

                        <div>
                            <x-ui.label for="applied_date">Tanggal Pengisian / Date <span class="text-rose-500">*</span></x-ui.label>
                            <x-ui.input type="date" id="applied_date" name="applied_date" :value="old('applied_date', now()->format('Y-m-d'))" required />
                        </div>

                        <div class="sm:col-span-2">
                            <x-ui.label for="photo">Foto Pelamar / Applicant Photograph (Format Pas Foto, maks 2MB)</x-ui.label>
                            <div class="mt-1 flex items-center gap-4">
                                <input type="file" id="photo" name="photo" accept="image/*" class="block w-full text-[14px] sm:text-[13px] text-muted file:mr-4 file:rounded-xl file:border-0 file:bg-brand/10 file:px-4 file:py-2.5 file:text-[13px] file:font-semibold file:text-brand hover:file:bg-brand/20" />
                            </div>
                            <p class="mt-1.5 text-[11.5px] text-subtle">Gunakan foto formal setengah badan dengan latar polos.</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl sm:rounded-3xl border border-line bg-surface p-4 sm:p-6 lg:p-8 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line pb-4">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-brand">Bagian 2</span>
                            <h2 class="text-[17px] font-bold text-ink">Identitas Pribadi & Kondisi Kesehatan</h2>
                            <p class="text-[12px] text-muted">Personal Particulars & Present Health</p>
                        </div>
                        <span class="rounded-full bg-surface-2 px-3 py-1 text-[11px] font-semibold text-subtle">Langkah 2 dari 6</span>
                    </div>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="sm:col-span-2">
                            <x-ui.label for="name">Nama Lengkap / Full Name <span class="text-rose-500">*</span></x-ui.label>
                            <x-ui.input id="name" name="name" :value="old('name', $candidate->name)" required />
                        </div>

                        <div>
                            <x-ui.label for="alias">Nama Panggilan / Alias (If any)</x-ui.label>
                            <x-ui.input id="alias" name="alias" :value="old('alias')" placeholder="Contoh: Budi" />
                        </div>

                        <div class="sm:col-span-2 lg:col-span-3">
                            <x-ui.label for="address">Alamat Tinggal / Address <span class="text-rose-500">*</span></x-ui.label>
                            <x-ui.textarea id="address" name="address" :rows="2" required placeholder="Alamat domisili lengkap beserta RT/RW, Kelurahan, Kecamatan">{{ old('address', $candidate->address) }}</x-ui.textarea>
                        </div>

                        <div>
                            <x-ui.label for="email">Alamat Email / Email Address <span class="text-rose-500">*</span></x-ui.label>
                            <x-ui.input type="email" id="email" name="email" :value="old('email', $candidate->email)" required />
                        </div>

                        <div>
                            <x-ui.label for="mobile_phone">No. Handphone / Mobile Phone <span class="text-rose-500">*</span></x-ui.label>
                            <x-ui.input id="mobile_phone" name="mobile_phone" :value="old('mobile_phone', $candidate->phone)" required />
                        </div>

                        <div>
                            <x-ui.label for="home_phone">Telp Rumah / Home Tel</x-ui.label>
                            <x-ui.input id="home_phone" name="home_phone" :value="old('home_phone')" placeholder="Opsional" />
                        </div>

                        <div>
                            <x-ui.label for="gender">Jenis Kelamin / Sex <span class="text-rose-500">*</span></x-ui.label>
                            <select id="gender" name="gender" required class="h-11 w-full rounded-xl border border-line bg-surface px-3.5 text-[16px] sm:text-[14px] text-ink outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/15">
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="pria" @selected(old('gender', $candidate->gender) === 'pria' || old('gender', $candidate->gender) === 'L' || old('gender', $candidate->gender) === 'M')>Laki-laki / Male (M)</option>
                                <option value="wanita" @selected(old('gender', $candidate->gender) === 'wanita' || old('gender', $candidate->gender) === 'P' || old('gender', $candidate->gender) === 'F')>Perempuan / Female (F)</option>
                            </select>
                        </div>

                        <div>
                            <x-ui.label for="race">Suku / Race</x-ui.label>
                            <x-ui.input id="race" name="race" :value="old('race')" placeholder="Contoh: Jawa, Batak, Sunda, dll" />
                        </div>

                        <div>
                            <x-ui.label for="age">Usia / Age</x-ui.label>
                            <x-ui.input type="number" id="age" name="age" :value="old('age')" placeholder="Tahun" />
                        </div>

                        <div>
                            <x-ui.label for="religion">Agama / Religion <span class="text-rose-500">*</span></x-ui.label>
                            <select id="religion" name="religion" required class="h-11 w-full rounded-xl border border-line bg-surface px-3.5 text-[16px] sm:text-[14px] text-ink outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/15">
                                <option value="">Pilih Agama</option>
                                <option value="Islam" @selected(old('religion') === 'Islam')>Islam</option>
                                <option value="Kristen Protestan" @selected(old('religion') === 'Kristen Protestan')>Kristen Protestan</option>
                                <option value="Katolik" @selected(old('religion') === 'Katolik')>Katolik</option>
                                <option value="Hindu" @selected(old('religion') === 'Hindu')>Hindu</option>
                                <option value="Buddha" @selected(old('religion') === 'Buddha')>Buddha</option>
                                <option value="Khonghucu" @selected(old('religion') === 'Khonghucu')>Khonghucu</option>
                            </select>
                        </div>

                        <div>
                            <x-ui.label for="marital_status">Status Pernikahan / Marital Status <span class="text-rose-500">*</span></x-ui.label>
                            <select id="marital_status" name="marital_status" x-model="maritalStatus" required class="h-11 w-full rounded-xl border border-line bg-surface px-3.5 text-[16px] sm:text-[14px] text-ink outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/15">
                                <option value="Belum Menikah">Belum Menikah / Single</option>
                                <option value="Menikah">Menikah / Married</option>
                                <option value="Duda / Janda">Duda / Janda / Divorced / Widowed</option>
                            </select>
                        </div>

                        <div>
                            <x-ui.label for="birth_place">Tempat Lahir / Place of Birth <span class="text-rose-500">*</span></x-ui.label>
                            <x-ui.input id="birth_place" name="birth_place" :value="old('birth_place', $candidate->city)" required />
                        </div>

                        <div>
                            <x-ui.label for="birth_date">Tanggal Lahir / Date of Birth <span class="text-rose-500">*</span></x-ui.label>
                            <x-ui.input type="date" id="birth_date" name="birth_date" :value="old('birth_date')" required />
                        </div>

                        <div>
                            <x-ui.label for="ktp_no">No. KTP / NIRC <span class="text-rose-500">*</span></x-ui.label>
                            <x-ui.input id="ktp_no" name="ktp_no" :value="old('ktp_no')" required placeholder="16 digit NIK KTP" maxlength="16" />
                        </div>

                        <div>
                            <x-ui.label for="nationality">Kewarganegaraan / Nationality</x-ui.label>
                            <x-ui.input id="nationality" name="nationality" :value="old('nationality', 'WNI / Indonesia')" />
                        </div>

                        <div>
                            <x-ui.label for="passport_no">Passport No (Jika ada)</x-ui.label>
                            <x-ui.input id="passport_no" name="passport_no" :value="old('passport_no')" placeholder="Opsional" />
                        </div>
                    </div>

                    <div class="mt-8 border-t border-line pt-6">
                        <h3 class="text-[14px] font-bold text-ink">Kondisi Kesehatan / Present Health</h3>
                        <div class="mt-4 grid gap-5 sm:grid-cols-2">
                            <div>
                                <x-ui.label for="serious_illness">Apakah Anda mengidap penyakit serius? / Do you have serious illness?</x-ui.label>
                                <x-ui.input id="serious_illness" name="serious_illness" :value="old('serious_illness', 'Tidak ada')" placeholder="Tulis 'Tidak ada' atau sebutkan" />
                            </div>

                            <div>
                                <x-ui.label for="disabilities">Apakah Anda mempunyai cacat fisik? / Physical disabilities?</x-ui.label>
                                <x-ui.input id="disabilities" name="disabilities" :value="old('disabilities', 'Tidak ada')" placeholder="Tulis 'Tidak ada' atau sebutkan" />
                            </div>

                            <div class="sm:col-span-2">
                                <x-ui.label for="present_health">Kondisi kesehatan saat ini / Present Health <span class="text-rose-500">*</span></x-ui.label>
                                <x-ui.input id="present_health" name="present_health" :value="old('present_health', 'Sehat walafiat')" required />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl sm:rounded-3xl border border-line bg-surface p-4 sm:p-6 lg:p-8 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line pb-4">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-brand">Bagian 3</span>
                            <h2 class="text-[17px] font-bold text-ink">Data Keluarga / Family Particulars</h2>
                            <p class="text-[12px] text-muted">Pasangan, Anak, Orang Tua, dan Saudara Kandung</p>
                        </div>
                        <span class="rounded-full bg-surface-2 px-3 py-1 text-[11px] font-semibold text-subtle">Langkah 3 dari 6</span>
                    </div>

                    <div class="mt-6 rounded-2xl border border-line/80 bg-surface-2/40 p-5">
                        <h3 class="text-[14px] font-bold text-ink">Data Pasangan (Istri / Suami) - Kosongkan bila belum menikah</h3>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <x-ui.label for="spouse_name">Nama Pasangan / Spouse's Name</x-ui.label>
                                <x-ui.input id="spouse_name" name="spouse[name]" :value="old('spouse.name')" placeholder="Nama lengkap pasangan" />
                            </div>
                            <div>
                                <x-ui.label for="spouse_occupation">Pekerjaan / Occupation</x-ui.label>
                                <x-ui.input id="spouse_occupation" name="spouse[occupation]" :value="old('spouse.occupation')" />
                            </div>
                            <div>
                                <x-ui.label for="spouse_dob">Tanggal Lahir / DOB</x-ui.label>
                                <x-ui.input type="date" id="spouse_dob" name="spouse[dob]" :value="old('spouse.dob')" />
                            </div>
                            <div>
                                <x-ui.label for="spouse_nationality">Kewarganegaraan / Nationality</x-ui.label>
                                <x-ui.input id="spouse_nationality" name="spouse[nationality]" :value="old('spouse.nationality', 'Indonesia')" />
                            </div>
                            <div>
                                <x-ui.label for="spouse_ktp">No KTP / NIRC</x-ui.label>
                                <x-ui.input id="spouse_ktp" name="spouse[ktp_no]" :value="old('spouse.ktp_no')" />
                            </div>
                            <div>
                                <x-ui.label for="marriage_date">Tanggal Pernikahan / Marriage Date</x-ui.label>
                                <x-ui.input type="date" id="marriage_date" name="spouse[marriage_date]" :value="old('spouse.marriage_date')" />
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-line pt-6">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <h3 class="text-[14px] font-bold text-ink">Nama Anak / Children's Name (Jika ada)</h3>
                                <p class="text-[12px] text-muted">Isi jika Anda sudah memiliki anak</p>
                            </div>
                            <button type="button" x-on:click="addChild()" class="inline-flex items-center gap-1.5 rounded-xl border border-brand/30 bg-brand/10 px-3 py-1.5 text-[12px] font-semibold text-brand transition hover:bg-brand/20">
                                <x-icon name="plus" class="size-3.5" />
                                Tambah Anak
                            </button>
                        </div>

                        <div class="mt-4 space-y-3">
                            <template x-for="(child, index) in children" :key="index">
                                <div class="rounded-2xl border border-line bg-surface p-3.5 sm:p-4 space-y-2.5 shadow-sm">
                                    <div class="flex items-center justify-between border-b border-line/60 pb-2">
                                        <span class="text-[12px] font-bold text-ink" x-text="'Anak #' + (index + 1)"></span>
                                        <button type="button" x-on:click="removeChild(index)" class="inline-flex items-center gap-1 text-[12px] font-medium text-rose-500 hover:text-rose-700 p-1" title="Hapus baris">
                                            <x-icon name="trash" class="size-3.5" /> Hapus
                                        </button>
                                    </div>
                                    <div class="grid gap-2.5 sm:grid-cols-3">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1 sm:hidden">Nama Anak</label>
                                            <input type="text" :name="'children[' + index + '][name]'" x-model="child.name" placeholder="Nama Anak" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/15" />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1 sm:hidden">Tempat & Tgl Lahir</label>
                                            <input type="text" :name="'children[' + index + '][dob]'" x-model="child.dob" placeholder="Tempat & Tgl Lahir" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/15" />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1 sm:hidden">Jenis Kelamin</label>
                                            <select :name="'children[' + index + '][gender]'" x-model="child.gender" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/15">
                                                <option value="">Jenis Kelamin</option>
                                                <option value="L">Laki-laki (L)</option>
                                                <option value="P">Perempuan (P)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-line pt-6">
                        <h3 class="text-[14px] font-bold text-ink">Nama Orang Tua (Ayah & Ibu) <span class="text-rose-500">*</span></h3>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-line/80 bg-surface-2/30 p-4">
                                <h4 class="text-[13px] font-semibold text-ink">Ayah / Father</h4>
                                <div class="mt-3 space-y-3">
                                    <div>
                                        <x-ui.label for="father_name">Nama Ayah <span class="text-rose-500">*</span></x-ui.label>
                                        <x-ui.input id="father_name" name="father[name]" :value="old('father.name')" required />
                                    </div>
                                    <div>
                                        <x-ui.label for="father_occupation">Pekerjaan Ayah</x-ui.label>
                                        <x-ui.input id="father_occupation" name="father[occupation]" :value="old('father.occupation')" />
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-line/80 bg-surface-2/30 p-4">
                                <h4 class="text-[13px] font-semibold text-ink">Ibu / Mother</h4>
                                <div class="mt-3 space-y-3">
                                    <div>
                                        <x-ui.label for="mother_name">Nama Ibu <span class="text-rose-500">*</span></x-ui.label>
                                        <x-ui.input id="mother_name" name="mother[name]" :value="old('mother.name')" required />
                                    </div>
                                    <div>
                                        <x-ui.label for="mother_occupation">Pekerjaan Ibu</x-ui.label>
                                        <x-ui.input id="mother_occupation" name="mother[occupation]" :value="old('mother.occupation')" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-line pt-6">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <h3 class="text-[14px] font-bold text-ink">Nama Saudara Kandung / Sibling's Name</h3>
                                <p class="text-[12px] text-muted">Kakak / Adik kandung</p>
                            </div>
                            <button type="button" x-on:click="addSibling()" class="inline-flex items-center gap-1.5 rounded-xl border border-brand/30 bg-brand/10 px-3 py-1.5 text-[12px] font-semibold text-brand transition hover:bg-brand/20">
                                <x-icon name="plus" class="size-3.5" />
                                Tambah Saudara
                            </button>
                        </div>

                        <div class="mt-4 space-y-3">
                            <template x-for="(sibling, index) in siblings" :key="index">
                                <div class="rounded-2xl border border-line bg-surface p-3.5 sm:p-4 space-y-2.5 shadow-sm">
                                    <div class="flex items-center justify-between border-b border-line/60 pb-2">
                                        <span class="text-[12px] font-bold text-ink" x-text="'Saudara #' + (index + 1)"></span>
                                        <button type="button" x-on:click="removeSibling(index)" class="inline-flex items-center gap-1 text-[12px] font-medium text-rose-500 hover:text-rose-700 p-1" title="Hapus baris">
                                            <x-icon name="trash" class="size-3.5" /> Hapus
                                        </button>
                                    </div>
                                    <div class="grid gap-2.5 sm:grid-cols-2">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1 sm:hidden">Nama Saudara</label>
                                            <input type="text" :name="'siblings[' + index + '][name]'" x-model="sibling.name" placeholder="Nama Saudara" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/15" />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1 sm:hidden">Pekerjaan</label>
                                            <input type="text" :name="'siblings[' + index + '][job]'" x-model="sibling.job" placeholder="Pekerjaan" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/15" />
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-line pt-6">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <x-ui.label for="expected_salary">Gaji Minimal yang Diharapkan (Rp) <span class="text-rose-500">*</span></x-ui.label>
                                <x-ui.input type="number" id="expected_salary" name="expected_salary" :value="old('expected_salary')" placeholder="Contoh: 5000000" required />
                            </div>

                            <div>
                                <x-ui.label for="notice_period">Kapan Dapat Bergabung / Period of Notice <span class="text-rose-500">*</span></x-ui.label>
                                <x-ui.input id="notice_period" name="notice_period" :value="old('notice_period', 'Segera / 1 Bulan')" required placeholder="Contoh: Segera / 2 Minggu / 1 Bulan" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl sm:rounded-3xl border border-line bg-surface p-4 sm:p-6 lg:p-8 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line pb-4">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-brand">Bagian 4</span>
                            <h2 class="text-[17px] font-bold text-ink">Pendidikan, Bahasa & Keahlian</h2>
                            <p class="text-[12px] text-muted">Education, Language Proficiency & Skills</p>
                        </div>
                        <span class="rounded-full bg-surface-2 px-3 py-1 text-[11px] font-semibold text-subtle">Langkah 4 dari 6</span>
                    </div>

                    <div class="mt-6 space-y-4">
                        <h3 class="text-[14px] font-bold text-ink">Riwayat Pendidikan Formal</h3>

                        <div class="rounded-2xl border border-line/80 bg-surface-2/30 p-4">
                            <h4 class="text-[13px] font-semibold text-ink">SMA / SMK / Senior High School <span class="text-rose-500">*</span></h4>
                            <div class="mt-3 grid gap-3 sm:grid-cols-4">
                                <div class="sm:col-span-2">
                                    <x-ui.label for="edu_sma_name">Nama Sekolah</x-ui.label>
                                    <x-ui.input id="edu_sma_name" name="education[sma][name]" :value="old('education.sma.name')" required />
                                </div>
                                <div>
                                    <x-ui.label for="edu_sma_from">Tahun (Dari - Sampai)</x-ui.label>
                                    <div class="flex items-center gap-1.5">
                                        <input type="text" name="education[sma][from]" value="{{ old('education.sma.from') }}" placeholder="2015" class="h-10 w-full rounded-xl border border-line bg-surface px-2.5 text-center text-[13px] outline-none focus:border-brand" />
                                        <span>-</span>
                                        <input type="text" name="education[sma][to]" value="{{ old('education.sma.to') }}" placeholder="2018" class="h-10 w-full rounded-xl border border-line bg-surface px-2.5 text-center text-[13px] outline-none focus:border-brand" />
                                    </div>
                                </div>
                                <div>
                                    <x-ui.label for="edu_sma_achievement">Jurusan / Nilai</x-ui.label>
                                    <x-ui.input id="edu_sma_achievement" name="education[sma][achievement]" :value="old('education.sma.achievement')" placeholder="IPA / IPS / Nilai" />
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-line/80 bg-surface-2/30 p-4">
                            <h4 class="text-[13px] font-semibold text-ink">Universitas / Diploma / University</h4>
                            <div class="mt-3 grid gap-3 sm:grid-cols-4">
                                <div class="sm:col-span-2">
                                    <x-ui.label for="edu_univ_name">Nama Universitas & Jurusan</x-ui.label>
                                    <x-ui.input id="edu_univ_name" name="education[university][name]" :value="old('education.university.name')" placeholder="Contoh: Universitas Indonesia - S1 Manajemen" />
                                </div>
                                <div>
                                    <x-ui.label for="edu_univ_from">Tahun (Dari - Sampai)</x-ui.label>
                                    <div class="flex items-center gap-1.5">
                                        <input type="text" name="education[university][from]" value="{{ old('education.university.from') }}" placeholder="2018" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-2.5 text-center text-[16px] sm:text-[13px] outline-none focus:border-brand" />
                                        <span>-</span>
                                        <input type="text" name="education[university][to]" value="{{ old('education.university.to') }}" placeholder="2022" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-2.5 text-center text-[16px] sm:text-[13px] outline-none focus:border-brand" />
                                    </div>
                                </div>
                                <div>
                                    <x-ui.label for="edu_univ_achievement">IPK / Pencapaian</x-ui.label>
                                    <x-ui.input id="edu_univ_achievement" name="education[university][achievement]" :value="old('education.university.achievement')" placeholder="Contoh: 3.65 (Cumlaude)" />
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-line/80 bg-surface-2/30 p-4">
                            <h4 class="text-[13px] font-semibold text-ink">Kursus / Pelatihan / Lain-lain (Jika ada)</h4>
                            <div class="mt-3 grid gap-3 sm:grid-cols-4">
                                <div class="sm:col-span-2">
                                    <x-ui.label for="edu_course_name">Nama Institusi / Kursus</x-ui.label>
                                    <x-ui.input id="edu_course_name" name="education[courses][name]" :value="old('education.courses.name')" placeholder="Nama kursus / sertifikasi" />
                                </div>
                                <div>
                                    <x-ui.label for="edu_course_from">Tahun (Dari - Sampai)</x-ui.label>
                                    <div class="flex items-center gap-1.5">
                                        <input type="text" name="education[courses][from]" value="{{ old('education.courses.from') }}" placeholder="2023" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-2.5 text-center text-[16px] sm:text-[13px] outline-none focus:border-brand" />
                                        <span>-</span>
                                        <input type="text" name="education[courses][to]" value="{{ old('education.courses.to') }}" placeholder="2023" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-2.5 text-center text-[16px] sm:text-[13px] outline-none focus:border-brand" />
                                    </div>
                                </div>
                                <div>
                                    <x-ui.label for="edu_course_achievement">Sertifikat / Hasil</x-ui.label>
                                    <x-ui.input id="edu_course_achievement" name="education[courses][achievement]" :value="old('education.courses.achievement')" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-line pt-6">
                        <h3 class="text-[14px] font-bold text-ink">Penguasaan Bahasa / Language Proficiency</h3>
                        <div class="mt-3 overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0">
                            <table class="w-full min-w-[460px] text-left text-[13px]">
                                <thead>
                                    <tr class="border-b border-line text-[11px] uppercase tracking-wider text-subtle">
                                        <th class="py-2">Bahasa</th>
                                        <th class="py-2">Bicara / Spoken</th>
                                        <th class="py-2">Menulis / Written</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line/60">
                                    <tr>
                                        <td class="py-2 font-medium">Bahasa Indonesia</td>
                                        <td class="py-2">
                                            <select name="languages[0][spoken]" class="h-11 sm:h-9 w-full rounded-xl sm:rounded-lg border border-line bg-surface px-2.5 text-[16px] sm:text-[12.5px]">
                                                <option value="Fluent" selected>Fluent (Lancar)</option>
                                                <option value="Fair">Fair (Cukup)</option>
                                                <option value="Poor">Poor (Kurang)</option>
                                            </select>
                                            <input type="hidden" name="languages[0][lang]" value="Bahasa Indonesia" />
                                        </td>
                                        <td class="py-2">
                                            <select name="languages[0][written]" class="h-11 sm:h-9 w-full rounded-xl sm:rounded-lg border border-line bg-surface px-2.5 text-[16px] sm:text-[12.5px]">
                                                <option value="Fluent" selected>Fluent (Lancar)</option>
                                                <option value="Fair">Fair (Cukup)</option>
                                                <option value="Poor">Poor (Kurang)</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 font-medium">Bahasa Inggris</td>
                                        <td class="py-2">
                                            <select name="languages[1][spoken]" class="h-11 sm:h-9 w-full rounded-xl sm:rounded-lg border border-line bg-surface px-2.5 text-[16px] sm:text-[12.5px]">
                                                <option value="Fluent">Fluent (Lancar)</option>
                                                <option value="Fair" selected>Fair (Cukup)</option>
                                                <option value="Poor">Poor (Kurang)</option>
                                            </select>
                                            <input type="hidden" name="languages[1][lang]" value="Bahasa Inggris" />
                                        </td>
                                        <td class="py-2">
                                            <select name="languages[1][written]" class="h-11 sm:h-9 w-full rounded-xl sm:rounded-lg border border-line bg-surface px-2.5 text-[16px] sm:text-[12.5px]">
                                                <option value="Fluent">Fluent (Lancar)</option>
                                                <option value="Fair" selected>Fair (Cukup)</option>
                                                <option value="Poor">Poor (Kurang)</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2">
                                            <input type="text" name="languages[2][lang]" placeholder="Bahasa lain (Mandarin/Jepang)" class="h-11 sm:h-9 w-full rounded-xl sm:rounded-lg border border-line bg-surface px-2.5 text-[16px] sm:text-[12.5px]" />
                                        </td>
                                        <td class="py-2">
                                            <select name="languages[2][spoken]" class="h-11 sm:h-9 w-full rounded-xl sm:rounded-lg border border-line bg-surface px-2.5 text-[16px] sm:text-[12.5px]">
                                                <option value="Poor">Poor (Kurang)</option>
                                                <option value="Fair">Fair (Cukup)</option>
                                                <option value="Fluent">Fluent (Lancar)</option>
                                            </select>
                                        </td>
                                        <td class="py-2">
                                            <select name="languages[2][written]" class="h-11 sm:h-9 w-full rounded-xl sm:rounded-lg border border-line bg-surface px-2.5 text-[16px] sm:text-[12.5px]">
                                                <option value="Poor">Poor (Kurang)</option>
                                                <option value="Fair">Fair (Cukup)</option>
                                                <option value="Fluent">Fluent (Lancar)</option>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-line pt-6">
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <h3 class="text-[14px] font-bold text-ink">Sebutkan 3 Kelebihan Anda <span class="text-rose-500">*</span></h3>
                                <p class="text-[12px] text-muted mb-3">Please list three of your strengths</p>
                                <div class="space-y-2">
                                    <x-ui.input name="strengths[]" placeholder="1. Kelebihan pertama" required :value="old('strengths.0')" />
                                    <x-ui.input name="strengths[]" placeholder="2. Kelebihan kedua" required :value="old('strengths.1')" />
                                    <x-ui.input name="strengths[]" placeholder="3. Kelebihan ketiga" required :value="old('strengths.2')" />
                                </div>
                            </div>

                            <div>
                                <h3 class="text-[14px] font-bold text-ink">Sebutkan 3 Kekurangan Anda <span class="text-rose-500">*</span></h3>
                                <p class="text-[12px] text-muted mb-3">Please list three of your weaknesses</p>
                                <div class="space-y-2">
                                    <x-ui.input name="weaknesses[]" placeholder="1. Kekurangan pertama" required :value="old('weaknesses.0')" />
                                    <x-ui.input name="weaknesses[]" placeholder="2. Kekurangan kedua" required :value="old('weaknesses.1')" />
                                    <x-ui.input name="weaknesses[]" placeholder="3. Kekurangan ketiga" required :value="old('weaknesses.2')" />
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <x-ui.label for="computer_skills">Penguasaan Komputer & Software / Computer Skills <span class="text-rose-500">*</span></x-ui.label>
                            <x-ui.textarea id="computer_skills" name="computer_skills" :rows="2" required placeholder="Sebutkan software / aplikasi yang Anda kuasai (contoh: Microsoft Office, Google Workspace, SAP, Photoshop, dll)">{{ old('computer_skills') }}</x-ui.textarea>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl sm:rounded-3xl border border-line bg-surface p-4 sm:p-6 lg:p-8 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line pb-4">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-brand">Bagian 5</span>
                            <h2 class="text-[17px] font-bold text-ink">Riwayat Pekerjaan / Career History</h2>
                            <p class="text-[12px] text-muted">Dimulai dengan posisi pertama sampai posisi sekarang</p>
                        </div>
                        <span class="rounded-full bg-surface-2 px-3 py-1 text-[11px] font-semibold text-subtle">Langkah 5 dari 6</span>
                    </div>

                    <div class="mt-6">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h3 class="text-[14px] font-bold text-ink">Pengalaman Kerja Sebelumnya</h3>
                            <button type="button" x-on:click="addCareer()" class="inline-flex items-center gap-1.5 rounded-xl border border-brand/30 bg-brand/10 px-3 py-1.5 text-[12px] font-semibold text-brand transition hover:bg-brand/20">
                                <x-icon name="plus" class="size-3.5" />
                                Tambah Pengalaman
                            </button>
                        </div>

                        <div class="mt-4 space-y-4">
                            <template x-for="(c, idx) in careers" :key="idx">
                                <div class="rounded-2xl border border-line bg-surface-2/30 p-3.5 sm:p-4 space-y-3">
                                    <div class="flex items-center justify-between border-b border-line/60 pb-2">
                                        <span class="text-[12.5px] font-bold text-ink" x-text="'Perusahaan #' + (idx + 1)"></span>
                                        <button type="button" x-on:click="removeCareer(idx)" class="inline-flex items-center gap-1 text-[12px] text-rose-500 hover:text-rose-700">
                                            <x-icon name="trash" class="size-3.5" /> Hapus
                                        </button>
                                    </div>
                                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Nama Perusahaan & Bidang</label>
                                            <input type="text" :name="'careers[' + idx + '][company]'" x-model="c.company" placeholder="PT ABC (Retail)" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none focus:border-brand" />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Jabatan / Posisi</label>
                                            <input type="text" :name="'careers[' + idx + '][position]'" x-model="c.position" placeholder="Staff Operasional" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none focus:border-brand" />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Melapor Kepada</label>
                                            <input type="text" :name="'careers[' + idx + '][report_to]'" x-model="c.report_to" placeholder="Manager" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none focus:border-brand" />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Periode (Dari - Sampai)</label>
                                            <div class="flex items-center gap-1.5">
                                                <input type="text" :name="'careers[' + idx + '][period_from]'" x-model="c.period_from" placeholder="2020" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-2.5 text-center text-[16px] sm:text-[13px]" />
                                                <span>-</span>
                                                <input type="text" :name="'careers[' + idx + '][period_to]'" x-model="c.period_to" placeholder="2023" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-2.5 text-center text-[16px] sm:text-[13px]" />
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Gaji Terakhir (Rp)</label>
                                            <input type="text" :name="'careers[' + idx + '][salary_end]'" x-model="c.salary_end" placeholder="Contoh: 4.500.000" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none focus:border-brand" />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Alasan Berhenti</label>
                                            <input type="text" :name="'careers[' + idx + '][reason_leaving]'" x-model="c.reason_leaving" placeholder="Kontrak selesai / Karir" class="h-11 sm:h-10 w-full rounded-xl border border-line bg-surface px-3 text-[16px] sm:text-[13.5px] text-ink outline-none focus:border-brand" />
                                        </div>
                                        <div class="sm:col-span-2 lg:col-span-3">
                                            <label class="block text-[11px] font-semibold text-subtle mb-1">Tanggung Jawab & Pencapaian Utama</label>
                                            <textarea :name="'careers[' + idx + '][responsibilities]'" x-model="c.responsibilities" rows="2" placeholder="Uraikan tugas pokok Anda" class="w-full rounded-xl border border-line bg-surface p-2.5 text-[16px] sm:text-[13.5px] text-ink outline-none focus:border-brand"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="mt-6 border-t border-line pt-6">
                            <x-ui.label for="current_benefits">Benefit / Insentif (Bonus) yang didapatkan di Perusahaan Sekarang</x-ui.label>
                            <x-ui.input id="current_benefits" name="current_benefits" :value="old('current_benefits')" placeholder="Misal: BPJS, Asuransi Swasta, Bonus Tahunan, Tunjangan Makan" />
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl sm:rounded-3xl border border-line bg-surface p-4 sm:p-6 lg:p-8 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line pb-4">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-brand">Bagian 6</span>
                            <h2 class="text-[17px] font-bold text-ink">Informasi Tambahan, Referensi & Pernyataan</h2>
                            <p class="text-[12px] text-muted">Legal Background, References & Declaration</p>
                        </div>
                        <span class="rounded-full bg-surface-2 px-3 py-1 text-[11px] font-semibold text-subtle">Langkah 6 dari 6</span>
                    </div>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div>
                            <x-ui.label for="criminal_record">Apakah Anda pernah dihukum karena tindakan pidana? <span class="text-rose-500">*</span></x-ui.label>
                            <select id="criminal_record" name="criminal_record" required class="h-11 w-full rounded-xl border border-line bg-surface px-3.5 text-[16px] sm:text-[14px] text-ink outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/15">
                                <option value="Tidak" @selected(old('criminal_record') === 'Tidak')>Tidak / No</option>
                                <option value="Ya" @selected(old('criminal_record') === 'Ya')>Ya / Yes (Jelaskan di catatan)</option>
                            </select>
                        </div>

                        <div>
                            <x-ui.label for="driving_license">Apakah Anda memiliki SIM? Sebutkan: <span class="text-rose-500">*</span></x-ui.label>
                            <x-ui.input id="driving_license" name="driving_license" :value="old('driving_license', 'SIM C')" required placeholder="Contoh: SIM C, SIM A, atau Tidak ada" />
                        </div>

                        <div>
                            <x-ui.label for="vehicle_owned">Apakah Anda memiliki kendaraan pribadi? <span class="text-rose-500">*</span></x-ui.label>
                            <x-ui.input id="vehicle_owned" name="vehicle_owned" :value="old('vehicle_owned', 'Sepeda Motor')" required placeholder="Contoh: Sepeda Motor / Mobil / Tidak ada" />
                        </div>

                        <div>
                            <x-ui.label for="other_business">Apakah Anda memiliki bisnis / usaha lain?</x-ui.label>
                            <x-ui.input id="other_business" name="other_business" :value="old('other_business', 'Tidak ada')" placeholder="Tulis 'Tidak ada' atau sebutkan nama bisnis" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-ui.label for="suitability_statement">Hal yang menurut Anda penting sebagai pertimbangan kecocokan posisi ini <span class="text-rose-500">*</span></x-ui.label>
                            <x-ui.textarea id="suitability_statement" name="suitability_statement" :rows="3" required placeholder="Tuliskan komitmen, motivasi, dan mengapa Anda paling cocok untuk posisi ini">{{ old('suitability_statement') }}</x-ui.textarea>
                        </div>
                    </div>

                    <div class="mt-8 border-t border-line pt-6">
                        <h3 class="text-[14px] font-bold text-ink">Referensi Profesional (Bukan Anggota Keluarga)</h3>
                        <p class="text-[12px] text-muted">Seseorang yang dapat memberikan keterangan tentang pendidikan atau pengalaman kerja Anda</p>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-line bg-surface-2/30 p-4 space-y-3">
                                <h4 class="text-[13px] font-semibold text-ink">Referensi #1 <span class="text-rose-500">*</span></h4>
                                <div>
                                    <x-ui.label for="ref1_name">Nama Lengkap</x-ui.label>
                                    <x-ui.input id="ref1_name" name="references[0][name]" :value="old('references.0.name')" required placeholder="Nama atasan / rekan / dosen" />
                                </div>
                                <div>
                                    <x-ui.label for="ref1_position">Jabatan & Perusahaan</x-ui.label>
                                    <x-ui.input id="ref1_position" name="references[0][position]" :value="old('references.0.position')" required placeholder="Contoh: Manager di PT ABC" />
                                </div>
                                <div>
                                    <x-ui.label for="ref1_phone">No. Handphone</x-ui.label>
                                    <x-ui.input id="ref1_phone" name="references[0][phone]" :value="old('references.0.phone')" required placeholder="0812..." />
                                </div>
                                <div>
                                    <x-ui.label for="ref1_rel">Hubungan / Kenal Sejak</x-ui.label>
                                    <x-ui.input id="ref1_rel" name="references[0][relation]" :value="old('references.0.relation')" required placeholder="Mantan Atasan Langsung (2 tahun)" />
                                </div>
                            </div>

                            <div class="rounded-2xl border border-line bg-surface-2/30 p-4 space-y-3">
                                <h4 class="text-[13px] font-semibold text-ink">Referensi #2</h4>
                                <div>
                                    <x-ui.label for="ref2_name">Nama Lengkap</x-ui.label>
                                    <x-ui.input id="ref2_name" name="references[1][name]" :value="old('references.1.name')" placeholder="Nama atasan / rekan" />
                                </div>
                                <div>
                                    <x-ui.label for="ref2_position">Jabatan & Perusahaan</x-ui.label>
                                    <x-ui.input id="ref2_position" name="references[1][position]" :value="old('references.1.position')" placeholder="Jabatan & Perusahaan" />
                                </div>
                                <div>
                                    <x-ui.label for="ref2_phone">No. Handphone</x-ui.label>
                                    <x-ui.input id="ref2_phone" name="references[1][phone]" :value="old('references.1.phone')" placeholder="0812..." />
                                </div>
                                <div>
                                    <x-ui.label for="ref2_rel">Hubungan</x-ui.label>
                                    <x-ui.input id="ref2_rel" name="references[1][relation]" :value="old('references.1.relation')" placeholder="Rekan Kerja" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 rounded-2xl border border-amber-500/20 bg-amber-500/5 p-5 text-amber-950 dark:text-amber-200">
                        <div class="flex items-start gap-3">
                            <input type="checkbox" id="declaration_check" name="declaration_check" value="1" required class="mt-1 size-4 rounded border-line text-brand focus:ring-brand" />
                            <label for="declaration_check" class="text-[12.5px] leading-relaxed cursor-pointer">
                                <strong>PERNYATAAN / DECLARATION:</strong> Saya menyatakan bahwa semua keterangan yang saya berikan dalam formulir ini adalah benar dan lengkap menurut pengetahuan saya. Apabila terdapat keterangan yang tidak benar atau dipalsukan, saya bersedia menerima sanksi pembatalan lamaran atau pemutusan hubungan kerja tanpa pesangon sesuai kebijakan perusahaan.
                            </label>
                        </div>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2 pt-3 border-t border-amber-500/15">
                            <div>
                                <x-ui.label for="applicant_signature">Nama Terang Pemohon / Applicant's Full Name <span class="text-rose-500">*</span></x-ui.label>
                                <x-ui.input id="applicant_signature" name="applicant_signature" :value="old('applicant_signature', $candidate->name)" required />
                            </div>
                            <div>
                                <x-ui.label for="signature_date">Tanggal Persetujuan <span class="text-rose-500">*</span></x-ui.label>
                                <x-ui.input type="date" id="signature_date" name="signature_date" :value="old('signature_date', now()->format('Y-m-d'))" required />
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center justify-end gap-3 border-t border-line pt-5">
                        <x-ui.button type="submit" variant="primary" icon="send" class="w-full sm:w-auto text-[15px] sm:text-[14px] px-6 py-3.5 min-h-[48px]">
                            Kirim Formulir Lamaran Kerja
                        </x-ui.button>
                    </div>
                </div>
            </form>
        @endif
    </div>
</x-layouts.public>