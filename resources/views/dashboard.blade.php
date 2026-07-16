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
<div class="syn-dashboard-viewport">
    <div class="syn-dashboard-frame">

        <header class="syn-dashboard-header">
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

            <div class="syn-dashboard-header-fill" aria-hidden="true"></div>

            <nav class="syn-dashboard-actions" aria-label="Menu pengguna">

                {{-- Profil. Jika profile.png belum ada, ikon SVG otomatis tampil. --}}
                <a
                    class="syn-dashboard-icon-button"
                    href="#"
                    title="Profil"
                    aria-label="Profil"
                >
                    <img
                        src="{{ asset('assets/images/profile.png') }}"
                        alt=""
                        onerror="this.hidden=true; this.nextElementSibling.hidden=false;"
                    >

                    <svg
                        class="syn-dashboard-fallback-icon"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                        hidden
                    >
                        <circle cx="12" cy="8" r="4"></circle>
                        <path d="M4 21a8 8 0 0 1 16 0"></path>
                    </svg>
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
                        alt="Halaman awal"
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
            <section class="syn-dashboard-menu" aria-label="Menu utama">

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
                href="https://mail.google.com/mail/?view=cm&fs=1&to={{ urlencode(config('access.contact_email', 'mpe.ppaba@ppa.co.id')) }}&su=SYNRCYPRO%20Support"
                target="_blank"
                rel="noopener noreferrer"
            >
                ? Bantuan
            </a>
        </main>

        <footer class="syn-dashboard-footer">
            @COPYRIGHT SYNRGYPRO {{ date('Y') }}. V1.0
        </footer>

    </div>
</div>
@endsection