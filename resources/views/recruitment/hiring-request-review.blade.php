<x-layouts.app :title="'Permintaan ' . $hiringRequest->reference()">
    @php
        $statusTones = ['baru' => 'info', 'direview' => 'warning', 'terbit' => 'success', 'ditolak' => 'danger'];
        $statusLabels = \App\Models\HiringRequest::statusOptions();
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
                Permintaan ini sudah terbit sebagai lowongan
                <span class="font-semibold">{{ $hiringRequest->jobOpening->title }}</span>
                pada {{ $hiringRequest->published_at?->translatedFormat('d M Y H:i') }}.
                Perubahan setelah terbit dilakukan di halaman Lowongan.
            </p>
            <div class="mt-2 flex flex-wrap gap-2">
                <x-ui.button size="sm" :href="route('career.show', $hiringRequest->jobOpening->slug)" target="_blank" icon="external">Lihat di halaman karir</x-ui.button>
                <x-ui.button size="sm" variant="secondary" :href="route('openings.index', ['edit' => $hiringRequest->jobOpening->id])" icon="pencil">Edit lowongan</x-ui.button>
            </div>
        </x-ui.alert>
    @endif

    @if ($hiringRequest->status === 'ditolak')
        <x-ui.alert variant="danger" class="mb-4">
            <p><span class="font-semibold">Permintaan ditolak.</span> {{ $hiringRequest->rejection_reason }}</p>
        </x-ui.alert>
    @endif

    <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_340px] xl:items-start">
        <div class="space-y-4">
            <x-ui.card title="Data lowongan" description="Lengkapi kekurangan dari pengaju sebelum diterbitkan ke halaman karir.">
                <form method="POST" action="{{ route('hiring-requests.update', $hiringRequest) }}" class="space-y-6" x-data="{ submitting: false }" @submit="submitting = true">
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

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-ui.label for="title">Jabatan lowongan</x-ui.label>
                            <x-ui.input name="title" :value="$hiringRequest->title" placeholder="Contoh: Service Advisor" :disabled="! $editable" />
                            <x-ui.field-error name="title" />
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

                    @if ($editable)
                        <div class="flex flex-wrap items-center justify-end gap-2 border-t border-line pt-5">
                            <x-ui.button type="submit" variant="secondary" icon="check" x-bind:disabled="submitting">Simpan perubahan</x-ui.button>
                            <x-ui.button
                                type="submit"
                                variant="gold"
                                icon="upload"
                                formaction="{{ route('hiring-requests.publish', $hiringRequest) }}"
                                data-confirm="Lowongan akan langsung tampil di halaman karir publik dan permintaan ini akan dikunci."
                                data-confirm-title="Terbitkan ke halaman karir?"
                                data-confirm-label="Ya, terbitkan"
                                x-bind:disabled="submitting"
                            >Terbitkan ke karir</x-ui.button>
                        </div>
                    @endif
                </form>
            </x-ui.card>
        </div>

        <aside class="space-y-4 xl:sticky xl:top-24">
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
                            <dt class="text-[11px] uppercase tracking-[0.08em] text-subtle">Ditinjau</dt>
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

            @if ($editable)
                <x-ui.card title="Tindakan">
                    <p class="text-[12.5px] leading-relaxed text-muted">
                        Setelah data lengkap, tekan <span class="font-semibold text-ink">Terbitkan ke karir</span> di bawah form. Lowongan langsung aktif di halaman karir dan siap menerima lamaran.
                    </p>

                    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-[12px] leading-relaxed text-amber-800 dark:border-amber-400/25 dark:bg-amber-400/10 dark:text-amber-300">
                        PT, departemen, dan jabatan internal boleh dikosongkan. Lowongan tetap bisa terbit dan tampil sebagai Majesty Group.
                    </div>

                    <form method="POST" action="{{ route('hiring-requests.reject', $hiringRequest) }}" class="mt-4" x-data="{ open: false }">
                        @csrf
                        @method('PATCH')

                        <x-ui.button type="button" variant="secondary" icon="x-circle" class="w-full" x-on:click="open = !open">Tolak permintaan</x-ui.button>

                        <div x-show="open" x-cloak class="mt-3 space-y-2">
                            <x-ui.label for="rejection_reason">Alasan penolakan</x-ui.label>
                            <x-ui.textarea name="rejection_reason" :rows="3" placeholder="Contoh: kebutuhan sudah terpenuhi lewat rekrutmen internal." />
                            <x-ui.field-error name="rejection_reason" />
                            <x-ui.button type="submit" variant="danger" size="sm" class="w-full">Kirim penolakan</x-ui.button>
                        </div>
                    </form>
                </x-ui.card>
            @endif
        </aside>
    </div>
</x-layouts.app>
