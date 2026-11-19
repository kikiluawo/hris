@php
    $initialCompany = old('company_id', $employee?->company_id ?? '');
    $initialBranch = old('branch_id', $employee?->branch_id ?? '');
    $initialDepartment = old('department_id', $employee?->department_id ?? '');
    $initialPosition = old('position_id', $employee?->position_id ?? '');
    $isEditing = (bool) ($employee && $employee->exists);
@endphp

<x-layouts.app :title="$isEditing ? 'Edit karyawan' : 'Tambah karyawan'">
    <x-ui.page-header
        :title="$isEditing ? 'Edit karyawan' : 'Tambah karyawan'"
        :description="$isEditing
            ? 'Perbarui data kepegawaian, identitas, kontak, dan keluarga. Perubahan penempatan tercatat di riwayat mutasi bila dilakukan lewat menu Mutasi.'
            : 'Lengkapi data karyawan baru. Kolom bertanda * wajib diisi.'"
    >
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="chevron-left" :href="$isEditing ? route('employees.show', $employee) : route('employees.index')">Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($errors->any())
        <x-ui.alert variant="danger" class="mb-4">
            <ul class="list-disc space-y-0.5 pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    <form
        method="POST"
        action="{{ $isEditing ? route('employees.update', $employee) : route('employees.store') }}"
        class="space-y-4"
        x-data="{
            organization: @js($organization),
            companyId: @js($initialCompany),
            branchId: @js($initialBranch),
            departmentId: @js($initialDepartment),
            positionId: @js($initialPosition),
            isEditing: @js($isEditing),
            nik: @js(old('nik', $employee?->nik ?? '')),
            joinedAt: @js(old('joined_at', $employee?->joined_at?->format('Y-m-d') ?? now()->format('Y-m-d'))),
            nikTouched: @js((bool) old('nik')),
            isGeneratingNik: false,
            get company() { return this.organization.find(c => c.id === Number(this.companyId)); },
            get branchOptions() { return this.company ? this.company.branches : []; },
            get departmentOptions() { return this.company ? this.company.departments : []; },
            get positionOptions() { return this.departmentOptions.find(d => d.id === Number(this.departmentId))?.positions ?? []; },
            onCompanyChange() {
                this.branchId = '';
                this.departmentId = '';
                this.positionId = '';
                if (!this.isEditing || !this.nik) {
                    this.generateStaffNumber();
                }
            },
            onDepartmentChange() {
                this.positionId = '';
            },
            async generateStaffNumber(force = false) {
                if (!this.companyId) return;
                if (this.isEditing && !force) return;
                if (this.nikTouched && !force) return;

                this.isGeneratingNik = true;
                try {
                    const res = await fetch(`{{ route('employees.next-staff-number') }}?company_id=${this.companyId}&joined_at=${this.joinedAt}`);
                    const data = await res.json();
                    if (data.staff_number) {
                        this.nik = data.staff_number;
                    }
                } catch (e) {
                    console.error(e);
                } finally {
                    this.isGeneratingNik = false;
                }
            },
            init() {
                if (!this.isEditing && this.companyId && !this.nik) {
                    this.generateStaffNumber();
                }
            }
        }"
    >
        @csrf
        @if ($isEditing)
            @method('PATCH')
        @endif

        <x-ui.card title="Penempatan" description="PT, cabang, departemen, dan jabatan tempat karyawan bernaung.">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-ui.label for="company_id">PT <span class="text-rose-500">*</span></x-ui.label>
                    <div class="relative">
                        <select
                            name="company_id"
                            id="company_id"
                            x-model="companyId"
                            x-on:change="onCompanyChange()"
                            data-select2-init
                            data-placeholder="Pilih PT"
                            class="h-11 w-full appearance-none rounded-xl border border-line bg-surface pl-3.5 pr-10 text-[15px] text-ink shadow-sm outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/15"
                        >
                            <option value="">Pilih PT</option>
                            <template x-for="option in organization" :key="option.id">
                                <option :value="option.id" x-text="option.name"></option>
                            </template>
                        </select>
                        <span data-select-chevron class="pointer-events-none absolute inset-y-0 right-0 flex w-10 items-center justify-center text-subtle">
                            <x-icon name="chevron-down" class="size-4" />
                        </span>
                    </div>
                    <x-ui.field-error name="company_id" />
                </div>

                <div>
                    <x-ui.label for="branch_id">Cabang</x-ui.label>
                    <div class="relative">
                        <select
                            name="branch_id"
                            id="branch_id"
                            x-model="branchId"
                            :disabled="!company"
                            data-select2-init
                            data-select2-clear
                            data-placeholder="Tanpa cabang"
                            class="h-11 w-full appearance-none rounded-xl border border-line bg-surface pl-3.5 pr-10 text-[15px] text-ink shadow-sm outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/15 disabled:bg-surface-2 disabled:text-muted"
                        >
                            <option value="">Tanpa cabang</option>
                            <template x-for="option in branchOptions" :key="option.id">
                                <option :value="option.id" x-text="option.name"></option>
                            </template>
                        </select>
                        <span data-select-chevron class="pointer-events-none absolute inset-y-0 right-0 flex w-10 items-center justify-center text-subtle">
                            <x-icon name="chevron-down" class="size-4" />
                        </span>
                    </div>
                    <x-ui.field-error name="branch_id" />
                </div>

                <div>
                    <x-ui.label for="department_id">Departemen <span class="text-rose-500">*</span></x-ui.label>
                    <div class="relative">
                        <select
                            name="department_id"
                            id="department_id"
                            x-model="departmentId"
                            x-on:change="onDepartmentChange()"
                            :disabled="!company"
                            data-select2-init
                            data-placeholder="Pilih departemen"
                            class="h-11 w-full appearance-none rounded-xl border border-line bg-surface pl-3.5 pr-10 text-[15px] text-ink shadow-sm outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/15 disabled:bg-surface-2 disabled:text-muted"
                        >
                            <option value="">Pilih departemen</option>
                            <template x-for="option in departmentOptions" :key="option.id">
                                <option :value="option.id" x-text="option.name"></option>
                            </template>
                        </select>
                        <span data-select-chevron class="pointer-events-none absolute inset-y-0 right-0 flex w-10 items-center justify-center text-subtle">
                            <x-icon name="chevron-down" class="size-4" />
                        </span>
                    </div>
                    <x-ui.field-error name="department_id" />
                </div>

                <div>
                    <x-ui.label for="position_id">Jabatan <span class="text-rose-500">*</span></x-ui.label>
                    <div class="relative">
                        <select
                            name="position_id"
                            id="position_id"
                            x-model="positionId"
                            :disabled="positionOptions.length === 0"
                            data-select2-init
                            data-placeholder="Pilih jabatan"
                            class="h-11 w-full appearance-none rounded-xl border border-line bg-surface pl-3.5 pr-10 text-[15px] text-ink shadow-sm outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/15 disabled:bg-surface-2 disabled:text-muted"
                        >
                            <option value="">Pilih jabatan</option>
                            <template x-for="option in positionOptions" :key="option.id">
                                <option :value="option.id" x-text="option.name"></option>
                            </template>
                        </select>
                        <span data-select-chevron class="pointer-events-none absolute inset-y-0 right-0 flex w-10 items-center justify-center text-subtle">
                            <x-icon name="chevron-down" class="size-4" />
                        </span>
                    </div>
                    <x-ui.field-error name="position_id" />
                </div>

                <div>
                    <x-ui.label for="employment_status">Status kepegawaian <span class="text-rose-500">*</span></x-ui.label>
                    <x-ui.select name="employment_status" :options="$statuses" :selected="$employee?->employment_status ?? 'permanent'" />
                    <x-ui.field-error name="employment_status" />
                </div>

                <div>
                    <x-ui.label for="joined_at">Tanggal masuk <span class="text-rose-500">*</span></x-ui.label>
                    <x-ui.input
                        type="date"
                        name="joined_at"
                        x-model="joinedAt"
                        x-on:change="if (!isEditing || !nik) { generateStaffNumber(); }"
                        :value="$employee?->joined_at?->format('Y-m-d') ?? now()->format('Y-m-d')"
                    />
                    <x-ui.field-error name="joined_at" />
                </div>

                <div class="flex items-end pb-2.5">
                    <x-ui.checkbox name="is_active" label="Karyawan aktif" :checked="$employee?->is_active ?? true" />
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Identitas" description="Data pribadi dan dokumen resmi karyawan.">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <div class="flex items-center justify-between">
                        <x-ui.label for="nik">No. staff <span class="text-rose-500">*</span></x-ui.label>
                        <button
                            type="button"
                            x-show="companyId"
                            x-on:click="generateStaffNumber(true)"
                            class="text-[11.5px] font-semibold text-brand hover:underline inline-flex items-center gap-1"
                            :disabled="isGeneratingNik"
                        >
                            <x-icon name="refresh" class="size-3" x-bind:class="isGeneratingNik ? 'animate-spin' : ''" />
                            <span x-text="isGeneratingNik ? 'Menghasilkan…' : 'Generate No. Staff'"></span>
                        </button>
                    </div>
                    <div class="relative">
                        <input
                            type="text"
                            name="nik"
                            id="nik"
                            x-model="nik"
                            x-on:input="nikTouched = true"
                            placeholder="KODEPT-TAHUNJOIN.BULANJOIN.NOMORURUT (contoh: MAL006-2026.11.001)"
                            class="h-11 w-full rounded-xl border border-line bg-surface px-3.5 text-[14px] font-mono font-medium text-ink shadow-sm outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/15"
                            required
                        >
                    </div>
                    <p class="mt-1 text-[11px] text-subtle">
                        Format: <span class="font-mono font-semibold text-brand">KODEPT-TAHUNJOIN.BULANJOIN.NOMORURUT</span> (contoh: <code class="font-mono text-brand">MAL006-2026.11.001</code>)
                    </p>
                    <x-ui.field-error name="nik" />
                </div>

                <div>
                    <x-ui.label for="name">Nama lengkap <span class="text-rose-500">*</span></x-ui.label>
                    <x-ui.input name="name" :value="$employee?->name" placeholder="Nama sesuai KTP" />
                    <x-ui.field-error name="name" />
                </div>

                <div>
                    <x-ui.label for="gender">Jenis kelamin</x-ui.label>
                    <x-ui.select name="gender" :options="['pria' => 'Pria', 'wanita' => 'Wanita']" :selected="$employee?->gender" placeholder="Pilih jenis kelamin" data-select2-clear />
                    <x-ui.field-error name="gender" />
                </div>

                <div>
                    <x-ui.label for="religion">Agama</x-ui.label>
                    <x-ui.select
                        name="religion"
                        :options="['Islam' => 'Islam', 'Kristen' => 'Kristen', 'Katolik' => 'Katolik', 'Hindu' => 'Hindu', 'Buddha' => 'Buddha', 'Konghucu' => 'Konghucu']"
                        :selected="$employee?->religion"
                        placeholder="Pilih agama"
                        data-select2-clear
                    />
                    <x-ui.field-error name="religion" />
                </div>

                <div>
                    <x-ui.label for="birth_place">Tempat lahir</x-ui.label>
                    <x-ui.input name="birth_place" :value="$employee?->birth_place" placeholder="Jakarta" />
                    <x-ui.field-error name="birth_place" />
                </div>

                <div>
                    <x-ui.label for="birth_date">Tanggal lahir</x-ui.label>
                    <x-ui.input type="date" name="birth_date" :value="$employee?->birth_date?->format('Y-m-d')" />
                    <x-ui.field-error name="birth_date" />
                </div>

                <div>
                    <x-ui.label for="marital_status">Status pernikahan</x-ui.label>
                    <x-ui.input name="marital_status" :value="$employee?->marital_status" placeholder="Menikah / Belum menikah / Cerai" />
                    <x-ui.field-error name="marital_status" />
                </div>

                <div>
                    <x-ui.label for="ktp_no">No. KTP</x-ui.label>
                    <x-ui.input name="ktp_no" :value="$employee?->ktp_no" placeholder="16 digit" />
                    <x-ui.field-error name="ktp_no" />
                </div>

                <div>
                    <x-ui.label for="npwp_no">No. NPWP</x-ui.label>
                    <x-ui.input name="npwp_no" :value="$employee?->npwp_no" placeholder="00.000.000.0-000.000" />
                    <x-ui.field-error name="npwp_no" />
                </div>

                <div>
                    <x-ui.label for="bpjs_tk_no">No. BPJS TK</x-ui.label>
                    <x-ui.input name="bpjs_tk_no" :value="$employee?->bpjs_tk_no" placeholder="11 digit" />
                    <x-ui.field-error name="bpjs_tk_no" />
                </div>

                <div>
                    <x-ui.label for="bpjs_kes_no">BPJS Kes</x-ui.label>
                    <x-ui.input name="bpjs_kes_no" :value="$employee?->bpjs_kes_no" placeholder="13 digit" />
                    <x-ui.field-error name="bpjs_kes_no" />
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Kontrak" description="Isi bila karyawan terikat kontrak kerja. Kontrak I dan II boleh dikosongkan.">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-ui.label for="contract_1_start">Kontrak I mulai</x-ui.label>
                    <x-ui.input type="date" name="contract_1_start" :value="$employee?->contract_1_start?->format('Y-m-d')" />
                    <x-ui.field-error name="contract_1_start" />
                </div>
                <div>
                    <x-ui.label for="contract_1_end">Kontrak I berakhir</x-ui.label>
                    <x-ui.input type="date" name="contract_1_end" :value="$employee?->contract_1_end?->format('Y-m-d')" />
                    <x-ui.field-error name="contract_1_end" />
                </div>
                <div>
                    <x-ui.label for="contract_2_start">Kontrak II mulai</x-ui.label>
                    <x-ui.input type="date" name="contract_2_start" :value="$employee?->contract_2_start?->format('Y-m-d')" />
                    <x-ui.field-error name="contract_2_start" />
                </div>
                <div>
                    <x-ui.label for="contract_2_end">Kontrak II berakhir</x-ui.label>
                    <x-ui.input type="date" name="contract_2_end" :value="$employee?->contract_2_end?->format('Y-m-d')" />
                    <x-ui.field-error name="contract_2_end" />
                </div>
                <div class="sm:col-span-2">
                    <x-ui.label for="note">Ket</x-ui.label>
                    <x-ui.input name="note" :value="$employee?->note" placeholder="Catatan tambahan, mis. perpanjangan otomatis" />
                    <x-ui.field-error name="note" />
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Kontak & alamat" description="Email wajib agar karyawan bisa dihubungi.">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-ui.label for="phone">No handphone</x-ui.label>
                    <x-ui.input name="phone" :value="$employee?->phone" placeholder="0812xxxxxxx" />
                    <x-ui.field-error name="phone" />
                </div>
                <div>
                    <x-ui.label for="email">Email <span class="text-rose-500">*</span></x-ui.label>
                    <x-ui.input type="email" name="email" :value="$employee?->email" placeholder="nama@perusahaan.com" />
                    <x-ui.field-error name="email" />
                </div>
                <div class="sm:col-span-2">
                    <x-ui.label for="address">Alamat tempat tinggal</x-ui.label>
                    <x-ui.textarea name="address" rows="3" :value="$employee?->address" placeholder="Jalan, nomor, kelurahan, kota" />
                    <x-ui.field-error name="address" />
                </div>
                <div>
                    <x-ui.label for="emergency_phone">No tlp darurat</x-ui.label>
                    <x-ui.input name="emergency_phone" :value="$employee?->emergency_phone" placeholder="0813xxxxxxx" />
                    <x-ui.field-error name="emergency_phone" />
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Keluarga" description="Data pasangan, orang tua, serta jumlah dan nama anak.">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-ui.label for="spouse_name">Nama istri / suami</x-ui.label>
                    <x-ui.input name="spouse_name" :value="$employee?->spouse_name" placeholder="Nama pasangan" />
                    <x-ui.field-error name="spouse_name" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-ui.label for="daughters_count">Jml. anak perempuan</x-ui.label>
                        <x-ui.input type="number" name="daughters_count" min="0" max="20" :value="$employee?->daughters_count ?? 0" />
                        <x-ui.field-error name="daughters_count" />
                    </div>
                    <div>
                        <x-ui.label for="sons_count">Jml. anak laki-laki</x-ui.label>
                        <x-ui.input type="number" name="sons_count" min="0" max="20" :value="$employee?->sons_count ?? 0" />
                        <x-ui.field-error name="sons_count" />
                    </div>
                </div>
                <div>
                    <x-ui.label for="daughters_names">Nama anak perempuan</x-ui.label>
                    <x-ui.input name="daughters_names" :value="$employee?->daughters_names" placeholder="Pisahkan dengan koma, mis. Sherly, Sheria" />
                    <x-ui.field-error name="daughters_names" />
                </div>
                <div>
                    <x-ui.label for="sons_names">Nama anak laki-laki</x-ui.label>
                    <x-ui.input name="sons_names" :value="$employee?->sons_names" placeholder="Pisahkan dengan koma, mis. Alfredo, Carlson" />
                    <x-ui.field-error name="sons_names" />
                </div>
                <div>
                    <x-ui.label for="father_name">Nama ayah</x-ui.label>
                    <x-ui.input name="father_name" :value="$employee?->father_name" placeholder="Nama ayah kandung" />
                    <x-ui.field-error name="father_name" />
                </div>
                <div>
                    <x-ui.label for="mother_name">Nama ibu</x-ui.label>
                    <x-ui.input name="mother_name" :value="$employee?->mother_name" placeholder="Nama ibu kandung" />
                    <x-ui.field-error name="mother_name" />
                </div>
            </div>
        </x-ui.card>

        <div class="flex flex-wrap items-center gap-2">
            <x-ui.button type="submit">{{ $isEditing ? 'Simpan perubahan' : 'Tambah karyawan' }}</x-ui.button>
            <x-ui.button variant="ghost" :href="$isEditing ? route('employees.show', $employee) : route('employees.index')">Batal</x-ui.button>
        </div>
    </form>
</x-layouts.app>
