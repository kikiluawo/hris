@props(['title' => null, 'description' => null, 'image' => null])

@php
    $fullTitle = $title ? $title . ' · Majesty Talent Portal' : 'Majesty Talent Portal';
    $metaDescription = $description ?? 'Asesmen online resmi Majesty Talent Portal — Majesty Group.';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
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
<body class="min-h-full bg-canvas">
    {{ $slot }}
</body>
</html>
