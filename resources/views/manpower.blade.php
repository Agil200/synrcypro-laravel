@extends('layouts.app')

@section('title', 'Manpower — SYNRGYPRO')
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
       PENYEMPURNAAN UI LAPTOP
       Struktur HTML dan JavaScript tetap dipertahankan.
       ===================================================== */

    body.syn-manpower-page {
        color: #1f2937;
        background: #f3f5f7;
        font-family: Arial, Helvetica, sans-serif;
    }

    .manpower-page {
        grid-template-columns: 220px minmax(0, 1fr);
        background: #f3f5f7;
        transition: grid-template-columns 0.24s ease;
    }

    /* Sidebar lebih bersih dan nyaman dibaca di laptop */
    .manpower-sidebar {
        border-right: 1px solid #c7ccd2;
        background:
            linear-gradient(
                180deg,
                #f1f1f1 0%,
                #dddddd 100%
            );
    }

    .manpower-sidebar-header {
        grid-template-columns: minmax(0, 1fr) 52px;
        border-bottom: 1px solid #606060;
        background: #121212;
    }

    .manpower-sidebar-logo {
        padding: 5px;
        overflow: hidden;
    }

    .manpower-sidebar-logo img {
        width: 76px;
        height: 52px;
        max-height: none;
        object-fit: contain;
    }

    .manpower-sidebar-toggle {
        width: 52px;
        border-left: 1px solid #666666;
        color: #151515;
        background: #ffffff;
        font-size: 28px;
        line-height: 1;
    }

    .manpower-navigation {
        padding: 10px 0;
    }

    .manpower-menu-link,
    .manpower-menu-toggle {
        min-height: 44px;
        gap: 11px;
        padding: 10px 15px;
        border-left: 4px solid transparent;
        color: #111111;
        font-size: 13px;
        font-weight: 800;
        transition:
            background 0.18s ease,
            border-color 0.18s ease,
            color 0.18s ease;
    }

    .manpower-menu-link:hover,
    .manpower-menu-toggle:hover,
    .manpower-menu-link.active,
    .manpower-menu-group.is-open > .manpower-menu-toggle {
        background: rgba(255, 255, 255, 0.78);
    }

    .manpower-menu-link.active {
        border-left-color: #d71920;
    }

    .manpower-menu-icon {
        width: 23px;
        height: 23px;
        flex-basis: 23px;
    }

    .manpower-menu-icon img {
        width: 21px;
        height: 21px;
        opacity: 0.86;
        filter: grayscale(1) contrast(1.12);
    }

    .manpower-menu-arrow {
        width: 18px;
        height: 18px;
        flex-basis: 18px;
        font-size: 18px;
    }

    .manpower-submenu-link {
        min-height: 35px;
        padding: 7px 14px 7px 50px;
        border-left: 4px solid transparent;
        color: #2f3742;
        font-size: 12px;
        font-weight: 700;
        transition:
            background 0.18s ease,
            border-color 0.18s ease,
            color 0.18s ease;
    }

    .manpower-submenu-link::before {
        left: 34px;
        width: 6px;
        height: 6px;
        background: #444444;
    }

    .manpower-submenu-link:hover,
    .manpower-submenu-link.active {
        padding-left: 50px;
        border-left-color: #d71920;
        color: #111111;
        background: rgba(255, 255, 255, 0.90);
    }

    /* Pengaturan dan Bantuan dibuat di tengah */
    .manpower-sidebar-bottom {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
        padding: 14px 12px 18px;
    }

    .manpower-bottom-link {
        display: inline-flex;
        width: 100%;
        max-width: 155px;
        min-height: 34px;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 0 10px;
        border-radius: 8px;
        color: #111111;
        font-size: 12px;
        font-weight: 800;
        text-align: center;
        transition: background 0.18s ease;
    }

    .manpower-bottom-link:hover {
        background: rgba(255, 255, 255, 0.75);
    }

    /* Header dirapikan tanpa mengubah urutan tombol */
    .manpower-header {
        min-width: 0;
        border-bottom: 1px solid #dce2e8;
    }

    .manpower-header-brand {
        justify-content: center;
        padding: 0 18px;
    }

    .manpower-header-brand img {
        width: 125px;
        max-height: 45px;
    }

    .manpower-header-actions {
        gap: 9px;
        padding: 0 11px;
    }

    .manpower-header-button {
        width: 44px;
        height: 44px;
        flex-basis: 44px;
        border-width: 2px;
        border-radius: 50%;
        background: #ffffff;
        box-shadow: none;
    }

    .manpower-grid-shortcut {
        border-radius: 10px;
    }

    .manpower-header-button:hover {
        border-color: #333333;
        box-shadow: 0 7px 16px rgba(0, 0, 0, 0.14);
    }

    .manpower-home-icon {
        width: 72% !important;
        height: 72% !important;
        object-fit: contain !important;
    }

    .manpower-profile-icon,
    .manpower-logout-button img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
    }

    /* Area isi dibuat lebih lega dan profesional */
    .manpower-content {
        padding: 16px;
        color: #1f2937;
        background: #f3f5f7;
    }

    .manpower-welcome {
        display: inline-flex;
        min-height: 42px;
        align-items: center;
        margin: 0;
        padding: 0 15px;
        border: 1px solid #dce2e8;
        border-radius: 10px;
        color: #111827;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        font-size: 13px;
        font-weight: 800;
    }

    .manpower-footer {
        font-size: 9px;
        font-weight: 800;
    }

    /* Sidebar collapse tetap bekerja, hanya dibuat lebih konsisten */
    .manpower-page.sidebar-collapsed {
        grid-template-columns: 72px minmax(0, 1fr);
    }

    .manpower-page.sidebar-collapsed .manpower-sidebar-header {
        grid-template-columns: 72px;
    }

    .manpower-page.sidebar-collapsed .manpower-sidebar-toggle {
        width: 72px;
        border-left: 0;
    }

    .manpower-page.sidebar-collapsed .manpower-menu-link,
    .manpower-page.sidebar-collapsed .manpower-menu-toggle {
        justify-content: center;
        padding-inline: 0;
        border-left-color: transparent;
    }


    @media (max-width: 1450px) {
        .manpower-page {
            grid-template-columns: 205px minmax(0, 1fr);
        }


        .manpower-content {
            padding: 12px;
        }
    }

    @media (max-width: 900px) {
        .manpower-page {
            grid-template-columns: 72px minmax(0, 1fr);
        }

        .manpower-sidebar-header {
            grid-template-columns: 72px;
        }

        .manpower-sidebar-logo,
        .manpower-menu-label,
        .manpower-menu-arrow,
        .manpower-submenu,
        .manpower-sidebar-bottom {
            display: none;
        }

        .manpower-sidebar-toggle {
            width: 72px;
            border-left: 0;
        }

        .manpower-menu-link,
        .manpower-menu-toggle {
            justify-content: center;
            padding-inline: 0;
            border-left-color: transparent;
        }

    }
    /* Menyamakan ukuran dropdown profil dengan tombol header Manpower */
.manpower-header-actions .syn-profile-trigger {
    width: 44px;
    height: 44px;
    min-width: 44px;
    flex: 0 0 44px;
}

.manpower-header-actions .syn-profile-wrapper {
    flex: 0 0 44px;
}

/* =====================================================
   FIX SCROLL CONTENT, SIDEBAR, DAN FOOTER
   ===================================================== */

html,
body {
    width: 100%;
    height: 100%;
}

body.syn-manpower-page {
    height: 100vh;
    min-height: 0;
    overflow: hidden;
}

/*
 * Seluruh layout harus tepat setinggi layar.
 * Header 64px, isi fleksibel, footer 30px.
 */
.manpower-page {
    width: 100%;
    height: 100vh;
    min-height: 0;
    overflow: hidden;

    grid-template-rows:
        64px
        minmax(0, 1fr)
        30px;
}

/*
 * Sidebar tidak membuat halaman utama bertambah tinggi.
 */
.manpower-sidebar {
    min-height: 0;
    overflow: hidden;
}

/*
 * Yang dapat di-scroll hanya daftar menu sidebar.
 */
.manpower-navigation {
    min-height: 0;
    overflow-x: hidden;
    overflow-y: auto;
    scrollbar-gutter: stable;
}

/*
 * Pengaturan dan Bantuan tetap berada di bawah sidebar.
 */
.manpower-sidebar-bottom {
    flex: 0 0 auto;
}

/*
 * Area konten memiliki scroll sendiri.
 */
.manpower-content {
    min-width: 0;
    min-height: 0;
    overflow-x: auto;
    overflow-y: auto;
}

/*
 * Footer selalu terlihat dan tidak tertutup konten.
 */
.manpower-footer {
    position: relative;
    z-index: 5;

    width: 100%;
    height: 30px;
    min-height: 30px;

    overflow: hidden;
    flex: 0 0 30px;
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
        class="manpower-menu-link {{ request()->routeIs('manpower') ? 'active' : '' }}"
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
    <div
        class="manpower-menu-group {{ request()->routeIs('mine-permit.*') ? 'is-open' : '' }}"
    >
        <button
            type="button"
            class="manpower-menu-toggle"
            aria-expanded="{{ request()->routeIs('mine-permit.*') ? 'true' : 'false' }}"
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
            href="{{ route('mine-permit.monitoring-she') }}"
            class="manpower-submenu-link
                {{ request()->routeIs('mine-permit.monitoring-she')
                    ? 'active'
                    : '' }}"
        >
            Monitoring SHE
        </a>

        <a
            href="{{ route('mine-permit.monitoring-internal-upload') }}"
            class="manpower-submenu-link
                {{ request()->routeIs('mine-permit.monitoring-internal-upload')
                    ? 'active'
                    : '' }}"
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

{{-- Penutup manpower-menu-group Mine Permit --}}
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
        <!-- Daftar Test BNN -->
        <a
            href="https://docs.google.com/spreadsheets/d/1V9LU2Ft9NpxHULY7cVWczqpclCDy_Vja6qWtr7la38o/edit?usp=sharing"
            target="_blank"
            rel="noopener noreferrer"
            class="manpower-submenu-link"
        >
            Daftar Test BNN
        </a>

        <!-- Monitoring Kehadiran -->
        <a
            href="https://docs.google.com/spreadsheets/d/1enc9LxoaGo-ZNjxJ53UY24N3y-TTHn-W8P4UzmtXADU/edit?usp=sharing"
            target="_blank"
            rel="noopener noreferrer"
            class="manpower-submenu-link"
        >
            Monitoring Kehadiran
        </a>
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
                href="{{ route('bast.index', 'Senter P101X') }}"
                class="manpower-submenu-link"
            >
                BAST Senter P101X
            </a>

            <a
                href="{{ route('bast.index', 'Laser') }}"
                class="manpower-submenu-link"
            >
                BAST Laser
            </a>

            <a
                href="{{ route('bast.index', 'Laptop') }}"
                class="manpower-submenu-link"
            >
                BAST Laptop
            </a>

            <a
                href="{{ route('bast.index', 'Radio HT') }}"
                class="manpower-submenu-link"
            >
                BAST Radio HT
            </a>

            <a
                href="{{ route('bast.index', 'Lainnya') }}"
                class="manpower-submenu-link"
            >
                BAST Lainnya
            </a>
        </div>
    </div>
</div>

{{-- Monitoring APD --}}
<div
    class="manpower-menu-group
        {{ request()->routeIs('apd.*') ? 'is-open' : '' }}"
>
    <button
        type="button"
        class="manpower-menu-toggle"
        aria-expanded="{{
            request()->routeIs('apd.*')
                ? 'true'
                : 'false'
        }}"
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
                href="{{ route('apd.index') }}"
                class="manpower-submenu-link
                    {{
                        request()->routeIs('apd.index')
                            && request('open') === null
                            ? 'active'
                            : ''
                    }}"
            >
                Monitoring &amp; Pencarian
            </a>

            <a
                href="{{
                    route(
                        'apd.index',
                        ['open' => 'create']
                    )
                }}"
                class="manpower-submenu-link
                    {{
                        request()->routeIs('apd.index')
                            && request('open') === 'create'
                            ? 'active'
                            : ''
                    }}"
            >
                Input Pengajuan
            </a>

            <a
                href="{{
                    route(
                        'apd.index',
                        ['open' => 'pickup']
                    )
                }}"
                class="manpower-submenu-link
                    {{
                        request()->routeIs('apd.index')
                            && request('open') === 'pickup'
                            ? 'active'
                            : ''
                    }}"
            >
                Pengambilan Ready
            </a>
        </div>
    </div>
</div>

{{-- CC ST SP --}}
<div
    class="manpower-menu-group
        {{ request()->routeIs('cc-st-sp.*') ? 'is-open' : '' }}"
>
    <button
        type="button"
        class="manpower-menu-toggle"
        aria-expanded="{{ request()->routeIs('cc-st-sp.*') ? 'true' : 'false' }}"
    >
        <span class="manpower-menu-icon">
            <img
                src="{{ asset('assets/images/CC,ST,SP.png') }}"
                alt=""
            >
        </span>

        <span class="manpower-menu-label">CC ST SP</span>

        <span class="manpower-menu-arrow" aria-hidden="true">›</span>
    </button>

    <div class="manpower-submenu">
        <div class="manpower-submenu-inner">
            <a
                href="{{ route('cc-st-sp.coaching.index') }}"
                class="manpower-submenu-link
                    {{ request()->routeIs('cc-st-sp.coaching.*') ? 'active' : '' }}"
            >
                Coaching Counselling
            </a>

            <a
                href="{{ route('cc-st-sp.teguran.index') }}"
                class="manpower-submenu-link
                    {{ request()->routeIs('cc-st-sp.teguran.*') ? 'active' : '' }}"
            >
                Surat Teguran
            </a>

            <a
                href="{{ route('cc-st-sp.peringatan.index') }}"
                class="manpower-submenu-link
                    {{ request()->routeIs('cc-st-sp.peringatan.*') ? 'active' : '' }}"
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
<div
    class="manpower-menu-group
        {{ request()->routeIs('document-out.*') ? 'is-open' : '' }}"
>
    <button
        type="button"
        class="manpower-menu-toggle"
        aria-expanded="{{
            request()->routeIs('document-out.*')
                ? 'true'
                : 'false'
        }}"
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
                href="{{ route('document-out.index') }}"
                class="manpower-submenu-link
                    {{
                        request()->routeIs('document-out.*')
                            ? 'active'
                            : ''
                    }}"
            >
                Monitoring Surat Keluar
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
                href="https://mail.google.com/mail/?view=cm&fs=1&to={{ urlencode(config('access.contact_email', 'mpe.ppaba@ppa.co.id')) }}&su=SYNRGYPRO%20Support"
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
        {{-- Shortcut lintas modul --}}
        <x-module-shortcut />

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
                alt="Dashboard"
            >
        </a>

        {{-- Dropdown Profil --}}
        <x-profile-dropdown />

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
                    alt="Logout"
                >
            </button>
        </form>
    </nav>
</header>

{{-- Isi halaman --}}
<main class="manpower-content">

    @if (isset($contentView))

        @include($contentView)

    @else

        <p class="manpower-welcome">
            Hi, {{ Auth::user()?->name ?? 'Pengguna' }}
        </p>

    @endif

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
| Accordion submenu
|--------------------------------------------------------------------------
| Hanya satu grup submenu yang boleh terbuka.
| Grup yang memiliki submenu aktif tetap terbuka saat halaman dimuat.
*/

const menuGroups =
    document.querySelectorAll(
        '.manpower-menu-group'
    );

const menuToggles =
    document.querySelectorAll(
        '.manpower-menu-toggle'
    );


/*
|--------------------------------------------------------------------------
| Atur submenu ketika halaman pertama kali dibuka
|--------------------------------------------------------------------------
*/

const activeSubmenuLink =
    document.querySelector(
        '.manpower-submenu-link.active'
    );

const activeMenuGroup =
    activeSubmenuLink
        ? activeSubmenuLink.closest(
            '.manpower-menu-group'
        )
        : null;


menuGroups.forEach(function (menuGroup) {

    const menuToggle =
        menuGroup.querySelector(
            '.manpower-menu-toggle'
        );

    const shouldOpen =
        menuGroup === activeMenuGroup;

    menuGroup.classList.toggle(
        'is-open',
        shouldOpen
    );

    if (menuToggle) {
        menuToggle.setAttribute(
            'aria-expanded',
            String(shouldOpen)
        );
    }

});


/*
|--------------------------------------------------------------------------
| Buka menu yang diklik dan tutup menu lainnya
|--------------------------------------------------------------------------
*/

menuToggles.forEach(function (menuToggle) {

    menuToggle.addEventListener(
        'click',
        function () {

            const selectedMenuGroup =
                menuToggle.closest(
                    '.manpower-menu-group'
                );

            if (!selectedMenuGroup) {
                return;
            }

            const willOpen =
                !selectedMenuGroup.classList.contains(
                    'is-open'
                );


            /*
             * Tutup seluruh submenu terlebih dahulu.
             */

            menuGroups.forEach(function (menuGroup) {

                const otherToggle =
                    menuGroup.querySelector(
                        '.manpower-menu-toggle'
                    );

                menuGroup.classList.remove(
                    'is-open'
                );

                if (otherToggle) {
                    otherToggle.setAttribute(
                        'aria-expanded',
                        'false'
                    );
                }

            });


            /*
             * Buka menu yang baru dipilih.
             * Jika menu yang sama diklik ulang, menu tetap tertutup.
             */

            if (willOpen) {

                selectedMenuGroup.classList.add(
                    'is-open'
                );

                menuToggle.setAttribute(
                    'aria-expanded',
                    'true'
                );

            }

        }
    );

});

/*
|--------------------------------------------------------------------------
| Penutup DOMContentLoaded
|--------------------------------------------------------------------------
*/

});
</script>
@endpush