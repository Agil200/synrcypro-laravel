<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SYNRCYPRO')</title>
    <meta name="description" content="SYNRCYPRO monitoring and operations dashboard">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
</head>
<body class="@yield('body-class')">
    @yield('content')
    <script src="{{ asset('assets/js/dashboard.js') }}" defer></script>
</body>
</html>
