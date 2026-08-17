@extends('admin-all.layout')

@section('title', 'Input Ticket IFUTS')

@section('admin-content')
@php
    $ifutsSpreadsheetUrl = 'https://docs.google.com/spreadsheets/d/110H1XSrSOyj_PjphSlruUv3B5EVHVXNgOSoP0ZVVO84/edit?pli=1&gid=2129255501#gid=2129255501';
    $ifutsAccessEmail = 'intanputriutamippa@gmail.com';
@endphp

<style>
    #adminAllShell .aa-main {
        min-height: 0 !important;
        overflow: hidden !important;
    }

    #adminAllShell .aa-content {
        width: 100% !important;
        max-width: none !important;
        height: 100% !important;
        min-height: 0 !important;
        margin: 0 !important;
        overflow: hidden !important;
    }

    .ifi-v1-page {
        display: flex;
        width: 100%;
        height: 100%;
        min-height: 0;
        flex-direction: column;
        overflow: hidden;
    }

    .ifi-v1-head {
        display: flex;
        flex: 0 0 auto;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 7px;
    }

    .ifi-v1-title {
        margin: 0;
        color: #0d2c59;
        font-size: clamp(20px, 1.8vw, 26px);
        font-weight: 900;
        letter-spacing: -.03em;
        line-height: 1.05;
    }

    .ifi-v1-head-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 6px;
    }

    .ifi-v1-top-btn {
        display: inline-flex;
        min-height: 30px;
        align-items: center;
        justify-content: center;
        padding: 0 10px;
        border: 1px solid #cfd8e2;
        border-radius: 7px;
        color: #172b43;
        background: #fff;
        font-size: 8px;
        font-weight: 900;
        text-decoration: none;
        text-transform: uppercase;
        white-space: nowrap;
        transition: .15s ease;
    }

    .ifi-v1-top-btn:hover {
        color: #172b43;
        text-decoration: none;
        transform: translateY(-1px);
        box-shadow: 0 5px 14px rgba(31, 47, 65, .08);
    }

    .ifi-v1-stage {
        position: relative;
        min-height: 0;
        flex: 1 1 auto;
        display: grid;
        place-items: center;
        padding: 24px;
        border: 1px solid #d9e0e7;
        border-radius: 10px;
        background:
            radial-gradient(circle at 7% 10%, rgba(52, 168, 83, .07), transparent 23%),
            linear-gradient(180deg, #fbfcfe 0%, #f5f8fb 100%);
        overflow: auto;
    }

    .ifi-v1-card {
        width: min(920px, 96%);
        overflow: hidden;
        border: 1px solid #d7dfe8;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 16px 42px rgba(27, 43, 63, .10);
    }

    .ifi-v1-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 20px;
        border-bottom: 1px solid #e5eaf0;
        background: linear-gradient(135deg, #ffffff 0%, #f8fbf9 100%);
    }

    .ifi-v1-brand {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 14px;
    }

    .ifi-v1-sheet-logo {
        flex: 0 0 auto;
        display: grid;
        width: 62px;
        height: 62px;
        place-items: center;
        border: 1px solid #cfe8d7;
        border-radius: 14px;
        background: #eaf7ee;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,.55);
    }

    .ifi-v1-sheet-logo svg {
        display: block;
        width: 36px;
        height: 36px;
    }

    .ifi-v1-brand-copy {
        min-width: 0;
    }

    .ifi-v1-brand-copy h2 {
        margin: 0 0 5px;
        color: #172b43;
        font-size: 16px;
        font-weight: 900;
        letter-spacing: -.01em;
        line-height: 1.2;
    }

    .ifi-v1-brand-copy p {
        margin: 0;
        color: #748295;
        font-size: 9px;
        font-weight: 700;
        line-height: 1.55;
    }

    .ifi-v1-badges {
        display: flex;
        flex: 0 0 auto;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 6px;
    }

    .ifi-v1-badge {
        display: inline-flex;
        min-height: 23px;
        align-items: center;
        padding: 0 8px;
        border-radius: 999px;
        color: #405267;
        background: #eef2f6;
        font-size: 7px;
        font-weight: 900;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .ifi-v1-badge.green {
        color: #176a38;
        background: #e6f6eb;
    }

    .ifi-v1-badge.blue {
        color: #0d63b7;
        background: #e8f3ff;
    }

    .ifi-v1-card-body {
        padding: 18px 20px 20px;
    }

    .ifi-v1-info-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.6fr) minmax(270px, .8fr);
        gap: 10px;
    }

    .ifi-v1-panel {
        padding: 13px 14px;
        border: 1px solid #e1e7ed;
        border-radius: 10px;
        background: #fbfcfe;
    }

    .ifi-v1-label {
        display: block;
        margin-bottom: 5px;
        color: #758294;
        font-size: 7px;
        font-weight: 900;
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    .ifi-v1-text {
        margin: 0;
        color: #314359;
        font-size: 9px;
        font-weight: 700;
        line-height: 1.7;
    }

    .ifi-v1-access {
        border-color: #cfe8d7;
        background: #f3fbf5;
    }

    .ifi-v1-email {
        margin-top: 2px;
        color: #1f5131;
        font-size: 11px;
        font-weight: 900;
        overflow-wrap: anywhere;
    }

    .ifi-v1-access-note {
        margin-top: 5px;
        color: #5f7767;
        font-size: 8px;
        font-weight: 700;
        line-height: 1.5;
    }

    .ifi-v1-warning {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        margin-top: 10px;
        padding: 10px 12px;
        border: 1px solid #efddb2;
        border-radius: 10px;
        background: #fffaf0;
    }

    .ifi-v1-warning-icon {
        flex: 0 0 auto;
        display: grid;
        width: 22px;
        height: 22px;
        place-items: center;
        border-radius: 50%;
        color: #8b631d;
        background: #f7e7ba;
        font-size: 10px;
        font-weight: 900;
    }

    .ifi-v1-warning p {
        margin: 0;
        color: #70572c;
        font-size: 8px;
        font-weight: 700;
        line-height: 1.6;
    }

    .ifi-v1-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 15px;
        padding-top: 14px;
        border-top: 1px solid #e8edf2;
    }

    .ifi-v1-btn {
        display: inline-flex;
        min-height: 36px;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 0 14px;
        border-radius: 8px;
        font-size: 8px;
        font-weight: 900;
        text-decoration: none;
        text-transform: uppercase;
        transition: .15s ease;
    }

    .ifi-v1-btn.secondary {
        border: 1px solid #d6dee7;
        color: #526174;
        background: #fff;
    }

    .ifi-v1-btn.primary {
        border: 1px solid #188038;
        color: #fff;
        background: #188038;
        box-shadow: 0 5px 15px rgba(24, 128, 56, .18);
    }

    .ifi-v1-btn:hover {
        text-decoration: none;
        transform: translateY(-1px);
    }

    .ifi-v1-btn.primary:hover {
        color: #fff;
        border-color: #126b2e;
        background: #126b2e;
    }

    .ifi-v1-btn.secondary:hover {
        color: #172b43;
        border-color: #c5ced8;
        background: #f8fafc;
    }

    .ifi-v1-btn svg {
        display: block;
        width: 15px;
        height: 15px;
    }

    @media (max-width: 850px) {
        .ifi-v1-card-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .ifi-v1-badges {
            justify-content: flex-start;
        }

        .ifi-v1-info-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .ifi-v1-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .ifi-v1-head-actions {
            justify-content: flex-start;
        }

        .ifi-v1-stage {
            place-items: start stretch;
            padding: 10px;
        }

        .ifi-v1-card {
            width: 100%;
        }

        .ifi-v1-card-head,
        .ifi-v1-card-body {
            padding: 14px;
        }

        .ifi-v1-sheet-logo {
            width: 52px;
            height: 52px;
        }

        .ifi-v1-actions {
            align-items: stretch;
            flex-direction: column-reverse;
        }

        .ifi-v1-btn {
            width: 100%;
        }
    }
</style>

<div class="ifi-v1-page">
    <div class="ifi-v1-head">
        <h1 class="ifi-v1-title">INPUT TICKET IFUTS</h1>

        <div class="ifi-v1-head-actions">
            <a href="{{ route('admin-all.ifuts.index') }}" class="ifi-v1-top-btn">
                Dashboard IFUTS
            </a>

            <a href="{{ route('admin-all.ifuts.monitoring') }}" class="ifi-v1-top-btn">
                Monitoring Ticket
            </a>
        </div>
    </div>

    <div class="ifi-v1-stage">
        <section class="ifi-v1-card">
            <div class="ifi-v1-card-head">
                <div class="ifi-v1-brand">
                    <div class="ifi-v1-sheet-logo" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M6.5 2.5H14.7L19.5 7.3V20.1C19.5 20.87 18.87 21.5 18.1 21.5H6.5C5.73 21.5 5.1 20.87 5.1 20.1V3.9C5.1 3.13 5.73 2.5 6.5 2.5Z" fill="#188038"/>
                            <path d="M14.6 2.5V6.35C14.6 6.93 15.07 7.4 15.65 7.4H19.5" fill="#34A853"/>
                            <path d="M14.6 2.5V6.35C14.6 6.93 15.07 7.4 15.65 7.4H19.5" stroke="#FFFFFF" stroke-opacity=".45" stroke-width=".7"/>
                            <path d="M8 10.2H16.6M8 13.2H16.6M8 16.2H16.6M10.85 9.1V17.3M13.75 9.1V17.3" stroke="white" stroke-width="1.05" stroke-linecap="round"/>
                        </svg>
                    </div>

                    <div class="ifi-v1-brand-copy">
                        <h2>Google Spreadsheet IFUTS — General Affair</h2>
                        <p>
                            Media resmi input dan revisi ticket IFUTS melalui
                            Google Spreadsheet yang dikelola General Affair.
                        </p>
                    </div>
                </div>

                <div class="ifi-v1-badges">
                    <span class="ifi-v1-badge green">Departemen Produksi</span>
                    <span class="ifi-v1-badge blue">Spreadsheet GA</span>
                    <span class="ifi-v1-badge">V1.0</span>
                </div>
            </div>

            <div class="ifi-v1-card-body">
                <div class="ifi-v1-info-grid">
                    <div class="ifi-v1-panel">
                        <span class="ifi-v1-label">Informasi Input & Revisi</span>
                        <p class="ifi-v1-text">
                            Pada SYNRGYPRO v1.0, proses input ticket, reschedule,
                            perubahan tanggal, perubahan rute, dan revisi data IFUTS
                            masih dilakukan langsung pada Google Spreadsheet resmi
                            General Affair. Dashboard dan Monitoring IFUTS di website
                            digunakan untuk membantu pemantauan data Produksi.
                        </p>
                    </div>

                    <div class="ifi-v1-panel ifi-v1-access">
                        <span class="ifi-v1-label">Akun yang memiliki akses</span>
                        <div class="ifi-v1-email">{{ $ifutsAccessEmail }}</div>
                        <div class="ifi-v1-access-note">
                            Gunakan akun tersebut saat membuka dan melakukan perubahan data.
                        </div>
                    </div>
                </div>

                <div class="ifi-v1-warning">
                    <div class="ifi-v1-warning-icon">!</div>
                    <p>
                        Pastikan Google Chrome sudah login menggunakan akun yang memiliki akses.
                        Jika Spreadsheet tidak dapat dibuka atau muncul permintaan izin,
                        silakan konfirmasi kepada tim General Affair.
                    </p>
                </div>

                <div class="ifi-v1-actions">
                    <a href="{{ route('admin-all.ifuts.index') }}" class="ifi-v1-btn secondary">
                        Kembali ke Dashboard
                    </a>

                    <a
                        href="{{ $ifutsSpreadsheetUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="ifi-v1-btn primary"
                    >
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M6.5 2.5H14.7L19.5 7.3V20.1C19.5 20.87 18.87 21.5 18.1 21.5H6.5C5.73 21.5 5.1 20.87 5.1 20.1V3.9C5.1 3.13 5.73 2.5 6.5 2.5Z" fill="currentColor" opacity=".25"/>
                            <path d="M8 10.2H16.6M8 13.2H16.6M8 16.2H16.6M10.85 9.1V17.3M13.75 9.1V17.3" stroke="currentColor" stroke-width="1.15" stroke-linecap="round"/>
                        </svg>
                        Buka Spreadsheet IFUTS
                    </a>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection