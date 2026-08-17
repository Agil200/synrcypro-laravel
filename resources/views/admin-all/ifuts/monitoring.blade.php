@extends('admin-all.layout')

@section('title', 'Monitoring Ticket IFUTS')

@section('admin-content')
@php
    $dashboard = $ifutsDashboard ?? [
        'filters' => [
            'month' => null,
            'year' => null,
            'category' => null,
            'position' => null,
            'poh' => null,
            'search' => null,
        ],
        'available_years' => [],
        'available_categories' => [],
        'available_positions' => [],
        'available_poh' => [],
        'available_localities' => [],
        'summary' => [
            'total' => 0,
            'regular_out' => 0,
            'additional_out' => 0,
        ],
        'rows' => [],
        'data_total' => 0,
        'all_total' => 0,
    ];

    $filters = $dashboard['filters'] ?? [];
    $dataRows = $dashboard['rows'] ?? [];
    $pagination = $dashboard['pagination'] ?? [
        'page' => 1,
        'per_page' => 20,
        'per_page_options' => [20, 30, 50, 100],
        'total' => count($dataRows),
        'last_page' => 1,
        'from' => count($dataRows) > 0 ? 1 : 0,
        'to' => count($dataRows),
    ];

    $selectedYear = (int) ($filters['year'] ?? now('Asia/Jakarta')->year);

    $yearOptions = array_values(
        array_unique(
            array_merge(
                [$selectedYear],
                array_map(
                    'intval',
                    $dashboard['available_years'] ?? []
                )
            )
        )
    );
    rsort($yearOptions);

    $monthNames = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    $spreadsheetUrl = $ifutsSource['spreadsheet_url']
        ?? 'https://docs.google.com/spreadsheets/d/110H1XSrSOyj_PjphSlruUv3B5EVHVXNgOSoP0ZVVO84/edit?pli=1&gid=2129255501#gid=2129255501';

    $hasSecondaryFilter = collect([
        $filters['month'] ?? null,
        $filters['category'] ?? null,
        $filters['position'] ?? null,
        $filters['poh'] ?? null,
        $filters['search'] ?? null,
    ])->filter(
        static fn ($value) => $value !== null && $value !== ''
    )->isNotEmpty();
@endphp

<style>
    /* =========================================================
       IFUTS MONITORING — FIXED INNER SHELL
       Header/sidebar/footer berasal dari admin-all.layout.
       Title, status, filter, judul tabel, rows selector, header tabel
       dan pagination tetap. HANYA ROW DATA yang scroll vertikal.
       ========================================================= */

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

    .ifm-page {
        display: flex;
        width: 100%;
        height: 100%;
        min-height: 0;
        flex-direction: column;
        overflow: hidden;
    }

    .ifm-head,
    .ifm-status-strip,
    .ifm-filter-card {
        flex: 0 0 auto;
    }

    .ifm-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 7px;
    }

    .ifm-title {
        margin: 0;
        color: #0d2c59;
        font-size: clamp(20px, 1.8vw, 26px);
        font-weight: 900;
        letter-spacing: -.03em;
        line-height: 1.05;
    }

    .ifm-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 6px;
    }

    .ifm-btn {
        display: inline-flex;
        min-height: 30px;
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
    }

    .ifm-btn:hover {
        text-decoration: none;
        transform: translateY(-1px);
        box-shadow: 0 5px 14px rgba(31, 47, 65, .08);
    }

    .ifm-btn.primary {
        border-color: #09879a;
        color: #fff;
        background: #09879a;
    }

    .ifm-status-strip {
        display: flex;
        min-height: 31px;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 7px;
        padding: 6px 9px;
        border: 1px solid #b7e6d0;
        border-radius: 8px;
        color: #11643d;
        background: #effff7;
        font-size: 8px;
        font-weight: 800;
    }

    .ifm-status-strip.error {
        border-color: #f0c4c8;
        color: #a72632;
        background: #fff2f3;
    }

    .ifm-status-left,
    .ifm-status-badges {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
    }

    .ifm-dot {
        width: 8px;
        height: 8px;
        flex: 0 0 8px;
        border-radius: 50%;
        background: currentColor;
        box-shadow: 0 0 0 3px rgba(17, 100, 61, .10);
    }

    .ifm-badge {
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

    .ifm-badge.readonly {
        color: #0d63b7;
        background: #e8f3ff;
    }

    .ifm-filter-card {
        margin-bottom: 7px;
        padding: 8px;
        border: 1px solid #d9e0e7;
        border-radius: 9px;
        background: #fff;
        box-shadow: 0 4px 14px rgba(31, 47, 65, .04);
    }

    .ifm-filter-grid {
        display: grid;
        grid-template-columns:
            110px
            125px
            minmax(135px, .9fr)
            minmax(150px, 1fr)
            130px
            minmax(180px, 1.2fr)
            auto;
        gap: 7px;
        align-items: end;
    }

    .ifm-field label {
        display: block;
        margin: 0 0 4px;
        color: #526174;
        font-size: 7px;
        font-weight: 900;
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    .ifm-control {
        width: 100%;
        height: 30px;
        padding: 0 8px;
        border: 1px solid #d6dde6;
        border-radius: 7px;
        color: #21344b;
        background: #fff;
        font-size: 9px;
        outline: none;
    }

    .ifm-control:focus {
        border-color: #09879a;
        box-shadow: 0 0 0 3px rgba(9, 135, 154, .09);
    }

    .ifm-filter-actions {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .ifm-search {
        height: 30px;
        padding: 0 13px;
        border: 0;
        border-radius: 7px;
        color: #fff;
        background: #172b43;
        font-size: 8px;
        font-weight: 900;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .ifm-reset {
        display: inline-flex;
        height: 30px;
        align-items: center;
        justify-content: center;
        padding: 0 11px;
        border: 1px solid #e3b6b6;
        border-radius: 7px;
        color: #a92731;
        background: #fff7f7;
        font-size: 8px;
        font-weight: 900;
        text-decoration: none;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .ifm-reset:hover {
        color: #8e1f28;
        background: #fff0f1;
        text-decoration: none;
    }


    .ifm-table-panel {
        display: flex;
        min-height: 0;
        flex: 1 1 auto;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid #d7dde7;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 5px 16px rgba(25, 42, 70, .04);
    }

    .ifm-table-title {
        display: flex;
        flex: 0 0 auto;
        min-height: 34px;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 7px 9px;
        border-bottom: 1px solid #e2e7ed;
        background: #fff;
    }

    .ifm-table-title-left {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 7px;
    }

    .ifm-table-title h2 {
        margin: 0;
        color: #172b43;
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .ifm-data-count {
        display: inline-flex;
        min-height: 21px;
        align-items: center;
        padding: 0 7px;
        border-radius: 999px;
        color: #0d6673;
        background: #e8f8fa;
        font-size: 7px;
        font-weight: 900;
        white-space: nowrap;
    }

    .ifm-filter-state {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 4px;
    }

    .ifm-filter-pill {
        display: inline-flex;
        min-height: 20px;
        align-items: center;
        padding: 0 6px;
        border-radius: 999px;
        color: #9a5400;
        background: #fff3df;
        font-size: 6px;
        font-weight: 900;
        white-space: nowrap;
    }



.ifm-detail-link {
    display: inline-flex;
    max-width: 100%;
    align-items: center;
    gap: 4px;
    color: #0d63b7;
    font-weight: 900;
    text-decoration: none;
    text-underline-offset: 2px;
}

.ifm-detail-link:hover {
    color: #084e90;
    text-decoration: underline;
}

.ifm-detail-link.name {
    color: #172b43;
}

.ifm-detail-link.name:hover {
    color: #0d63b7;
}

    .ifm-table-tools {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .ifm-per-page {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #657386;
        font-size: 7px;
        font-weight: 900;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .ifm-per-page select {
        height: 25px;
        padding: 0 22px 0 7px;
        border: 1px solid #d6dde6;
        border-radius: 6px;
        color: #21344b;
        background: #fff;
        font-size: 8px;
        font-weight: 800;
    }

    .ifm-pagination {
        display: flex;
        flex: 0 0 auto;
        min-height: 34px;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 6px 9px;
        border-top: 1px solid #e2e7ed;
        background: #fff;
    }

    .ifm-page-info {
        color: #667587;
        font-size: 7px;
        font-weight: 800;
        white-space: nowrap;
    }

    .ifm-page-links {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .ifm-page-link {
        display: inline-flex;
        min-width: 26px;
        height: 25px;
        align-items: center;
        justify-content: center;
        padding: 0 7px;
        border: 1px solid #d6dde6;
        border-radius: 6px;
        color: #33475f;
        background: #fff;
        font-size: 7px;
        font-weight: 900;
        text-decoration: none;
    }

    .ifm-page-link:hover {
        border-color: #9eb1c6;
        text-decoration: none;
    }

    .ifm-page-link.active {
        border-color: #172b43;
        color: #fff;
        background: #172b43;
    }

    .ifm-page-link.disabled {
        pointer-events: none;
        opacity: .45;
    }

    .ifm-table-scroll {
        width: 100%;
        min-height: 0;
        flex: 1 1 auto;
        overflow: auto !important;
        overscroll-behavior: contain;
        scrollbar-gutter: stable;
    }

    .ifm-table {
        width: 100%;
        min-width: 1450px;
        border-collapse: collapse;
    }

    .ifm-table th {
        position: sticky;
        top: 0;
        z-index: 5;
        padding: 7px 8px;
        border-bottom: 1px solid #dfe5ec;
        color: #546275;
        background: #f7f9fc;
        font-size: 7px;
        font-weight: 900;
        letter-spacing: .035em;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .ifm-table td {
        padding: 7px 8px;
        border-bottom: 1px solid #edf0f4;
        color: #26384f;
        font-size: 8px;
        vertical-align: top;
        white-space: nowrap;
    }

    .ifm-table tbody tr:hover {
        background: #fbfdff;
    }

    .ifm-name {
        min-width: 150px;
        max-width: 220px;
        overflow: hidden;
        font-weight: 900;
        text-overflow: ellipsis;
    }

    .ifm-note {
        min-width: 180px;
        max-width: 280px;
        white-space: normal !important;
    }

    .ifm-type {
        display: inline-flex;
        min-height: 19px;
        align-items: center;
        padding: 0 6px;
        border-radius: 999px;
        font-size: 6px;
        font-weight: 900;
    }

    .ifm-type {
        color: #3d526a;
        background: #edf2f7;
    }

    .ifm-type.reguler {
        color: #0b6c43;
        background: #e3f6eb;
    }

    .ifm-type.tambahan {
        color: #a55a00;
        background: #fff0d9;
    }


    .ifm-empty-row {
        padding: 22px 12px !important;
        color: #8b96a8 !important;
        text-align: center;
    }

    @media (max-width: 1180px) {
        .ifm-filter-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .ifm-filter-actions {
            width: 100%;
        }

        .ifm-search,
        .ifm-reset {
            flex: 1 1 0;
        }

    }

    @media (max-width: 720px) {
        .ifm-head,
        .ifm-status-strip,
        .ifm-table-title {
            align-items: stretch;
            flex-direction: column;
        }

        .ifm-actions,
        .ifm-filter-state {
            justify-content: flex-start;
        }

        .ifm-filter-grid {
            grid-template-columns: 1fr 1fr;
        }

    }
</style>

<div class="ifm-page">
    <div class="ifm-head">
        <h1 class="ifm-title">MONITORING TICKET IFUTS</h1>

        <div class="ifm-actions">
            <a
                href="{{ route('admin-all.ifuts.index') }}"
                class="ifm-btn"
            >
                ← Dashboard IFUTS
            </a>

            <a
                href="{{ $spreadsheetUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                class="ifm-btn primary"
            >
                ▣ Spreadsheet
            </a>
        </div>
    </div>

    @if(($ifutsIntegration['connected'] ?? false) === true)
        <div class="ifm-status-strip">
            <div class="ifm-status-left">
                <span class="ifm-dot"></span>
                <strong>Google Sheets terhubung.</strong>
                <span>
                    {{ number_format($dashboard['data_total'] ?? 0) }}
                    data tahun {{ $selectedYear }}
                </span>
            </div>

            <div class="ifm-status-badges">
                <span class="ifm-badge">Tahun {{ $selectedYear }}</span>
                <span class="ifm-badge">IFUTS Produksi</span>
                <span class="ifm-badge readonly">Read Only</span>
            </div>
        </div>
    @else
        <div class="ifm-status-strip error">
            <div class="ifm-status-left">
                <span class="ifm-dot"></span>
                <strong>Google Sheets IFUTS belum terhubung.</strong>
                <span>
                    {{ $ifutsIntegration['message'] ?? 'Integrasi belum tersedia.' }}
                </span>
            </div>

            <div class="ifm-status-badges">
                <span class="ifm-badge readonly">Read Only</span>
            </div>
        </div>
    @endif

    <form
        method="GET"
        action="{{ route('admin-all.ifuts.monitoring') }}"
        class="ifm-filter-card"
    >
        <input
            type="hidden"
            name="per_page"
            value="{{ $pagination['per_page'] ?? 20 }}"
        >

        <div class="ifm-filter-grid">
            <div class="ifm-field">
                <label for="ifm-year">Tahun OUT</label>
                <select id="ifm-year" name="year" class="ifm-control">
                    @foreach($yearOptions as $year)
                        <option
                            value="{{ $year }}"
                            @selected(
                                (string) ($filters['year'] ?? '')
                                === (string) $year
                            )
                        >
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="ifm-field">
                <label for="ifm-month">Bulan OUT</label>
                <select id="ifm-month" name="month" class="ifm-control">
                    <option value="">Semua Bulan</option>
                    @foreach($monthNames as $monthNumber => $monthName)
                        <option
                            value="{{ $monthNumber }}"
                            @selected(
                                (string) ($filters['month'] ?? '')
                                === (string) $monthNumber
                            )
                        >
                            {{ $monthName }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="ifm-field">
                <label for="ifm-category">Kategori</label>
                <select id="ifm-category" name="category" class="ifm-control">
                    <option value="">Semua Kategori</option>
                    @foreach($dashboard['available_categories'] ?? [] as $option)
                        <option
                            value="{{ $option }}"
                            @selected(
                                strtoupper(trim((string) ($filters['category'] ?? '')))
                                === strtoupper(trim((string) $option))
                            )
                        >
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="ifm-field">
                <label for="ifm-position">Jabatan</label>
                <select id="ifm-position" name="position" class="ifm-control">
                    <option value="">Semua Jabatan</option>
                    @foreach($dashboard['available_positions'] ?? [] as $option)
                        <option
                            value="{{ $option }}"
                            @selected(
                                strtoupper(trim((string) ($filters['position'] ?? '')))
                                === strtoupper(trim((string) $option))
                            )
                        >
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="ifm-field">
                <label for="ifm-poh">POH</label>
                <select id="ifm-poh" name="poh" class="ifm-control">
                    <option value="">Semua POH</option>
                    @foreach($dashboard['available_poh'] ?? [] as $option)
                        <option
                            value="{{ $option }}"
                            @selected(
                                strtoupper(trim((string) ($filters['poh'] ?? '')))
                                === strtoupper(trim((string) $option))
                            )
                        >
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="ifm-field">
                <label for="ifm-search">Cari Data</label>
                <input
                    id="ifm-search"
                    type="search"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    class="ifm-control"
                    placeholder="NRP, nama, rute..."
                    autocomplete="off"
                >
            </div>

            <div class="ifm-filter-actions">
                <button type="submit" class="ifm-search">Search</button>

                <a
                    href="{{ route('admin-all.ifuts.monitoring') }}"
                    class="ifm-reset"
                >
                    ↻ Reset
                </a>
            </div>
        </div>
    </form>

    <section class="ifm-table-panel">
        <div class="ifm-table-title">
            <div class="ifm-table-title-left">
                <h2>Data Monitoring Ticket</h2>
                <span class="ifm-data-count">
                    {{ number_format($dashboard['data_total'] ?? 0) }} data
                </span>
            </div>

            <div class="ifm-table-tools">
                @if($hasSecondaryFilter)
                    <div class="ifm-filter-state">
                        @if(!empty($filters['month']))
                            <span class="ifm-filter-pill">
                                {{ $monthNames[(int) $filters['month']] ?? $filters['month'] }}
                            </span>
                        @endif
                        @if(!empty($filters['category']))
                            <span class="ifm-filter-pill">{{ $filters['category'] }}</span>
                        @endif
                        @if(!empty($filters['position']))
                            <span class="ifm-filter-pill">{{ $filters['position'] }}</span>
                        @endif
                        @if(!empty($filters['poh']))
                            <span class="ifm-filter-pill">POH {{ $filters['poh'] }}</span>
                        @endif
                        @if(!empty($filters['search']))
                            <span class="ifm-filter-pill">Cari: {{ $filters['search'] }}</span>
                        @endif
                    </div>
                @endif

                <form method="GET" action="{{ route('admin-all.ifuts.monitoring') }}" class="ifm-per-page">
                    <input type="hidden" name="year" value="{{ $filters['year'] ?? $selectedYear }}">
                    @if(!empty($filters['month']))
                        <input type="hidden" name="month" value="{{ $filters['month'] }}">
                    @endif
                    @if(!empty($filters['category']))
                        <input type="hidden" name="category" value="{{ $filters['category'] }}">
                    @endif
                    @if(!empty($filters['position']))
                        <input type="hidden" name="position" value="{{ $filters['position'] }}">
                    @endif
                    @if(!empty($filters['poh']))
                        <input type="hidden" name="poh" value="{{ $filters['poh'] }}">
                    @endif
                    @if(!empty($filters['search']))
                        <input type="hidden" name="search" value="{{ $filters['search'] }}">
                    @endif

                    <span>Rows</span>
                    <select name="per_page" onchange="this.form.submit()">
                        @foreach($pagination['per_page_options'] ?? [20, 30, 50, 100] as $option)
                            <option
                                value="{{ $option }}"
                                @selected((int) ($pagination['per_page'] ?? 20) === (int) $option)
                            >
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        <div class="ifm-table-scroll">
            <table class="ifm-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kategori</th>
                        <th>NRP</th>
                        <th>Nama</th>
                        <th>Departemen</th>
                        <th>Jabatan</th>
                        <th>POH</th>
                        <th>Tgl OUT</th>
                        <th>Rute OUT</th>
                        <th>Ket Tiket OUT</th>
                        <th>Lokasi IN</th>
                        <th>Tgl IN</th>
                        <th>Rute IN</th>
                        <th>Ket Tiket IN</th>
                        <th>Note</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($dataRows as $index => $row)
                        @php
                            $outType = strtoupper(
                                trim((string) ($row['KET_TIKET_OUT'] ?? ''))
                            );

                            $inType = strtoupper(
                                trim((string) ($row['KET_TIKET_IN'] ?? ''))
                            );

                            $detailQuery = request()->only([
                                'year',
                                'month',
                                'category',
                                'position',
                                'poh',
                                'search',
                                'per_page',
                                'page',
                            ]);

                            $detailUrl = route(
                                'admin-all.ifuts.detail',
                                ['sheetRow' => (int) ($row['_SHEET_ROW'] ?? 0)]
                            );

                            if ($detailQuery !== []) {
                                $detailUrl .= '?'.http_build_query($detailQuery);
                            }
                        @endphp

                        <tr>
                            <td>
                                <a
                                    href="{{ $detailUrl }}"
                                    class="ifm-detail-link"
                                    title="Lihat detail ticket"
                                >
                                    {{ (int) ($pagination['from'] ?? 1) + $index }}
                                </a>
                            </td>

                            <td>{{ $row['KATEGORI'] ?? '-' }}</td>

                            <td>
                                <a
                                    href="{{ $detailUrl }}"
                                    class="ifm-detail-link"
                                    title="Lihat detail ticket {{ $row['NRP'] ?? '' }}"
                                >
                                    {{ $row['NRP'] ?? '-' }}
                                </a>
                            </td>

                            <td
                                class="ifm-name"
                                title="{{ $row['NAMA'] ?? '-' }}"
                            >
                                <a
                                    href="{{ $detailUrl }}"
                                    class="ifm-detail-link name"
                                    title="Lihat detail {{ $row['NAMA'] ?? '' }}"
                                >
                                    {{ $row['NAMA'] ?? '-' }}
                                </a>
                            </td>

                            <td>{{ $row['DEPARTEMEN'] ?? '-' }}</td>
                            <td>{{ $row['JABATAN'] ?? '-' }}</td>

                            <td>{{ $row['POH_LOKASI'] ?? '-' }}</td>

                            <td>{{ $row['TGL_OUT'] ?? '-' }}</td>
                            <td>{{ $row['RUTE_OUT'] ?? '-' }}</td>

                            <td>
                                @if($outType !== '')
                                    <span class="ifm-type {{ strtolower($outType) }}">
                                        {{ $outType }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>

                            <td>{{ $row['LOKASI_IN'] ?? '-' }}</td>
                            <td>{{ $row['TGL_IN'] ?? '-' }}</td>
                            <td>{{ $row['RUTE_IN'] ?? '-' }}</td>

                            <td>
                                @if($inType !== '')
                                    <span class="ifm-type {{ strtolower($inType) }}">
                                        {{ $inType }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>

                            <td class="ifm-note">
                                {{ $row['NOTE'] ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="ifm-empty-row">
                                Belum ada data IFUTS yang dapat ditampilkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ifm-pagination">
            <div class="ifm-page-info">
                Menampilkan
                {{ number_format($pagination['from'] ?? 0) }}
                -
                {{ number_format($pagination['to'] ?? 0) }}
                dari
                {{ number_format($pagination['total'] ?? 0) }}
                data
            </div>

            @php
                $currentPage = (int) ($pagination['page'] ?? 1);
                $lastPage = (int) ($pagination['last_page'] ?? 1);
                $pageStart = max(1, $currentPage - 2);
                $pageEnd = min($lastPage, $currentPage + 2);
            @endphp

            <div class="ifm-page-links">
                <a
                    href="{{ $currentPage > 1 ? request()->fullUrlWithQuery(['page' => $currentPage - 1]) : '#' }}"
                    class="ifm-page-link {{ $currentPage <= 1 ? 'disabled' : '' }}"
                >
                    ‹ Sebelumnya
                </a>

                @if($pageStart > 1)
                    <a href="{{ request()->fullUrlWithQuery(['page' => 1]) }}" class="ifm-page-link">1</a>
                    @if($pageStart > 2)
                        <span class="ifm-page-link disabled">…</span>
                    @endif
                @endif

                @for($pageNumber = $pageStart; $pageNumber <= $pageEnd; $pageNumber++)
                    <a
                        href="{{ request()->fullUrlWithQuery(['page' => $pageNumber]) }}"
                        class="ifm-page-link {{ $pageNumber === $currentPage ? 'active' : '' }}"
                    >
                        {{ $pageNumber }}
                    </a>
                @endfor

                @if($pageEnd < $lastPage)
                    @if($pageEnd < $lastPage - 1)
                        <span class="ifm-page-link disabled">…</span>
                    @endif
                    <a href="{{ request()->fullUrlWithQuery(['page' => $lastPage]) }}" class="ifm-page-link">
                        {{ $lastPage }}
                    </a>
                @endif

                <a
                    href="{{ $currentPage < $lastPage ? request()->fullUrlWithQuery(['page' => $currentPage + 1]) : '#' }}"
                    class="ifm-page-link {{ $currentPage >= $lastPage ? 'disabled' : '' }}"
                >
                    Selanjutnya ›
                </a>
            </div>
        </div>

    </section>
</div>
@endsection