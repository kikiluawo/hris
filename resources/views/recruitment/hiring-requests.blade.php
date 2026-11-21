<x-layouts.app title="Permintaan Karyawan">
    <x-ui.page-header
        title="Permintaan Karyawan"
        description="Permintaan yang masuk dari atasan/unit lewat form publik. Tinjau, lengkapi, lalu terbitkan menjadi lowongan di halaman karir."
    >
        <x-slot:actions>
            <x-ui.button :href="route('openings.index')" variant="secondary" icon="file-text">Kelola lowongan</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if (session('status'))
        <x-ui.alert variant="success" class="mb-4">{{ session('status') }}</x-ui.alert>
    @endif

    @if (session('error'))
        <x-ui.alert variant="danger" class="mb-4">{{ session('error') }}</x-ui.alert>
    @endif

    <x-ui.card class="mb-4">
        <div class="flex items-start gap-3">
            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-brand-soft text-brand-soft-ink">
                <x-icon name="link" class="size-5" />
            </span>
            <div class="min-w-0 flex-1">
                <h2 class="text-[15px] font-semibold">Link form permintaan</h2>
                <p class="mt-0.5 text-[12.5px] text-muted">Satu link dipakai semua unit. Bagikan ke atasan yang butuh tambahan tenaga kerja.</p>
            </div>
        </div>

        @if ($token !== '')
            <div
                class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center"
                x-data="{
                    copied: false,
                    copy() {
                        const input = this.$refs.link;
                        const flash = () => {
                            this.copied = true;
                            setTimeout(() => { this.copied = false; }, 1800);
                        };
                        if (navigator.clipboard && window.isSecureContext) {
                            navigator.clipboard.writeText(input.value).then(flash).catch(() => {
                                input.select();
                                document.execCommand('copy');
                                flash();
                            });
                        } else {
                            input.select();
                            document.execCommand('copy');
                            flash();
                        }
                    },
                }"
            >
                <div class="min-w-0 flex-1">
                    <x-ui.input
                        name="request_link"
                        :value="route('hiring-requests.create', ['token' => $token])"
                        icon="link"
                        readonly
                        x-ref="link"
                        x-on:focus="$el.select()"
                    />
                </div>
                <div class="flex shrink-0 gap-2">
                    <x-ui.button type="button" variant="secondary" icon="copy" x-on:click="copy()">
                        <span x-text="copied ? 'Tersalin' : 'Salin link'">Salin link</span>
                    </x-ui.button>
                    <x-ui.button :href="route('hiring-requests.create', ['token' => $token])" target="_blank" variant="secondary" icon="external">Buka</x-ui.button>
                </div>
            </div>

            <div class="mt-3 flex flex-col gap-3 rounded-xl border border-line bg-surface-2/50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-[12px] leading-relaxed text-muted">Ganti token kalau link tersebar ke pihak yang salah. Link lama langsung tidak bisa dibuka.</p>
                <form
                    method="POST"
                    action="{{ route('hiring-requests.token') }}"
                    class="shrink-0"
                    data-confirm="Semua link form yang lama akan langsung tidak bisa dibuka."
                    data-confirm-title="Ganti token form?"
                    data-confirm-tone="danger"
                    data-confirm-label="Ya, ganti token"
                >
                    @csrf
                    @method('PATCH')
                    <x-ui.button type="submit" variant="secondary" size="sm" icon="refresh">Ganti token</x-ui.button>
                </form>
            </div>
        @else
            <x-ui.alert variant="warning" class="mt-4">
                Token form belum diset. Buat token untuk mengaktifkan link.
            </x-ui.alert>

            <div class="mt-3">
                <form
                    method="POST"
                    action="{{ route('hiring-requests.token') }}"
                    data-confirm="Token baru akan dibuat dan link form langsung aktif."
                    data-confirm-title="Buat token form?"
                    data-confirm-label="Buat token"
                >
                    @csrf
                    @method('PATCH')
                    <x-ui.button type="submit" variant="secondary" size="sm" icon="refresh">Buat token</x-ui.button>
                </form>
            </div>
        @endif
    </x-ui.card>

    @php
        $statusTones = [
            'baru' => 'info',
            'direview' => 'warning',
            'menunggu_approval' => 'warning',
            'terbit' => 'success',
            'ditolak' => 'danger',
        ];
    @endphp

    @if (($pendingMyApproval ?? 0) > 0)
        <div class="mb-4 rounded-2xl border border-gold/40 bg-gold/10 p-4 text-[13px] text-ink">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-gold text-[#1d150a]">
                        <x-icon name="bell" class="size-4.5" />
                    </span>
                    <div>
                        <p class="font-bold">Perhatian: {{ $pendingMyApproval }} permintaan karyawan menunggu approval Anda</p>
                        <p class="text-[12px] text-muted">Silakan tinjau dan berikan persetujuan untuk menerbitkan lowongan.</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <x-ui.button :href="route('hiring-requests.index', ['my_approval' => 1])" variant="gold" size="sm" icon="filter">
                        Tampilkan yang butuh approval saya
                    </x-ui.button>
                </div>
            </div>
        </div>
    @endif

    <div class="space-y-4">
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            <x-ui.stat-card label="Baru" :value="$counts['baru'] ?? 0" icon="bell" footnote="Belum ditinjau HRD" />
            <x-ui.stat-card label="Ditinjau" :value="$counts['direview'] ?? 0" icon="pencil" footnote="Sedang dilengkapi" />
            <x-ui.stat-card label="Menunggu Approval" :value="$counts['menunggu_approval'] ?? 0" icon="clock" footnote="CEO / CFO" />
            <x-ui.stat-card label="Terbit" :value="$counts['terbit'] ?? 0" icon="check-circle" footnote="Sudah tampil di karir" />
            <x-ui.stat-card label="Ditolak" :value="$counts['ditolak'] ?? 0" icon="x-circle" footnote="Tidak diproses" />
        </div>

        <x-ui.card>
            <form method="GET" action="{{ route('hiring-requests.index') }}" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_240px_auto] xl:items-end">
                <div>
                    <x-ui.label for="q">Cari permintaan</x-ui.label>
                    <x-ui.input type="search" name="q" :value="$filters['q'] ?? null" icon="search" placeholder="Jabatan, pengaju, atau PT" />
                </div>

                <div>
                    <x-ui.label for="status">Status</x-ui.label>
                    <x-ui.select name="status" :options="$statuses" :selected="$filters['status'] ?? null" placeholder="Semua status" data-select2-clear />
                </div>

                <div class="flex gap-2">
                    <x-ui.button type="submit" icon="filter">Terapkan</x-ui.button>
                    @if (array_filter($filters))
                        <x-ui.button variant="ghost" :href="route('hiring-requests.index')">Reset</x-ui.button>
                    @endif
                </div>
            </form>
        </x-ui.card>

        <x-ui.card :padded="false">
            <div class="flex items-center justify-between px-5 py-4 sm:px-6">
                <div>
                    <h2 class="text-[15px] font-semibold">Daftar permintaan</h2>
                    <p class="mt-0.5 text-[12.5px] text-muted">{{ $requests->total() }} permintaan ditemukan</p>
                </div>
                <x-icon name="file-text" class="size-5 text-subtle" />
            </div>

            <div class="overflow-x-auto border-t border-line">
                <table class="w-full min-w-[1040px] text-left">
                    <thead class="bg-surface-2/60">
                        <tr class="text-[10.5px] uppercase tracking-[0.08em] text-subtle">
                            <th class="px-5 py-3 font-semibold">Permintaan</th>
                            <th class="px-3 py-3 font-semibold">Pengaju</th>
                            <th class="px-3 py-3 font-semibold">PT penempatan</th>
                            <th class="px-3 py-3 font-semibold">Kualifikasi</th>
                            <th class="px-2 py-3 font-semibold">Status</th>
                            <th class="px-3 py-3 font-semibold">Masuk</th>
                            <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requests as $hiringRequest)
                            @php
                                $skills = $hiringRequest->required_skills ?? [];
                                $summary = collect([
                                    count($skills) > 0 ? count($skills) . ' skill' : null,
                                    $hiringRequest->min_education,
                                    $hiringRequest->min_experience !== null ? $hiringRequest->min_experience . ' th' : null,
                                ])->filter()->implode(' · ');
                            @endphp
                            <tr class="border-t border-line/80 align-top transition hover:bg-surface-2/40">
                                <td class="px-5 py-3.5">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-subtle">{{ $hiringRequest->reference() }}</p>
                                    <p class="mt-1 text-[13.5px] font-semibold">{{ $hiringRequest->title }}</p>
                                    <p class="mt-0.5 text-[11.5px] text-subtle">{{ $hiringRequest->headcount }} orang</p>
                                    @if ($hiringRequest->isReplacement())
                                        <div class="mt-1">
                                            <span class="inline-flex items-center gap-1 rounded-md bg-amber-500/10 px-2 py-0.5 text-[11px] font-semibold text-amber-700 dark:text-amber-300">
                                                Pergantian: {{ $hiringRequest->replaced_employee_name ?: 'Karyawan' }}
                                            </span>
                                        </div>
                                    @else
                                        <div class="mt-1">
                                            <span class="inline-flex items-center gap-1 rounded-md bg-blue-500/10 px-2 py-0.5 text-[11px] font-semibold text-blue-700 dark:text-blue-300">
                                                Karyawan Baru
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5">
                                    <p class="text-[13px] font-medium">{{ $hiringRequest->requester_name }}</p>
                                    <p class="mt-0.5 text-[11.5px] text-subtle">{{ $hiringRequest->requester_position }}</p>
                                    @if ($hiringRequest->requesting_unit)
                                        <p class="mt-0.5 text-[11px] text-subtle">{{ $hiringRequest->requesting_unit }}</p>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5 text-[12.5px]">
                                    @if ($hiringRequest->company)
                                        <span class="text-muted">{{ $hiringRequest->company->name }}</span>
                                    @else
                                        <x-ui.badge variant="warning">Belum ditentukan</x-ui.badge>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5 text-[12.5px] text-muted">
                                    {{ $summary !== '' ? $summary : '—' }}
                                </td>
                                <td class="px-2 py-3.5">
                                    @if ($hiringRequest->status === 'menunggu_approval')
                                        <x-ui.badge variant="warning">{{ $hiringRequest->approvalSummary() }}</x-ui.badge>
                                    @else
                                        <x-ui.badge :variant="$statusTones[$hiringRequest->status] ?? 'neutral'">{{ $statuses[$hiringRequest->status] ?? ucfirst($hiringRequest->status) }}</x-ui.badge>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-3.5 text-[12.5px] text-muted">
                                    {{ $hiringRequest->created_at->translatedFormat('d M Y') }}
                                    <p class="text-[11px] text-subtle">{{ $hiringRequest->created_at->format('H:i') }}</p>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <x-ui.button
                                        :href="route('hiring-requests.show', $hiringRequest)"
                                        :variant="$hiringRequest->status === 'baru' ? 'primary' : 'secondary'"
                                        size="sm"
                                        icon="chevron-right"
                                    >{{ $hiringRequest->status === 'baru' ? 'Tinjau' : 'Lihat' }}</x-ui.button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-ui.empty-state icon="file-text" title="Belum ada permintaan" description="Bagikan link form permintaan ke atasan atau unit yang membutuhkan tenaga kerja baru." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($requests->hasPages())
                <div class="border-t border-line px-5 py-4 sm:px-6">
                    {{ $requests->links() }}
                </div>
            @endif
        </x-ui.card>
    </div>
</x-layouts.app>
