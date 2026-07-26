@extends('layouts.app')

@section('title', 'Admin All — SYNRGYPRO')
@section('body-class', 'syn-admin-all-page')

@push('styles')
<style>
    :root {
        --aa-sidebar: 225px;
        --aa-sidebar-collapsed: 72px;
        --aa-header: 64px;
        --aa-footer: 28px;

        --aa-bg: #f3f5f7;
        --aa-surface: #ffffff;
        --aa-soft: #f8fafc;
        --aa-border: #dce2e8;
        --aa-text: #1f2937;
        --aa-muted: #6b7280;

        --aa-black: #121212;
        --aa-red: #d71920;
        --aa-blue: #1478e8;
        --aa-green: #20b26b;
        --aa-cyan: #11b8a6;
        --aa-orange: #f59e0b;
        --aa-purple: #7c3aed;
        --aa-gray: #64748b;

        --aa-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
    }

    * {
        box-sizing: border-box;
    }

    body.syn-admin-all-page {
        margin: 0;
        overflow: hidden;
        color: var(--aa-text);
        background: var(--aa-bg);
        font-family: Arial, Helvetica, sans-serif;
    }

    button,
    input,
    select,
    textarea {
        font: inherit;
    }

    button {
        cursor: pointer;
    }

    [hidden] {
        display: none !important;
    }

    .aa-page {
        display: grid;
        width: 100%;
        height: 100vh;
        grid-template-columns: var(--aa-sidebar) minmax(0, 1fr);
        grid-template-rows:
            var(--aa-header)
            minmax(0, 1fr)
            var(--aa-footer);
        overflow: hidden;
        background: var(--aa-bg);
        transition: grid-template-columns .24s ease;
    }

    /* =====================================================
       SIDEBAR
       ===================================================== */

    .aa-sidebar {
        display: flex;
        grid-row: 1 / 4;
        min-width: 0;
        flex-direction: column;
        border-right: 1px solid #c7ccd2;
        background: linear-gradient(180deg, #f1f1f1 0%, #dddddd 100%);
    }

    .aa-sidebar-head {
        display: grid;
        min-height: var(--aa-header);
        grid-template-columns: minmax(0, 1fr) 52px;
        border-bottom: 1px solid #606060;
        background: var(--aa-black);
    }

    .aa-sidebar-logo {
        display: grid;
        place-items: center;
        min-width: 0;
        padding: 5px;
        overflow: hidden;
    }

    .aa-sidebar-logo img {
        display: block;
        width: 76px;
        height: 52px;
        object-fit: contain;
    }

    .aa-sidebar-toggle {
        display: grid;
        width: 52px;
        place-items: center;
        padding: 0;
        border: 0;
        border-left: 1px solid #666;
        color: #151515;
        background: #fff;
        font-size: 28px;
        line-height: 1;
    }

    .aa-navigation {
        flex: 1;
        padding: 10px 0;
        overflow-x: hidden;
        overflow-y: auto;
    }

    .aa-menu-link,
    .aa-menu-toggle {
        display: flex;
        width: 100%;
        min-height: 44px;
        align-items: center;
        gap: 11px;
        padding: 10px 15px;
        border: 0;
        border-left: 4px solid transparent;
        color: #111;
        background: transparent;
        font-size: 13px;
        font-weight: 800;
        line-height: 1.25;
        text-align: left;
        text-decoration: none;
        transition:
            background .18s ease,
            border-color .18s ease,
            color .18s ease;
    }

    .aa-menu-link:hover,
    .aa-menu-toggle:hover,
    .aa-menu-link.active,
    .aa-menu-group.is-open > .aa-menu-toggle {
        background: rgba(255, 255, 255, .78);
    }

    .aa-menu-link.active,
    .aa-submenu-button.active {
        border-left-color: var(--aa-red);
    }

    .aa-menu-icon {
        display: grid;
        width: 23px;
        height: 23px;
        flex: 0 0 23px;
        place-items: center;
        color: #222;
        font-size: 16px;
    }

    .aa-menu-icon img {
        width: 21px;
        height: 21px;
        opacity: .86;
        object-fit: contain;
        filter: grayscale(1) contrast(1.1);
    }

    .aa-menu-label {
        min-width: 0;
        flex: 1;
    }

    .aa-menu-arrow {
        display: inline-grid;
        width: 18px;
        height: 18px;
        place-items: center;
        margin-left: auto;
        font-size: 18px;
        transition: transform .2s ease;
    }

    .aa-menu-group.is-open .aa-menu-arrow {
        transform: rotate(90deg);
    }

    .aa-submenu {
        display: grid;
        grid-template-rows: 0fr;
        opacity: 0;
        transition:
            grid-template-rows .22s ease,
            opacity .18s ease;
    }

    .aa-menu-group.is-open .aa-submenu {
        grid-template-rows: 1fr;
        opacity: 1;
    }

    .aa-submenu-inner {
        overflow: hidden;
    }

    .aa-submenu-button,
    .aa-submenu-link {
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
        text-decoration: none;
        transition:
            background .18s ease,
            border-color .18s ease;
    }

    .aa-submenu-button::before,
    .aa-submenu-link::before {
        position: absolute;
        left: 34px;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #444;
        content: "";
    }

    .aa-submenu-button:hover,
    .aa-submenu-link:hover,
    .aa-submenu-button.active {
        color: #111;
        background: rgba(255, 255, 255, .9);
    }

    .aa-sidebar-bottom {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
        padding: 14px 12px 18px;
    }

    .aa-bottom-link {
        display: inline-flex;
        width: 100%;
        max-width: 155px;
        min-height: 34px;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border-radius: 8px;
        color: #111;
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
        transition: background .18s ease;
    }

    .aa-bottom-link:hover {
        background: rgba(255, 255, 255, .75);
    }

    .aa-bottom-link.help span:first-child {
        color: var(--aa-red);
    }

    /* =====================================================
       HEADER
       ===================================================== */

    .aa-header {
        display: grid;
        grid-column: 2;
        grid-row: 1;
        min-width: 0;
        grid-template-columns: minmax(0, 1fr) auto;
        border-bottom: 1px solid var(--aa-border);
        background: #fff;
    }

    .aa-header-brand {
        display: flex;
        min-width: 0;
        align-items: center;
        justify-content: center;
        padding: 0 18px;
        overflow: hidden;
        background:
            linear-gradient(
                90deg,
                #1b1b1b 0%,
                #2d2d2d 48%,
                #4b2424 76%,
                #d95d20 100%
            );
    }

    .aa-header-brand img {
        width: 125px;
        max-height: 45px;
        object-fit: contain;
    }

    .aa-header-actions {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 0 11px;
        background: #fff;
    }

    .aa-header-button {
        display: inline-grid;
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        place-items: center;
        padding: 0;
        overflow: hidden;
        border: 2px solid #111;
        border-radius: 50%;
        background: #fff;
        text-decoration: none;
        transition:
            transform .18s ease,
            box-shadow .18s ease;
    }

    .aa-header-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 16px rgba(0, 0, 0, .14);
    }

    .aa-header-button img {
        width: 72%;
        height: 72%;
        object-fit: contain;
    }

    .aa-profile-button img,
    .aa-logout-button img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    .aa-logout-form {
        display: flex;
        margin: 0;
    }

    .aa-logout-button {
        border-color: transparent;
    }

    /* =====================================================
       CONTENT & GENERIC
       ===================================================== */

    .aa-content {
        position: relative;
        grid-column: 2;
        grid-row: 2;
        min-width: 0;
        min-height: 0;
        padding: 14px;
        overflow-x: hidden;
        overflow-y: auto;
        background: var(--aa-bg);
    }

    .aa-view {
        display: none;
        min-height: 100%;
    }

    .aa-view.active {
        display: block;
    }

    .aa-page-title {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 12px;
    }

    .aa-page-title h1 {
        margin: 0;
        color: #111827;
        font-size: 21px;
        line-height: 1.15;
    }

    .aa-page-title p {
        margin: 4px 0 0;
        color: var(--aa-muted);
        font-size: 12px;
    }

    .aa-panel,
    .aa-kpi-card,
    .aa-table-card,
    .aa-form-card,
    .aa-chart-card,
    .aa-link-card,
    .aa-download-card {
        border: 1px solid var(--aa-border);
        background: var(--aa-surface);
        box-shadow: var(--aa-shadow);
    }

    .aa-panel,
    .aa-table-card,
    .aa-form-card,
    .aa-chart-card,
    .aa-link-card,
    .aa-download-card {
        border-radius: 13px;
        overflow: hidden;
    }

    .aa-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 15px;
        border-bottom: 1px solid var(--aa-border);
    }

    .aa-card-header h2 {
        margin: 0;
        color: #111827;
        font-size: 14px;
    }

    .aa-card-header small {
        color: var(--aa-muted);
        font-size: 11px;
    }

    .aa-button {
        display: inline-flex;
        min-height: 39px;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 0 15px;
        border: 0;
        border-radius: 9px;
        color: #fff;
        background: var(--aa-blue);
        font-size: 12px;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
    }

    .aa-button.secondary {
        border: 1px solid #ccd3da;
        color: #374151;
        background: #fff;
    }

    .aa-button.success {
        background: var(--aa-green);
    }

    .aa-button.danger {
        background: var(--aa-red);
    }

    .aa-button.dark {
        background: #30363d;
    }

    .aa-field {
        display: grid;
        gap: 6px;
    }

    .aa-field label {
        color: #374151;
        font-size: 12px;
        font-weight: 800;
    }

    .aa-input,
    .aa-select,
    .aa-textarea {
        width: 100%;
        min-width: 0;
        border: 1px solid #ccd3da;
        border-radius: 9px;
        outline: none;
        color: var(--aa-text);
        background: #fff;
        font-size: 13px;
    }

    .aa-input,
    .aa-select {
        height: 39px;
        padding: 0 12px;
    }

    .aa-textarea {
        min-height: 90px;
        padding: 11px 12px;
        resize: vertical;
    }

    .aa-input:focus,
    .aa-select:focus,
    .aa-textarea:focus {
        border-color: var(--aa-blue);
        box-shadow: 0 0 0 3px rgba(20, 120, 232, .12);
    }

    .aa-badge {
        display: inline-flex;
        min-height: 24px;
        align-items: center;
        justify-content: center;
        padding: 0 9px;
        border-radius: 999px;
        color: #1d4ed8;
        background: #e8f0ff;
        font-size: 10px;
        font-weight: 900;
        white-space: nowrap;
    }

    .aa-badge.green {
        color: #087443;
        background: #e4f8ee;
    }

    .aa-badge.orange {
        color: #a85b00;
        background: #fff1dc;
    }

    .aa-badge.red {
        color: #c51f2b;
        background: #ffe8eb;
    }

    .aa-badge.gray {
        color: #4b5563;
        background: #eef1f4;
    }

    .aa-badge.cyan {
        color: #087c73;
        background: #e4faf7;
    }

    /* =====================================================
       DASHBOARD
       ===================================================== */

    .aa-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 12px;
    }

    .aa-kpi-card {
        display: grid;
        width: 100%;
        min-height: 94px;
        grid-template-columns: 48px minmax(0, 1fr);
        gap: 11px;
        align-items: center;
        padding: 14px;
        border-radius: 13px;
        color: inherit;
        text-align: left;
        transition:
            transform .18s ease,
            border-color .18s ease;
    }

    .aa-kpi-card:hover {
        border-color: #adb7c1;
        transform: translateY(-2px);
    }

    .aa-kpi-icon {
        display: grid;
        width: 48px;
        height: 48px;
        place-items: center;
        border-radius: 13px;
        color: #fff;
        background: #30363d;
        font-size: 21px;
    }

    .aa-kpi-card:nth-child(2) .aa-kpi-icon {
        background: var(--aa-blue);
    }

    .aa-kpi-card:nth-child(3) .aa-kpi-icon {
        background: var(--aa-orange);
    }

    .aa-kpi-card:nth-child(4) .aa-kpi-icon {
        background: var(--aa-purple);
    }

    .aa-kpi-card small {
        display: block;
        margin-bottom: 5px;
        color: var(--aa-muted);
        font-size: 11px;
        font-weight: 800;
    }

    .aa-kpi-value {
        display: block;
        color: #111827;
        font-size: 26px;
        font-weight: 900;
        line-height: 1;
    }

    .aa-dashboard-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .aa-quick-list {
        display: grid;
        gap: 9px;
        padding: 13px;
    }

    .aa-quick-item {
        display: grid;
        grid-template-columns: 42px minmax(0, 1fr) auto;
        gap: 10px;
        align-items: center;
        padding: 11px;
        border: 1px solid #e1e6eb;
        border-radius: 10px;
        background: #fff;
    }

    .aa-quick-icon {
        display: grid;
        width: 42px;
        height: 42px;
        place-items: center;
        border-radius: 11px;
        color: #fff;
        background: var(--aa-blue);
        font-size: 18px;
    }

    .aa-quick-item strong {
        display: block;
        font-size: 12px;
    }

    .aa-quick-item small {
        display: block;
        margin-top: 4px;
        color: var(--aa-muted);
        font-size: 10px;
    }

    /* =====================================================
       FORMS
       ===================================================== */

    .aa-form-body {
        padding: 16px;
    }

    .aa-form-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 13px;
    }

    .aa-form-grid .span-2 {
        grid-column: span 2;
    }

    .aa-form-grid .span-3 {
        grid-column: span 3;
    }

    .aa-form-grid .span-4 {
        grid-column: 1 / -1;
    }

    .aa-form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 16px;
        padding-top: 14px;
        border-top: 1px solid var(--aa-border);
    }

    /* =====================================================
       TABLE
       ===================================================== */

    .aa-table-wrap {
        max-height: 410px;
        overflow: auto;
    }

    .aa-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 12px;
    }

    .aa-table th,
    .aa-table td {
        padding: 10px 11px;
        border-bottom: 1px solid var(--aa-border);
        text-align: left;
        white-space: nowrap;
    }

    .aa-table th {
        position: sticky;
        top: 0;
        z-index: 2;
        color: #374151;
        background: #f8fafc;
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .aa-table tbody tr:hover {
        background: #f8fbff;
    }

    .aa-table-action {
        display: inline-flex;
        min-height: 30px;
        align-items: center;
        justify-content: center;
        padding: 0 10px;
        border: 0;
        border-radius: 7px;
        color: #fff;
        background: var(--aa-blue);
        font-size: 10px;
        font-weight: 900;
    }

    .aa-empty-state {
        padding: 30px 16px !important;
        color: var(--aa-muted);
        text-align: center !important;
    }

    /* =====================================================
       TICKETING / IFUTS
       ===================================================== */

    .aa-status-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 12px;
    }

    .aa-status-card {
        padding: 14px;
        border: 1px solid var(--aa-border);
        border-top: 3px solid var(--status-color, var(--aa-blue));
        border-radius: 12px;
        background: #fff;
        box-shadow: var(--aa-shadow);
    }

    .aa-status-card strong {
        display: block;
        color: var(--status-color, var(--aa-blue));
        font-size: 24px;
    }

    .aa-status-card small {
        display: block;
        margin-top: 5px;
        color: var(--aa-muted);
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .aa-status-card.waiting {
        --status-color: var(--aa-orange);
    }

    .aa-status-card.process {
        --status-color: var(--aa-blue);
    }

    .aa-status-card.done {
        --status-color: var(--aa-green);
    }

    .aa-status-card.reject {
        --status-color: var(--aa-red);
    }

    /* =====================================================
       DOWNLOAD BERITA ACARA / E-ARSIP
       ===================================================== */

    .aa-download-card,
    .aa-link-card {
        display: grid;
        grid-template-columns: 100px minmax(0, 1fr) auto;
        gap: 18px;
        align-items: center;
        padding: 22px;
    }

    .aa-download-illustration,
    .aa-link-illustration {
        display: grid;
        width: 100px;
        height: 100px;
        place-items: center;
        padding: 8px;
        overflow: hidden;
        border: 1px solid #e2e7ec;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 6px 16px rgba(15, 23, 42, .08);
    }

    .aa-download-illustration img,
    .aa-link-illustration img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .aa-download-card h2,
    .aa-link-card h2 {
        margin: 0 0 7px;
        font-size: 20px;
    }

    .aa-download-card p,
    .aa-link-card p {
        margin: 0;
        color: var(--aa-muted);
        font-size: 13px;
        line-height: 1.6;
    }

    .aa-note-box {
        margin-top: 12px;
        padding: 13px 15px;
        border: 1px solid #cfe3ff;
        border-radius: 10px;
        color: #22558c;
        background: #eef6ff;
        font-size: 12px;
        line-height: 1.55;
    }

    /* =====================================================
       RKB DASHBOARD
       ===================================================== */

    .aa-rkb-status-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 12px;
    }

    .aa-rkb-status-card {
        min-height: 112px;
        padding: 15px;
        border: 1px solid var(--aa-border);
        border-radius: 13px;
        background: #fff;
        box-shadow: var(--aa-shadow);
    }

    .aa-rkb-status-card h3 {
        margin: 0 0 6px;
        font-size: 13px;
    }

    .aa-rkb-status-card p {
        margin: 0 0 12px;
        color: var(--aa-muted);
        font-size: 10px;
    }

    .aa-rkb-count-row {
        display: flex;
        gap: 20px;
    }

    .aa-rkb-count-row span {
        display: grid;
        gap: 3px;
    }

    .aa-rkb-count-row strong {
        font-size: 22px;
    }

    .aa-rkb-count-row small {
        color: var(--aa-muted);
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .aa-chart-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 12px;
    }

    .aa-chart-body {
        min-height: 250px;
        padding: 18px;
    }

    .aa-donut-layout {
        display: grid;
        min-height: 210px;
        grid-template-columns: 170px minmax(0, 1fr);
        gap: 18px;
        place-items: center;
    }

    .aa-donut {
        position: relative;
        display: grid;
        width: 155px;
        height: 155px;
        place-items: center;
        border-radius: 50%;
        background:
            conic-gradient(
                #1478e8 0deg 62deg,
                #86a5b2 62deg 240deg,
                #dfab79 240deg 360deg
            );
    }

    .aa-donut::after {
        position: absolute;
        width: 82px;
        height: 82px;
        border-radius: 50%;
        background: #fff;
        content: "";
    }

    .aa-donut-center {
        position: relative;
        z-index: 1;
        text-align: center;
    }

    .aa-donut-center strong {
        display: block;
        font-size: 24px;
    }

    .aa-donut-center span {
        color: var(--aa-muted);
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .aa-legend {
        display: grid;
        gap: 10px;
    }

    .aa-legend-item {
        display: grid;
        grid-template-columns: 11px minmax(0, 1fr) auto;
        gap: 8px;
        align-items: center;
        font-size: 11px;
    }

    .aa-legend-dot {
        width: 11px;
        height: 11px;
        border-radius: 50%;
        background: var(--legend-color, var(--aa-blue));
    }

    .aa-bars {
        display: flex;
        height: 205px;
        align-items: flex-end;
        gap: 14px;
        padding: 0 8px 28px;
        border-bottom: 1px solid #9ca3af;
        border-left: 1px solid #9ca3af;
    }

    .aa-bar {
        position: relative;
        flex: 1;
        min-width: 24px;
        max-width: 70px;
        border-radius: 6px 6px 0 0;
        background: var(--bar-color, var(--aa-blue));
    }

    .aa-bar-value {
        position: absolute;
        top: -20px;
        right: 0;
        left: 0;
        font-size: 10px;
        font-weight: 900;
        text-align: center;
    }

    .aa-bar-label {
        position: absolute;
        right: -7px;
        bottom: -22px;
        left: -7px;
        color: var(--aa-muted);
        font-size: 9px;
        font-weight: 800;
        text-align: center;
    }

    /* =====================================================
       STOCK
       ===================================================== */

    .aa-stock-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 12px;
    }

    .aa-stock-card {
        padding: 14px;
        border: 1px solid var(--aa-border);
        border-radius: 12px;
        background: #fff;
        box-shadow: var(--aa-shadow);
        text-align: center;
    }

    .aa-stock-card strong {
        display: block;
        color: #111827;
        font-size: 24px;
    }

    .aa-stock-card small {
        display: block;
        margin-top: 5px;
        color: var(--aa-muted);
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
    }

    /* =====================================================
       FILE INPUT / TOAST / LOADING
       ===================================================== */

    .aa-file-box {
        display: grid;
        min-height: 110px;
        place-items: center;
        padding: 14px;
        border: 1px dashed #b8c4d0;
        border-radius: 10px;
        color: var(--aa-muted);
        background: #f8fafc;
        text-align: center;
    }

    .aa-file-box input {
        width: 100%;
    }

    .aa-toast {
        position: fixed;
        right: 22px;
        bottom: 48px;
        z-index: 1400;
        min-width: 260px;
        max-width: 390px;
        padding: 13px 15px;
        visibility: hidden;
        opacity: 0;
        border-radius: 10px;
        color: #fff;
        background: #1f2937;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .22);
        font-size: 12px;
        font-weight: 800;
        transform: translateY(15px);
        transition:
            opacity .2s ease,
            transform .2s ease,
            visibility .2s ease;
    }

    .aa-toast.is-visible {
        visibility: visible;
        opacity: 1;
        transform: translateY(0);
    }

    .aa-loading-layer {
        position: absolute;
        inset: 0;
        z-index: 40;
        display: none;
        padding: 14px;
        background: rgba(243, 245, 247, .94);
    }

    .aa-loading-layer.is-visible {
        display: block;
    }

    .aa-loading-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }

    .aa-skeleton {
        position: relative;
        min-height: 95px;
        overflow: hidden;
        border-radius: 12px;
        background: #e3e7eb;
    }

    .aa-skeleton.large {
        min-height: 300px;
        grid-column: span 2;
        margin-top: 12px;
    }

    .aa-skeleton.table {
        min-height: 230px;
        grid-column: 1 / -1;
        margin-top: 12px;
    }

    .aa-skeleton::after {
        position: absolute;
        inset: 0;
        background:
            linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, .7),
                transparent
            );
        content: "";
        transform: translateX(-100%);
        animation: aa-shimmer 1.2s infinite;
    }

    @keyframes aa-shimmer {
        100% {
            transform: translateX(100%);
        }
    }

    /* =====================================================
       FOOTER / COLLAPSE / RESPONSIVE
       ===================================================== */

    .aa-footer {
        display: flex;
        grid-column: 2;
        grid-row: 3;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: #383838;
        font-size: 9px;
        font-weight: 800;
    }

    .aa-page.sidebar-collapsed {
        grid-template-columns:
            var(--aa-sidebar-collapsed)
            minmax(0, 1fr);
    }

    .aa-page.sidebar-collapsed .aa-sidebar-head {
        grid-template-columns: var(--aa-sidebar-collapsed);
    }

    .aa-page.sidebar-collapsed .aa-sidebar-logo,
    .aa-page.sidebar-collapsed .aa-menu-label,
    .aa-page.sidebar-collapsed .aa-menu-arrow,
    .aa-page.sidebar-collapsed .aa-submenu,
    .aa-page.sidebar-collapsed .aa-sidebar-bottom {
        display: none;
    }

    .aa-page.sidebar-collapsed .aa-sidebar-toggle {
        width: var(--aa-sidebar-collapsed);
        border-left: 0;
    }

    .aa-page.sidebar-collapsed .aa-menu-link,
    .aa-page.sidebar-collapsed .aa-menu-toggle {
        justify-content: center;
        padding-inline: 0;
        border-left-color: transparent;
    }

    @media (max-width: 1450px) {
        :root {
            --aa-sidebar: 205px;
        }

        .aa-content {
            padding: 11px;
        }

        .aa-kpi-grid,
        .aa-status-grid,
        .aa-rkb-status-grid,
        .aa-stock-grid {
            gap: 9px;
        }

        .aa-form-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .aa-form-grid .span-4 {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 1180px) {
        .aa-kpi-grid,
        .aa-status-grid,
        .aa-stock-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .aa-dashboard-grid,
        .aa-chart-grid {
            grid-template-columns: 1fr;
        }

        .aa-form-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .aa-form-grid .span-3 {
            grid-column: 1 / -1;
        }

        .aa-download-card,
        .aa-link-card {
            grid-template-columns: 80px minmax(0, 1fr);
        }

        .aa-download-card .aa-button,
        .aa-link-card .aa-button {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 900px) {
        .aa-page {
            grid-template-columns:
                var(--aa-sidebar-collapsed)
                minmax(0, 1fr);
        }

        .aa-sidebar-head {
            grid-template-columns: var(--aa-sidebar-collapsed);
        }

        .aa-sidebar-logo,
        .aa-menu-label,
        .aa-menu-arrow,
        .aa-submenu,
        .aa-sidebar-bottom {
            display: none;
        }

        .aa-sidebar-toggle {
            width: var(--aa-sidebar-collapsed);
            border-left: 0;
        }

        .aa-menu-link,
        .aa-menu-toggle {
            justify-content: center;
            padding-inline: 0;
            border-left-color: transparent;
        }

        .aa-rkb-status-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
@php
    /*
    |--------------------------------------------------------------------------
    | DATA FALLBACK
    |--------------------------------------------------------------------------
    | Backend dapat mengirim:
    | $adminStats, $ticketRows, $rkbStats, $rkbRows,
    | $stockStats, $stockRows, $ifutsEndpoint, $rkbEndpoint, $stockEndpoint
    */

    $adminStats = $adminStats ?? [
        'ticket_pending' => 14,
        'ticket_process' => 8,
        'rkb_process' => 7,
        'stock_low' => 6,
    ];

    $ticketRows = collect($ticketRows ?? [
        [
            'nik' => '22005103',
            'name' => 'Dedy Prasetia',
            'department' => 'Produksi',
            'poh' => 'Yogyakarta',
            'route_out' => 'PLM-JOG',
            'date_out' => '2026-07-23',
            'route_in' => 'JOG-PLM',
            'date_in' => '2026-07-24',
            'status' => 'Menunggu Approval',
        ],
        [
            'nik' => '22008120',
            'name' => 'Intan Putri Utami',
            'department' => 'Produksi',
            'poh' => 'Palembang',
            'route_out' => 'PLM-SOC',
            'date_out' => '2026-07-25',
            'route_in' => 'SOC-PLM',
            'date_in' => '2026-07-28',
            'status' => 'Diproses',
        ],
        [
            'nik' => '21004219',
            'name' => 'Rizal Hidayat',
            'department' => 'Engineering',
            'poh' => 'Jakarta',
            'route_out' => 'PLM-CGK',
            'date_out' => '2026-07-29',
            'route_in' => 'CGK-PLM',
            'date_in' => '2026-08-02',
            'status' => 'Selesai',
        ],
    ]);

    $rkbStats = $rkbStats ?? [
        'waiting_approval_sh' => 2,
        'waiting_approval_sm' => 0,
        'waiting_process' => 7,
        'waiting_supply' => 4,
        'ready' => 0,
        'reject' => 0,
        'finish' => 0,
    ];

    $rkbRows = collect($rkbRows ?? [
        [
            'duration' => 61,
            'status' => 'Waiting Approval SH',
            'date' => '2026-07-24 13:51:10',
            'requester' => 'INTAN PUTRI UTAMI',
            'position' => 'OPD ADMIN',
            'rkb_no' => '0122/RKB/PRO/PPA-BA/VII/2026',
            'item' => 'Portable SSD 1TB Sandisk Extreme E61',
            'quantity' => 1,
            'receiver' => 'Admin Produksi',
        ],
        [
            'duration' => 418,
            'status' => 'Waiting Process',
            'date' => '2026-07-09 16:47:10',
            'requester' => 'INTAN PUTRI UTAMI',
            'position' => 'OPD ADMIN',
            'rkb_no' => '0045/RKB/PRO/PPA-BA/VII/2026',
            'item' => 'TV LG 43UA7550 43 Inch UHD AI 4K',
            'quantity' => 2,
            'receiver' => 'Warehouse',
        ],
        [
            'duration' => 443,
            'status' => 'Waiting Supply',
            'date' => '2026-07-08 15:44:26',
            'requester' => 'INTAN PUTRI UTAMI',
            'position' => 'OPD ADMIN',
            'rkb_no' => '0041/RKB/PRO/PPA-BA/VII/2026',
            'item' => 'Toilet Portable',
            'quantity' => 3,
            'receiver' => 'Logistik',
        ],
    ]);

    $stockStats = $stockStats ?? [
        'total_item' => 428,
        'low_stock' => 6,
        'out_of_stock' => 2,
        'updated_today' => 18,
    ];

    $stockRows = collect($stockRows ?? [
        [
            'item' => 'Safety Helmet',
            'part_number' => 'SH-001',
            'location' => 'Gudang A',
            'stock' => 36,
            'minimum' => 10,
            'status' => 'Aman',
        ],
        [
            'item' => 'Gloves Heavy Duty',
            'part_number' => 'GLV-014',
            'location' => 'Gudang B',
            'stock' => 5,
            'minimum' => 12,
            'status' => 'Stok Rendah',
        ],
        [
            'item' => 'Portable Radio',
            'part_number' => 'RAD-022',
            'location' => 'Gudang A',
            'stock' => 0,
            'minimum' => 4,
            'status' => 'Habis',
        ],
    ]);

    $ifutsEndpoint = $ifutsEndpoint ?? null;
    $rkbEndpoint = $rkbEndpoint ?? null;
    $stockEndpoint = $stockEndpoint ?? null;
@endphp

<div class="aa-page" id="adminAllPage">
    {{-- SIDEBAR --}}
    <aside class="aa-sidebar">
        <div class="aa-sidebar-head">
            <div class="aa-sidebar-logo">
                <img
                    src="{{ asset('assets/images/LOGO ADMIN ALL.png') }}"
                    alt="Admin All"
                >
            </div>

            <button
                type="button"
                class="aa-sidebar-toggle"
                id="aaSidebarToggle"
                aria-label="Buka atau tutup sidebar"
            >
                ☰
            </button>
        </div>

        <nav class="aa-navigation" aria-label="Menu Admin All">
            <button
                type="button"
                class="aa-menu-link active"
                data-aa-view="aa-dashboard"
            >
                <span class="aa-menu-icon">▦</span>
                <span class="aa-menu-label">Dashboard</span>
            </button>

            <div class="aa-menu-group is-open">
                <button
                    type="button"
                    class="aa-menu-toggle"
                    aria-expanded="true"
                >
                    <span class="aa-menu-icon">🎫</span>
                    <span class="aa-menu-label">Ticketing Karyawan</span>
                    <span class="aa-menu-arrow">›</span>
                </button>

                <div class="aa-submenu">
                    <div class="aa-submenu-inner">
                        <button
                            type="button"
                            class="aa-submenu-button"
                            data-aa-view="aa-ticket-summary"
                        >
                            Ringkasan
                        </button>

                        <button
                            type="button"
                            class="aa-submenu-button"
                            data-aa-view="aa-ifuts-input"
                        >
                            Input IFUTS
                        </button>

                        <button
                            type="button"
                            class="aa-submenu-button"
                            data-aa-view="aa-berita-acara"
                        >
                            Berita Acara
                        </button>
                    </div>
                </div>
            </div>

            <div class="aa-menu-group is-open">
                <button
                    type="button"
                    class="aa-menu-toggle"
                    aria-expanded="true"
                >
                    <span class="aa-menu-icon">📦</span>
                    <span class="aa-menu-label">RKB</span>
                    <span class="aa-menu-arrow">›</span>
                </button>

                <div class="aa-submenu">
                    <div class="aa-submenu-inner">
                        <button
                            type="button"
                            class="aa-submenu-button"
                            data-aa-view="aa-rkb-summary"
                        >
                            Ringkasan
                        </button>

                        <button
                            type="button"
                            class="aa-submenu-button"
                            data-aa-view="aa-rkb-input"
                        >
                            Input Monitoring
                        </button>
                    </div>
                </div>
            </div>

            <div class="aa-menu-group">
                <button
                    type="button"
                    class="aa-menu-toggle"
                    aria-expanded="false"
                >
                    <span class="aa-menu-icon">🏭</span>
                    <span class="aa-menu-label">Stock Opname Gudang</span>
                    <span class="aa-menu-arrow">›</span>
                </button>

                <div class="aa-submenu">
                    <div class="aa-submenu-inner">
                        <button
                            type="button"
                            class="aa-submenu-button"
                            data-aa-view="aa-stock-summary"
                        >
                            Ringkasan
                        </button>

                        <button
                            type="button"
                            class="aa-submenu-button"
                            data-aa-view="aa-stock-update"
                        >
                            Update Stock Barang
                        </button>
                    </div>
                </div>
            </div>

            <div class="aa-menu-group">
                <button
                    type="button"
                    class="aa-menu-toggle"
                    aria-expanded="false"
                >
                    <span class="aa-menu-icon">🗂</span>
                    <span class="aa-menu-label">E-Arsip</span>
                    <span class="aa-menu-arrow">›</span>
                </button>

                <div class="aa-submenu">
                    <div class="aa-submenu-inner">
                        <button
                            type="button"
                            class="aa-submenu-button"
                            data-aa-view="aa-archive"
                        >
                            Link Google Drive
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <div class="aa-sidebar-bottom">
            <a href="#" class="aa-bottom-link">
                <span>⚙</span>
                <span>Pengaturan</span>
            </a>

            <a
                href="https://mail.google.com/mail/?view=cm&fs=1&to={{ urlencode(config('access.contact_email', 'mpe.ppaba@ppa.co.id')) }}&su=SYNRGYPRO%20Support"
                target="_blank"
                rel="noopener noreferrer"
                class="aa-bottom-link help"
            >
                <span>?</span>
                <span>Bantuan</span>
            </a>
        </div>
    </aside>

    {{-- HEADER --}}
    <header class="aa-header">
        <div class="aa-header-brand">
            <img
                src="{{ asset('assets/images/synrgypro-logo.png') }}"
                alt="SYNRGYPRO"
            >
        </div>

        <nav class="aa-header-actions" aria-label="Shortcut pengguna">
            <x-module-shortcut />

            <a
                href="{{ route('dashboard') }}"
                class="aa-header-button"
                aria-label="Dashboard"
            >
                <img
                    src="{{ asset('assets/images/LOGO HOME.jpeg') }}"
                    alt=""
                >
            </a>

            <button
                type="button"
                class="aa-header-button aa-profile-button"
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
                class="aa-logout-form"
            >
                @csrf

                <button
                    type="submit"
                    class="aa-header-button aa-logout-button"
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
    <main class="aa-content">
        <div
            class="aa-loading-layer"
            id="aaLoadingLayer"
            aria-hidden="true"
        >
            <div class="aa-loading-grid">
                <div class="aa-skeleton"></div>
                <div class="aa-skeleton"></div>
                <div class="aa-skeleton"></div>
                <div class="aa-skeleton"></div>
                <div class="aa-skeleton large"></div>
                <div class="aa-skeleton large"></div>
                <div class="aa-skeleton table"></div>
            </div>
        </div>

        {{-- DASHBOARD --}}
        <section class="aa-view active" id="aa-dashboard">
            <div class="aa-page-title">
                <div>
                    <h1>Dashboard Admin All</h1>
                    <p>
                        Ringkasan ticketing, RKB, stock gudang, dan arsip dokumen.
                    </p>
                </div>
            </div>

            <div class="aa-kpi-grid">
                <button
                    type="button"
                    class="aa-kpi-card"
                    data-open-view="aa-ticket-summary"
                >
                    <span class="aa-kpi-icon">🎫</span>
                    <span>
                        <small>Ticket Menunggu</small>
                        <span class="aa-kpi-value">
                            {{ number_format((int) data_get($adminStats, 'ticket_pending', 0)) }}
                        </span>
                    </span>
                </button>

                <button
                    type="button"
                    class="aa-kpi-card"
                    data-open-view="aa-ticket-summary"
                >
                    <span class="aa-kpi-icon">⏳</span>
                    <span>
                        <small>Ticket Diproses</small>
                        <span class="aa-kpi-value">
                            {{ number_format((int) data_get($adminStats, 'ticket_process', 0)) }}
                        </span>
                    </span>
                </button>

                <button
                    type="button"
                    class="aa-kpi-card"
                    data-open-view="aa-rkb-summary"
                >
                    <span class="aa-kpi-icon">📦</span>
                    <span>
                        <small>RKB On Process</small>
                        <span class="aa-kpi-value">
                            {{ number_format((int) data_get($adminStats, 'rkb_process', 0)) }}
                        </span>
                    </span>
                </button>

                <button
                    type="button"
                    class="aa-kpi-card"
                    data-open-view="aa-stock-summary"
                >
                    <span class="aa-kpi-icon">⚠</span>
                    <span>
                        <small>Stock Rendah</small>
                        <span class="aa-kpi-value">
                            {{ number_format((int) data_get($adminStats, 'stock_low', 0)) }}
                        </span>
                    </span>
                </button>
            </div>

            <div class="aa-dashboard-grid">
                <article class="aa-panel">
                    <div class="aa-card-header">
                        <div>
                            <h2>Akses Cepat Ticketing</h2>
                            <small>Input dan dokumen pemesanan tiket</small>
                        </div>
                    </div>

                    <div class="aa-quick-list">
                        <div class="aa-quick-item">
                            <span class="aa-quick-icon">🧾</span>
                            <span>
                                <strong>Input IFUTS</strong>
                                <small>Input data ticketing menggantikan spreadsheet manual.</small>
                            </span>
                            <button
                                type="button"
                                class="aa-button"
                                data-open-view="aa-ifuts-input"
                            >
                                Buka
                            </button>
                        </div>

                        <div class="aa-quick-item">
                            <span class="aa-quick-icon" style="background:#7c3aed;">📄</span>
                            <span>
                                <strong>Berita Acara Pemesanan Tiket</strong>
                                <small>Download template Word resmi.</small>
                            </span>
                            <button
                                type="button"
                                class="aa-button"
                                data-open-view="aa-berita-acara"
                            >
                                Buka
                            </button>
                        </div>
                    </div>
                </article>

                <article class="aa-panel">
                    <div class="aa-card-header">
                        <div>
                            <h2>Akses Cepat Administrasi</h2>
                            <small>RKB, stock gudang, dan arsip</small>
                        </div>
                    </div>

                    <div class="aa-quick-list">
                        <div class="aa-quick-item">
                            <span class="aa-quick-icon" style="background:#e06426;">📦</span>
                            <span>
                                <strong>Monitoring RKB</strong>
                                <small>Ringkasan status dan input item RKB.</small>
                            </span>
                            <button
                                type="button"
                                class="aa-button"
                                data-open-view="aa-rkb-summary"
                            >
                                Buka
                            </button>
                        </div>

                        <div class="aa-quick-item">
                            <span class="aa-quick-icon" style="background:#20b26b;">🗂</span>
                            <span>
                                <strong>E-Arsip Google Drive</strong>
                                <small>Buka folder arsip perusahaan.</small>
                            </span>
                            <button
                                type="button"
                                class="aa-button"
                                data-open-view="aa-archive"
                            >
                                Buka
                            </button>
                        </div>
                    </div>
                </article>
            </div>
        </section>

        {{-- TICKETING SUMMARY --}}
        <section class="aa-view" id="aa-ticket-summary">
            <div class="aa-page-title">
                <div>
                    <h1>Ringkasan Ticketing Karyawan</h1>
                    <p>
                        Monitoring permintaan tiket keluar dan kembali.
                    </p>
                </div>

                <button
                    type="button"
                    class="aa-button"
                    data-open-view="aa-ifuts-input"
                >
                    + Input IFUTS
                </button>
            </div>

            <div class="aa-status-grid">
                <article class="aa-status-card waiting">
                    <strong>14</strong>
                    <small>Menunggu Approval</small>
                </article>

                <article class="aa-status-card process">
                    <strong>8</strong>
                    <small>Diproses</small>
                </article>

                <article class="aa-status-card done">
                    <strong>27</strong>
                    <small>Selesai</small>
                </article>

                <article class="aa-status-card reject">
                    <strong>1</strong>
                    <small>Ditolak</small>
                </article>
            </div>

            <article class="aa-table-card">
                <div class="aa-card-header">
                    <div>
                        <h2>Daftar Permintaan Tiket</h2>
                        <small>{{ $ticketRows->count() }} data contoh</small>
                    </div>
                </div>

                <div class="aa-table-wrap">
                    <table class="aa-table">
                        <thead>
                            <tr>
                                <th>NIK</th>
                                <th>Nama</th>
                                <th>Departemen</th>
                                <th>POH</th>
                                <th>Rute Out</th>
                                <th>Tgl Out</th>
                                <th>Rute In</th>
                                <th>Tgl In</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($ticketRows as $ticket)
                                @php
                                    $ticketStatus = data_get($ticket, 'status', '-');
                                    $ticketClass = match ($ticketStatus) {
                                        'Selesai' => 'green',
                                        'Diproses' => '',
                                        'Ditolak' => 'red',
                                        default => 'orange',
                                    };
                                @endphp

                                <tr>
                                    <td>{{ data_get($ticket, 'nik', '-') }}</td>
                                    <td>{{ data_get($ticket, 'name', '-') }}</td>
                                    <td>{{ data_get($ticket, 'department', '-') }}</td>
                                    <td>{{ data_get($ticket, 'poh', '-') }}</td>
                                    <td>{{ data_get($ticket, 'route_out', '-') }}</td>
                                    <td>{{ data_get($ticket, 'date_out', '-') }}</td>
                                    <td>{{ data_get($ticket, 'route_in', '-') }}</td>
                                    <td>{{ data_get($ticket, 'date_in', '-') }}</td>
                                    <td>
                                        <span class="aa-badge {{ $ticketClass }}">
                                            {{ $ticketStatus }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

        {{-- INPUT IFUTS --}}
        <section class="aa-view" id="aa-ifuts-input">
            <div class="aa-page-title">
                <div>
                    <h1>Input IFUTS</h1>
                    <p>
                        Pengganti input manual spreadsheet untuk data ticketing.
                    </p>
                </div>
            </div>

            <form
                class="aa-form-card"
                id="aaIfutsForm"
                data-endpoint="{{ $ifutsEndpoint }}"
            >
                <div class="aa-card-header">
                    <div>
                        <h2>Form Input IFUTS</h2>
                        <small>Isi data sesuai kebutuhan ticketing karyawan</small>
                    </div>
                </div>

                <div class="aa-form-body">
                    <div class="aa-form-grid">
                        <div class="aa-field">
                            <label for="ifutsCategory">Kategori *</label>
                            <select
                                id="ifutsCategory"
                                name="kategori"
                                class="aa-select"
                                required
                            >
                                <option value="">Pilih kategori</option>
                                <option>Staff</option>
                                <option>Non Staff</option>
                                <option>Onsite</option>
                                <option>Offsite</option>
                            </select>
                        </div>

                        <div class="aa-field">
                            <label for="ifutsNik">NIK *</label>
                            <input
                                type="text"
                                id="ifutsNik"
                                name="nik"
                                class="aa-input"
                                required
                            >
                        </div>

                        <div class="aa-field span-2">
                            <label for="ifutsName">Nama *</label>
                            <input
                                type="text"
                                id="ifutsName"
                                name="nama"
                                class="aa-input"
                                required
                            >
                        </div>

                        <div class="aa-field">
                            <label for="ifutsDepartment">Departemen *</label>
                            <input
                                type="text"
                                id="ifutsDepartment"
                                name="departemen"
                                class="aa-input"
                                required
                            >
                        </div>

                        <div class="aa-field">
                            <label for="ifutsPoh">POH (Kolom N) *</label>
                            <input
                                type="text"
                                id="ifutsPoh"
                                name="poh"
                                class="aa-input"
                                required
                            >
                        </div>

                        <div class="aa-field">
                            <label for="ifutsPhone">No. HP Aktif *</label>
                            <input
                                type="tel"
                                id="ifutsPhone"
                                name="no_hp_aktif"
                                class="aa-input"
                                required
                            >
                        </div>

                        <div class="aa-field">
                            <label for="ifutsKtp">NIK KTP *</label>
                            <input
                                type="text"
                                id="ifutsKtp"
                                name="nik_ktp"
                                class="aa-input"
                                required
                            >
                        </div>

                        <div class="aa-field">
                            <label for="ifutsBirth">Tanggal Lahir *</label>
                            <input
                                type="date"
                                id="ifutsBirth"
                                name="tgl_lahir"
                                class="aa-input"
                                required
                            >
                        </div>

                        <div class="aa-field">
                            <label for="ifutsOutDate">Tanggal Out *</label>
                            <input
                                type="date"
                                id="ifutsOutDate"
                                name="tgl_out"
                                class="aa-input"
                                required
                            >
                        </div>

                        <div class="aa-field span-2">
                            <label for="ifutsOutRoute">Rute Keberangkatan *</label>
                            <input
                                type="text"
                                id="ifutsOutRoute"
                                name="rute_out"
                                class="aa-input"
                                placeholder="Contoh: PLM-JOG"
                                required
                            >
                        </div>

                        <div class="aa-field span-2">
                            <label for="ifutsTicketInfo">Keterangan Tiket</label>
                            <input
                                type="text"
                                id="ifutsTicketInfo"
                                name="ket_tiket"
                                class="aa-input"
                                placeholder="Contoh: Pulang, onsite, tambahan, perubahan rute"
                            >
                        </div>

                        <div class="aa-field">
                            <label for="ifutsInLocation">Lokasi In *</label>
                            <input
                                type="text"
                                id="ifutsInLocation"
                                name="lokasi_in"
                                class="aa-input"
                                required
                            >
                        </div>

                        <div class="aa-field">
                            <label for="ifutsInDate">Tanggal In *</label>
                            <input
                                type="date"
                                id="ifutsInDate"
                                name="tgl_in"
                                class="aa-input"
                                required
                            >
                        </div>

                        <div class="aa-field span-2">
                            <label for="ifutsInRoute">Rute Kedatangan *</label>
                            <input
                                type="text"
                                id="ifutsInRoute"
                                name="rute_in"
                                class="aa-input"
                                placeholder="Contoh: JOG-PLM"
                                required
                            >
                        </div>

                        <div class="aa-field span-4">
                            <label for="ifutsNote">Note</label>
                            <textarea
                                id="ifutsNote"
                                name="note"
                                class="aa-textarea"
                                placeholder="Catatan tambahan..."
                            ></textarea>
                        </div>
                    </div>

                    <div class="aa-form-actions">
                        <button
                            type="reset"
                            class="aa-button secondary"
                        >
                            Reset
                        </button>

                        <button
                            type="submit"
                            class="aa-button"
                        >
                            Simpan Data IFUTS
                        </button>
                    </div>
                </div>
            </form>
        </section>

        {{-- BERITA ACARA --}}
        <section class="aa-view" id="aa-berita-acara">
            <div class="aa-page-title">
                <div>
                    <h1>Berita Acara Pemesanan Tiket</h1>
                    <p>
                        Halaman ini hanya menyediakan download template Word.
                    </p>
                </div>
            </div>

            <article class="aa-download-card">
                <div class="aa-download-illustration">
                    <img
                        src="{{ asset('assets/images/berita acara word.png') }}"
                        alt="Logo dokumen Berita Acara Word"
                    >
                </div>

                <div>
                    <h2>Template Berita Acara Pemesanan Tiket</h2>
                    <p>
                        Download file Word, isi data karyawan, permintaan tiket,
                        alasan, serta persetujuan sesuai proses perusahaan.
                    </p>
                </div>

                <a
                    href="{{ asset('downloads/BA_PEMESANAN_TIKET.docx') }}"
                    class="aa-button success"
                    download
                >
                    ⬇ Download Word
                </a>
            </article>

            <div class="aa-note-box">
                File tidak ditampilkan atau diedit di website. Pengguna cukup
                mengunduh template Word, mengisinya, lalu memproses dokumen
                sesuai alur persetujuan yang berlaku.
            </div>
        </section>

        {{-- RKB SUMMARY --}}
        <section class="aa-view" id="aa-rkb-summary">
            <div class="aa-page-title">
                <div>
                    <h1>Dashboard Rencana Kebutuhan Barang</h1>
                    <p>
                        Monitoring pengajuan dan proses pemenuhan RKB.
                    </p>
                </div>

                <button
                    type="button"
                    class="aa-button"
                    data-open-view="aa-rkb-input"
                >
                    + Input Monitoring
                </button>
            </div>

            <div class="aa-rkb-status-grid">
                <article class="aa-rkb-status-card">
                    <h3>Waiting Approval</h3>
                    <p>Persetujuan Section Head dan Site Manager</p>

                    <div class="aa-rkb-count-row">
                        <span>
                            <strong>{{ data_get($rkbStats, 'waiting_approval_sh', 0) }}</strong>
                            <small>SH</small>
                        </span>

                        <span>
                            <strong>{{ data_get($rkbStats, 'waiting_approval_sm', 0) }}</strong>
                            <small>SM</small>
                        </span>
                    </div>
                </article>

                <article class="aa-rkb-status-card">
                    <h3>On Process</h3>
                    <p>Proses item dari RKB hingga logistik</p>

                    <div class="aa-rkb-count-row">
                        <span>
                            <strong>{{ data_get($rkbStats, 'waiting_process', 0) }}</strong>
                            <small>Progress</small>
                        </span>

                        <span>
                            <strong>{{ data_get($rkbStats, 'ready', 0) }}</strong>
                            <small>Ready</small>
                        </span>
                    </div>
                </article>

                <article class="aa-rkb-status-card">
                    <h3>Finish / Complete</h3>
                    <p>Proses item ready dari logistik</p>

                    <div class="aa-rkb-count-row">
                        <span>
                            <strong>{{ data_get($rkbStats, 'reject', 0) }}</strong>
                            <small>Reject</small>
                        </span>

                        <span>
                            <strong>{{ data_get($rkbStats, 'finish', 0) }}</strong>
                            <small>Finish</small>
                        </span>
                    </div>
                </article>
            </div>

            <div class="aa-chart-grid">
                <article class="aa-chart-card">
                    <div class="aa-card-header">
                        <div>
                            <h2>RKB Berdasarkan Status</h2>
                            <small>Komposisi proses pengajuan</small>
                        </div>
                    </div>

                    <div class="aa-chart-body aa-donut-layout">
                        <div class="aa-donut">
                            <div class="aa-donut-center">
                                <strong>13</strong>
                                <span>Total</span>
                            </div>
                        </div>

                        <div class="aa-legend">
                            <div class="aa-legend-item">
                                <span class="aa-legend-dot" style="--legend-color:#1478e8;"></span>
                                <span>Waiting Process</span>
                                <strong>2</strong>
                            </div>

                            <div class="aa-legend-item">
                                <span class="aa-legend-dot" style="--legend-color:#86a5b2;"></span>
                                <span>Waiting Supply</span>
                                <strong>7</strong>
                            </div>

                            <div class="aa-legend-item">
                                <span class="aa-legend-dot" style="--legend-color:#dfab79;"></span>
                                <span>Waiting Approval SH</span>
                                <strong>4</strong>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="aa-chart-card">
                    <div class="aa-card-header">
                        <div>
                            <h2>Leadtime Proses RKB</h2>
                            <small>Rata-rata jam per tahap</small>
                        </div>
                    </div>

                    <div class="aa-chart-body">
                        <div class="aa-bars">
                            @foreach ([
                                ['label' => 'APPV SH', 'value' => 55, 'color' => '#dfab79'],
                                ['label' => 'APPV SM', 'value' => 70, 'color' => '#ffc312'],
                                ['label' => 'LOGISTIC', 'value' => 74, 'color' => '#1478e8'],
                                ['label' => 'READY', 'value' => 8, 'color' => '#20b26b'],
                                ['label' => 'FINISH', 'value' => 5, 'color' => '#7c3aed'],
                            ] as $bar)
                                <div
                                    class="aa-bar"
                                    style="
                                        height: {{ max(8, $bar['value']) }}%;
                                        --bar-color: {{ $bar['color'] }};
                                    "
                                >
                                    <span class="aa-bar-value">
                                        {{ $bar['value'] }}
                                    </span>

                                    <span class="aa-bar-label">
                                        {{ $bar['label'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </article>
            </div>

            <article class="aa-table-card">
                <div class="aa-card-header">
                    <div>
                        <h2>Daftar Dokumen Pengajuan RKB</h2>
                        <small>{{ $rkbRows->count() }} data contoh</small>
                    </div>
                </div>

                <div class="aa-table-wrap">
                    <table class="aa-table">
                        <thead>
                            <tr>
                                <th>Durasi (Jam)</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Pemohon</th>
                                <th>Posisi</th>
                                <th>No. RKB</th>
                                <th>Total Item</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($rkbRows as $row)
                                @php
                                    $rkbStatus = data_get($row, 'status', '-');
                                    $rkbClass = match ($rkbStatus) {
                                        'Waiting Approval SH' => 'orange',
                                        'Waiting Supply' => 'cyan',
                                        'Finish' => 'green',
                                        'Reject' => 'red',
                                        default => '',
                                    };
                                @endphp

                                <tr>
                                    <td>{{ data_get($row, 'duration', 0) }}</td>
                                    <td>
                                        <span class="aa-badge {{ $rkbClass }}">
                                            {{ $rkbStatus }}
                                        </span>
                                    </td>
                                    <td>{{ data_get($row, 'date', '-') }}</td>
                                    <td>{{ data_get($row, 'requester', '-') }}</td>
                                    <td>{{ data_get($row, 'position', '-') }}</td>
                                    <td>{{ data_get($row, 'rkb_no', '-') }}</td>
                                    <td>{{ data_get($row, 'quantity', 0) }}</td>
                                    <td>
                                        <button type="button" class="aa-table-action">
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

        {{-- RKB INPUT --}}
        <section class="aa-view" id="aa-rkb-input">
            <div class="aa-page-title">
                <div>
                    <h1>Input Monitoring RKB</h1>
                    <p>
                        Input item dan evidence untuk pemantauan RKB.
                    </p>
                </div>
            </div>

            <form
                class="aa-form-card"
                id="aaRkbForm"
                data-endpoint="{{ $rkbEndpoint }}"
                enctype="multipart/form-data"
            >
                <div class="aa-card-header">
                    <div>
                        <h2>Form Monitoring RKB</h2>
                        <small>Nama barang, nomor RKB, kuantitas, penerima, dan evidence</small>
                    </div>
                </div>

                <div class="aa-form-body">
                    <div class="aa-form-grid">
                        <div class="aa-field span-2">
                            <label for="rkbItemName">Nama Barang *</label>
                            <input
                                type="text"
                                id="rkbItemName"
                                name="nama_barang"
                                class="aa-input"
                                required
                            >
                        </div>

                        <div class="aa-field">
                            <label for="rkbNumber">No. RKB *</label>
                            <input
                                type="text"
                                id="rkbNumber"
                                name="no_rkb"
                                class="aa-input"
                                required
                            >
                        </div>

                        <div class="aa-field">
                            <label for="rkbQuantity">Kuantitas *</label>
                            <input
                                type="number"
                                id="rkbQuantity"
                                name="kuantitas"
                                class="aa-input"
                                min="1"
                                required
                            >
                        </div>

                        <div class="aa-field span-2">
                            <label for="rkbReceiver">Penerima *</label>
                            <input
                                type="text"
                                id="rkbReceiver"
                                name="penerima"
                                class="aa-input"
                                required
                            >
                        </div>

                        <div class="aa-field span-2">
                            <label for="rkbEvidence">Evidence *</label>
                            <div class="aa-file-box">
                                <input
                                    type="file"
                                    id="rkbEvidence"
                                    name="evidence"
                                    accept=".jpg,.jpeg,.png,.pdf"
                                    required
                                >
                                <small>
                                    JPG, PNG, atau PDF maksimal 5 MB
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="aa-form-actions">
                        <button
                            type="reset"
                            class="aa-button secondary"
                        >
                            Reset
                        </button>

                        <button
                            type="submit"
                            class="aa-button"
                        >
                            Simpan Monitoring RKB
                        </button>
                    </div>
                </div>
            </form>
        </section>

        {{-- STOCK SUMMARY --}}
        <section class="aa-view" id="aa-stock-summary">
            <div class="aa-page-title">
                <div>
                    <h1>Ringkasan Stock Opname Gudang</h1>
                    <p>
                        Monitoring stok barang dan kebutuhan pembaruan.
                    </p>
                </div>

                <button
                    type="button"
                    class="aa-button"
                    data-open-view="aa-stock-update"
                >
                    + Update Stock
                </button>
            </div>

            <div class="aa-stock-grid">
                <article class="aa-stock-card">
                    <strong>{{ data_get($stockStats, 'total_item', 0) }}</strong>
                    <small>Total Item</small>
                </article>

                <article class="aa-stock-card">
                    <strong style="color:#f59e0b;">
                        {{ data_get($stockStats, 'low_stock', 0) }}
                    </strong>
                    <small>Stok Rendah</small>
                </article>

                <article class="aa-stock-card">
                    <strong style="color:#d71920;">
                        {{ data_get($stockStats, 'out_of_stock', 0) }}
                    </strong>
                    <small>Stok Habis</small>
                </article>

                <article class="aa-stock-card">
                    <strong style="color:#20b26b;">
                        {{ data_get($stockStats, 'updated_today', 0) }}
                    </strong>
                    <small>Update Hari Ini</small>
                </article>
            </div>

            <article class="aa-table-card">
                <div class="aa-card-header">
                    <div>
                        <h2>Daftar Stock Barang</h2>
                        <small>{{ $stockRows->count() }} data contoh</small>
                    </div>
                </div>

                <div class="aa-table-wrap">
                    <table class="aa-table">
                        <thead>
                            <tr>
                                <th>Nama Barang</th>
                                <th>Part Number</th>
                                <th>Lokasi</th>
                                <th>Stock</th>
                                <th>Minimum</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($stockRows as $row)
                                @php
                                    $stockStatus = data_get($row, 'status', '-');
                                    $stockClass = match ($stockStatus) {
                                        'Aman' => 'green',
                                        'Stok Rendah' => 'orange',
                                        'Habis' => 'red',
                                        default => 'gray',
                                    };
                                @endphp

                                <tr>
                                    <td>{{ data_get($row, 'item', '-') }}</td>
                                    <td>{{ data_get($row, 'part_number', '-') }}</td>
                                    <td>{{ data_get($row, 'location', '-') }}</td>
                                    <td>{{ data_get($row, 'stock', 0) }}</td>
                                    <td>{{ data_get($row, 'minimum', 0) }}</td>
                                    <td>
                                        <span class="aa-badge {{ $stockClass }}">
                                            {{ $stockStatus }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

        {{-- STOCK UPDATE --}}
        <section class="aa-view" id="aa-stock-update">
            <div class="aa-page-title">
                <div>
                    <h1>Update Stock Barang</h1>
                    <p>
                        Perbarui jumlah stok dan evidence stock opname.
                    </p>
                </div>
            </div>

            <form
                class="aa-form-card"
                id="aaStockForm"
                data-endpoint="{{ $stockEndpoint }}"
                enctype="multipart/form-data"
            >
                <div class="aa-card-header">
                    <div>
                        <h2>Form Update Stock</h2>
                        <small>Data dapat dihubungkan ke backend gudang</small>
                    </div>
                </div>

                <div class="aa-form-body">
                    <div class="aa-form-grid">
                        <div class="aa-field span-2">
                            <label for="stockItem">Nama Barang *</label>
                            <input
                                type="text"
                                id="stockItem"
                                name="nama_barang"
                                class="aa-input"
                                required
                            >
                        </div>

                        <div class="aa-field">
                            <label for="stockPartNumber">Part Number</label>
                            <input
                                type="text"
                                id="stockPartNumber"
                                name="part_number"
                                class="aa-input"
                            >
                        </div>

                        <div class="aa-field">
                            <label for="stockLocation">Lokasi *</label>
                            <input
                                type="text"
                                id="stockLocation"
                                name="lokasi"
                                class="aa-input"
                                required
                            >
                        </div>

                        <div class="aa-field">
                            <label for="stockPrevious">Stock Sebelumnya</label>
                            <input
                                type="number"
                                id="stockPrevious"
                                name="stock_sebelumnya"
                                class="aa-input"
                                min="0"
                            >
                        </div>

                        <div class="aa-field">
                            <label for="stockCurrent">Stock Terbaru *</label>
                            <input
                                type="number"
                                id="stockCurrent"
                                name="stock_terbaru"
                                class="aa-input"
                                min="0"
                                required
                            >
                        </div>

                        <div class="aa-field span-2">
                            <label for="stockEvidence">Evidence</label>
                            <div class="aa-file-box">
                                <input
                                    type="file"
                                    id="stockEvidence"
                                    name="evidence"
                                    accept=".jpg,.jpeg,.png,.pdf"
                                >
                                <small>Foto atau PDF stock opname</small>
                            </div>
                        </div>

                        <div class="aa-field span-4">
                            <label for="stockNote">Keterangan</label>
                            <textarea
                                id="stockNote"
                                name="keterangan"
                                class="aa-textarea"
                            ></textarea>
                        </div>
                    </div>

                    <div class="aa-form-actions">
                        <button
                            type="reset"
                            class="aa-button secondary"
                        >
                            Reset
                        </button>

                        <button
                            type="submit"
                            class="aa-button"
                        >
                            Simpan Update Stock
                        </button>
                    </div>
                </div>
            </form>
        </section>

        {{-- E-ARSIP --}}
        <section class="aa-view" id="aa-archive">
            <div class="aa-page-title">
                <div>
                    <h1>E-Arsip</h1>
                    <p>
                        Akses folder arsip dokumen melalui Google Drive.
                    </p>
                </div>
            </div>

            <article class="aa-link-card">
                <div class="aa-link-illustration">
                    <img
                        src="{{ asset('assets/images/google drive.png') }}"
                        alt="Logo Google Drive"
                    >
                </div>

                <div>
                    <h2>Folder E-Arsip Google Drive</h2>
                    <p>
                        Klik tombol untuk membuka folder arsip resmi.
                        Link akan dibuka pada tab browser baru.
                    </p>
                </div>

                <a
                    href="https://drive.google.com/drive/folders/1X01OjcwoWZkItRwpK8J8nZ1I5We2xECv?hl=ID"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="aa-button success"
                >
                    Buka Google Drive
                </a>
            </article>
        </section>
    </main>

    <footer class="aa-footer">
        &copy; COPYRIGHT SYNRGYPRO {{ date('Y') }}. V1.0
    </footer>
</div>

<div class="aa-toast" id="aaToast"></div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const page =
        document.getElementById('adminAllPage');

    const sidebarToggle =
        document.getElementById('aaSidebarToggle');

    const menuGroups =
        document.querySelectorAll('.aa-menu-group');

    const viewButtons =
        document.querySelectorAll('[data-aa-view]');

    const openViewButtons =
        document.querySelectorAll('[data-open-view]');

    const views =
        document.querySelectorAll('.aa-view');

    const ifutsForm =
        document.getElementById('aaIfutsForm');

    const rkbForm =
        document.getElementById('aaRkbForm');

    const stockForm =
        document.getElementById('aaStockForm');

    const loadingLayer =
        document.getElementById('aaLoadingLayer');

    const toast =
        document.getElementById('aaToast');

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    function showToast(message) {
        if (!toast) {
            return;
        }

        toast.textContent = message;
        toast.classList.add('is-visible');

        window.setTimeout(function () {
            toast.classList.remove('is-visible');
        }, 2800);
    }

    function validateFile(file, maxMb) {
        if (!file) {
            return true;
        }

        const allowedTypes = [
            'image/jpeg',
            'image/png',
            'application/pdf'
        ];

        if (!allowedTypes.includes(file.type)) {
            showToast('Evidence harus JPG, PNG, atau PDF.');
            return false;
        }

        const maxBytes =
            (maxMb || 5) * 1024 * 1024;

        if (file.size > maxBytes) {
            showToast(
                'Ukuran evidence maksimal ' +
                (maxMb || 5) +
                ' MB.'
            );

            return false;
        }

        return true;
    }

    async function submitForm(
        form,
        endpoint,
        storageKey,
        successMessage
    ) {
        if (!form.reportValidity()) {
            return;
        }

        const formData =
            new FormData(form);

        if (!endpoint) {
            const stored = JSON.parse(
                localStorage.getItem(storageKey) || '[]'
            );

            const data = {};

            formData.forEach(function (value, key) {
                data[key] =
                    value instanceof File
                        ? value.name
                        : value;
            });

            stored.push({
                ...data,
                created_at: new Date().toISOString()
            });

            localStorage.setItem(
                storageKey,
                JSON.stringify(stored)
            );

            form.reset();
            showToast(successMessage + ' tersimpan dalam mode demo.');
            return;
        }

        const csrfToken =
            document.querySelector(
                'meta[name="csrf-token"]'
            )?.getAttribute('content');

        window.setAdminAllLoading(true);

        try {
            const response =
                await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        ...(csrfToken
                            ? { 'X-CSRF-TOKEN': csrfToken }
                            : {})
                    },
                    body: formData
                });

            if (!response.ok) {
                throw new Error('Gagal menyimpan data ke backend.');
            }

            form.reset();
            showToast(successMessage + ' berhasil disimpan.');
        } catch (error) {
            console.error(error);
            showToast(error.message);
        } finally {
            window.setAdminAllLoading(false);
        }
    }

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

    menuGroups.forEach(function (group) {
        const toggle =
            group.querySelector('.aa-menu-toggle');

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
    | VIEW NAVIGATION
    |--------------------------------------------------------------------------
    */

    function openView(targetId) {
        const target =
            document.getElementById(targetId);

        if (!target) {
            return;
        }

        views.forEach(function (view) {
            view.classList.toggle(
                'active',
                view.id === targetId
            );
        });

        viewButtons.forEach(function (button) {
            button.classList.toggle(
                'active',
                button.dataset.aaView === targetId
            );
        });

        const activeButton =
            document.querySelector(
                '[data-aa-view="' +
                CSS.escape(targetId) +
                '"]'
            );

        const parentGroup =
            activeButton?.closest('.aa-menu-group');

        if (parentGroup) {
            parentGroup.classList.add('is-open');

            parentGroup
                .querySelector('.aa-menu-toggle')
                ?.setAttribute('aria-expanded', 'true');
        }

        document.querySelector('.aa-content')
            ?.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
    }

    viewButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            openView(button.dataset.aaView);
        });
    });

    openViewButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            openView(button.dataset.openView);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | FORM IFUTS
    |--------------------------------------------------------------------------
    */

    ifutsForm?.addEventListener(
        'submit',
        function (event) {
            event.preventDefault();

            submitForm(
                ifutsForm,
                ifutsForm.dataset.endpoint,
                'synrgypro_admin_ifuts',
                'Data IFUTS'
            );
        }
    );

    /*
    |--------------------------------------------------------------------------
    | FORM RKB
    |--------------------------------------------------------------------------
    */

    rkbForm?.addEventListener(
        'submit',
        function (event) {
            event.preventDefault();

            const evidence =
                document.getElementById('rkbEvidence')
                    ?.files?.[0];

            if (!validateFile(evidence, 5)) {
                return;
            }

            submitForm(
                rkbForm,
                rkbForm.dataset.endpoint,
                'synrgypro_admin_rkb',
                'Monitoring RKB'
            );
        }
    );

    /*
    |--------------------------------------------------------------------------
    | FORM STOCK
    |--------------------------------------------------------------------------
    */

    stockForm?.addEventListener(
        'submit',
        function (event) {
            event.preventDefault();

            const evidence =
                document.getElementById('stockEvidence')
                    ?.files?.[0];

            if (!validateFile(evidence, 5)) {
                return;
            }

            submitForm(
                stockForm,
                stockForm.dataset.endpoint,
                'synrgypro_admin_stock',
                'Update stock'
            );
        }
    );

    /*
    |--------------------------------------------------------------------------
    | LOADING API
    |--------------------------------------------------------------------------
    */

    window.setAdminAllLoading =
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
});
</script>
@endpush