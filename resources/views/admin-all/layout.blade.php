@extends('layouts.app')

@section('title', trim($__env->yieldContent('admin-page-title', 'Admin All')).' — SYNRGYPRO')
@section('body-class', 'admin-all-body')

@push('styles')
<style>
    :root {
        --admin-sidebar: 248px;
        --admin-header: 66px;
        --admin-red: #d71920;
        --admin-red-dark: #b51218;
        --admin-ink: #15202b;
        --admin-muted: #687383;
        --admin-border: #e1e6eb;
        --admin-soft: #f5f7f9;
        --admin-white: #ffffff;
        --admin-green: #17864b;
        --admin-orange: #d47a09;
        --admin-blue: #1669b2;
        --admin-shadow: 0 10px 28px rgba(24, 34, 45, .08);
    }

    * { box-sizing: border-box; }

    body.admin-all-body {
        margin: 0;
        color: var(--admin-ink);
        background: var(--admin-soft);
        font-family: Arial, Helvetica, sans-serif;
    }

    button, input, select { font: inherit; }
    button { cursor: pointer; }

    .admin-shell {
        display: grid;
        min-height: 100vh;
        grid-template-columns: var(--admin-sidebar) minmax(0, 1fr);
        grid-template-rows: var(--admin-header) minmax(0, 1fr) 32px;
    }

    .admin-sidebar {
        display: flex;
        grid-row: 1 / 4;
        flex-direction: column;
        border-right: 1px solid #cfd5db;
        background: linear-gradient(180deg, #f0f1f2 0%, #dfe2e5 100%);
    }

    .admin-brand {
        display: flex;
        min-height: var(--admin-header);
        align-items: center;
        gap: 10px;
        padding: 8px 15px;
        color: #fff;
        background: #141414;
    }

    .admin-brand img {
        width: 58px;
        height: 46px;
        object-fit: contain;
    }

    .admin-brand strong {
        display: block;
        font-size: 15px;
        letter-spacing: .04em;
    }

    .admin-brand small {
        display: block;
        margin-top: 2px;
        color: #bfc5cc;
        font-size: 10px;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .admin-nav {
        flex: 1;
        padding: 14px 10px;
        overflow-y: auto;
    }

    .admin-nav-label {
        margin: 17px 10px 7px;
        color: #68717c;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .admin-nav-label:first-child { margin-top: 3px; }

    .admin-nav-link {
        display: flex;
        min-height: 42px;
        align-items: center;
        gap: 11px;
        margin: 3px 0;
        padding: 9px 11px;
        border: 1px solid transparent;
        border-radius: 8px;
        color: #202830;
        font-size: 12px;
        font-weight: 750;
        text-decoration: none;
    }

    .admin-nav-link:hover,
    .admin-nav-link.active {
        border-color: #d2d7dc;
        background: rgba(255, 255, 255, .83);
    }

    .admin-nav-link.active {
        box-shadow: inset 4px 0 0 var(--admin-red);
    }

    .admin-nav-link.planned {
        cursor: default;
        opacity: .62;
    }

    .admin-nav-icon {
        display: grid;
        width: 24px;
        height: 24px;
        flex: 0 0 24px;
        place-items: center;
        border-radius: 7px;
        color: #fff;
        background: #363d44;
        font-size: 12px;
        font-weight: 900;
    }

    .admin-nav-link.active .admin-nav-icon { background: var(--admin-red); }

    .admin-plan-badge {
        margin-left: auto;
        padding: 3px 6px;
        border-radius: 999px;
        color: #6c737b;
        background: #fff;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .05em;
    }

    .admin-sidebar-bottom {
        padding: 12px;
        border-top: 1px solid #c7cdd2;
    }

    .admin-back-link {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px;
        border: 1px solid #c7cdd2;
        border-radius: 8px;
        color: #252c33;
        background: rgba(255, 255, 255, .7);
        font-size: 11px;
        font-weight: 800;
        text-decoration: none;
    }

    .admin-header {
        display: flex;
        min-width: 0;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 0 22px;
        border-bottom: 1px solid #d5dbe1;
        background: #fff;
    }

    .admin-header-title { min-width: 0; }

    .admin-header-title strong {
        display: block;
        overflow: hidden;
        font-size: 16px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .admin-header-title small {
        display: block;
        margin-top: 2px;
        color: var(--admin-muted);
        font-size: 10px;
    }

    .admin-header-actions {
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .admin-main {
        min-width: 0;
        padding: 24px;
        overflow: auto;
    }

    .admin-content {
        width: min(1440px, 100%);
        margin: 0 auto;
    }

    .admin-page-heading {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
    }

    .admin-page-heading h1 {
        margin: 0;
        font-size: clamp(22px, 2vw, 30px);
        letter-spacing: -.03em;
    }

    .admin-page-heading p {
        max-width: 720px;
        margin: 6px 0 0;
        color: var(--admin-muted);
        font-size: 12px;
        line-height: 1.55;
    }

    .admin-alert {
        margin-bottom: 16px;
        padding: 12px 14px;
        border: 1px solid;
        border-radius: 9px;
        font-size: 12px;
        line-height: 1.45;
    }

    .admin-alert.success {
        border-color: #a9dfc0;
        color: #105d35;
        background: #ecfaf2;
    }

    .admin-alert.error {
        border-color: #f0b8ba;
        color: #8e171c;
        background: #fff1f1;
    }

    .admin-alert ul { margin: 5px 0 0; padding-left: 18px; }

    .admin-grid { display: grid; gap: 15px; }
    .admin-grid.stats { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .admin-grid.modules { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .admin-grid.two { grid-template-columns: minmax(0, 1.5fr) minmax(280px, .7fr); }

    .admin-card {
        min-width: 0;
        border: 1px solid var(--admin-border);
        border-radius: 12px;
        background: #fff;
        box-shadow: var(--admin-shadow);
    }

    .admin-card-body { padding: 17px; }

    .admin-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 17px;
        border-bottom: 1px solid var(--admin-border);
    }

    .admin-card-header h2 {
        margin: 0;
        font-size: 14px;
    }

    .admin-card-header small {
        color: var(--admin-muted);
        font-size: 10px;
    }

    .admin-stat-card {
        position: relative;
        min-height: 112px;
        padding: 18px;
        overflow: hidden;
    }

    .admin-stat-card::after {
        position: absolute;
        top: 0;
        right: 0;
        width: 5px;
        height: 100%;
        background: var(--stat-color, var(--admin-red));
        content: '';
    }

    .admin-stat-card small {
        display: block;
        color: var(--admin-muted);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .admin-stat-card strong {
        display: block;
        margin-top: 9px;
        font-size: 30px;
        line-height: 1;
    }

    .admin-stat-card span {
        display: block;
        margin-top: 8px;
        color: var(--admin-muted);
        font-size: 10px;
    }

    .admin-section-title {
        margin: 25px 0 12px;
        font-size: 14px;
    }

    .admin-module-card {
        display: block;
        min-height: 132px;
        padding: 17px;
        color: inherit;
        text-decoration: none;
    }

    .admin-module-card.enabled:hover {
        border-color: #bac3cb;
        transform: translateY(-1px);
    }

    .admin-module-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .admin-module-icon {
        display: grid;
        width: 36px;
        height: 36px;
        place-items: center;
        border-radius: 10px;
        color: #fff;
        background: var(--module-color, #343b43);
        font-size: 14px;
        font-weight: 900;
    }

    .admin-status-pill {
        padding: 4px 8px;
        border-radius: 999px;
        color: #626c76;
        background: #edf0f2;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    .admin-status-pill.ready {
        color: #0f6236;
        background: #dff5e9;
    }

    .admin-module-card h3 { margin: 14px 0 5px; font-size: 13px; }

    .admin-module-card p {
        margin: 0;
        color: var(--admin-muted);
        font-size: 10px;
        line-height: 1.45;
    }

    .admin-table-wrap { overflow-x: auto; }

    .admin-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
    }

    .admin-table th,
    .admin-table td {
        padding: 11px 12px;
        border-bottom: 1px solid #e8ecf0;
        text-align: left;
        vertical-align: middle;
    }

    .admin-table th {
        color: #56616d;
        background: #f7f8fa;
        font-size: 9px;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .admin-table tr:last-child td { border-bottom: 0; }

    .admin-user-cell {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 180px;
    }

    .admin-avatar {
        display: grid;
        width: 32px;
        height: 32px;
        flex: 0 0 32px;
        place-items: center;
        overflow: hidden;
        border-radius: 50%;
        color: #fff;
        background: #2f3943;
        font-size: 10px;
        font-weight: 900;
    }

    .admin-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .admin-user-cell strong { display: block; font-size: 11px; }
    .admin-user-cell small { display: block; margin-top: 2px; color: var(--admin-muted); font-size: 9px; }

    .admin-role-pill,
    .admin-active-pill {
        display: inline-flex;
        padding: 4px 8px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 800;
    }

    .admin-role-pill { color: #234e76; background: #e4f1fb; }
    .admin-active-pill.active { color: #126238; background: #def5e8; }
    .admin-active-pill.inactive { color: #8d191e; background: #fde5e6; }

    .admin-form-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .admin-field label {
        display: block;
        margin-bottom: 5px;
        color: #47525e;
        font-size: 9px;
        font-weight: 850;
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    .admin-input,
    .admin-select {
        width: 100%;
        height: 38px;
        padding: 8px 10px;
        border: 1px solid #cfd6dd;
        border-radius: 7px;
        outline: none;
        color: #202830;
        background: #fff;
        font-size: 11px;
    }

    .admin-input:focus,
    .admin-select:focus {
        border-color: var(--admin-red);
        box-shadow: 0 0 0 3px rgba(215, 25, 32, .09);
    }

    .admin-checkbox {
        display: flex;
        height: 38px;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
        border: 1px solid #cfd6dd;
        border-radius: 7px;
        background: #fff;
        font-size: 11px;
    }

    .admin-btn {
        display: inline-flex;
        min-height: 36px;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 8px 13px;
        border: 1px solid transparent;
        border-radius: 7px;
        font-size: 10px;
        font-weight: 850;
        text-decoration: none;
    }

    .admin-btn.primary { color: #fff; background: var(--admin-red); }
    .admin-btn.primary:hover { background: var(--admin-red-dark); }
    .admin-btn.secondary { border-color: #cbd2d9; color: #26313b; background: #fff; }
    .admin-btn.warning { border-color: #efb2b5; color: #921c21; background: #fff4f4; }
    .admin-btn.success { border-color: #a7d8bb; color: #12613a; background: #effaf3; }
    .admin-btn:disabled { cursor: not-allowed; opacity: .45; }

    .admin-filter-bar {
        display: grid;
        grid-template-columns: minmax(220px, 1fr) 170px 190px auto;
        gap: 9px;
        align-items: end;
    }

    .admin-inline-form { display: flex; min-width: 250px; align-items: center; gap: 7px; }
    .admin-inline-form .admin-select { min-width: 150px; }

    .admin-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 16px;
        border-top: 1px solid var(--admin-border);
        color: var(--admin-muted);
        font-size: 10px;
    }

    .admin-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 18px;
        border-top: 1px solid #d4dae0;
        color: #6c7580;
        background: #fff;
        font-size: 9px;
    }

    .admin-mobile-toggle {
        display: none;
        width: 38px;
        height: 38px;
        border: 1px solid #d1d7dc;
        border-radius: 8px;
        background: #fff;
        font-size: 18px;
    }

    @media (max-width: 1100px) {
        .admin-grid.stats,
        .admin-grid.modules { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .admin-grid.two { grid-template-columns: 1fr; }
        .admin-form-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 780px) {
        .admin-shell { grid-template-columns: 1fr; }
        .admin-sidebar {
            position: fixed;
            z-index: 1000;
            top: 0;
            bottom: 0;
            left: 0;
            width: min(290px, 86vw);
            transform: translateX(-102%);
            transition: transform .2s ease;
        }
        .admin-shell.sidebar-open .admin-sidebar { transform: translateX(0); }
        .admin-header, .admin-main, .admin-footer { grid-column: 1; }
        .admin-mobile-toggle { display: inline-grid; place-items: center; }
        .admin-header { padding: 0 13px; }
        .admin-main { padding: 16px 12px; }
        .admin-grid.stats,
        .admin-grid.modules,
        .admin-form-grid { grid-template-columns: 1fr; }
        .admin-filter-bar { grid-template-columns: 1fr; }
        .admin-page-heading { align-items: flex-start; flex-direction: column; }
        .admin-header-actions > :not(.profile-dropdown) { display: none; }
    }
</style>
@endpush

@section('content')
<div class="admin-shell" id="adminShell">
    <aside class="admin-sidebar" aria-label="Navigasi Admin All">
        <div class="admin-brand">
            <img src="{{ asset('assets/images/syngypro-logo.png') }}" alt="SYNRGYPRO">
            <div>
                <strong>ADMIN ALL</strong>
                <small>Control Center</small>
            </div>
        </div>

        <nav class="admin-nav">
            <div class="admin-nav-label">Administrasi</div>

            <a href="{{ route('admin-all') }}"
               class="admin-nav-link {{ request()->routeIs('admin-all') ? 'active' : '' }}">
                <span class="admin-nav-icon">D</span>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('admin-all.users.index') }}"
               class="admin-nav-link {{ request()->routeIs('admin-all.users.*') ? 'active' : '' }}">
                <span class="admin-nav-icon">U</span>
                <span>User Management</span>
            </a>

            <span class="admin-nav-link planned">
                <span class="admin-nav-icon">R</span>
                <span>Role & Permission</span>
                <span class="admin-plan-badge">NEXT</span>
            </span>

            <span class="admin-nav-link planned">
                <span class="admin-nav-icon">A</span>
                <span>Activity Log</span>
                <span class="admin-plan-badge">PLAN</span>
            </span>

            <div class="admin-nav-label">Monitoring & Internal</div>

            @foreach ([
                ['S', 'Suggestion System'],
                ['I', 'IFUTS Case Desk'],
                ['M', 'MCU & FU Internal'],
                ['O', 'Stock Opname Gudang'],
                ['E', 'E-Arsip'],
            ] as [$icon, $label])
                <span class="admin-nav-link planned">
                    <span class="admin-nav-icon">{{ $icon }}</span>
                    <span>{{ $label }}</span>
                    <span class="admin-plan-badge">PLAN</span>
                </span>
            @endforeach
        </nav>

        <div class="admin-sidebar-bottom">
            <a href="{{ route('dashboard') }}" class="admin-back-link">
                ← Kembali ke Dashboard Utama
            </a>
        </div>
    </aside>

    <header class="admin-header">
        <div style="display:flex;align-items:center;gap:10px;min-width:0">
            <button type="button" class="admin-mobile-toggle" id="adminMobileToggle" aria-label="Buka menu">☰</button>
            <div class="admin-header-title">
                <strong>@yield('admin-page-title', 'Admin All')</strong>
                <small>SYNRGYPRO • Site BA</small>
            </div>
        </div>

        <div class="admin-header-actions">
            <x-module-shortcut />
            <x-profile-dropdown />
        </div>
    </header>

    <main class="admin-main">
        <div class="admin-content">
            @if (session('success'))
                <div class="admin-alert success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="admin-alert error">
                    <strong>Periksa kembali data berikut:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('admin-content')
        </div>
    </main>

    <footer class="admin-footer">
        <span>SYNRGYPRO Admin All</span>
        <span>© {{ date('Y') }} Produksi • Site BA</span>
    </footer>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const shell = document.getElementById('adminShell');
        const toggle = document.getElementById('adminMobileToggle');

        if (!shell || !toggle) return;

        toggle.addEventListener('click', function () {
            shell.classList.toggle('sidebar-open');
        });
    })();
</script>
@endpush
