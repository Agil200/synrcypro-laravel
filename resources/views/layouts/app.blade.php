<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>@yield('title', 'SYNRGYPRO')</title>

    {{-- Favicon SYNRGYPRO --}}
    <link
        rel="icon"
        type="image/x-icon"
        href="{{ asset('favicon.ico') }}?v=2"
    >
    <link
        rel="icon"
        type="image/png"
        sizes="32x32"
        href="{{ asset('assets/images/syngypro-favicon-32.png') }}?v=2"
    >
    <link
        rel="icon"
        type="image/png"
        sizes="192x192"
        href="{{ asset('assets/images/syngypro-favicon-192.png') }}?v=2"
    >
    <link
        rel="apple-touch-icon"
        sizes="180x180"
        href="{{ asset('assets/images/syngypro-apple-touch-icon.png') }}?v=2"
    >

    {{-- CSS utama aplikasi --}}
    <link
        rel="stylesheet"
        href="{{ asset('assets/css/app.css') }}?v={{ filemtime(public_path('assets/css/app.css')) }}"
    >

    {{-- CSS dropdown profil --}}
    <link
        rel="stylesheet"
        href="{{ asset('assets/css/profile-dropdown.css') }}?v={{ filemtime(public_path('assets/css/profile-dropdown.css')) }}"
    >

    {{-- CSS khusus halaman --}}
    @stack('styles')

    {{-- Tema global --}}
    <link
        rel="stylesheet"
        href="{{ asset('assets/css/global-theme.css') }}?v={{ filemtime(public_path('assets/css/global-theme.css')) }}"
    >

    <script
        src="{{ asset('assets/js/global-theme.js') }}?v={{ filemtime(public_path('assets/js/global-theme.js')) }}"
        defer
    ></script>
</head>

<body class="@yield('body-class')">

    @yield('content')

    <script
        src="{{ asset('assets/js/profile-dropdown.js') }}?v={{ filemtime(public_path('assets/js/profile-dropdown.js')) }}"
    ></script>

    @stack('scripts')

</body>
</html>