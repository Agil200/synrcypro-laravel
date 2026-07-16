@extends('layouts.app')

@section('title', 'Dashboard — SYNRCYPRO')
@section('body-class', 'syn-dashboard-page')

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('assets/css/dashboard-figma.css') }}?v={{ filemtime(public_path('assets/css/dashboard-figma.css')) }}"
    >
@endpush

@section('content')

<header class="syn-dashboard-header">

    {{-- Logo aplikasi --}}
    <a
        class="syn-dashboard-brand"
        href="{{ route('dashboard') }}"
        aria-label="Dashboard SYNRCYPRO"
    >
        <img
            src="{{ asset('assets/images/synrgypro-logo.png') }}"
            alt="SYNRCYPRO Production Monitoring"
        >
    </a>

    {{-- Bagian hitam tengah header --}}
    <div class="syn-dashboard-header-fill"></div>

    {{-- Tombol kanan --}}
    <nav class="syn-dashboard-actions">

        {{-- Profil --}}
        <a
            class="syn-dashboard-icon-button"
            href="#"
            title="Profil"
            aria-label="Profil"
        >
            <img
                src="{{ asset('assets/images/images-removebg-preview.png') }}"
                alt="Profil"
            >
        </a>

        {{-- Kembali ke halaman awal --}}
        <a
            class="syn-dashboard-icon-button"
            href="{{ route('login') }}"
            title="Kembali ke halaman awal"
            aria-label="Kembali ke halaman awal"
        >
            <img
                src="{{ asset('assets/images/LOGO HOME.jpeg') }}"
                alt="Kembali"
            >
        </a>

        {{-- Logout --}}
        <form
            method="POST"
            action="{{ route('logout') }}"
            class="syn-dashboard-logout-form"
        >
            @csrf

            <button
                class="syn-dashboard-icon-button"
                type="submit"
                title="Logout"
                aria-label="Logout"
            >
                <img
                    src="{{ asset('assets/images/LOGO LOGOUT.png') }}"
                    alt="Logout"
                >
            </button>
        </form>

    </nav>
</header>

<main class="syn-dashboard-main">

    <section class="syn-dashboard-menu">

        <a class="syn-dashboard-card" href="#">
            <img
                src="{{ asset('assets/images/LOGO MANPOWER.png') }}"
                alt="Manpower"
            >

            <span>MANPOWER</span>
        </a>

        <a class="syn-dashboard-card" href="#">
            <img
                src="{{ asset('assets/images/LOGO PEOPLE DEVELOPMENT.png') }}"
                alt="People Development"
            >

            <span>PEOPLE DEVELOPMENT</span>
        </a>

        <a class="syn-dashboard-card" href="#">
            <img
                src="{{ asset('assets/images/DATABASE.png') }}"
                alt="Database"
            >

            <span>DATABASE</span>
        </a>

        <a class="syn-dashboard-card" href="#">
            <img
                src="{{ asset('assets/images/LOGO ADMIN ALL.png') }}"
                alt="Admin All"
            >

            <span>ADMIN ALL</span>
        </a>

    </section>

    <a
        class="syn-dashboard-help"
        href="mailto:it.support@ppa.co.id?subject=SYNRCYPRO%20Support"
    >
        ? Bantuan
    </a>

</main>

<footer class="syn-dashboard-footer">
    @COPYRIGHT SYNRGYPRO {{ date('Y') }}. V1.0
</footer>

@endsection
