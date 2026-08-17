@extends('admin-all.layout')

@section('title', 'Detail Ticket IFUTS')

@section('admin-content')
@php
    $ticket = $ticket ?? [];

    $returnQuery = request()->only([
        'year',
        'month',
        'category',
        'position',
        'poh',
        'search',
        'per_page',
        'page',
    ]);

    $backUrl = route('admin-all.ifuts.monitoring');

    if ($returnQuery !== []) {
        $backUrl .= '?'.http_build_query($returnQuery);
    }

    $sheetRow = (int) ($ticket['_SHEET_ROW'] ?? $sheetRow ?? 0);

    $spreadsheetRowUrl =
        'https://docs.google.com/spreadsheets/d/110H1XSrSOyj_PjphSlruUv3B5EVHVXNgOSoP0ZVVO84/edit'
        .'?pli=1#gid=2129255501&range=A'.$sheetRow;

    $display = static function (mixed $value): string {
        $value = trim((string) $value);
        return $value !== '' ? $value : '-';
    };

    $outType = strtoupper(trim((string) ($ticket['KET_TIKET_OUT'] ?? '')));
    $inType = strtoupper(trim((string) ($ticket['KET_TIKET_IN'] ?? '')));

    $hasOutOperational =
        trim((string) ($ticket['TIME_TAKE_OFF_OUT'] ?? '')) !== ''
        || trim((string) ($ticket['TIME_LANDING_OUT'] ?? '')) !== ''
        || trim((string) ($ticket['ESTIMASI_BIAYA_OUT'] ?? '')) !== ''
        || trim((string) ($ticket['MASKAPAI_OUT'] ?? '')) !== '';

    $hasInOperational =
        trim((string) ($ticket['TIME_TAKE_OFF_IN'] ?? '')) !== ''
        || trim((string) ($ticket['TIME_LANDING_IN'] ?? '')) !== ''
        || trim((string) ($ticket['ESTIMASI_BIAYA_IN'] ?? '')) !== ''
        || trim((string) ($ticket['MASKAPAI_IN'] ?? '')) !== '';

    $noteText = trim((string) ($ticket['NOTE'] ?? ''));
    $hasNote = $noteText !== '';
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

    .ifd-page {
        display: flex;
        width: 100%;
        height: 100%;
        min-height: 0;
        flex-direction: column;
        overflow: hidden;
    }

    .ifd-head {
        display: flex;
        flex: 0 0 auto;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 6px;
    }

    .ifd-title-wrap { min-width: 0; }

    .ifd-title {
        margin: 0;
        color: #0d2c59;
        font-size: clamp(20px, 1.75vw, 25px);
        font-weight: 900;
        letter-spacing: -.03em;
        line-height: 1.05;
    }

    .ifd-subtitle {
        margin-top: 3px;
        color: #7a8796;
        font-size: 8px;
        font-weight: 800;
    }

    .ifd-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 6px;
    }

    .ifd-btn {
        display: inline-flex;
        min-height: 29px;
        align-items: center;
        justify-content: center;
        gap: 6px;
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

    .ifd-btn:hover {
        color: #172b43;
        text-decoration: none;
        transform: translateY(-1px);
        box-shadow: 0 5px 14px rgba(31, 47, 65, .08);
    }

    .ifd-btn.primary {
        color: #fff;
        border-color: #188038;
        background: #188038;
    }

    .ifd-btn.primary:hover {
        color: #fff;
        background: #126b2e;
    }

    .ifd-status {
        display: flex;
        flex: 0 0 auto;
        min-height: 29px;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 6px;
        padding: 5px 9px;
        border: 1px solid #b7e6d0;
        border-radius: 8px;
        color: #11643d;
        background: #effff7;
        font-size: 8px;
        font-weight: 800;
    }

    .ifd-status.error {
        color: #a72632;
        border-color: #f0c4c8;
        background: #fff2f3;
    }

    .ifd-status-left,
    .ifd-badges {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
    }

    .ifd-dot {
        width: 8px;
        height: 8px;
        flex: 0 0 8px;
        border-radius: 50%;
        background: currentColor;
        box-shadow: 0 0 0 3px rgba(17, 100, 61, .10);
    }

    .ifd-badge {
        display: inline-flex;
        min-height: 20px;
        align-items: center;
        padding: 0 7px;
        border-radius: 999px;
        color: #405267;
        background: #fff;
        font-size: 7px;
        font-weight: 900;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .ifd-badge.blue {
        color: #0d63b7;
        background: #e8f3ff;
    }

    .ifd-body {
        min-height: 0;
        flex: 1 1 auto;
        overflow: auto;
        padding-right: 2px;
        scrollbar-gutter: stable;
    }

    .ifd-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) repeat(3, minmax(145px, .55fr));
        gap: 7px;
        margin-bottom: 7px;
    }

    .ifd-hero-card {
        min-width: 0;
        padding: 9px 10px;
        border: 1px solid #dce3ea;
        border-radius: 9px;
        background: #fff;
        box-shadow: 0 3px 12px rgba(31, 47, 65, .035);
    }

    .ifd-hero-card.name {
        border-left: 4px solid #09879a;
    }

    .ifd-label {
        display: block;
        margin-bottom: 3px;
        color: #7a8796;
        font-size: 7px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .ifd-value {
        color: #172b43;
        font-size: 9px;
        font-weight: 900;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .ifd-hero-card.name .ifd-value {
        font-size: 12px;
    }

    .ifd-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 7px;
    }

    .ifd-section {
        overflow: hidden;
        border: 1px solid #dce3ea;
        border-radius: 9px;
        background: #fff;
        box-shadow: 0 3px 12px rgba(31, 47, 65, .035);
    }

    .ifd-section.full { grid-column: span 2; }

    .ifd-section-head {
        display: flex;
        min-height: 31px;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 6px 9px;
        border-bottom: 1px solid #e6ebf0;
        background: #f8fafc;
    }

    .ifd-section-title {
        color: #172b43;
        font-size: 8px;
        font-weight: 900;
        text-transform: uppercase;
    }

    /* STEP 4.2 — semantic color polish */
    .ifd-section-employee {
        border-top: 3px solid #0f9ba8;
    }

    .ifd-section-employee .ifd-section-head {
        background: #ecfbfc;
        border-bottom-color: #cdeff2;
    }

    .ifd-section-employee .ifd-section-title {
        color: #0b6f79;
    }

    .ifd-section-info {
        border-top: 3px solid #d4a017;
    }

    .ifd-section-info .ifd-section-head {
        background: #fff9e8;
        border-bottom-color: #f3e2aa;
    }

    .ifd-section-info .ifd-section-title {
        color: #8b6510;
    }

    .ifd-section-out {
        border-top: 3px solid #2f73d9;
    }

    .ifd-section-out .ifd-section-head {
        background: #eef5ff;
        border-bottom-color: #d5e5fb;
    }

    .ifd-section-out .ifd-section-title {
        color: #1f5fb9;
    }

    .ifd-section-in {
        border-top: 3px solid #2f9d62;
    }

    .ifd-section-in .ifd-section-head {
        background: #edf9f2;
        border-bottom-color: #d2ecdc;
    }

    .ifd-section-in .ifd-section-title {
        color: #237849;
    }

    .ifd-section-note {
        border-top: 3px solid #7b8794;
    }

    .ifd-section-note .ifd-section-head {
        background: #f3f5f7;
        border-bottom-color: #e1e6eb;
    }

    .ifd-section-note .ifd-section-title {
        color: #52606d;
    }

    /* STEP 4.3 — final UX polish */
    .ifd-title-icon,
    .ifd-section-icon {
        display:inline-grid;
        flex:0 0 auto;
        place-items:center;
    }

    .ifd-title-icon {
        width:18px;
        height:18px;
        margin-right:5px;
        vertical-align:-3px;
    }

    .ifd-section-title {
        display:inline-flex;
        align-items:center;
        gap:5px;
    }

    .ifd-section-icon {
        width:14px;
        height:14px;
    }

    .ifd-section-icon svg,
    .ifd-title-icon svg {
        display:block;
        width:100%;
        height:100%;
    }

    .ifd-person-line {
        display:flex;
        flex-wrap:wrap;
        align-items:center;
        gap:6px;
        margin-top:5px;
    }

    .ifd-mini-badge {
        display:inline-flex;
        min-height:20px;
        align-items:center;
        gap:4px;
        padding:0 7px;
        border:1px solid #dbe3ea;
        border-radius:999px;
        color:#526174;
        background:#f8fafc;
        font-size:7px;
        font-weight:900;
        text-transform:uppercase;
        white-space:nowrap;
    }

    .ifd-mini-badge.nrp {
        color:#0d63b7;
        border-color:#cfe2f7;
        background:#edf6ff;
    }

    .ifd-mini-badge.poh {
        color:#176a38;
        border-color:#cdebd7;
        background:#effaf3;
    }

    .ifd-flight-label {
        display:flex;
        align-items:center;
        gap:5px;
        margin-bottom:6px;
        color:#6b7785;
        font-size:7px;
        font-weight:900;
        letter-spacing:.04em;
        text-transform:uppercase;
    }

    .ifd-flight-label::before {
        content:'';
        width:5px;
        height:5px;
        border-radius:50%;
        background:#b78918;
    }

    .ifd-operational .ifd-value {
        font-size:10px;
    }

    .ifd-note {
        position:relative;
        background:#fafbfc;
    }

    .ifd-note.has-note {
        padding-left:36px;
    }

    .ifd-note.has-note::before {
        content:'';
        position:absolute;
        left:11px;
        top:10px;
        width:16px;
        height:16px;
        border:2px solid #7b8794;
        border-radius:4px;
        opacity:.7;
    }

    .ifd-note.has-note::after {
        content:'';
        position:absolute;
        left:15px;
        top:15px;
        width:8px;
        height:2px;
        border-top:2px solid #7b8794;
        border-bottom:2px solid #7b8794;
        opacity:.7;
    }

    .ifd-note.empty-note {
        color:#7b8794;
        font-style:italic;
        font-weight:800;
    }

    .ifd-copy-btn {
        display:inline-flex;
        min-height:29px;
        align-items:center;
        justify-content:center;
        gap:6px;
        padding:0 10px;
        border:1px solid #cfd8e2;
        border-radius:7px;
        color:#526174;
        background:#fff;
        font-size:8px;
        font-weight:900;
        text-transform:uppercase;
        cursor:pointer;
        transition:.15s ease;
    }

    .ifd-copy-btn:hover {
        color:#172b43;
        border-color:#bfc9d4;
        background:#f8fafc;
        transform:translateY(-1px);
    }

    .ifd-copy-status {
        min-width:54px;
        color:#176a38;
        font-size:7px;
        font-weight:900;
        text-transform:uppercase;
    }

    .ifd-section-tag,
    .ifd-type {
        display: inline-flex;
        min-height: 19px;
        align-items: center;
        padding: 0 7px;
        border-radius: 999px;
        font-size: 7px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .ifd-section-tag {
        color: #526174;
        background: #edf2f7;
    }

    .ifd-section-tag.ga {
        color: #6a4d09;
        background: #fff1c9;
    }

    .ifd-type {
        color: #405267;
        background: #edf2f7;
    }

    .ifd-type.reguler {
        color: #176a38;
        background: #e6f6eb;
    }

    .ifd-type.tambahan {
        color: #9a5b00;
        background: #fff0cf;
    }

    .ifd-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .ifd-field {
        min-width: 0;
        padding: 7px 9px;
        border-right: 1px solid #edf1f5;
        border-bottom: 1px solid #edf1f5;
    }

    .ifd-field:nth-child(2n) {
        border-right: 0;
    }

    .ifd-field.wide {
        grid-column: span 2;
        border-right: 0;
    }

    .ifd-operational {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        border-top: 1px solid #e8edf2;
        background: #fbfcfe;
    }

    .ifd-operational .ifd-field {
        border-bottom: 0;
    }

    .ifd-operational .ifd-field:nth-child(2n) {
        border-right: 1px solid #edf1f5;
    }

    .ifd-operational .ifd-field:last-child {
        border-right: 0;
    }

    .ifd-ga-label {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .ifd-ga-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: #b78918;
    }

    .ifd-note {
        min-height: 54px;
        padding: 9px 10px;
        color: #314359;
        font-size: 9px;
        font-weight: 700;
        line-height: 1.55;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }

    @media (max-width: 1120px) {
        .ifd-operational {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .ifd-operational .ifd-field {
            border-bottom: 1px solid #edf1f5;
        }
    }

    @media (max-width: 1050px) {
        .ifd-hero {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .ifd-hero-card.name {
            grid-column: span 2;
        }
    }

    @media (max-width: 760px) {
        .ifd-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .ifd-actions {
            justify-content: flex-start;
        }

        .ifd-status {
            align-items: flex-start;
            flex-direction: column;
        }

        .ifd-grid,
        .ifd-hero {
            grid-template-columns: 1fr;
        }

        .ifd-hero-card.name,
        .ifd-section.full {
            grid-column: span 1;
        }
    }

    @media (max-width: 560px) {
        .ifd-fields,
        .ifd-operational {
            grid-template-columns: 1fr;
        }

        .ifd-field,
        .ifd-field:nth-child(2n),
        .ifd-operational .ifd-field,
        .ifd-operational .ifd-field:nth-child(2n) {
            border-right: 0;
            border-bottom: 1px solid #edf1f5;
        }

        .ifd-field:last-child,
        .ifd-operational .ifd-field:last-child {
            border-bottom: 0;
        }

        .ifd-field.wide {
            grid-column: span 1;
        }
    }
</style>

<div class="ifd-page">
    <div class="ifd-head">
        <div class="ifd-title-wrap">
            <h1 class="ifd-title">
                <span class="ifd-title-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M6 3.5H14.5L18.5 7.5V20.5H6V3.5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                        <path d="M14.5 3.5V7.5H18.5M8.8 11H15.8M8.8 14.2H15.8M8.8 17.4H13.4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                    </svg>
                </span>
                DETAIL TICKET IFUTS
            </h1>
            <div class="ifd-subtitle">
                Detail ticket Produksi dari Google Spreadsheet IFUTS General Affair.
            </div>
        </div>

        <div class="ifd-actions">
            <a href="{{ $backUrl }}" class="ifd-btn">
                ← Kembali ke Monitoring
            </a>

            <button type="button" class="ifd-copy-btn" id="ifdCopyLinkButton">
                Copy Link
            </button>
            <span class="ifd-copy-status" id="ifdCopyStatus"></span>

            @if($sheetRow > 0)
                <a
                    href="{{ $spreadsheetRowUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="ifd-btn primary"
                >
                    Buka di Spreadsheet
                </a>
            @endif
        </div>
    </div>

    <div class="ifd-status {{ ($ifutsIntegration['connected'] ?? false) ? '' : 'error' }}">
        <div class="ifd-status-left">
            <span class="ifd-dot"></span>

            @if($ifutsIntegration['connected'] ?? false)
                <span>Data IFUTS terhubung dengan Google Spreadsheet General Affair.</span>
            @else
                <span>{{ $ifutsIntegration['message'] ?? 'Data IFUTS belum tersedia.' }}</span>
            @endif
        </div>

        <div class="ifd-badges">
            <span class="ifd-badge blue">Read Only</span>
            <span class="ifd-badge">Row {{ $sheetRow > 0 ? number_format($sheetRow) : '-' }}</span>
        </div>
    </div>

    <div class="ifd-body">
        @if(!empty($ticket))
            <div class="ifd-hero">
                <div class="ifd-hero-card name">
                    <span class="ifd-label">Nama Karyawan</span>
                    <div class="ifd-value">{{ $display($ticket['NAMA'] ?? null) }}</div>

                    <div class="ifd-person-line">
                        <span class="ifd-mini-badge nrp">
                            NRP {{ $display($ticket['NRP'] ?? null) }}
                        </span>

                        <span class="ifd-mini-badge poh">
                            POH {{ $display($ticket['POH_LOKASI'] ?? null) }}
                        </span>
                    </div>
                </div>

                <div class="ifd-hero-card">
                    <span class="ifd-label">NRP</span>
                    <div class="ifd-value">{{ $display($ticket['NRP'] ?? null) }}</div>
                </div>

                <div class="ifd-hero-card">
                    <span class="ifd-label">Kategori</span>
                    <div class="ifd-value">{{ $display($ticket['KATEGORI'] ?? null) }}</div>
                </div>

                <div class="ifd-hero-card">
                    <span class="ifd-label">POH</span>
                    <div class="ifd-value">{{ $display($ticket['POH_LOKASI'] ?? null) }}</div>
                </div>
            </div>

            <div class="ifd-grid">
                <section class="ifd-section ifd-section-employee">
                    <div class="ifd-section-head">
                        <span class="ifd-section-title">
                        <span class="ifd-section-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="8" r="3.2" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M5.5 19C6.1 15.7 8.4 14 12 14C15.6 14 17.9 15.7 18.5 19" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </span>
                        Data Karyawan
                    </span>
                        <span class="ifd-section-tag">Produksi</span>
                    </div>

                    <div class="ifd-fields">
                        <div class="ifd-field">
                            <span class="ifd-label">Departemen</span>
                            <div class="ifd-value">{{ $display($ticket['DEPARTEMEN'] ?? null) }}</div>
                        </div>

                        <div class="ifd-field">
                            <span class="ifd-label">Jabatan</span>
                            <div class="ifd-value">{{ $display($ticket['JABATAN'] ?? null) }}</div>
                        </div>

                        <div class="ifd-field">
                            <span class="ifd-label">Lokal / Non Lokal</span>
                            <div class="ifd-value">{{ $display($ticket['POH_STATUS'] ?? null) }}</div>
                        </div>

                        <div class="ifd-field">
                            <span class="ifd-label">No HP Aktif</span>
                            <div class="ifd-value">{{ $display($ticket['NO_HP_AKTIF'] ?? null) }}</div>
                        </div>

                        <div class="ifd-field">
                            <span class="ifd-label">NIK KTP</span>
                            <div class="ifd-value">{{ $display($ticket['NIK_KTP'] ?? null) }}</div>
                        </div>

                        <div class="ifd-field">
                            <span class="ifd-label">Tgl Lahir</span>
                            <div class="ifd-value">{{ $display($ticket['TGL_LAHIR'] ?? null) }}</div>
                        </div>
                    </div>
                </section>

                <section class="ifd-section ifd-section-info">
                    <div class="ifd-section-head">
                        <span class="ifd-section-title">
                        <span class="ifd-section-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <rect x="5" y="4.5" width="14" height="15" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M8.5 9H15.5M8.5 12.5H15.5M8.5 16H13" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                            </svg>
                        </span>
                        Informasi Data
                    </span>
                        <span class="ifd-section-tag ga">Spreadsheet GA</span>
                    </div>

                    <div class="ifd-fields">
                        <div class="ifd-field">
                            <span class="ifd-label">Sumber Data</span>
                            <div class="ifd-value">Google Spreadsheet GA</div>
                        </div>

                        <div class="ifd-field">
                            <span class="ifd-label">Sheet Row</span>
                            <div class="ifd-value">{{ $sheetRow > 0 ? number_format($sheetRow) : '-' }}</div>
                        </div>

                        <div class="ifd-field">
                            <span class="ifd-label">Departemen</span>
                            <div class="ifd-value">{{ $display($ticket['DEPARTEMEN'] ?? null) }}</div>
                        </div>

                        <div class="ifd-field">
                            <span class="ifd-label">Mode Website</span>
                            <div class="ifd-value">READ ONLY</div>
                        </div>
                    </div>
                </section>

                <section class="ifd-section ifd-section-out">
                    <div class="ifd-section-head">
                        <span class="ifd-section-title">
                        <span class="ifd-section-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M4.5 12H18M14 7.5L18.5 12L14 16.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M7 7V17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </span>
                        Perjalanan OUT
                    </span>

                        @if($outType !== '')
                            <span class="ifd-type {{ strtolower($outType) }}">
                                {{ $outType }}
                            </span>
                        @endif
                    </div>

                    <div class="ifd-fields">
                        <div class="ifd-field">
                            <span class="ifd-label">Tgl OUT</span>
                            <div class="ifd-value">{{ $display($ticket['TGL_OUT'] ?? null) }}</div>
                        </div>

                        <div class="ifd-field">
                            <span class="ifd-label">Rute OUT</span>
                            <div class="ifd-value">{{ $display($ticket['RUTE_OUT'] ?? null) }}</div>
                        </div>

                        <div class="ifd-field wide">
                            <span class="ifd-label">Ket Tiket OUT</span>
                            <div class="ifd-value">{{ $display($ticket['KET_TIKET_OUT'] ?? null) }}</div>
                        </div>
                    </div>

                    <div class="ifd-operational">
                        <div class="ifd-field">
                            <span class="ifd-flight-label">Take Off</span>
                            <div class="ifd-value">{{ $display($ticket['TIME_TAKE_OFF_OUT'] ?? null) }}</div>
                        </div>

                        <div class="ifd-field">
                            <span class="ifd-flight-label">Landing</span>
                            <div class="ifd-value">{{ $display($ticket['TIME_LANDING_OUT'] ?? null) }}</div>
                        </div>

                        <div class="ifd-field">
                            <span class="ifd-flight-label">Estimasi Biaya</span>
                            <div class="ifd-value">{{ $display($ticket['ESTIMASI_BIAYA_OUT'] ?? null) }}</div>
                        </div>

                        <div class="ifd-field">
                            <span class="ifd-flight-label">Maskapai</span>
                            <div class="ifd-value">{{ $display($ticket['MASKAPAI_OUT'] ?? null) }}</div>
                        </div>
                    </div>
                </section>

                <section class="ifd-section ifd-section-in">
                    <div class="ifd-section-head">
                        <span class="ifd-section-title">
                        <span class="ifd-section-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M19.5 12H6M10 7.5L5.5 12L10 16.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M17 7V17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </span>
                        Perjalanan IN
                    </span>

                        @if($inType !== '')
                            <span class="ifd-type {{ strtolower($inType) }}">
                                {{ $inType }}
                            </span>
                        @endif
                    </div>

                    <div class="ifd-fields">
                        <div class="ifd-field">
                            <span class="ifd-label">Lokasi IN</span>
                            <div class="ifd-value">{{ $display($ticket['LOKASI_IN'] ?? null) }}</div>
                        </div>

                        <div class="ifd-field">
                            <span class="ifd-label">Tgl IN</span>
                            <div class="ifd-value">{{ $display($ticket['TGL_IN'] ?? null) }}</div>
                        </div>

                        <div class="ifd-field">
                            <span class="ifd-label">Rute IN</span>
                            <div class="ifd-value">{{ $display($ticket['RUTE_IN'] ?? null) }}</div>
                        </div>

                        <div class="ifd-field">
                            <span class="ifd-label">Ket Tiket IN</span>
                            <div class="ifd-value">{{ $display($ticket['KET_TIKET_IN'] ?? null) }}</div>
                        </div>
                    </div>

                    <div class="ifd-operational">
                        <div class="ifd-field">
                            <span class="ifd-flight-label">Take Off</span>
                            <div class="ifd-value">{{ $display($ticket['TIME_TAKE_OFF_IN'] ?? null) }}</div>
                        </div>

                        <div class="ifd-field">
                            <span class="ifd-flight-label">Landing</span>
                            <div class="ifd-value">{{ $display($ticket['TIME_LANDING_IN'] ?? null) }}</div>
                        </div>

                        <div class="ifd-field">
                            <span class="ifd-flight-label">Estimasi Biaya</span>
                            <div class="ifd-value">{{ $display($ticket['ESTIMASI_BIAYA_IN'] ?? null) }}</div>
                        </div>

                        <div class="ifd-field">
                            <span class="ifd-flight-label">Maskapai</span>
                            <div class="ifd-value">{{ $display($ticket['MASKAPAI_IN'] ?? null) }}</div>
                        </div>
                    </div>
                </section>

                <section class="ifd-section full ifd-section-note">
                    <div class="ifd-section-head">
                        <span class="ifd-section-title">
                        <span class="ifd-section-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M6 4.5H18V19.5H6V4.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                <path d="M8.5 9H15.5M8.5 12.5H15.5M8.5 16H13" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                            </svg>
                        </span>
                        Note
                    </span>
                    </div>

                    <div class="ifd-note {{ $hasNote ? 'has-note' : 'empty-note' }}">
                        {{ $hasNote ? $noteText : 'Tidak ada catatan.' }}
                    </div>
                </section>
            </div>
        @else
            <section class="ifd-section full">
                <div class="ifd-note">
                    Detail ticket belum dapat ditampilkan karena data Google Spreadsheet tidak tersedia.
                </div>
            </section>
        @endif
    </div>
</div>

<script>
(function () {
    const button = document.getElementById('ifdCopyLinkButton');
    const status = document.getElementById('ifdCopyStatus');

    if (!button || !status) {
        return;
    }

    button.addEventListener('click', async function () {
        try {
            await navigator.clipboard.writeText(window.location.href);
            status.textContent = 'Tersalin';

            window.setTimeout(function () {
                status.textContent = '';
            }, 1800);
        } catch (error) {
            status.textContent = 'Gagal Copy';

            window.setTimeout(function () {
                status.textContent = '';
            }, 1800);
        }
    });
})();
</script>

@endsection