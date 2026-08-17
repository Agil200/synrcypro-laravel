@extends('layouts.app')

@section('title', 'Admin All — SYNRGYPRO')
@section('body-class', 'aa-app-body')

@push('styles')
<style>
    :root {
        --aa-side: 186px;
        --aa-top: 58px;
        --aa-footer: 28px;
        --aa-bg: #f1f4f7;
        --aa-card: #ffffff;
        --aa-border: #d9e0e7;
        --aa-text: #0d1f33;
        --aa-muted: #617083;
        --aa-red: #e00012;
        --aa-blue: #0f78ef;
        --aa-shadow: 0 5px 18px rgba(31, 47, 65, .07);
    }

    * { box-sizing: border-box; }

    html {
        height: 100%;
        overflow: hidden;
    }

    body.aa-app-body {
        margin: 0;
        width: 100%;
        height: 100vh;
        height: 100dvh;
        max-height: 100vh;
        max-height: 100dvh;
        overflow: hidden;
        color: var(--aa-text);
        background: var(--aa-bg);
        font-family: Arial, Helvetica, sans-serif;
    }

    button, input, select { font: inherit; }
    button { cursor: pointer; }

    .aa-shell {
        position: fixed;
        inset: 0;
        display: grid;
        width: 100%;
        height: 100vh;
        height: 100dvh;
        min-height: 0;
        overflow: hidden;
        grid-template-columns: var(--aa-side) minmax(0, 1fr);
        grid-template-rows: var(--aa-top) minmax(0, 1fr) var(--aa-footer);
        background: var(--aa-bg);
    }

    .aa-sidebar {
        position: relative;
        z-index: 40;
        display: grid;
        min-height: 0;
        overflow: hidden;
        grid-column: 1;
        grid-row: 1 / 4;
        grid-template-rows: var(--aa-top) minmax(0, 1fr) auto;
        border-right: 1px solid #c4cad0;
        background: linear-gradient(180deg, #f4f4f4 0%, #e1e3e5 100%);
    }

    .aa-sidebar-head {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 42px;
        border-bottom: 1px solid #343434;
        background: #111;
    }

    .aa-module-mark {
        display: grid;
        place-items: center;
        min-width: 0;
        color: #fff;
        background: #111;
    }

    .aa-module-mark img {
        width: 39px;
        height: 39px;
        object-fit: contain;
    }

    .aa-module-mark-fallback {
        display: grid;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 4px;
        color: #111;
        background: #fff;
        font-size: 18px;
        font-weight: 900;
    }

    .aa-sidebar-toggle {
        display: grid;
        place-items: center;
        padding: 0;
        border: 0;
        border-left: 1px solid #454545;
        color: #111;
        background: #fff;
        font-size: 23px;
    }

    .aa-navigation {
        min-height: 0;
        padding: 10px 7px;
        overflow-x: hidden;
        overflow-y: auto;
        overscroll-behavior: contain;
        scrollbar-gutter: stable;
    }

    .aa-menu-link,
    .aa-menu-toggle {
        display: flex;
        width: 100%;
        min-height: 38px;
        align-items: center;
        gap: 7px;
        margin: 2px 0;
        padding: 7px 8px;
        border: 1px solid transparent;
        border-radius: 4px;
        color: #111827;
        background: transparent;
        font-size: 10px;
        font-weight: 800;
        text-align: left;
        text-decoration: none;
    }

    .aa-menu-link:hover,
    .aa-menu-toggle:hover,
    .aa-menu-link.active,
    .aa-menu-group.open > .aa-menu-toggle {
        border-color: #cbd2d8;
        background: #fff;
    }

    .aa-menu-link.active,
    .aa-menu-group.open > .aa-menu-toggle {
        box-shadow: 0 1px 3px rgba(15, 23, 42, .06);
    }

    .aa-menu-icon {
        display: grid;
        width: 22px;
        height: 22px;
        flex: 0 0 22px;
        place-items: center;
        color: #89939f;
    }

    .aa-menu-link.active .aa-menu-icon,
    .aa-menu-group.open > .aa-menu-toggle .aa-menu-icon {
        color: #111827;
    }

    .aa-line-icon {
        display: block;
        width: 16px;
        height: 16px;
        fill: none;
        stroke: currentColor;
        stroke-linecap: round;
        stroke-linejoin: round;
        stroke-width: 2;
    }

    .aa-submenu-mini-icon {
        width: 14px;
        height: 14px;
        flex: 0 0 14px;
        color: #9aa4af;
    }

    .aa-menu-label { min-width: 0; flex: 1; }
    .aa-menu-arrow { font-size: 15px; transition: transform .18s ease; }
    .aa-menu-group.open .aa-menu-arrow { transform: rotate(90deg); }

    .aa-submenu {
        display: grid;
        grid-template-rows: 0fr;
        opacity: 0;
        transition: grid-template-rows .18s ease, opacity .18s ease;
    }

    .aa-menu-group.open .aa-submenu { grid-template-rows: 1fr; opacity: 1; }
    .aa-submenu-inner { overflow: hidden; }

    .aa-submenu-link,
    .aa-submenu-planned {
        position: relative;
        display: flex;
        min-height: 29px;
        align-items: center;
        gap: 7px;
        padding: 6px 8px 6px 28px;
        color: #374151;
        font-size: 9px;
        font-weight: 700;
        text-decoration: none;
    }

    .aa-submenu-link::before,
    .aa-submenu-planned::before {
        display: none;
    }

    .aa-submenu-link:hover,
    .aa-submenu-link.active {
        color: #111;
        background: #fff;
    }

    .aa-submenu-link.active .aa-submenu-mini-icon {
        color: var(--aa-red);
    }

    .aa-submenu-planned { cursor: default; opacity: .72; }

    .aa-step-badge {
        margin-left: auto;
        padding: 2px 5px;
        border-radius: 999px;
        color: #65707a;
        background: #fff;
        font-size: 7px;
        font-weight: 900;
    }

    .aa-sidebar-bottom {
        padding: 8px 10px 12px;
        border-top: 1px solid #c8cdd2;
    }

    .aa-bottom-link {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 2px;
        color: #111827;
        font-size: 9px;
        font-weight: 800;
        text-decoration: none;
    }

    .aa-bottom-link.help { color: var(--aa-red); }

    .aa-header {
        position: relative;
        z-index: 30;
        display: flex;
        min-width: 0;
        min-height: 0;
        grid-column: 2;
        grid-row: 1;
        align-items: center;
        justify-content: flex-end;
        border-bottom: 1px solid #171717;
        background: linear-gradient(100deg, #171717 0%, #202020 45%, #b55a34 100%);
    }

    .aa-header-logo {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .aa-header-logo img {
        display: block;
        width: 105px;
        height: 43px;
        object-fit: contain;
    }

    .aa-header-actions {
        display: flex;
        height: 100%;
        align-items: center;
        gap: 6px;
        padding: 0 8px;
        background: #fff;
    }

    .aa-header-button {
        display: inline-grid;
        width: 39px;
        height: 39px;
        place-items: center;
        padding: 0;
        border: 1px solid #111;
        border-radius: 9px;
        color: #111;
        background: #fff;
        text-decoration: none;
    }

    .aa-header-button img { width: 27px; height: 27px; object-fit: contain; }
    .aa-logout-button { border-color: var(--aa-red); }
    .aa-logout-form { margin: 0; }

    .aa-main {
        min-width: 0;
        min-height: 0;
        grid-column: 2;
        grid-row: 2;
        padding: 10px;
        overflow-x: hidden;
        overflow-y: auto;
        overscroll-behavior: contain;
        scrollbar-gutter: stable;
        -webkit-overflow-scrolling: touch;
    }

    .aa-content { width: 100%; max-width: none; margin: 0; }

    .aa-footer {
        position: relative;
        z-index: 30;
        display: grid;
        min-width: 0;
        min-height: 0;
        grid-column: 2;
        grid-row: 3;
        place-items: center;
        color: #fff;
        background: #111;
        font-size: 8px;
        font-weight: 700;
        letter-spacing: .04em;
    }

    .aa-page-title {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 8px;
    }

    .aa-page-title h1 {
        margin: 0;
        color: #051d39;
        font-size: clamp(20px, 2vw, 27px);
        letter-spacing: -.03em;
    }

    .aa-page-title p {
        margin: 3px 0 0;
        color: #5d6c7c;
        font-size: 9px;
        line-height: 1.4;
    }

    .aa-title-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 7px; }

    .aa-action-button {
        display: inline-flex;
        min-height: 31px;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 7px 11px;
        border: 1px solid #cfd8e2;
        border-radius: 7px;
        color: #172b43;
        background: #fff;
        font-size: 8px;
        font-weight: 900;
        text-decoration: none;
        text-transform: uppercase;
    }

    .aa-action-button.primary { border-color: var(--aa-blue); color: #fff; background: var(--aa-blue); }

    .aa-info-strip {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 10px;
        padding: 8px 11px;
        border: 1px solid #a9e3c6;
        border-radius: 7px;
        color: #12643b;
        background: #edfff5;
        font-size: 8px;
    }

    .aa-info-strip strong::before {
        display: inline-block;
        width: 8px;
        height: 8px;
        margin-right: 7px;
        border-radius: 50%;
        background: #20b76b;
        box-shadow: 0 0 0 4px rgba(32, 183, 107, .13);
        content: '';
    }

    .aa-grid { display: grid; gap: 9px; }
    .aa-grid.stats { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .aa-grid.two { grid-template-columns: minmax(0, 1.45fr) minmax(300px, .75fr); }

    .aa-card {
        min-width: 0;
        border: 1px solid var(--aa-border);
        border-radius: 10px;
        background: #fff;
        box-shadow: var(--aa-shadow);
    }

    .aa-stat {
        position: relative;
        min-height: 72px;
        padding: 12px 13px 10px 54px;
        overflow: hidden;
    }

    .aa-stat-icon {
        position: absolute;
        top: 14px;
        left: 13px;
        display: grid;
        width: 32px;
        height: 32px;
        place-items: center;
        border-radius: 8px;
        color: #fff;
        background: var(--stat-color, #172b43);
        font-size: 13px;
        font-weight: 900;
    }

    .aa-stat small {
        display: block;
        color: #536273;
        font-size: 7px;
        font-weight: 900;
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    .aa-stat strong { display: block; margin-top: 2px; font-size: 22px; line-height: 1; }
    .aa-stat span { display: block; margin-top: 4px; color: #667587; font-size: 7px; }

    .aa-section { margin-top: 10px; }

    .aa-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 12px;
        border-bottom: 1px solid var(--aa-border);
    }

    .aa-card-head h2 { margin: 0; font-size: 12px; }
    .aa-card-head p { margin: 2px 0 0; color: #667587; font-size: 7px; }
    .aa-card-body { padding: 11px 12px; }

    .aa-module-list { display: grid; gap: 7px; }

    .aa-module-row {
        display: grid;
        grid-template-columns: 34px minmax(0, 1fr) 105px 68px;
        align-items: center;
        gap: 9px;
        padding: 9px;
        border: 1px solid #e0e6eb;
        border-radius: 8px;
        background: #fff;
    }

    .aa-module-row-icon {
        display: grid;
        width: 32px;
        height: 32px;
        place-items: center;
        border-radius: 8px;
        color: #fff;
        background: var(--module-color);
        font-size: 12px;
        font-weight: 900;
    }

    .aa-module-row strong { display: block; font-size: 9px; }
    .aa-module-row small { display: block; margin-top: 2px; color: #697789; font-size: 7px; }
    .aa-source { color: #4e6073; font-size: 7px; font-weight: 800; text-align: right; }

    .aa-status {
        justify-self: end;
        padding: 3px 7px;
        border-radius: 999px;
        color: #67717c;
        background: #edf1f4;
        font-size: 7px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .aa-status.active { color: #11633a; background: #ddf5e7; }

    .aa-folder-list { display: grid; gap: 8px; }

    .aa-folder-card {
        display: grid;
        grid-template-columns: 34px minmax(0, 1fr) auto;
        align-items: center;
        gap: 9px;
        padding: 10px;
        border: 1px solid #dfe5ea;
        border-radius: 8px;
        color: inherit;
        background: #fff;
        text-decoration: none;
    }

    .aa-folder-card:hover { border-color: #aebbc7; }
    .aa-folder-icon { font-size: 23px; }
    .aa-folder-card strong { display: block; font-size: 9px; line-height: 1.3; }
    .aa-folder-card small { display: block; margin-top: 3px; color: #68778a; font-size: 7px; }
    .aa-folder-arrow { font-size: 16px; }

    .aa-ownership {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 7px;
    }

    .aa-range {
        padding: 9px;
        border: 1px solid #b7d9ff;
        border-radius: 8px;
        background: #eef7ff;
    }

    .aa-range strong { display: block; color: #0d63b7; font-size: 11px; }
    .aa-range span { display: block; margin-top: 4px; color: #53677b; font-size: 7px; line-height: 1.4; }

    .aa-pill-list { display: flex; flex-wrap: wrap; gap: 6px; }

    .aa-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 8px;
        border: 1px solid #d8e0e8;
        border-radius: 999px;
        color: #405267;
        background: #fff;
        font-size: 8px;
        font-weight: 900;
    }

    .aa-access-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
    }

    .aa-access-card {
        padding: 10px;
        border: 1px solid #dde5ec;
        border-radius: 9px;
        background: #fff;
    }

    .aa-access-card strong { display: block; font-size: 10px; }
    .aa-access-card small { display: block; margin-top: 2px; color: #697789; font-size: 7px; text-transform: uppercase; font-weight: 900; }
    .aa-access-card p { margin: 7px 0 0; color: #506174; font-size: 8px; line-height: 1.45; }

    .aa-workflow {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px;
    }

    .aa-workflow-step {
        position: relative;
        min-height: 70px;
        padding: 11px;
        border: 1px solid #dde5ec;
        border-radius: 9px;
        background: #fff;
    }

    .aa-workflow-step span {
        display: grid;
        width: 24px;
        height: 24px;
        place-items: center;
        border-radius: 7px;
        color: #fff;
        background: var(--aa-red);
        font-size: 9px;
        font-weight: 900;
    }

    .aa-workflow-step strong { display: block; margin-top: 8px; font-size: 9px; line-height: 1.35; }

    .aa-shell.collapsed { grid-template-columns: 56px minmax(0, 1fr); }
    .aa-shell.collapsed .aa-sidebar-head { grid-template-columns: 56px; }
    .aa-shell.collapsed .aa-module-mark,
    .aa-shell.collapsed .aa-menu-label,
    .aa-shell.collapsed .aa-menu-arrow,
    .aa-shell.collapsed .aa-submenu,
    .aa-shell.collapsed .aa-sidebar-bottom { display: none; }
    .aa-shell.collapsed .aa-sidebar-toggle { width: 56px; border-left: 0; }
    .aa-shell.collapsed .aa-menu-link,
    .aa-shell.collapsed .aa-menu-toggle { justify-content: center; padding: 7px; }

    @media (max-width: 950px) {
        .aa-grid.stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .aa-grid.two { grid-template-columns: 1fr; }
    }

    @media (max-width: 700px) {
        .aa-shell { grid-template-columns: 56px minmax(0, 1fr); }
        .aa-sidebar-head { grid-template-columns: 56px; }
        .aa-module-mark,
        .aa-menu-label,
        .aa-menu-arrow,
        .aa-submenu,
        .aa-sidebar-bottom { display: none; }
        .aa-sidebar-toggle { width: 56px; border-left: 0; }
        .aa-menu-link, .aa-menu-toggle { justify-content: center; padding: 7px; }
        .aa-header-logo { left: 34%; }
        .aa-page-title { flex-direction: column; }
        .aa-title-actions { justify-content: flex-start; }
        .aa-grid.stats, .aa-ownership, .aa-access-grid, .aa-workflow { grid-template-columns: 1fr; }
        .aa-module-row { grid-template-columns: 34px minmax(0, 1fr) 58px; }
        .aa-source { display: none; }
    }
</style>
@endpush

@section('content')
<div class="aa-shell" id="adminAllShell">
    <aside class="aa-sidebar">
        <div class="aa-sidebar-head">
            <div class="aa-module-mark">
                <img
                    src="{{ asset('assets/images/LOGO ADMIN ALL.png') }}"
                    alt="Admin All"
                    onerror="this.style.display='none';this.nextElementSibling.style.display='grid'"
                >
                <span class="aa-module-mark-fallback" style="display:none">A</span>
            </div>
            <button type="button" class="aa-sidebar-toggle" id="adminAllToggle" aria-label="Tutup atau buka sidebar">☰</button>
        </div>

        @php
            $icons = [
                'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
                'suggestion' => '<path d="M9 18h6"/><path d="M10 22h4"/><path d="M8.2 14.8A7 7 0 1 1 15.8 14.8c-.9.7-1.3 1.6-1.3 2.7h-5c0-1.1-.4-2-1.3-2.7Z"/>',
                'chart' => '<path d="M4 19V5"/><path d="M4 19h16"/><path d="M8 16v-5"/><path d="M12 16V8"/><path d="M16 16v-9"/>',
                'table' => '<rect x="4" y="5" width="16" height="14" rx="2"/><path d="M4 10h16"/><path d="M10 5v14"/>',
                'check' => '<path d="M20 6 9 17l-5-5"/><path d="M4 4h16v16H4z"/>',
                'shield' => '<path d="M12 3 20 6v6c0 5-3.4 8-8 9-4.6-1-8-4-8-9V6l8-3Z"/><path d="m9 12 2 2 4-5"/>',
                'ticket' => '<path d="M5 6h14a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-3a2 2 0 0 0 0-4V8a2 2 0 0 1 2-2Z"/><path d="M9 6v14"/>',
                'plane' => '<path d="M22 2 11 13"/><path d="m22 2-7 20-4-9-9-4 20-7Z"/>',
                'edit' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4 11.5-11.5Z"/>',
                'heart' => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/>',
                'calendar' => '<rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4"/><path d="M16 2v4"/><path d="M3 10h18"/>',
                'repeat' => '<path d="m17 2 4 4-4 4"/><path d="M3 11V9a3 3 0 0 1 3-3h15"/><path d="m7 22-4-4 4-4"/><path d="M21 13v2a3 3 0 0 1-3 3H3"/>',
                'box' => '<path d="M21 8 12 3 3 8l9 5 9-5Z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/>',
                'clipboard' => '<path d="M9 3h6l1 2h3v16H5V5h3l1-2Z"/><path d="M9 13h6"/><path d="M9 17h4"/>',
                'history' => '<path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/><path d="M12 7v6l4 2"/>',
                'folder' => '<path d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/>',
                'file' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/>',
                'settings' => '<path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1A2 2 0 1 1 4.2 17l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.6-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1A2 2 0 1 1 7 4.2l.1.1a1.7 1.7 0 0 0 1.9.3h.1A1.7 1.7 0 0 0 10 3V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1A2 2 0 1 1 19.8 7l-.1.1a1.7 1.7 0 0 0-.3 1.9v.1A1.7 1.7 0 0 0 21 10h.1a2 2 0 1 1 0 4H21a1.7 1.7 0 0 0-1.6 1Z"/>',
                'help' => '<circle cx="12" cy="12" r="10"/><path d="M9.5 9a2.6 2.6 0 0 1 5 1c0 2-2.5 2.1-2.5 4"/><path d="M12 18h.01"/>',
            ];

            $adminAllIcon = static function (string $name, string $class = '') use ($icons) {
                return new \Illuminate\Support\HtmlString(
                    '<svg class="aa-line-icon '.$class.'" viewBox="0 0 24 24" aria-hidden="true">'.$icons[$name].'</svg>'
                );
            };

            $suggestionUrl = \Illuminate\Support\Facades\Route::has('admin-all.suggestion.index')
                ? route('admin-all.suggestion.index')
                : '#';

            $ifutsUrl = \Illuminate\Support\Facades\Route::has('admin-all.ifuts.index')
                ? route('admin-all.ifuts.index')
                : '#';

            $ifutsMonitoringUrl = \Illuminate\Support\Facades\Route::has('admin-all.ifuts.monitoring')
                ? route('admin-all.ifuts.monitoring')
                : '#';

            $ifutsInputUrl = \Illuminate\Support\Facades\Route::has('admin-all.ifuts.input')
                ? route('admin-all.ifuts.input')
                : '#';

            $mcuFuInternalUrl = \Illuminate\Support\Facades\Route::has('admin-all.mcu-fu.index')
                ? route('admin-all.mcu-fu.index')
                : '#';

            $mcuFuInternalMcuUrl = \Illuminate\Support\Facades\Route::has('admin-all.mcu-fu.mcu')
                ? route('admin-all.mcu-fu.mcu')
                : '#';

            $mcuFuInternalFollowUpUrl = \Illuminate\Support\Facades\Route::has('admin-all.mcu-fu.follow-up')
                ? route('admin-all.mcu-fu.follow-up')
                : '#';

            $mcuFuInternalHistoryUrl = \Illuminate\Support\Facades\Route::has('admin-all.mcu-fu.history')
                ? route('admin-all.mcu-fu.history')
                : '#';

            $suggestionMonitoringUrl = \Illuminate\Support\Facades\Route::has('admin-all.suggestion.monitoring')
                ? route('admin-all.suggestion.monitoring')
                : '#';

            $suggestionVerificationGlUrl = \Illuminate\Support\Facades\Route::has('admin-all.suggestion.verification-gl')
                ? route('admin-all.suggestion.verification-gl')
                : '#';

            $suggestionApprovalShUrl = \Illuminate\Support\Facades\Route::has('admin-all.suggestion.approval-sh')
                ? route('admin-all.suggestion.approval-sh')
                : '#';

            // STEP 8A — Persetujuan DH / PM.
            $suggestionApprovalDhPmUrl = \Illuminate\Support\Facades\Route::has('admin-all.suggestion.approval-dh-pm')
                ? route('admin-all.suggestion.approval-dh-pm')
                : '#';

            $detailFromSh = request()->routeIs('admin-all.suggestion.detail')
                && request()->query('from') === 'sh';

            $barangUrl = \Illuminate\Support\Facades\Route::has('barang.index')
                ? route('barang.index')
                : '#';

            $adminMenus = [
                [
                    'icon' => 'suggestion',
                    'title' => 'Suggestion System',
                    'active' => request()->routeIs('admin-all.suggestion.*'),
                    'items' => [
                        ['icon' => 'chart', 'label' => 'Dashboard Suggestion', 'url' => $suggestionUrl, 'active' => request()->routeIs('admin-all.suggestion.index')],
                        ['icon' => 'table', 'label' => 'Monitoring Data SS', 'url' => $suggestionMonitoringUrl, 'active' => request()->routeIs('admin-all.suggestion.monitoring') || (request()->routeIs('admin-all.suggestion.detail') && !$detailFromSh)],
                        ['icon' => 'check', 'label' => 'Verifikasi GL', 'url' => $suggestionVerificationGlUrl, 'active' => request()->routeIs('admin-all.suggestion.verification-gl')],
                        ['icon' => 'shield', 'label' => 'Persetujuan SH', 'url' => $suggestionApprovalShUrl, 'active' => request()->routeIs('admin-all.suggestion.approval-sh') || $detailFromSh],
                        ['icon' => 'shield', 'label' => 'Persetujuan DH / PM', 'url' => $suggestionApprovalDhPmUrl, 'active' => request()->routeIs('admin-all.suggestion.approval-dh-pm')],
                    ],
                ],
                [
                    'icon' => 'plane',
                    'title' => 'IFUTS TICKETING',
                    'active' => request()->routeIs('admin-all.ifuts.*'),
                    'items' => [
                        [
                            'icon' => 'chart',
                            'label' => 'Dashboard IFUTS',
                            'url' => $ifutsUrl,
                            'active' => request()->routeIs('admin-all.ifuts.index'),
                        ],
                        [
                            'icon' => 'ticket',
                            'label' => 'Monitoring Tiket',
                            'url' => $ifutsMonitoringUrl,
                            'active' => request()->routeIs('admin-all.ifuts.monitoring'),
                        ],
                        [
                            'icon' => 'edit',
                            'label' => 'Input Ticket',
                            'url' => $ifutsInputUrl,
                            'active' => request()->routeIs('admin-all.ifuts.input')
                                || request()->routeIs('admin-all.ifuts.input.validate'),
                        ],
                    ],
                ],
                [
                    'icon' => 'heart',
                    'title' => 'MCU & FU Internal',
                    'active' => request()->routeIs('admin-all.mcu-fu.*'),
                    'items' => [
                        [
                            'icon' => 'chart',
                            'label' => 'Dashboard MCU & FU',
                            'url' => $mcuFuInternalUrl,
                            'active' => request()->routeIs('admin-all.mcu-fu.index'),
                        ],
                        [
                            'icon' => 'calendar',
                            'label' => 'Input / Update MCU',
                            'url' => $mcuFuInternalMcuUrl,
                            'active' => request()->routeIs('admin-all.mcu-fu.mcu')
                                || request()->routeIs('admin-all.mcu-fu.mcu.update'),
                        ],
                        [
                            'icon' => 'repeat',
                            'label' => 'Input Follow Up',
                            'url' => $mcuFuInternalFollowUpUrl,
                            'active' => request()->routeIs('admin-all.mcu-fu.follow-up')
                                || request()->routeIs('admin-all.mcu-fu.follow-up.update'),
                        ],
                        [
                            'icon' => 'history',
                            'label' => 'Riwayat Update',
                            'url' => $mcuFuInternalHistoryUrl,
                            'active' => request()->routeIs('admin-all.mcu-fu.history'),
                        ],
                    ],
                ],
                [
                    'icon' => 'box',
                    'title' => 'Stock Opname Gudang',
                    'active' => request()->routeIs('barang.*'),
                    'items' => [
                        [
                            'icon' => 'chart', 
                            'label' => 'Dashboard Stock', 
                            'url' => $barangUrl, 
                            'active' => request()->routeIs('barang.index')
                        ],
                    ],
                ],
            ];

            $support = config('admin_all.support', []);
            $supportUrl = 'https://mail.google.com/mail/?view=cm&fs=1&tf=cm'
                .'&to='.rawurlencode($support['email'] ?? 'mpe.ppaba@ppa.co.id')
                .'&su='.rawurlencode($support['subject'] ?? 'SYNRGYPRO Support')
                .'&body='.rawurlencode($support['body'] ?? '');
        @endphp

        <nav class="aa-navigation">
            <a href="{{ route('admin-all') }}" class="aa-menu-link {{ request()->routeIs('admin-all') ? 'active' : '' }}">
                <span class="aa-menu-icon">{{ $adminAllIcon('dashboard') }}</span>
                <span class="aa-menu-label">Dashboard</span>
            </a>

            @foreach ($adminMenus as $menu)
                <div class="aa-menu-group {{ $menu['active'] ? 'open' : '' }}">
                    <button type="button" class="aa-menu-toggle" aria-expanded="{{ $menu['active'] ? 'true' : 'false' }}">
                        <span class="aa-menu-icon">{{ $adminAllIcon($menu['icon']) }}</span>
                        <span class="aa-menu-label">{{ $menu['title'] }}</span>
                        <span class="aa-menu-arrow">›</span>
                    </button>
                    <div class="aa-submenu">
                        <div class="aa-submenu-inner">
                            @foreach ($menu['items'] as $item)
                                @if (! empty($item['url']) && empty($item['planned']))
                                    <a href="{{ $item['url'] }}" class="aa-submenu-link {{ ! empty($item['active']) ? 'active' : '' }}">
                                        {{ $adminAllIcon($item['icon'], 'aa-submenu-mini-icon') }}
                                        <span>{{ $item['label'] }}</span>
                                    </a>
                                @else
                                    <span class="aa-submenu-planned">
                                        {{ $adminAllIcon($item['icon'], 'aa-submenu-mini-icon') }}
                                        <span>{{ $item['label'] }}</span>
                                        <span class="aa-step-badge">TAHAP BERIKUT</span>
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="aa-menu-group">
                <button type="button" class="aa-menu-toggle" aria-expanded="false">
                    <span class="aa-menu-icon">{{ $adminAllIcon('folder') }}</span>
                    <span class="aa-menu-label">E-Arsip</span>
                    <span class="aa-menu-arrow">›</span>
                </button>
                <div class="aa-submenu">
                    <div class="aa-submenu-inner">
                        <a class="aa-submenu-link" target="_blank" rel="noopener noreferrer"
                           href="https://drive.google.com/drive/folders/1X01OjcwoWZkItRwpK8J8nZ1I5We2xECv?hl=ID">
                            {{ $adminAllIcon('file', 'aa-submenu-mini-icon') }}
                            <span>Prosedur Departemen</span>
                        </a>
                        <a class="aa-submenu-link" target="_blank" rel="noopener noreferrer"
                           href="https://drive.google.com/drive/folders/1umQAn1zufRo_D-ohTesPVfN12vl8R0tV">
                            {{ $adminAllIcon('folder', 'aa-submenu-mini-icon') }}
                            <span>Kumpulan Form Admin</span>
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <div class="aa-sidebar-bottom">
            <a href="{{ route('profile.settings') }}" class="aa-bottom-link">{{ $adminAllIcon('settings', 'aa-submenu-mini-icon') }} <span>Pengaturan</span></a>
            <a href="{{ $supportUrl }}" target="_blank" rel="noopener noreferrer" class="aa-bottom-link help">{{ $adminAllIcon('help', 'aa-submenu-mini-icon') }} <span>Bantuan</span></a>
        </div>
    </aside>

    <header class="aa-header">
        <div class="aa-header-logo">
            <img src="{{ asset('assets/images/synrgypro-logo.png') }}" alt="SYNRGYPRO">
        </div>

        <nav class="aa-header-actions" aria-label="Shortcut pengguna">
            <x-module-shortcut />

            <a href="{{ route('dashboard') }}" class="aa-header-button" aria-label="Dashboard Utama">
                <img src="{{ asset('assets/images/LOGO HOME.jpeg') }}" alt="Dashboard">
            </a>

            <x-profile-dropdown />

            <form method="POST" action="{{ route('logout') }}" class="aa-logout-form">
                @csrf
                <button type="submit" class="aa-header-button aa-logout-button" aria-label="Logout">
                    <img src="{{ asset('assets/images/LOGO LOGOUT.png') }}" alt="Logout">
                </button>
            </form>
        </nav>
    </header>

    <main class="aa-main">
        <div class="aa-content">
            @yield('admin-content')
        </div>
    </main>

    <footer class="aa-footer">
        © COPYRIGHT SYNRGYPRO {{ date('Y') }}. V1.0
    </footer>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const shell = document.getElementById('adminAllShell');
        const toggle = document.getElementById('adminAllToggle');

        toggle?.addEventListener('click', function () {
            shell?.classList.toggle('collapsed');
        });

        document.querySelectorAll('.aa-menu-toggle').forEach(function (button) {
            button.addEventListener('click', function () {
                const group = button.closest('.aa-menu-group');
                const willOpen = !group.classList.contains('open');

                document.querySelectorAll('.aa-menu-group.open').forEach(function (opened) {
                    if (opened !== group) {
                        opened.classList.remove('open');
                        opened.querySelector('.aa-menu-toggle')?.setAttribute('aria-expanded', 'false');
                    }
                });

                group.classList.toggle('open', willOpen);
                button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            });
        });
    })();
</script>
@endpush