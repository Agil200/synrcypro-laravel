<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>@yield('title', 'SYNRGYPRO')</title>

    {{-- CSS utama aplikasi --}}
    <link
        rel="stylesheet"
        href="{{ asset('assets/css/app.css') }}?v={{ filemtime(public_path('assets/css/app.css')) }}"
    >

    {{-- CSS dropdown profil untuk semua halaman --}}
    <link
        rel="stylesheet"
        href="{{ asset('assets/css/profile-dropdown.css') }}?v={{ filemtime(public_path('assets/css/profile-dropdown.css')) }}"
    >

    {{--
        CSS khusus dari masing-masing halaman.
        Contohnya CSS Dashboard, Manpower, dan People Development.
    --}}
    @stack('styles')

    {{--
        CSS tema global harus dipanggil setelah @stack('styles')
        supaya dapat menimpa warna bawaan setiap halaman.
    --}}
    <link
        rel="stylesheet"
        href="{{ asset('assets/css/global-theme.css') }}?v={{ filemtime(public_path('assets/css/global-theme.css')) }}"
    >

    {{--
        Membaca tema dari localStorage pada seluruh halaman.
    --}}
    <script
        src="{{ asset('assets/js/global-theme.js') }}?v={{ filemtime(public_path('assets/js/global-theme.js')) }}"
        defer
    ></script>
</head>

<body class="@yield('body-class')">
    @yield('content')

    {{-- JavaScript dropdown profil untuk semua halaman --}}
    <script
        src="{{ asset('assets/js/profile-dropdown.js') }}?v={{ filemtime(public_path('assets/js/profile-dropdown.js')) }}"
    ></script>

    {{-- JavaScript khusus masing-masing halaman --}}
    @stack('scripts')
</body>
</html>