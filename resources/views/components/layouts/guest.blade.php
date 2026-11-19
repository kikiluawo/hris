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
<body class="min-h-full bg-canvas">
    <div class="grid min-h-screen lg:grid-cols-[minmax(0,1fr)_46%]">
        <div class="flex flex-col px-6 py-7 sm:px-10 lg:px-16">
            <a href="{{ url('/') }}" class="inline-flex w-fit items-center gap-3 rounded-xl border border-gold/25 bg-[#06231b] px-4 py-2.5 shadow-sm transition hover:border-gold/50">
                <img
                    src="{{ asset('images/majesty-logo.png') }}"
                    alt="Majesty Group"
                    class="h-7 w-auto object-contain opacity-95"
                />
                <span class="h-5 w-px bg-gold/30" aria-hidden="true"></span>
                <span class="text-[10px] font-semibold uppercase tracking-[0.2em] text-gold">HR Portal</span>
            </a>

            <div class="flex flex-1 items-center justify-center py-10">
                <div class="w-full max-w-[400px] animate-rise">
                    {{ $slot }}
                </div>
            </div>

            <p class="text-[11.5px] text-subtle">&copy; {{ date('Y') }} {{ config('app.name') }}. Seluruh hak dilindungi.</p>
        </div>

        <aside class="relative hidden overflow-hidden bg-[#0a3327] lg:flex lg:flex-col">
            <div class="pattern-grid absolute inset-0 opacity-70"></div>
            <div class="absolute -left-28 top-1/4 size-[440px] rounded-full bg-emerald-400/20 blur-3xl"></div>
            <div class="absolute -right-24 bottom-0 size-[400px] rounded-full bg-lime-300/10 blur-3xl"></div>

            <div class="relative flex flex-1 flex-col justify-center px-14 xl:px-20">
                <img
                    src="{{ asset('images/majesty-logo.png') }}"
                    alt="Majesty Group"
                    class="mb-8 h-11 w-auto self-start object-contain opacity-95"
                />

                <span class="inline-flex w-fit items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1 text-[10.5px] font-semibold uppercase tracking-[0.14em] text-emerald-100/90">
                    <span class="size-1.5 rounded-full bg-emerald-300"></span>
                    People Operations
                </span>

                <h2 class="mt-7 max-w-md text-[33px] font-semibold leading-[1.16] tracking-tight text-white xl:text-[37px]">
                    Kelola tim Anda dengan tenang, tanpa spreadsheet.
                </h2>

                <p class="mt-4 max-w-sm text-[14px] leading-relaxed text-emerald-50/70">
                    Satu tempat untuk data karyawan, cuti, kehadiran, dan penggajian.
                </p>

                <ul class="mt-9 space-y-3.5">
                    @foreach (['Data karyawan terpusat dan rapi', 'Persetujuan cuti lebih cepat', 'Laporan kehadiran otomatis'] as $feature)
                        <li class="flex items-center gap-3 text-[13.5px] text-emerald-50/85">
                            <span class="flex size-5 items-center justify-center rounded-full border border-emerald-300/30 bg-emerald-300/10 text-emerald-200">
                                <x-icon name="check" class="size-3" />
                            </span>
                            {{ $feature }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="relative border-t border-white/10 px-14 py-6 text-[11.5px] text-emerald-50/45 xl:px-20">
                Majesty HR Portal · Dashboard Internal Majesty Group
            </div>
        </aside>
    </div>
</body>
</html>
