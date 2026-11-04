<x-layouts.app title="Pelamar">
    <x-ui.page-header
        title="Pelamar"
        description="Semua lamaran yang masuk lewat link lowongan publik. Unduh CV untuk ditinjau sebelum tahap berikutnya."
    >
        <x-slot:actions>
            <x-ui.button :href="route('openings.index')" variant="secondary" icon="file-text">Kelola lowongan</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="space-y-4">
        <x-ui.card>
            <form method="GET" action="{{ route('applicants.index') }}" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_260px_auto] xl:items-end">
                <div>
                    <x-ui.label for="q">Cari pelamar</x-ui.label>
                    <x-ui.input type="search" name="q" :value="request('q')" icon="search" placeholder="Nama atau email" />
                </div>

                <div>
                    <x-ui.label for="job">Lowongan</x-ui.label>
                    <x-ui.select
                        name="job"
                        :options="$openings->all()"
                        :selected="request('job')"
                        placeholder="Semua lowongan"
                        data-select2-clear
                    />
                </div>

                <div class="flex gap-2">
                    <x-ui.button type="submit" icon="filter">Terapkan</x-ui.button>
                    @if (request()->filled('q') || request()->filled('job'))
                        <x-ui.button variant="ghost" :href="route('applicants.index')">Reset</x-ui.button>
                    @endif
                </div>
            </form>
        </x-ui.card>

        <x-ui.card :padded="false">
            <div class="flex items-center justify-between px-5 py-4 sm:px-6">
                <div>
                    <h2 class="text-[15px] font-semibold">Daftar pelamar</h2>
                    <p class="mt-0.5 text-[12.5px] text-muted">{{ $applications->count() }} lamaran ditemukan</p>
                </div>
                <x-icon name="users" class="size-5 text-subtle" />
            </div>

            <div class="overflow-x-auto border-t border-line">
                <table class="w-full min-w-[1000px] text-left">
                    <thead class="bg-surface-2/60">
                        <tr class="text-[10.5px] uppercase tracking-[0.08em] text-subtle">
                            <th class="px-5 py-3 font-semibold">Pelamar</th>
                            <th class="px-3 py-3 font-semibold">Kontak</th>
                            <th class="px-2 py-3 font-semibold">Domisili</th>
                            <th class="px-2 py-3 font-semibold">Lowongan</th>
                            <th class="px-2 py-3 font-semibold">Sumber</th>
                            <th class="px-2 py-3 font-semibold">Tanggal</th>
                            <th class="px-5 py-3 text-right font-semibold">CV</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($applications as $application)
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
                                <td class="whitespace-nowrap px-3 py-3.5 text-[12.5px] text-muted">{{ $application->candidate->phone }}</td>
                                <td class="px-2 py-3.5 text-[12.5px] text-muted">
                                    {{ $application->candidate->city }}
                                    <p class="max-w-[220px] truncate text-[11px] text-subtle" title="{{ $application->candidate->address }}">{{ $application->candidate->address }}</p>
                                </td>
                                <td class="px-2 py-3.5 text-[12.5px]">
                                    <a href="{{ route('applicants.index', ['job' => $application->job_opening_id]) }}" class="font-medium text-brand hover:underline">{{ $application->jobOpening->title }}</a>
                                </td>
                                <td class="px-2 py-3.5">
                                    @if ($application->source)
                                        <x-ui.badge variant="info">{{ $application->source }}</x-ui.badge>
                                    @else
                                        <span class="text-[12.5px] text-subtle">—</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-2 py-3.5 text-[12.5px] text-muted">
                                    {{ $application->created_at->translatedFormat('d M Y') }}
                                    <p class="text-[11px] text-subtle">{{ $application->created_at->format('H:i') }}</p>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <x-ui.button variant="secondary" size="sm" icon="download" :href="route('applicants.cv', $application)">Unduh</x-ui.button>
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
        </x-ui.card>
    </div>
</x-layouts.app>
