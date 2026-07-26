@extends('layouts.app')

@section('title', 'Database — SYNRGYPRO')
@section('body-class', 'syn-database-page')


@php
    /*
    |--------------------------------------------------------------------------
    | DATA DASHBOARD
    |--------------------------------------------------------------------------
    | Nanti controller/API cukup mengirim variabel berikut:
    | $summaryData, $messDistribution, $statusDistribution, dan $employeeRows.
    | Data di bawah adalah fallback agar halaman tetap tampil sebelum API aktif.
    */

    $summaryData = $summaryData ?? [
        'total_karyawan' => 870,
        'tinggal_mess' => 360,
        'tinggal_non_mess' => 510,
    ];

    $messDistribution = collect($messDistribution ?? [
        ['label' => 'A1', 'value' => 78],
        ['label' => 'A2', 'value' => 63],
        ['label' => 'B1', 'value' => 83],
        ['label' => 'B2', 'value' => 52],
        ['label' => 'B3', 'value' => 55],
        ['label' => 'B4', 'value' => 57],
        ['label' => 'B5', 'value' => 62],
        ['label' => 'B6', 'value' => 48],
        ['label' => 'B7', 'value' => 59],
        ['label' => 'B8', 'value' => 65],
        ['label' => 'B9', 'value' => 72],
        ['label' => 'B10', 'value' => 83],
        ['label' => 'C03', 'value' => 65],
        ['label' => 'Mess', 'value' => 28],
    ])->map(function ($item) {
        return [
            'label' => (string) ($item['label'] ?? '-'),
            'value' => max(0, (int) ($item['value'] ?? 0)),
        ];
    })->values();

    $statusDistribution = collect($statusDistribution ?? [
        [
            'label' => 'Tinggal di Mess',
            'value' => (int) ($summaryData['tinggal_mess'] ?? 0),
            'color' => '#22c55e',
        ],
        [
            'label' => 'Tinggal Non Mess',
            'value' => (int) ($summaryData['tinggal_non_mess'] ?? 0),
            'color' => '#14b8a6',
        ],
    ])->map(function ($item, $index) {
        $palette = ['#22c55e', '#14b8a6', '#3b82f6', '#f59e0b', '#ef4444'];

        return [
            'label' => (string) ($item['label'] ?? '-'),
            'value' => max(0, (int) ($item['value'] ?? 0)),
            'color' => (string) ($item['color'] ?? $palette[$index % count($palette)]),
        ];
    })->values();

    $employeeRows = collect($employeeRows ?? [
        [
            'nrp' => '10001',
            'nama' => 'Contoh Karyawan 1',
            'status_tinggal' => 'Mess',
            'gedung_kamar' => 'A1 / 01',
            'kontak' => '0812-0000-0001',
        ],
        [
            'nrp' => '10002',
            'nama' => 'Contoh Karyawan 2',
            'status_tinggal' => 'Non Mess',
            'gedung_kamar' => '-',
            'kontak' => 'karyawan2@example.com',
        ],
        [
            'nrp' => '10003',
            'nama' => 'Contoh Karyawan 3',
            'status_tinggal' => 'Mess',
            'gedung_kamar' => 'B2 / 07',
            'kontak' => '0812-0000-0003',
        ],
    ])->values();

    $maxMessValue = max(1, (int) $messDistribution->max('value'));
    $statusTotal = max(1, (int) $statusDistribution->sum('value'));

    $pieStart = 0;
    $pieSegments = [];

    foreach ($statusDistribution as $statusItem) {
        $pieEnd = $pieStart + (($statusItem['value'] / $statusTotal) * 360);
        $pieSegments[] = sprintf(
            '%s %.2fdeg %.2fdeg',
            $statusItem['color'],
            $pieStart,
            $pieEnd
        );
        $pieStart = $pieEnd;
    }

    $pieGradient = implode(', ', $pieSegments);

    $databaseSubmenuIcon = asset('assets/images/database-submenu.png');
    $atrSubmenuIcon = file_exists(public_path('assets/images/ATR-submenu.png'))
        ? asset('assets/images/ATR-submenu.png')
        : asset('assets/images/ATR- submenu.png');
@endphp

@push('styles')
<style>
    * { box-sizing: border-box; }

    body.syn-database-page {
        margin: 0;
        overflow: hidden;
        background: #202020;
        font-family: Arial, Helvetica, sans-serif;
    }

    button, input, select { font: inherit; }

    .db-page {
        display: grid;
        min-height: 100vh;
        grid-template-columns: 180px minmax(0, 1fr);
        grid-template-rows: 68px minmax(0, 1fr) 24px;
        background: #f6f6f6;
        transition: grid-template-columns .25s ease;
    }

    /* SIDEBAR */
    .db-sidebar {
        display: flex;
        grid-row: 1 / 4;
        min-width: 0;
        flex-direction: column;
        border-right: 1px solid #bdbdbd;
        background: linear-gradient(135deg, #eeeeee 0%, #c7c7c7 100%);
    }

    .db-sidebar-head {
        display: grid;
        min-height: 68px;
        grid-template-columns: minmax(0, 1fr) 44px;
        border-bottom: 1px solid #aaa;
        background: #0c0a0a;
    }

    .db-sidebar-logo {
        display: grid;
        place-items: center;
        overflow: hidden;
        padding: 7px;
    }

.db-sidebar-logo img {
    display: block;
    width: 58px;
    height: 52px;
    object-fit: contain;
}

    .db-sidebar-toggle {
        display: grid;
        place-items: center;
        border: 0;
        border-left: 1px solid #aaa;
        color: #222;
        background: #fff;
        cursor: pointer;
        font-size: 27px;
    }

    .db-navigation {
        flex: 1;
        padding-top: 7px;
        overflow-x: hidden;
        overflow-y: auto;
    }

    .db-menu-link,
    .db-menu-toggle {
        display: flex;
        width: 100%;
        min-height: 34px;
        align-items: center;
        gap: 7px;
        padding: 7px 10px;
        border: 0;
        color: #111;
        background: transparent;
        cursor: pointer;
        font-size: 11px;
        font-weight: 800;
        text-align: left;
        text-decoration: none;
    }

    .db-menu-link:hover,
    .db-menu-toggle:hover,
    .db-menu-link.active,
    .db-menu-group.is-open > .db-menu-toggle {
        background: rgba(255,255,255,.62);
    }

    .db-menu-icon {
        display: grid;
        width: 20px;
        height: 20px;
        flex: 0 0 20px;
        place-items: center;
    }

    .db-menu-icon img {
        width: 19px;
        height: 19px;
        object-fit: contain;
    }

    .db-menu-label { flex: 1; min-width: 0; }

    .db-menu-arrow {
        margin-left: auto;
        font-size: 16px;
        transition: transform .2s ease;
    }

    .db-menu-group.is-open .db-menu-arrow { transform: rotate(90deg); }

    .db-submenu {
        display: grid;
        grid-template-rows: 0fr;
        opacity: 0;
        transition: grid-template-rows .22s ease, opacity .18s ease;
    }

    .db-menu-group.is-open .db-submenu {
        grid-template-rows: 1fr;
        opacity: 1;
    }

    .db-submenu-inner { overflow: hidden; }

    .db-submenu-button {
        position: relative;
        display: flex;
        width: 100%;
        min-height: 27px;
        align-items: center;
        padding: 5px 8px 5px 46px;
        border: 0;
        color: #222;
        background: transparent;
        cursor: pointer;
        font-size: 9px;
        font-weight: 700;
        text-align: left;
    }

    .db-submenu-button::before {
        position: absolute;
        left: 34px;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #444;
        content: '';
    }

    .db-submenu-button:hover,
    .db-submenu-button.active {
        background: rgba(255,255,255,.72);
    }

    .db-sidebar-bottom {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 3px;
        padding: 9px 7px 12px;
        text-align: center;
    }

    .db-bottom-link {
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 4px 0;
        color: #111;
        font-size: 9px;
        font-weight: 800;
        text-decoration: none;
    }

    .db-bottom-link.help span:first-child { color: #d71920; }

    /* HEADER */
    .db-header {
        display: grid;
        grid-column: 2;
        grid-row: 1;
        grid-template-columns: minmax(0, 1fr) auto;
        min-width: 0;
        border-bottom: 1px solid #cfcfcf;
        background: #fff;
    }

    .db-header-brand {
        display: flex;
        min-width: 0;
        align-items: center;
        justify-content: flex-end;
        padding: 0 18px;
        overflow: hidden;
        background: linear-gradient(90deg, #0d0b0b 0%, #171414 65%, #4d4d4d 100%);
    }

    .db-header-brand img {
        width: 108px;
        max-height: 44px;
        object-fit: contain;
    }

    .db-header-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0 10px;
        background: #fff;
    }

    .db-header-button {
        display: inline-grid;
        width: 43px;
        height: 43px;
        flex: 0 0 43px;
        place-items: center;
        padding: 0;
        overflow: hidden;
        border: 2px solid #111;
        border-radius: 50%;
        background: #fff;
        cursor: pointer;
        text-decoration: none;
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .db-header-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,.16);
    }

    .db-header-button img {
        width: 72%;
        height: 72%;
        object-fit: contain;
    }

    .db-profile-button img,
    .db-logout-button img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    .db-logout-form { display: flex; margin: 0; }
    .db-logout-button { border-color: transparent; }

    /* CONTENT */
    .db-content {
        grid-column: 2;
        grid-row: 2;
        min-width: 0;
        padding: 7px 10px;
        overflow: auto;
        background: #f7f7f7;
    }

    .db-view { display: none; min-height: 100%; }
    .db-view.active { display: block; }

    .db-view-title {
        margin: 0 0 6px;
        color: #111;
        font-size: 8px;
        font-weight: 700;
    }

    .db-panel { border-radius: 18px; background: #ededed; }

    .db-search-row {
        display: grid;
        grid-template-columns: minmax(160px,1fr) 84px minmax(140px,.8fr) 84px auto;
        gap: 6px;
        align-items: center;
        margin-bottom: 10px;
    }

    .db-input,
    .db-select {
        width: 100%;
        height: 23px;
        min-width: 0;
        padding: 0 10px;
        border: 0;
        border-radius: 7px;
        outline: 0;
        color: #222;
        background: #e9e9e9;
        font-size: 8px;
    }

    .db-search-button,
    .db-source-button,
    .db-upload-button {
        height: 24px;
        padding: 0 17px;
        border: 0;
        border-radius: 6px;
        color: #fff;
        background: #1478e8;
        cursor: pointer;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .db-source-button,
    .db-upload-button { background: #f12828; }

    .db-summary-panel { padding: 14px 15px 18px; }

    .db-stat-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(120px,1fr));
        gap: 43px;
        margin: 0 70px 20px;
    }

    .db-stat-card { text-align: center; }

    .db-stat-card small {
        display: block;
        margin-bottom: 6px;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .db-stat-value {
        display: grid;
        min-height: 36px;
        place-items: center;
        border-bottom: 5px solid transparent;
        background: #fff;
        font-size: 19px;
        font-weight: 900;
    }

    .db-stat-card:nth-child(2) .db-stat-value { border-color: #16f013; }
    .db-stat-card:nth-child(3) .db-stat-value { border-color: #13e3c4; }

    .db-chart-grid {
        display: grid;
        grid-template-columns: minmax(0,1.2fr) minmax(230px,.8fr);
        gap: 52px;
        margin: 0 32px 26px;
    }

    .db-chart-box {
        min-height: 130px;
        padding: 11px 20px;
        background: #fff;
    }

    .db-chart-title {
        margin-bottom: 8px;
        font-size: 7px;
        font-weight: 800;
    }

    .db-bars {
        display: flex;
        height: 92px;
        align-items: flex-end;
        gap: 7px;
        padding: 0 7px 17px;
        border-bottom: 1px solid #555;
        border-left: 1px solid #555;
    }

    .db-bar {
        position: relative;
        flex: 1;
        min-width: 8px;
        background: #d2d2d2;
    }

    .db-bar span {
        position: absolute;
        right: 0;
        bottom: -15px;
        left: 0;
        font-size: 6px;
        font-weight: 700;
        text-align: center;
    }

    .db-donut-wrap {
        display: grid;
        place-items: center;
        padding-top: 5px;
    }

    .db-donut-content {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 20px;
        width: 100%;
    }

    .db-donut {
        position: relative;
        width: 100px;
        height: 100px;
        flex: 0 0 100px;
        border-radius: 50%;
    }

    .db-donut::after {
        position: absolute;
        inset: 27px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        background: #ffffff;
        color: #222222;
        content: attr(data-total);
        font-size: 11px;
        font-weight: 900;
    }

    .db-donut-legend {
        display: grid;
        gap: 7px;
        min-width: 125px;
    }

    .db-donut-legend-item {
        display: grid;
        grid-template-columns: 9px minmax(0, 1fr) auto;
        gap: 6px;
        align-items: center;
        font-size: 7px;
    }

    .db-donut-legend-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
    }

    .db-bar-value {
        position: absolute;
        top: -11px;
        right: 0;
        left: 0;
        color: #444444;
        font-size: 6px;
        font-weight: 800;
        text-align: center;
    }

    .db-table-empty {
        padding: 18px !important;
        color: #777777;
        font-size: 8px;
        text-align: center !important;
    }

    .db-table-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 20px;
        padding: 0 9px;
        border: 0;
        border-radius: 5px;
        color: #ffffff;
        background: #1478e8;
        cursor: pointer;
        font-size: 7px;
        font-weight: 800;
        text-decoration: none;
    }

    .db-table-title {
        margin: 0 17px 8px;
        font-size: 7px;
        font-weight: 800;
    }

    .db-table {
        width: calc(100% - 30px);
        margin: 0 15px;
        border-collapse: collapse;
        background: #fff;
        font-size: 7px;
    }

    .db-table th,
    .db-table td {
        height: 25px;
        padding: 5px 7px;
        border: 1px solid #8f8f8f;
        text-align: center;
    }

    /* ATR RINGKASAN */
    .atr-toolbar {
        display: grid;
        grid-template-columns: 85px 95px minmax(200px,1fr);
        gap: 7px;
        padding: 6px 8px;
        border-radius: 3px;
        background: #f8fbff;
        box-shadow: 0 1px 5px rgba(0,0,0,.10);
    }

    .atr-toolbar-group small {
        display: block;
        margin-bottom: 2px;
        color: #5b5b5b;
        font-size: 6px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .atr-toolbar .db-input,
    .atr-toolbar .db-select {
        height: 20px;
        border: 1px solid #c8d8eb;
        border-radius: 2px;
        background: #fff;
        font-size: 7px;
    }

    .atr-progress {
        margin-top: 6px;
        padding: 7px 9px 9px;
        border-radius: 3px;
        background: #f7fbff;
        box-shadow: 0 1px 5px rgba(0,0,0,.10);
    }

    .atr-progress-title {
        margin-bottom: 6px;
        color: #d72828;
        font-size: 7px;
        font-weight: 800;
    }

    .atr-progress-values {
        display: grid;
        grid-template-columns: repeat(3,1fr);
        margin-bottom: 5px;
        text-align: center;
    }

    .atr-progress-values strong { display: block; font-size: 11px; }
    .atr-progress-values small { display: block; font-size: 5px; text-transform: uppercase; }
    .atr-progress-values > div:nth-child(1) strong { color: #ef3030; }
    .atr-progress-values > div:nth-child(2) strong { color: #1baf5d; }

    .atr-track {
        height: 4px;
        overflow: hidden;
        border-radius: 999px;
        background: #dce7f2;
    }

    .atr-bar { width: 20%; height: 100%; background: #14ae5c; }

    .atr-ranking-panel {
        margin-top: 9px;
        padding: 22px 13px 78px;
        border-radius: 22px;
        background: #eee;
    }

    .atr-ranking-title {
        margin-bottom: 6px;
        font-size: 7px;
        font-weight: 800;
    }

    .atr-ranking-table {
        width: 100%;
        overflow: hidden;
        border-collapse: collapse;
        border-radius: 7px;
        background: #fff;
        font-size: 6px;
    }

    .atr-ranking-table th,
    .atr-ranking-table td {
        height: 18px;
        padding: 3px 5px;
        border-bottom: 1px solid #e3e9ef;
        text-align: center;
    }

    .atr-ranking-table th:nth-child(2),
    .atr-ranking-table td:nth-child(2),
    .atr-ranking-table th:nth-child(3),
    .atr-ranking-table td:nth-child(3) { text-align: left; }

    .atr-name-cell { display: flex; align-items: center; gap: 5px; }

    .atr-avatar {
        display: grid;
        width: 15px;
        height: 15px;
        place-items: center;
        border-radius: 50%;
        color: #fff;
        background: #273a58;
        font-size: 5px;
        font-weight: 900;
    }

    .atr-badge {
        display: inline-flex;
        min-width: 25px;
        justify-content: center;
        padding: 2px 4px;
        border-radius: 999px;
        color: #fff;
        background: #ef3340;
        font-size: 5px;
        font-weight: 800;
    }

    /* ATR DETAIL */
    .atr-detail-topbar {
        display: grid;
        grid-template-columns: 80px 120px minmax(200px,1fr) auto;
        gap: 6px;
        align-items: end;
        padding: 8px;
        border-radius: 4px;
        background: #f8fbff;
        box-shadow: 0 1px 5px rgba(0,0,0,.08);
    }

    .db-upload-button {
        min-width: 120px;
        border-radius: 9px;
        text-transform: none;
    }

    .atr-card-panel {
        margin-top: 8px;
        padding: 10px 8px 34px;
        border-radius: 20px;
        background: #eee;
    }

    .atr-card-title {
        margin-bottom: 7px;
        font-size: 7px;
        font-weight: 800;
    }

    .atr-grid {
        display: grid;
        grid-template-columns: repeat(3,minmax(180px,1fr));
        gap: 8px;
    }

    .atr-card {
        overflow: hidden;
        border-radius: 5px;
        background: #fff;
        box-shadow: 0 1px 4px rgba(0,0,0,.12);
    }

    .atr-card-body { padding: 8px; }

    .atr-card-head {
        display: grid;
        grid-template-columns: 28px minmax(0,1fr) auto;
        gap: 7px;
        align-items: center;
        margin-bottom: 6px;
    }

    .atr-photo {
        display: grid;
        width: 28px;
        height: 28px;
        place-items: center;
        border-radius: 50%;
        color: #fff;
        background: #496b8d;
        font-size: 7px;
        font-weight: 900;
    }

    .atr-card-name strong {
        display: block;
        overflow: hidden;
        font-size: 6px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .atr-card-name small {
        display: block;
        margin-top: 2px;
        color: #8a8a8a;
        font-size: 5px;
    }

    .atr-score { color: #ff7b23; font-size: 11px; font-weight: 900; }

    .atr-meta { display: grid; gap: 3px; color: #5f5f5f; font-size: 5px; }
    .atr-meta-row { display: flex; justify-content: space-between; gap: 8px; }

    .atr-card-action {
        width: 100%;
        height: 16px;
        border: 0;
        color: #fff;
        background: #e72f43;
        cursor: pointer;
        font-size: 5px;
        font-weight: 800;
    }

    .atr-card-action.success {
        color: #149954;
        background: #e4f8ed;
        border: 1px solid #b8ebcc;
    }

    /* FOOTER */
    .db-footer {
        display: flex;
        grid-column: 2;
        grid-row: 3;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: #383838;
        font-size: 6px;
        font-weight: 800;
    }

    /* COLLAPSED */
    .db-page.sidebar-collapsed { grid-template-columns: 50px minmax(0,1fr); }
    .db-page.sidebar-collapsed .db-sidebar-head { grid-template-columns: 50px; }
    .db-page.sidebar-collapsed .db-sidebar-logo,
    .db-page.sidebar-collapsed .db-menu-label,
    .db-page.sidebar-collapsed .db-menu-arrow,
    .db-page.sidebar-collapsed .db-submenu,
    .db-page.sidebar-collapsed .db-sidebar-bottom { display: none; }
    .db-page.sidebar-collapsed .db-sidebar-toggle { width: 50px; border-left: 0; }
    .db-page.sidebar-collapsed .db-menu-link,
    .db-page.sidebar-collapsed .db-menu-toggle { justify-content: center; padding-inline: 0; }

    @media (max-width: 900px) {
        .db-page { grid-template-columns: 52px minmax(0,1fr); }
        .db-sidebar-head { grid-template-columns: 52px; }
        .db-sidebar-logo,
        .db-menu-label,
        .db-menu-arrow,
        .db-submenu,
        .db-sidebar-bottom { display: none; }
        .db-sidebar-toggle { width: 52px; border-left: 0; }
        .db-menu-link,
        .db-menu-toggle { justify-content: center; padding-inline: 0; }
        .db-search-row { grid-template-columns: 1fr auto; }
        .db-source-button { grid-column: 1 / -1; }
        .db-stat-grid { gap: 12px; margin-inline: 0; }
        .db-chart-grid { grid-template-columns: 1fr; gap: 12px; margin-inline: 0; }
        .atr-grid { grid-template-columns: repeat(2,minmax(160px,1fr)); }
    }

    @media (max-width: 600px) {
        body.syn-database-page { overflow: auto; }
        .db-page { min-width: 640px; }
    }
</style>
@endpush

@section('content')
<div class="db-page" id="databasePage">
    <aside class="db-sidebar">
        <div class="db-sidebar-head">
<div class="db-sidebar-logo">
    <img
        src="{{ asset('assets/images/DATABASE.png') }}"
        alt="Database"
    >
</div>
            <button type="button" class="db-sidebar-toggle" id="databaseSidebarToggle" aria-label="Buka atau tutup sidebar">☰</button>
        </div>

        <nav class="db-navigation" aria-label="Menu Database">
            <button type="button" class="db-menu-link active" data-db-view="database-summary">
                <span class="db-menu-icon">▦</span>
                <span class="db-menu-label">Dashboard</span>
            </button>

            <div class="db-menu-group is-open">
                <button type="button" class="db-menu-toggle" aria-expanded="true">
                    <span class="db-menu-icon">
                        <img src="{{ $databaseSubmenuIcon }}" alt="Database">
                    </span>
                    <span class="db-menu-label">Database</span>
                    <span class="db-menu-arrow">›</span>
                </button>

                <div class="db-submenu">
                    <div class="db-submenu-inner">
                        <button type="button" class="db-submenu-button active" data-db-view="database-summary">Ringkasan &amp; Pencarian</button>
                    </div>
                </div>
            </div>

            <div class="db-menu-group is-open">
                <button type="button" class="db-menu-toggle" aria-expanded="true">
                    <span class="db-menu-icon">
                        <img src="{{ $atrSubmenuIcon }}" alt="ATR Karyawan">
                    </span>
                    <span class="db-menu-label">ATR Karyawan</span>
                    <span class="db-menu-arrow">›</span>
                </button>

                <div class="db-submenu">
                    <div class="db-submenu-inner">
                        <button type="button" class="db-submenu-button" data-db-view="atr-summary">Ringkasan</button>
                        <button type="button" class="db-submenu-button" data-db-view="atr-detail">Detail dan Upload</button>
                    </div>
                </div>
            </div>
        </nav>

        <div class="db-sidebar-bottom">
            <a href="#" class="db-bottom-link"><span>⚙</span><span>Pengaturan</span></a>
            <a href="https://mail.google.com/mail/?view=cm&fs=1&to={{ urlencode(config('access.contact_email', 'mpe.ppaba@ppa.co.id')) }}&su=SYNRGYPRO%20Support" target="_blank" rel="noopener noreferrer" class="db-bottom-link help"><span>?</span><span>Bantuan</span></a>
        </div>
    </aside>

    <header class="db-header">
        <div class="db-header-brand">
            <img src="{{ asset('assets/images/synrgypro-logo.png') }}" alt="SYNRGYPRO">
        </div>

        <nav class="db-header-actions" aria-label="Shortcut pengguna">
            <a href="{{ route('dashboard') }}" class="db-header-button" aria-label="Dashboard">
                <img src="{{ asset('assets/images/LOGO HOME.jpeg') }}" alt="">
            </a>

            <button type="button" class="db-header-button db-profile-button" aria-label="Profil">
                @if (Auth::user()?->avatar)
                    <img src="{{ Auth::user()->avatar }}" alt="Foto profil {{ Auth::user()->name }}" referrerpolicy="no-referrer">
                @else
                    <img src="{{ asset('assets/images/profile.png') }}" alt="Profil">
                @endif
            </button>

            <form method="POST" action="{{ route('logout') }}" class="db-logout-form">
                @csrf
                <button type="submit" class="db-header-button db-logout-button" aria-label="Logout">
                    <img src="{{ asset('assets/images/LOGO LOGOUT.png') }}" alt="">
                </button>
            </form>
        </nav>
    </header>

    <main class="db-content">
        <section class="db-view active" id="database-summary">
            <p class="db-view-title">Pencarian</p>

            <div class="db-search-row">
                <input type="text" class="db-input" placeholder="NAMA/NIRP">
                <button type="button" class="db-search-button">Search</button>
                <select class="db-select">
                    <option value="">TEMPAT TINGGAL</option>
                    <option value="mess">Mess</option>
                    <option value="non-mess">Non Mess</option>
                </select>
                <button type="button" class="db-search-button">Search</button>
                <button type="button" class="db-source-button">Source Data</button>
            </div>

            <div class="db-panel db-summary-panel">
                <div class="db-stat-grid">
                    <div class="db-stat-card">
                        <small>Total Karyawan</small>
                        <div class="db-stat-value">
                            {{ number_format((int) ($summaryData['total_karyawan'] ?? 0)) }}
                        </div>
                    </div>

                    <div class="db-stat-card">
                        <small>Tinggal di Mess</small>
                        <div class="db-stat-value">
                            {{ number_format((int) ($summaryData['tinggal_mess'] ?? 0)) }}
                        </div>
                    </div>

                    <div class="db-stat-card">
                        <small>Tinggal Non Mess</small>
                        <div class="db-stat-value">
                            {{ number_format((int) ($summaryData['tinggal_non_mess'] ?? 0)) }}
                        </div>
                    </div>
                </div>

                <div class="db-chart-grid">
                    <div>
                        <div class="db-chart-title">CHART: Distribusi Penghuni Mess</div>

                        <div class="db-chart-box">
                            <div class="db-bars">
                                @forelse ($messDistribution as $messItem)
                                    @php
                                        $barHeight = max(
                                            3,
                                            round(($messItem['value'] / $maxMessValue) * 100, 2)
                                        );
                                    @endphp

                                    <div
                                        class="db-bar"
                                        style="height: {{ $barHeight }}%;"
                                        title="{{ $messItem['label'] }}: {{ $messItem['value'] }}"
                                    >
                                        <strong class="db-bar-value">
                                            {{ $messItem['value'] }}
                                        </strong>

                                        <span>{{ $messItem['label'] }}</span>
                                    </div>
                                @empty
                                    <div class="db-table-empty">
                                        Data distribusi mess belum tersedia.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="db-chart-title">CHART: Status Karyawan</div>

                        <div class="db-chart-box db-donut-wrap">
                            <div class="db-donut-content">
                                <div
                                    class="db-donut"
                                    data-total="{{ number_format($statusTotal) }}"
                                    style="background: conic-gradient({{ $pieGradient }});"
                                    aria-label="Status karyawan"
                                ></div>

                                <div class="db-donut-legend">
                                    @foreach ($statusDistribution as $statusItem)
                                        <div class="db-donut-legend-item">
                                            <span
                                                class="db-donut-legend-dot"
                                                style="background: {{ $statusItem['color'] }};"
                                            ></span>

                                            <span>{{ $statusItem['label'] }}</span>
                                            <strong>{{ number_format($statusItem['value']) }}</strong>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="db-table-title">TABEL DATA RINGKASAN</div>

                <table class="db-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NRP</th>
                            <th>Nama Karyawan</th>
                            <th>Status Tinggal</th>
                            <th>Gedung/Kamar</th>
                            <th>No. HP / Email</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($employeeRows as $index => $employeeRow)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $employeeRow['nrp'] ?? '-' }}</td>
                                <td>{{ $employeeRow['nama'] ?? '-' }}</td>
                                <td>{{ $employeeRow['status_tinggal'] ?? '-' }}</td>
                                <td>{{ $employeeRow['gedung_kamar'] ?? '-' }}</td>
                                <td>{{ $employeeRow['kontak'] ?? '-' }}</td>
                                <td>
                                    <button
                                        type="button"
                                        class="db-table-action"
                                        data-employee-nrp="{{ $employeeRow['nrp'] ?? '' }}"
                                    >
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="db-table-empty">
                                    Data karyawan belum tersedia.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="db-view" id="atr-summary">
            <div class="atr-toolbar">
                <div class="atr-toolbar-group"><small>Bulan</small><select class="db-select"><option>Juni 2026</option><option>Mei 2026</option></select></div>
                <div class="atr-toolbar-group"><small>Posisi / Jabatan</small><select class="db-select"><option>Semua Posisi</option></select></div>
                <div class="atr-toolbar-group"><small>Cari Karyawan</small><input type="text" class="db-input" placeholder="Cari NRP atau Nama..."></div>
            </div>

            <div class="atr-progress">
                <div class="atr-progress-title">▲ Progress Pemanggilan</div>
                <div class="atr-progress-values">
                    <div><strong>169</strong><small>Belum</small></div>
                    <div><strong>41</strong><small>Sudah</small></div>
                    <div><strong>210</strong><small>Total Perlu</small></div>
                </div>
                <div class="atr-track"><div class="atr-bar"></div></div>
            </div>

            <div class="atr-ranking-panel">
                <div class="atr-ranking-title">Top 10 Absensi &amp; Alfa Terbanyak</div>
                <table class="atr-ranking-table">
                    <thead><tr><th>#</th><th>Nama</th><th>Jabatan</th><th>S</th><th>I</th><th>A</th><th>Total</th><th>ATR%</th></tr></thead>
                    <tbody>
                        @foreach ([
                            ['Andri Oktariyanta','Operator HD 785',3,2,0,'88.2%'],
                            ['Ridwan Husuma','Operator GD 825',4,0,0,'87.5%'],
                            ['Wida Ardiyanto','Operator PC 500',0,3,1,'91.3%'],
                            ['Tri Prasetyo','Operator HD 785',1,3,0,'93.6%'],
                            ['Deko Prayama','Operator HD 785',2,2,0,'90.1%'],
                            ['Firman Akbar','Operator HD 785',1,3,0,'92.5%'],
                            ['Jono','Operator GD 825',1,2,0,'94.0%'],
                            ['Abdulla Hozoini Hart','Operator DT 135',0,4,0,'89.7%'],
                            ['Joko Wib','Operator HD 825',0,3,0,'91.8%'],
                            ['Ramita Adriana','Operator HD 785',2,2,0,'90.4%']
                        ] as $index => $employee)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><div class="atr-name-cell"><span class="atr-avatar">{{ strtoupper(substr($employee[0], 0, 1)) }}</span><span>{{ $employee[0] }}</span></div></td>
                                <td>{{ $employee[1] }}</td>
                                <td>{{ $employee[2] }}</td><td>{{ $employee[3] }}</td><td>{{ $employee[4] }}</td>
                                <td>{{ $employee[2] + $employee[3] + $employee[4] }}</td>
                                <td><span class="atr-badge">{{ $employee[5] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="db-view" id="atr-detail">
            <div class="atr-detail-topbar">
                <div class="atr-toolbar-group"><small>Bulan</small><select class="db-select"><option>Juni 2026</option></select></div>
                <div class="atr-toolbar-group"><small>Posisi / Jabatan</small><select class="db-select"><option>Semua Posisi</option></select></div>
                <div class="atr-toolbar-group"><small>Cari Karyawan</small><input type="text" class="db-input" placeholder="Cari NRP atau Nama..."></div>
                <button type="button" class="db-upload-button">Upload Data</button>
            </div>

            <div class="atr-progress">
                <div class="atr-progress-title">▲ Progress Pemanggilan</div>
                <div class="atr-progress-values">
                    <div><strong>169</strong><small>Belum</small></div>
                    <div><strong>41</strong><small>Sudah</small></div>
                    <div><strong>210</strong><small>Total Perlu</small></div>
                </div>
                <div class="atr-track"><div class="atr-bar"></div></div>
            </div>

            <div class="atr-card-panel">
                <div class="atr-card-title">Karyawan ATR di Bawah 98.5%</div>
                <div class="atr-grid">
                    @foreach ([
                        ['Nanang Sartani','Operator HD 785','96.4%',false],
                        ['Joni','Operator PC 500','85.7%',false],
                        ['Dedza Audio Bayu Pradama','Operator HD 785','90.5%',true],
                        ['Doni Ichwan Hermawan','Operator HD 785','95.7%',false],
                        ['Alfin Fasbilillah','Operator GD 825','90.9%',false],
                        ['Rama Maulana Santoso','Operator PC 500','95.7%',false],
                        ['Dhyasadi Satya','Operator DT 135','91.3%',false],
                        ['Ardiansyah Tri Pamungkas','Operator HD 785','96.4%',false],
                        ['Rohmandiga Wenisha','Operator GD 825','92.3%',false],
                        ['Abdulla Hozoini Hart','Operator HD 785','85.7%',false],
                        ['Ratna Nona Furwemi','Operator HD 785','91.3%',false],
                        ['Rima Mahatindra Putra','Operator PC 500','96.3%',false]
                    ] as $employee)
                        <article class="atr-card">
                            <div class="atr-card-body">
                                <div class="atr-card-head">
                                    <div class="atr-photo">{{ strtoupper(substr($employee[0], 0, 1)) }}</div>
                                    <div class="atr-card-name"><strong>{{ $employee[0] }}</strong><small>{{ $employee[1] }}</small></div>
                                    <div class="atr-score">{{ $employee[2] }}</div>
                                </div>
                                <div class="atr-meta">
                                    <div class="atr-meta-row"><span>Sakit / Izin / Alfa</span><strong>1 / 0 / 0</strong></div>
                                    <div class="atr-meta-row"><span>Bapak Asuh</span><strong>Supervisor</strong></div>
                                </div>
                            </div>
                            <button type="button" class="atr-card-action {{ $employee[3] ? 'success' : '' }}">{{ $employee[3] ? 'Sudah Dipanggil' : 'Lakukan Pemanggilan' }}</button>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    </main>

    <footer class="db-footer">@COPYRIGHT SYNRGYPRO {{ date('Y') }}. V1.0</footer>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const page = document.getElementById('databasePage');
        const sidebarToggle = document.getElementById('databaseSidebarToggle');
        const menuGroups = document.querySelectorAll('.db-menu-group');
        const viewButtons = document.querySelectorAll('[data-db-view]');
        const views = document.querySelectorAll('.db-view');

        if (page && sidebarToggle) {
            sidebarToggle.addEventListener('click', function () {
                page.classList.toggle('sidebar-collapsed');
            });
        }

        menuGroups.forEach(function (group) {
            const toggle = group.querySelector('.db-menu-toggle');
            if (!toggle) return;

            toggle.addEventListener('click', function () {
                const willOpen = !group.classList.contains('is-open');
                group.classList.toggle('is-open', willOpen);
                toggle.setAttribute('aria-expanded', String(willOpen));
            });
        });

        viewButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const targetId = button.dataset.dbView;
                const targetView = document.getElementById(targetId);
                if (!targetView) return;

                views.forEach(function (view) {
                    view.classList.toggle('active', view.id === targetId);
                });

                viewButtons.forEach(function (item) {
                    item.classList.toggle('active', item.dataset.dbView === targetId);
                });

                const parentGroup = button.closest('.db-menu-group');
                if (parentGroup) {
                    parentGroup.classList.add('is-open');
                    const parentToggle = parentGroup.querySelector('.db-menu-toggle');
                    if (parentToggle) parentToggle.setAttribute('aria-expanded', 'true');
                }
            });
        });
    });
</script>
@endpush