<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Surface Mine Production')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Font lokal (self-hosted, tanpa CDN agar tidak blocking saat offline/lamban) --}}
    <link href="{{ asset('css/fonts.css') }}?v={{ @filemtime(public_path('css/fonts.css')) ?: 1 }}" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-[var(--bg)] min-h-screen font-['Inter',sans-serif]">
    @yield('content')
</body>
</html>
