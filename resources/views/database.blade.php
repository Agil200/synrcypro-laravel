@extends('layouts.app')

@section('title', 'Database — SYNRGYPRO')
@section('body-class', 'syn-database-page')

@push('styles')
<style>
    :root {
        --db-sidebar-width: 220px;
        --db-sidebar-collapsed: 72px;
        --db-header-height: 64px;
        --db-footer-height: 28px;

        --db-bg: #f3f5f7;
        --db-surface: #ffffff;
        --db-surface-soft: #f8fafc;
        --db-border: #dce2e8;
        --db-text: #1f2937;
        --db-muted: #6b7280;

        --db-black: #121212;
        --db-red: #d71920;
        --db-blue: #1478e8;
        --db-green: #20b26b;
        --db-cyan: #11b8a6;
        --db-orange: #f59e0b;

        --db-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
    }

    * {
        box-sizing: border-box;
    }

    body.syn-database-page {
        margin: 0;
        overflow: hidden;
        color: var(--db-text);
        background: var(--db-bg);
        font-family: Arial, Helvetica, sans-serif;
    }

    button,
    input,
    select {
        font: inherit;
    }

    button {
        cursor: pointer;
    }

.db-page {
    min-height: 100vh;
    height: 100vh;

    display: grid;
    grid-template-columns: 220px minmax(0, 1fr);
    grid-template-rows: 64px minmax(0, 1fr) 28px;

    overflow: hidden;
}

    /* =====================================================
       SIDEBAR
       ===================================================== */

    .db-sidebar {
        display: flex;
        grid-row: 1 / 4;
        min-width: 0;
        flex-direction: column;
        border-right: 1px solid #c7ccd2;
        background:
            linear-gradient(
                180deg,
                #f1f1f1 0%,
                #dddddd 100%
            );
    }

    .db-sidebar-head {
        display: grid;
        min-height: var(--db-header-height);
        grid-template-columns: minmax(0, 1fr) 52px;
        border-bottom: 1px solid #606060;
        background: var(--db-black);
    }

    .db-sidebar-logo {
        display: grid;
        place-items: center;
        min-width: 0;
        padding: 5px;
        overflow: hidden;
    }

    /* Logo besar di sebelah tombol hamburger */
    .db-sidebar-logo img {
        display: block;
        width: 76px;
        height: 52px;
        object-fit: contain;
    }

    .db-sidebar-toggle {
        display: grid;
        place-items: center;
        padding: 0;
        border: 0;
        border-left: 1px solid #666666;
        color: #151515;
        background: #ffffff;
        font-size: 28px;
        line-height: 1;
    }

    .db-navigation {
        flex: 1;
        padding: 10px 0;
        overflow-x: hidden;
        overflow-y: auto;
    }

    .db-menu-link,
    .db-menu-toggle {
        display: flex;
        width: 100%;
        min-height: 44px;
        align-items: center;
        gap: 11px;
        padding: 10px 15px;
        border: 0;
        color: #111111;
        background: transparent;
        font-size: 13px;
        font-weight: 800;
        line-height: 1.25;
        text-align: left;
        text-decoration: none;
        transition:
            background 0.18s ease,
            color 0.18s ease;
    }

    .db-menu-link:hover,
    .db-menu-toggle:hover,
    .db-menu-link.active,
    .db-menu-group.is-open > .db-menu-toggle {
        background: rgba(255, 255, 255, 0.78);
    }

    .db-menu-icon {
        display: grid;
        width: 23px;
        height: 23px;
        flex: 0 0 23px;
        place-items: center;
        color: #111111;
        font-size: 17px;
    }

    .db-menu-icon img {
        display: block;
        width: 21px;
        height: 21px;
        opacity: 0.86;
        object-fit: contain;
    }

    .db-menu-label {
        min-width: 0;
        flex: 1;
    }

    .db-menu-arrow {
        display: inline-grid;
        width: 18px;
        height: 18px;
        place-items: center;
        margin-left: auto;
        font-size: 18px;
        transition: transform 0.2s ease;
    }

    .db-menu-group.is-open .db-menu-arrow {
        transform: rotate(90deg);
    }

    .db-submenu {
        display: grid;
        grid-template-rows: 0fr;
        opacity: 0;
        transition:
            grid-template-rows 0.22s ease,
            opacity 0.18s ease;
    }

    .db-menu-group.is-open .db-submenu {
        grid-template-rows: 1fr;
        opacity: 1;
    }

    .db-submenu-inner {
        overflow: hidden;
    }

    .db-submenu-button {
        position: relative;
        display: flex;
        width: 100%;
        min-height: 35px;
        align-items: center;
        padding: 7px 14px 7px 50px;
        border: 0;
        border-left: 4px solid transparent;
        color: #2f3742;
        background: transparent;
        font-size: 12px;
        font-weight: 700;
        text-align: left;
        transition:
            background 0.18s ease,
            border-color 0.18s ease;
    }

    .db-submenu-button::before {
        position: absolute;
        left: 34px;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #444444;
        content: "";
    }

    .db-submenu-button:hover,
    .db-submenu-button.active {
        border-left-color: var(--db-red);
        color: #111111;
        background: rgba(255, 255, 255, 0.9);
    }

    .db-sidebar-bottom {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
        padding: 14px 12px 18px;
    }

    .db-bottom-link {
        display: inline-flex;
        width: 100%;
        max-width: 155px;
        min-height: 34px;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border-radius: 8px;
        color: #111111;
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
        transition: background 0.18s ease;
    }

    .db-bottom-link:hover {
        background: rgba(255, 255, 255, 0.75);
    }

    .db-bottom-link.help span:first-child {
        color: var(--db-red);
    }

    /* =====================================================
       HEADER
       ===================================================== */

    .db-header {
        display: grid;
        grid-column: 2;
        grid-row: 1;
        min-width: 0;
        grid-template-columns: minmax(0, 1fr) auto;
        border-bottom: 1px solid var(--db-border);
        background: #ffffff;
    }

    .db-header-brand {
        display: flex;
        min-width: 0;
        align-items: center;
        justify-content: flex-end;
        padding: 0 18px;
        overflow: hidden;
        background:
            linear-gradient(
                90deg,
                #101010 0%,
                #1d1b1b 62%,
                #454545 100%
            );
    }

    .db-header-brand img {
        display: block;
        width: 125px;
        max-height: 45px;
        object-fit: contain;
    }

    .db-header-actions {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 0 11px;
        background: #ffffff;
    }

    .db-header-button {
        display: inline-grid;
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        place-items: center;
        padding: 0;
        overflow: hidden;
        border: 2px solid #111111;
        border-radius: 50%;
        background: #ffffff;
        text-decoration: none;
        transition:
            transform 0.18s ease,
            box-shadow 0.18s ease;
    }

    .db-header-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 16px rgba(0, 0, 0, 0.14);
    }

    .db-header-button img {
        display: block;
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

    .db-logout-form {
        display: flex;
        margin: 0;
    }

    .db-logout-button {
        border-color: transparent;
    }

    /* =====================================================
       CONTENT
       ===================================================== */

.db-content {
    grid-column: 2;
    grid-row: 2;

    min-width: 0;
    min-height: 0;

    padding: 14px;
    overflow-x: hidden;
    overflow-y: auto;

    background: #f3f5f7;
}

    .db-view {
        display: none;
        min-height: 100%;
    }

    .db-view.active {
        display: block;
    }

    .db-page-title {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 12px;
    }

    .db-page-title h1 {
        margin: 0;
        color: #111827;
        font-size: 21px;
        line-height: 1.15;
    }

    .db-page-title p {
        margin: 4px 0 0;
        color: var(--db-muted);
        font-size: 12px;
    }

    .db-filter-panel,
    .db-kpi-card,
    .db-chart-card,
    .db-table-card,
    .atr-panel {
        border: 1px solid var(--db-border);
        background: var(--db-surface);
        box-shadow: var(--db-shadow);
    }

    /* =====================================================
       FILTER
       ===================================================== */

    .db-filter-panel {
        display: grid;
        grid-template-columns:
            minmax(220px, 1fr)
            minmax(170px, 0.48fr)
            auto;
        gap: 12px;
        align-items: end;
        margin-bottom: 13px;
        padding: 13px;
        border-radius: 13px;
    }

    .db-field {
        display: grid;
        gap: 6px;
    }

    .db-field label {
        color: #374151;
        font-size: 12px;
        font-weight: 800;
    }

    .db-input,
    .db-select {
        width: 100%;
        height: 39px;
        min-width: 0;
        padding: 0 12px;
        border: 1px solid #ccd3da;
        border-radius: 9px;
        outline: none;
        color: var(--db-text);
        background: #ffffff;
        font-size: 13px;
    }

    .db-input:focus,
    .db-select:focus {
        border-color: var(--db-blue);
        box-shadow: 0 0 0 3px rgba(20, 120, 232, 0.12);
    }

    .db-filter-actions {
        display: flex;
        gap: 8px;
    }

    .db-button {
        display: inline-flex;
        min-height: 39px;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 0 15px;
        border: 0;
        border-radius: 9px;
        color: #ffffff;
        background: var(--db-blue);
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .db-button.secondary {
        border: 1px solid #ccd3da;
        color: #374151;
        background: #ffffff;
    }

    .db-button.dark {
        background: #30363d;
    }

    .db-button.danger {
        background: var(--db-red);
    }

    /* =====================================================
       KPI
       ===================================================== */

    .db-kpi-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 13px;
        margin-bottom: 13px;
    }

    .db-kpi-card {
        display: grid;
        width: 100%;
        min-height: 94px;
        grid-template-columns: 50px minmax(0, 1fr);
        gap: 12px;
        align-items: center;
        padding: 14px;
        border-radius: 13px;
        color: inherit;
        font-family: inherit;
        text-align: left;
        transition:
            transform 0.18s ease,
            border-color 0.18s ease,
            box-shadow 0.18s ease;
    }

    .db-kpi-card:hover,
    .db-kpi-card.active {
        border-color: #aeb7c1;
        box-shadow: 0 11px 28px rgba(15, 23, 42, 0.12);
        transform: translateY(-2px);
    }

    .db-kpi-icon {
        display: grid;
        width: 50px;
        height: 50px;
        place-items: center;
        border-radius: 14px;
        color: #ffffff;
        background: #30363d;
        font-size: 22px;
    }

    .db-kpi-card:nth-child(2) .db-kpi-icon {
        background: var(--db-green);
    }

    .db-kpi-card:nth-child(3) .db-kpi-icon {
        background: var(--db-cyan);
    }

    .db-kpi-card small {
        display: block;
        margin-bottom: 5px;
        color: var(--db-muted);
        font-size: 12px;
        font-weight: 800;
    }

    .db-kpi-value {
        display: block;
        color: #111827;
        font-size: 28px;
        font-weight: 900;
        line-height: 1;
    }

    /* =====================================================
       CHART
       ===================================================== */

    .db-chart-grid {
        display: grid;
        grid-template-columns:
            minmax(0, 1.55fr)
            minmax(310px, 0.75fr);
        gap: 13px;
        margin-bottom: 13px;
    }

    .db-chart-card,
    .db-table-card,
    .atr-panel {
        border-radius: 13px;
        overflow: hidden;
    }

    .db-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 15px;
        border-bottom: 1px solid var(--db-border);
    }

    .db-card-header h2 {
        margin: 0;
        color: #111827;
        font-size: 14px;
    }

    .db-card-header small {
        color: var(--db-muted);
        font-size: 11px;
    }

    .db-chart-body {
        min-height: 235px;
        padding: 16px;
    }

    .db-bars {
        display: flex;
        height: 196px;
        align-items: flex-end;
        gap: 8px;
        padding: 0 6px 24px;
        border-bottom: 1px solid #9ca3af;
        border-left: 1px solid #9ca3af;
    }

    .db-bar {
        position: relative;
        flex: 1;
        min-width: 11px;
        max-width: 44px;
        border-radius: 6px 6px 0 0;
        background: #cfd4da;
        transition:
            background 0.18s ease,
            transform 0.18s ease;
    }

    .db-bar:hover {
        background: var(--db-blue);
        transform: translateY(-2px);
    }

    .db-bar-value {
        position: absolute;
        top: -18px;
        right: 0;
        left: 0;
        color: #374151;
        font-size: 10px;
        font-weight: 900;
        text-align: center;
    }

    .db-bar-label {
        position: absolute;
        right: -6px;
        bottom: -20px;
        left: -6px;
        color: var(--db-muted);
        font-size: 9px;
        font-weight: 700;
        text-align: center;
    }

    .db-donut-layout {
        display: grid;
        min-height: 205px;
        grid-template-columns: 150px minmax(0, 1fr);
        gap: 16px;
        place-items: center;
    }

    .db-donut {
        position: relative;
        display: grid;
        width: 145px;
        height: 145px;
        place-items: center;
        border-radius: 50%;
        background:
            conic-gradient(
                var(--db-green) 0deg var(--mess-angle),
                var(--db-cyan) var(--mess-angle) 360deg
            );
    }

    .db-donut::after {
        position: absolute;
        width: 84px;
        height: 84px;
        border-radius: 50%;
        background: #ffffff;
        content: "";
    }

    .db-donut-center {
        position: relative;
        z-index: 1;
        text-align: center;
    }

    .db-donut-center strong {
        display: block;
        font-size: 24px;
    }

    .db-donut-center span {
        color: var(--db-muted);
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .db-legend {
        display: grid;
        width: 100%;
        gap: 11px;
    }

    .db-legend-item {
        display: grid;
        grid-template-columns: 12px minmax(0, 1fr) auto;
        gap: 8px;
        align-items: center;
        font-size: 12px;
    }

    .db-legend-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--db-green);
    }

    .db-legend-item:nth-child(2) .db-legend-dot {
        background: var(--db-cyan);
    }

    /* =====================================================
       TABLE
       ===================================================== */

    .db-table-wrap {
        max-height: 255px;
        overflow: auto;
    }

    .db-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 12px;
    }

    .db-table th,
    .db-table td {
        padding: 10px 11px;
        border-bottom: 1px solid var(--db-border);
        text-align: left;
        white-space: nowrap;
    }

    .db-table th {
        position: sticky;
        top: 0;
        z-index: 2;
        color: #374151;
        background: #f8fafc;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: 0.2px;
        text-transform: uppercase;
    }

    .db-table tbody tr:hover {
        background: #f8fbff;
    }

    .db-badge {
        display: inline-flex;
        min-height: 24px;
        align-items: center;
        padding: 0 9px;
        border-radius: 999px;
        color: #0f7b50;
        background: #e6f8f0;
        font-size: 10px;
        font-weight: 900;
    }

    .db-badge.non-mess {
        color: #087c73;
        background: #e4faf7;
    }

    .db-detail-button {
        min-height: 30px;
        padding: 0 11px;
        border: 0;
        border-radius: 7px;
        color: #ffffff;
        background: var(--db-blue);
        font-size: 11px;
        font-weight: 900;
    }

    .db-empty-state {
        padding: 32px 16px !important;
        color: var(--db-muted);
        text-align: center !important;
    }

    /* =====================================================
       STATISTIK ATR
       ===================================================== */

    .atr-statistics-panel {
        margin-bottom: 12px;
        padding: 14px;
        border-radius: 13px;
    }

    .atr-statistics-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 12px;
    }

    .atr-statistics-title {
        margin: 0;
        color: #111827;
        font-size: 14px;
        font-weight: 900;
    }

    .atr-statistics-badge {
        display: inline-flex;
        min-height: 24px;
        align-items: center;
        justify-content: center;
        padding: 0 10px;
        border-radius: 999px;
        color: #ef3340;
        background: #ffe8eb;
        font-size: 10px;
        font-weight: 900;
        white-space: nowrap;
    }

    .atr-statistics-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .atr-stat-card {
        position: relative;
        display: grid;
        min-height: 76px;
        place-items: center;
        padding: 12px;
        overflow: hidden;
        border: 1px solid #e2e7ec;
        border-top: 3px solid var(--stat-color, #64748b);
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 5px 14px rgba(15, 23, 42, 0.06);
        text-align: center;
    }

    .atr-stat-card strong {
        display: block;
        color: var(--stat-color, #334155);
        font-size: 23px;
        font-weight: 900;
        line-height: 1;
    }

    .atr-stat-card small {
        display: block;
        margin-top: 6px;
        color: #718096;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: 0.2px;
        text-transform: uppercase;
    }

    .atr-stat-card.is-safe {
        --stat-color: #18b85f;
    }

    .atr-stat-card.is-below {
        --stat-color: #ef3340;
    }

    .atr-stat-card.is-no-data {
        --stat-color: #64748b;
    }

    .atr-stat-card.is-sick {
        --stat-color: #f59e0b;
    }

    .atr-stat-card.is-permission {
        --stat-color: #3978f6;
    }

    .atr-stat-card.is-alpha {
        --stat-color: #ff3158;
    }

    @media (max-width: 900px) {
        .atr-statistics-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    /* =====================================================
       ATR RINGKASAN
       ===================================================== */

    .atr-toolbar {
        display: grid;
        grid-template-columns:
            minmax(130px, 0.35fr)
            minmax(180px, 0.55fr)
            minmax(220px, 1fr);
        gap: 12px;
        margin-bottom: 12px;
        padding: 13px;
        border-radius: 13px;
    }

    .atr-progress {
        margin-bottom: 12px;
        padding: 14px;
        border-radius: 13px;
    }

    .atr-progress-title {
        margin-bottom: 10px;
        color: var(--db-red);
        font-size: 13px;
        font-weight: 900;
    }

    .atr-progress-values {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        margin-bottom: 8px;
        text-align: center;
    }

    .atr-progress-values strong {
        display: block;
        font-size: 22px;
    }

    .atr-progress-values small {
        display: block;
        margin-top: 3px;
        color: var(--db-muted);
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .atr-progress-values > div:nth-child(1) strong {
        color: #ef3030;
    }

    .atr-progress-values > div:nth-child(2) strong {
        color: var(--db-green);
    }

    .atr-track {
        height: 7px;
        overflow: hidden;
        border-radius: 999px;
        background: #dce7f2;
    }

    .atr-bar {
        width: 20%;
        height: 100%;
        border-radius: inherit;
        background: var(--db-green);
    }

    .atr-ranking-wrap {
        max-height: 455px;
        overflow: auto;
    }

    .atr-ranking-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 12px;
    }

    .atr-ranking-table th,
    .atr-ranking-table td {
        padding: 9px 10px;
        border-bottom: 1px solid var(--db-border);
        text-align: center;
    }

    .atr-ranking-table th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f8fafc;
        font-size: 10px;
        text-transform: uppercase;
    }

    .atr-ranking-table th:nth-child(2),
    .atr-ranking-table td:nth-child(2),
    .atr-ranking-table th:nth-child(3),
    .atr-ranking-table td:nth-child(3) {
        text-align: left;
    }

    .atr-name-cell {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .atr-avatar {
        display: grid;
        width: 28px;
        height: 28px;
        flex: 0 0 28px;
        place-items: center;
        border-radius: 50%;
        color: #ffffff;
        background: #273a58;
        font-size: 10px;
        font-weight: 900;
    }

    .atr-score-badge {
        display: inline-flex;
        min-width: 48px;
        justify-content: center;
        padding: 4px 7px;
        border-radius: 999px;
        color: #ffffff;
        background: #ef3340;
        font-size: 10px;
        font-weight: 900;
    }

    /* =====================================================
       ATR DETAIL
       ===================================================== */

    .atr-detail-topbar {
        display: grid;
        grid-template-columns:
            minmax(130px, 0.35fr)
            minmax(180px, 0.55fr)
            minmax(220px, 1fr)
            auto;
        gap: 12px;
        align-items: end;
        margin-bottom: 12px;
        padding: 13px;
        border-radius: 13px;
    }

.atr-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(220px, 1fr));
    gap: 12px;
    padding: 14px 14px 30px;
}

    .atr-card {
        overflow: hidden;
        border: 1px solid var(--db-border);
        border-radius: 10px;
        background: #ffffff;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    }

    .atr-card-body {
        padding: 12px;
    }

    .atr-card-head {
        display: grid;
        grid-template-columns: 38px minmax(0, 1fr) auto;
        gap: 9px;
        align-items: center;
        margin-bottom: 9px;
    }

    .atr-photo {
        display: grid;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 50%;
        color: #ffffff;
        background: #496b8d;
        font-size: 12px;
        font-weight: 900;
    }

    .atr-card-name strong {
        display: block;
        overflow: hidden;
        color: #111827;
        font-size: 12px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .atr-card-name small {
        display: block;
        margin-top: 3px;
        color: var(--db-muted);
        font-size: 10px;
    }

    .atr-score {
        color: var(--db-orange);
        font-size: 18px;
        font-weight: 900;
    }

    .atr-meta {
        display: grid;
        gap: 5px;
        color: #5f5f5f;
        font-size: 10px;
    }

    .atr-meta-row {
        display: flex;
        justify-content: space-between;
        gap: 8px;
    }

    .atr-card-action {
        width: 100%;
        min-height: 30px;
        border: 0;
        color: #ffffff;
        background: #e72f43;
        font-size: 10px;
        font-weight: 900;
    }

    .atr-card-action.success {
        border-top: 1px solid #b8ebcc;
        color: #149954;
        background: #e4f8ed;
    }

    .atr-card-footer {
        min-height: 34px;
    }

    .atr-call-open {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .atr-called-state {
        display: grid;
        min-height: 62px;
        place-items: center;
        padding: 8px 10px;
        border-top: 1px solid #b8ebcc;
        color: #138a4e;
        background: #eafaf1;
        text-align: center;
    }

    .atr-called-state strong {
        display: block;
        font-size: 10px;
        font-weight: 900;
    }

    .atr-called-state small {
        display: block;
        margin-top: 3px;
        color: #65746b;
        font-size: 9px;
        font-weight: 700;
    }

    .atr-proof-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        margin-top: 5px;
        padding: 0;
        border: 0;
        color: #138a4e;
        background: transparent;
        font-size: 9px;
        font-weight: 900;
        text-decoration: underline;
    }

    /* =====================================================
       MODAL DOKUMENTASI PEMANGGILAN
       ===================================================== */

    .atr-call-backdrop {
        position: fixed;
        inset: 0;
        z-index: 1500;
        visibility: hidden;
        opacity: 0;
        background: rgba(15, 23, 42, 0.58);
        backdrop-filter: blur(2px);
        pointer-events: none;
        transition:
            opacity 0.22s ease,
            visibility 0.22s ease;
    }

    .atr-call-backdrop.is-open {
        visibility: visible;
        opacity: 1;
        pointer-events: auto;
    }

    .atr-call-modal {
        position: fixed;
        top: 50%;
        left: 50%;
        z-index: 1501;
        width: min(460px, calc(100vw - 28px));
        max-height: calc(100vh - 34px);
        padding: 0;
        overflow: auto;
        visibility: hidden;
        opacity: 0;
        border: 0;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 24px 70px rgba(15, 23, 42, 0.28);
        pointer-events: none;
        transform: translate(-50%, -47%) scale(0.96);
        transition:
            opacity 0.22s ease,
            transform 0.22s ease,
            visibility 0.22s ease;
    }

    .atr-call-modal.is-open {
        visibility: visible;
        opacity: 1;
        pointer-events: auto;
        transform: translate(-50%, -50%) scale(1);
    }

    .atr-call-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 20px 20px 14px;
        border-bottom: 1px solid #eef1f4;
    }

    .atr-call-modal-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        color: #273246;
        font-size: 18px;
        font-weight: 900;
    }

    .atr-call-modal-title span {
        color: #e62f43;
    }

    .atr-call-close {
        display: grid;
        width: 34px;
        height: 34px;
        place-items: center;
        padding: 0;
        border: 0;
        border-radius: 50%;
        color: #94a3b8;
        background: transparent;
        font-size: 24px;
        line-height: 1;
    }

    .atr-call-close:hover {
        color: #334155;
        background: #f1f5f9;
    }

    .atr-call-modal-body {
        padding: 16px 20px 20px;
    }

    .atr-call-info {
        display: grid;
        gap: 7px;
        margin-bottom: 15px;
        padding: 13px;
        border-radius: 11px;
        background: #f7f7f7;
    }

    .atr-call-info-row {
        display: grid;
        grid-template-columns: 82px minmax(0, 1fr);
        gap: 10px;
        align-items: start;
        font-size: 11px;
    }

    .atr-call-info-row span {
        color: #7b8491;
    }

    .atr-call-info-row strong {
        color: #273246;
        font-weight: 900;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .atr-call-info-row .atr-call-score {
        color: #ef3340;
    }

    .atr-call-upload-label {
        display: block;
        margin-bottom: 8px;
        color: #5b6680;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: 0.2px;
        text-transform: uppercase;
    }

    .atr-call-dropzone {
        display: grid;
        min-height: 132px;
        place-items: center;
        padding: 16px;
        border: 1.5px dashed #cbd5e1;
        border-radius: 11px;
        color: #64748b;
        background: #f8fafc;
        cursor: pointer;
        text-align: center;
        transition:
            border-color 0.18s ease,
            background 0.18s ease;
    }

    .atr-call-dropzone:hover,
    .atr-call-dropzone.is-dragging {
        border-color: #3978f6;
        background: #f3f7ff;
    }

    .atr-call-dropzone.has-file {
        border-color: #20b26b;
        background: #effcf5;
    }

    .atr-call-upload-icon {
        display: block;
        margin-bottom: 7px;
        font-size: 30px;
    }

    .atr-call-dropzone strong {
        display: block;
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
    }

    .atr-call-dropzone small {
        display: block;
        margin-top: 5px;
        color: #94a3b8;
        font-size: 9px;
    }

    .atr-call-file-name {
        display: none;
        max-width: 100%;
        margin-top: 7px;
        color: #138a4e;
        font-size: 10px;
        font-weight: 900;
        overflow-wrap: anywhere;
    }

    .atr-call-dropzone.has-file .atr-call-file-name {
        display: block;
    }

    .atr-call-error {
        display: none;
        margin: 8px 0 0;
        color: #dc2626;
        font-size: 10px;
        font-weight: 800;
    }

    .atr-call-error.is-visible {
        display: block;
    }

    .atr-call-actions {
        display: grid;
        gap: 7px;
        margin-top: 13px;
    }

    .atr-call-save,
    .atr-call-cancel {
        min-height: 38px;
        border-radius: 7px;
        font-size: 12px;
        font-weight: 900;
    }

    .atr-call-save {
        border: 0;
        color: #ffffff;
        background: #e62f43;
    }

    .atr-call-save:disabled {
        cursor: wait;
        opacity: 0.65;
    }

    .atr-call-cancel {
        border: 1px solid #8d96a3;
        color: #6b7280;
        background: #ffffff;
    }

    .atr-call-timestamp-note {
        margin: 12px 0 0;
        color: #8a939f;
        font-size: 9px;
        font-weight: 700;
        text-align: center;
    }

    @media (max-width: 560px) {
        .atr-call-modal-header {
            padding-inline: 15px;
        }

        .atr-call-modal-body {
            padding-inline: 15px;
        }
    }

    /* =====================================================
       LOADING SKELETON
       ===================================================== */

    .db-loading-layer {
        position: absolute;
        inset: 0;
        z-index: 30;
        display: none;
        padding: 14px;
        background: rgba(243, 245, 247, 0.94);
    }

    .db-loading-layer.is-visible {
        display: block;
    }

    .db-skeleton-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 13px;
    }

    .db-skeleton {
        position: relative;
        min-height: 95px;
        overflow: hidden;
        border-radius: 12px;
        background: #e3e7eb;
    }

    .db-skeleton.large {
        min-height: 270px;
        grid-column: span 2;
        margin-top: 13px;
    }

    .db-skeleton.medium {
        min-height: 270px;
        margin-top: 13px;
    }

    .db-skeleton.table {
        min-height: 220px;
        grid-column: 1 / -1;
        margin-top: 13px;
    }

    .db-skeleton::after {
        position: absolute;
        inset: 0;
        background:
            linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.7),
                transparent
            );
        content: "";
        transform: translateX(-100%);
        animation: db-shimmer 1.2s infinite;
    }

    @keyframes db-shimmer {
        100% {
            transform: translateX(100%);
        }
    }

    /* =====================================================
       DETAIL DRAWER
       ===================================================== */

    .db-drawer-backdrop {
        position: fixed;
        inset: 0;
        z-index: 1200;
        visibility: hidden;
        opacity: 0;
        background: rgba(15, 23, 42, 0.45);
        pointer-events: none;
        transition:
            opacity 0.22s ease,
            visibility 0.22s ease;
    }

    .db-drawer-backdrop.is-open {
        visibility: visible;
        opacity: 1;
        pointer-events: auto;
    }

    .db-detail-drawer {
        position: fixed;
        top: 0;
        right: 0;
        z-index: 1201;
        width: min(420px, 92vw);
        height: 100vh;
        padding: 22px;
        overflow-y: auto;
        background: #ffffff;
        box-shadow: -18px 0 44px rgba(15, 23, 42, 0.2);
        transform: translateX(100%);
        transition: transform 0.28s ease;
    }

    .db-detail-drawer.is-open {
        transform: translateX(0);
    }

    .db-drawer-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 18px;
        padding-bottom: 14px;
        border-bottom: 1px solid var(--db-border);
    }

    .db-drawer-header h2 {
        margin: 0;
        font-size: 18px;
    }

    .db-drawer-close {
        display: grid;
        width: 36px;
        height: 36px;
        place-items: center;
        padding: 0;
        border: 0;
        border-radius: 50%;
        color: #ffffff;
        background: var(--db-red);
        font-size: 23px;
    }

    .db-detail-list {
        display: grid;
        gap: 11px;
    }

    .db-detail-row {
        display: grid;
        grid-template-columns: 125px minmax(0, 1fr);
        gap: 12px;
        padding: 12px;
        border-radius: 10px;
        background: var(--db-surface-soft);
    }

    .db-detail-row span {
        color: var(--db-muted);
        font-size: 12px;
    }

    .db-detail-row strong {
        font-size: 13px;
        text-align: right;
        overflow-wrap: anywhere;
    }

    /* =====================================================
       FOOTER
       ===================================================== */

    .db-footer {
        display: flex;
        grid-column: 2;
        grid-row: 3;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        background: #383838;
        font-size: 9px;
        font-weight: 800;
    }

    /* =====================================================
       SIDEBAR COLLAPSED
       ===================================================== */

    .db-page.sidebar-collapsed {
        grid-template-columns:
            var(--db-sidebar-collapsed)
            minmax(0, 1fr);
    }

    .db-page.sidebar-collapsed .db-sidebar-head {
        grid-template-columns: var(--db-sidebar-collapsed);
    }

    .db-page.sidebar-collapsed .db-sidebar-logo,
    .db-page.sidebar-collapsed .db-menu-label,
    .db-page.sidebar-collapsed .db-menu-arrow,
    .db-page.sidebar-collapsed .db-submenu,
    .db-page.sidebar-collapsed .db-sidebar-bottom {
        display: none;
    }

    .db-page.sidebar-collapsed .db-sidebar-toggle {
        width: var(--db-sidebar-collapsed);
        border-left: 0;
    }

    .db-page.sidebar-collapsed .db-menu-link,
    .db-page.sidebar-collapsed .db-menu-toggle {
        justify-content: center;
        padding-inline: 0;
    }

    /* =====================================================
       LAPTOP RESPONSIVE
       ===================================================== */

    @media (max-width: 1450px) {
        :root {
            --db-sidebar-width: 205px;
        }

        .db-content {
            padding: 11px;
        }

        .db-page-title {
            margin-bottom: 10px;
        }

        .db-filter-panel,
        .db-kpi-grid,
        .db-chart-grid {
            margin-bottom: 10px;
        }

        .db-chart-body {
            min-height: 215px;
            padding: 14px;
        }

        .db-bars {
            height: 178px;
        }

        .db-table-wrap {
            max-height: 215px;
        }

        .atr-grid {
            grid-template-columns: repeat(3, minmax(190px, 1fr));
            gap: 9px;
            padding: 11px;
        }
    }

    @media (max-width: 1180px) {
        .db-filter-panel {
            grid-template-columns: 1fr 1fr;
        }

        .db-filter-actions {
            grid-column: 1 / -1;
        }

        .db-chart-grid {
            grid-template-columns: 1fr;
        }

        .db-kpi-value {
            font-size: 24px;
        }

        .atr-detail-topbar {
            grid-template-columns: 1fr 1fr;
        }

        .atr-detail-topbar .db-button {
            grid-column: 1 / -1;
        }

        .atr-grid {
            grid-template-columns: repeat(2, minmax(220px, 1fr));
        }
    }

    @media (max-width: 900px) {
        .db-page {
            grid-template-columns:
                var(--db-sidebar-collapsed)
                minmax(0, 1fr);
        }

        .db-sidebar-head {
            grid-template-columns: var(--db-sidebar-collapsed);
        }

        .db-sidebar-logo,
        .db-menu-label,
        .db-menu-arrow,
        .db-submenu,
        .db-sidebar-bottom {
            display: none;
        }

        .db-sidebar-toggle {
            width: var(--db-sidebar-collapsed);
            border-left: 0;
        }

        .db-menu-link,
        .db-menu-toggle {
            justify-content: center;
            padding-inline: 0;
        }

        .db-kpi-grid {
            grid-template-columns: 1fr;
        }

        .atr-toolbar {
            grid-template-columns: 1fr;
        }
    }

    /* Shortcut lintas modul mengikuti ukuran tombol header Database. */
    .db-header-actions .module-shortcut {
        display: flex;
        flex: 0 0 auto;
        align-items: center;
    }

    .db-header-actions .module-shortcut-trigger {
        width: 44px;
        height: 44px;
        flex-basis: 44px;
    }

</style>
@endpush

@section('content')
@php
    /*
    |--------------------------------------------------------------------------
    | DATA FALLBACK
    |--------------------------------------------------------------------------
    | Nanti controller/API cukup mengirim variabel:
    | $summaryData
    | $messDistribution
    | $employeeRows
    | $atrRanking
    | $atrEmployees
    */

    $summaryData = $summaryData ?? [
        'total' => 870,
        'mess' => 360,
        'non_mess' => 510,
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
        ['label' => 'MESS', 'value' => 28],
    ]);

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
    ]);

    $atrRanking = collect($atrRanking ?? [
        ['nama' => 'Andri Oktariyanta', 'jabatan' => 'Operator HD 785', 's' => 3, 'i' => 2, 'a' => 0, 'atr' => '88.2%'],
        ['nama' => 'Ridwan Husuma', 'jabatan' => 'Operator GD 825', 's' => 4, 'i' => 0, 'a' => 0, 'atr' => '87.5%'],
        ['nama' => 'Wida Ardiyanto', 'jabatan' => 'Operator PC 500', 's' => 0, 'i' => 3, 'a' => 1, 'atr' => '91.3%'],
        ['nama' => 'Tri Prasetyo', 'jabatan' => 'Operator HD 785', 's' => 1, 'i' => 3, 'a' => 0, 'atr' => '93.6%'],
        ['nama' => 'Deko Prayama', 'jabatan' => 'Operator HD 785', 's' => 2, 'i' => 2, 'a' => 0, 'atr' => '90.1%'],
        ['nama' => 'Firman Akbar', 'jabatan' => 'Operator HD 785', 's' => 1, 'i' => 3, 'a' => 0, 'atr' => '92.5%'],
        ['nama' => 'Jono', 'jabatan' => 'Operator GD 825', 's' => 1, 'i' => 2, 'a' => 0, 'atr' => '94.0%'],
        ['nama' => 'Abdulla Hozoini Hart', 'jabatan' => 'Operator DT 135', 's' => 0, 'i' => 4, 'a' => 0, 'atr' => '89.7%'],
        ['nama' => 'Joko Wib', 'jabatan' => 'Operator HD 825', 's' => 0, 'i' => 3, 'a' => 0, 'atr' => '91.8%'],
        ['nama' => 'Ramita Adriana', 'jabatan' => 'Operator HD 785', 's' => 2, 'i' => 2, 'a' => 0, 'atr' => '90.4%'],
    ]);

    $atrStatistics = $atrStatistics ?? [
        'month_label' => 'Juni 2026',
        'threshold' => '98.5%',
        'above_threshold' => 643,
        'below_threshold' => 210,
        'no_data' => 16,
        'total_sick' => 300,
        'total_permission' => 108,
        'total_alpha' => 2,
    ];

    $atrProgress = $atrProgress ?? [
        'belum' => 169,
        'sudah' => 41,
        'total' => 210,
    ];

    $atrProgressTotal = max(
        (int) data_get($atrProgress, 'total', 0),
        1
    );

    $atrProgressPercentage = min(
        100,
        max(
            0,
            (
                (int) data_get($atrProgress, 'sudah', 0) /
                $atrProgressTotal
            ) * 100
        )
    );

    $atrEmployees = collect($atrEmployees ?? [
        [
            'id' => 1,
            'nrp' => '1707255',
            'nama' => 'Nanang Sahrani',
            'jabatan' => 'Operator',
            'bapak_asuh' => 'Tony Yunus Yudhanto & Muhammad Al Fajri',
            'period' => 'Juni 2026',
            'score' => '96.4%',
            's' => 1,
            'i' => 0,
            'a' => 0,
            'called' => false,
            'called_at' => null,
            'proof_url' => null,
        ],
        [
            'id' => 2,
            'nrp' => '1683312',
            'nama' => 'Joni',
            'jabatan' => 'Operator PC 500',
            'bapak_asuh' => 'Irfan Wibawa',
            'period' => 'Juni 2026',
            'score' => '85.7%',
            's' => 3,
            'i' => 0,
            'a' => 0,
            'called' => false,
            'called_at' => null,
            'proof_url' => null,
        ],
        [
            'id' => 3,
            'nrp' => '1707256',
            'nama' => 'Dedza Audio Bayu Pradama',
            'jabatan' => 'Operator HD 785',
            'bapak_asuh' => 'Yogi Suganda Andita Wibawa',
            'period' => 'Juni 2026',
            'score' => '90.5%',
            's' => 0,
            'i' => 0,
            'a' => 0,
            'called' => true,
            'called_at' => '06 Jul 2026',
            'proof_url' => null,
        ],
        ['id' => 4, 'nrp' => '16833575', 'nama' => 'Doni Ichwan Hermawan', 'jabatan' => 'Operator HD 785', 'bapak_asuh' => 'Irfan Wibawa', 'period' => 'Juni 2026', 'score' => '95.7%', 's' => 1, 'i' => 0, 'a' => 0, 'called' => false],
        ['id' => 5, 'nrp' => '16884811', 'nama' => 'Alfin Fasbilillah', 'jabatan' => 'Operator GD 825', 'bapak_asuh' => 'Irfan Wibawa', 'period' => 'Juni 2026', 'score' => '90.9%', 's' => 1, 'i' => 0, 'a' => 0, 'called' => false],
        ['id' => 6, 'nrp' => '16884835', 'nama' => 'Rama Maulana Santoso', 'jabatan' => 'Operator PC 500', 'bapak_asuh' => 'Irfan Wibawa', 'period' => 'Juni 2026', 'score' => '95.7%', 's' => 1, 'i' => 0, 'a' => 0, 'called' => false],
        ['id' => 7, 'nrp' => '16884836', 'nama' => 'Dhyasadi Satya', 'jabatan' => 'Operator DT 135', 'bapak_asuh' => 'Supervisor', 'period' => 'Juni 2026', 'score' => '91.3%', 's' => 1, 'i' => 0, 'a' => 0, 'called' => false],
        ['id' => 8, 'nrp' => '16884837', 'nama' => 'Ardiansyah Tri Pamungkas', 'jabatan' => 'Operator HD 785', 'bapak_asuh' => 'Supervisor', 'period' => 'Juni 2026', 'score' => '96.4%', 's' => 1, 'i' => 0, 'a' => 0, 'called' => false],
        ['id' => 9, 'nrp' => '16884838', 'nama' => 'Rohmandiga Wenisha', 'jabatan' => 'Operator GD 825', 'bapak_asuh' => 'Supervisor', 'period' => 'Juni 2026', 'score' => '92.3%', 's' => 1, 'i' => 0, 'a' => 0, 'called' => false],
        ['id' => 10, 'nrp' => '16884839', 'nama' => 'Abdulla Hozoini Hart', 'jabatan' => 'Operator HD 785', 'bapak_asuh' => 'Supervisor', 'period' => 'Juni 2026', 'score' => '85.7%', 's' => 1, 'i' => 0, 'a' => 0, 'called' => false],
        ['id' => 11, 'nrp' => '16884840', 'nama' => 'Ratna Nona Furwemi', 'jabatan' => 'Operator HD 785', 'bapak_asuh' => 'Supervisor', 'period' => 'Juni 2026', 'score' => '91.3%', 's' => 1, 'i' => 0, 'a' => 0, 'called' => false],
        ['id' => 12, 'nrp' => '16884841', 'nama' => 'Rima Mahatindra Putra', 'jabatan' => 'Operator PC 500', 'bapak_asuh' => 'Supervisor', 'period' => 'Juni 2026', 'score' => '96.3%', 's' => 1, 'i' => 0, 'a' => 0, 'called' => false],
    ]);

    $totalEmployees = max((int) data_get($summaryData, 'total', 0), 1);
    $messEmployees = (int) data_get($summaryData, 'mess', 0);
    $nonMessEmployees = (int) data_get($summaryData, 'non_mess', 0);
    $messAngle = ($messEmployees / $totalEmployees) * 360;
    $maxMessValue = max((int) $messDistribution->max('value'), 1);
@endphp

<div class="db-page" id="databasePage">
    {{-- SIDEBAR --}}
    <aside class="db-sidebar">
        <div class="db-sidebar-head">
            <div class="db-sidebar-logo">
                <img
                    src="{{ asset('assets/images/DATABASE.png') }}"
                    alt="Database"
                >
            </div>

            <button
                type="button"
                class="db-sidebar-toggle"
                id="databaseSidebarToggle"
                aria-label="Buka atau tutup sidebar"
            >
                ☰
            </button>
        </div>

        <nav class="db-navigation" aria-label="Menu Database">
            <button
                type="button"
                class="db-menu-link active"
                data-db-view="database-summary"
            >
                <span class="db-menu-icon">▦</span>
                <span class="db-menu-label">Dashboard</span>
            </button>

            <div class="db-menu-group is-open">
                <button
                    type="button"
                    class="db-menu-toggle"
                    aria-expanded="true"
                >
                    <span class="db-menu-icon">
                        <img
                            src="{{ asset('assets/images/database-submenu.png') }}"
                            alt=""
                        >
                    </span>

                    <span class="db-menu-label">Database</span>
                    <span class="db-menu-arrow">›</span>
                </button>

                <div class="db-submenu">
                    <div class="db-submenu-inner">
                        <button
                            type="button"
                            class="db-submenu-button active"
                            data-db-view="database-summary"
                        >
                            Ringkasan &amp; Pencarian
                        </button>
                    </div>
                </div>
            </div>

            <div class="db-menu-group is-open">
                <button
                    type="button"
                    class="db-menu-toggle"
                    aria-expanded="true"
                >
                    <span class="db-menu-icon">
                        <img
                            src="{{ asset('assets/images/ATR-submenu.png') }}"
                            alt=""
                        >
                    </span>

                    <span class="db-menu-label">ATR Karyawan</span>
                    <span class="db-menu-arrow">›</span>
                </button>

                <div class="db-submenu">
                    <div class="db-submenu-inner">
                        <button
                            type="button"
                            class="db-submenu-button"
                            data-db-view="atr-summary"
                        >
                            Ringkasan
                        </button>

                        <button
                            type="button"
                            class="db-submenu-button"
                            data-db-view="atr-detail"
                        >
                            Detail dan Upload
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <div class="db-sidebar-bottom">
            <a href="#" class="db-bottom-link">
                <span>⚙</span>
                <span>Pengaturan</span>
            </a>

            <a
                href="https://mail.google.com/mail/?view=cm&fs=1&to={{ urlencode(config('access.contact_email', 'mpe.ppaba@ppa.co.id')) }}&su=SYNRGYPRO%20Support"
                target="_blank"
                rel="noopener noreferrer"
                class="db-bottom-link help"
            >
                <span>?</span>
                <span>Bantuan</span>
            </a>
        </div>
    </aside>

    {{-- HEADER --}}
    <header class="db-header">
        <div class="db-header-brand">
            <img
                src="{{ asset('assets/images/synrgypro-logo.png') }}"
                alt="SYNRGYPRO"
            >
        </div>

        <nav class="db-header-actions" aria-label="Shortcut pengguna">
            {{-- Shortcut Manpower, People Development, Database, dan Admin All --}}
            <x-module-shortcut />

            <a
                href="{{ route('dashboard') }}"
                class="db-header-button"
                aria-label="Dashboard"
            >
                <img
                    src="{{ asset('assets/images/LOGO HOME.jpeg') }}"
                    alt=""
                >
            </a>

            <button
                type="button"
                class="db-header-button db-profile-button"
                aria-label="Profil"
            >
                @if (Auth::user()?->avatar)
                    <img
                        src="{{ Auth::user()->avatar }}"
                        alt="Foto profil {{ Auth::user()->name }}"
                        referrerpolicy="no-referrer"
                    >
                @else
                    <img
                        src="{{ asset('assets/images/profile.png') }}"
                        alt="Profil"
                    >
                @endif
            </button>

            <form
                method="POST"
                action="{{ route('logout') }}"
                class="db-logout-form"
            >
                @csrf

                <button
                    type="submit"
                    class="db-header-button db-logout-button"
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

    {{-- CONTENT --}}
    <main class="db-content" style="position: relative;">
        {{-- Loading API --}}
        <div
            class="db-loading-layer"
            id="databaseLoadingLayer"
            aria-hidden="true"
        >
            <div class="db-skeleton-grid">
                <div class="db-skeleton"></div>
                <div class="db-skeleton"></div>
                <div class="db-skeleton"></div>
                <div class="db-skeleton large"></div>
                <div class="db-skeleton medium"></div>
                <div class="db-skeleton table"></div>
            </div>
        </div>

        {{-- VIEW DATABASE --}}
        <section class="db-view active" id="database-summary">
            <div class="db-page-title">
                <div>
                    <h1>Database Karyawan</h1>
                    <p>
                        Ringkasan hunian, distribusi mess, dan data karyawan.
                    </p>
                </div>
            </div>

            <form
                class="db-filter-panel"
                id="databaseFilterForm"
            >
                <div class="db-field">
                    <label for="employeeSearch">Nama / NRP</label>
                    <input
                        type="search"
                        id="employeeSearch"
                        class="db-input"
                        placeholder="Cari nama atau NRP..."
                        autocomplete="off"
                    >
                </div>

                <div class="db-field">
                    <label for="residenceFilter">Tempat Tinggal</label>
                    <select
                        id="residenceFilter"
                        class="db-select"
                    >
                        <option value="">Semua Status</option>
                        <option value="mess">Mess</option>
                        <option value="non-mess">Non Mess</option>
                    </select>
                </div>

                <div class="db-filter-actions">
                    <button type="submit" class="db-button">
                        Cari
                    </button>

                    <button
                        type="button"
                        class="db-button secondary"
                        id="resetFilter"
                    >
                        Reset
                    </button>

                    <button
                        type="button"
                        class="db-button dark"
                        id="sourceDataButton"
                    >
                        Source Data
                    </button>
                </div>
            </form>

            <div class="db-kpi-grid">
                <button
                    type="button"
                    class="db-kpi-card active"
                    data-kpi-filter=""
                >
                    <span class="db-kpi-icon">👥</span>
                    <span>
                        <small>Total Karyawan</small>
                        <span class="db-kpi-value">
                            {{ number_format($totalEmployees) }}
                        </span>
                    </span>
                </button>

                <button
                    type="button"
                    class="db-kpi-card"
                    data-kpi-filter="mess"
                >
                    <span class="db-kpi-icon">🏠</span>
                    <span>
                        <small>Tinggal di Mess</small>
                        <span class="db-kpi-value">
                            {{ number_format($messEmployees) }}
                        </span>
                    </span>
                </button>

                <button
                    type="button"
                    class="db-kpi-card"
                    data-kpi-filter="non-mess"
                >
                    <span class="db-kpi-icon">🚶</span>
                    <span>
                        <small>Tinggal Non Mess</small>
                        <span class="db-kpi-value">
                            {{ number_format($nonMessEmployees) }}
                        </span>
                    </span>
                </button>
            </div>

            <div class="db-chart-grid">
                <article class="db-chart-card">
                    <div class="db-card-header">
                        <div>
                            <h2>Distribusi Penghuni Mess</h2>
                            <small>Jumlah penghuni berdasarkan gedung</small>
                        </div>
                    </div>

                    <div class="db-chart-body">
                        <div class="db-bars">
                            @foreach ($messDistribution as $item)
                                @php
                                    $itemValue = (int) data_get($item, 'value', 0);
                                    $itemLabel = data_get($item, 'label', '-');

                                    $heightPercentage = max(
                                        8,
                                        ($itemValue / $maxMessValue) * 100
                                    );
                                @endphp

                                <div
                                    class="db-bar"
                                    style="height: {{ $heightPercentage }}%;"
                                    title="{{ $itemLabel }}: {{ $itemValue }}"
                                >
                                    <span class="db-bar-value">
                                        {{ $itemValue }}
                                    </span>

                                    <span class="db-bar-label">
                                        {{ $itemLabel }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </article>

                <article class="db-chart-card">
                    <div class="db-card-header">
                        <div>
                            <h2>Status Tempat Tinggal</h2>
                            <small>Mess dibandingkan Non Mess</small>
                        </div>
                    </div>

                    <div class="db-chart-body db-donut-layout">
                        <div
                            class="db-donut"
                            style="--mess-angle: {{ $messAngle }}deg;"
                        >
                            <div class="db-donut-center">
                                <strong>
                                    {{ number_format($totalEmployees) }}
                                </strong>
                                <span>Total</span>
                            </div>
                        </div>

                        <div class="db-legend">
                            <div class="db-legend-item">
                                <span class="db-legend-dot"></span>
                                <span>Tinggal di Mess</span>
                                <strong>
                                    {{ number_format($messEmployees) }}
                                </strong>
                            </div>

                            <div class="db-legend-item">
                                <span class="db-legend-dot"></span>
                                <span>Tinggal Non Mess</span>
                                <strong>
                                    {{ number_format($nonMessEmployees) }}
                                </strong>
                            </div>
                        </div>
                    </div>
                </article>
            </div>

            <article class="db-table-card">
                <div class="db-card-header">
                    <div>
                        <h2>Data Ringkasan Karyawan</h2>
                        <small id="tableResultText">
                            Menampilkan {{ $employeeRows->count() }} data
                        </small>
                    </div>
                </div>

                <div class="db-table-wrap">
                    <table class="db-table" id="employeeTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NRP</th>
                                <th>Nama Karyawan</th>
                                <th>Status Tinggal</th>
                                <th>Gedung/Kamar</th>
                                <th>No. HP / Email</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($employeeRows as $index => $employee)
                                @php
                                    $employeeStatus =
                                        (string) data_get(
                                            $employee,
                                            'status_tinggal',
                                            ''
                                        );

                                    $statusSlug = strtolower(
                                        str_replace(
                                            ' ',
                                            '-',
                                            trim($employeeStatus)
                                        )
                                    );

                                    $employeeName =
                                        (string) data_get(
                                            $employee,
                                            'nama',
                                            '-'
                                        );

                                    $employeeNrp =
                                        (string) data_get(
                                            $employee,
                                            'nrp',
                                            '-'
                                        );

                                    $employeeRoom =
                                        (string) data_get(
                                            $employee,
                                            'gedung_kamar',
                                            '-'
                                        );

                                    $employeeContact =
                                        (string) data_get(
                                            $employee,
                                            'kontak',
                                            '-'
                                        );
                                @endphp

                                <tr
                                    data-employee-row
                                    data-name="{{ strtolower($employeeName) }}"
                                    data-nrp="{{ strtolower($employeeNrp) }}"
                                    data-status="{{ $statusSlug }}"
                                >
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $employeeNrp }}</td>
                                    <td>{{ $employeeName }}</td>
                                    <td>
                                        <span
                                            class="db-badge {{ $statusSlug === 'non-mess' ? 'non-mess' : '' }}"
                                        >
                                            {{ $employeeStatus ?: '-' }}
                                        </span>
                                    </td>
                                    <td>{{ $employeeRoom }}</td>
                                    <td>{{ $employeeContact }}</td>
                                    <td>
                                        <button
                                            type="button"
                                            class="db-detail-button"
                                            data-detail-button
                                            data-nrp="{{ $employeeNrp }}"
                                            data-name="{{ $employeeName }}"
                                            data-status="{{ $employeeStatus }}"
                                            data-room="{{ $employeeRoom }}"
                                            data-contact="{{ $employeeContact }}"
                                        >
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="7"
                                        class="db-empty-state"
                                    >
                                        Data belum tersedia.
                                    </td>
                                </tr>
                            @endforelse

                            <tr id="emptyFilterRow" hidden>
                                <td
                                    colspan="7"
                                    class="db-empty-state"
                                >
                                    Tidak ada data yang sesuai dengan filter.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

        {{-- VIEW ATR RINGKASAN --}}
        <section class="db-view" id="atr-summary">
            <div class="db-page-title">
                <div>
                    <h1>Ringkasan ATR Karyawan</h1>
                    <p>
                        Monitoring progres pemanggilan dan absensi karyawan.
                    </p>
                </div>
            </div>

            <div class="atr-toolbar atr-panel">
                <div class="db-field">
                    <label for="atrMonth">Bulan</label>
                    <select id="atrMonth" class="db-select">
                        <option>Juni 2026</option>
                        <option>Mei 2026</option>
                        <option>April 2026</option>
                    </select>
                </div>

                <div class="db-field">
                    <label for="atrPosition">Posisi / Jabatan</label>
                    <select id="atrPosition" class="db-select">
                        <option>Semua Posisi</option>
                    </select>
                </div>

                <div class="db-field">
                    <label for="atrSearch">Cari Karyawan</label>
                    <input
                        type="search"
                        id="atrSearch"
                        class="db-input"
                        placeholder="Cari NRP atau nama..."
                    >
                </div>
            </div>

            {{-- Statistik ATR dinamis --}}
            <section class="atr-statistics-panel atr-panel">
                <div class="atr-statistics-head">
                    <h2 class="atr-statistics-title">
                        Statistik ATR —
                        <span id="atrStatisticsMonth">
                            {{ data_get($atrStatistics, 'month_label', 'Juni 2026') }}
                        </span>
                    </h2>

                    <span
                        class="atr-statistics-badge"
                        id="atrStatisticsBadge"
                    >
                        {{ number_format((int) data_get($atrStatistics, 'below_threshold', 0)) }}
                        di bawah
                        {{ data_get($atrStatistics, 'threshold', '98.5%') }}
                    </span>
                </div>

                <div class="atr-statistics-grid">
                    <article class="atr-stat-card is-safe">
                        <div>
                            <strong id="atrStatAbove">
                                {{ number_format((int) data_get($atrStatistics, 'above_threshold', 0)) }}
                            </strong>
                            <small>
                                Aman ≥{{ data_get($atrStatistics, 'threshold', '98.5%') }}
                            </small>
                        </div>
                    </article>

                    <article class="atr-stat-card is-below">
                        <div>
                            <strong id="atrStatBelow">
                                {{ number_format((int) data_get($atrStatistics, 'below_threshold', 0)) }}
                            </strong>
                            <small>Di Bawah</small>
                        </div>
                    </article>

                    <article class="atr-stat-card is-no-data">
                        <div>
                            <strong id="atrStatNoData">
                                {{ number_format((int) data_get($atrStatistics, 'no_data', 0)) }}
                            </strong>
                            <small>No Data</small>
                        </div>
                    </article>

                    <article class="atr-stat-card is-sick">
                        <div>
                            <strong id="atrStatSick">
                                {{ number_format((int) data_get($atrStatistics, 'total_sick', 0)) }}
                            </strong>
                            <small>Total Sakit</small>
                        </div>
                    </article>

                    <article class="atr-stat-card is-permission">
                        <div>
                            <strong id="atrStatPermission">
                                {{ number_format((int) data_get($atrStatistics, 'total_permission', 0)) }}
                            </strong>
                            <small>Total Izin</small>
                        </div>
                    </article>

                    <article class="atr-stat-card is-alpha">
                        <div>
                            <strong id="atrStatAlpha">
                                {{ number_format((int) data_get($atrStatistics, 'total_alpha', 0)) }}
                            </strong>
                            <small>Total Alpa</small>
                        </div>
                    </article>
                </div>
            </section>

            <div class="atr-progress atr-panel">
                <div class="atr-progress-title">
                    ▲ Progress Pemanggilan
                </div>

                <div class="atr-progress-values">
                    <div>
                        <strong id="atrProgressPending">
                            {{ number_format((int) data_get($atrProgress, 'belum', 0)) }}
                        </strong>
                        <small>Belum</small>
                    </div>

                    <div>
                        <strong id="atrProgressDone">
                            {{ number_format((int) data_get($atrProgress, 'sudah', 0)) }}
                        </strong>
                        <small>Sudah</small>
                    </div>

                    <div>
                        <strong id="atrProgressTotal">
                            {{ number_format((int) data_get($atrProgress, 'total', 0)) }}
                        </strong>
                        <small>Total Perlu</small>
                    </div>
                </div>

                <div class="atr-track">
                    <div
                        class="atr-bar"
                        id="atrProgressBar"
                        style="width: {{ $atrProgressPercentage }}%;"
                    ></div>
                </div>
            </div>

            <article class="atr-panel">
                <div class="db-card-header">
                    <div>
                        <h2>Top 10 Absensi &amp; Alfa Terbanyak</h2>
                        <small>Data karyawan berdasarkan periode terpilih</small>
                    </div>
                </div>

                <div class="atr-ranking-wrap">
                    <table class="atr-ranking-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>Jabatan</th>
                                <th>S</th>
                                <th>I</th>
                                <th>A</th>
                                <th>Total</th>
                                <th>ATR%</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($atrRanking as $index => $employee)
                                @php
                                    $sick = (int) data_get($employee, 's', 0);
                                    $permission = (int) data_get($employee, 'i', 0);
                                    $alpha = (int) data_get($employee, 'a', 0);
                                    $employeeName =
                                        (string) data_get(
                                            $employee,
                                            'nama',
                                            '-'
                                        );
                                @endphp

                                <tr>
                                    <td>{{ $index + 1 }}</td>

                                    <td>
                                        <div class="atr-name-cell">
                                            <span class="atr-avatar">
                                                {{ strtoupper(substr($employeeName, 0, 1)) }}
                                            </span>

                                            <span>{{ $employeeName }}</span>
                                        </div>
                                    </td>

                                    <td>
                                        {{ data_get($employee, 'jabatan', '-') }}
                                    </td>

                                    <td>{{ $sick }}</td>
                                    <td>{{ $permission }}</td>
                                    <td>{{ $alpha }}</td>
                                    <td>{{ $sick + $permission + $alpha }}</td>

                                    <td>
                                        <span class="atr-score-badge">
                                            {{ data_get($employee, 'atr', '-') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

        {{-- VIEW ATR DETAIL --}}
        <section class="db-view" id="atr-detail">
            <div class="db-page-title">
                <div>
                    <h1>Detail dan Upload ATR</h1>
                    <p>
                        Daftar karyawan dengan nilai ATR di bawah standar.
                    </p>
                </div>
            </div>

            <div class="atr-detail-topbar atr-panel">
                <div class="db-field">
                    <label for="atrDetailMonth">Bulan</label>
                    <select id="atrDetailMonth" class="db-select">
                        <option>Juni 2026</option>
                    </select>
                </div>

                <div class="db-field">
                    <label for="atrDetailPosition">
                        Posisi / Jabatan
                    </label>

                    <select
                        id="atrDetailPosition"
                        class="db-select"
                    >
                        <option>Semua Posisi</option>
                    </select>
                </div>

                <div class="db-field">
                    <label for="atrDetailSearch">
                        Cari Karyawan
                    </label>

                    <input
                        type="search"
                        id="atrDetailSearch"
                        class="db-input"
                        placeholder="Cari NRP atau nama..."
                    >
                </div>

                <button
                    type="button"
                    class="db-button danger"
                >
                    Upload Data
                </button>
            </div>

            <div class="atr-progress atr-panel">
                <div class="atr-progress-title">
                    ▲ Progress Pemanggilan
                </div>

                <div class="atr-progress-values">
                    <div>
                        <strong id="atrDetailProgressPending">
                            {{ number_format((int) data_get($atrProgress, 'belum', 0)) }}
                        </strong>
                        <small>Belum</small>
                    </div>

                    <div>
                        <strong id="atrDetailProgressDone">
                            {{ number_format((int) data_get($atrProgress, 'sudah', 0)) }}
                        </strong>
                        <small>Sudah</small>
                    </div>

                    <div>
                        <strong id="atrDetailProgressTotal">
                            {{ number_format((int) data_get($atrProgress, 'total', 0)) }}
                        </strong>
                        <small>Total Perlu</small>
                    </div>
                </div>

                <div class="atr-track">
                    <div
                        class="atr-bar"
                        id="atrDetailProgressBar"
                        style="width: {{ $atrProgressPercentage }}%;"
                    ></div>
                </div>
            </div>

            <article class="atr-panel">
                <div class="db-card-header">
                    <div>
                        <h2>Karyawan ATR di Bawah 98.5%</h2>
                        <small>
                            Gunakan filter untuk mempersempit daftar.
                        </small>
                    </div>
                </div>

                <div class="atr-grid">
                    @foreach ($atrEmployees as $employee)
                        @php
                            $employeeName =
                                (string) data_get($employee, 'nama', '-');

                            $employeeId =
                                (string) data_get(
                                    $employee,
                                    'id',
                                    data_get($employee, 'nrp', md5($employeeName))
                                );

                            $employeeNrp =
                                (string) data_get($employee, 'nrp', '-');

                            $employeeRole =
                                (string) data_get($employee, 'jabatan', '-');

                            $employeeMentor =
                                (string) data_get(
                                    $employee,
                                    'bapak_asuh',
                                    'Supervisor'
                                );

                            $employeePeriod =
                                (string) data_get(
                                    $employee,
                                    'period',
                                    'Juni 2026'
                                );

                            $employeeScore =
                                (string) data_get($employee, 'score', '-');

                            $employeeSick =
                                (int) data_get($employee, 's', 1);

                            $employeePermission =
                                (int) data_get($employee, 'i', 0);

                            $employeeAlpha =
                                (int) data_get($employee, 'a', 0);

                            $called =
                                (bool) data_get($employee, 'called', false);

                            $calledAt =
                                (string) data_get(
                                    $employee,
                                    'called_at',
                                    ''
                                );

                            $proofUrl =
                                (string) data_get(
                                    $employee,
                                    'proof_url',
                                    ''
                                );
                        @endphp

                        <article
                            class="atr-card"
                            data-atr-employee-card
                            data-employee-id="{{ $employeeId }}"
                            data-nrp="{{ $employeeNrp }}"
                            data-name="{{ $employeeName }}"
                            data-role="{{ $employeeRole }}"
                            data-mentor="{{ $employeeMentor }}"
                            data-period="{{ $employeePeriod }}"
                            data-score="{{ $employeeScore }}"
                            data-sick="{{ $employeeSick }}"
                            data-permission="{{ $employeePermission }}"
                            data-alpha="{{ $employeeAlpha }}"
                            data-called="{{ $called ? '1' : '0' }}"
                            data-called-at="{{ $calledAt }}"
                            data-proof-url="{{ $proofUrl }}"
                        >
                            <div class="atr-card-body">
                                <div class="atr-card-head">
                                    <div class="atr-photo">
                                        {{ strtoupper(substr($employeeName, 0, 1)) }}
                                    </div>

                                    <div class="atr-card-name">
                                        <strong>{{ $employeeName }}</strong>

                                        <small>
                                            {{ $employeeRole }}<br>
                                            NRP: {{ $employeeNrp }}
                                        </small>
                                    </div>

                                    <div class="atr-score">
                                        {{ $employeeScore }}
                                    </div>
                                </div>

                                <div class="atr-meta">
                                    <div class="atr-meta-row">
                                        <span>Sakit / Izin / Alfa</span>
                                        <strong>
                                            {{ $employeeSick }} /
                                            {{ $employeePermission }} /
                                            {{ $employeeAlpha }}
                                        </strong>
                                    </div>

                                    <div class="atr-meta-row">
                                        <span>Bapak Asuh</span>
                                        <strong>{{ $employeeMentor }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="atr-card-footer" data-atr-card-footer>
                                @if ($called)
                                    <div class="atr-called-state">
                                        <div>
                                            <strong>● Sudah Dipanggil</strong>
                                            <small>
                                                {{ $calledAt ?: 'Tanggal tersimpan di sistem' }}
                                            </small>

                                            <button
                                                type="button"
                                                class="atr-proof-link"
                                                data-view-proof
                                            >
                                                ▣ Lihat Bukti
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    <button
                                        type="button"
                                        class="atr-card-action atr-call-open"
                                        data-call-open
                                    >
                                        🔔 Lakukan Pemanggilan
                                    </button>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </article>
        </section>
    </main>

    <footer class="db-footer">
        @COPYRIGHT SYNRGYPRO {{ date('Y') }}. V1.0
    </footer>
</div>

{{-- DETAIL DRAWER --}}
<div
    class="db-drawer-backdrop"
    id="employeeDrawerBackdrop"
    aria-hidden="true"
></div>

<aside
    class="db-detail-drawer"
    id="employeeDrawer"
    aria-hidden="true"
>
    <div class="db-drawer-header">
        <h2>Detail Karyawan</h2>

        <button
            type="button"
            class="db-drawer-close"
            id="employeeDrawerClose"
            aria-label="Tutup detail"
        >
            &times;
        </button>
    </div>

    <div
        class="db-detail-list"
        id="employeeDetailList"
    ></div>
</aside>

{{-- MODAL DOKUMENTASI PEMANGGILAN --}}
<div
    class="atr-call-backdrop"
    id="atrCallBackdrop"
    aria-hidden="true"
></div>

<section
    class="atr-call-modal"
    id="atrCallModal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="atrCallModalTitle"
    aria-hidden="true"
>
    <div class="atr-call-modal-header">
        <h2
            class="atr-call-modal-title"
            id="atrCallModalTitle"
        >
            <span>🔔</span>
            Dokumentasi Pemanggilan
        </h2>

        <button
            type="button"
            class="atr-call-close"
            id="atrCallClose"
            aria-label="Tutup modal"
        >
            &times;
        </button>
    </div>

    <form
        class="atr-call-modal-body"
        id="atrCallForm"
        enctype="multipart/form-data"
        data-submit-url="{{ $atrDocumentationEndpoint ?? '' }}"
    >
        <input
            type="hidden"
            name="employee_id"
            id="atrCallEmployeeId"
        >

        <div class="atr-call-info">
            <div class="atr-call-info-row">
                <span>Operator</span>
                <strong id="atrCallEmployeeName">-</strong>
            </div>

            <div class="atr-call-info-row">
                <span>Bapak Asuh</span>
                <strong id="atrCallEmployeeMentor">-</strong>
            </div>

            <div class="atr-call-info-row">
                <span>Periode</span>
                <strong id="atrCallEmployeePeriod">-</strong>
            </div>

            <div class="atr-call-info-row">
                <span>ATR · S/I/A</span>
                <strong
                    class="atr-call-score"
                    id="atrCallEmployeeScore"
                >
                    -
                </strong>
            </div>
        </div>

        <label
            class="atr-call-upload-label"
            for="atrCallFile"
        >
            Upload Bukti Pemanggilan
        </label>

        <input
            type="file"
            name="document"
            id="atrCallFile"
            accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf"
            hidden
        >

        <label
            class="atr-call-dropzone"
            id="atrCallDropzone"
            for="atrCallFile"
        >
            <span>
                <span class="atr-call-upload-icon">📷</span>
                <strong>Klik untuk pilih foto / dokumen</strong>
                <small>JPG, PNG, PDF — maksimal 5MB</small>
                <span
                    class="atr-call-file-name"
                    id="atrCallFileName"
                ></span>
            </span>
        </label>

        <p
            class="atr-call-error"
            id="atrCallError"
            role="alert"
        ></p>

        <div class="atr-call-actions">
            <button
                type="submit"
                class="atr-call-save"
                id="atrCallSave"
            >
                ✓ Simpan Dokumentasi
            </button>

            <button
                type="button"
                class="atr-call-cancel"
                id="atrCallCancel"
            >
                Batal
            </button>
        </div>

        <p class="atr-call-timestamp-note">
            ◷ Timestamp upload otomatis dicatat sistem
        </p>
    </form>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const page =
        document.getElementById('databasePage');

    const sidebarToggle =
        document.getElementById('databaseSidebarToggle');

    const menuGroups =
        document.querySelectorAll('.db-menu-group');

    const viewButtons =
        document.querySelectorAll('[data-db-view]');

    const views =
        document.querySelectorAll('.db-view');

    const filterForm =
        document.getElementById('databaseFilterForm');

    const employeeSearch =
        document.getElementById('employeeSearch');

    const residenceFilter =
        document.getElementById('residenceFilter');

    const resetFilter =
        document.getElementById('resetFilter');

    const employeeRows =
        Array.from(
            document.querySelectorAll('[data-employee-row]')
        );

    const emptyFilterRow =
        document.getElementById('emptyFilterRow');

    const tableResultText =
        document.getElementById('tableResultText');

    const kpiButtons =
        document.querySelectorAll('[data-kpi-filter]');

    const detailButtons =
        document.querySelectorAll('[data-detail-button]');

    const drawer =
        document.getElementById('employeeDrawer');

    const drawerBackdrop =
        document.getElementById('employeeDrawerBackdrop');

    const drawerClose =
        document.getElementById('employeeDrawerClose');

    const employeeDetailList =
        document.getElementById('employeeDetailList');

    const loadingLayer =
        document.getElementById('databaseLoadingLayer');

    /*
    |--------------------------------------------------------------------------
    | SIDEBAR
    |--------------------------------------------------------------------------
    */

    if (page && sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            page.classList.toggle('sidebar-collapsed');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | BUKA / TUTUP SUBMENU
    |--------------------------------------------------------------------------
    */

    menuGroups.forEach(function (group) {
        const toggle =
            group.querySelector('.db-menu-toggle');

        if (!toggle) {
            return;
        }

        toggle.addEventListener('click', function () {
            const willOpen =
                !group.classList.contains('is-open');

            group.classList.toggle('is-open', willOpen);

            toggle.setAttribute(
                'aria-expanded',
                String(willOpen)
            );
        });
    });

    /*
    |--------------------------------------------------------------------------
    | PINDAH VIEW TANPA RELOAD
    |--------------------------------------------------------------------------
    */

    viewButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const targetId =
                button.dataset.dbView;

            const targetView =
                document.getElementById(targetId);

            if (!targetView) {
                return;
            }

            views.forEach(function (view) {
                view.classList.toggle(
                    'active',
                    view.id === targetId
                );
            });

            viewButtons.forEach(function (item) {
                item.classList.toggle(
                    'active',
                    item.dataset.dbView === targetId
                );
            });

            const parentGroup =
                button.closest('.db-menu-group');

            if (parentGroup) {
                parentGroup.classList.add('is-open');

                const parentToggle =
                    parentGroup.querySelector(
                        '.db-menu-toggle'
                    );

                if (parentToggle) {
                    parentToggle.setAttribute(
                        'aria-expanded',
                        'true'
                    );
                }
            }
        });
    });

    /*
    |--------------------------------------------------------------------------
    | FILTER DATA TABEL
    |--------------------------------------------------------------------------
    */

    function normalize(value) {
        return String(value || '')
            .trim()
            .toLowerCase();
    }

    function setActiveKpi(status) {
        kpiButtons.forEach(function (button) {
            button.classList.toggle(
                'active',
                normalize(button.dataset.kpiFilter) ===
                    normalize(status)
            );
        });
    }

    function applyEmployeeFilter() {
        const keyword =
            normalize(employeeSearch?.value);

        const status =
            normalize(residenceFilter?.value);

        let visibleCount = 0;

        employeeRows.forEach(function (row) {
            const rowName =
                normalize(row.dataset.name);

            const rowNrp =
                normalize(row.dataset.nrp);

            const rowStatus =
                normalize(row.dataset.status);

            const matchesKeyword =
                !keyword ||
                rowName.includes(keyword) ||
                rowNrp.includes(keyword);

            const matchesStatus =
                !status ||
                rowStatus === status;

            const visible =
                matchesKeyword && matchesStatus;

            row.hidden = !visible;

            if (visible) {
                visibleCount += 1;
            }
        });

        if (emptyFilterRow) {
            emptyFilterRow.hidden =
                visibleCount !== 0 ||
                employeeRows.length === 0;
        }

        if (tableResultText) {
            tableResultText.textContent =
                'Menampilkan ' +
                visibleCount +
                ' dari ' +
                employeeRows.length +
                ' data';
        }

        setActiveKpi(status);
    }

    if (filterForm) {
        filterForm.addEventListener(
            'submit',
            function (event) {
                event.preventDefault();
                applyEmployeeFilter();
            }
        );
    }

    if (resetFilter) {
        resetFilter.addEventListener(
            'click',
            function () {
                if (employeeSearch) {
                    employeeSearch.value = '';
                }

                if (residenceFilter) {
                    residenceFilter.value = '';
                }

                applyEmployeeFilter();
            }
        );
    }

    if (employeeSearch) {
        employeeSearch.addEventListener(
            'input',
            applyEmployeeFilter
        );
    }

    if (residenceFilter) {
        residenceFilter.addEventListener(
            'change',
            applyEmployeeFilter
        );
    }

    kpiButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const status =
                button.dataset.kpiFilter || '';

            if (residenceFilter) {
                residenceFilter.value = status;
            }

            applyEmployeeFilter();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | DETAIL DRAWER
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value) {
        return String(value || '-')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function closeDrawer() {
        if (!drawer || !drawerBackdrop) {
            return;
        }

        drawer.classList.remove('is-open');
        drawerBackdrop.classList.remove('is-open');

        drawer.setAttribute('aria-hidden', 'true');
        drawerBackdrop.setAttribute('aria-hidden', 'true');
    }

    function openDrawer(button) {
        if (
            !drawer ||
            !drawerBackdrop ||
            !employeeDetailList
        ) {
            return;
        }

        const details = [
            ['NRP', button.dataset.nrp],
            ['Nama Karyawan', button.dataset.name],
            ['Status Tinggal', button.dataset.status],
            ['Gedung / Kamar', button.dataset.room],
            ['No. HP / Email', button.dataset.contact]
        ];

        employeeDetailList.innerHTML =
            details
                .map(function (detail) {
                    return (
                        '<div class="db-detail-row">' +
                            '<span>' +
                                escapeHtml(detail[0]) +
                            '</span>' +
                            '<strong>' +
                                escapeHtml(detail[1]) +
                            '</strong>' +
                        '</div>'
                    );
                })
                .join('');

        drawer.classList.add('is-open');
        drawerBackdrop.classList.add('is-open');

        drawer.setAttribute('aria-hidden', 'false');
        drawerBackdrop.setAttribute('aria-hidden', 'false');
    }

    detailButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            openDrawer(button);
        });
    });

    if (drawerClose) {
        drawerClose.addEventListener(
            'click',
            closeDrawer
        );
    }

    if (drawerBackdrop) {
        drawerBackdrop.addEventListener(
            'click',
            closeDrawer
        );
    }

    document.addEventListener(
        'keydown',
        function (event) {
            if (event.key === 'Escape') {
                closeDrawer();
            }
        }
    );

    /*
    |--------------------------------------------------------------------------
    | LOADING UNTUK API
    |--------------------------------------------------------------------------
    | Backend/JavaScript API dapat memanggil:
    | window.setDatabaseLoading(true);
    | window.setDatabaseLoading(false);
    */

    window.setDatabaseLoading =
        function (isLoading) {
            if (!loadingLayer) {
                return;
            }

            loadingLayer.classList.toggle(
                'is-visible',
                Boolean(isLoading)
            );

            loadingLayer.setAttribute(
                'aria-hidden',
                String(!isLoading)
            );
        };

    /*
    |--------------------------------------------------------------------------
    | UPDATE DASHBOARD ATR DARI API
    |--------------------------------------------------------------------------
    | Contoh pemakaian setelah fetch API:
    |
    | window.updateAtrDashboard({
    |     statistics: {
    |         month_label: 'Juli 2026',
    |         threshold: '98.5%',
    |         above_threshold: 650,
    |         below_threshold: 205,
    |         no_data: 15,
    |         total_sick: 280,
    |         total_permission: 95,
    |         total_alpha: 1
    |     },
    |     progress: {
    |         belum: 150,
    |         sudah: 55,
    |         total: 205
    |     }
    | });
    */

    function setTextById(id, value) {
        const element = document.getElementById(id);

        if (!element) {
            return;
        }

        const numericValue = Number(value);

        element.textContent = Number.isFinite(numericValue)
            ? numericValue.toLocaleString('id-ID')
            : String(value ?? '-');
    }

    window.updateAtrDashboard = function (payload) {
        const statistics = payload?.statistics || {};
        const progress = payload?.progress || {};

        const monthElement =
            document.getElementById('atrStatisticsMonth');

        const badgeElement =
            document.getElementById('atrStatisticsBadge');

        const threshold =
            statistics.threshold || '98.5%';

        if (monthElement && statistics.month_label) {
            monthElement.textContent =
                statistics.month_label;
        }

        setTextById(
            'atrStatAbove',
            statistics.above_threshold
        );

        setTextById(
            'atrStatBelow',
            statistics.below_threshold
        );

        setTextById(
            'atrStatNoData',
            statistics.no_data
        );

        setTextById(
            'atrStatSick',
            statistics.total_sick
        );

        setTextById(
            'atrStatPermission',
            statistics.total_permission
        );

        setTextById(
            'atrStatAlpha',
            statistics.total_alpha
        );

        if (
            badgeElement &&
            statistics.below_threshold !== undefined
        ) {
            const belowValue =
                Number(statistics.below_threshold) || 0;

            badgeElement.textContent =
                belowValue.toLocaleString('id-ID') +
                ' di bawah ' +
                threshold;
        }

        [
            'atrProgressPending',
            'atrDetailProgressPending'
        ].forEach(function (id) {
            setTextById(id, progress.belum);
        });

        [
            'atrProgressDone',
            'atrDetailProgressDone'
        ].forEach(function (id) {
            setTextById(id, progress.sudah);
        });

        [
            'atrProgressTotal',
            'atrDetailProgressTotal'
        ].forEach(function (id) {
            setTextById(id, progress.total);
        });

        if (
            progress.sudah !== undefined &&
            progress.total !== undefined
        ) {
            const done =
                Number(progress.sudah) || 0;

            const total =
                Math.max(Number(progress.total) || 0, 1);

            const percentage =
                Math.min(
                    100,
                    Math.max(0, (done / total) * 100)
                );

            [
                'atrProgressBar',
                'atrDetailProgressBar'
            ].forEach(function (id) {
                const progressBar =
                    document.getElementById(id);

                if (progressBar) {
                    progressBar.style.width =
                        percentage + '%';
                }
            });
        }
    };

    /*
    |--------------------------------------------------------------------------
    | Label bulan mengikuti pilihan dropdown
    |--------------------------------------------------------------------------
    */

    const atrMonthSelect =
        document.getElementById('atrMonth');

    if (atrMonthSelect) {
        atrMonthSelect.addEventListener(
            'change',
            function () {
                const monthElement =
                    document.getElementById(
                        'atrStatisticsMonth'
                    );

                if (monthElement) {
                    monthElement.textContent =
                        atrMonthSelect.value;
                }
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DOKUMENTASI PEMANGGILAN ATR
    |--------------------------------------------------------------------------
    | Jika $atrDocumentationEndpoint dikirim dari controller, form akan POST
    | ke endpoint tersebut dengan FormData. Jika kosong, berjalan sebagai demo
    | front-end dan status disimpan sementara di localStorage.
    */

    const atrCallModal =
        document.getElementById('atrCallModal');

    const atrCallBackdrop =
        document.getElementById('atrCallBackdrop');

    const atrCallClose =
        document.getElementById('atrCallClose');

    const atrCallCancel =
        document.getElementById('atrCallCancel');

    const atrCallForm =
        document.getElementById('atrCallForm');

    const atrCallFile =
        document.getElementById('atrCallFile');

    const atrCallDropzone =
        document.getElementById('atrCallDropzone');

    const atrCallFileName =
        document.getElementById('atrCallFileName');

    const atrCallError =
        document.getElementById('atrCallError');

    const atrCallSave =
        document.getElementById('atrCallSave');

    const atrCallEmployeeId =
        document.getElementById('atrCallEmployeeId');

    const atrCallEmployeeName =
        document.getElementById('atrCallEmployeeName');

    const atrCallEmployeeMentor =
        document.getElementById('atrCallEmployeeMentor');

    const atrCallEmployeePeriod =
        document.getElementById('atrCallEmployeePeriod');

    const atrCallEmployeeScore =
        document.getElementById('atrCallEmployeeScore');

    const atrEmployeeCards =
        Array.from(
            document.querySelectorAll(
                '[data-atr-employee-card]'
            )
        );

    const atrProofObjectUrls = new Map();
    let activeAtrEmployeeCard = null;

    function formatCallDate(dateValue) {
        const date = dateValue
            ? new Date(dateValue)
            : new Date();

        if (Number.isNaN(date.getTime())) {
            return String(dateValue || '-');
        }

        return new Intl.DateTimeFormat(
            'id-ID',
            {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            }
        ).format(date);
    }

    function showAtrCallError(message) {
        if (!atrCallError) {
            return;
        }

        atrCallError.textContent = message || '';
        atrCallError.classList.toggle(
            'is-visible',
            Boolean(message)
        );
    }

    function setAtrSelectedFile(file) {
        if (!atrCallDropzone || !atrCallFileName) {
            return;
        }

        if (!file) {
            atrCallDropzone.classList.remove('has-file');
            atrCallFileName.textContent = '';
            return;
        }

        atrCallDropzone.classList.add('has-file');
        atrCallFileName.textContent = file.name;
    }

    function validateAtrCallFile(file) {
        if (!file) {
            return 'Pilih bukti pemanggilan terlebih dahulu.';
        }

        const allowedTypes = [
            'image/jpeg',
            'image/png',
            'application/pdf'
        ];

        if (!allowedTypes.includes(file.type)) {
            return 'Format file harus JPG, PNG, atau PDF.';
        }

        if (file.size > 5 * 1024 * 1024) {
            return 'Ukuran file maksimal 5MB.';
        }

        return '';
    }

    function openAtrCallModal(card) {
        if (
            !atrCallModal ||
            !atrCallBackdrop ||
            !atrCallForm
        ) {
            return;
        }

        activeAtrEmployeeCard = card;
        atrCallForm.reset();
        setAtrSelectedFile(null);
        showAtrCallError('');

        const scoreSummary =
            (card.dataset.score || '-') +
            ' · S:' +
            (card.dataset.sick || '0') +
            ' I:' +
            (card.dataset.permission || '0') +
            ' A:' +
            (card.dataset.alpha || '0');

        if (atrCallEmployeeId) {
            atrCallEmployeeId.value =
                card.dataset.employeeId || '';
        }

        if (atrCallEmployeeName) {
            atrCallEmployeeName.textContent =
                card.dataset.name || '-';
        }

        if (atrCallEmployeeMentor) {
            atrCallEmployeeMentor.textContent =
                card.dataset.mentor || '-';
        }

        if (atrCallEmployeePeriod) {
            atrCallEmployeePeriod.textContent =
                card.dataset.period || '-';
        }

        if (atrCallEmployeeScore) {
            atrCallEmployeeScore.textContent =
                scoreSummary;
        }

        atrCallModal.classList.add('is-open');
        atrCallBackdrop.classList.add('is-open');

        atrCallModal.setAttribute('aria-hidden', 'false');
        atrCallBackdrop.setAttribute('aria-hidden', 'false');
    }

    function closeAtrCallModal() {
        if (!atrCallModal || !atrCallBackdrop) {
            return;
        }

        atrCallModal.classList.remove('is-open');
        atrCallBackdrop.classList.remove('is-open');

        atrCallModal.setAttribute('aria-hidden', 'true');
        atrCallBackdrop.setAttribute('aria-hidden', 'true');

        activeAtrEmployeeCard = null;
    }

    function renderAtrCalledState(card, calledAt) {
        const footer =
            card.querySelector('[data-atr-card-footer]');

        if (!footer) {
            return;
        }

        footer.innerHTML =
            '<div class="atr-called-state">' +
                '<div>' +
                    '<strong>● Sudah Dipanggil</strong>' +
                    '<small>' +
                        escapeHtml(calledAt) +
                    '</small>' +
                    '<button type="button" ' +
                        'class="atr-proof-link" ' +
                        'data-view-proof>' +
                        '▣ Lihat Bukti' +
                    '</button>' +
                '</div>' +
            '</div>';

        card.dataset.called = '1';
        card.dataset.calledAt = calledAt;
    }

    function changeAtrProgressAfterCall() {
        const pendingIds = [
            'atrProgressPending',
            'atrDetailProgressPending'
        ];

        const doneIds = [
            'atrProgressDone',
            'atrDetailProgressDone'
        ];

        const totalIds = [
            'atrProgressTotal',
            'atrDetailProgressTotal'
        ];

        function readNumber(id) {
            const element = document.getElementById(id);

            return element
                ? Number(
                    element.textContent
                        .replace(/[^0-9.-]/g, '')
                  ) || 0
                : 0;
        }

        const pending = Math.max(
            0,
            readNumber(pendingIds[0]) - 1
        );

        const done = readNumber(doneIds[0]) + 1;
        const total = Math.max(readNumber(totalIds[0]), 1);

        pendingIds.forEach(function (id) {
            setTextById(id, pending);
        });

        doneIds.forEach(function (id) {
            setTextById(id, done);
        });

        const percentage = Math.min(
            100,
            Math.max(0, (done / total) * 100)
        );

        [
            'atrProgressBar',
            'atrDetailProgressBar'
        ].forEach(function (id) {
            const bar = document.getElementById(id);

            if (bar) {
                bar.style.width = percentage + '%';
            }
        });
    }

    function storeAtrDemoState(card, state) {
        try {
            const key =
                'synrgypro.atr.call.' +
                (card.dataset.employeeId || card.dataset.name);

            localStorage.setItem(
                key,
                JSON.stringify(state)
            );
        } catch (error) {
            console.warn('Local storage tidak tersedia.', error);
        }
    }

    function restoreAtrDemoStates() {
        let restoredCount = 0;

        atrEmployeeCards.forEach(function (card) {
            if (card.dataset.called === '1') {
                return;
            }

            try {
                const key =
                    'synrgypro.atr.call.' +
                    (card.dataset.employeeId || card.dataset.name);

                const saved = JSON.parse(
                    localStorage.getItem(key) || 'null'
                );

                if (!saved?.calledAt) {
                    return;
                }

                card.dataset.proofFileName =
                    saved.fileName || '';

                renderAtrCalledState(
                    card,
                    saved.calledAt
                );

                restoredCount += 1;
            } catch (error) {
                console.warn('Status demo gagal dipulihkan.', error);
            }
        });

        for (let index = 0; index < restoredCount; index += 1) {
            changeAtrProgressAfterCall();
        }
    }

    document.addEventListener('click', function (event) {
        const callButton =
            event.target.closest('[data-call-open]');

        if (callButton) {
            const card = callButton.closest(
                '[data-atr-employee-card]'
            );

            if (card) {
                openAtrCallModal(card);
            }

            return;
        }

        const proofButton =
            event.target.closest('[data-view-proof]');

        if (!proofButton) {
            return;
        }

        const card = proofButton.closest(
            '[data-atr-employee-card]'
        );

        if (!card) {
            return;
        }

        const proofUrl =
            card.dataset.proofUrl ||
            atrProofObjectUrls.get(
                card.dataset.employeeId
            );

        if (proofUrl) {
            window.open(
                proofUrl,
                '_blank',
                'noopener,noreferrer'
            );
            return;
        }

        const fileName =
            card.dataset.proofFileName || '';

        window.alert(
            fileName
                ? 'Bukti tersimpan: ' + fileName +
                  '. URL bukti akan tersedia setelah backend terhubung.'
                : 'Bukti tersimpan di backend, tetapi URL bukti belum diberikan.'
        );
    });

    if (atrCallFile) {
        atrCallFile.addEventListener('change', function () {
            const file = atrCallFile.files?.[0] || null;
            const validationError = validateAtrCallFile(file);

            setAtrSelectedFile(file);
            showAtrCallError(
                file && validationError
                    ? validationError
                    : ''
            );
        });
    }

    if (atrCallDropzone) {
        ['dragenter', 'dragover'].forEach(function (type) {
            atrCallDropzone.addEventListener(type, function (event) {
                event.preventDefault();
                atrCallDropzone.classList.add('is-dragging');
            });
        });

        ['dragleave', 'drop'].forEach(function (type) {
            atrCallDropzone.addEventListener(type, function (event) {
                event.preventDefault();
                atrCallDropzone.classList.remove('is-dragging');
            });
        });

        atrCallDropzone.addEventListener('drop', function (event) {
            const file = event.dataTransfer?.files?.[0] || null;

            if (!file || !atrCallFile) {
                return;
            }

            const transfer = new DataTransfer();
            transfer.items.add(file);
            atrCallFile.files = transfer.files;

            const validationError = validateAtrCallFile(file);
            setAtrSelectedFile(file);
            showAtrCallError(validationError);
        });
    }

    [
        atrCallClose,
        atrCallCancel,
        atrCallBackdrop
    ].forEach(function (element) {
        if (element) {
            element.addEventListener(
                'click',
                closeAtrCallModal
            );
        }
    });

    document.addEventListener('keydown', function (event) {
        if (
            event.key === 'Escape' &&
            atrCallModal?.classList.contains('is-open')
        ) {
            closeAtrCallModal();
        }
    });

    if (atrCallForm) {
        atrCallForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            if (!activeAtrEmployeeCard) {
                return;
            }

            const file = atrCallFile?.files?.[0] || null;
            const validationError = validateAtrCallFile(file);

            if (validationError) {
                showAtrCallError(validationError);
                return;
            }

            showAtrCallError('');

            if (atrCallSave) {
                atrCallSave.disabled = true;
                atrCallSave.textContent = 'Menyimpan...';
            }

            try {
                const submitUrl =
                    atrCallForm.dataset.submitUrl || '';

                let calledAt = formatCallDate(new Date());
                let proofUrl = '';

                if (submitUrl) {
                    const formData = new FormData(atrCallForm);

                    formData.append(
                        'period',
                        activeAtrEmployeeCard.dataset.period || ''
                    );

                    const csrfToken =
                        document.querySelector(
                            'meta[name="csrf-token"]'
                        )?.content;

                    const response = await fetch(
                        submitUrl,
                        {
                            method: 'POST',
                            headers: csrfToken
                                ? {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json'
                                  }
                                : {
                                    'Accept': 'application/json'
                                  },
                            body: formData
                        }
                    );

                    if (!response.ok) {
                        const errorPayload = await response
                            .json()
                            .catch(function () {
                                return {};
                            });

                        throw new Error(
                            errorPayload.message ||
                            'Dokumentasi gagal disimpan.'
                        );
                    }

                    const payload = await response.json();

                    calledAt = formatCallDate(
                        payload.called_at || new Date()
                    );

                    proofUrl =
                        payload.proof_url || '';
                } else {
                    const objectUrl = URL.createObjectURL(file);

                    atrProofObjectUrls.set(
                        activeAtrEmployeeCard.dataset.employeeId,
                        objectUrl
                    );
                }

                activeAtrEmployeeCard.dataset.proofUrl = proofUrl;
                activeAtrEmployeeCard.dataset.proofFileName = file.name;

                renderAtrCalledState(
                    activeAtrEmployeeCard,
                    calledAt
                );

                storeAtrDemoState(
                    activeAtrEmployeeCard,
                    {
                        calledAt: calledAt,
                        fileName: file.name
                    }
                );

                changeAtrProgressAfterCall();
                closeAtrCallModal();
            } catch (error) {
                showAtrCallError(
                    error.message ||
                    'Dokumentasi gagal disimpan.'
                );
            } finally {
                if (atrCallSave) {
                    atrCallSave.disabled = false;
                    atrCallSave.textContent =
                        '✓ Simpan Dokumentasi';
                }
            }
        });
    }

    restoreAtrDemoStates();

    applyEmployeeFilter();
});
</script>
@endpush