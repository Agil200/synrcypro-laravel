@extends('layouts.app')

@section('title', 'Manpower — SYNRCYPRO')
@section('body-class', 'syn-manpower-page')

@push('styles')
<style>
    * {
        box-sizing: border-box;
    }

    body.syn-manpower-page {
        margin: 0;
        overflow: hidden;
        background: #f4f4f4;
    }

.manpower-page {
    display: grid;
    width: 100%;
    min-height: 100vh;
    grid-template-columns: 220px minmax(0, 1fr);
    grid-template-rows: 64px minmax(0, 1fr) 30px;
    background: #f4f4f4;
}

    /* =========================
       SIDEBAR
       ========================= */

    .manpower-sidebar {
        display: flex;
        grid-row: 1 / 4;
        flex-direction: column;
        background:
            linear-gradient(
                135deg,
                #eeeeee 0%,
                #c9c9c9 100%
            );
        border-right: 1px solid #bbbbbb;
    }

.manpower-sidebar-header {
    display: grid;
    min-height: 64px;
    grid-template-columns: 1fr 46px;
    background: #222;
}

    .manpower-sidebar-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4px;
    }

.manpower-sidebar-logo img {
    display: block;
    width: 72px;
    max-height: 50px;
    object-fit: contain;
}

.manpower-sidebar-toggle {
    display: grid;
    width: 46px;
    place-items: center;
    border: 0;
    border-left: 1px solid #777;
    color: #333;
    background: #f7f7f7;
    cursor: pointer;
    font-size: 24px;
}

.manpower-navigation {
    display: flex;
    flex: 1;
    flex-direction: column;
    padding-top: 8px;
    overflow-y: auto;
    overflow-x: hidden;
}

.manpower-menu-group {
    width: 100%;
}

.manpower-menu-link,
.manpower-menu-toggle {
    display: flex;
    width: 100%;
    min-height: 40px;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    border: 0;
    color: #111;
    background: transparent;
    cursor: pointer;
    font-family: inherit;
    font-size: 13px;
    font-weight: 700;
    line-height: 1.25;
    text-align: left;
    text-decoration: none;
    transition:
        background 0.2s ease,
        color 0.2s ease;
}

.manpower-menu-link:hover,
.manpower-menu-toggle:hover,
.manpower-menu-link.active,
.manpower-menu-group.is-open > .manpower-menu-toggle {
    background: rgba(255, 255, 255, 0.75);
}

.manpower-menu-icon {
    display: grid;
    width: 20px;
    height: 20px;
    flex: 0 0 20px;
    place-items: center;
}

.manpower-menu-icon img {
    display: block;
    width: 18px;
    height: 18px;
    opacity: 0.78;
    object-fit: contain;
    filter: grayscale(1) contrast(1.15);
}

.manpower-menu-label {
    min-width: 0;
    flex: 1;
}

.manpower-menu-arrow {
    display: inline-grid;
    width: 18px;
    height: 18px;
    flex: 0 0 18px;
    place-items: center;
    margin-left: auto;
    font-size: 18px;
    font-weight: 700;
    transition: transform 0.22s ease;
}

.manpower-menu-group.is-open .manpower-menu-arrow {
    transform: rotate(90deg);
}

.manpower-submenu {
    display: grid;
    grid-template-rows: 0fr;
    opacity: 0;
    transition:
        grid-template-rows 0.24s ease,
        opacity 0.2s ease;
}

.manpower-menu-group.is-open .manpower-submenu {
    grid-template-rows: 1fr;
    opacity: 1;
}

.manpower-submenu-inner {
    overflow: hidden;
    padding-bottom: 0;
    transition: padding-bottom 0.24s ease;
}

.manpower-menu-group.is-open .manpower-submenu-inner {
    padding-bottom: 5px;
}

.manpower-submenu-link {
    position: relative;
    display: flex;
    min-height: 28px;
    align-items: center;
    padding: 5px 12px 5px 44px;
    color: #222;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.3;
    text-decoration: none;
    transition:
        background 0.2s ease,
        padding-left 0.2s ease;
}

.manpower-submenu-link::before {
    position: absolute;
    left: 31px;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #333;
    content: "";
}

.manpower-submenu-link:hover,
.manpower-submenu-link.active {
    padding-left: 48px;
    background: rgba(255, 255, 255, 0.65);
}

    .manpower-sidebar-bottom {
        padding: 10px 6px 8px;
    }

    .manpower-bottom-link {
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 4px 0;
        color: #111;
        font-size: 9px;
        font-weight: 700;
        text-decoration: none;
    }

    .manpower-bottom-link.help {
        color: #111;
    }

    .manpower-bottom-link.help span:first-child {
        color: #d71920;
    }

    /* =========================
       HEADER
       ========================= */

    .manpower-header {
        display: grid;
        grid-column: 2;
        grid-row: 1;
        grid-template-columns: minmax(0, 1fr) auto;
        border-bottom: 1px solid #cfcfcf;
        background: #fff;
    }

.manpower-header-brand {
    position: relative;
    display: flex;
    overflow: hidden;
    align-items: center;
    justify-content: center;
    padding: 0 20px;
    background: linear-gradient(
        90deg,
        #1b1b1b 0%,
        #2d2d2d 48%,
        #4b2424 76%,
        #d95d20 100%
    );
}

.manpower-header-brand::after {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background: linear-gradient(
        90deg,
        transparent 0%,
        rgba(255, 255, 255, 0.03) 50%,
        rgba(255, 255, 255, 0.10) 100%
    );
    content: "";
}

.manpower-header-brand img {
    position: relative;
    z-index: 1;
    display: block;
    width: 115px;
    max-height: 44px;
    object-fit: contain;
    filter: drop-shadow(0 3px 7px rgba(0, 0, 0, 0.35));
}

.manpower-header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0 14px;
    background: #fff;
}

.manpower-header-button {
    display: inline-grid;
    width: 46px;
    height: 46px;
    flex: 0 0 46px;
    place-items: center;
    overflow: hidden;
    padding: 0;
    border: 2px solid #111;
    border-radius: 14px;
    color: #111;
    background: linear-gradient(
        180deg,
        #ffffff 0%,
        #f1f1f1 100%
    );
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.09);
    cursor: pointer;
    text-decoration: none;
    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease,
        border-color 0.2s ease;
}

.manpower-header-button:hover {
    transform: translateY(-2px);
    border-color: #444;
    box-shadow: 0 7px 15px rgba(0, 0, 0, 0.15);
}

.manpower-logout-button {
    border-color: #c71922;
    background: linear-gradient(
        180deg,
        #ffffff 0%,
        #fff0f0 100%
    );
}

    .manpower-header-button img {
        display: block;
        width: 100%;
        height: 100%;
        border-radius: inherit;
        object-fit: cover;
    }

    /* Ikon empat kotak */
    .manpower-grid-shortcut {
        border-radius: 8px;
    }

    .manpower-grid-icon {
        display: grid;
        width: 24px;
        height: 24px;
        grid-template-columns: repeat(2, 1fr);
        grid-template-rows: repeat(2, 1fr);
        gap: 3px;
    }

    .manpower-grid-icon span {
        border: 2px solid currentColor;
        border-radius: 2px;
    }

    .manpower-home-icon {
        width: 72%;
        height: 72%;
        object-fit: contain;
    }

    .manpower-profile-icon {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .manpower-logout-form {
        display: flex;
        margin: 0;
    }

    .manpower-logout-button {
        border-color: transparent;
    }

    /* =========================
       CONTENT
       ========================= */

    .manpower-content {
        grid-column: 2;
        grid-row: 2;
        min-width: 0;
        padding: 10px;
        overflow: auto;
        color: #111;
        background: #f7f7f7;
    }

    .manpower-welcome {
        margin: 0;
        font-size: 11px;
        font-weight: 700;
    }

    /* =========================
       FOOTER
       ========================= */

    .manpower-footer {
        display: flex;
        grid-column: 2;
        grid-row: 3;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: #3b3b3b;
        font-size: 7px;
        font-weight: 700;
    }

    /* Sidebar ditutup */
.manpower-page.sidebar-collapsed {
    grid-template-columns: 52px minmax(0, 1fr);
}

.manpower-page.sidebar-collapsed .manpower-sidebar-header {
    grid-template-columns: 52px;
}

.manpower-page.sidebar-collapsed .manpower-sidebar-logo,
.manpower-page.sidebar-collapsed .manpower-menu-label,
.manpower-page.sidebar-collapsed .manpower-menu-arrow,
.manpower-page.sidebar-collapsed .manpower-sidebar-bottom,
.manpower-page.sidebar-collapsed .manpower-submenu {
    display: none;
}

.manpower-page.sidebar-collapsed .manpower-sidebar-toggle {
    width: 52px;
    border-left: 0;
}

.manpower-page.sidebar-collapsed .manpower-menu-link,
.manpower-page.sidebar-collapsed .manpower-menu-toggle {
    justify-content: center;
    padding-inline: 0;
}

    .manpower-page.sidebar-collapsed .manpower-sidebar-toggle {
        width: 42px;
        border-left: 0;
    }

    .manpower-page.sidebar-collapsed .manpower-menu-link {
        justify-content: center;
        padding-inline: 0;
    }

    @media (max-width: 760px) {
        .manpower-page {
            grid-template-columns: 46px minmax(0, 1fr);
        }

        .manpower-sidebar-header {
            display: block;
        }

        .manpower-sidebar-logo,
        .manpower-menu-label,
        .manpower-menu-arrow,
        .manpower-sidebar-bottom {
            display: none;
        }

        .manpower-sidebar-toggle {
            width: 46px;
            height: 48px;
            border-left: 0;
        }

        .manpower-menu-link {
            justify-content: center;
            padding-inline: 0;
        }

        .manpower-header-actions {
            gap: 5px;
            padding: 0 5px;
        }

        .manpower-header-button {
            width: 35px;
            height: 35px;
            flex-basis: 35px;
        }
    }

    /* =====================================================
       POPUP MENU UTAMA — TURUN DARI ATAS
       ===================================================== */

    .main-menu-backdrop {
        position: fixed;
        top: 64px;
        right: 0;
        bottom: 30px;
        left: 220px;
        z-index: 900;
        visibility: hidden;
        opacity: 0;
        background: rgba(0, 0, 0, 0.48);
        pointer-events: none;
        transition:
            left 0.25s ease,
            opacity 0.30s ease,
            visibility 0.30s ease;
    }

    .main-menu-backdrop.is-open {
        visibility: visible;
        opacity: 1;
        pointer-events: auto;
    }

    .main-menu-popup {
        position: fixed;
        top: 64px;
        right: 0;
        left: 220px;
        z-index: 901;
        max-height: calc(100vh - 94px);
        padding: 24px 30px 30px;
        overflow-x: hidden;
        overflow-y: auto;
        visibility: hidden;
        opacity: 0;
        background: #ffffff;
        border-bottom: 1px solid #cccccc;
        box-shadow: 0 18px 40px rgba(0, 0, 0, 0.28);
        pointer-events: none;
        transform: translateY(-110%);
        transition:
            left 0.25s ease,
            transform 0.45s cubic-bezier(0.77, 0, 0.18, 1),
            opacity 0.25s ease,
            visibility 0.45s ease;
    }

    .main-menu-popup.is-open {
        visibility: visible;
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0);
    }

    .main-menu-popup-header {
        display: flex;
        width: 100%;
        max-width: 1050px;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin: 0 auto 22px;
        padding-bottom: 14px;
        border-bottom: 1px solid #dddddd;
    }

    .main-menu-popup-header strong {
        display: block;
        color: #111111;
        font-size: 18px;
        font-weight: 800;
        letter-spacing: 0.4px;
    }

    .main-menu-popup-header small {
        display: block;
        margin-top: 4px;
        color: #777777;
        font-size: 11px;
    }

    .main-menu-popup-close {
        display: grid;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        place-items: center;
        padding: 0 0 2px;
        border: 0;
        border-radius: 50%;
        color: #ffffff;
        background: #c71922;
        box-shadow: 0 5px 12px rgba(199, 25, 34, 0.28);
        cursor: pointer;
        font-family: Arial, sans-serif;
        font-size: 27px;
        line-height: 1;
        transition:
            transform 0.20s ease,
            background 0.20s ease;
    }

    .main-menu-popup-close:hover {
        background: #9f1017;
        transform: rotate(90deg);
    }

    .main-menu-popup-close:focus-visible,
    .main-menu-popup-card:focus-visible,
    #mainMenuTrigger:focus-visible {
        outline: 3px solid rgba(224, 100, 38, 0.45);
        outline-offset: 3px;
    }

    .main-menu-popup-grid {
        display: grid;
        width: 100%;
        max-width: 1050px;
        grid-template-columns: repeat(4, minmax(140px, 1fr));
        gap: 22px;
        margin: 0 auto;
    }

    .main-menu-popup-card {
        display: flex;
        min-width: 0;
        min-height: 160px;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 14px;
        padding: 20px;
        overflow: hidden;
        border: 2px solid transparent;
        border-radius: 10px;
        color: #ffffff;
        background: #0d0b0b;
        box-shadow: 0 7px 18px rgba(0, 0, 0, 0.18);
        text-align: center;
        text-decoration: none;
        transition:
            transform 0.22s ease,
            border-color 0.22s ease,
            box-shadow 0.22s ease;
    }

    .main-menu-popup-card:hover {
        border-color: #e06426;
        box-shadow: 0 13px 25px rgba(0, 0, 0, 0.27);
        transform: translateY(-5px);
    }

    .main-menu-popup-card.active {
        border-color: #e06426;
    }

    .main-menu-popup-card img {
        display: block;
        width: 72px;
        height: 72px;
        object-fit: contain;
    }

    .main-menu-popup-card span {
        max-width: 100%;
        font-size: 14px;
        font-weight: 800;
        line-height: 1.2;
        overflow-wrap: anywhere;
    }

    /* Posisi popup mengikuti kondisi sidebar. */
    .manpower-page.sidebar-collapsed .main-menu-popup,
    .manpower-page.sidebar-collapsed .main-menu-backdrop {
        left: 52px;
    }

    @media (max-width: 900px) {
        .main-menu-popup-grid {
            grid-template-columns: repeat(2, minmax(120px, 1fr));
        }
    }

    @media (max-width: 760px) {
        .main-menu-popup,
        .main-menu-backdrop {
            left: 46px;
        }

        .main-menu-popup {
            padding: 18px 15px 25px;
        }

        .main-menu-popup-header {
            margin-bottom: 16px;
        }

        .main-menu-popup-grid {
            grid-template-columns: repeat(2, minmax(100px, 1fr));
            gap: 12px;
        }

        .main-menu-popup-card {
            min-height: 125px;
            gap: 10px;
            padding: 14px 8px;
        }

        .main-menu-popup-card img {
            width: 52px;
            height: 52px;
        }

        .main-menu-popup-card span {
            font-size: 11px;
        }
    }

    @media (max-width: 420px) {
        .main-menu-popup-grid {
            grid-template-columns: 1fr;
        }
    }

</style>
@endpush

@section('content')
<div
    class="manpower-page"
    id="manpowerPage"
>
    {{-- Sidebar --}}
    <aside class="manpower-sidebar">

        <div class="manpower-sidebar-header">
            <div class="manpower-sidebar-logo">
                <img
                    src="{{ asset('assets/images/LOGO MANPOWER.png') }}"
                    alt="Manpower"
                >
            </div>

            <button
                type="button"
                class="manpower-sidebar-toggle"
                id="sidebarToggle"
                aria-label="Buka atau tutup sidebar"
            >
                ☰
            </button>
        </div>

<nav
    class="manpower-navigation"
    aria-label="Menu Manpower"
>
    {{-- Dashboard --}}
    <a
        href="{{ route('manpower') }}"
        class="manpower-menu-link active"
    >
        <span class="manpower-menu-icon">
            <img
                src="{{ asset('assets/images/DASHBOARD.png') }}"
                alt=""
            >
        </span>

        <span class="manpower-menu-label">
            Dashboard
        </span>
    </a>

    {{-- Mine Permit --}}
    <div class="manpower-menu-group">
        <button
            type="button"
            class="manpower-menu-toggle"
            aria-expanded="false"
        >
            <span class="manpower-menu-icon">
                <img
                    src="{{ asset('assets/images/LOGO MANPOWER.png') }}"
                    alt=""
                >
            </span>

            <span class="manpower-menu-label">
                Mine Permit
            </span>

            <span
                class="manpower-menu-arrow"
                aria-hidden="true"
            >
                ›
            </span>
        </button>

        <div class="manpower-submenu">
            <div class="manpower-submenu-inner">
                <a
                    href="#"
                    class="manpower-submenu-link"
                >
                    Monitoring SHE
                </a>

                <a
                    href="#"
                    class="manpower-submenu-link"
                >
                    Monitoring Internal Upload
                </a>

                <a
                    href="#"
                    class="manpower-submenu-link"
                >
                    Monitoring Mine Permit
                </a>
            </div>
        </div>
    </div>

    {{-- Test BNN --}}
    <div class="manpower-menu-group">
        <button
            type="button"
            class="manpower-menu-toggle"
            aria-expanded="false"
        >
            <span class="manpower-menu-icon">
                <img
                    src="{{ asset('assets/images/BNN.png') }}"
                    alt=""
                >
            </span>

            <span class="manpower-menu-label">
                Test BNN
            </span>

            <span
                class="manpower-menu-arrow"
                aria-hidden="true"
            >
                ›
            </span>
        </button>

        <div class="manpower-submenu">
            <div class="manpower-submenu-inner">
                <a
                    href="#"
                    class="manpower-submenu-link"
                >
                    Daftar Test BNN
                </a>

                <a
                    href="#"
                    class="manpower-submenu-link"
                >
                    Monitoring Kehadiran
                </a>
            </div>
        </div>
    </div>

    {{-- Berita Acara Asset --}}
    <div class="manpower-menu-group">
        <button
            type="button"
            class="manpower-menu-toggle"
            aria-expanded="false"
        >
            <span class="manpower-menu-icon">
                <img
                    src="{{ asset('assets/images/BAST.png') }}"
                    alt=""
                >
            </span>

            <span class="manpower-menu-label">
                Berita Acara Asset
            </span>

            <span
                class="manpower-menu-arrow"
                aria-hidden="true"
            >
                ›
            </span>
        </button>

        <div class="manpower-submenu">
            <div class="manpower-submenu-inner">
                <a
                    href="#"
                    class="manpower-submenu-link"
                >
                    BAST Senter P101X
                </a>

                <a
                    href="#"
                    class="manpower-submenu-link"
                >
                    BAST Laser
                </a>

                <a
                    href="#"
                    class="manpower-submenu-link"
                >
                    BAST Laptop
                </a>

                <a
                    href="#"
                    class="manpower-submenu-link"
                >
                    BAST Radio HT
                </a>

                <a
                    href="#"
                    class="manpower-submenu-link"
                >
                    BAST Lainnya
                </a>
            </div>
        </div>
    </div>

    {{-- Monitoring APD --}}
    <div class="manpower-menu-group">
        <button
            type="button"
            class="manpower-menu-toggle"
            aria-expanded="false"
        >
            <span class="manpower-menu-icon">
                <img
                    src="{{ asset('assets/images/APD.png') }}"
                    alt=""
                >
            </span>

            <span class="manpower-menu-label">
                Monitoring APD
            </span>

            <span
                class="manpower-menu-arrow"
                aria-hidden="true"
            >
                ›
            </span>
        </button>

        <div class="manpower-submenu">
            <div class="manpower-submenu-inner">
                <a
                    href="#"
                    class="manpower-submenu-link"
                >
                    Pencarian
                </a>

                <a
                    href="#"
                    class="manpower-submenu-link"
                >
                    Input Pengajuan
                </a>
            </div>
        </div>
    </div>

    {{-- CC ST SP --}}
    <div class="manpower-menu-group">
        <button
            type="button"
            class="manpower-menu-toggle"
            aria-expanded="false"
        >
            <span class="manpower-menu-icon">
                <img
                    src="{{ asset('assets/images/CC,ST,SP.png') }}"
                    alt=""
                >
            </span>

            <span class="manpower-menu-label">
                CC ST SP
            </span>

            <span
                class="manpower-menu-arrow"
                aria-hidden="true"
            >
                ›
            </span>
        </button>

        <div class="manpower-submenu">
            <div class="manpower-submenu-inner">
                <a
                    href="#"
                    class="manpower-submenu-link"
                >
                    Coaching Counselling
                </a>

                <a
                    href="#"
                    class="manpower-submenu-link"
                >
                    Surat Teguran
                </a>

                <a
                    href="#"
                    class="manpower-submenu-link"
                >
                    Surat Peringatan
                </a>
            </div>
        </div>
    </div>

    {{-- MCU & FU --}}
    <div class="manpower-menu-group">
        <button
            type="button"
            class="manpower-menu-toggle"
            aria-expanded="false"
        >
            <span class="manpower-menu-icon">
                <img
                    src="{{ asset('assets/images/MCU DAN FU.png') }}"
                    alt=""
                >
            </span>

            <span class="manpower-menu-label">
                MCU &amp; FU
            </span>

            <span
                class="manpower-menu-arrow"
                aria-hidden="true"
            >
                ›
            </span>
        </button>

        <div class="manpower-submenu">
            <div class="manpower-submenu-inner">
                <a
                    href="#"
                    class="manpower-submenu-link"
                >
                    Monitoring MCU &amp; FU
                </a>
            </div>
        </div>
    </div>

    {{-- Document Out --}}
    <div class="manpower-menu-group">
        <button
            type="button"
            class="manpower-menu-toggle"
            aria-expanded="false"
        >
            <span class="manpower-menu-icon">
                <img
                    src="{{ asset('assets/images/E-ARSIP.png') }}"
                    alt=""
                >
            </span>

            <span class="manpower-menu-label">
                DOCUMENT OUT
            </span>

            <span
                class="manpower-menu-arrow"
                aria-hidden="true"
            >
                ›
            </span>
        </button>

        <div class="manpower-submenu">
            <div class="manpower-submenu-inner">
                <a
                    href="#"
                    class="manpower-submenu-link"
                >
                    Monitoring Dokumen
                </a>
            </div>
        </div>
    </div>
</nav>

        <div class="manpower-sidebar-bottom">
            <a
                href="#"
                class="manpower-bottom-link"
            >
                <span>⚙</span>
                <span>Pengaturan</span>
            </a>

            <a
                href="https://mail.google.com/mail/?view=cm&fs=1&to={{ urlencode(config('access.contact_email', 'mpe.ppaba@ppa.co.id')) }}&su=SYNRCYPRO%20Support"
                target="_blank"
                rel="noopener noreferrer"
                class="manpower-bottom-link help"
            >
                <span>?</span>
                <span>Bantuan</span>
            </a>
        </div>
    </aside>

    {{-- Header --}}
    <header class="manpower-header">

        <div class="manpower-header-brand">
            <img
                src="{{ asset('assets/images/synrgypro-logo.png') }}"
                alt="SYNRGYPRO"
            >
        </div>

        <nav
            class="manpower-header-actions"
            aria-label="Shortcut pengguna"
        >
{{-- Tombol popup menu utama --}}
<button
    type="button"
    class="manpower-header-button manpower-grid-shortcut"
    id="mainMenuTrigger"
    aria-label="Buka menu utama"
    aria-expanded="false"
    aria-controls="mainMenuPopup"
>
    <span
        class="manpower-grid-icon"
        aria-hidden="true"
    >
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </span>
</button>

            {{-- Home --}}
            <a
                href="{{ route('dashboard') }}"
                class="manpower-header-button"
                title="Dashboard"
                aria-label="Dashboard"
            >
                <img
                    class="manpower-home-icon"
                    src="{{ asset('assets/images/LOGO HOME.jpeg') }}"
                    alt=""
                >
            </a>

            {{-- Profil --}}
            <button
                type="button"
                class="manpower-header-button"
                title="Profil"
                aria-label="Profil"
            >
                @if (Auth::user()?->avatar)
                    <img
                        class="manpower-profile-icon"
                        src="{{ Auth::user()->avatar }}"
                        alt="Foto profil {{ Auth::user()->name }}"
                        referrerpolicy="no-referrer"
                    >
                @else
                    <img
                        class="manpower-profile-icon"
                        src="{{ asset('assets/images/profile.png') }}"
                        alt="Profil"
                    >
                @endif
            </button>

            {{-- Logout --}}
            <form
                method="POST"
                action="{{ route('logout') }}"
                class="manpower-logout-form"
            >
                @csrf

                <button
                    type="submit"
                    class="manpower-header-button manpower-logout-button"
                    title="Logout"
                    aria-label="Logout"
                >
                    <img
                        src="{{ asset('assets/images/LOGO LOGOUT.png') }}"
                        alt=""
                    >
                </button>
            </form>
        </nav>
    </header>

{{-- Background gelap ketika popup terbuka --}}
<div
    class="main-menu-backdrop"
    id="mainMenuBackdrop"
    aria-hidden="true"
></div>

{{-- Popup menu utama --}}
<section
    class="main-menu-popup"
    id="mainMenuPopup"
    aria-hidden="true"
    aria-label="Menu utama aplikasi"
>
    <div class="main-menu-popup-header">
        <div>
            <strong>MENU UTAMA</strong>
            <small>Pilih modul yang ingin dibuka</small>
        </div>

        <button
            type="button"
            class="main-menu-popup-close"
            id="mainMenuClose"
            aria-label="Tutup menu utama"
        >
            &times;
        </button>
    </div>

    <div class="main-menu-popup-grid">

        {{-- Manpower --}}
        <a
            href="{{ route('manpower') }}"
            class="main-menu-popup-card active"
        >
            <img
                src="{{ asset('assets/images/LOGO MANPOWER.png') }}"
                alt=""
            >

            <span>MANPOWER</span>
        </a>

        {{-- People Development --}}
        <a
            href="#"
            class="main-menu-popup-card"
        >
            <img
                src="{{ asset('assets/images/LOGO PEOPLE DEVELOPMENT.png') }}"
                alt=""
            >

            <span>PEOPLE DEVELOPMENT</span>
        </a>

        {{-- Database --}}
        <a
            href="#"
            class="main-menu-popup-card"
        >
            <img
                src="{{ asset('assets/images/DATABASE.png') }}"
                alt=""
            >

            <span>DATABASE</span>
        </a>

        {{-- Admin All --}}
        <a
            href="#"
            class="main-menu-popup-card"
        >
            <img
                src="{{ asset('assets/images/LOGO ADMIN ALL.png') }}"
                alt=""
            >

            <span>ADMIN ALL</span>
        </a>

    </div>
</section>

    {{-- Isi halaman --}}
    <main class="manpower-content">
        <p class="manpower-welcome">
            Hi, {{ Auth::user()?->name ?? 'Pengguna' }}
        </p>
    </main>

    <footer class="manpower-footer">
        @COPYRIGHT SYNRGYPRO {{ date('Y') }}. V1.0
    </footer>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const manpowerPage =
            document.getElementById('manpowerPage');

        const sidebarToggle =
            document.getElementById('sidebarToggle');

        /*
        |--------------------------------------------------------------------------
        | Buka dan tutup sidebar
        |--------------------------------------------------------------------------
        */

        if (manpowerPage && sidebarToggle) {
            sidebarToggle.addEventListener(
                'click',
                function () {
                    manpowerPage.classList.toggle(
                        'sidebar-collapsed'
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Buka dan tutup submenu
        |--------------------------------------------------------------------------
        */

        const menuToggles =
            document.querySelectorAll(
                '.manpower-menu-toggle'
            );

        menuToggles.forEach(function (menuToggle) {
            menuToggle.addEventListener(
                'click',
                function () {
                    const menuGroup =
                        menuToggle.closest(
                            '.manpower-menu-group'
                        );

                    if (!menuGroup) {
                        return;
                    }

                    const willOpen =
                        !menuGroup.classList.contains(
                            'is-open'
                        );

                    menuGroup.classList.toggle(
                        'is-open',
                        willOpen
                    );

                    menuToggle.setAttribute(
                        'aria-expanded',
                        String(willOpen)
                    );
                }
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Popup menu utama dari atas
        |--------------------------------------------------------------------------
        */

        const mainMenuTrigger =
            document.getElementById('mainMenuTrigger');

        const mainMenuPopup =
            document.getElementById('mainMenuPopup');

        const mainMenuBackdrop =
            document.getElementById('mainMenuBackdrop');

        const mainMenuClose =
            document.getElementById('mainMenuClose');

        function setMainMenuState(open) {
            if (
                !mainMenuTrigger ||
                !mainMenuPopup ||
                !mainMenuBackdrop
            ) {
                return;
            }

            mainMenuPopup.classList.toggle(
                'is-open',
                open
            );

            mainMenuBackdrop.classList.toggle(
                'is-open',
                open
            );

            mainMenuTrigger.setAttribute(
                'aria-expanded',
                String(open)
            );

            mainMenuPopup.setAttribute(
                'aria-hidden',
                String(!open)
            );

            mainMenuBackdrop.setAttribute(
                'aria-hidden',
                String(!open)
            );
        }

        if (
            mainMenuTrigger &&
            mainMenuPopup &&
            mainMenuBackdrop
        ) {
            mainMenuTrigger.addEventListener(
                'click',
                function () {
                    const isOpen =
                        mainMenuPopup.classList.contains(
                            'is-open'
                        );

                    setMainMenuState(!isOpen);
                }
            );

            mainMenuBackdrop.addEventListener(
                'click',
                function () {
                    setMainMenuState(false);
                    mainMenuTrigger.focus();
                }
            );

            if (mainMenuClose) {
                mainMenuClose.addEventListener(
                    'click',
                    function () {
                        setMainMenuState(false);
                        mainMenuTrigger.focus();
                    }
                );
            }

            mainMenuPopup
                .querySelectorAll('.main-menu-popup-card')
                .forEach(function (menuLink) {
                    menuLink.addEventListener(
                        'click',
                        function (event) {
                            const destination =
                                menuLink.getAttribute('href');

                            /* Menu yang route-nya belum dibuat tidak menggulir halaman. */
                            if (!destination || destination === '#') {
                                event.preventDefault();
                            }

                            setMainMenuState(false);
                        }
                    );
                });

            document.addEventListener(
                'keydown',
                function (event) {
                    if (
                        event.key === 'Escape' &&
                        mainMenuPopup.classList.contains(
                            'is-open'
                        )
                    ) {
                        setMainMenuState(false);
                        mainMenuTrigger.focus();
                    }
                }
            );
        }

    });
</script>
@endpush