<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>@yield('title', 'SYNRCYPRO')</title>

    <link
        rel="stylesheet"
        href="{{ asset('assets/css/app.css') }}?v={{ filemtime(public_path('assets/css/app.css')) }}"
    >

    @stack('styles')
</head>

<body class="@yield('body-class')">
    @yield('content')

    @stack('scripts')
</body>
</html>