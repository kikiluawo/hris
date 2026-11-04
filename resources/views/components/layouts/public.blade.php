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
        <header class="sticky top-0 z-30 border-b border-line/80 bg-canvas/85 backdrop-blur-md">
            <div class="mx-auto flex h-16 w-full max-w-[1080px] items-center gap-3 px-4 sm:px-6 lg:px-8">
                <a href="{{ route('career.index') }}" class="flex items-center gap-3">
                    <span class="flex size-9 items-center justify-center rounded-xl bg-brand text-brand-ink shadow-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="size-5" aria-hidden="true">
                            <path d="M8 6v12M16 6v12M8 12h8" />
                        </svg>
                    </span>
                    <span class="leading-tight">
                        <span class="block text-[15px] font-bold tracking-tight">HRD System</span>
                        <span class="block text-[10px] font-semibold uppercase tracking-[0.14em] text-subtle">Karir</span>
                    </span>
                </a>

                <nav class="ml-auto flex items-center gap-1.5">
                    <button
                        type="button"
                        @click="$store.theme.toggle()"
                        class="flex size-10 items-center justify-center rounded-xl text-muted transition hover:bg-surface-2 hover:text-ink"
                        aria-label="Ganti tema"
                    >
                        <x-icon name="moon" class="size-[19px] dark:hidden" />
                        <x-icon name="sun" class="hidden size-[19px] dark:block" />
                    </button>

                    @auth
                        <x-ui.button :href="route('dashboard')" variant="secondary" size="sm" icon="grid">Dashboard</x-ui.button>
                    @else
                        <x-ui.button :href="route('login')" variant="secondary" size="sm">Masuk</x-ui.button>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="flex-1 px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
            <div class="mx-auto w-full max-w-[1080px]">
                {{ $slot }}
            </div>
        </main>

        <footer class="border-t border-line/70 px-4 py-6 text-[12px] text-subtle sm:px-6 lg:px-8">
            <div class="mx-auto flex w-full max-w-[1080px] flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Seluruh hak dilindungi.</p>
                <p>Rekrutmen &middot; HRD System</p>
            </div>
        </footer>
    </div>
</body>
</html>
