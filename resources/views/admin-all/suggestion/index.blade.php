@extends('admin-all.layout')

@section('title', 'Suggestion System')

@section('admin-content')
@php
    $dashboard = $suggestionDashboard ?? [
        'filters' => [
            'month' => null,
            'year' => null,
            'status' => null,
            'nrp' => null,
        ],
        'available_years' => [],
        'total' => 0,
        'status_chart' => [],
        'top_names' => [],
        'rows' => [],
        'data_total' => 0,
    ];

    $filters = $dashboard['filters'] ?? [];

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

    $statusRows = $dashboard['status_chart'] ?? [];
    $topNames = $dashboard['top_names'] ?? [];
    $dataRows = $dashboard['rows'] ?? [];

    $selectedStatus = $filters['status'] ?? null;
    $selectedNrp = $filters['nrp'] ?? null;

    $monitoringUrl = \Illuminate\Support\Facades\Route::has('admin-all.suggestion.monitoring')
        ? route('admin-all.suggestion.monitoring')
        : route('admin-all.suggestion.index');

    $selectedPersonName = null;

    if ($selectedNrp) {
        foreach ($suggestionData['database']['rows'] ?? [] as $row) {
            if (trim((string) ($row['NRP'] ?? '')) === (string) $selectedNrp) {
                $selectedPersonName = trim(
                    (string) ($row['NAMA_KARYAWAN'] ?? '')
                );
                break;
            }
        }
    }

    $statusDisplay = null;

    if ($selectedStatus) {
        foreach ($statusRows as $statusItem) {
            if (($statusItem['key'] ?? null) === $selectedStatus) {
                $statusDisplay = $statusItem['label'] ?? $selectedStatus;
                break;
            }
        }

        $statusDisplay = $statusDisplay
            ?: ucwords(
                strtolower(
                    str_replace('_', ' ', $selectedStatus)
                )
            );
    }
@endphp

<style>
    /*
     * Suggestion System tetap menggunakan shell Admin All:
     * sidebar + header + footer.
     * Konten dibuat memenuhi seluruh area MAIN setelah sidebar.
     */
    .aa-content {
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
    }

    .ss-page {
        width: 100%;
        max-width: none;
        padding: 0 0 18px;
    }

    .ss-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 10px;
    }

    .ss-title {
        margin: 0;
        color: #0d2c59;
        font-size: 24px;
        font-weight: 800;
        line-height: 1.1;
    }

    .ss-subtitle {
        margin: 5px 0 0;
        color: #697386;
        font-size: 12px;
    }

    .ss-head-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: flex-end;
    }

    .ss-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 34px;
        padding: 0 14px;
        border-radius: 8px;
        border: 1px solid #d8dee8;
        background: #fff;
        color: #23314d;
        font-size: 11px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
    }

    .ss-btn:hover {
        text-decoration: none;
        transform: translateY(-1px);
    }

    .ss-btn-primary {
        border-color: #1479ef;
        background: #1479ef;
        color: #fff;
    }

    .ss-alert {
        display: flex;
        align-items: center;
        gap: 8px;
        min-height: 34px;
        padding: 7px 10px;
        margin-bottom: 10px;
        border-radius: 8px;
        border: 1px solid #c7ead8;
        background: #edfff5;
        color: #14784a;
        font-size: 10px;
        font-weight: 700;
    }

    .ss-alert.error {
        border-color: #f0c8c8;
        background: #fff0f0;
        color: #aa1f2c;
    }

    .ss-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: currentColor;
        flex: 0 0 auto;
    }

    .ss-filter-card,
    .ss-panel,
    .ss-total-card {
        border: 1px solid #d7dde7;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 8px 18px rgba(25, 42, 70, .04);
    }

    .ss-filter-card {
        padding: 12px;
        margin-bottom: 10px;
    }

    .ss-filter-row {
        display: grid;
        grid-template-columns: minmax(170px, 1fr) minmax(150px, .8fr) auto auto;
        gap: 9px;
        align-items: end;
    }

    .ss-field label {
        display: block;
        margin-bottom: 5px;
        color: #253550;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .ss-select {
        width: 100%;
        min-height: 36px;
        padding: 0 10px;
        border: 1px solid #d6dde8;
        border-radius: 8px;
        background: #fff;
        color: #253550;
        font-size: 12px;
        outline: none;
    }

    .ss-select:focus {
        border-color: #1479ef;
        box-shadow: 0 0 0 3px rgba(20, 121, 239, .10);
    }

    .ss-grid {
        display: grid;
        grid-template-columns: 220px minmax(0, .85fr) minmax(0, 1.5fr);
        gap: 10px;
        margin-bottom: 10px;
    }

    .ss-total-link {
        display: block;
        height: 100%;
        color: inherit;
        text-decoration: none;
    }

    .ss-total-card {
        position: relative;
        min-height: 280px;
        padding: 18px;
        overflow: hidden;
        cursor: pointer;
        transition: .18s ease;
    }

    .ss-total-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 26px rgba(25, 42, 70, .09);
    }

    .ss-total-card::after {
        content: '';
        position: absolute;
        width: 130px;
        height: 130px;
        right: -42px;
        bottom: -42px;
        border-radius: 50%;
        background: linear-gradient(
            135deg,
            rgba(239, 125, 0, .20),
            rgba(20, 121, 239, .08)
        );
    }

    .ss-kicker {
        color: #788398;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .11em;
        text-transform: uppercase;
    }

    .ss-total-number {
        margin-top: 22px;
        color: #0d2c59;
        font-size: 58px;
        font-weight: 900;
        line-height: .95;
    }

    .ss-total-label {
        margin-top: 8px;
        color: #4f5d73;
        font-size: 12px;
        font-weight: 700;
    }

    .ss-total-hint {
        position: absolute;
        left: 18px;
        bottom: 18px;
        z-index: 2;
        color: #1479ef;
        font-size: 10px;
        font-weight: 800;
    }

    .ss-panel {
        min-height: 280px;
        padding: 13px;
    }

    .ss-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 8px;
    }

    .ss-panel-title {
        margin: 0;
        color: #14243e;
        font-size: 13px;
        font-weight: 900;
    }

    .ss-panel-subtitle {
        margin-top: 2px;
        color: #7c8799;
        font-size: 9px;
    }

    .ss-chip {
        display: inline-flex;
        align-items: center;
        min-height: 23px;
        padding: 0 8px;
        border-radius: 999px;
        background: #edf4ff;
        color: #1266c7;
        font-size: 9px;
        font-weight: 900;
        white-space: nowrap;
    }

    .ss-chart-wrap {
        position: relative;
        height: 215px;
    }

    .ss-chart-empty {
        height: 215px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #8d98aa;
        font-size: 11px;
        text-align: center;
    }

    .ss-table-panel {
        border: 1px solid #d7dde7;
        border-radius: 12px;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 8px 18px rgba(25, 42, 70, .04);
    }

    .ss-table-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 13px;
        border-bottom: 1px solid #e4e8ef;
    }

    .ss-table-filter {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        align-items: center;
    }

    .ss-filter-badge {
        display: inline-flex;
        align-items: center;
        min-height: 25px;
        padding: 0 9px;
        border-radius: 999px;
        background: #fff3e5;
        color: #b65d00;
        font-size: 9px;
        font-weight: 900;
    }

    .ss-filter-clear {
        color: #d12631;
        font-size: 9px;
        font-weight: 900;
        text-decoration: none;
    }

    .ss-table-scroll {
        width: 100%;
        overflow-x: auto;
    }

    .ss-table {
        width: 100%;
        min-width: 1120px;
        border-collapse: collapse;
    }

    .ss-table th {
        padding: 9px 10px;
        border-bottom: 1px solid #e1e6ee;
        background: #f7f9fc;
        color: #5e6a7e;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .04em;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .ss-table td {
        padding: 9px 10px;
        border-bottom: 1px solid #edf0f5;
        color: #24334b;
        font-size: 10px;
        vertical-align: top;
    }

    .ss-table tbody tr:hover {
        background: #fbfcfe;
    }

    .ss-table .title-cell {
        min-width: 260px;
        max-width: 360px;
        white-space: normal;
        font-weight: 700;
    }

    .ss-status {
        display: inline-flex;
        align-items: center;
        min-height: 23px;
        padding: 0 8px;
        border-radius: 999px;
        background: #eef3fa;
        color: #33445f;
        font-size: 9px;
        font-weight: 900;
        white-space: nowrap;
    }

    .ss-status.submitted {
        background: #fff2dc;
        color: #aa5c00;
    }

    /* VERIFIED GL/QCC / SH / DH-PM = BLUE */
    .ss-status.verified {
        background: #e5f2ff;
        color: #0d63b7;
    }

    /* APPROVED DH/PM / SELESAI = GREEN */
    .ss-status.approved {
        background: #ddf5e8;
        color: #087847;
    }

    /* REJECTED ANY STAGE = RED */
    .ss-status.rejected {
        background: #ffe8eb;
        color: #a72632;
    }

    /* REVISION = AMBER */
    .ss-status.revision {
        background: #fff4c7;
        color: #856000;
    }

    .ss-link {
        color: #1479ef;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
    }

    .ss-link:hover {
        text-decoration: underline;
    }

    .ss-empty {
        padding: 26px 16px !important;
        color: #8b96a8 !important;
        text-align: center;
    }

    @media (max-width: 1100px) {
        .ss-grid {
            grid-template-columns: 180px 1fr;
        }

        .ss-grid .ss-panel:last-child {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 720px) {
        .ss-page {
            padding: 10px;
        }

        .ss-head,
        .ss-table-head {
            align-items: stretch;
            flex-direction: column;
        }

        .ss-head-actions {
            justify-content: flex-start;
        }

        .ss-filter-row {
            grid-template-columns: 1fr 1fr;
        }

        .ss-grid {
            grid-template-columns: 1fr;
        }

        .ss-grid .ss-panel:last-child {
            grid-column: auto;
        }

        .ss-total-card {
            min-height: 180px;
        }
    }

/* ==========================================================
       FIXED INNER SHELL — DASHBOARD SUGGESTION
       Area atas + table header tetap.
       Hanya isi data tabel yang scroll.
       ========================================================== */
    #adminAllShell .aa-main {
        min-height: 0 !important;
        overflow: hidden !important;
    }

    #adminAllShell .aa-content {
        height: 100% !important;
        min-height: 0 !important;
        overflow: hidden !important;
    }

    .ss-page {
        height: 100% !important;
        min-height: 0 !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
        padding-bottom: 0 !important;
    }

    .ss-head,
    .ss-alert,
    .ss-filter-card,
    .ss-grid {
        flex: 0 0 auto;
    }

    .ss-table-panel {
        flex: 1 1 auto;
        min-height: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .ss-table-head {
        flex: 0 0 auto;
        position: relative;
        z-index: 5;
        background: #fff;
    }

    .ss-table-scroll {
        flex: 1 1 auto;
        min-height: 0;
        overflow: auto !important;
        overscroll-behavior: contain;
        scrollbar-gutter: stable;
    }

    .ss-table thead th {
        position: sticky;
        top: 0;
        z-index: 4;
        background: #f7f9fc;
    }

    @media (max-height: 760px) and (min-width: 721px) {
        .ss-total-card,
        .ss-panel {
            min-height: 220px;
        }

        .ss-chart-wrap,
        .ss-chart-empty {
            height: 160px;
        }

        .ss-total-number {
            margin-top: 12px;
            font-size: 46px;
        }
    }

</style>

<div class="ss-page">
    <div class="ss-head">
        <div>
            <h1 class="ss-title">Suggestion System</h1>
        </div>

        <div class="ss-head-actions">
            <a
                href="{{ route('admin-all') }}"
                class="ss-btn"
            >
                ← ADMIN ALL
            </a>

            <a
                href="{{ $suggestion['spreadsheet_url'] ?? '#' }}"
                target="_blank"
                rel="noopener noreferrer"
                class="ss-btn ss-btn-primary"
            >
                BUKA SPREADSHEET
            </a>
        </div>
    </div>

    @if(($suggestionIntegration['connected'] ?? false) === true)
        <div class="ss-alert">
            <span class="ss-dot"></span>
            Google Sheets terhubung.
            DATABASE_SS terbaca
            {{ number_format($suggestionData['database']['total'] ?? 0) }}
            data.
            Akses login:
            {{ $suggestionAccess['access'] ?? '-' }}
            /
            {{ $suggestionAccess['status'] ?? '-' }}.
        </div>
    @else
        <div class="ss-alert error">
            <span class="ss-dot"></span>
            {{ $suggestionIntegration['message'] ?? 'Google Sheets belum terhubung.' }}
        </div>
    @endif

    <form
        method="GET"
        action="{{ route('admin-all.suggestion.index') }}"
        class="ss-filter-card"
    >
        <div class="ss-filter-row">
            <div class="ss-field">
                <label for="month">Bulan Submit</label>
                <select
                    id="month"
                    name="month"
                    class="ss-select"
                >
                    <option value="">Semua Bulan</option>

                    @foreach($monthNames as $monthNumber => $monthName)
                        <option
                            value="{{ $monthNumber }}"
                            @selected(
                                (int) ($filters['month'] ?? 0)
                                === $monthNumber
                            )
                        >
                            {{ $monthName }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="ss-field">
                <label for="year">Tahun Submit</label>
                <select
                    id="year"
                    name="year"
                    class="ss-select"
                >
                    <option value="">Semua Tahun</option>

                    @foreach($dashboard['available_years'] ?? [] as $yearItem)
                        <option
                            value="{{ $yearItem }}"
                            @selected(
                                (int) ($filters['year'] ?? 0)
                                === (int) $yearItem
                            )
                        >
                            {{ $yearItem }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button
                type="submit"
                class="ss-btn ss-btn-primary"
            >
                TERAPKAN FILTER
            </button>

            <a
                href="{{ route('admin-all.suggestion.index') }}"
                class="ss-btn"
            >
                RESET
            </a>
        </div>
    </form>

    <div class="ss-grid">
        <a
            href="{{ $monitoringUrl }}{{ ($filters['month'] ?? null) || ($filters['year'] ?? null)
                ? '?'.http_build_query(array_filter([
                    'month' => $filters['month'] ?? null,
                    'year' => $filters['year'] ?? null,
                ]))
                : '' }}"
            class="ss-total-link"
            title="Klik untuk melihat seluruh data pada periode filter"
        >
            <div class="ss-total-card">
                <div class="ss-kicker">
                    Total Suggestion
                </div>

                <div class="ss-total-number">
                    {{ number_format($dashboard['total'] ?? 0) }}
                </div>

                <div class="ss-total-label">
                    Suggestion pada periode terpilih
                </div>

                <div class="ss-total-hint">
                    Klik untuk buka data →
                </div>
            </div>
        </a>

        <div class="ss-panel">
            <div class="ss-panel-head">
                <div>
                    <h2 class="ss-panel-title">
                        Status Suggestion
                    </h2>

                    <div class="ss-panel-subtitle">
                        Klik / tap bagian donut untuk drill-down data.
                    </div>
                </div>

                <span class="ss-chip">
                    {{ count($statusRows) }} STATUS
                </span>
            </div>

            @if(count($statusRows) > 0)
                <div class="ss-chart-wrap">
                    <canvas id="ssStatusChart"></canvas>
                </div>
            @else
                <div class="ss-chart-empty">
                    Belum ada data status pada periode ini.
                </div>
            @endif
        </div>

        <div class="ss-panel">
            <div class="ss-panel-head">
                <div>
                    <h2 class="ss-panel-title">
                        Top 10 Nama Pembuat SS
                    </h2>

                    <div class="ss-panel-subtitle">
                        Ranking berdasarkan NRP.
                        Klik / tap bar untuk melihat data orang tersebut.
                    </div>
                </div>

                <span class="ss-chip">
                    TOP 10
                </span>
            </div>

            @if(count($topNames) > 0)
                <div class="ss-chart-wrap">
                    <canvas id="ssTopNameChart"></canvas>
                </div>
            @else
                <div class="ss-chart-empty">
                    Belum ada data pembuat pada periode ini.
                </div>
            @endif
        </div>
    </div>

    <div
        id="data-ss"
        class="ss-table-panel"
    >
        <div class="ss-table-head">
            <div>
                <h2 class="ss-panel-title">
                    Data Suggestion System
                </h2>

                <div class="ss-panel-subtitle">
                    Menampilkan
                    {{ number_format($dashboard['data_total'] ?? 0) }}
                    dari
                    {{ number_format($dashboard['total'] ?? 0) }}
                    data periode.
                </div>
            </div>

            <div class="ss-table-filter">
                @if($statusDisplay)
                    <span class="ss-filter-badge">
                        STATUS: {{ $statusDisplay }}
                    </span>
                @endif

                @if($selectedNrp)
                    <span class="ss-filter-badge">
                        NRP:
                        {{ $selectedNrp }}
                        @if($selectedPersonName)
                            — {{ $selectedPersonName }}
                        @endif
                    </span>
                @endif

                @if($statusDisplay || $selectedNrp)
                    <a
                        href="{{ route('admin-all.suggestion.index', array_filter([
                            'month' => $filters['month'] ?? null,
                            'year' => $filters['year'] ?? null,
                        ])) }}#data-ss"
                        class="ss-filter-clear"
                    >
                        HAPUS DRILL-DOWN
                    </a>
                @endif
            </div>
        </div>

        <div class="ss-table-scroll">
            <table class="ss-table">
                <thead>
                    <tr>
                        <th>No SS</th>
                        <th>Submit</th>
                        <th>NRP</th>
                        <th>Nama</th>
                        <th>Lokasi</th>
                        <th>Judul Suggestion</th>
                        <th>Status</th>
                        <th>GL / QCC</th>
                        <th>SH</th>
                        <th>Dokumen</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($dataRows as $row)
                        @php
                            $statusKey = strtoupper(
                                trim((string) ($row['STATUS'] ?? ''))
                            );

                            $statusClass = match ($statusKey) {
                                'SUBMITTED' => 'submitted',

                                'VERIFIED_GL_QCC',
                                'APPROVED_SH',
                                'VERIFIED_SH',
                                'VERIFIED_DH_PM' => 'verified',

                                'APPROVED_DH_PM',
                                'SELESAI',
                                'DONE',
                                'COMPLETED' => 'approved',

                                'REJECTED_GL_QCC',
                                'REJECTED_SH',
                                'REJECTED_DH_PM' => 'rejected',

                                'REVISION_GL_QCC' => 'revision',

                                default => '',
                            };

                            $statusLabel = match ($statusKey) {
                                'SUBMITTED' => 'Submitted',

                                'VERIFIED_GL_QCC' => 'Verified GL / QCC',
                                'APPROVED_SH',
                                'VERIFIED_SH' => 'Verified SH',
                                'VERIFIED_DH_PM' => 'Verified DH / PM',

                                'APPROVED_DH_PM',
                                'SELESAI',
                                'DONE',
                                'COMPLETED' => 'Approved DH / PM',

                                'REJECTED_GL_QCC' => 'Rejected GL / QCC',
                                'REJECTED_SH' => 'Rejected SH',
                                'REJECTED_DH_PM' => 'Rejected DH / PM',

                                'REVISION_GL_QCC' => 'Revision GL / QCC',

                                default => $statusKey !== ''
                                    ? ucwords(
                                        strtolower(
                                            str_replace('_', ' ', $statusKey)
                                        )
                                    )
                                    : 'No Status',
                            };
                        @endphp

                        <tr>
                            <td>
                                @if(!empty($row['NO_SS']))
                                    <a
                                        href="{{ route('admin-all.suggestion.detail', ['noSs' => $row['NO_SS']]) }}"
                                        class="ss-link"
                                        title="Buka detail {{ $row['NO_SS'] }}"
                                    >
                                        {{ $row['NO_SS'] }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>

                            <td>
                                {{ $row['SUBMIT_AT'] ?? '-' }}
                            </td>

                            <td>
                                {{ $row['NRP'] ?? '-' }}
                            </td>

                            <td>
                                <strong>
                                    {{ $row['NAMA_KARYAWAN'] ?? '-' }}
                                </strong>
                            </td>

                            <td>
                                {{ $row['LOKASI'] ?? '-' }}
                            </td>

                            <td class="title-cell">
                                {{ $row['JUDUL_SS'] ?? '-' }}
                            </td>

                            <td>
                                <span class="ss-status {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>

                            <td>
                                {{ $row['STATUS_GL_QCC'] ?? '-' }}
                            </td>

                            <td>
                                {{ $row['STATUS_SH'] ?? '-' }}
                            </td>

                            <td>
                                @if(!empty($row['FOLDER_SS_URL']))
                                    <a
                                        class="ss-link"
                                        href="{{ $row['FOLDER_SS_URL'] }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        Folder
                                    </a>
                                @endif

                                @if(
                                    !empty($row['FOLDER_SS_URL'])
                                    && !empty($row['FILE_EXCEL_URL'])
                                )
                                    &nbsp;·&nbsp;
                                @endif

                                @if(!empty($row['FILE_EXCEL_URL']))
                                    <a
                                        class="ss-link"
                                        href="{{ $row['FILE_EXCEL_URL'] }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        File
                                    </a>
                                @endif

                                @if(
                                    empty($row['FOLDER_SS_URL'])
                                    && empty($row['FILE_EXCEL_URL'])
                                )
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="10"
                                class="ss-empty"
                            >
                                Tidak ada data yang cocok dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if(count($statusRows) > 0 || count($topNames) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

<script>
(function () {
    const dashboardBaseUrl =
        @json($monitoringUrl);

    const selectedMonth =
        @json($filters['month'] ?? null);

    const selectedYear =
        @json($filters['year'] ?? null);

    function openDrillDown(type, value) {
        const params = new URLSearchParams();

        if (selectedMonth) {
            params.set('month', selectedMonth);
        }

        if (selectedYear) {
            params.set('year', selectedYear);
        }

        if (type === 'status' && value) {
            params.set('status', value);
        }

        if (type === 'nrp' && value) {
            params.set('nrp', value);
        }

        const query = params.toString();

        window.location.href =
            dashboardBaseUrl
            + (query ? '?' + query : '');
    }

    if (
        typeof Chart !== 'undefined'
        && document.getElementById('ssStatusChart')
    ) {
        const statusRows =
            @json($statusRows);

        const statusValues =
            statusRows.map(item => item.count);

        const statusKeys =
            statusRows.map(
                item => String(item.key || '').trim().toUpperCase()
            );

        const statusLabelMap = {
            SUBMITTED: 'Submitted',

            VERIFIED_GL_QCC: 'Verified GL / QCC',
            APPROVED_SH: 'Verified SH',
            VERIFIED_SH: 'Verified SH',
            VERIFIED_DH_PM: 'Verified DH / PM',

            APPROVED_DH_PM: 'Approved DH / PM',
            SELESAI: 'Approved DH / PM',
            DONE: 'Approved DH / PM',
            COMPLETED: 'Approved DH / PM',

            REJECTED_GL_QCC: 'Rejected GL / QCC',
            REJECTED_SH: 'Rejected SH',
            REJECTED_DH_PM: 'Rejected DH / PM',

            REVISION_GL_QCC: 'Revision GL / QCC'
        };

        const statusColorMap = {
            SUBMITTED: '#ef7d00',

            VERIFIED_GL_QCC: '#1479ef',
            APPROVED_SH: '#1479ef',
            VERIFIED_SH: '#1479ef',
            VERIFIED_DH_PM: '#1479ef',

            APPROVED_DH_PM: '#0aa768',
            SELESAI: '#0aa768',
            DONE: '#0aa768',
            COMPLETED: '#0aa768',

            REJECTED_GL_QCC: '#ef3340',
            REJECTED_SH: '#ef3340',
            REJECTED_DH_PM: '#ef3340',

            REVISION_GL_QCC: '#d6a100'
        };

        const statusLabels =
            statusKeys.map(
                (key, index) =>
                    statusLabelMap[key]
                    || statusRows[index]?.label
                    || key
                    || 'No Status'
            );

        const statusColors =
            statusKeys.map(
                key => statusColorMap[key] || '#64748b'
            );

        const statusChart =
            new Chart(
                document.getElementById('ssStatusChart'),
                {
                    type: 'doughnut',

                    data: {
                        labels: statusLabels,

                        datasets: [{
                            data: statusValues,
                            backgroundColor: statusColors,
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 7
                        }]
                    },

                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '66%',

                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 8,
                                    font: {
                                        size: 9,
                                        weight: '700'
                                    }
                                }
                            },

                            tooltip: {
                                callbacks: {
                                    label(context) {
                                        return (
                                            ' '
                                            + context.label
                                            + ': '
                                            + context.raw
                                        );
                                    }
                                }
                            }
                        },

                        onHover(event, elements) {
                            event.native.target.style.cursor =
                                elements.length
                                    ? 'pointer'
                                    : 'default';
                        },

                        onClick(event, elements) {
                            if (!elements.length) {
                                return;
                            }

                            const index =
                                elements[0].index;

                            openDrillDown(
                                'status',
                                statusKeys[index]
                            );
                        }
                    }
                }
            );
    }

    if (
        typeof Chart !== 'undefined'
        && document.getElementById('ssTopNameChart')
    ) {
        const topRows =
            @json($topNames);

        const topLabels =
            topRows.map(item => item.name);

        const topValues =
            topRows.map(item => item.count);

        const topNrp =
            topRows.map(item => item.nrp);

        const topChart =
            new Chart(
                document.getElementById('ssTopNameChart'),
                {
                    type: 'bar',

                    data: {
                        labels: topLabels,

                        datasets: [{
                            label: 'Jumlah SS',
                            data: topValues,
                            backgroundColor: '#1479ef',
                            borderRadius: 6,
                            borderSkipped: false
                        }]
                    },

                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,

                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                    font: {
                                        size: 9
                                    }
                                },
                                grid: {
                                    color: '#edf0f5'
                                }
                            },

                            y: {
                                ticks: {
                                    font: {
                                        size: 9,
                                        weight: '700'
                                    }
                                },
                                grid: {
                                    display: false
                                }
                            }
                        },

                        plugins: {
                            legend: {
                                display: false
                            }
                        },

                        onHover(event, elements) {
                            event.native.target.style.cursor =
                                elements.length
                                    ? 'pointer'
                                    : 'default';
                        },

                        onClick(event, elements) {
                            if (!elements.length) {
                                return;
                            }

                            const index =
                                elements[0].index;

                            if (!topNrp[index]) {
                                return;
                            }

                            openDrillDown(
                                'nrp',
                                topNrp[index]
                            );
                        }
                    }
                }
            );
    }
})();
</script>
@endif
@endsection