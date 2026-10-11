<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Surface Mine') }}</title>

        <!-- Fonts lokal (self-hosted, tanpa CDN agar tidak blocking saat offline/lamban) -->
        <link href="{{ asset('css/fonts.css') }}?v={{ @filemtime(public_path('css/fonts.css')) ?: 1 }}" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[var(--bg)] min-h-screen font-['Inter',sans-serif]">
    {{-- Cegah double-submit (tap ganda di HP saat loading lambat -> 419 Page Expired).
         Tidak mengubah fitur: hanya memblokir submit kedua, validasi & error server tetap jalan normal. --}}
    <script>
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (form.dataset.guarded === '1') { e.preventDefault(); return; }
        setTimeout(function () {
            if (e.defaultPrevented) return;
            form.dataset.guarded = '1';
            form.querySelectorAll('button[type="submit"], input[type="submit"], button:not([type])').forEach(function (b) { b.disabled = true; });
        }, 0);
    });
    </script>
        {{ $slot }}
    </body>
</html>
