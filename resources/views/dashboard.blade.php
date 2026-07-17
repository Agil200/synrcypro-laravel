@extends('layouts.app')

@section('title', 'Dashboard — SYNRCYPRO')
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
}

.syn-profile-default-icon {
    width: 34px;
    height: 34px;
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

    .syn-profile-default-icon {
        width: 72%;
        height: 72%;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .syn-profile-dropdown {
        position: absolute;
        top: calc(100% + 18px);
        right: 0;
        z-index: 1000;
        width: clamp(270px, 18vw, 330px);
        overflow: hidden;
        border: 1px solid #d6d6d6;
        border-radius: 5px;
        background: #fff;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.28);
    }

    .syn-profile-dropdown[hidden] {
        display: none;
    }

    .syn-profile-dropdown::before {
        position: absolute;
        top: -9px;
        right: 27px;
        width: 17px;
        height: 17px;
        border-top: 1px solid #d6d6d6;
        border-left: 1px solid #d6d6d6;
        background: #282828;
        content: "";
        transform: rotate(45deg);
    }

    .syn-profile-header {
        display: flex;
        min-height: 205px;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 24px 18px;
        color: #fff;
        background: #282828;
        text-align: center;
    }

    .syn-profile-avatar {
        display: grid;
        width: 82px;
        height: 82px;
        place-items: center;
        margin-bottom: 13px;
        overflow: hidden;
        border: 3px solid #e97820;
        border-radius: 50%;
        background: #fff;
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

    .syn-profile-header strong {
        max-width: 100%;
        overflow: hidden;
        font-size: 14px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .syn-profile-role {
        margin-top: 6px;
        color: #f4f4f4;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .syn-profile-email {
        max-width: 100%;
        margin-top: 6px;
        overflow: hidden;
        color: #bdbdbd;
        font-size: 10px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .syn-profile-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 12px;
        background: #f7f7f7;
    }

    .syn-profile-footer form {
        margin: 0;
    }

    .syn-profile-action {
        display: inline-flex;
        min-height: 34px;
        align-items: center;
        justify-content: center;
        padding: 0 14px;
        border: 1px solid #d5d5d5;
        border-radius: 2px;
        color: #666;
        background: #fff;
        cursor: pointer;
        font-family: inherit;
        font-size: 11px;
        text-decoration: none;
    }

    .syn-profile-action:hover {
        color: #111;
        border-color: #999;
        background: #eeeeee;
    }

    .syn-profile-signout {
        color: #c71922;
    }

    @media (max-width: 760px), (max-aspect-ratio: 4 / 3) {
        .syn-profile-dropdown {
            position: fixed;
            top: 92px;
            right: 14px;
            width: min(315px, calc(100vw - 28px));
        }

        .syn-profile-dropdown::before {
            display: none;
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
                aria-label="Dashboard SYNRCYPRO"
            >
                <img
                    src="{{ asset('assets/images/synrgypro-logo.png') }}"
                    alt="SYNRCYPRO Production Monitoring"
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

                            <strong>
                                {{ Auth::user()?->name ?? 'Pengguna SYNRCYPRO' }}
                            </strong>

                            <span class="syn-profile-role">
                                {{ Auth::user()?->role ?? 'Operator' }}
                            </span>

                            <small class="syn-profile-email">
                                {{ Auth::user()?->email ?? 'Email tidak tersedia' }}
                            </small>
                        </div>

                        <div class="syn-profile-footer">

                            {{-- Ganti # dengan route profil jika sudah dibuat --}}
                            <a
                                href="#"
                                class="syn-profile-action"
                            >
                                Profile
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
                                    Sign out
                                </button>
                            </form>

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
>
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

            {{-- Bantuan melalui Gmail --}}
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const profileTrigger =
            document.getElementById('profileTrigger');

        const profileDropdown =
            document.getElementById('profileDropdown');

        if (!profileTrigger || !profileDropdown) {
            return;
        }

        function openProfile() {
            profileDropdown.hidden = false;

            profileTrigger.setAttribute(
                'aria-expanded',
                'true'
            );
        }

        function closeProfile() {
            profileDropdown.hidden = true;

            profileTrigger.setAttribute(
                'aria-expanded',
                'false'
            );
        }

        function toggleProfile() {
            if (profileDropdown.hidden) {
                openProfile();
            } else {
                closeProfile();
            }
        }

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

        document.addEventListener(
            'click',
            function () {
                closeProfile();
            }
        );

        document.addEventListener(
            'keydown',
            function (event) {
                if (event.key === 'Escape') {
                    closeProfile();
                    profileTrigger.focus();
                }
            }
        );
    });
</script>
@endpush