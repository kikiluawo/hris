@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title . ' · ' . config('app.name') : config('app.name') }}</title>

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
                <a href="{{ route('career.index') }}" class="group flex items-center gap-3" aria-label="Beranda karier">
                    <span class="flex size-10 items-center justify-center rounded-full border border-gold/40 bg-white/[0.04] text-gold transition group-hover:border-gold/70">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="size-[18px]" aria-hidden="true">
                            <path d="M8 6v12M16 6v12M8 12h8" />
                        </svg>
                    </span>
                    <span class="leading-tight">
                        <span class="block font-display text-[17px] font-semibold tracking-tight">HRD System</span>
                        <span class="mt-0.5 block text-[9px] font-semibold uppercase tracking-[0.32em] text-gold/80">Karier</span>
                    </span>
                </a>

                <nav class="ml-auto flex items-center gap-2">
                    <a href="{{ route('career.index') }}" class="hidden items-center gap-2 rounded-full px-4 py-2 text-[12.5px] font-semibold text-white/70 transition hover:bg-white/5 hover:text-white sm:inline-flex">
                        Lowongan
                    </a>

                    @auth
                        <x-ui.button :href="route('dashboard')" variant="glass" size="sm">
                            <x-icon name="grid" class="size-4" />
                            <span class="hidden sm:inline">Dashboard</span>
                            <span class="sr-only sm:hidden">Dashboard</span>
                        </x-ui.button>
                    @endauth
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
                    <p class="font-display text-[19px] font-semibold text-white">{{ config('app.name') }}</p>
                    <p class="mt-2 text-[12.5px] leading-relaxed">Rekrutmen resmi Majesty Group. Satu pintu untuk seluruh kesempatan karier di jaringan perusahaan kami.</p>
                </div>

                <div class="flex gap-12 text-[12.5px]">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-gold/70">Karier</p>
                        <div class="mt-3 flex flex-col gap-2">
                            <a href="{{ route('career.index') }}" class="transition hover:text-white">Lowongan</a>
                            <a href="{{ route('career.index') }}#cara-melamar" class="transition hover:text-white">Cara melamar</a>
                        </div>
                    </div>

                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-gold/70">Akun</p>
                        <div class="mt-3 flex flex-col gap-2">
                            @auth
                                <a href="{{ route('dashboard') }}" class="transition hover:text-white">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="transition hover:text-white">Masuk HR</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t border-white/5">
                <div class="mx-auto flex w-full max-w-[1160px] flex-col gap-1 px-4 py-4 text-[11.5px] sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                    <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Seluruh hak dilindungi.</p>
                    <p class="uppercase tracking-[0.2em] text-white/40">Rekrutmen &middot; Majesty Group</p>
                </div>
            </div>
        </footer>
    </div>

    <x-ui.confirm-modal />
</body>
</html>
