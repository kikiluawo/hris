@props(['title' => null, 'description' => null, 'image' => null])

@php
    $fullTitle = $title ? $title . ' · Majesty Talent Portal' : 'Majesty Talent Portal';
    $metaDescription = $description ?? 'Rekrutmen resmi Majesty Group. Temukan posisi yang sesuai dan kirim lamaran 100% online tanpa perlu membuat akun.';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $fullTitle }}</title>
    <x-layouts.head-meta :title="$fullTitle" :description="$metaDescription" :image="$image" site-name="Majesty Talent Portal" />

    <script>
        (() => {
            const stored = localStorage.getItem('theme') ?? 'system';
            const dark = stored === 'dark' || (stored === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full">
    <div class="flex min-h-screen flex-col">
        <header class="sticky top-0 z-30 border-b border-gold/15 bg-[#06231b] text-white">
            <div class="mx-auto flex h-[72px] w-full max-w-[1160px] items-center gap-4 px-4 sm:px-6 lg:px-8">
                <a href="{{ route('career.index') }}" class="group flex items-center gap-3" aria-label="Beranda Majesty Talent Portal">
                    <img
                        src="{{ asset('images/majesty-logo.png') }}"
                        alt="Majesty Group"
                        class="h-7 w-auto object-contain opacity-95 transition group-hover:opacity-100 sm:h-9"
                    />
                    <span class="h-5 w-px bg-gold/30 sm:h-6" aria-hidden="true"></span>
                    <span class="text-[9px] font-semibold uppercase tracking-[0.22em] text-gold/85 sm:text-[10px] sm:tracking-[0.26em]">
                        Talent Portal
                    </span>
                </a>

                <nav class="ml-auto flex items-center gap-2">
                    <a href="{{ route('career.index') }}" class="hidden items-center gap-2 rounded-full px-4 py-2 text-[12.5px] font-semibold text-white/70 transition hover:bg-white/5 hover:text-white sm:inline-flex">
                        Lowongan
                    </a>
                </nav>
            </div>
            <div class="h-px w-full bg-gradient-to-r from-transparent via-gold/40 to-transparent"></div>
        </header>

        <main class="flex-1">
            @isset($hero)
                {{ $hero }}
            @endisset

            <div class="px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
                <div class="mx-auto w-full max-w-[1160px]">
                    {{ $slot }}
                </div>
            </div>
        </main>

        <footer class="border-t border-gold/15 bg-[#04150f] text-white/60">
            <div class="mx-auto flex w-full max-w-[1160px] flex-col gap-8 px-4 py-10 sm:px-6 lg:flex-row lg:items-start lg:justify-between lg:px-8">
                <div class="max-w-sm">
                    <img
                        src="{{ asset('images/majesty-logo.png') }}"
                        alt="Majesty Group"
                        class="h-10 w-auto object-contain opacity-95"
                    />
                    <p class="mt-2.5 text-[10px] font-semibold uppercase tracking-[0.26em] text-gold/80">Talent Portal</p>
                    <p class="mt-2.5 text-[12.5px] leading-relaxed">Rekrutmen resmi Majesty Group. Satu pintu untuk seluruh kesempatan karier di jaringan perusahaan kami.</p>
                </div>

                <div class="flex gap-12 text-[12.5px]">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-gold/70">Karier</p>
                        <div class="mt-3 flex flex-col gap-2">
                            <a href="{{ route('career.index') }}" class="transition hover:text-white">Lowongan</a>
                            <a href="{{ route('career.index') }}#cara-melamar" class="transition hover:text-white">Cara melamar</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t border-white/5">
                <div class="mx-auto flex w-full max-w-[1160px] flex-col gap-1 px-4 py-4 text-[11.5px] sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                    <p>&copy; {{ date('Y') }} Majesty Talent Portal. Seluruh hak dilindungi.</p>
                    <p class="uppercase tracking-[0.2em] text-white/40">Rekrutmen &middot; Majesty Group</p>
                </div>
            </div>
        </footer>
    </div>

    <x-ui.confirm-modal />
</body>
</html>
