@props([
    'title' => 'Majesty Talent Portal',
    'description' => 'Rekrutmen resmi Majesty Group. Satu pintu untuk seluruh kesempatan karier di jaringan perusahaan kami.',
    'image' => null,
    'siteName' => 'Majesty Talent Portal',
])

@php
    $ogImage = $image ?: asset('images/og-thumbnail.png') . '?v=1';
    $ogUrl = url()->current();
@endphp

<meta name="description" content="{{ $description }}">
<meta name="theme-color" content="#0c1e38">

<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=1">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=1">
<link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=1">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=1">

<meta property="og:type" content="website">
<meta property="og:locale" content="id_ID">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:url" content="{{ $ogUrl }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:image:type" content="image/png">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="{{ $title }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $ogImage }}">
