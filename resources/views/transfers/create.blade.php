@php
    $initialEmployee = (int) old('employee_id', $selectedEmployeeId ?: 0) ?: null;
    $currentAssignment = $initialEmployee ? $assignments->get($initialEmployee) : null;

    $initialCompany = old('to_company_id', $currentAssignment['company_id'] ?? '');
    $initialBranch = old('to_branch_id', $currentAssignment['branch_id'] ?? '');
    $initialDepartment = old('to_department_id', $currentAssignment['department_id'] ?? '');
    $initialPosition = old('to_position_id', $currentAssignment['position_id'] ?? '');
@endphp

<x-layouts.app title="Mutasi karyawan">
    <x-ui.page-header
        title="Mutasi karyawan"
        description="Pindahkan karyawan ke PT, cabang, departemen, atau jabatan lain. Perubahan langsung berlaku dan tercatat di riwayat."
    />

    @if ($errors->any())
        <x-ui.alert variant="danger" class="mb-4">
            <ul class="list-disc space-y-0.5 pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    <div
        class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_340px] lg:items-start"
        x-data="{
            organization: @js($organization),
            assignments: @js($assignments),
            employeeId: @js($initialEmployee),
            companyId: @js($initialCompany),
            branchId: @js($initialBranch),
            departmentId: @js($initialDepartment),
            positionId: @js($initialPosition),
            get current() { return this.employeeId ? this.assignments[this.employeeId] : null; },
            get currentCompany() { return this.current ? this.organization.find(c => c.id === Number(this.current.company_id)) : null; },
            get currentBranch() { return this.current?.branch_id ? this.currentCompany?.branches.find(b => b.id === Number(this.current.branch_id)) : null; },
            get currentDepartment() { return this.current ? this.currentCompany?.departments.find(d => d.id === Number(this.current.department_id)) : null; },
            get company() { return this.organization.find(c => c.id === Number(this.companyId)); },
            get branchOptions() { return this.company ? this.company.branches : []; },
            get departmentOptions() { return this.company ? this.company.departments : []; },
            get positionOptions() { return this.departmentOptions.find(d => d.id === Number(this.departmentId))?.positions ?? []; },
            findPositionName(id) {
                for (const company of this.organization) {
                    for (const department of company.departments) {
                        const position = department.positions.find(p => p.id === Number(id));
                        if (position) return position.name;
                    }
                }
                return null;
            },
            get currentPositionName() { return this.current ? this.findPositionName(this.current.position_id) : null; },
            get targetPositionName() { return this.positionId ? this.findPositionName(this.positionId) : null; },
            get positionChanged() { return this.targetPositionName !== null && this.currentPositionName !== null && this.targetPositionName !== this.currentPositionName; },
            onEmployeeChange() {
                const assignment = this.current;
                if (!assignment) return;
                this.companyId = assignment.company_id;
                this.branchId = assignment.branch_id ?? '';
                this.departmentId = assignment.department_id;
                this.positionId = assignment.position_id;
                window.refreshSelect2Soon(['to_company_id', 'to_branch_id', 'to_department_id', 'to_position_id']);
            },
            onCompanyChange() {
                this.branchId = '';
                this.departmentId = '';
                this.positionId = '';
            },
            onDepartmentChange() {
                this.positionId = '';
            }
        }"
    >
        <form method="POST" action="{{ route('transfers.store') }}" class="space-y-4">
            @csrf

            <x-ui.card title="Karyawan & tanggal" description="Pilih karyawan yang akan dimutasi dan tanggal berlakunya.">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-ui.label for="employee_id">Karyawan</x-ui.label>
                        <x-ui.select
                            name="employee_id"
                            :options="$employees->mapWithKeys(fn ($employee) => [$employee->id => $employee->name . ' · ' . $employee->company->code])->all()"
                            :selected="$initialEmployee"
                            placeholder="Pilih karyawan"
                            x-model="employeeId"
                            x-on:change="onEmployeeChange()"
                        />
                    </div>

                    <div>
                        <x-ui.label for="effective_date">Tanggal efektif</x-ui.label>
                        <x-ui.input type="date" name="effective_date" :value="old('effective_date', now()->format('Y-m-d'))" />
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card title="Tujuan mutasi" description="Cabang opsional. Departemen mengikuti PT, jabatan mengikuti departemen.">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-ui.label for="to_company_id">PT tujuan</x-ui.label>
                        <div class="relative">
                            <select
                                name="to_company_id"
                                id="to_company_id"
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
                    </div>

                    <div>
                        <x-ui.label for="to_branch_id">Cabang tujuan</x-ui.label>
                        <div class="relative">
                            <select
                                name="to_branch_id"
                                id="to_branch_id"
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
                    </div>

                    <div>
                        <x-ui.label for="to_department_id">Departemen tujuan</x-ui.label>
                        <div class="relative">
                            <select
                                name="to_department_id"
                                id="to_department_id"
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
                    </div>

                    <div>
                        <x-ui.label for="to_position_id">Jabatan tujuan</x-ui.label>
                        <div class="relative">
                            <select
                                name="to_position_id"
                                id="to_position_id"
                                x-model="positionId"
                                :disabled="!departmentId"
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
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-2 rounded-xl border border-line bg-surface-2/40 px-3.5 py-2.5" x-show="positionId" x-cloak>
                    <span class="text-[12px] font-semibold text-muted">Status jabatan:</span>
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-[11.5px] font-semibold"
                        :class="positionChanged
                            ? 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-400/25 dark:bg-amber-400/10 dark:text-amber-300'
                            : 'border-line bg-surface text-muted'"
                        x-text="positionChanged ? 'Jabatan berganti' : 'Jabatan tetap'"
                    ></span>
                    <span class="text-[12px] text-subtle" x-show="currentPositionName" x-text="'Saat ini: ' + currentPositionName"></span>
                </div>
            </x-ui.card>

            <x-ui.card title="Alasan & catatan" description="Opsional, membantu tim HR memahami konteks mutasi.">
                <div class="space-y-4">
                    <div>
                        <x-ui.label for="reason">Alasan mutasi</x-ui.label>
                        <x-ui.input name="reason" :value="old('reason')" placeholder="Contoh: kebutuhan operasional" />
                    </div>

                    <div>
                        <x-ui.label for="notes">Catatan</x-ui.label>
                        <x-ui.textarea name="notes" rows="4" :value="old('notes')" placeholder="Detail tambahan mengenai mutasi" />
                    </div>
                </div>
            </x-ui.card>

            <div class="flex flex-wrap gap-2">
                <x-ui.button type="submit" icon="transfer">Simpan mutasi</x-ui.button>
                <x-ui.button variant="ghost" :href="route('transfers.index')">Batal</x-ui.button>
            </div>
        </form>

        <aside class="space-y-4 lg:sticky lg:top-24">
            <x-ui.card title="Penempatan saat ini" description="Data sebelum mutasi disimpan.">
                <template x-if="!current">
                    <p class="text-[13px] text-muted">Pilih karyawan untuk melihat penempatan saat ini.</p>
                </template>

                <template x-if="current">
                    <dl class="space-y-3 text-[13px]">
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-subtle">PT</dt>
                            <dd class="text-right font-medium" x-text="currentCompany?.name ?? '—'"></dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-subtle">Cabang</dt>
                            <dd class="text-right font-medium" x-text="currentBranch?.name ?? 'Tanpa cabang'"></dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-subtle">Departemen</dt>
                            <dd class="text-right font-medium" x-text="currentDepartment?.name ?? '—'"></dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-subtle">Jabatan</dt>
                            <dd class="text-right font-medium" x-text="currentPositionName ?? '—'"></dd>
                        </div>
                    </dl>
                </template>
            </x-ui.card>

            <div class="rounded-2xl border border-line bg-surface-2/50 p-4 text-[12.5px] leading-relaxed text-muted">
                <p class="mb-2 flex items-center gap-2 font-semibold text-ink">
                    <x-icon name="alert-circle" class="size-4 text-brand" />
                    Cara kerja mutasi
                </p>
                <ul class="list-disc space-y-1.5 pl-4">
                    <li>Setelah disimpan, penempatan karyawan langsung berpindah.</li>
                    <li>Riwayat mencatat PT, cabang, departemen, dan jabatan sebelum & sesudah.</li>
                    <li>Status jabatan dihitung dari perbandingan nama jabatan lama dan baru.</li>
                </ul>
            </div>
        </aside>
    </div>
</x-layouts.app>
