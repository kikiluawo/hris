<x-layouts.app title="Lowongan">
    <x-ui.page-header
        title="Lowongan"
        description="Buat lowongan, lalu bagikan link publiknya atau unduh gambar lowongannya untuk Instagram, WhatsApp, atau media sosial lain. Pelamar mengisi formulir dan mengunggah CV melalui link tersebut."
    >
        <x-slot:actions>
            <x-ui.button :href="route('hiring-requests.index')" variant="secondary" icon="bell">Permintaan karyawan</x-ui.button>
            <x-ui.button :href="route('applicants.index')" variant="secondary" icon="users">Lihat pelamar</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if (session('error'))
        <x-ui.alert variant="danger" class="mb-4">{{ session('error') }}</x-ui.alert>
    @endif

    @if ($errors->any())
        <x-ui.alert variant="danger" class="mb-4">
            <ul class="list-disc space-y-0.5 pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    @php
        $startStep = $errors->hasAny(['title', 'company_id', 'department_id', 'position_id', 'description'])
            ? 1
            : ($errors->hasAny(['required_skills', 'min_education', 'min_experience', 'screening_threshold']) ? 2 : 1);
    @endphp

    <div class="space-y-4">
        <x-ui.card
            :title="$editing ? 'Edit lowongan' : 'Buat lowongan'"
            description="Isi tiga langkah singkat. Link publik dibuat otomatis dari judul dan tetap sama walaupun judul diubah."
        >
            <form
                method="POST"
                action="{{ $editing ? route('openings.update', $editing) : route('openings.store') }}"
                class="space-y-5"
                novalidate
                @keydown.enter="if (step < total && $event.target.matches('input:not(.select2-search__field)')) { $event.preventDefault(); next(); }"
                x-data="{
                    step: {{ $startStep }},
                    total: 3,
                    review: { title: '', company: '—', department: '—', position: '—', skills: [], education: '—', experience: '—', threshold: '70' },
                    init() {
                        if (this.step === 3) this.$nextTick(() => this.buildReview());
                        this.$nextTick(() => this.refreshSelects());
                    },
                    refreshSelects() {
                        const panel = this.$refs['panel' + this.step];
                        if (!panel) return;
                        const ids = [...panel.querySelectorAll('select[data-select2-init]')]
                            .map((select) => select.id)
                            .filter(Boolean);
                        if (ids.length) window.refreshSelect2Soon(ids);
                    },
                    field(name) {
                        const el = this.$root.querySelector('[name=\'' + name + '\']');
                        if (!el) return '';
                        if (el.tagName === 'SELECT') {
                            return el.selectedOptions.length ? el.selectedOptions[0].text.trim() : '';
                        }
                        return el.value.trim();
                    },
                    clean(value) {
                        return !value || /^(Pilih|Tanpa|Tidak)/.test(value) ? '—' : value;
                    },
                    next() {
                        const missing = [...this.$refs['panel' + this.step].querySelectorAll('[data-required]')]
                            .find((el) => !el.value.trim());
                        if (missing) {
                            missing.setCustomValidity('Kolom ini wajib diisi.');
                            missing.reportValidity();
                            missing.addEventListener('input', () => missing.setCustomValidity(''), { once: true });
                            return;
                        }
                        if (this.step < this.total) this.step++;
                        if (this.step === 3) this.$nextTick(() => this.buildReview());
                        this.$nextTick(() => this.refreshSelects());
                    },
                    back() {
                        this.step = Math.max(this.step - 1, 1);
                        this.$nextTick(() => this.refreshSelects());
                    },
                    buildReview() {
                        const experience = this.field('min_experience');
                        this.review = {
                            title: this.field('title') || 'Belum diisi',
                            company: this.clean(this.field('company_id')),
                            department: this.clean(this.field('department_id')),
                            position: this.clean(this.field('position_id')),
                            skills: this.field('required_skills').split(/[,\n;]+/).map((s) => s.trim()).filter(Boolean),
                            education: this.clean(this.field('min_education')),
                            experience: experience ? experience + ' tahun' : '—',
                            threshold: (this.field('screening_threshold') || '70') + '%',
                        };
                    },
                }"
            >
                @csrf
                @if ($editing)
                    @method('PATCH')
                @endif

                <div class="flex flex-wrap items-center gap-x-2 gap-y-2">
                    @foreach ([1 => 'Informasi', 2 => 'Kualifikasi', 3 => 'Publikasi'] as $number => $label)
                        <div class="flex items-center gap-2">
                            <span
                                class="flex size-6 items-center justify-center rounded-full text-[11px] font-bold transition-colors"
                                :class="step >= {{ $number }} ? 'bg-brand text-brand-ink' : 'bg-surface-2 text-subtle'"
                            >{{ $number }}</span>
                            <span class="text-[12px] font-semibold" :class="step === {{ $number }} ? 'text-ink' : 'text-subtle'">{{ $label }}</span>
                            @if (! $loop->last)
                                <span class="mx-0.5 h-px w-5 bg-line sm:w-8"></span>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div x-ref="panel1" x-show="step === 1" class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-ui.label for="title">Judul lowongan</x-ui.label>
                            <x-ui.input name="title" :value="$editing?->title" data-required placeholder="Accounting Staff" />
                            <x-ui.field-error name="title" />
                        </div>

                        <div>
                            <x-ui.label for="company_id">PT</x-ui.label>
                            <x-ui.select
                                name="company_id"
                                :options="$companies->all()"
                                :selected="$editing?->company_id"
                                placeholder="Tanpa PT tertentu"
                                data-select2-clear
                            />
                            <x-ui.field-error name="company_id" />
                        </div>

                        <div>
                            <x-ui.label for="department_id">Departemen</x-ui.label>
                            <x-ui.select
                                name="department_id"
                                :options="$departments->all()"
                                :selected="$editing?->department_id"
                                placeholder="Pilih departemen"
                                data-select2-clear
                            />
                            <x-ui.field-error name="department_id" />
                        </div>

                        <div>
                            <x-ui.label for="position_id">Jabatan</x-ui.label>
                            <x-ui.select
                                name="position_id"
                                :options="$positions->all()"
                                :selected="$editing?->position_id"
                                placeholder="Pilih jabatan"
                                data-select2-clear
                            />
                            <x-ui.field-error name="position_id" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-ui.label for="description">Deskripsi & tanggung jawab</x-ui.label>
                            <x-ui.textarea name="description" :value="$editing?->description" :rows="7" data-required placeholder="Tuliskan tanggung jawab, jam kerja, dan informasi lain yang perlu diketahui pelamar." />
                            <x-ui.field-error name="description" />
                        </div>
                    </div>
                </div>

                <div x-ref="panel2" x-show="step === 2" x-cloak class="space-y-4">
                    <div class="rounded-2xl border border-line bg-surface-2/40 px-4 py-3 text-[12.5px] leading-relaxed text-muted">
                        Kualifikasi ini dipakai untuk menyaring pelamar secara otomatis. Kosongkan jika tidak ingin membatasi pelamar.
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-ui.label for="required_skills">Keahlian wajib</x-ui.label>
                            <x-ui.input
                                name="required_skills"
                                :value="$editing?->required_skills ? implode(', ', $editing->required_skills) : null"
                                placeholder="Microsoft Excel, Akuntansi, Teliti"
                            />
                            <p class="mt-1 text-[11.5px] text-subtle">Pisahkan dengan koma. Maksimal 20 keahlian.</p>
                            <x-ui.field-error name="required_skills" />
                        </div>

                        <div>
                            <x-ui.label for="min_education">Pendidikan minimum</x-ui.label>
                            <x-ui.select
                                name="min_education"
                                :options="$educationLevels"
                                :selected="$editing?->min_education"
                                placeholder="Tidak ditentukan"
                                data-select2-clear
                            />
                            <x-ui.field-error name="min_education" />
                        </div>

                        <div>
                            <x-ui.label for="min_experience">Pengalaman minimum (tahun)</x-ui.label>
                            <x-ui.input type="number" name="min_experience" :value="$editing?->min_experience" min="0" max="50" placeholder="0" />
                            <x-ui.field-error name="min_experience" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-ui.label for="screening_threshold">Ambang lolos screening (%)</x-ui.label>
                            <x-ui.input type="number" name="screening_threshold" :value="$editing?->screening_threshold ?? 70" min="0" max="100" placeholder="70" />
                            <p class="mt-1 text-[11.5px] text-subtle">Pelamar dengan skor CV di bawah angka ini tidak diloloskan otomatis. Default 70.</p>
                            <x-ui.field-error name="screening_threshold" />
                        </div>
                    </div>
                </div>

                <div x-ref="panel3" x-show="step === 3" x-cloak class="space-y-4">
                    <div class="rounded-2xl border border-line bg-surface-2/40 p-5">
                        <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-subtle">Ringkasan lowongan</p>
                        <p class="mt-2 text-[17px] font-semibold leading-snug" x-text="review.title"></p>
                        <p class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-[12.5px] text-muted">
                            <span x-text="review.company"></span>
                            <span class="text-subtle">·</span>
                            <span x-text="review.department + ' / ' + review.position"></span>
                        </p>

                        <div x-show="review.skills.length" class="mt-3 flex flex-wrap gap-1.5">
                            <template x-for="skill in review.skills" :key="skill">
                                <span class="rounded-full border border-line bg-surface px-2.5 py-1 text-[12px] font-medium text-muted" x-text="skill"></span>
                            </template>
                        </div>
                        <p x-show="!review.skills.length" class="mt-3 text-[12px] text-subtle">Keahlian belum diisi.</p>

                        <dl class="mt-3 grid gap-2 text-[12.5px] sm:grid-cols-2">
                            <div class="flex items-center justify-between gap-3 rounded-xl border border-line bg-surface px-3 py-2">
                                <dt class="text-subtle">Pendidikan</dt>
                                <dd class="font-semibold" x-text="review.education"></dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 rounded-xl border border-line bg-surface px-3 py-2">
                                <dt class="text-subtle">Pengalaman</dt>
                                <dd class="font-semibold" x-text="review.experience"></dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 rounded-xl border border-line bg-surface px-3 py-2 sm:col-span-2">
                                <dt class="text-subtle">Ambang lolos screening</dt>
                                <dd class="font-semibold" x-text="review.threshold"></dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-2xl border border-line p-4">
                        <x-ui.checkbox name="is_open" label="Lowongan langsung dibuka untuk pelamar" :checked="$editing?->is_open ?? true" />
                        <p class="mt-1.5 text-[11.5px] text-subtle">Jika tidak dicentang, lowongan tersimpan sebagai ditutup dan tidak tampil di halaman karir.</p>
                    </div>

                    <p class="flex items-center gap-1.5 text-[12px] text-subtle">
                        <x-icon name="link" class="size-3.5" />
                        Link publik dibuat otomatis dari judul, misalnya /karir/accounting-staff.
                    </p>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line pt-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-ui.button variant="ghost" x-show="step > 1" x-cloak x-on:click="back()" icon="chevron-left">Kembali</x-ui.button>
                        @if ($editing)
                            <x-ui.button variant="ghost" x-show="step === 1" x-cloak :href="route('openings.index')" icon="close">Batal</x-ui.button>
                        @else
                            <span x-show="step === 1" class="text-[12px] text-subtle">Isi data lowongan terlebih dahulu.</span>
                        @endif
                    </div>

                    <div class="ml-auto flex items-center gap-3">
                        <p class="text-[12px] text-subtle">Langkah <span class="font-semibold text-ink" x-text="step"></span> dari 3</p>
                        <x-ui.button x-show="step < 3" x-on:click="next()">Lanjut <x-icon name="chevron-right" class="size-4" /></x-ui.button>
                        <x-ui.button x-show="step === 3" x-cloak type="submit" icon="check">{{ $editing ? 'Simpan perubahan' : 'Buat lowongan' }}</x-ui.button>
                    </div>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card :padded="false">
            <div class="flex items-center justify-between px-5 py-4 sm:px-6">
                <div>
                    <h2 class="text-[15px] font-semibold">Daftar lowongan</h2>
                    <p class="mt-0.5 text-[12.5px] text-muted">{{ $openings->count() }} lowongan · {{ $openings->where('is_open', true)->count() }} dibuka</p>
                </div>
                <x-icon name="file-text" class="size-5 text-subtle" />
            </div>

            <div class="overflow-x-auto border-t border-line">
                <table class="w-full min-w-[1100px] text-left">
                    <thead class="bg-surface-2/60">
                        <tr class="text-[10.5px] uppercase tracking-[0.08em] text-subtle">
                            <th class="px-5 py-3 font-semibold">Lowongan</th>
                            <th class="px-3 py-3 font-semibold">PT</th>
                            <th class="px-2 py-3 font-semibold">Departemen / Jabatan</th>
                            <th class="px-2 py-3 font-semibold">Status</th>
                            <th class="px-2 py-3 text-center font-semibold">Pelamar</th>
                            <th class="px-2 py-3 font-semibold">Bagikan</th>
                            <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($openings as $opening)
                            @php
                                $poster = [
                                    'title' => $opening->title,
                                    'slug' => $opening->slug,
                                    'company' => $opening->company?->name ?? 'Majesty Group',
                                    'department' => $opening->department?->name,
                                    'position' => $opening->position?->name,
                                    'education' => $opening->min_education,
                                    'experience' => $opening->min_experience,
                                    'skills' => $opening->required_skills ?? [],
                                    'opened' => $opening->created_at->translatedFormat('d M Y'),
                                    'url' => route('career.show', $opening->slug),
                                    'isOpen' => $opening->is_open,
                                ];
                            @endphp
                            <tr class="border-t border-line/80 transition hover:bg-surface-2/40">
                                <td class="px-5 py-3.5">
                                    <p class="text-[13.5px] font-semibold">{{ $opening->title }}</p>
                                    <p class="mt-0.5 text-[11px] text-subtle">/karir/{{ $opening->slug }}</p>
                                </td>
                                <td class="px-3 py-3.5 text-[12.5px] text-muted">{{ $opening->company?->name ?? '—' }}</td>
                                <td class="px-2 py-3.5 text-[12.5px] text-muted">
                                    {{ $opening->department?->name ?? '—' }}
                                    <p class="text-[11px] text-subtle">{{ $opening->position?->name ?? '—' }}</p>
                                </td>
                                <td class="px-2 py-3.5">
                                    <x-ui.badge :variant="$opening->is_open ? 'success' : 'neutral'">{{ $opening->is_open ? 'Dibuka' : 'Ditutup' }}</x-ui.badge>
                                </td>
                                <td class="px-2 py-3.5 text-center text-[12.5px] tabular-nums text-muted">
                                    <a href="{{ route('applicants.index', ['job' => $opening->id]) }}" class="font-semibold text-brand hover:underline">{{ $opening->applications_count }}</a>
                                </td>
                                <td class="px-2 py-3.5">
                                    <div x-data="{ copied: false }" class="flex items-center gap-1">
                                        <x-ui.button
                                            variant="ghost"
                                            size="icon"
                                            icon="image"
                                            x-on:click="$store.posterBox.show({{ Js::from($poster) }})"
                                            aria-label="Simpan gambar {{ $opening->title }}"
                                            title="Simpan gambar untuk WhatsApp"
                                        />
                                        <x-ui.button
                                            variant="ghost"
                                            size="icon"
                                            x-on:click="navigator.clipboard.writeText('{{ route('career.show', $opening->slug) }}').then(() => { copied = true; setTimeout(() => copied = false, 1500); }).catch(() => {})"
                                            aria-label="Salin link {{ $opening->title }}"
                                            title="Salin link publik"
                                        >
                                            <x-icon name="link" class="size-4" x-show="!copied" />
                                            <x-icon name="check" class="size-4 text-brand" x-show="copied" x-cloak />
                                        </x-ui.button>
                                        <x-ui.button variant="ghost" size="icon" icon="external" :href="route('career.show', $opening->slug)" target="_blank" aria-label="Buka link publik {{ $opening->title }}" />
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <form method="POST" action="{{ route('openings.toggle', $opening) }}">
                                            @csrf
                                            @method('PATCH')
                                            <x-ui.button
                                                variant="secondary"
                                                size="sm"
                                                :icon="$opening->is_open ? 'eye-off' : 'eye'"
                                                type="submit"
                                            >
                                                {{ $opening->is_open ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </x-ui.button>
                                        </form>
                                        <x-ui.button variant="ghost" size="icon" icon="pencil" :href="route('openings.index', ['edit' => $opening->id])" aria-label="Edit {{ $opening->title }}" />
                                        <form method="POST" action="{{ route('openings.destroy', $opening) }}" data-confirm="Lowongan {{ $opening->title }} akan dihapus permanen. Tindakan ini tidak bisa dibatalkan." data-confirm-title="Hapus lowongan?" data-confirm-tone="danger" data-confirm-label="Ya, hapus">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button variant="ghost" size="icon" icon="trash" type="submit" aria-label="Hapus {{ $opening->title }}" />
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-ui.empty-state icon="file-text" title="Belum ada lowongan" description="Buat lowongan pertama, lalu bagikan link publiknya." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>

    @push('modals')
        <x-ui.poster-modal />
    @endpush
</x-layouts.app>
