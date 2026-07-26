@extends('layouts.app')

@section('title', 'People Development — SYNRGYPRO')
@section('body-class', 'syn-people-development-page')

@push('styles')
<style>
    :root {
        --pd-sidebar: 225px;
        --pd-sidebar-collapsed: 72px;
        --pd-header: 64px;
        --pd-footer: 28px;

        --pd-bg: #f3f5f7;
        --pd-surface: #ffffff;
        --pd-surface-soft: #f8fafc;
        --pd-border: #dce2e8;
        --pd-text: #1f2937;
        --pd-muted: #6b7280;

        --pd-black: #121212;
        --pd-red: #d71920;
        --pd-orange: #e06426;
        --pd-blue: #1478e8;
        --pd-green: #20b26b;
        --pd-cyan: #11b8a6;
        --pd-purple: #7c3aed;
        --pd-yellow: #f59e0b;

        --pd-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
    }

    * {
        box-sizing: border-box;
    }

    body.syn-people-development-page {
        margin: 0;
        overflow: hidden;
        color: var(--pd-text);
        background: var(--pd-bg);
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

    .pd-page {
        display: grid;
        width: 100%;
        height: 100vh;
        grid-template-columns: var(--pd-sidebar) minmax(0, 1fr);
        grid-template-rows:
            var(--pd-header)
            minmax(0, 1fr)
            var(--pd-footer);
        overflow: hidden;
        background: var(--pd-bg);
        transition: grid-template-columns .24s ease;
    }

    /* =====================================================
       SIDEBAR
       ===================================================== */

    .pd-sidebar {
        display: flex;
        grid-row: 1 / 4;
        min-width: 0;
        flex-direction: column;
        border-right: 1px solid #c7ccd2;
        background: linear-gradient(180deg, #f1f1f1 0%, #dddddd 100%);
    }

    .pd-sidebar-head {
        display: grid;
        min-height: var(--pd-header);
        grid-template-columns: minmax(0, 1fr) 52px;
        border-bottom: 1px solid #606060;
        background: var(--pd-black);
    }

    .pd-sidebar-logo {
        display: grid;
        place-items: center;
        min-width: 0;
        padding: 5px;
        overflow: hidden;
    }

    .pd-sidebar-logo img {
        display: block;
        width: 76px;
        height: 52px;
        object-fit: contain;
    }

    .pd-sidebar-toggle {
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

    .pd-navigation {
        flex: 1;
        padding: 10px 0;
        overflow-x: hidden;
        overflow-y: auto;
    }

    .pd-menu-link,
    .pd-menu-toggle {
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

    .pd-menu-link:hover,
    .pd-menu-toggle:hover,
    .pd-menu-link.active,
    .pd-menu-group.is-open > .pd-menu-toggle {
        background: rgba(255, 255, 255, .78);
    }

    .pd-menu-link.active,
    .pd-submenu-button.active {
        border-left-color: var(--pd-red);
    }

    .pd-menu-icon {
        display: grid;
        width: 23px;
        height: 23px;
        flex: 0 0 23px;
        place-items: center;
        color: #222;
        font-size: 17px;
    }

    .pd-menu-icon img {
        width: 21px;
        height: 21px;
        opacity: .86;
        object-fit: contain;
        filter: grayscale(1) contrast(1.12);
    }

    .pd-menu-label {
        min-width: 0;
        flex: 1;
    }

    .pd-menu-arrow {
        display: inline-grid;
        width: 18px;
        height: 18px;
        place-items: center;
        margin-left: auto;
        font-size: 18px;
        transition: transform .2s ease;
    }

    .pd-menu-group.is-open .pd-menu-arrow {
        transform: rotate(90deg);
    }

    .pd-submenu {
        display: grid;
        grid-template-rows: 0fr;
        opacity: 0;
        transition:
            grid-template-rows .22s ease,
            opacity .18s ease;
    }

    .pd-menu-group.is-open .pd-submenu {
        grid-template-rows: 1fr;
        opacity: 1;
    }

    .pd-submenu-inner {
        overflow: hidden;
    }

    .pd-submenu-button {
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
            background .18s ease,
            border-color .18s ease;
    }

    .pd-submenu-button::before {
        position: absolute;
        left: 34px;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #444;
        content: "";
    }

    .pd-submenu-button:hover,
    .pd-submenu-button.active {
        color: #111;
        background: rgba(255, 255, 255, .9);
    }

    .pd-sidebar-bottom {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
        padding: 14px 12px 18px;
    }

    .pd-bottom-link {
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

    .pd-bottom-link:hover {
        background: rgba(255, 255, 255, .75);
    }

    .pd-bottom-link.help span:first-child {
        color: var(--pd-red);
    }

    /* =====================================================
       HEADER
       ===================================================== */

    .pd-header {
        display: grid;
        grid-column: 2;
        grid-row: 1;
        min-width: 0;
        grid-template-columns: minmax(0, 1fr) auto;
        border-bottom: 1px solid var(--pd-border);
        background: #fff;
    }

    .pd-header-brand {
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

    .pd-header-brand img {
        width: 125px;
        max-height: 45px;
        object-fit: contain;
    }

    .pd-header-actions {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 0 11px;
        background: #fff;
    }

    .pd-header-button {
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

    .pd-header-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 16px rgba(0, 0, 0, .14);
    }

    .pd-header-button img {
        width: 72%;
        height: 72%;
        object-fit: contain;
    }

    .pd-profile-button img,
    .pd-logout-button img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    .pd-logout-form {
        display: flex;
        margin: 0;
    }

    .pd-logout-button {
        border-color: transparent;
    }

    /* =====================================================
       CONTENT & GENERIC COMPONENTS
       ===================================================== */

    .pd-content {
        position: relative;
        grid-column: 2;
        grid-row: 2;
        min-width: 0;
        min-height: 0;
        padding: 14px;
        overflow-x: hidden;
        overflow-y: auto;
        background: var(--pd-bg);
    }

    .pd-view {
        display: none;
        min-height: 100%;
    }

    .pd-view.active {
        display: block;
    }

    .pd-page-title {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 12px;
    }

    .pd-page-title h1 {
        margin: 0;
        color: #111827;
        font-size: 21px;
        line-height: 1.15;
    }

    .pd-page-title p {
        margin: 4px 0 0;
        color: var(--pd-muted);
        font-size: 12px;
    }

    .pd-panel,
    .pd-kpi-card,
    .pd-calendar-card,
    .pd-upcoming-card,
    .pd-table-card,
    .pd-form-card,
    .pd-matrix-card {
        border: 1px solid var(--pd-border);
        background: var(--pd-surface);
        box-shadow: var(--pd-shadow);
    }

    .pd-panel,
    .pd-calendar-card,
    .pd-upcoming-card,
    .pd-table-card,
    .pd-form-card,
    .pd-matrix-card {
        border-radius: 13px;
        overflow: hidden;
    }

    .pd-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 15px;
        border-bottom: 1px solid var(--pd-border);
    }

    .pd-card-header h2 {
        margin: 0;
        color: #111827;
        font-size: 14px;
    }

    .pd-card-header small {
        color: var(--pd-muted);
        font-size: 11px;
    }

    .pd-button {
        display: inline-flex;
        min-height: 39px;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 0 15px;
        border: 0;
        border-radius: 9px;
        color: #fff;
        background: var(--pd-blue);
        font-size: 12px;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
    }

    .pd-button.secondary {
        border: 1px solid #ccd3da;
        color: #374151;
        background: #fff;
    }

    .pd-button.danger {
        background: var(--pd-red);
    }

    .pd-button.success {
        background: var(--pd-green);
    }

    .pd-field {
        display: grid;
        gap: 6px;
    }

    .pd-field label {
        color: #374151;
        font-size: 12px;
        font-weight: 800;
    }

    .pd-input,
    .pd-select,
    .pd-textarea {
        width: 100%;
        min-width: 0;
        border: 1px solid #ccd3da;
        border-radius: 9px;
        outline: none;
        color: var(--pd-text);
        background: #fff;
        font-size: 13px;
    }

    .pd-input,
    .pd-select {
        height: 39px;
        padding: 0 12px;
    }

    .pd-textarea {
        min-height: 92px;
        padding: 11px 12px;
        resize: vertical;
    }

    .pd-input:focus,
    .pd-select:focus,
    .pd-textarea:focus {
        border-color: var(--pd-blue);
        box-shadow: 0 0 0 3px rgba(20, 120, 232, .12);
    }

    .pd-badge {
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

    .pd-badge.green {
        color: #087443;
        background: #e4f8ee;
    }

    .pd-badge.orange {
        color: #a85b00;
        background: #fff1dc;
    }

    .pd-badge.red {
        color: #c51f2b;
        background: #ffe8eb;
    }

    .pd-badge.gray {
        color: #4b5563;
        background: #eef1f4;
    }

    /* =====================================================
       DASHBOARD KPI
       ===================================================== */

    .pd-kpi-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 12px;
    }

    .pd-kpi-card {
        display: grid;
        min-height: 95px;
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

    .pd-kpi-card:hover {
        border-color: #adb7c1;
        transform: translateY(-2px);
    }

    .pd-kpi-icon {
        display: grid;
        width: 48px;
        height: 48px;
        place-items: center;
        border-radius: 13px;
        color: #fff;
        background: #30363d;
        font-size: 21px;
    }

    .pd-kpi-card:nth-child(2) .pd-kpi-icon {
        background: var(--pd-blue);
    }

    .pd-kpi-card:nth-child(3) .pd-kpi-icon {
        background: var(--pd-purple);
    }

    .pd-kpi-card:nth-child(4) .pd-kpi-icon {
        background: var(--pd-green);
    }

    .pd-kpi-card:nth-child(5) .pd-kpi-icon {
        background: var(--pd-yellow);
    }

    .pd-kpi-card small {
        display: block;
        margin-bottom: 5px;
        color: var(--pd-muted);
        font-size: 11px;
        font-weight: 800;
    }

    .pd-kpi-value {
        display: block;
        color: #111827;
        font-size: 26px;
        font-weight: 900;
        line-height: 1;
    }

    .pd-dashboard-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.55fr) minmax(300px, .72fr);
        gap: 12px;
        margin-bottom: 12px;
    }

    /* =====================================================
       CALENDAR
       ===================================================== */

    .pd-calendar-toolbar {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .pd-calendar-nav {
        display: grid;
        width: 32px;
        height: 32px;
        place-items: center;
        padding: 0;
        border: 1px solid #ccd3da;
        border-radius: 8px;
        color: #374151;
        background: #fff;
        font-size: 18px;
    }

    .pd-calendar-month {
        min-width: 145px;
        color: #111827;
        font-size: 13px;
        font-weight: 900;
        text-align: center;
    }

    .pd-calendar {
        padding: 12px;
    }

    .pd-calendar-weekdays,
    .pd-calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 5px;
    }

    .pd-calendar-weekdays {
        margin-bottom: 5px;
    }

    .pd-calendar-weekdays span {
        padding: 6px 2px;
        color: var(--pd-muted);
        font-size: 10px;
        font-weight: 900;
        text-align: center;
        text-transform: uppercase;
    }

    .pd-calendar-day {
        position: relative;
        min-height: 76px;
        padding: 7px;
        overflow: hidden;
        border: 1px solid #e1e6eb;
        border-radius: 8px;
        background: #fff;
    }

    .pd-calendar-day.is-outside {
        background: #f7f8fa;
        opacity: .52;
    }

    .pd-calendar-day.is-today {
        border-color: var(--pd-blue);
        box-shadow: inset 0 0 0 1px var(--pd-blue);
    }

    .pd-day-number {
        display: block;
        margin-bottom: 4px;
        color: #374151;
        font-size: 10px;
        font-weight: 900;
    }

    .pd-calendar-event {
        display: block;
        width: 100%;
        margin-top: 3px;
        padding: 4px 5px;
        overflow: hidden;
        border: 0;
        border-left: 3px solid var(--event-color, var(--pd-blue));
        border-radius: 5px;
        color: #263445;
        background: #edf4ff;
        font-size: 8px;
        font-weight: 800;
        line-height: 1.25;
        text-align: left;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .pd-calendar-event[data-category="Versatility"] {
        --event-color: var(--pd-purple);
        background: #f1eaff;
    }

    .pd-calendar-event[data-category="Safety"] {
        --event-color: var(--pd-red);
        background: #ffecef;
    }

    .pd-calendar-event[data-category="Leadership"] {
        --event-color: var(--pd-orange);
        background: #fff0e7;
    }

    .pd-calendar-event[data-category="Technical Skill"] {
        --event-color: var(--pd-blue);
    }

    /* =====================================================
       UPCOMING
       ===================================================== */

    .pd-upcoming-list {
        display: grid;
        max-height: 450px;
        gap: 8px;
        padding: 12px;
        overflow-y: auto;
    }

    .pd-upcoming-item {
        display: grid;
        grid-template-columns: 48px minmax(0, 1fr);
        gap: 10px;
        padding: 10px;
        border: 1px solid #e1e6eb;
        border-radius: 10px;
        background: #fff;
        text-align: left;
        transition:
            border-color .18s ease,
            transform .18s ease;
    }

    .pd-upcoming-item:hover {
        border-color: #aeb7c1;
        transform: translateY(-1px);
    }

    .pd-date-box {
        display: grid;
        min-height: 48px;
        place-items: center;
        border-radius: 9px;
        color: #fff;
        background: var(--pd-blue);
        text-align: center;
    }

    .pd-date-box strong {
        display: block;
        font-size: 17px;
        line-height: 1;
    }

    .pd-date-box span {
        display: block;
        margin-top: 2px;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .pd-upcoming-info strong {
        display: block;
        color: #111827;
        font-size: 12px;
        line-height: 1.25;
    }

    .pd-upcoming-info small {
        display: block;
        margin-top: 4px;
        color: var(--pd-muted);
        font-size: 10px;
        line-height: 1.35;
    }

    /* =====================================================
       TABLES
       ===================================================== */

    .pd-table-wrap {
        max-height: 390px;
        overflow: auto;
    }

    .pd-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 12px;
    }

    .pd-table th,
    .pd-table td {
        padding: 10px 11px;
        border-bottom: 1px solid var(--pd-border);
        text-align: left;
        white-space: nowrap;
    }

    .pd-table th {
        position: sticky;
        top: 0;
        z-index: 2;
        color: #374151;
        background: #f8fafc;
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .pd-table tbody tr:hover {
        background: #f8fbff;
    }

    .pd-table-action {
        display: inline-flex;
        min-height: 30px;
        align-items: center;
        justify-content: center;
        padding: 0 10px;
        border: 0;
        border-radius: 7px;
        color: #fff;
        background: var(--pd-blue);
        font-size: 10px;
        font-weight: 900;
    }

    .pd-empty-state {
        padding: 30px 16px !important;
        color: var(--pd-muted);
        text-align: center !important;
    }

    /* =====================================================
       FORMS
       ===================================================== */

    .pd-form-body {
        padding: 16px;
    }

    .pd-form-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 13px;
    }

    .pd-form-grid .span-2 {
        grid-column: span 2;
    }

    .pd-form-grid .span-3 {
        grid-column: 1 / -1;
    }

    .pd-form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 16px;
        padding-top: 14px;
        border-top: 1px solid var(--pd-border);
    }

    /* =====================================================
       ATTENDANCE / EVALUATION
       ===================================================== */

    .pd-summary-strip {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 12px;
    }

    .pd-summary-box {
        padding: 13px;
        border: 1px solid var(--pd-border);
        border-radius: 11px;
        background: #fff;
        box-shadow: var(--pd-shadow);
        text-align: center;
    }

    .pd-summary-box strong {
        display: block;
        color: #111827;
        font-size: 22px;
    }

    .pd-summary-box small {
        display: block;
        margin-top: 4px;
        color: var(--pd-muted);
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .pd-score {
        color: var(--pd-blue);
        font-weight: 900;
    }

    .pd-score.up {
        color: var(--pd-green);
    }

    /* =====================================================
       COMPETENCY MATRIX
       ===================================================== */

    .pd-matrix-wrap {
        overflow: auto;
    }

    .pd-matrix {
        width: 100%;
        min-width: 850px;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 11px;
    }

    .pd-matrix th,
    .pd-matrix td {
        padding: 10px;
        border-right: 1px solid var(--pd-border);
        border-bottom: 1px solid var(--pd-border);
        text-align: center;
    }

    .pd-matrix th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f8fafc;
        font-size: 10px;
        text-transform: uppercase;
    }

    .pd-matrix th:first-child,
    .pd-matrix td:first-child {
        position: sticky;
        left: 0;
        z-index: 3;
        min-width: 190px;
        background: #fff;
        text-align: left;
    }

    .pd-skill-state {
        display: inline-flex;
        min-width: 74px;
        min-height: 25px;
        align-items: center;
        justify-content: center;
        padding: 0 8px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 900;
    }

    .pd-skill-state.competent {
        color: #087443;
        background: #e4f8ee;
    }

    .pd-skill-state.training {
        color: #a85b00;
        background: #fff1dc;
    }

    .pd-skill-state.not-yet {
        color: #b4232f;
        background: #ffe8eb;
    }

    /* =====================================================
       CERTIFICATES
       ===================================================== */

    .pd-certificate-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(220px, 1fr));
        gap: 12px;
    }

    .pd-certificate-card {
        padding: 15px;
        border: 1px solid var(--pd-border);
        border-radius: 12px;
        background: #fff;
        box-shadow: var(--pd-shadow);
    }

    .pd-certificate-icon {
        display: grid;
        width: 45px;
        height: 45px;
        place-items: center;
        margin-bottom: 12px;
        border-radius: 12px;
        color: #fff;
        background: var(--pd-purple);
        font-size: 20px;
    }

    .pd-certificate-card h3 {
        margin: 0 0 5px;
        font-size: 13px;
    }

    .pd-certificate-card p {
        margin: 0 0 12px;
        color: var(--pd-muted);
        font-size: 11px;
        line-height: 1.45;
    }

    /* =====================================================
       DRAWER
       ===================================================== */

    .pd-drawer-backdrop {
        position: fixed;
        inset: 0;
        z-index: 1200;
        visibility: hidden;
        opacity: 0;
        background: rgba(15, 23, 42, .45);
        pointer-events: none;
        transition:
            opacity .22s ease,
            visibility .22s ease;
    }

    .pd-drawer-backdrop.is-open {
        visibility: visible;
        opacity: 1;
        pointer-events: auto;
    }

    .pd-agenda-drawer {
        position: fixed;
        top: 0;
        right: 0;
        z-index: 1201;
        width: min(440px, 92vw);
        height: 100vh;
        padding: 22px;
        overflow-y: auto;
        background: #fff;
        box-shadow: -18px 0 44px rgba(15, 23, 42, .2);
        transform: translateX(100%);
        transition: transform .28s ease;
    }

    .pd-agenda-drawer.is-open {
        transform: translateX(0);
    }

    .pd-drawer-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 18px;
        padding-bottom: 14px;
        border-bottom: 1px solid var(--pd-border);
    }

    .pd-drawer-header h2 {
        margin: 0;
        font-size: 18px;
    }

    .pd-drawer-close {
        display: grid;
        width: 36px;
        height: 36px;
        place-items: center;
        padding: 0;
        border: 0;
        border-radius: 50%;
        color: #fff;
        background: var(--pd-red);
        font-size: 23px;
    }

    .pd-agenda-detail {
        display: grid;
        gap: 10px;
    }

    .pd-detail-row {
        display: grid;
        grid-template-columns: 125px minmax(0, 1fr);
        gap: 12px;
        padding: 11px;
        border-radius: 9px;
        background: var(--pd-surface-soft);
    }

    .pd-detail-row span {
        color: var(--pd-muted);
        font-size: 11px;
    }

    .pd-detail-row strong {
        font-size: 12px;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .pd-drawer-actions {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
        margin-top: 16px;
    }

    /* =====================================================
       TOAST & LOADING
       ===================================================== */

    .pd-toast {
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

    .pd-toast.is-visible {
        visibility: visible;
        opacity: 1;
        transform: translateY(0);
    }

    .pd-loading-layer {
        position: absolute;
        inset: 0;
        z-index: 40;
        display: none;
        padding: 14px;
        background: rgba(243, 245, 247, .94);
    }

    .pd-loading-layer.is-visible {
        display: block;
    }

    .pd-loading-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 12px;
    }

    .pd-skeleton {
        position: relative;
        min-height: 95px;
        overflow: hidden;
        border-radius: 12px;
        background: #e3e7eb;
    }

    .pd-skeleton.large {
        min-height: 310px;
        grid-column: span 3;
        margin-top: 12px;
    }

    .pd-skeleton.medium {
        min-height: 310px;
        grid-column: span 2;
        margin-top: 12px;
    }

    .pd-skeleton.table {
        min-height: 210px;
        grid-column: 1 / -1;
        margin-top: 12px;
    }

    .pd-skeleton::after {
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
        animation: pd-shimmer 1.2s infinite;
    }

    @keyframes pd-shimmer {
        100% {
            transform: translateX(100%);
        }
    }

    /* =====================================================
       FOOTER / COLLAPSE / RESPONSIVE
       ===================================================== */

    .pd-footer {
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

    .pd-page.sidebar-collapsed {
        grid-template-columns:
            var(--pd-sidebar-collapsed)
            minmax(0, 1fr);
    }

    .pd-page.sidebar-collapsed .pd-sidebar-head {
        grid-template-columns: var(--pd-sidebar-collapsed);
    }

    .pd-page.sidebar-collapsed .pd-sidebar-logo,
    .pd-page.sidebar-collapsed .pd-menu-label,
    .pd-page.sidebar-collapsed .pd-menu-arrow,
    .pd-page.sidebar-collapsed .pd-submenu,
    .pd-page.sidebar-collapsed .pd-sidebar-bottom {
        display: none;
    }

    .pd-page.sidebar-collapsed .pd-sidebar-toggle {
        width: var(--pd-sidebar-collapsed);
        border-left: 0;
    }

    .pd-page.sidebar-collapsed .pd-menu-link,
    .pd-page.sidebar-collapsed .pd-menu-toggle {
        justify-content: center;
        padding-inline: 0;
        border-left-color: transparent;
    }

    @media (max-width: 1450px) {
        :root {
            --pd-sidebar: 205px;
        }

        .pd-content {
            padding: 11px;
        }

        .pd-kpi-grid {
            gap: 9px;
        }

        .pd-kpi-card {
            grid-template-columns: 42px minmax(0, 1fr);
            gap: 8px;
            min-height: 86px;
            padding: 11px;
        }

        .pd-kpi-icon {
            width: 42px;
            height: 42px;
        }

        .pd-kpi-value {
            font-size: 23px;
        }

        .pd-calendar-day {
            min-height: 67px;
        }
    }

    @media (max-width: 1180px) {
        .pd-kpi-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .pd-dashboard-grid {
            grid-template-columns: 1fr;
        }

        .pd-form-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .pd-form-grid .span-3 {
            grid-column: 1 / -1;
        }

        .pd-certificate-grid {
            grid-template-columns: repeat(2, minmax(220px, 1fr));
        }
    }

    @media (max-width: 900px) {
        .pd-page {
            grid-template-columns:
                var(--pd-sidebar-collapsed)
                minmax(0, 1fr);
        }

        .pd-sidebar-head {
            grid-template-columns: var(--pd-sidebar-collapsed);
        }

        .pd-sidebar-logo,
        .pd-menu-label,
        .pd-menu-arrow,
        .pd-submenu,
        .pd-sidebar-bottom {
            display: none;
        }

        .pd-sidebar-toggle {
            width: var(--pd-sidebar-collapsed);
            border-left: 0;
        }

        .pd-menu-link,
        .pd-menu-toggle {
            justify-content: center;
            padding-inline: 0;
            border-left-color: transparent;
        }

        .pd-summary-strip {
            grid-template-columns: repeat(2, 1fr);
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
    | $trainingStats, $trainingAgendas, $followUps, $participants,
    | $evaluations, $skills, $competencyRows, $gapRecommendations,
    | $trainingHistory, $certificates, $agendaEndpoint
    */

    $calendarMonth = $calendarMonth ?? '2026-07';

    $trainingStats = $trainingStats ?? [
        'month_training' => 12,
        'upcoming_training' => 4,
        'participants' => 186,
        'attendance_rate' => 92,
        'pending_evaluation' => 3,
    ];

    $trainingAgendas = collect($trainingAgendas ?? [
        [
            'id' => 1,
            'title' => 'Training Penambahan Versatility',
            'category' => 'Versatility',
            'date' => '2026-07-23',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'location' => 'Training Room',
            'trainer' => 'Internal Trainer',
            'target_position' => 'Operator Produksi',
            'quota' => 25,
            'participants' => 21,
            'objective' => 'Menambah kemampuan operator pada unit kerja tambahan.',
            'competency' => 'Versatility alat berat',
            'status' => 'Terjadwal',
        ],
        [
            'id' => 2,
            'title' => 'Safety Leadership for Supervisor',
            'category' => 'Safety',
            'date' => '2026-07-26',
            'start_time' => '08:30',
            'end_time' => '11:30',
            'location' => 'Meeting Room 2',
            'trainer' => 'HSE Department',
            'target_position' => 'Supervisor',
            'quota' => 20,
            'participants' => 18,
            'objective' => 'Meningkatkan peran supervisor dalam budaya keselamatan.',
            'competency' => 'Safety leadership',
            'status' => 'Terjadwal',
        ],
        [
            'id' => 3,
            'title' => 'Basic Operation GD 825',
            'category' => 'Technical Skill',
            'date' => '2026-07-29',
            'start_time' => '08:00',
            'end_time' => '15:00',
            'location' => 'Training Ground',
            'trainer' => 'Technical Instructor',
            'target_position' => 'Operator',
            'quota' => 16,
            'participants' => 14,
            'objective' => 'Memberikan dasar pengoperasian GD 825.',
            'competency' => 'Operasi GD 825',
            'status' => 'Terjadwal',
        ],
        [
            'id' => 4,
            'title' => 'Coaching Skill for Foreman',
            'category' => 'Leadership',
            'date' => '2026-08-03',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'location' => 'Learning Center',
            'trainer' => 'External Trainer',
            'target_position' => 'Foreman',
            'quota' => 20,
            'participants' => 17,
            'objective' => 'Meningkatkan keterampilan coaching atasan lini.',
            'competency' => 'Coaching dan feedback',
            'status' => 'Draft',
        ],
    ]);

    $followUps = collect($followUps ?? [
        [
            'training' => 'Basic Operation HD 785',
            'participants' => 24,
            'status' => 'Selesai',
            'evaluation' => 'Belum Lengkap',
            'action' => 'Lengkapi Evaluasi',
        ],
        [
            'training' => 'Refresher Defensive Driving',
            'participants' => 18,
            'status' => 'Selesai',
            'evaluation' => 'Menunggu Post-test',
            'action' => 'Input Nilai',
        ],
        [
            'training' => 'Induction Karyawan Baru',
            'participants' => 32,
            'status' => 'Berjalan',
            'evaluation' => 'Belum Tersedia',
            'action' => 'Lihat Peserta',
        ],
    ]);

    $participants = collect($participants ?? [
        [
            'nrp' => '1707255',
            'name' => 'Nanang Sahrani',
            'position' => 'Operator HD 785',
            'department' => 'Produksi',
            'training' => 'Training Penambahan Versatility',
            'nomination' => 'Diusulkan',
            'approval' => 'Disetujui',
            'attendance' => 'Terdaftar',
        ],
        [
            'nrp' => '1683312',
            'name' => 'Joni',
            'position' => 'Operator PC 500',
            'department' => 'Produksi',
            'training' => 'Training Penambahan Versatility',
            'nomination' => 'Diusulkan',
            'approval' => 'Menunggu',
            'attendance' => 'Terdaftar',
        ],
        [
            'nrp' => '1707256',
            'name' => 'Dedza Audio Bayu Pradama',
            'position' => 'Operator HD 785',
            'department' => 'Produksi',
            'training' => 'Basic Operation GD 825',
            'nomination' => 'Diusulkan',
            'approval' => 'Disetujui',
            'attendance' => 'Hadir',
        ],
        [
            'nrp' => '16833575',
            'name' => 'Doni Ichwan Hermawan',
            'position' => 'Operator HD 785',
            'department' => 'Produksi',
            'training' => 'Basic Operation GD 825',
            'nomination' => 'Diusulkan',
            'approval' => 'Disetujui',
            'attendance' => 'Izin',
        ],
    ]);

    $evaluations = collect($evaluations ?? [
        [
            'name' => 'Nanang Sahrani',
            'training' => 'Training Penambahan Versatility',
            'pre_test' => 65,
            'post_test' => 87,
            'feedback' => 4.6,
            'effectiveness' => 'Meningkat',
        ],
        [
            'name' => 'Joni',
            'training' => 'Training Penambahan Versatility',
            'pre_test' => 70,
            'post_test' => 84,
            'feedback' => 4.4,
            'effectiveness' => 'Meningkat',
        ],
        [
            'name' => 'Dedza Audio Bayu Pradama',
            'training' => 'Basic Operation GD 825',
            'pre_test' => 72,
            'post_test' => 90,
            'feedback' => 4.8,
            'effectiveness' => 'Sangat Baik',
        ],
    ]);

    $skills = collect($skills ?? [
        'HD 785',
        'GD 825',
        'PC 500',
        'DT 135',
    ]);

    $competencyRows = collect($competencyRows ?? [
        [
            'name' => 'Nanang Sahrani',
            'position' => 'Operator HD 785',
            'skills' => [
                'HD 785' => 'Kompeten',
                'GD 825' => 'Training',
                'PC 500' => 'Belum',
                'DT 135' => 'Belum',
            ],
            'versatility' => 1,
        ],
        [
            'name' => 'Joni',
            'position' => 'Operator PC 500',
            'skills' => [
                'HD 785' => 'Belum',
                'GD 825' => 'Belum',
                'PC 500' => 'Kompeten',
                'DT 135' => 'Training',
            ],
            'versatility' => 1,
        ],
        [
            'name' => 'Dedza Audio Bayu Pradama',
            'position' => 'Operator HD 785',
            'skills' => [
                'HD 785' => 'Kompeten',
                'GD 825' => 'Kompeten',
                'PC 500' => 'Belum',
                'DT 135' => 'Belum',
            ],
            'versatility' => 2,
        ],
    ]);

    $gapRecommendations = collect($gapRecommendations ?? [
        [
            'name' => 'Nanang Sahrani',
            'current_skill' => 'HD 785',
            'target_skill' => 'GD 825',
            'gap' => 'Perlu praktek unit',
            'recommendation' => 'Training Penambahan Versatility',
            'priority' => 'Tinggi',
        ],
        [
            'name' => 'Joni',
            'current_skill' => 'PC 500',
            'target_skill' => 'DT 135',
            'gap' => 'Belum sertifikasi',
            'recommendation' => 'Basic Operation DT 135',
            'priority' => 'Sedang',
        ],
    ]);

    $trainingHistory = collect($trainingHistory ?? [
        [
            'date' => '2026-06-18',
            'training' => 'Basic Operation HD 785',
            'category' => 'Technical Skill',
            'participants' => 24,
            'attendance_rate' => '96%',
            'status' => 'Selesai',
        ],
        [
            'date' => '2026-06-25',
            'training' => 'Refresher Defensive Driving',
            'category' => 'Safety',
            'participants' => 18,
            'attendance_rate' => '100%',
            'status' => 'Selesai',
        ],
        [
            'date' => '2026-07-10',
            'training' => 'Induction Karyawan Baru',
            'category' => 'Induction',
            'participants' => 32,
            'attendance_rate' => '94%',
            'status' => 'Selesai',
        ],
    ]);

    $certificates = collect($certificates ?? [
        [
            'name' => 'Nanang Sahrani',
            'training' => 'Basic Operation HD 785',
            'certificate_no' => 'CERT-2026-00125',
            'issued_at' => '18 Juni 2026',
        ],
        [
            'name' => 'Dedza Audio Bayu Pradama',
            'training' => 'Defensive Driving',
            'certificate_no' => 'CERT-2026-00138',
            'issued_at' => '25 Juni 2026',
        ],
        [
            'name' => 'Joni',
            'training' => 'Induction Karyawan Baru',
            'certificate_no' => 'CERT-2026-00152',
            'issued_at' => '10 Juli 2026',
        ],
    ]);

    $agendaEndpoint = $agendaEndpoint ?? null;
@endphp

<div class="pd-page" id="peopleDevelopmentPage">
    {{-- SIDEBAR --}}
    <aside class="pd-sidebar">
        <div class="pd-sidebar-head">
            <div class="pd-sidebar-logo">
                <img
                    src="{{ asset('assets/images/LOGO PEOPLE DEVELOPMENT.png') }}"
                    alt="People Development"
                >
            </div>

            <button
                type="button"
                class="pd-sidebar-toggle"
                id="pdSidebarToggle"
                aria-label="Buka atau tutup sidebar"
            >
                ☰
            </button>
        </div>

        <nav class="pd-navigation" aria-label="Menu People Development">
            <button
                type="button"
                class="pd-menu-link active"
                data-pd-view="pd-dashboard"
            >
                <span class="pd-menu-icon">▦</span>
                <span class="pd-menu-label">Dashboard</span>
            </button>

            <div class="pd-menu-group is-open">
                <button
                    type="button"
                    class="pd-menu-toggle"
                    aria-expanded="true"
                >
                    <span class="pd-menu-icon">🗓</span>
                    <span class="pd-menu-label">Training</span>
                    <span class="pd-menu-arrow">›</span>
                </button>

                <div class="pd-submenu">
                    <div class="pd-submenu-inner">
                        <button
                            type="button"
                            class="pd-submenu-button"
                            data-pd-view="pd-calendar-view"
                        >
                            Kalender Training
                        </button>

                        <button
                            type="button"
                            class="pd-submenu-button"
                            data-pd-view="pd-input-agenda"
                        >
                            Input Agenda
                        </button>

                        <button
                            type="button"
                            class="pd-submenu-button"
                            data-pd-view="pd-agenda-list"
                        >
                            Daftar Agenda
                        </button>
                    </div>
                </div>
            </div>

            <div class="pd-menu-group">
                <button
                    type="button"
                    class="pd-menu-toggle"
                    aria-expanded="false"
                >
                    <span class="pd-menu-icon">👥</span>
                    <span class="pd-menu-label">Peserta</span>
                    <span class="pd-menu-arrow">›</span>
                </button>

                <div class="pd-submenu">
                    <div class="pd-submenu-inner">
                        <button
                            type="button"
                            class="pd-submenu-button"
                            data-pd-view="pd-nomination"
                        >
                            Nominasi Peserta
                        </button>

                        <button
                            type="button"
                            class="pd-submenu-button"
                            data-pd-view="pd-participants"
                        >
                            Daftar Peserta
                        </button>

                        <button
                            type="button"
                            class="pd-submenu-button"
                            data-pd-view="pd-attendance"
                        >
                            Kehadiran
                        </button>
                    </div>
                </div>
            </div>

            <div class="pd-menu-group">
                <button
                    type="button"
                    class="pd-menu-toggle"
                    aria-expanded="false"
                >
                    <span class="pd-menu-icon">📝</span>
                    <span class="pd-menu-label">Evaluasi</span>
                    <span class="pd-menu-arrow">›</span>
                </button>

                <div class="pd-submenu">
                    <div class="pd-submenu-inner">
                        <button
                            type="button"
                            class="pd-submenu-button"
                            data-pd-view="pd-prepost"
                        >
                            Pre-test &amp; Post-test
                        </button>

                        <button
                            type="button"
                            class="pd-submenu-button"
                            data-pd-view="pd-feedback"
                        >
                            Feedback Peserta
                        </button>
                    </div>
                </div>
            </div>

            <div class="pd-menu-group">
                <button
                    type="button"
                    class="pd-menu-toggle"
                    aria-expanded="false"
                >
                    <span class="pd-menu-icon">🧩</span>
                    <span class="pd-menu-label">Kompetensi</span>
                    <span class="pd-menu-arrow">›</span>
                </button>

                <div class="pd-submenu">
                    <div class="pd-submenu-inner">
                        <button
                            type="button"
                            class="pd-submenu-button"
                            data-pd-view="pd-matrix"
                        >
                            Matriks Versatility
                        </button>

                        <button
                            type="button"
                            class="pd-submenu-button"
                            data-pd-view="pd-gap"
                        >
                            Gap Kompetensi
                        </button>
                    </div>
                </div>
            </div>

            <div class="pd-menu-group">
                <button
                    type="button"
                    class="pd-menu-toggle"
                    aria-expanded="false"
                >
                    <span class="pd-menu-icon">📊</span>
                    <span class="pd-menu-label">Laporan</span>
                    <span class="pd-menu-arrow">›</span>
                </button>

                <div class="pd-submenu">
                    <div class="pd-submenu-inner">
                        <button
                            type="button"
                            class="pd-submenu-button"
                            data-pd-view="pd-history"
                        >
                            Riwayat Training
                        </button>

                        <button
                            type="button"
                            class="pd-submenu-button"
                            data-pd-view="pd-certificates"
                        >
                            Sertifikat
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <div class="pd-sidebar-bottom">
            <a href="#" class="pd-bottom-link">
                <span>⚙</span>
                <span>Pengaturan</span>
            </a>

            <a
                href="https://mail.google.com/mail/?view=cm&fs=1&to={{ urlencode(config('access.contact_email', 'mpe.ppaba@ppa.co.id')) }}&su=SYNRGYPRO%20Support"
                target="_blank"
                rel="noopener noreferrer"
                class="pd-bottom-link help"
            >
                <span>?</span>
                <span>Bantuan</span>
            </a>
        </div>
    </aside>

    {{-- HEADER --}}
    <header class="pd-header">
        <div class="pd-header-brand">
            <img
                src="{{ asset('assets/images/synrgypro-logo.png') }}"
                alt="SYNRGYPRO"
            >
        </div>

        <nav class="pd-header-actions" aria-label="Shortcut pengguna">
            <x-module-shortcut />

            <a
                href="{{ route('dashboard') }}"
                class="pd-header-button"
                aria-label="Dashboard"
            >
                <img
                    src="{{ asset('assets/images/LOGO HOME.jpeg') }}"
                    alt=""
                >
            </a>

            <button
                type="button"
                class="pd-header-button pd-profile-button"
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
                class="pd-logout-form"
            >
                @csrf

                <button
                    type="submit"
                    class="pd-header-button pd-logout-button"
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
    <main class="pd-content">
        <div
            class="pd-loading-layer"
            id="pdLoadingLayer"
            aria-hidden="true"
        >
            <div class="pd-loading-grid">
                <div class="pd-skeleton"></div>
                <div class="pd-skeleton"></div>
                <div class="pd-skeleton"></div>
                <div class="pd-skeleton"></div>
                <div class="pd-skeleton"></div>
                <div class="pd-skeleton large"></div>
                <div class="pd-skeleton medium"></div>
                <div class="pd-skeleton table"></div>
            </div>
        </div>

        {{-- DASHBOARD --}}
        <section class="pd-view active" id="pd-dashboard">
            <div class="pd-page-title">
                <div>
                    <h1>Dashboard People Development</h1>
                    <p>
                        Monitoring agenda training, peserta, evaluasi,
                        dan peningkatan kompetensi.
                    </p>
                </div>

                <button
                    type="button"
                    class="pd-button"
                    data-open-view="pd-input-agenda"
                >
                    + Input Agenda
                </button>
            </div>

            <div class="pd-kpi-grid">
                <button
                    type="button"
                    class="pd-kpi-card"
                    data-open-view="pd-agenda-list"
                >
                    <span class="pd-kpi-icon">🗓</span>
                    <span>
                        <small>Training Bulan Ini</small>
                        <span class="pd-kpi-value">
                            {{ number_format((int) data_get($trainingStats, 'month_training', 0)) }}
                        </span>
                    </span>
                </button>

                <button
                    type="button"
                    class="pd-kpi-card"
                    data-open-view="pd-calendar-view"
                >
                    <span class="pd-kpi-icon">⏳</span>
                    <span>
                        <small>Training Mendatang</small>
                        <span class="pd-kpi-value">
                            {{ number_format((int) data_get($trainingStats, 'upcoming_training', 0)) }}
                        </span>
                    </span>
                </button>

                <button
                    type="button"
                    class="pd-kpi-card"
                    data-open-view="pd-participants"
                >
                    <span class="pd-kpi-icon">👥</span>
                    <span>
                        <small>Total Peserta</small>
                        <span class="pd-kpi-value">
                            {{ number_format((int) data_get($trainingStats, 'participants', 0)) }}
                        </span>
                    </span>
                </button>

                <button
                    type="button"
                    class="pd-kpi-card"
                    data-open-view="pd-attendance"
                >
                    <span class="pd-kpi-icon">✓</span>
                    <span>
                        <small>Tingkat Kehadiran</small>
                        <span class="pd-kpi-value">
                            {{ data_get($trainingStats, 'attendance_rate', 0) }}%
                        </span>
                    </span>
                </button>

                <button
                    type="button"
                    class="pd-kpi-card"
                    data-open-view="pd-prepost"
                >
                    <span class="pd-kpi-icon">!</span>
                    <span>
                        <small>Belum Dievaluasi</small>
                        <span class="pd-kpi-value">
                            {{ number_format((int) data_get($trainingStats, 'pending_evaluation', 0)) }}
                        </span>
                    </span>
                </button>
            </div>

            <div class="pd-dashboard-grid">
                <article class="pd-calendar-card">
                    <div class="pd-card-header">
                        <div>
                            <h2>Kalender Training</h2>
                            <small>Klik agenda untuk melihat detail</small>
                        </div>

                        <div class="pd-calendar-toolbar">
                            <button
                                type="button"
                                class="pd-calendar-nav"
                                data-calendar-prev
                                aria-label="Bulan sebelumnya"
                            >
                                ‹
                            </button>

                            <span
                                class="pd-calendar-month"
                                data-calendar-label
                            ></span>

                            <button
                                type="button"
                                class="pd-calendar-nav"
                                data-calendar-next
                                aria-label="Bulan berikutnya"
                            >
                                ›
                            </button>
                        </div>
                    </div>

                    <div class="pd-calendar">
                        <div class="pd-calendar-weekdays">
                            <span>Sen</span>
                            <span>Sel</span>
                            <span>Rab</span>
                            <span>Kam</span>
                            <span>Jum</span>
                            <span>Sab</span>
                            <span>Min</span>
                        </div>

                        <div
                            class="pd-calendar-grid"
                            data-calendar-grid
                        ></div>
                    </div>
                </article>

                <article class="pd-upcoming-card">
                    <div class="pd-card-header">
                        <div>
                            <h2>Agenda Mendatang</h2>
                            <small>Jadwal training terdekat</small>
                        </div>
                    </div>

                    <div
                        class="pd-upcoming-list"
                        data-upcoming-list
                    ></div>
                </article>
            </div>

            <article class="pd-table-card">
                <div class="pd-card-header">
                    <div>
                        <h2>Training yang Perlu Ditindaklanjuti</h2>
                        <small>Evaluasi dan kelengkapan administrasi</small>
                    </div>
                </div>

                <div class="pd-table-wrap">
                    <table class="pd-table">
                        <thead>
                            <tr>
                                <th>Training</th>
                                <th>Peserta</th>
                                <th>Status</th>
                                <th>Evaluasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($followUps as $item)
                                <tr>
                                    <td>{{ data_get($item, 'training', '-') }}</td>
                                    <td>{{ data_get($item, 'participants', 0) }}</td>
                                    <td>
                                        <span class="pd-badge green">
                                            {{ data_get($item, 'status', '-') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="pd-badge orange">
                                            {{ data_get($item, 'evaluation', '-') }}
                                        </span>
                                    </td>
                                    <td>
                                        <button
                                            type="button"
                                            class="pd-table-action"
                                            data-open-view="pd-prepost"
                                        >
                                            {{ data_get($item, 'action', 'Detail') }}
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

        {{-- KALENDER TRAINING --}}
        <section class="pd-view" id="pd-calendar-view">
            <div class="pd-page-title">
                <div>
                    <h1>Kalender Training</h1>
                    <p>
                        Seluruh agenda training dalam tampilan kalender bulanan.
                    </p>
                </div>

                <button
                    type="button"
                    class="pd-button"
                    data-open-view="pd-input-agenda"
                >
                    + Input Agenda
                </button>
            </div>

            <article class="pd-calendar-card">
                <div class="pd-card-header">
                    <div>
                        <h2>Agenda Bulanan</h2>
                        <small>Klik agenda untuk detail lengkap</small>
                    </div>

                    <div class="pd-calendar-toolbar">
                        <button
                            type="button"
                            class="pd-calendar-nav"
                            data-calendar-prev
                        >
                            ‹
                        </button>

                        <span
                            class="pd-calendar-month"
                            data-calendar-label
                        ></span>

                        <button
                            type="button"
                            class="pd-calendar-nav"
                            data-calendar-next
                        >
                            ›
                        </button>
                    </div>
                </div>

                <div class="pd-calendar">
                    <div class="pd-calendar-weekdays">
                        <span>Sen</span>
                        <span>Sel</span>
                        <span>Rab</span>
                        <span>Kam</span>
                        <span>Jum</span>
                        <span>Sab</span>
                        <span>Min</span>
                    </div>

                    <div
                        class="pd-calendar-grid"
                        data-calendar-grid
                    ></div>
                </div>
            </article>
        </section>

        {{-- INPUT AGENDA --}}
        <section class="pd-view" id="pd-input-agenda">
            <div class="pd-page-title">
                <div>
                    <h1>Input Agenda Training</h1>
                    <p>
                        Tambahkan jadwal, trainer, peserta, dan tujuan training.
                    </p>
                </div>
            </div>

            <form
                class="pd-form-card"
                id="pdAgendaForm"
                data-endpoint="{{ $agendaEndpoint }}"
            >
                <div class="pd-card-header">
                    <div>
                        <h2>Form Agenda Training</h2>
                        <small>Field bertanda wajib harus diisi</small>
                    </div>
                </div>

                <div class="pd-form-body">
                    <div class="pd-form-grid">
                        <div class="pd-field span-2">
                            <label for="agendaTitle">Nama Training *</label>
                            <input
                                type="text"
                                id="agendaTitle"
                                name="title"
                                class="pd-input"
                                placeholder="Contoh: Training Penambahan Versatility"
                                required
                            >
                        </div>

                        <div class="pd-field">
                            <label for="agendaCategory">Kategori *</label>
                            <select
                                id="agendaCategory"
                                name="category"
                                class="pd-select"
                                required
                            >
                                <option value="">Pilih kategori</option>
                                <option>Technical Skill</option>
                                <option>Safety</option>
                                <option>Leadership</option>
                                <option>Versatility</option>
                                <option>Compliance</option>
                                <option>Soft Skill</option>
                                <option>Induction</option>
                            </select>
                        </div>

                        <div class="pd-field">
                            <label for="agendaDate">Tanggal *</label>
                            <input
                                type="date"
                                id="agendaDate"
                                name="date"
                                class="pd-input"
                                required
                            >
                        </div>

                        <div class="pd-field">
                            <label for="agendaStart">Jam Mulai *</label>
                            <input
                                type="time"
                                id="agendaStart"
                                name="start_time"
                                class="pd-input"
                                required
                            >
                        </div>

                        <div class="pd-field">
                            <label for="agendaEnd">Jam Selesai *</label>
                            <input
                                type="time"
                                id="agendaEnd"
                                name="end_time"
                                class="pd-input"
                                required
                            >
                        </div>

                        <div class="pd-field">
                            <label for="agendaLocation">Lokasi *</label>
                            <input
                                type="text"
                                id="agendaLocation"
                                name="location"
                                class="pd-input"
                                placeholder="Training Room"
                                required
                            >
                        </div>

                        <div class="pd-field">
                            <label for="agendaTrainer">Trainer / Vendor *</label>
                            <input
                                type="text"
                                id="agendaTrainer"
                                name="trainer"
                                class="pd-input"
                                placeholder="Nama trainer atau vendor"
                                required
                            >
                        </div>

                        <div class="pd-field">
                            <label for="agendaTarget">Target Jabatan</label>
                            <input
                                type="text"
                                id="agendaTarget"
                                name="target_position"
                                class="pd-input"
                                placeholder="Operator Produksi"
                            >
                        </div>

                        <div class="pd-field">
                            <label for="agendaQuota">Kuota Peserta</label>
                            <input
                                type="number"
                                id="agendaQuota"
                                name="quota"
                                class="pd-input"
                                min="1"
                                value="20"
                            >
                        </div>

                        <div class="pd-field">
                            <label for="agendaStatus">Status Agenda</label>
                            <select
                                id="agendaStatus"
                                name="status"
                                class="pd-select"
                            >
                                <option>Draft</option>
                                <option>Terjadwal</option>
                                <option>Berjalan</option>
                            </select>
                        </div>

                        <div class="pd-field span-3">
                            <label for="agendaObjective">Tujuan Training</label>
                            <textarea
                                id="agendaObjective"
                                name="objective"
                                class="pd-textarea"
                                placeholder="Tuliskan tujuan dan hasil yang diharapkan..."
                            ></textarea>
                        </div>

                        <div class="pd-field span-3">
                            <label for="agendaCompetency">
                                Kompetensi yang Ditingkatkan
                            </label>
                            <textarea
                                id="agendaCompetency"
                                name="competency"
                                class="pd-textarea"
                                placeholder="Contoh: Versatility alat berat, safety leadership..."
                            ></textarea>
                        </div>
                    </div>

                    <div class="pd-form-actions">
                        <button
                            type="reset"
                            class="pd-button secondary"
                        >
                            Reset
                        </button>

                        <button
                            type="submit"
                            class="pd-button"
                        >
                            Simpan Agenda
                        </button>
                    </div>
                </div>
            </form>
        </section>

        {{-- DAFTAR AGENDA --}}
        <section class="pd-view" id="pd-agenda-list">
            <div class="pd-page-title">
                <div>
                    <h1>Daftar Agenda Training</h1>
                    <p>Daftar seluruh agenda training dan status pelaksanaannya.</p>
                </div>

                <button
                    type="button"
                    class="pd-button"
                    data-open-view="pd-input-agenda"
                >
                    + Input Agenda
                </button>
            </div>

            <article class="pd-table-card">
                <div class="pd-card-header">
                    <div>
                        <h2>Agenda Training</h2>
                        <small id="pdAgendaCount"></small>
                    </div>
                </div>

                <div class="pd-table-wrap">
                    <table class="pd-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Training</th>
                                <th>Kategori</th>
                                <th>Trainer</th>
                                <th>Peserta</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody id="pdAgendaTableBody"></tbody>
                    </table>
                </div>
            </article>
        </section>

        {{-- NOMINASI PESERTA --}}
        <section class="pd-view" id="pd-nomination">
            <div class="pd-page-title">
                <div>
                    <h1>Nominasi Peserta</h1>
                    <p>Usulan peserta dan persetujuan atasan.</p>
                </div>
            </div>

            <article class="pd-table-card">
                <div class="pd-card-header">
                    <div>
                        <h2>Usulan Peserta Training</h2>
                        <small>Persetujuan dapat dihubungkan ke workflow backend</small>
                    </div>
                </div>

                <div class="pd-table-wrap">
                    <table class="pd-table">
                        <thead>
                            <tr>
                                <th>NRP</th>
                                <th>Nama</th>
                                <th>Jabatan</th>
                                <th>Departemen</th>
                                <th>Training</th>
                                <th>Status Nominasi</th>
                                <th>Persetujuan</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($participants as $participant)
                                <tr>
                                    <td>{{ data_get($participant, 'nrp', '-') }}</td>
                                    <td>{{ data_get($participant, 'name', '-') }}</td>
                                    <td>{{ data_get($participant, 'position', '-') }}</td>
                                    <td>{{ data_get($participant, 'department', '-') }}</td>
                                    <td>{{ data_get($participant, 'training', '-') }}</td>
                                    <td>
                                        <span class="pd-badge">
                                            {{ data_get($participant, 'nomination', '-') }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $approval = data_get($participant, 'approval', '-');
                                        @endphp

                                        <span class="pd-badge {{ $approval === 'Disetujui' ? 'green' : 'orange' }}">
                                            {{ $approval }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

        {{-- DAFTAR PESERTA --}}
        <section class="pd-view" id="pd-participants">
            <div class="pd-page-title">
                <div>
                    <h1>Daftar Peserta</h1>
                    <p>Peserta terdaftar pada seluruh agenda training.</p>
                </div>
            </div>

            <article class="pd-table-card">
                <div class="pd-card-header">
                    <div>
                        <h2>Data Peserta Training</h2>
                        <small>{{ $participants->count() }} data contoh</small>
                    </div>
                </div>

                <div class="pd-table-wrap">
                    <table class="pd-table">
                        <thead>
                            <tr>
                                <th>NRP</th>
                                <th>Nama</th>
                                <th>Jabatan</th>
                                <th>Training</th>
                                <th>Persetujuan</th>
                                <th>Kehadiran</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($participants as $participant)
                                <tr>
                                    <td>{{ data_get($participant, 'nrp', '-') }}</td>
                                    <td>{{ data_get($participant, 'name', '-') }}</td>
                                    <td>{{ data_get($participant, 'position', '-') }}</td>
                                    <td>{{ data_get($participant, 'training', '-') }}</td>
                                    <td>
                                        <span class="pd-badge green">
                                            {{ data_get($participant, 'approval', '-') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="pd-badge">
                                            {{ data_get($participant, 'attendance', '-') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

        {{-- KEHADIRAN --}}
        <section class="pd-view" id="pd-attendance">
            <div class="pd-page-title">
                <div>
                    <h1>Kehadiran Training</h1>
                    <p>Pencatatan hadir, terlambat, izin, dan tidak hadir.</p>
                </div>
            </div>

            <div class="pd-summary-strip">
                <div class="pd-summary-box">
                    <strong>42</strong>
                    <small>Hadir</small>
                </div>
                <div class="pd-summary-box">
                    <strong>3</strong>
                    <small>Terlambat</small>
                </div>
                <div class="pd-summary-box">
                    <strong>2</strong>
                    <small>Izin</small>
                </div>
                <div class="pd-summary-box">
                    <strong>1</strong>
                    <small>Tidak Hadir</small>
                </div>
            </div>

            <article class="pd-table-card">
                <div class="pd-card-header">
                    <div>
                        <h2>Daftar Kehadiran</h2>
                        <small>Status dapat diperbarui oleh admin training</small>
                    </div>
                </div>

                <div class="pd-table-wrap">
                    <table class="pd-table">
                        <thead>
                            <tr>
                                <th>NRP</th>
                                <th>Nama</th>
                                <th>Training</th>
                                <th>Status Kehadiran</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($participants as $participant)
                                <tr>
                                    <td>{{ data_get($participant, 'nrp', '-') }}</td>
                                    <td>{{ data_get($participant, 'name', '-') }}</td>
                                    <td>{{ data_get($participant, 'training', '-') }}</td>
                                    <td>
                                        <select class="pd-select" style="height:32px;">
                                            @foreach (['Hadir', 'Terlambat', 'Izin', 'Tidak Hadir', 'Terdaftar'] as $attendance)
                                                <option
                                                    {{ data_get($participant, 'attendance') === $attendance ? 'selected' : '' }}
                                                >
                                                    {{ $attendance }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <button type="button" class="pd-table-action">
                                            Simpan
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

        {{-- PRE TEST POST TEST --}}
        <section class="pd-view" id="pd-prepost">
            <div class="pd-page-title">
                <div>
                    <h1>Pre-test dan Post-test</h1>
                    <p>Pengukuran peningkatan pengetahuan setelah training.</p>
                </div>
            </div>

            <article class="pd-table-card">
                <div class="pd-card-header">
                    <div>
                        <h2>Hasil Evaluasi Peserta</h2>
                        <small>Selisih nilai menunjukkan peningkatan</small>
                    </div>
                </div>

                <div class="pd-table-wrap">
                    <table class="pd-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Training</th>
                                <th>Pre-test</th>
                                <th>Post-test</th>
                                <th>Peningkatan</th>
                                <th>Efektivitas</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($evaluations as $evaluation)
                                @php
                                    $pre = (int) data_get($evaluation, 'pre_test', 0);
                                    $post = (int) data_get($evaluation, 'post_test', 0);
                                @endphp

                                <tr>
                                    <td>{{ data_get($evaluation, 'name', '-') }}</td>
                                    <td>{{ data_get($evaluation, 'training', '-') }}</td>
                                    <td class="pd-score">{{ $pre }}</td>
                                    <td class="pd-score up">{{ $post }}</td>
                                    <td>
                                        <strong class="pd-score up">
                                            +{{ $post - $pre }}
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="pd-badge green">
                                            {{ data_get($evaluation, 'effectiveness', '-') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

        {{-- FEEDBACK --}}
        <section class="pd-view" id="pd-feedback">
            <div class="pd-page-title">
                <div>
                    <h1>Feedback Peserta</h1>
                    <p>Penilaian peserta terhadap materi dan trainer.</p>
                </div>
            </div>

            <div class="pd-summary-strip">
                <div class="pd-summary-box">
                    <strong>4.6</strong>
                    <small>Rata-rata Kepuasan</small>
                </div>
                <div class="pd-summary-box">
                    <strong>94%</strong>
                    <small>Materi Relevan</small>
                </div>
                <div class="pd-summary-box">
                    <strong>96%</strong>
                    <small>Trainer Dipahami</small>
                </div>
                <div class="pd-summary-box">
                    <strong>91%</strong>
                    <small>Direkomendasikan</small>
                </div>
            </div>

            <article class="pd-table-card">
                <div class="pd-card-header">
                    <div>
                        <h2>Ringkasan Feedback</h2>
                        <small>Skala 1 sampai 5</small>
                    </div>
                </div>

                <div class="pd-table-wrap">
                    <table class="pd-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Training</th>
                                <th>Nilai Feedback</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($evaluations as $evaluation)
                                <tr>
                                    <td>{{ data_get($evaluation, 'name', '-') }}</td>
                                    <td>{{ data_get($evaluation, 'training', '-') }}</td>
                                    <td>
                                        <strong class="pd-score">
                                            {{ data_get($evaluation, 'feedback', 0) }}/5
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="pd-badge green">
                                            Positif
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

        {{-- MATRIX --}}
        <section class="pd-view" id="pd-matrix">
            <div class="pd-page-title">
                <div>
                    <h1>Matriks Versatility</h1>
                    <p>
                        Pemetaan kompetensi operator pada beberapa unit.
                    </p>
                </div>
            </div>

            <article class="pd-matrix-card">
                <div class="pd-card-header">
                    <div>
                        <h2>Matriks Kompetensi Operator</h2>
                        <small>
                            Kompeten, sedang training, atau belum kompeten
                        </small>
                    </div>
                </div>

                <div class="pd-matrix-wrap">
                    <table class="pd-matrix">
                        <thead>
                            <tr>
                                <th>Karyawan</th>
                                @foreach ($skills as $skill)
                                    <th>{{ $skill }}</th>
                                @endforeach
                                <th>Versatility</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($competencyRows as $employee)
                                <tr>
                                    <td>
                                        <strong>
                                            {{ data_get($employee, 'name', '-') }}
                                        </strong>
                                        <br>
                                        <small style="color:#6b7280;">
                                            {{ data_get($employee, 'position', '-') }}
                                        </small>
                                    </td>

                                    @foreach ($skills as $skill)
                                        @php
                                            $state = data_get(
                                                $employee,
                                                'skills.' . $skill,
                                                'Belum'
                                            );

                                            $stateClass = match ($state) {
                                                'Kompeten' => 'competent',
                                                'Training' => 'training',
                                                default => 'not-yet',
                                            };
                                        @endphp

                                        <td>
                                            <span class="pd-skill-state {{ $stateClass }}">
                                                {{ $state }}
                                            </span>
                                        </td>
                                    @endforeach

                                    <td>
                                        <strong>
                                            {{ data_get($employee, 'versatility', 0) }}
                                        </strong>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

        {{-- GAP --}}
        <section class="pd-view" id="pd-gap">
            <div class="pd-page-title">
                <div>
                    <h1>Gap Kompetensi</h1>
                    <p>
                        Rekomendasi training berdasarkan kebutuhan kompetensi.
                    </p>
                </div>
            </div>

            <article class="pd-table-card">
                <div class="pd-card-header">
                    <div>
                        <h2>Rekomendasi Pengembangan</h2>
                        <small>Dapat dihasilkan otomatis dari data skill matrix</small>
                    </div>
                </div>

                <div class="pd-table-wrap">
                    <table class="pd-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Skill Saat Ini</th>
                                <th>Target Skill</th>
                                <th>Gap</th>
                                <th>Rekomendasi Training</th>
                                <th>Prioritas</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($gapRecommendations as $item)
                                <tr>
                                    <td>{{ data_get($item, 'name', '-') }}</td>
                                    <td>{{ data_get($item, 'current_skill', '-') }}</td>
                                    <td>{{ data_get($item, 'target_skill', '-') }}</td>
                                    <td>{{ data_get($item, 'gap', '-') }}</td>
                                    <td>{{ data_get($item, 'recommendation', '-') }}</td>
                                    <td>
                                        <span class="pd-badge {{ data_get($item, 'priority') === 'Tinggi' ? 'red' : 'orange' }}">
                                            {{ data_get($item, 'priority', '-') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

        {{-- HISTORY --}}
        <section class="pd-view" id="pd-history">
            <div class="pd-page-title">
                <div>
                    <h1>Riwayat Training</h1>
                    <p>Riwayat pelaksanaan training dan tingkat kehadiran.</p>
                </div>
            </div>

            <article class="pd-table-card">
                <div class="pd-card-header">
                    <div>
                        <h2>Riwayat Pelaksanaan</h2>
                        <small>Siap difilter berdasarkan periode dan kategori</small>
                    </div>
                </div>

                <div class="pd-table-wrap">
                    <table class="pd-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Training</th>
                                <th>Kategori</th>
                                <th>Peserta</th>
                                <th>Kehadiran</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($trainingHistory as $history)
                                <tr>
                                    <td>{{ data_get($history, 'date', '-') }}</td>
                                    <td>{{ data_get($history, 'training', '-') }}</td>
                                    <td>{{ data_get($history, 'category', '-') }}</td>
                                    <td>{{ data_get($history, 'participants', 0) }}</td>
                                    <td>{{ data_get($history, 'attendance_rate', '-') }}</td>
                                    <td>
                                        <span class="pd-badge green">
                                            {{ data_get($history, 'status', '-') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

        {{-- CERTIFICATES --}}
        <section class="pd-view" id="pd-certificates">
            <div class="pd-page-title">
                <div>
                    <h1>Sertifikat Training</h1>
                    <p>Dokumen sertifikat peserta yang telah menyelesaikan training.</p>
                </div>
            </div>

            <div class="pd-certificate-grid">
                @foreach ($certificates as $certificate)
                    <article class="pd-certificate-card">
                        <div class="pd-certificate-icon">🏅</div>

                        <h3>{{ data_get($certificate, 'name', '-') }}</h3>

                        <p>
                            {{ data_get($certificate, 'training', '-') }}
                            <br>
                            No: {{ data_get($certificate, 'certificate_no', '-') }}
                            <br>
                            Terbit: {{ data_get($certificate, 'issued_at', '-') }}
                        </p>

                        <button type="button" class="pd-button">
                            Lihat Sertifikat
                        </button>
                    </article>
                @endforeach
            </div>
        </section>
    </main>

    <footer class="pd-footer">
        &copy; COPYRIGHT SYNRGYPRO {{ date('Y') }}. V1.0
    </footer>
</div>

{{-- AGENDA DETAIL DRAWER --}}
<div
    class="pd-drawer-backdrop"
    id="pdDrawerBackdrop"
    aria-hidden="true"
></div>

<aside
    class="pd-agenda-drawer"
    id="pdAgendaDrawer"
    aria-hidden="true"
>
    <div class="pd-drawer-header">
        <h2>Detail Agenda Training</h2>

        <button
            type="button"
            class="pd-drawer-close"
            id="pdDrawerClose"
            aria-label="Tutup detail agenda"
        >
            &times;
        </button>
    </div>

    <div
        class="pd-agenda-detail"
        id="pdAgendaDetail"
    ></div>

    <div class="pd-drawer-actions">
        <button
            type="button"
            class="pd-button"
            data-open-view="pd-participants"
        >
            Lihat Peserta
        </button>

        <button
            type="button"
            class="pd-button secondary"
            data-open-view="pd-input-agenda"
        >
            Edit Agenda
        </button>

        <button
            type="button"
            class="pd-button success"
            data-open-view="pd-attendance"
        >
            Catat Kehadiran
        </button>

        <button
            type="button"
            class="pd-button danger"
        >
            Batalkan Agenda
        </button>
    </div>
</aside>

<div class="pd-toast" id="pdToast"></div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const page =
        document.getElementById('peopleDevelopmentPage');

    const sidebarToggle =
        document.getElementById('pdSidebarToggle');

    const menuGroups =
        document.querySelectorAll('.pd-menu-group');

    const viewButtons =
        document.querySelectorAll('[data-pd-view]');

    const openViewButtons =
        document.querySelectorAll('[data-open-view]');

    const views =
        document.querySelectorAll('.pd-view');

    const calendarGrids =
        document.querySelectorAll('[data-calendar-grid]');

    const calendarLabels =
        document.querySelectorAll('[data-calendar-label]');

    const upcomingLists =
        document.querySelectorAll('[data-upcoming-list]');

    const prevButtons =
        document.querySelectorAll('[data-calendar-prev]');

    const nextButtons =
        document.querySelectorAll('[data-calendar-next]');

    const agendaForm =
        document.getElementById('pdAgendaForm');

    const agendaTableBody =
        document.getElementById('pdAgendaTableBody');

    const agendaCount =
        document.getElementById('pdAgendaCount');

    const drawer =
        document.getElementById('pdAgendaDrawer');

    const drawerBackdrop =
        document.getElementById('pdDrawerBackdrop');

    const drawerClose =
        document.getElementById('pdDrawerClose');

    const agendaDetail =
        document.getElementById('pdAgendaDetail');

    const toast =
        document.getElementById('pdToast');

    const loadingLayer =
        document.getElementById('pdLoadingLayer');

    const agendaEndpoint =
        agendaForm?.dataset.endpoint || '';

    const storageKey =
        'synrgypro_people_development_agendas';

    const initialAgendas =
        @json($trainingAgendas->values());

    let agendas = initialAgendas;
    let calendarDate =
        new Date(@json($calendarMonth) + '-01T00:00:00');

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    function normalize(value) {
        return String(value || '').trim();
    }

    function escapeHtml(value) {
        return String(value ?? '-')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

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

    function formatDate(dateString, options) {
        const date =
            new Date(dateString + 'T00:00:00');

        return new Intl.DateTimeFormat(
            'id-ID',
            options || {
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            }
        ).format(date);
    }

    function formatMonth(date) {
        return new Intl.DateTimeFormat(
            'id-ID',
            {
                month: 'long',
                year: 'numeric'
            }
        ).format(date);
    }

    function saveLocalAgendas() {
        try {
            localStorage.setItem(
                storageKey,
                JSON.stringify(agendas)
            );
        } catch (error) {
            console.warn('Agenda tidak dapat disimpan lokal.', error);
        }
    }

    function loadLocalAgendas() {
        try {
            const saved =
                JSON.parse(
                    localStorage.getItem(storageKey)
                );

            if (Array.isArray(saved) && saved.length) {
                agendas = saved;
            }
        } catch (error) {
            console.warn('Agenda lokal tidak dapat dibaca.', error);
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
            group.querySelector('.pd-menu-toggle');

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
                button.dataset.pdView === targetId
            );
        });

        const activeButton =
            document.querySelector(
                '[data-pd-view="' +
                CSS.escape(targetId) +
                '"]'
            );

        const parentGroup =
            activeButton?.closest('.pd-menu-group');

        if (parentGroup) {
            parentGroup.classList.add('is-open');

            const parentToggle =
                parentGroup.querySelector('.pd-menu-toggle');

            parentToggle?.setAttribute(
                'aria-expanded',
                'true'
            );
        }

        closeDrawer();

        document.querySelector('.pd-content')
            ?.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
    }

    viewButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            openView(button.dataset.pdView);
        });
    });

    openViewButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            openView(button.dataset.openView);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | CALENDAR
    |--------------------------------------------------------------------------
    */

    function findAgenda(id) {
        return agendas.find(function (agenda) {
            return String(agenda.id) === String(id);
        });
    }

    function renderCalendarGrid(grid) {
        if (!grid) {
            return;
        }

        const year =
            calendarDate.getFullYear();

        const month =
            calendarDate.getMonth();

        const firstDay =
            new Date(year, month, 1);

        const mondayIndex =
            (firstDay.getDay() + 6) % 7;

        const gridStart =
            new Date(year, month, 1 - mondayIndex);

        const today =
            new Date();

        const cells = [];

        for (let index = 0; index < 42; index += 1) {
            const cellDate =
                new Date(gridStart);

            cellDate.setDate(
                gridStart.getDate() + index
            );

            const isoDate = [
                cellDate.getFullYear(),
                String(cellDate.getMonth() + 1).padStart(2, '0'),
                String(cellDate.getDate()).padStart(2, '0')
            ].join('-');

            const dayAgendas =
                agendas.filter(function (agenda) {
                    return agenda.date === isoDate;
                });

            const isOutside =
                cellDate.getMonth() !== month;

            const isToday =
                cellDate.getFullYear() === today.getFullYear() &&
                cellDate.getMonth() === today.getMonth() &&
                cellDate.getDate() === today.getDate();

            const agendaButtons =
                dayAgendas
                    .slice(0, 3)
                    .map(function (agenda) {
                        return (
                            '<button' +
                                ' type="button"' +
                                ' class="pd-calendar-event"' +
                                ' data-agenda-id="' +
                                    escapeHtml(agenda.id) +
                                '"' +
                                ' data-category="' +
                                    escapeHtml(agenda.category) +
                                '"' +
                                ' title="' +
                                    escapeHtml(agenda.title) +
                                '"' +
                            '>' +
                                escapeHtml(agenda.start_time) +
                                ' ' +
                                escapeHtml(agenda.title) +
                            '</button>'
                        );
                    })
                    .join('');

            cells.push(
                '<div class="pd-calendar-day' +
                    (isOutside ? ' is-outside' : '') +
                    (isToday ? ' is-today' : '') +
                '">' +
                    '<span class="pd-day-number">' +
                        cellDate.getDate() +
                    '</span>' +
                    agendaButtons +
                '</div>'
            );
        }

        grid.innerHTML = cells.join('');
    }

    function renderCalendars() {
        calendarLabels.forEach(function (label) {
            label.textContent =
                formatMonth(calendarDate);
        });

        calendarGrids.forEach(renderCalendarGrid);
    }

    function renderUpcoming() {
        const sorted =
            [...agendas]
                .sort(function (a, b) {
                    return String(a.date)
                        .localeCompare(String(b.date));
                })
                .slice(0, 6);

        upcomingLists.forEach(function (list) {
            if (!sorted.length) {
                list.innerHTML =
                    '<div class="pd-empty-state">' +
                        'Belum ada agenda training.' +
                    '</div>';

                return;
            }

            list.innerHTML =
                sorted.map(function (agenda) {
                    const date =
                        new Date(agenda.date + 'T00:00:00');

                    const day =
                        String(date.getDate()).padStart(2, '0');

                    const month =
                        new Intl.DateTimeFormat(
                            'id-ID',
                            { month: 'short' }
                        ).format(date);

                    return (
                        '<button' +
                            ' type="button"' +
                            ' class="pd-upcoming-item"' +
                            ' data-agenda-id="' +
                                escapeHtml(agenda.id) +
                            '"' +
                        '>' +
                            '<span class="pd-date-box">' +
                                '<span>' +
                                    '<strong>' + day + '</strong>' +
                                    '<span>' + escapeHtml(month) + '</span>' +
                                '</span>' +
                            '</span>' +
                            '<span class="pd-upcoming-info">' +
                                '<strong>' +
                                    escapeHtml(agenda.title) +
                                '</strong>' +
                                '<small>' +
                                    escapeHtml(agenda.start_time) +
                                    '–' +
                                    escapeHtml(agenda.end_time) +
                                    ' · ' +
                                    escapeHtml(agenda.location) +
                                    '<br>' +
                                    escapeHtml(agenda.participants || 0) +
                                    '/' +
                                    escapeHtml(agenda.quota || 0) +
                                    ' peserta' +
                                '</small>' +
                            '</span>' +
                        '</button>'
                    );
                }).join('');
        });
    }

    function renderAgendaTable() {
        if (!agendaTableBody) {
            return;
        }

        const sorted =
            [...agendas]
                .sort(function (a, b) {
                    return String(a.date)
                        .localeCompare(String(b.date));
                });

        if (agendaCount) {
            agendaCount.textContent =
                sorted.length + ' agenda';
        }

        agendaTableBody.innerHTML =
            sorted.map(function (agenda) {
                const statusClass =
                    agenda.status === 'Terjadwal'
                        ? 'green'
                        : agenda.status === 'Draft'
                            ? 'gray'
                            : 'orange';

                return (
                    '<tr>' +
                        '<td>' +
                            escapeHtml(formatDate(agenda.date)) +
                        '</td>' +
                        '<td>' +
                            escapeHtml(agenda.title) +
                        '</td>' +
                        '<td>' +
                            '<span class="pd-badge">' +
                                escapeHtml(agenda.category) +
                            '</span>' +
                        '</td>' +
                        '<td>' +
                            escapeHtml(agenda.trainer) +
                        '</td>' +
                        '<td>' +
                            escapeHtml(agenda.participants || 0) +
                            '/' +
                            escapeHtml(agenda.quota || 0) +
                        '</td>' +
                        '<td>' +
                            '<span class="pd-badge ' +
                                statusClass +
                            '">' +
                                escapeHtml(agenda.status) +
                            '</span>' +
                        '</td>' +
                        '<td>' +
                            '<button' +
                                ' type="button"' +
                                ' class="pd-table-action"' +
                                ' data-agenda-id="' +
                                    escapeHtml(agenda.id) +
                                '"' +
                            '>' +
                                'Detail' +
                            '</button>' +
                        '</td>' +
                    '</tr>'
                );
            }).join('');
    }

    function renderAgendaUI() {
        renderCalendars();
        renderUpcoming();
        renderAgendaTable();
    }

    prevButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            calendarDate =
                new Date(
                    calendarDate.getFullYear(),
                    calendarDate.getMonth() - 1,
                    1
                );

            renderCalendars();
        });
    });

    nextButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            calendarDate =
                new Date(
                    calendarDate.getFullYear(),
                    calendarDate.getMonth() + 1,
                    1
                );

            renderCalendars();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | AGENDA DRAWER
    |--------------------------------------------------------------------------
    */

    function closeDrawer() {
        if (!drawer || !drawerBackdrop) {
            return;
        }

        drawer.classList.remove('is-open');
        drawerBackdrop.classList.remove('is-open');

        drawer.setAttribute('aria-hidden', 'true');
        drawerBackdrop.setAttribute('aria-hidden', 'true');
    }

    function openAgendaDrawer(agenda) {
        if (
            !agenda ||
            !drawer ||
            !drawerBackdrop ||
            !agendaDetail
        ) {
            return;
        }

        const detailRows = [
            ['Nama Training', agenda.title],
            ['Kategori', agenda.category],
            ['Tanggal', formatDate(agenda.date)],
            [
                'Waktu',
                agenda.start_time + '–' + agenda.end_time
            ],
            ['Lokasi', agenda.location],
            ['Trainer', agenda.trainer],
            ['Target Peserta', agenda.target_position],
            [
                'Kuota',
                (agenda.participants || 0) +
                '/' +
                (agenda.quota || 0) +
                ' peserta'
            ],
            ['Tujuan', agenda.objective],
            ['Kompetensi', agenda.competency],
            ['Status', agenda.status]
        ];

        agendaDetail.innerHTML =
            detailRows
                .map(function (row) {
                    return (
                        '<div class="pd-detail-row">' +
                            '<span>' +
                                escapeHtml(row[0]) +
                            '</span>' +
                            '<strong>' +
                                escapeHtml(row[1]) +
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

    document.addEventListener('click', function (event) {
        const agendaButton =
            event.target.closest('[data-agenda-id]');

        if (!agendaButton) {
            return;
        }

        const agenda =
            findAgenda(agendaButton.dataset.agendaId);

        openAgendaDrawer(agenda);
    });

    drawerClose?.addEventListener('click', closeDrawer);
    drawerBackdrop?.addEventListener('click', closeDrawer);

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeDrawer();
        }
    });

    /*
    |--------------------------------------------------------------------------
    | INPUT AGENDA
    |--------------------------------------------------------------------------
    */

    async function saveAgendaToBackend(agenda) {
        if (!agendaEndpoint) {
            return agenda;
        }

        const csrfToken =
            document.querySelector(
                'meta[name="csrf-token"]'
            )?.getAttribute('content');

        const response =
            await fetch(agendaEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    ...(csrfToken
                        ? { 'X-CSRF-TOKEN': csrfToken }
                        : {})
                },
                body: JSON.stringify(agenda)
            });

        if (!response.ok) {
            throw new Error(
                'Gagal menyimpan agenda ke backend.'
            );
        }

        return await response.json();
    }

    agendaForm?.addEventListener(
        'submit',
        async function (event) {
            event.preventDefault();

            if (!agendaForm.reportValidity()) {
                return;
            }

            const formData =
                new FormData(agendaForm);

            const agenda = {
                id: Date.now(),
                title: normalize(formData.get('title')),
                category: normalize(formData.get('category')),
                date: normalize(formData.get('date')),
                start_time: normalize(
                    formData.get('start_time')
                ),
                end_time: normalize(
                    formData.get('end_time')
                ),
                location: normalize(formData.get('location')),
                trainer: normalize(formData.get('trainer')),
                target_position: normalize(
                    formData.get('target_position')
                ),
                quota: Number(formData.get('quota')) || 0,
                participants: 0,
                objective: normalize(
                    formData.get('objective')
                ),
                competency: normalize(
                    formData.get('competency')
                ),
                status: normalize(formData.get('status'))
            };

            try {
                window.setPeopleDevelopmentLoading(true);

                const savedAgenda =
                    await saveAgendaToBackend(agenda);

                agendas.push({
                    ...agenda,
                    ...savedAgenda
                });

                saveLocalAgendas();
                renderAgendaUI();

                agendaForm.reset();
                showToast('Agenda training berhasil disimpan.');

                openView('pd-agenda-list');
            } catch (error) {
                console.error(error);
                showToast(error.message);
            } finally {
                window.setPeopleDevelopmentLoading(false);
            }
        }
    );

    /*
    |--------------------------------------------------------------------------
    | LOADING API
    |--------------------------------------------------------------------------
    */

    window.setPeopleDevelopmentLoading =
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
    | UPDATE DATA DARI API TANPA RELOAD
    |--------------------------------------------------------------------------
    */

    window.updatePeopleDevelopmentDashboard =
        function (payload) {
            if (Array.isArray(payload?.agendas)) {
                agendas = payload.agendas;
                saveLocalAgendas();
                renderAgendaUI();
            }
        };

    loadLocalAgendas();
    renderAgendaUI();
});
</script>
@endpush