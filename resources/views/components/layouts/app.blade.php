@props(['title' => null, 'description' => null, 'image' => null])

@php
    $fullTitle = $title ? $title . ' · ' . config('app.name') : config('app.name');
    $metaDescription = $description ?? 'Majesty HR Portal — Sistem manajemen SDM dan rekrutmen internal Majesty Group.';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $fullTitle }}</title>
    <x-layouts.head-meta :title="$fullTitle" :description="$metaDescription" :image="$image" :site-name="config('app.name')" />

    <script>
        (() => {
            const stored = localStorage.getItem('theme') ?? 'system';
            const dark = stored === 'dark' || (stored === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full overflow-x-clip text-ink">
    <div
        x-data="{ get sidebarOpen() { return $store.sidebar.open; }, set sidebarOpen(v) { $store.sidebar.open = v; } }"
        @keydown.escape.window="$store.sidebar.close()"
        class="min-h-screen overflow-x-clip"
    >
        <x-layouts.sidebar />

        <div class="flex min-h-screen flex-col lg:pl-[264px]">
            <x-layouts.topbar />

            <main class="flex-1 px-4 pb-10 pt-5 sm:px-7 lg:px-10 lg:pt-8">
                <div class="mx-auto w-full max-w-[1180px] animate-rise">
                    {{ $slot }}
                </div>
            </main>

            <footer class="flex flex-col gap-1 border-t border-line/70 px-5 py-5 text-[12px] text-subtle sm:flex-row sm:items-center sm:justify-between sm:px-8 lg:px-10">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Seluruh hak dilindungi.</p>
                <p>Dibangun dengan Laravel 12</p>
            </footer>
        </div>

        <div
            x-show="$store.sidebar.open"
            x-cloak
            x-transition:enter="transition-opacity ease-out duration-250"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="$store.sidebar.close()"
            class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm lg:hidden"
            aria-hidden="true"
        ></div>

        <x-ui.confirm-modal />

        @stack('modals')

        @if (session('status'))
            <div
                x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 4500)"
                x-show="show"
                x-cloak
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0 -translate-y-2"
                class="fixed left-1/2 top-5 z-[60] flex w-[calc(100%-2rem)] max-w-sm -translate-x-1/2 items-start gap-3 rounded-xl border border-brand-line bg-brand-soft px-4 py-3 text-[13px] text-brand-soft-ink shadow-pop"
            >
                <x-icon name="check-circle" class="mt-0.5 size-[18px] shrink-0" />
                <p class="min-w-0 flex-1">{{ session('status') }}</p>
                <button type="button" @click="show = false" class="ml-1 transition hover:opacity-70" aria-label="Tutup notifikasi">
                    <x-icon name="close" class="size-4" />
                </button>
            </div>
        @endif
    </div>
</body>
</html>
