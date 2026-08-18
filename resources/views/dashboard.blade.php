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
    /* =====================================================
       RESET & OVERRIDE TAMPILAN DASHBOARD ENTERPRISE
       ===================================================== */

    html, body {
        width: 100% !important;
        height: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow-x: hidden !important;
        background-color: #0b1120 !important;
        background-image: 
            radial-gradient(at 0% 0%, rgba(13, 148, 136, 0.15) 0px, transparent 50%),
            radial-gradient(at 100% 100%, rgba(226, 99, 37, 0.15) 0px, transparent 50%),
            radial-gradient(at 50% 50%, rgba(15, 23, 42, 1) 0px, rgba(2, 6, 23, 1) 100%) !important;
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif !important;
    }

    .syn-dashboard-viewport {
        min-height: 100vh !important;
        width: 100vw !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 24px !important;
        box-sizing: border-box !important;
    }

    .syn-dashboard-frame {
        width: 100% !important;
        max-width: 1260px !important;
        background: #ffffff !important;
        border-radius: 20px !important;
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(255, 255, 255, 0.1) !important;
        overflow: hidden !important;
        display: flex !important;
        flex-direction: column !important;
        margin: auto !important;
    }

    /* Header Bar */
    .syn-dashboard-header {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        background: #0f172a !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        padding: 0 24px 0 0 !important;
        height: 74px !important;
        width: 100% !important;
        box-sizing: border-box !important;
        gap: 16px !important;
    }

    .syn-dashboard-brand {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0 28px !important;
        height: 100% !important;
        background: #ffffff !important;
        text-decoration: none !important;
        flex-shrink: 0 !important;
        border-right: 1px solid #e2e8f0 !important;
    }

    .syn-dashboard-brand img {
        height: 40px !important;
        width: auto !important;
        object-fit: contain !important;
    }

    /* Widget Kalender & Jam Realtime di Tengah Header */
    .syn-dashboard-header-fill {
        flex: 1 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: transparent !important;
    }

    .syn-live-widget {
        display: inline-flex !important;
        align-items: center !important;
        gap: 14px !important;
        background: rgba(255, 255, 255, 0.07) !important;
        border: 1px solid rgba(255, 255, 255, 0.14) !important;
        padding: 6px 20px !important;
        border-radius: 30px !important;
        backdrop-filter: blur(8px) !important;
    }

    .syn-widget-item {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        color: #e2e8f0 !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        letter-spacing: 0.3px !important;
    }

    .syn-widget-item .widget-icon {
        font-size: 13px !important;
    }

    .syn-widget-divider {
        width: 1px !important;
        height: 16px !important;
        background: rgba(255, 255, 255, 0.2) !important;
    }

    .syn-clock-time {
        color: #38bdf8 !important;
        font-family: 'Consolas', monospace !important;
        font-size: 13px !important;
        letter-spacing: 1px !important;
    }

    /* Action Nav Buttons (Hilangkan Kotak Putih Memanjang) */
    .syn-dashboard-actions {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 10px !important;
        background: transparent !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .syn-dashboard-actions .syn-profile-wrapper,
    .syn-dashboard-actions .syn-notification-wrapper {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 0 !important;
        background: transparent !important;
    }

    .syn-dashboard-actions .syn-notification-trigger,
    .syn-dashboard-actions .syn-profile-trigger,
    .syn-dashboard-icon-button {
        width: 42px !important;
        height: 42px !important;
        min-width: 42px !important;
        min-height: 42px !important;
        border-radius: 10px !important;
        background: rgba(255, 255, 255, 0.08) !important;
        border: 1px solid rgba(255, 255, 255, 0.15) !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        padding: 0 !important;
        text-decoration: none !important;
        box-sizing: border-box !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        position: relative !important;
    }

    .syn-dashboard-actions .syn-notification-trigger:hover,
    .syn-dashboard-actions .syn-profile-trigger:hover,
    .syn-dashboard-icon-button:hover {
        background: rgba(255, 255, 255, 0.18) !important;
        border-color: rgba(255, 255, 255, 0.35) !important;
        transform: translateY(-2px) !important;
    }

    .syn-dashboard-actions .syn-notification-badge {
        position: absolute !important;
        top: -4px !important;
        right: -4px !important;
        background: #ef4444 !important;
        color: #ffffff !important;
        font-size: 10px !important;
        font-weight: 800 !important;
        padding: 2px 6px !important;
        border-radius: 10px !important;
        border: 2px solid #0f172a !important;
        line-height: 1 !important;
    }

    .syn-dashboard-icon-button img {
        width: 20px !important;
        height: 20px !important;
        object-fit: contain !important;
        display: block !important;
    }

    .syn-dashboard-logout-form {
        margin: 0 !important;
        padding: 0 !important;
        display: flex !important;
        align-items: center !important;
    }

    /* Main Area & Menu Grid */
    .syn-dashboard-main {
        padding: 56px 40px 42px !important;
        background: #f8fafc !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        box-sizing: border-box !important;
    }

    .syn-dashboard-menu {
        width: 100% !important;
        display: grid !important;
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 24px !important;
        margin-bottom: 40px !important;
        align-items: stretch !important;
    }

    /* Kartu Menu Tidak Terpotong */
    .syn-dashboard-card {
        height: auto !important;
        min-height: 210px !important;
        background: linear-gradient(160deg, #1e293b 0%, #0f172a 100%) !important;
        border: 1px solid #334155 !important;
        border-radius: 18px !important;
        padding: 36px 20px !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        text-decoration: none !important;
        box-shadow: 0 12px 30px -5px rgba(15, 23, 42, 0.18) !important;
        transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1) !important;
        position: relative !important;
        overflow: visible !important;
        box-sizing: border-box !important;
    }

    .syn-dashboard-card::before {
        content: '' !important;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        height: 3px !important;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent) !important;
    }

    .syn-dashboard-card:hover {
        transform: translateY(-8px) !important;
        box-shadow: 0 20px 35px -10px rgba(15, 23, 42, 0.35) !important;
        border-color: #0d9488 !important;
        background: linear-gradient(160deg, #243248 0%, #0f172a 100%) !important;
    }

    .syn-dashboard-card img {
        width: 64px !important;
        height: 64px !important;
        max-height: 64px !important;
        object-fit: contain !important;
        margin: 0 0 18px 0 !important;
        filter: drop-shadow(0 6px 12px rgba(0,0,0,0.35)) !important;
        transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1) !important;
        display: block !important;
    }

    .syn-dashboard-card:hover img {
        transform: scale(1.08) !important;
    }

    .syn-dashboard-card span {
        color: #ffffff !important;
        font-size: 13.5px !important;
        font-weight: 800 !important;
        letter-spacing: 0.6px !important;
        text-align: center !important;
        line-height: 1.4 !important;
        text-transform: uppercase !important;
        display: block !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Help Link */
    .syn-dashboard-help {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        color: #e11d48 !important;
        text-decoration: none !important;
        padding: 8px 20px !important;
        border-radius: 30px !important;
        background: #ffe4e6 !important;
        border: 1px solid #fecdd3 !important;
        transition: all 0.2s ease !important;
    }

    .syn-dashboard-help:hover {
        background: #fecdd3 !important;
        color: #be123c !important;
        transform: translateY(-1px) !important;
    }

    /* Footer */
    .syn-dashboard-footer {
        padding: 16px 24px !important;
        background: #ffffff !important;
        border-top: 1px solid #e2e8f0 !important;
        text-align: center !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        color: #94a3b8 !important;
        letter-spacing: 0.5px !important;
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

    @media (max-width: 992px) {
        .syn-dashboard-menu {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 16px !important;
        }
        .syn-live-widget {
            display: none !important;
        }
    }

    @media (max-width: 560px) {
        .syn-dashboard-menu {
            grid-template-columns: 1fr !important;
        }
        .syn-dashboard-main {
            padding: 30px 16px !important;
        }
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

            {{-- Widget Kalender & Jam Realtime --}}
            <div class="syn-dashboard-header-fill">
                <div class="syn-live-widget">
                    <div class="syn-widget-item">
                        <span class="widget-icon">📅</span>
                        <span id="syn-live-date">Memuat tanggal...</span>
                    </div>
                    <div class="syn-widget-divider"></div>
                    <div class="syn-widget-item">
                        <span class="widget-icon">⏰</span>
                        <span id="syn-live-clock" class="syn-clock-time">00:00:00 WIB</span>
                    </div>
                </div>
            </div>

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
    // -------------------------------------------------------------
    // REALTIME LIVE CLOCK & CALENDAR
    // -------------------------------------------------------------
    function updateLiveClock() {
        const now = new Date();
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        const dayName = days[now.getDay()];
        const dayDate = now.getDate();
        const monthName = months[now.getMonth()];
        const year = now.getFullYear();

        const dateEl = document.getElementById('syn-live-date');
        const clockEl = document.getElementById('syn-live-clock');

        if (dateEl) {
            dateEl.textContent = `${dayName}, ${dayDate} ${monthName} ${year}`;
        }

        if (clockEl) {
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            clockEl.textContent = `${h}:${m}:${s} WIB`;
        }
    }

    updateLiveClock();
    setInterval(updateLiveClock, 1000);

    // -------------------------------------------------------------
    // TRANSISI DUA SISI
    // -------------------------------------------------------------
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