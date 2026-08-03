<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>@yield('title', 'SYNRGYPRO')</title>

    @php
        $faviconPath = public_path(
            'assets/images/syngypro-logo.png'
        );

        $faviconVersion = is_file($faviconPath)
            ? filemtime($faviconPath)
            : time();
    @endphp

    {{-- Favicon SYNRGYPRO --}}
<link
    rel="icon"
    type="image/png"
    href="{{ asset('assets/images/syngypro-tab-v1.png') }}?v=20260804"
>

<link
    rel="shortcut icon"
    type="image/png"
    href="{{ asset('assets/images/syngypro-tab-v1.png') }}?v=20260804"
>
    >

    <link
        rel="apple-touch-icon"
        href="{{ asset('assets/images/syngypro-logo.png') }}?v={{ $faviconVersion }}"
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