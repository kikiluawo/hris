<x-layouts.public title="Form Permintaan Karyawan">
    <x-slot:hero>
        <section class="relative isolate overflow-hidden bg-[#0c1e38] text-white">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,rgba(217,180,106,0.16),transparent_55%)]"></div>
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_right,rgba(20,53,102,0.5),transparent_55%)]"></div>
            <div class="pattern-grid pointer-events-none absolute inset-0 opacity-25"></div>

            <div class="relative mx-auto w-full max-w-[1160px] px-4 pb-12 pt-14 sm:px-6 sm:pb-16 sm:pt-18 lg:px-8">
                <p class="flex items-center gap-3 text-[10px] font-semibold uppercase tracking-[0.34em] text-gold">
                    <span class="h-px w-8 bg-gold/60"></span>
                    Rekrutmen internal
                </p>

                <h1 class="mt-6 max-w-2xl font-display text-[38px] font-medium leading-[1.05] tracking-tight sm:text-[50px]">
                    Form Permintaan <span class="italic text-gold">Karyawan</span>
                </h1>

                <p class="mt-5 max-w-xl text-[13.5px] leading-relaxed text-white/60 sm:text-[14.5px]">
                    Ajukan kebutuhan tenaga kerja untuk unit Anda. Setelah dikirim, HRD meninjau dan melengkapi data sebelum lowongan diterbitkan ke halaman karier.
                </p>

                <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-2 text-[11.5px] font-medium text-white/50">
                    <span class="inline-flex items-center gap-2">
                        <span class="size-1.5 rounded-full bg-gold"></span>
                        Tanpa perlu membuat akun
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <span class="size-1.5 rounded-full bg-gold/60"></span>
                        Ditinjau HRD sebelum terbit
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <span class="size-1.5 rounded-full bg-gold/60"></span>
                        PT penempatan boleh menyusul
                    </span>
                </div>
            </div>
        </section>
    </x-slot:hero>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px] lg:items-start">
        <div class="overflow-hidden rounded-[26px] border border-line bg-surface shadow-card">
            <div class="relative overflow-hidden bg-[#0c1e38] px-6 py-5 text-white">
                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(217,180,106,0.16),transparent_60%)]"></div>
                <div class="relative">
                    <p class="text-[9.5px] font-semibold uppercase tracking-[0.3em] text-gold">Formulir permintaan</p>
                    <h2 class="mt-1.5 font-display text-[24px] font-medium">Data kebutuhan karyawan</h2>
                    <p class="mt-1 text-[12px] text-white/50">Kolom bertanda bintang wajib diisi.</p>
                </div>
            </div>

            <div class="p-6 sm:p-8">
                @if ($errors->any())
                    <x-ui.alert variant="danger" class="mb-5">
                        <ul class="list-disc space-y-0.5 pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-ui.alert>
                @endif

                <form method="POST" action="{{ route('hiring-requests.store', $token) }}" class="space-y-8" x-data="{ submitting: false, requestType: '{{ old('request_type', 'baru') }}' }" @submit="submitting = true">
                    @csrf

                    <div class="sr-only" aria-hidden="true">
                        <label for="website">Website</label>
                        <input type="text" name="website" id="website" tabindex="-1" autocomplete="off" />
                    </div>

                    <section class="space-y-4">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-gold-strong">1. Data pengaju</p>
                            <p class="mt-1 text-[12px] text-muted">Supaya HRD tahu harus menghubungi siapa jika ada yang perlu dikonfirmasi.</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <x-ui.label for="requester_name">Nama pengaju <span class="text-rose-500">*</span></x-ui.label>
                                <x-ui.input name="requester_name" placeholder="Nama lengkap" autocomplete="name" />
                                <x-ui.field-error name="requester_name" />
                            </div>

                            <div>
                                <x-ui.label for="requester_position">Jabatan pengaju <span class="text-rose-500">*</span></x-ui.label>
                                <x-ui.input name="requester_position" placeholder="Contoh: Kepala Cabang" />
                                <x-ui.field-error name="requester_position" />
                            </div>

                            <div>
                                <x-ui.label for="requester_phone">No. HP / WhatsApp <span class="text-rose-500">*</span></x-ui.label>
                                <x-ui.input type="tel" name="requester_phone" icon="phone" placeholder="08xxxxxxxxxx" autocomplete="tel" />
                                <x-ui.field-error name="requester_phone" />
                            </div>

                            <div>
                                <x-ui.label for="requester_email">Email <span class="font-normal text-subtle">(opsional)</span></x-ui.label>
                                <x-ui.input type="email" name="requester_email" icon="mail" placeholder="nama@perusahaan.com" autocomplete="email" />
                                <x-ui.field-error name="requester_email" />
                            </div>

                            <div class="sm:col-span-2">
                                <x-ui.label for="requesting_unit">Asal unit / divisi <span class="font-normal text-subtle">(opsional)</span></x-ui.label>
                                <x-ui.input name="requesting_unit" placeholder="Contoh: Divisi Aftersales" />
                                <x-ui.field-error name="requesting_unit" />
                            </div>
                        </div>
                    </section>

                    <div class="h-px bg-line"></div>

                    <section class="space-y-4">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-gold-strong">2. Kebutuhan posisi &amp; jenis pengajuan</p>
                            <p class="mt-1 text-[12px] text-muted">Tentukan apakah ini penambahan karyawan baru atau pergantian posisi yang sudah ada.</p>
                        </div>

                        <div>
                            <x-ui.label>Jenis kebutuhan karyawan <span class="text-rose-500">*</span></x-ui.label>
                            <div class="mt-2 grid gap-3 sm:grid-cols-2">
                                <label
                                    class="relative flex cursor-pointer items-start gap-3 rounded-2xl border p-4 transition"
                                    :class="requestType === 'baru' ? 'border-brand bg-brand-soft/40 shadow-sm' : 'border-line bg-surface hover:border-brand/40'"
                                >
                                    <input
                                        type="radio"
                                        name="request_type"
                                        value="baru"
                                        x-model="requestType"
                                        class="mt-0.5 size-4 text-brand focus:ring-brand"
                                    />
                                    <div>
                                        <p class="text-[13.5px] font-semibold text-ink">Karyawan Baru</p>
                                        <p class="mt-0.5 text-[12px] text-muted leading-relaxed">
                                            Penambahan kuota / formasi baru untuk ekspansi atau beban kerja tambahan.
                                        </p>
                                    </div>
                                </label>

                                <label
                                    class="relative flex cursor-pointer items-start gap-3 rounded-2xl border p-4 transition"
                                    :class="requestType === 'pergantian' ? 'border-brand bg-brand-soft/40 shadow-sm' : 'border-line bg-surface hover:border-brand/40'"
                                >
                                    <input
                                        type="radio"
                                        name="request_type"
                                        value="pergantian"
                                        x-model="requestType"
                                        class="mt-0.5 size-4 text-brand focus:ring-brand"
                                    />
                                    <div>
                                        <p class="text-[13.5px] font-semibold text-ink">Pergantian Karyawan</p>
                                        <p class="mt-0.5 text-[12px] text-muted leading-relaxed">
                                            Menggantikan karyawan sebelumnya (resign, mutasi, promosi, dsb).
                                        </p>
                                    </div>
                                </label>
                            </div>
                            <x-ui.field-error name="request_type" />
                        </div>

                        <div x-show="requestType === 'pergantian'" x-cloak x-transition class="space-y-4 rounded-2xl border border-amber-200/80 bg-amber-50/50 p-4.5 dark:border-amber-400/20 dark:bg-amber-400/5">
                            <div class="flex items-center gap-2 text-amber-800 dark:text-amber-300">
                                <x-icon name="user" class="size-4" />
                                <span class="text-[12.5px] font-semibold">Data karyawan yang digantikan</span>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <x-ui.label for="replaced_employee_name">Nama karyawan yang digantikan <span class="text-rose-500">*</span></x-ui.label>
                                    <x-ui.input name="replaced_employee_name" :value="old('replaced_employee_name')" placeholder="Nama lengkap karyawan yang digantikan" />
                                    <x-ui.field-error name="replaced_employee_name" />
                                </div>

                                <div class="sm:col-span-2">
                                    <x-ui.label for="replacement_reason">Alasan pergantian <span class="text-rose-500">*</span></x-ui.label>
                                    <x-ui.textarea name="replacement_reason" :rows="3" placeholder="Sertakan alasan pergantian (contoh: Mengundurkan diri terhitung akhir bulan ini, mutasi ke cabang lain, dll.)">{{ old('replacement_reason') }}</x-ui.textarea>
                                    <x-ui.field-error name="replacement_reason" />
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <x-ui.label for="title">Jabatan yang dibutuhkan <span class="text-rose-500">*</span></x-ui.label>
                                <x-ui.input name="title" icon="briefcase" placeholder="Contoh: Service Advisor, Mekanik Mobil, Admin Warranty" />
                                <x-ui.field-error name="title" />
                            </div>

                            <div>
                                <x-ui.label for="job_type">Tipe pekerjaan <span class="text-rose-500">*</span></x-ui.label>
                                <x-ui.select name="job_type" :options="$jobTypes" :selected="old('job_type', \App\Models\JobOpening::TYPE_FULL_TIME)" />
                                <x-ui.field-error name="job_type" />
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <x-ui.label for="headcount">Jumlah orang <span class="text-rose-500">*</span></x-ui.label>
                                <x-ui.input type="number" name="headcount" :value="1" min="1" max="100" inputmode="numeric" />
                                <x-ui.field-error name="headcount" />
                            </div>

                            <div>
                                <x-ui.label for="company_id">PT penempatan <span class="font-normal text-subtle">(opsional)</span></x-ui.label>
                                <x-ui.select name="company_id" :options="$companies->all()" placeholder="Belum ditentukan" />
                                <p class="mt-1.5 text-[11.5px] leading-relaxed text-subtle">Kosongkan jika PT baru diputuskan CEO/CFO setelah kandidat diterima.</p>
                                <x-ui.field-error name="company_id" />
                            </div>
                        </div>

                        <div>
                            <x-ui.label for="description">Deskripsi pekerjaan &amp; tanggung jawab <span class="text-rose-500">*</span></x-ui.label>
                            <x-ui.textarea name="description" :rows="5" placeholder="Contoh: Melayani pelanggan di bengkel, membuat estimasi biaya, mengikuti target penjualan jasa." />
                            <x-ui.field-error name="description" />
                        </div>
                    </section>

                    <div class="h-px bg-line"></div>

                    <section class="space-y-4">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-gold-strong">3. Kualifikasi</p>
                            <p class="mt-1 text-[12px] text-muted">Dipakai untuk menyaring CV pelamar secara otomatis. Isi yang benar-benar penting saja.</p>
                        </div>

                        <div>
                            <x-ui.label for="required_skills">Keahlian yang dicari <span class="font-normal text-subtle">(opsional)</span></x-ui.label>
                            <x-ui.textarea name="required_skills" :rows="3" placeholder="Contoh: service advisor, estimasi biaya, komunikasi, SIM A" />
                            <p class="mt-1.5 text-[11.5px] text-subtle">Pisahkan dengan koma atau baris baru. Maksimal 20 keahlian.</p>
                            <x-ui.field-error name="required_skills" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <x-ui.label for="min_education">Pendidikan minimum <span class="font-normal text-subtle">(opsional)</span></x-ui.label>
                                <x-ui.select name="min_education" :options="$educationLevels" placeholder="Tidak ditentukan" />
                                <x-ui.field-error name="min_education" />
                            </div>

                            <div>
                                <x-ui.label for="min_experience">Pengalaman minimum (tahun) <span class="font-normal text-subtle">(opsional)</span></x-ui.label>
                                <x-ui.input type="number" name="min_experience" placeholder="0" min="0" max="50" inputmode="numeric" />
                                <x-ui.field-error name="min_experience" />
                            </div>
                        </div>
                    </section>

                    <div class="h-px bg-line"></div>

                    <section class="space-y-4">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-gold-strong">4. Catatan tambahan</p>
                            <p class="mt-1 text-[12px] text-muted">Target waktu, kondisi khusus, atau alasan kebutuhan.</p>
                        </div>

                        <div>
                            <x-ui.label for="notes">Catatan <span class="font-normal text-subtle">(opsional)</span></x-ui.label>
                            <x-ui.textarea name="notes" :rows="3" placeholder="Contoh: Dibutuhkan secepatnya untuk cabang baru." />
                            <x-ui.field-error name="notes" />
                        </div>
                    </section>

                    <div class="flex flex-col gap-3 border-t border-line pt-6 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-[11.5px] leading-relaxed text-subtle">
                            Dengan mengirim form ini, data akan digunakan untuk proses rekrutmen internal.
                        </p>

                        <x-ui.button type="submit" variant="gold" size="lg" icon="check" class="shrink-0" x-bind:disabled="submitting">
                            <span x-show="!submitting">Kirim permintaan</span>
                            <span x-show="submitting" x-cloak>Mengirim…</span>
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>

        <aside class="space-y-4 lg:sticky lg:top-24">
            <div class="rounded-[26px] border border-gold/20 bg-[#0c1e38] p-6 text-white">
                <p class="text-[9.5px] font-semibold uppercase tracking-[0.3em] text-gold">Setelah dikirim</p>
                <h2 class="mt-2 font-display text-[22px] font-medium leading-snug">Alur permintaan</h2>

                <ol class="mt-5 space-y-4">
                    @foreach ([
                        ['HRD meninjau', 'HRD memeriksa kelengkapan data dan menanyakan hal yang kurang.'],
                        ['HRD melengkapi', 'Departemen, jabatan internal, dan PT penempatan bisa diisi atau diubah HRD.'],
                        ['Lowongan terbit', 'HRD menekan tombol terbitkan dan lowongan tampil di halaman karier.'],
                        ['Pelamar masuk', 'CV pelamar disaring otomatis sesuai kualifikasi yang diisi.'],
                    ] as $index => $step)
                        <li class="flex gap-3">
                            <span class="flex size-6 shrink-0 items-center justify-center rounded-full border border-gold/40 text-[11px] font-bold text-gold">{{ $index + 1 }}</span>
                            <div>
                                <p class="text-[13px] font-semibold text-white">{{ $step[0] }}</p>
                                <p class="mt-0.5 text-[11.5px] leading-relaxed text-white/50">{{ $step[1] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>

            <div class="rounded-2xl border border-line bg-surface p-5 shadow-card">
                <div class="flex items-start gap-3">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-xl border border-gold/25 bg-gold-soft text-gold-strong">
                        <x-icon name="building" class="size-[18px]" />
                    </span>
                    <div>
                        <p class="text-[13px] font-semibold">Belum tahu PT penempatan?</p>
                        <p class="mt-1 text-[11.5px] leading-relaxed text-muted">
                            Biarkan kosong. Lowongan tetap bisa terbit dan tampil sebagai Majesty Group sampai PT ditetapkan.
                        </p>
                    </div>
                </div>
            </div>

            <a href="{{ route('career.index') }}" class="flex items-center justify-between rounded-2xl border border-line bg-surface px-5 py-4 text-[12.5px] font-semibold text-muted shadow-card transition hover:border-gold/40 hover:text-ink">
                Lihat halaman karier
                <x-icon name="arrow-right" class="size-4 text-gold-strong" />
            </a>
        </aside>
    </div>
</x-layouts.public>
