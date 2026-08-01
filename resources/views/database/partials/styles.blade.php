<style>
:root {
    --db-sidebar-width: 220px;
    --db-sidebar-collapsed: 72px;
    --db-header-height: 64px;
    --db-footer-height: 28px;
    --db-bg: #f3f5f7;
    --db-surface: #ffffff;
    --db-border: #dce2e8;
    --db-text: #172033;
    --db-muted: #64748b;
    --db-black: #121212;
    --db-red: #e51d2a;
    --db-blue: #147df5;
    --db-green: #1da15d;
    --db-orange: #f59e0b;
    --db-shadow: 0 8px 22px rgba(15, 23, 42, .07);
}

* { box-sizing: border-box; }

body.syn-database-page {
    margin: 0;
    overflow: hidden;
    color: var(--db-text);
    background: var(--db-bg);
    font-family: Arial, Helvetica, sans-serif;
}

.db-page {
    display: grid;
    width: 100%;
    height: 100vh;
    grid-template-columns: var(--db-sidebar-width) minmax(0, 1fr);
    grid-template-rows: var(--db-header-height) minmax(0, 1fr) var(--db-footer-height);
    overflow: hidden;
}

.db-sidebar {
    display: flex;
    grid-row: 1 / 4;
    min-width: 0;
    flex-direction: column;
    border-right: 1px solid #c7ccd2;
    background: linear-gradient(180deg, #f2f2f2 0%, #dddddd 100%);
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
    overflow: hidden;
}

.db-sidebar-logo img {
    width: 76px;
    height: 52px;
    object-fit: contain;
}

.db-sidebar-toggle {
    border: 0;
    border-left: 1px solid #666;
    color: #151515;
    background: #fff;
    cursor: pointer;
    font-size: 28px;
}

.db-navigation {
    min-height: 0;
    flex: 1;
    padding: 10px 0;
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
    color: #111;
    background: transparent;
    cursor: pointer;
    font-size: 13px;
    font-weight: 800;
    text-align: left;
    text-decoration: none;
}

.db-menu-link:hover,
.db-menu-toggle:hover,
.db-menu-link.active,
.db-menu-group.is-open > .db-menu-toggle {
    background: rgba(255,255,255,.82);
}

.db-menu-link.active,
.db-submenu-link.active {
    border-left: 4px solid var(--db-red);
}

.db-menu-icon {
    display: grid;
    width: 23px;
    height: 23px;
    flex: 0 0 23px;
    place-items: center;
}

.db-menu-icon img {
    width: 21px;
    height: 21px;
    object-fit: contain;
}

.db-menu-label { min-width: 0; flex: 1; }

.db-menu-arrow {
    transition: transform .18s ease;
}

.db-menu-group.is-open .db-menu-arrow {
    transform: rotate(90deg);
}

.db-submenu {
    display: grid;
    grid-template-rows: 0fr;
    transition: grid-template-rows .2s ease;
}

.db-menu-group.is-open .db-submenu {
    grid-template-rows: 1fr;
}

.db-submenu-inner {
    min-height: 0;
    overflow: hidden;
}

.db-submenu-link {
    display: flex;
    min-height: 35px;
    align-items: center;
    gap: 9px;
    padding: 8px 12px 8px 40px;
    border-left: 4px solid transparent;
    color: #111;
    font-size: 11px;
    font-weight: 700;
    line-height: 1.2;
    text-decoration: none;
    transition:
        color .16s ease,
        background .16s ease,
        border-color .16s ease;
}

.db-submenu-icon {
    display: inline-grid;
    width: 16px;
    height: 16px;
    flex: 0 0 16px;
    place-items: center;
    color: #687385;
    transition:
        color .16s ease,
        transform .16s ease;
}

.db-submenu-icon svg {
    display: block;
    width: 15px;
    height: 15px;
    stroke: currentColor;
    stroke-width: 1.9;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.db-submenu-text {
    min-width: 0;
    flex: 1;
}

.db-submenu-link:hover,
.db-submenu-link.active {
    color: #111827;
    background: rgba(255,255,255,.82);
}

.db-submenu-link:hover .db-submenu-icon,
.db-submenu-link.active .db-submenu-icon {
    color: var(--db-red);
    transform: translateX(1px);
}

.db-sidebar-bottom {
    padding: 10px 15px 14px;
}

.db-bottom-link {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 7px 0;
    color: #111;
    font-size: 11px;
    font-weight: 800;
    text-decoration: none;
}

.db-bottom-link.help { color: #c40000; }

.db-header {
    display: grid;
    min-width: 0;
    grid-template-columns: minmax(0, 1fr) auto;
    border-bottom: 1px solid #dce2e8;
    background: #ffffff;
}

.db-header-brand {
    position: relative;
    display: flex;
    min-width: 0;
    overflow: hidden;
    align-items: center;
    justify-content: center;
    padding: 0 18px;
    background: linear-gradient(
        90deg,
        #1b1b1b 0%,
        #2d2d2d 48%,
        #4b2424 76%,
        #d95d20 100%
    );
}

.db-header-brand::after {
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

.db-header-brand img {
    position: relative;
    z-index: 1;
    display: block;
    width: 125px;
    max-height: 45px;
    object-fit: contain;
    filter: drop-shadow(
        0 3px 7px rgba(0, 0, 0, 0.35)
    );
}

.db-header-actions {
    display: flex;
    min-width: max-content;
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
    overflow: hidden;
    padding: 0;
    border: 2px solid #111111;
    border-radius: 50%;
    color: #111111;
    background: #ffffff;
    box-shadow: none;
    cursor: pointer;
    text-decoration: none;
    transition:
        transform 0.18s ease,
        border-color 0.18s ease,
        box-shadow 0.18s ease;
}

.db-header-button:hover {
    transform: translateY(-2px);
    border-color: #333333;
    box-shadow: 0 7px 16px rgba(0, 0, 0, 0.14);
}

.db-home-icon {
    width: 72% !important;
    height: 72% !important;
    border-radius: 50%;
    object-fit: contain !important;
}

.db-logout-button {
    border-color: #c71922;
}

.db-logout-button img {
    width: 100% !important;
    height: 100% !important;
    border-radius: 50%;
    object-fit: cover !important;
}

.db-logout-form {
    display: flex;
    margin: 0;
}

/*
 * Samakan komponen shortcut lintas modul dengan header Manpower.
 */
.db-header-actions .module-shortcut {
    display: flex;
    flex: 0 0 44px;
    align-items: center;
}

.db-header-actions .module-shortcut-trigger {
    width: 44px;
    height: 44px;
    min-width: 44px;
    flex: 0 0 44px;
    border: 2px solid #111111;
    border-radius: 10px;
    background: #ffffff;
    box-shadow: none;
}

/*
 * Samakan dropdown profil dengan tombol header Manpower.
 */
.db-header-actions .syn-profile-wrapper {
    flex: 0 0 44px;
}

.db-header-actions .syn-profile-trigger {
    width: 44px;
    height: 44px;
    min-width: 44px;
    flex: 0 0 44px;
    padding: 0;
    border: 2px solid #111111;
    border-radius: 50%;
    background: #ffffff;
    box-shadow: none;
}

.db-header-actions .syn-profile-trigger img {
    width: 100% !important;
    height: 100% !important;
    border-radius: 50%;
    object-fit: cover !important;
}

.db-content {
    position: relative;
    min-width: 0;
    min-height: 0;
    padding: 12px;
    overflow: auto;
    background: var(--db-bg);
}

.db-footer {
    display: grid;
    place-items: center;
    color: #fff;
    background: #333;
    font-size: 9px;
    font-weight: 800;
}

.db-page.sidebar-collapsed {
    grid-template-columns: var(--db-sidebar-collapsed) minmax(0, 1fr);
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

.db-page.sidebar-collapsed .db-menu-link,
.db-page.sidebar-collapsed .db-menu-toggle {
    justify-content: center;
    padding-inline: 0;
}

.db-page-title {
    margin-bottom: 10px;
}

.db-page-title h1 {
    margin: 0 0 4px;
    font-size: 20px;
    font-weight: 900;
}

.db-page-title p {
    margin: 0;
    color: var(--db-muted);
    font-size: 11px;
}

.db-panel,
.db-kpi-card,
.db-table-card {
    border: 1px solid var(--db-border);
    border-radius: 12px;
    background: var(--db-surface);
    box-shadow: var(--db-shadow);
}

.db-panel { padding: 13px; margin-bottom: 11px; }

.db-filter-grid {
    display: grid;
    grid-template-columns: minmax(220px, 1fr) 220px auto;
    gap: 10px;
    align-items: end;
}

.db-field {
    display: flex;
    min-width: 0;
    flex-direction: column;
    gap: 6px;
}

.db-field label {
    font-size: 10px;
    font-weight: 800;
}

.db-input,
.db-select {
    width: 100%;
    height: 38px;
    padding: 0 12px;
    border: 1px solid #cfd6de;
    border-radius: 8px;
    background: #fff;
    outline: none;
}

.db-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.db-button {
    display: inline-flex;
    min-height: 38px;
    align-items: center;
    justify-content: center;
    padding: 0 14px;
    border: 0;
    border-radius: 8px;
    color: #fff;
    background: var(--db-blue);
    cursor: pointer;
    font-size: 10px;
    font-weight: 900;
    text-decoration: none;
}

.db-button.secondary { color: #172033; background: #fff; border: 1px solid #cfd6de; }
.db-button.dark { background: #374151; }
.db-button.green { background: var(--db-green); }
.db-button.red { background: var(--db-red); }
.db-button.purple { background: #7c3aed; }

.db-kpi-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 11px;
}

.db-kpi-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
}

.db-kpi-icon {
    display: grid;
    width: 44px;
    height: 44px;
    flex: 0 0 44px;
    place-items: center;
    border-radius: 10px;
    color: #fff;
    background: #27344a;
    font-size: 20px;
}

.db-kpi-card strong {
    display: block;
    margin-top: 3px;
    font-size: 25px;
}

.db-kpi-card small {
    color: var(--db-muted);
    font-size: 9px;
    font-weight: 800;
}

.db-table-card { overflow: hidden; }

.db-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 13px;
    border-bottom: 1px solid var(--db-border);
}

.db-card-header h2 {
    margin: 0 0 3px;
    font-size: 13px;
}

.db-card-header small {
    color: var(--db-muted);
    font-size: 9px;
}

.db-table-wrap {
    overflow: auto;
}

.db-table {
    width: 100%;
    min-width: 900px;
    border-collapse: collapse;
}

.db-table th,
.db-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #e5e7eb;
    font-size: 10px;
    text-align: left;
}

.db-table th {
    position: sticky;
    z-index: 2;
    top: 0;
    color: #334155;
    background: #f8fafc;
    font-size: 8px;
    text-transform: uppercase;
}

.db-badge {
    display: inline-flex;
    min-height: 21px;
    align-items: center;
    padding: 2px 8px;
    border-radius: 999px;
    font-size: 8px;
    font-weight: 900;
}

.db-badge.green { color: #087a45; background: #dcfce7; }
.db-badge.red { color: #b91c1c; background: #fee2e2; }
.db-badge.orange { color: #9a5b00; background: #fef3c7; }
.db-badge.blue { color: #0756b8; background: #dbeafe; }

.db-grid-2 {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 11px;
}

.db-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 11px;
}

.atr-stat-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 9px;
}

.atr-stat {
    padding: 15px;
    border: 1px solid var(--db-border);
    border-top: 3px solid var(--db-blue);
    border-radius: 10px;
    background: #fff;
    text-align: center;
}

.atr-stat strong {
    display: block;
    font-size: 23px;
}

.atr-stat small {
    color: var(--db-muted);
    font-size: 8px;
    font-weight: 900;
    text-transform: uppercase;
}

.atr-progress-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    text-align: center;
}

.atr-track {
    height: 7px;
    margin-top: 10px;
    overflow: hidden;
    border-radius: 999px;
    background: #e2e8f0;
}

.atr-bar {
    height: 100%;
    border-radius: inherit;
    background: var(--db-green);
}

.db-upload-zone {
    display: grid;
    min-height: 190px;
    place-items: center;
    padding: 20px;
    border: 2px dashed #cbd5e1;
    border-radius: 14px;
    color: var(--db-muted);
    background: #f8fafc;
    text-align: center;
}

.db-upload-zone strong {
    display: block;
    margin-bottom: 7px;
    color: var(--db-text);
    font-size: 16px;
}

.db-empty {
    padding: 30px;
    color: var(--db-muted);
    text-align: center;
    font-size: 11px;
    font-weight: 700;
}

.db-call-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
}

.db-call-card {
    overflow: hidden;
    border: 1px solid var(--db-border);
    border-radius: 11px;
    background: #fff;
}

.db-call-body { padding: 13px; }

.db-call-action {
    display: block;
    padding: 9px;
    color: #fff;
    background: var(--db-red);
    font-size: 9px;
    font-weight: 900;
    text-align: center;
}

.db-call-action.done {
    color: #087a45;
    background: #dcfce7;
}

.db-loading-layer {
    position: absolute;
    z-index: 5000;
    inset: 0;
    display: none;
    place-items: center;
    background: rgba(243,245,247,.8);
    backdrop-filter: blur(2px);
}

.db-loading-layer.is-visible { display: grid; }

.db-loading-box {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 18px;
    border-radius: 12px;
    background: #fff;
    box-shadow: var(--db-shadow);
    font-size: 11px;
    font-weight: 800;
}

.db-spinner {
    width: 22px;
    height: 22px;
    border: 3px solid #dbeafe;
    border-top-color: var(--db-blue);
    border-radius: 50%;
    animation: db-spin .8s linear infinite;
}

@keyframes db-spin {
    to { transform: rotate(360deg); }
}

@media (max-width: 1100px) {
    .db-kpi-grid,
    .atr-stat-grid,
    .db-call-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .db-filter-grid,
    .db-grid-2,
    .db-grid-3 {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 760px) {
    .db-page {
        grid-template-columns: var(--db-sidebar-collapsed) minmax(0, 1fr);
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

    .db-menu-link,
    .db-menu-toggle {
        justify-content: center;
        padding-inline: 0;
    }

    .db-kpi-grid,
    .atr-stat-grid,
    .db-call-grid {
        grid-template-columns: 1fr;
    }
}

/* ================================================================
   ATR STICKY UI
   ================================================================ */

.atr-sticky-zone {
    position: sticky;
    z-index: 35;
    top: 0;
    margin: 0 -2px 11px;
    padding: 2px;
    background: var(--db-bg);
    box-shadow:
        0 10px 18px -18px rgba(15, 23, 42, .68);
}

.atr-sticky-zone > :last-child {
    margin-bottom: 0;
}

/* Ringkasan ATR: filter dan badge statistik tetap terlihat. */
.atr-summary-sticky .atr-stat-grid {
    grid-template-columns: repeat(6, minmax(0, 1fr));
}

/* Dokumentasi pemanggilan: filter tetap terlihat. */
.atr-call-filter-sticky {
    z-index: 38;
}

/* Pengaturan PIC: badge KPI tetap terlihat. */
.pic-kpi-sticky {
    z-index: 37;
}

.pic-kpi-sticky .pic-kpi-grid {
    margin-bottom: 0;
}

/* Riwayat dan ranking ATR memakai area scroll tersendiri. */
.atr-table-scroll {
    position: relative;
    overflow: auto;
    overscroll-behavior: contain;
    scrollbar-gutter: stable;
}

.atr-ranking-scroll {
    height: clamp(
        260px,
        calc(100vh - 390px),
        560px
    );
}

.atr-history-scroll {
    height: clamp(
        340px,
        calc(100vh - 235px),
        680px
    );
}

.atr-table-scroll .db-table th {
    position: sticky;
    z-index: 12;
    top: 0;
    background: #f8fafc;
    box-shadow:
        0 1px 0 #dce2e8,
        0 5px 12px rgba(15, 23, 42, .05);
}

/* Kolom pertama tetap terlihat ketika tabel ATR digeser horizontal. */
.atr-table-scroll .db-table th:first-child,
.atr-table-scroll .db-table td:first-child {
    position: sticky;
    left: 0;
    min-width: 74px;
}

.atr-table-scroll .db-table tbody td:first-child {
    z-index: 6;
    background: #fff;
}

.atr-table-scroll .db-table thead th:first-child {
    z-index: 18;
    background: #f8fafc;
}

.atr-table-scroll .db-table tbody tr:hover td,
.atr-table-scroll .db-table tbody tr:hover td:first-child {
    background: #f8fbff;
}

/* Preview upload belum berupa tabel pada Fase 1. */
.atr-upload-preview-card {
    scroll-margin-top: 12px;
}

@media (max-width: 1350px) {
    .atr-summary-sticky .atr-stat-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .atr-ranking-scroll {
        height: clamp(
            250px,
            calc(100vh - 520px),
            480px
        );
    }
}

@media (max-width: 1100px) {
    .atr-sticky-zone {
        position: static;
    }

    .atr-table-scroll,
    .pic-table-wrap {
        height: 55vh !important;
        min-height: 300px;
    }
}

@media (max-width: 760px) {
    .atr-summary-sticky .atr-stat-grid {
        grid-template-columns: 1fr;
    }
}

</style>