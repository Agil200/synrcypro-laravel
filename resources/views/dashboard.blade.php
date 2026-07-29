@extends('layouts.app')

@section('title', 'Dashboard — SYNRGYPRO')
@section('body-class', 'syn-dashboard-page')

@push('styles')
<link
    rel="stylesheet"
    href="{{ asset('assets/css/dashboard-figma.css') }}?v={{ filemtime(public_path('assets/css/dashboard-figma.css')) }}"
>

<style>
    /* =====================================================
       DROPDOWN PROFIL
       ===================================================== */

    .syn-profile-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .syn-profile-trigger {
        display: inline-flex;
        width: 54px;
        height: 54px;
        min-width: 54px;
        flex: 0 0 54px;
        align-items: center;
        justify-content: center;
        padding: 0;
        overflow: hidden;
        border: 3px solid #111;
        border-radius: 50%;
        background: #fff;
        box-sizing: border-box;
        cursor: pointer;
    }

    .syn-profile-trigger:hover,
    .syn-profile-trigger[aria-expanded="true"] {
        border-color: #d65d22;
        background: #f7f7f7;
    }

    .syn-profile-default-icon {
        width: 72%;
        height: 72%;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .syn-profile-trigger-avatar {
        display: block;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    .syn-profile-dropdown {
        position: absolute;
        top: calc(100% + 14px);
        right: 0;
        z-index: 1000;
        width: min(320px, calc(100vw - 28px));
        overflow: visible;
        border: 1px solid #d9dde3;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 18px 45px rgba(0, 0, 0, 0.22);
    }

    .syn-profile-dropdown[hidden] {
        display: none;
    }

    .syn-profile-dropdown::before {
        position: absolute;
        top: -8px;
        right: 20px;
        width: 14px;
        height: 14px;
        border-top: 1px solid #d9dde3;
        border-left: 1px solid #d9dde3;
        background: #fff;
        content: "";
        transform: rotate(45deg);
    }

    .syn-profile-card {
        position: relative;
        z-index: 1;
        overflow: hidden;
        border-radius: 12px;
        background: #fff;
    }

    .syn-profile-header {
        display: flex;
        align-items: center;
        gap: 13px;
        padding: 17px 16px;
        background: #fff;
        text-align: left;
    }

    .syn-profile-avatar {
        display: grid;
        width: 58px;
        height: 58px;
        min-width: 58px;
        place-items: center;
        overflow: hidden;
        border: 2px solid #d65d22;
        border-radius: 50%;
        background: #f5f5f5;
    }

    .syn-profile-avatar img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .syn-profile-avatar svg {
        width: 68%;
        height: 68%;
        fill: none;
        stroke: #111;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .syn-profile-identity {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .syn-profile-name {
        max-width: 100%;
        overflow: hidden;
        color: #111827;
        font-size: 15px;
        font-weight: 800;
        line-height: 1.25;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .syn-profile-nrp,
    .syn-profile-role {
        max-width: 100%;
        overflow: hidden;
        color: #6b7280;
        font-size: 12px;
        line-height: 1.35;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .syn-profile-divider {
        height: 1px;
        margin: 0;
        border: 0;
        background: #e5e7eb;
    }

    .syn-profile-menu-list {
        display: flex;
        flex-direction: column;
        gap: 3px;
        padding: 8px;
    }

    .syn-profile-menu-list form {
        width: 100%;
        margin: 0;
    }

    .syn-profile-action {
        display: flex;
        width: 100%;
        min-height: 43px;
        align-items: center;
        gap: 11px;
        padding: 10px 12px;
        border: 0;
        border-radius: 8px;
        color: #1f2937;
        background: transparent;
        cursor: pointer;
        box-sizing: border-box;
        font-family: inherit;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.2;
        text-align: left;
        text-decoration: none;
    }

    .syn-profile-action:hover,
    .syn-profile-action:focus-visible {
        color: #111827;
        background: #f1f3f5;
        outline: none;
    }

    .syn-profile-action-icon {
        display: inline-flex;
        width: 22px;
        height: 22px;
        flex: 0 0 22px;
        align-items: center;
        justify-content: center;
        color: #4b5563;
    }

    .syn-profile-action-icon svg {
        width: 19px;
        height: 19px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .syn-profile-signout {
        color: #c71922;
    }

    .syn-profile-signout .syn-profile-action-icon {
        color: #c71922;
    }

    .syn-profile-signout:hover,
    .syn-profile-signout:focus-visible {
        color: #a80f18;
        background: #fff1f2;
    }

    @media (max-width: 760px), (max-aspect-ratio: 4 / 3) {
        .syn-profile-dropdown {
            position: fixed;
            top: 92px;
            right: 14px;
            width: min(320px, calc(100vw - 28px));
        }

        .syn-profile-dropdown::before {
            display: none;
        }
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

                {{-- Dropdown profil --}}
                <div class="syn-profile-wrapper">

                    <button
                        type="button"
                        class="syn-dashboard-icon-button syn-profile-trigger"
                        id="profileTrigger"
                        title="Profil"
                        aria-label="Buka profil"
                        aria-expanded="false"
                        aria-controls="profileDropdown"
                    >
                        @if (Auth::user()?->avatar)
                            <img
                                class="syn-profile-trigger-avatar"
                                src="{{ Auth::user()->avatar }}"
                                alt="Foto profil {{ Auth::user()->name }}"
                                referrerpolicy="no-referrer"
                            >
                        @else
                            <svg
                                class="syn-profile-default-icon"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <circle cx="12" cy="8" r="4"></circle>
                                <path d="M4 21a8 8 0 0 1 16 0"></path>
                            </svg>
                        @endif
                    </button>

                    <div
                        class="syn-profile-dropdown"
                        id="profileDropdown"
                        hidden
                    >
                        <div class="syn-profile-card">

                            <div class="syn-profile-header">
                                <div class="syn-profile-avatar">
                                    @if (Auth::user()?->avatar)
                                        <img
                                            src="{{ Auth::user()->avatar }}"
                                            alt="Foto profil {{ Auth::user()->name }}"
                                            referrerpolicy="no-referrer"
                                        >
                                    @else
                                        <svg
                                            viewBox="0 0 24 24"
                                            aria-hidden="true"
                                        >
                                            <circle cx="12" cy="8" r="4"></circle>
                                            <path d="M4 21a8 8 0 0 1 16 0"></path>
                                        </svg>
                                    @endif
                                </div>

                                <div class="syn-profile-identity">
                                    <strong class="syn-profile-name">
                                        {{ Auth::user()?->name ?? 'Calvin Anggoro' }}
                                    </strong>

                                    <span class="syn-profile-nrp">
                                        NRP: {{ Auth::user()?->nrp ?? '10001' }}
                                    </span>

                                    <span class="syn-profile-role">
                                        {{ Auth::user()?->jabatan ?? Auth::user()?->role ?? 'Supervisor Produksi' }}
                                    </span>
                                </div>
                            </div>

                            <hr class="syn-profile-divider">

                            <div class="syn-profile-menu-list">
                                <a
                                    href="{{ url('/profil') }}"
                                    class="syn-profile-action"
                                >
                                    <span class="syn-profile-action-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24">
                                            <circle cx="12" cy="8" r="4"></circle>
                                            <path d="M4 21a8 8 0 0 1 16 0"></path>
                                        </svg>
                                    </span>
                                    <span>Profil Saya</span>
                                </a>

                                <a
                                    href="{{ url('/pengaturan') }}"
                                    class="syn-profile-action"
                                >
                                    <span class="syn-profile-action-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="3"></circle>
                                            <path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.12 2.12-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.03 1.56V20.3h-3v-.08a1.7 1.7 0 0 0-1.03-1.56 1.7 1.7 0 0 0-1.88.34l-.06.06-2.12-2.12.06-.06A1.7 1.7 0 0 0 7 15a1.7 1.7 0 0 0-1.56-1.03H5.3v-3h.14A1.7 1.7 0 0 0 7 9.94a1.7 1.7 0 0 0-.34-1.88L6.6 8l2.12-2.12.06.06a1.7 1.7 0 0 0 1.88.34A1.7 1.7 0 0 0 11.7 4.7v-.08h3v.08a1.7 1.7 0 0 0 1.03 1.56 1.7 1.7 0 0 0 1.88-.34l.06-.06L19.8 8l-.06.06a1.7 1.7 0 0 0-.34 1.88 1.7 1.7 0 0 0 1.56 1.03h.14v3h-.14A1.7 1.7 0 0 0 19.4 15Z"></path>
                                        </svg>
                                    </span>
                                    <span>Pengaturan Akun</span>
                                </a>

                                <a
                                    href="{{ url('/ubah-email') }}"
                                    class="syn-profile-action"
                                >
                                    <span class="syn-profile-action-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24">
                                            <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                            <path d="m3 7 9 6 9-6"></path>
                                        </svg>
                                    </span>
                                    <span>Ubah Email</span>
                                </a>

                                <form
                                    method="POST"
                                    action="{{ route('logout') }}"
                                >
                                    @csrf

                                    <button
                                        type="submit"
                                        class="syn-profile-action syn-profile-signout"
                                    >
                                        <span class="syn-profile-action-icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24">
                                                <path d="M10 17l5-5-5-5"></path>
                                                <path d="M15 12H3"></path>
                                                <path d="M15 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4"></path>
                                            </svg>
                                        </span>
                                        <span>Keluar</span>
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>

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
        /* =====================================================
           DROPDOWN PROFIL
           ===================================================== */

        const profileTrigger =
            document.getElementById('profileTrigger');

        const profileDropdown =
            document.getElementById('profileDropdown');

        function openProfile() {
            if (!profileTrigger || !profileDropdown) {
                return;
            }

            profileDropdown.hidden = false;
            profileTrigger.setAttribute('aria-expanded', 'true');
        }

        function closeProfile() {
            if (!profileTrigger || !profileDropdown) {
                return;
            }

            profileDropdown.hidden = true;
            profileTrigger.setAttribute('aria-expanded', 'false');
        }

        function toggleProfile() {
            if (!profileDropdown) {
                return;
            }

            if (profileDropdown.hidden) {
                openProfile();
            } else {
                closeProfile();
            }
        }

        if (profileTrigger && profileDropdown) {
            profileTrigger.addEventListener(
                'click',
                function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    toggleProfile();
                }
            );

            profileDropdown.addEventListener(
                'click',
                function (event) {
                    event.stopPropagation();
                }
            );
        }

        document.addEventListener(
            'click',
            function () {
                closeProfile();
            }
        );

        /* =====================================================
           TRANSISI DUA SISI
           ===================================================== */

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

            // Memaksa browser membaca posisi awal panel.
            void twoSideTransition.offsetWidth;

            requestAnimationFrame(function () {
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
                closeProfile();
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

            requestAnimationFrame(function () {
                twoSideTransition.classList.remove('is-covered');
            });

            window.setTimeout(function () {
                twoSideTransition.classList.remove('is-visible');
                twoSideTransition.setAttribute('aria-hidden', 'true');
            }, transitionDuration);
        }

        document.addEventListener(
            'keydown',
            function (event) {
                if (event.key === 'Escape') {
                    closeProfile();

                    if (profileTrigger) {
                        profileTrigger.focus();
                    }
                }
            }
        );
    });
</script>
@endpush