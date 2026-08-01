@extends('layouts.app')

@section('title', 'Dashboard — SYNRGYPRO')
@section('body-class', 'syn-dashboard-page')

@push('styles')
@php
    $dashboardCssFile = public_path(
        'assets/css/dashboard-figma.css'
    );

    $dashboardCssVersion = is_file($dashboardCssFile)
        ? filemtime($dashboardCssFile)
        : time();
@endphp

<link
    rel="stylesheet"
    href="{{
        asset('assets/css/dashboard-figma.css')
    }}?v={{ $dashboardCssVersion }}"
>

<style>
    /*
     * Ukuran komponen profil khusus Dashboard.
     * Struktur, dropdown, dan perilakunya tetap berasal dari
     * components/profile-dropdown.blade.php serta file global.
     */
    .syn-dashboard-actions .syn-profile-wrapper {
        flex: 0 0 54px;
    }

    .syn-dashboard-actions .syn-profile-trigger {
        width: 54px;
        height: 54px;
        min-width: 54px;
        flex: 0 0 54px;
    }

    /* =====================================================
       TRANSISI DUA SISI SAAT MEMBUKA MENU
       ===================================================== */

    .syn-two-side-transition {
        position: fixed;
        inset: 0;
        z-index: 99999;
        visibility: hidden;
        overflow: hidden;
        pointer-events: none;
    }

    .syn-two-side-transition.is-visible {
        visibility: visible;
        pointer-events: auto;
    }

    .syn-two-side-panel {
        position: absolute;
        top: 0;
        bottom: 0;
        width: 50.1%;
        background:
            linear-gradient(
                135deg,
                #080808 0%,
                #1d1d1d 55%,
                #421d16 82%,
                #d65d22 100%
            );
        transition:
            transform 0.68s cubic-bezier(0.77, 0, 0.18, 1);
        will-change: transform;
    }

    .syn-two-side-panel-left {
        left: 0;
        transform: translateX(-100%);
    }

    .syn-two-side-panel-right {
        right: 0;
        transform: translateX(100%);
    }

    .syn-two-side-transition.is-covered
        .syn-two-side-panel-left,
    .syn-two-side-transition.is-covered
        .syn-two-side-panel-right {
        transform: translateX(0);
    }

    .syn-two-side-transition-center {
        position: absolute;
        top: 50%;
        left: 50%;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        opacity: 0;
        color: #ffffff;
        pointer-events: none;
        transform: translate(-50%, -50%) scale(0.94);
        transition:
            opacity 0.24s ease 0.2s,
            transform 0.24s ease 0.2s;
    }

    .syn-two-side-transition.is-covered
        .syn-two-side-transition-center {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }

    .syn-two-side-transition-center img {
        display: block;
        width: clamp(120px, 15vw, 190px);
        max-height: 90px;
        object-fit: contain;
        filter: drop-shadow(0 7px 16px rgba(0, 0, 0, 0.45));
    }

    .syn-two-side-transition-center span {
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 1.5px;
        text-align: center;
    }

    .syn-dashboard-card.is-transitioning {
        border-color: #e26325;
        box-shadow: 0 14px 32px rgba(0, 0, 0, 0.32);
        transform: translateY(-5px) scale(1.025);
    }

    @media (prefers-reduced-motion: reduce) {
        .syn-two-side-panel,
        .syn-two-side-transition-center {
            transition-duration: 0.01ms;
        }
    }
</style>
@endpush

@section('content')
<div class="syn-dashboard-viewport">
    <div class="syn-dashboard-frame">

        <header class="syn-dashboard-header">

            {{-- Logo SYNRGYPRO --}}
            <a
                class="syn-dashboard-brand"
                href="{{ route('dashboard') }}"
                aria-label="Dashboard SYNRGYPRO"
            >
                <img
                    src="{{ asset('assets/images/synrgypro-logo.png') }}"
                    alt="SYNRGYPRO Production Monitoring"
                >
            </a>

            <div
                class="syn-dashboard-header-fill"
                aria-hidden="true"
            ></div>

            <nav
                class="syn-dashboard-actions"
                aria-label="Menu pengguna"
            >
                {{-- Dropdown profil global --}}
                <x-profile-dropdown />

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

                {{-- Logout cepat menggunakan ikon power --}}
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

            <section
                class="syn-dashboard-menu"
                aria-label="Menu utama"
            >
                <a
                    class="syn-dashboard-card"
                    href="{{ route('manpower') }}"
                    data-two-side-transition
                >
                    <img
                        src="{{ asset('assets/images/LOGO MANPOWER.png') }}"
                        alt="Manpower"
                    >

                    <span>MANPOWER</span>
                </a>

                <a
                    class="syn-dashboard-card"
                    href="{{ route('people-development') }}"
                    data-two-side-transition
                >
                    <img
                        src="{{ asset('assets/images/LOGO PEOPLE DEVELOPMENT.png') }}"
                        alt="People Development"
                    >

                    <span>PEOPLE DEVELOPMENT</span>
                </a>

                <a
                    class="syn-dashboard-card"
                    href="{{ route('database') }}"
                    data-two-side-transition
                >
                    <img
                        src="{{ asset('assets/images/DATABASE.png') }}"
                        alt="Database"
                    >

                    <span>DATABASE</span>
                </a>

                <a
                    class="syn-dashboard-card"
                    href="{{ route('admin-all') }}"
                    data-two-side-transition
                >
                    <img
                        src="{{ asset('assets/images/LOGO ADMIN ALL.png') }}"
                        alt="Admin All"
                    >

                    <span>ADMIN ALL</span>
                </a>
            </section>

            {{-- Bantuan melalui Gmail --}}
            <a
                class="syn-dashboard-help"
                href="https://mail.google.com/mail/?view=cm&fs=1&to={{ urlencode(config('access.contact_email', 'mpe.ppaba@ppa.co.id')) }}&su=SYNRGYPRO%20Support"
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

{{-- Transisi layar dari sisi kiri dan kanan --}}
<div
    class="syn-two-side-transition"
    id="twoSideTransition"
    aria-hidden="true"
>
    <div
        class="syn-two-side-panel syn-two-side-panel-left"
        aria-hidden="true"
    ></div>

    <div
        class="syn-two-side-panel syn-two-side-panel-right"
        aria-hidden="true"
    ></div>

    <div class="syn-two-side-transition-center">
        <img
            src="{{ asset('assets/images/synrgypro-logo.png') }}"
            alt="SYNRGYPRO"
        >

        <span>MEMBUKA MENU...</span>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const twoSideTransition =
        document.getElementById('twoSideTransition');

    const transitionLinks =
        document.querySelectorAll('[data-two-side-transition]');

    const transitionDuration = 680;
    let transitionRunning = false;

    function isValidDestination(link, event) {
        const destination = link.getAttribute('href');

        return Boolean(
            destination &&
            destination !== '#' &&
            !destination.startsWith('javascript:') &&
            link.target !== '_blank' &&
            !event.ctrlKey &&
            !event.metaKey &&
            !event.shiftKey &&
            !event.altKey
        );
    }

    function runTwoSideTransition(link) {
        if (!twoSideTransition || transitionRunning) {
            window.location.href = link.href;
            return;
        }

        transitionRunning = true;
        link.classList.add('is-transitioning');

        twoSideTransition.classList.add('is-visible');
        twoSideTransition.setAttribute('aria-hidden', 'false');

        /*
         * Memaksa browser membaca posisi awal panel
         * sebelum animasi dimulai.
         */
        void twoSideTransition.offsetWidth;

        window.requestAnimationFrame(function () {
            twoSideTransition.classList.add('is-covered');
        });

        window.setTimeout(function () {
            sessionStorage.setItem(
                'synTwoSideTransition',
                'open-on-load'
            );

            window.location.href = link.href;
        }, transitionDuration);
    }

    transitionLinks.forEach(function (link) {
        link.addEventListener('click', function (event) {
            if (!isValidDestination(link, event)) {
                return;
            }

            event.preventDefault();
            runTwoSideTransition(link);
        });
    });

    /*
     * Saat kembali ke Dashboard, panel membuka kembali
     * ke sisi kiri dan kanan.
     */
    if (
        twoSideTransition &&
        sessionStorage.getItem('synTwoSideTransition') ===
            'open-on-load'
    ) {
        sessionStorage.removeItem('synTwoSideTransition');

        twoSideTransition.classList.add(
            'is-visible',
            'is-covered'
        );

        twoSideTransition.setAttribute('aria-hidden', 'false');

        void twoSideTransition.offsetWidth;

        window.requestAnimationFrame(function () {
            twoSideTransition.classList.remove('is-covered');
        });

        window.setTimeout(function () {
            twoSideTransition.classList.remove('is-visible');
            twoSideTransition.setAttribute('aria-hidden', 'true');
        }, transitionDuration);
    }
});
</script>
@endpush