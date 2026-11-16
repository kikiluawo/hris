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
            </div>
        @else
            <form
                method="POST"
                action="{{ route('employee-biodatas.store', $biodata->token) }}"
                enctype="multipart/form-data"
                class="space-y-8"
                x-data="{
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
                    addRow(list) {
                        list.push({});
                    },
                    removeRow(list, index) {
                        if (list.length > 1) {
                            list.splice(index, 1);
                        }
                    }
                }"
            >
                @csrf

                <div class="rounded-3xl border border-line bg-surface p-6 shadow-sm sm:p-8">
                    <div class="flex items-center justify-between border-b border-line pb-4">
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
                            <select id="gender" name="personal[gender]" required class="h-11 w-full rounded-xl border border-line bg-surface px-3.5 text-[14px] text-ink outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/15">
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
                            <select id="religion" name="personal[religion]" required class="h-11 w-full rounded-xl border border-line bg-surface px-3.5 text-[14px] text-ink outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/15">
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
                            <select id="marital_status" name="personal[marital_status]" required class="h-11 w-full rounded-xl border border-line bg-surface px-3.5 text-[14px] text-ink outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/15">
                                <option value="Tidak Kawin" @selected(old('personal.marital_status', $personal['marital_status'] ?? '') === 'Tidak Kawin' || old('personal.marital_status', $personal['marital_status'] ?? '') === 'Belum Menikah')>Tidak Kawin / Belum Menikah</option>
                                <option value="Kawin" @selected(old('personal.marital_status', $personal['marital_status'] ?? '') === 'Kawin' || old('personal.marital_status', $personal['marital_status'] ?? '') === 'Menikah')>Kawin / Menikah</option>
                                <option value="Duda" @selected(old('personal.marital_status', $personal['marital_status'] ?? '') === 'Duda')>Duda</option>
                                <option value="Janda" @selected(old('personal.marital_status', $personal['marital_status'] ?? '') === 'Janda')>Janda</option>
                            </select>
                        </div>

                        <div>
                            <x-ui.label for="blood_type">Golongan Darah</x-ui.label>
                            <select id="blood_type" name="personal[blood_type]" class="h-11 w-full rounded-xl border border-line bg-surface px-3.5 text-[14px] text-ink outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/15">
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
                                <input type="number" id="birth_order" name="personal[birth_order]" value="{{ old('personal.birth_order', $personal['birth_order'] ?? '') }}" placeholder="Anak ke" min="1" max="20" class="h-11 w-full rounded-xl border border-line bg-surface px-3 text-[14px]" />
                                <span class="text-muted">dari</span>
                                <input type="number" id="siblings_count" name="personal[siblings_count]" value="{{ old('personal.siblings_count', $personal['siblings_count'] ?? '') }}" placeholder="Total saudara" min="1" max="20" class="h-11 w-full rounded-xl border border-line bg-surface px-3 text-[14px]" />
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
                                <input type="file" id="attachment_ktp" name="attachment_ktp" accept="image/*,.pdf" class="mt-1.5 block w-full text-[12.5px] file:mr-3 file:rounded-lg file:border-0 file:bg-brand/10 file:px-3 file:py-1.5 file:text-[12px] file:font-semibold file:text-brand" />
                            </div>

                            <div class="rounded-2xl border border-line p-3.5 bg-surface-2/30">
                                <x-ui.label for="attachment_kk">Scan / Foto Kartu Keluarga (KK) <span class="text-rose-500">*</span></x-ui.label>
                                <input type="file" id="attachment_kk" name="attachment_kk" accept="image/*,.pdf" class="mt-1.5 block w-full text-[12.5px] file:mr-3 file:rounded-lg file:border-0 file:bg-brand/10 file:px-3 file:py-1.5 file:text-[12px] file:font-semibold file:text-brand" />
                            </div>

                            <div class="rounded-2xl border border-line p-3.5 bg-surface-2/30">
                                <x-ui.label for="attachment_npwp">Scan / Foto Kartu NPWP (Jika ada)</x-ui.label>
                                <input type="file" id="attachment_npwp" name="attachment_npwp" accept="image/*,.pdf" class="mt-1.5 block w-full text-[12.5px] file:mr-3 file:rounded-lg file:border-0 file:bg-brand/10 file:px-3 file:py-1.5 file:text-[12px] file:font-semibold file:text-brand" />
                            </div>

                            <div class="rounded-2xl border border-line p-3.5 bg-surface-2/30">
                                <x-ui.label for="attachment_bpjs">Scan / Foto Kartu BPJS Ketenagakerjaan (Jika ada)</x-ui.label>
                                <input type="file" id="attachment_bpjs" name="attachment_bpjs" accept="image/*,.pdf" class="mt-1.5 block w-full text-[12.5px] file:mr-3 file:rounded-lg file:border-0 file:bg-brand/10 file:px-3 file:py-1.5 file:text-[12px] file:font-semibold file:text-brand" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-line bg-surface p-6 shadow-sm sm:p-8">
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
                            <div class="grid grid-cols-2 gap-2">
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
                </div>

                <div class="rounded-3xl border border-line bg-surface p-6 shadow-sm sm:p-8">
                    <div class="border-b border-line pb-4">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-brand">Bagian 3</span>
                        <h2 class="text-[18px] font-bold text-ink">3. Riwayat Pendidikan</h2>
                    </div>

                    <div class="mt-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-[14px] font-bold text-ink">a. Pendidikan Formal</h3>
                            <button type="button" x-on:click="addRow(eduFormal)" class="inline-flex items-center gap-1 rounded-lg border border-brand/30 bg-brand/10 px-2.5 py-1 text-[11.5px] font-semibold text-brand">
                                + Tambah
                            </button>
                        </div>

                        <div class="mt-3 space-y-3">
                            <template x-for="(row, idx) in eduFormal" :key="idx">
                                <div class="rounded-2xl border border-line bg-surface-2/20 p-3.5 space-y-2.5">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[12px] font-bold text-subtle" x-text="'Pendidikan #' + (idx + 1)"></span>
                                        <button type="button" x-on:click="removeRow(eduFormal, idx)" class="text-[11px] text-rose-500 hover:underline">Hapus</button>
                                    </div>
                                    <div class="grid gap-2.5 sm:grid-cols-3">
                                        <div>
                                            <input type="text" :name="'education_formal[' + idx + '][school]'" x-model="row.school" placeholder="Nama & Alamat Sekolah" class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                                        </div>
                                        <div>
                                            <input type="text" :name="'education_formal[' + idx + '][major]'" x-model="row.major" placeholder="Jurusan" class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                                        </div>
                                        <div>
                                            <input type="text" :name="'education_formal[' + idx + '][period]'" x-model="row.period" placeholder="Dari Thn s/d Thn (2018-2022)" class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                                        </div>
                                        <div>
                                            <select :name="'education_formal[' + idx + '][graduated]'" x-model="row.graduated" class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]">
                                                <option value="Tamat">Tamat</option>
                                                <option value="Tidak Tamat">Tidak Tamat</option>
                                            </select>
                                        </div>
                                        <div>
                                            <input type="text" :name="'education_formal[' + idx + '][dorm]'" x-model="row.dorm" placeholder="Alamat yg dipondoki selama sekolah" class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                                        </div>
                                        <div>
                                            <input type="text" :name="'education_formal[' + idx + '][notes]'" x-model="row.notes" placeholder="Keterangan / Nilai" class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="mt-8 border-t border-line pt-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-[14px] font-bold text-ink">b. Pendidikan Informal / Kursus & Pelatihan</h3>
                            <button type="button" x-on:click="addRow(eduInformal)" class="inline-flex items-center gap-1 rounded-lg border border-brand/30 bg-brand/10 px-2.5 py-1 text-[11.5px] font-semibold text-brand">
                                + Tambah
                            </button>
                        </div>

                        <div class="mt-3 space-y-3">
                            <template x-for="(row, idx) in eduInformal" :key="idx">
                                <div class="rounded-2xl border border-line bg-surface-2/20 p-3.5 space-y-2.5">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[12px] font-bold text-subtle" x-text="'Pelatihan #' + (idx + 1)"></span>
                                        <button type="button" x-on:click="removeRow(eduInformal, idx)" class="text-[11px] text-rose-500 hover:underline">Hapus</button>
                                    </div>
                                    <div class="grid gap-2.5 sm:grid-cols-3">
                                        <div>
                                            <input type="text" :name="'education_informal[' + idx + '][institute]'" x-model="row.institute" placeholder="Nama Institusi" class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                                        </div>
                                        <div>
                                            <input type="text" :name="'education_informal[' + idx + '][training]'" x-model="row.training" placeholder="Jenis Kursus / Pelatihan" class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                                        </div>
                                        <div>
                                            <input type="text" :name="'education_informal[' + idx + '][period]'" x-model="row.period" placeholder="Tahun (Dari s/d Thn)" class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                                        </div>
                                        <div>
                                            <select :name="'education_informal[' + idx + '][graduated]'" x-model="row.graduated" class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]">
                                                <option value="Tamat">Tamat</option>
                                                <option value="Tidak Tamat">Tidak Tamat</option>
                                            </select>
                                        </div>
                                        <div>
                                            <input type="text" :name="'education_informal[' + idx + '][dorm]'" x-model="row.dorm" placeholder="Alamat yg dipondoki" class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                                        </div>
                                        <div>
                                            <input type="text" :name="'education_informal[' + idx + '][notes]'" x-model="row.notes" placeholder="Keterangan / Sertifikat" class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-line bg-surface p-6 shadow-sm sm:p-8">
                    <div class="flex items-center justify-between border-b border-line pb-4">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-brand">Bagian 4</span>
                            <h2 class="text-[18px] font-bold text-ink">4. Pengalaman Kerja</h2>
                        </div>
                        <button type="button" x-on:click="addRow(workExperiences)" class="inline-flex items-center gap-1 rounded-lg border border-brand/30 bg-brand/10 px-2.5 py-1 text-[11.5px] font-semibold text-brand">
                            + Tambah Perusahaan
                        </button>
                    </div>

                    <div class="mt-4 space-y-3">
                        <template x-for="(work, idx) in workExperiences" :key="idx">
                            <div class="rounded-2xl border border-line bg-surface-2/20 p-4 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-[12px] font-bold text-subtle" x-text="'Pengalaman #' + (idx + 1)"></span>
                                    <button type="button" x-on:click="removeRow(workExperiences, idx)" class="text-[11px] text-rose-500 hover:underline">Hapus</button>
                                </div>
                                <div class="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-3">
                                    <div>
                                        <label class="block text-[11px] text-subtle">Nama Perusahaan</label>
                                        <input type="text" :name="'work_experiences[' + idx + '][company]'" x-model="work.company" class="mt-1 h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] text-subtle">Jabatan</label>
                                        <input type="text" :name="'work_experiences[' + idx + '][position]'" x-model="work.position" class="mt-1 h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] text-subtle">Dari Thn s/d Thn</label>
                                        <input type="text" :name="'work_experiences[' + idx + '][period]'" x-model="work.period" class="mt-1 h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-[11px] text-subtle">Alamat Kantor</label>
                                        <input type="text" :name="'work_experiences[' + idx + '][address]'" x-model="work.address" class="mt-1 h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] text-subtle">Keterangan / Alasan Pindah</label>
                                        <input type="text" :name="'work_experiences[' + idx + '][notes]'" x-model="work.notes" class="mt-1 h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="rounded-3xl border border-line bg-surface p-6 shadow-sm sm:p-8">
                    <div class="flex items-center justify-between border-b border-line pb-4">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-brand">Bagian 5</span>
                            <h2 class="text-[18px] font-bold text-ink">5. Tempat Tinggal Sejak Dilahirkan (Selengkapnya & Kronologis)</h2>
                            <p class="text-[12px] text-muted">Tuliskan riwayat tempat tinggal dari lahir hingga saat ini</p>
                        </div>
                        <button type="button" x-on:click="addRow(residences)" class="inline-flex items-center gap-1 rounded-lg border border-brand/30 bg-brand/10 px-2.5 py-1 text-[11.5px] font-semibold text-brand">
                            + Tambah Alamat
                        </button>
                    </div>

                    <div class="mt-4 space-y-3">
                        <template x-for="(res, idx) in residences" :key="idx">
                            <div class="flex items-center gap-3 rounded-2xl border border-line bg-surface-2/20 p-3.5">
                                <span class="flex size-6 shrink-0 items-center justify-center rounded-lg bg-surface text-[11px] font-bold text-subtle" x-text="idx + 1"></span>
                                <div class="grid flex-1 gap-2.5 sm:grid-cols-4">
                                    <div>
                                        <input type="text" :name="'residences_history[' + idx + '][period]'" x-model="res.period" placeholder="Dari Thn s/d Thn (1998-2016)" class="h-9 w-full rounded-xl border border-line bg-surface px-2.5 text-[12.5px]" />
                                    </div>
                                    <div class="sm:col-span-2">
                                        <input type="text" :name="'residences_history[' + idx + '][address]'" x-model="res.address" placeholder="Nama Jalan & Nomor Rumah" class="h-9 w-full rounded-xl border border-line bg-surface px-2.5 text-[12.5px]" />
                                    </div>
                                    <div>
                                        <input type="text" :name="'residences_history[' + idx + '][city]'" x-model="res.city" placeholder="Kota / Keterangan" class="h-9 w-full rounded-xl border border-line bg-surface px-2.5 text-[12.5px]" />
                                    </div>
                                </div>
                                <button type="button" x-on:click="removeRow(residences, idx)" class="text-rose-500">
                                    <x-icon name="trash" class="size-4" />
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="rounded-3xl border border-line bg-surface p-6 shadow-sm sm:p-8">
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
                                <input type="text" name="family_details[spouse][name]" value="{{ old('family_details.spouse.name', $family['spouse']['name'] ?? '') }}" class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                            </div>
                            <div>
                                <x-ui.label>TTL / Lahir</x-ui.label>
                                <input type="text" name="family_details[spouse][birth]" value="{{ old('family_details.spouse.birth', $family['spouse']['birth_date'] ?? '') }}" placeholder="Kota & Tanggal" class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                            </div>
                            <div>
                                <x-ui.label>Agama & Suku</x-ui.label>
                                <input type="text" name="family_details[spouse][religion_race]" value="{{ old('family_details.spouse.religion_race', $family['spouse']['religion'] ?? '') }}" class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                            </div>
                            <div>
                                <x-ui.label>Pekerjaan</x-ui.label>
                                <input type="text" name="family_details[spouse][job]" value="{{ old('family_details.spouse.job', $family['spouse']['job'] ?? '') }}" class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                            </div>
                            <div>
                                <x-ui.label>No. Telepon / HP</x-ui.label>
                                <input type="text" name="family_details[spouse][phone]" value="{{ old('family_details.spouse.phone', $family['spouse']['phone'] ?? '') }}" class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                            </div>
                            <div class="sm:col-span-3">
                                <x-ui.label>Alamat Kantor & Telp</x-ui.label>
                                <input type="text" name="family_details[spouse][office]" value="{{ old('family_details.spouse.office', $family['spouse']['office_address'] ?? '') }}" class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-line/80 bg-surface-2/30 p-4 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <h3 class="text-[13.5px] font-bold text-ink">b. Bapak</h3>
                                <select name="family_details[father][status_type]" class="h-7 rounded-lg border border-line bg-surface px-2 text-[11px]">
                                    <option value="Kandung" selected>Kandung</option>
                                    <option value="Angkat">Angkat</option>
                                    <option value="Tiri">Tiri</option>
                                </select>
                            </div>
                            <div>
                                <x-ui.label>Nama Lengkap <span class="text-rose-500">*</span></x-ui.label>
                                <input type="text" name="family_details[father][name]" value="{{ old('family_details.father.name', $family['father']['name'] ?? '') }}" required class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                            </div>
                            <div>
                                <x-ui.label>Pekerjaan</x-ui.label>
                                <input type="text" name="family_details[father][job]" value="{{ old('family_details.father.job', $family['father']['job'] ?? '') }}" class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                            </div>
                            <div>
                                <x-ui.label>No. HP / Telp</x-ui.label>
                                <input type="text" name="family_details[father][phone]" value="{{ old('family_details.father.phone', $family['father']['phone'] ?? '') }}" class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                            </div>
                        </div>

                        <div class="rounded-2xl border border-line/80 bg-surface-2/30 p-4 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <h3 class="text-[13.5px] font-bold text-ink">c. Ibu</h3>
                                <select name="family_details[mother][status_type]" class="h-7 rounded-lg border border-line bg-surface px-2 text-[11px]">
                                    <option value="Kandung" selected>Kandung</option>
                                    <option value="Angkat">Angkat</option>
                                    <option value="Tiri">Tiri</option>
                                </select>
                            </div>
                            <div>
                                <x-ui.label>Nama Lengkap <span class="text-rose-500">*</span></x-ui.label>
                                <input type="text" name="family_details[mother][name]" value="{{ old('family_details.mother.name', $family['mother']['name'] ?? '') }}" required class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                            </div>
                            <div>
                                <x-ui.label>Pekerjaan</x-ui.label>
                                <input type="text" name="family_details[mother][job]" value="{{ old('family_details.mother.job', $family['mother']['job'] ?? '') }}" class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                            </div>
                            <div>
                                <x-ui.label>No. HP / Telp</x-ui.label>
                                <input type="text" name="family_details[mother][phone]" value="{{ old('family_details.mother.phone', $family['mother']['phone'] ?? '') }}" class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-line/80 bg-surface-2/30 p-4 space-y-2.5">
                            <h3 class="text-[13.5px] font-bold text-ink">d. Bapak Mertua (Jika menikah)</h3>
                            <div>
                                <x-ui.label>Nama Lengkap</x-ui.label>
                                <input type="text" name="family_details[father_in_law][name]" value="{{ old('family_details.father_in_law.name', $family['father_in_law']['name'] ?? '') }}" class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                            </div>
                            <div>
                                <x-ui.label>Pekerjaan & Telp</x-ui.label>
                                <input type="text" name="family_details[father_in_law][job]" value="{{ old('family_details.father_in_law.job', $family['father_in_law']['job'] ?? '') }}" placeholder="Pekerjaan / Telp" class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                            </div>
                        </div>

                        <div class="rounded-2xl border border-line/80 bg-surface-2/30 p-4 space-y-2.5">
                            <h3 class="text-[13.5px] font-bold text-ink">e. Ibu Mertua (Jika menikah)</h3>
                            <div>
                                <x-ui.label>Nama Lengkap</x-ui.label>
                                <input type="text" name="family_details[mother_in_law][name]" value="{{ old('family_details.mother_in_law.name', $family['mother_in_law']['name'] ?? '') }}" class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                            </div>
                            <div>
                                <x-ui.label>Pekerjaan & Telp</x-ui.label>
                                <input type="text" name="family_details[mother_in_law][job]" value="{{ old('family_details.mother_in_law.job', $family['mother_in_law']['job'] ?? '') }}" placeholder="Pekerjaan / Telp" class="h-9 w-full rounded-xl border border-line bg-surface px-3 text-[12.5px]" />
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-line pt-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-[14px] font-bold text-ink">f. Anak-Anak (Kandung, Tiri, Angkat)</h3>
                            <button type="button" x-on:click="addRow(children)" class="inline-flex items-center gap-1 rounded-lg border border-brand/30 bg-brand/10 px-2.5 py-1 text-[11.5px] font-semibold text-brand">
                                + Tambah Anak
                            </button>
                        </div>

                        <div class="mt-3 space-y-2.5">
                            <template x-for="(child, idx) in children" :key="idx">
                                <div class="flex items-center gap-2.5 rounded-2xl border border-line bg-surface p-3">
                                    <span class="text-[11px] font-bold text-subtle" x-text="idx + 1"></span>
                                    <div class="grid flex-1 gap-2 sm:grid-cols-5">
                                        <input type="text" :name="'family_details[children][' + idx + '][name]'" x-model="child.name" placeholder="Nama Lengkap" class="h-9 rounded-xl border border-line bg-surface px-2.5 text-[12.5px]" />
                                        <select :name="'family_details[children][' + idx + '][gender]'" x-model="child.gender" class="h-9 rounded-xl border border-line bg-surface px-2.5 text-[12.5px]">
                                            <option value="L">L</option>
                                            <option value="P">P</option>
                                        </select>
                                        <input type="text" :name="'family_details[children][' + idx + '][age]'" x-model="child.age" placeholder="Umur (Thn)" class="h-9 rounded-xl border border-line bg-surface px-2.5 text-[12.5px]" />
                                        <input type="text" :name="'family_details[children][' + idx + '][job_school]'" x-model="child.job_school" placeholder="Sekolah / Pekerjaan" class="h-9 rounded-xl border border-line bg-surface px-2.5 text-[12.5px]" />
                                        <input type="text" :name="'family_details[children][' + idx + '][notes]'" x-model="child.notes" placeholder="Keterangan" class="h-9 rounded-xl border border-line bg-surface px-2.5 text-[12.5px]" />
                                    </div>
                                    <button type="button" x-on:click="removeRow(children, idx)" class="text-rose-500">
                                        <x-icon name="trash" class="size-4" />
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-line pt-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-[14px] font-bold text-ink">g. Saudara-Saudara (Kandung, Tiri, Angkat)</h3>
                            <button type="button" x-on:click="addRow(siblings)" class="inline-flex items-center gap-1 rounded-lg border border-brand/30 bg-brand/10 px-2.5 py-1 text-[11.5px] font-semibold text-brand">
                                + Tambah Saudara
                            </button>
                        </div>

                        <div class="mt-3 space-y-2.5">
                            <template x-for="(sib, idx) in siblings" :key="idx">
                                <div class="flex items-center gap-2.5 rounded-2xl border border-line bg-surface p-3">
                                    <span class="text-[11px] font-bold text-subtle" x-text="idx + 1"></span>
                                    <div class="grid flex-1 gap-2 sm:grid-cols-5">
                                        <input type="text" :name="'family_details[siblings][' + idx + '][name]'" x-model="sib.name" placeholder="Nama Lengkap" class="h-9 rounded-xl border border-line bg-surface px-2.5 text-[12.5px]" />
                                        <select :name="'family_details[siblings][' + idx + '][gender]'" x-model="sib.gender" class="h-9 rounded-xl border border-line bg-surface px-2.5 text-[12.5px]">
                                            <option value="L">L</option>
                                            <option value="P">P</option>
                                        </select>
                                        <input type="text" :name="'family_details[siblings][' + idx + '][age]'" x-model="sib.age" placeholder="Umur (Thn)" class="h-9 rounded-xl border border-line bg-surface px-2.5 text-[12.5px]" />
                                        <input type="text" :name="'family_details[siblings][' + idx + '][job_school]'" x-model="sib.job_school" placeholder="Pekerjaan / Sekolah" class="h-9 rounded-xl border border-line bg-surface px-2.5 text-[12.5px]" />
                                        <input type="text" :name="'family_details[siblings][' + idx + '][notes]'" x-model="sib.notes" placeholder="No Telp / Ket" class="h-9 rounded-xl border border-line bg-surface px-2.5 text-[12.5px]" />
                                    </div>
                                    <button type="button" x-on:click="removeRow(siblings, idx)" class="text-rose-500">
                                        <x-icon name="trash" class="size-4" />
                                    </button>
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

                        <div class="mt-3 space-y-2.5">
                            <template x-for="(fr, idx) in closeFriends" :key="idx">
                                <div class="rounded-2xl border border-line bg-surface-2/20 p-3.5 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[12px] font-bold text-subtle" x-text="'Sahabat #' + (idx + 1)"></span>
                                    </div>
                                    <div class="grid gap-2 sm:grid-cols-4">
                                        <div>
                                            <input type="text" :name="'family_details[friends][' + idx + '][name]'" x-model="fr.name" placeholder="Nama Lengkap" required class="h-9 w-full rounded-xl border border-line bg-surface px-2.5 text-[12.5px]" />
                                        </div>
                                        <div>
                                            <input type="text" :name="'family_details[friends][' + idx + '][phone]'" x-model="fr.phone" placeholder="No. Telepon / HP" required class="h-9 w-full rounded-xl border border-line bg-surface px-2.5 text-[12.5px]" />
                                        </div>
                                        <div>
                                            <input type="text" :name="'family_details[friends][' + idx + '][job_school]'" x-model="fr.job_school" placeholder="Pekerjaan & Alamat" required class="h-9 w-full rounded-xl border border-line bg-surface px-2.5 text-[12.5px]" />
                                        </div>
                                        <div>
                                            <input type="text" :name="'family_details[friends][' + idx + '][notes]'" x-model="fr.notes" placeholder="Kenal Sejak / Keterangan" class="h-9 w-full rounded-xl border border-line bg-surface px-2.5 text-[12.5px]" />
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-line bg-surface p-6 shadow-sm sm:p-8">
                    <div class="flex items-center justify-between border-b border-line pb-4">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-brand">Bagian 7</span>
                            <h2 class="text-[18px] font-bold text-ink">7. Pengalaman Berorganisasi</h2>
                        </div>
                        <button type="button" x-on:click="addRow(organizations)" class="inline-flex items-center gap-1 rounded-lg border border-brand/30 bg-brand/10 px-2.5 py-1 text-[11.5px] font-semibold text-brand">
                            + Tambah Organisasi
                        </button>
                    </div>

                    <div class="mt-4 space-y-3">
                        <template x-for="(org, idx) in organizations" :key="idx">
                            <div class="flex items-center gap-2.5 rounded-2xl border border-line bg-surface p-3">
                                <span class="text-[11px] font-bold text-subtle" x-text="idx + 1"></span>
                                <div class="grid flex-1 gap-2 sm:grid-cols-4">
                                    <input type="text" :name="'organization_history[' + idx + '][name]'" x-model="org.name" placeholder="Nama Organisasi" class="h-9 rounded-xl border border-line bg-surface px-2.5 text-[12.5px]" />
                                    <input type="text" :name="'organization_history[' + idx + '][place]'" x-model="org.place" placeholder="Tempat / Kota" class="h-9 rounded-xl border border-line bg-surface px-2.5 text-[12.5px]" />
                                    <input type="text" :name="'organization_history[' + idx + '][position]'" x-model="org.position" placeholder="Jabatan" class="h-9 rounded-xl border border-line bg-surface px-2.5 text-[12.5px]" />
                                    <input type="text" :name="'organization_history[' + idx + '][period]'" x-model="org.period" placeholder="Dari Thn s/d Thn" class="h-9 rounded-xl border border-line bg-surface px-2.5 text-[12.5px]" />
                                </div>
                                <button type="button" x-on:click="removeRow(organizations, idx)" class="text-rose-500">
                                    <x-icon name="trash" class="size-4" />
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="rounded-3xl border border-line bg-surface p-6 shadow-sm sm:p-8">
                    <div class="border-b border-line pb-4">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-brand">Bagian 8</span>
                        <h2 class="text-[18px] font-bold text-ink">8. Pernyataan & Tanda Tangan</h2>
                    </div>

                    <div class="mt-5 rounded-2xl border border-amber-500/20 bg-amber-500/5 p-5 text-amber-950 dark:text-amber-200">
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
                                <x-ui.input id="signed_name" name="signed_name" :value="old('signed_name', $personal['full_name'] ?? '')" required />
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center justify-end gap-3 border-t border-line pt-5">
                        <x-ui.button type="submit" variant="primary" icon="send" class="w-full sm:w-auto text-[14px] px-6 py-3">
                            Kirim Formulir Biodata Karyawan
                        </x-ui.button>
                    </div>
                </div>
            </form>
        @endif
    </div>
</x-layouts.public>