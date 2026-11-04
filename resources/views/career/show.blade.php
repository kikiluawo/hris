<x-layouts.public :title="$opening->title">
    <a href="{{ route('career.index') }}" class="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-muted transition hover:text-ink">
        <x-icon name="chevron-left" class="size-4" />
        Semua lowongan
    </a>

    @if (session('error'))
        <x-ui.alert variant="danger" class="mt-4">{{ session('error') }}</x-ui.alert>
    @endif

    <div class="mt-5 grid gap-5 lg:grid-cols-[minmax(0,1fr)_400px] lg:items-start">
        <article class="rounded-2xl border border-line bg-surface p-6 shadow-card sm:p-8">
            <x-ui.badge variant="brand">{{ $opening->department?->name ?? 'Lowongan' }}</x-ui.badge>

            <h1 class="mt-3 text-[26px] font-semibold leading-tight tracking-tight sm:text-[30px]">{{ $opening->title }}</h1>

            @if ($opening->company)
                <p class="mt-2 flex items-center gap-1.5 text-[13px] text-muted">
                    <x-icon name="building" class="size-4 text-subtle" />
                    {{ $opening->company->name }}
                </p>
            @endif

            @if ($opening->position)
                <p class="mt-1 flex items-center gap-1.5 text-[13px] text-muted">
                    <x-icon name="briefcase" class="size-4 text-subtle" />
                    {{ $opening->position->name }}
                </p>
            @endif

            <div class="mt-6 border-t border-line pt-5">
                <p class="whitespace-pre-line text-[13.5px] leading-relaxed text-muted">{{ $opening->description }}</p>
            </div>
        </article>

        <aside class="lg:sticky lg:top-24">
            @if ($opening->is_open)
                <x-ui.card title="Kirim lamaran" description="Lengkapi data diri dan unggah CV Anda.">
                    @if ($errors->any())
                        <x-ui.alert variant="danger" class="mb-4">
                            <ul class="list-disc space-y-0.5 pl-4">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </x-ui.alert>
                    @endif

                    <form method="POST" action="{{ route('career.apply', $opening->slug) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="source" value="{{ request('utm_source') }}">

                        <div>
                            <x-ui.label for="name">Nama lengkap</x-ui.label>
                            <x-ui.input name="name" placeholder="Nama sesuai CV" autocomplete="name" />
                            <x-ui.field-error name="name" />
                        </div>

                        <div>
                            <x-ui.label for="email">Email</x-ui.label>
                            <x-ui.input type="email" name="email" icon="mail" placeholder="nama@email.com" autocomplete="email" />
                            <x-ui.field-error name="email" />
                        </div>

                        <div>
                            <x-ui.label for="phone">Nomor HP / WhatsApp</x-ui.label>
                            <x-ui.input type="tel" name="phone" icon="phone" placeholder="08xxxxxxxxxx" autocomplete="tel" />
                            <x-ui.field-error name="phone" />
                        </div>

                        <div>
                            <x-ui.label for="city">Kota</x-ui.label>
                            <x-ui.input name="city" placeholder="Kota domisili" autocomplete="address-level2" />
                            <x-ui.field-error name="city" />
                        </div>

                        <div>
                            <x-ui.label for="address">Alamat / domisili</x-ui.label>
                            <x-ui.textarea name="address" :rows="3" placeholder="Alamat lengkap saat ini" />
                            <x-ui.field-error name="address" />
                        </div>

                        <div>
                            <x-ui.label for="cv">CV</x-ui.label>
                            <div x-data="{ fileName: '' }">
                                <input
                                    type="file"
                                    name="cv"
                                    id="cv"
                                    accept=".pdf,.doc,.docx"
                                    class="sr-only"
                                    x-ref="cv"
                                    @change="fileName = $event.target.files[0]?.name ?? ''"
                                />
                                <label
                                    for="cv"
                                    class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-line bg-surface-2/40 px-5 py-7 text-center transition hover:border-brand/50 hover:bg-brand-soft/20"
                                >
                                    <span class="flex size-10 items-center justify-center rounded-xl bg-surface text-subtle shadow-sm">
                                        <x-icon name="upload" class="size-5" />
                                    </span>
                                    <span class="text-[13px] font-semibold" x-text="fileName || 'Pilih file CV'">Pilih file CV</span>
                                    <span class="text-[11.5px] text-subtle">PDF, DOC, atau DOCX &middot; maksimal 5 MB</span>
                                </label>
                                <x-ui.field-error name="cv" />
                            </div>
                        </div>

                        <x-ui.checkbox name="consent" label="Saya menyetujui data dan CV saya digunakan untuk proses rekrutmen." />
                        <x-ui.field-error name="consent" />

                        <x-ui.button type="submit" size="lg" class="w-full" icon="upload">Kirim lamaran</x-ui.button>
                    </form>
                </x-ui.card>
            @else
                <x-ui.card>
                    <x-ui.alert variant="warning">
                        Lowongan ini sudah ditutup dan tidak lagi menerima lamaran baru.
                    </x-ui.alert>
                    <x-ui.button :href="route('career.index')" variant="secondary" class="mt-4 w-full" icon="chevron-left">Lihat lowongan lain</x-ui.button>
                </x-ui.card>
            @endif
        </aside>
    </div>
</x-layouts.public>
