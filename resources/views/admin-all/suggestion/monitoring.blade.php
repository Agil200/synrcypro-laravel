@extends('admin-all.layout')

@section('title', 'Monitoring Data SS')

@section('admin-content')
@php
    $filters = $monitoring['filters'] ?? [];

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

    $statusOptions =
        $monitoring['status_options'] ?? [];

    $stageCounts = $monitoring['stage_counts'] ?? [
        'submitted' => 0,
        'gl_qcc' => 0,
        'sh' => 0,
        'dh_pm' => 0,
        'selesai' => 0,
    ];

    $statusClass = static function (?string $status): string {
        $status = strtoupper(trim((string) $status));

        if ($status === 'SUBMITTED') {
            return 'submitted';
        }

        if (str_contains($status, 'VERIFIED')) {
            return 'verified';
        }

        if (
            str_contains($status, 'APPROVED')
            || in_array($status, ['SELESAI', 'DONE', 'COMPLETED'], true)
        ) {
            return 'approved';
        }

        if (str_contains($status, 'REJECTED')) {
            return 'rejected';
        }

        return '';
    };

    $statusLabel = static function (?string $status): string {
        $status = strtoupper(trim((string) $status));

        if ($status === '') {
            return 'Tanpa Status';
        }

        return match ($status) {
            'VERIFIED_GL_QCC' => 'Verified GL / QCC',
            'APPROVED_SH' => 'Approved SH',
            default => ucwords(
                strtolower(
                    str_replace('_', ' ', $status)
                )
            ),
        };
    };
@endphp

<style>
    .ssm-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 8px;
    }

    .ssm-head h1 {
        margin: 0;
        color: #051d39;
        font-size: clamp(21px, 2vw, 27px);
        letter-spacing: -.03em;
    }

    .ssm-head p {
        margin: 3px 0 0;
        color: #5d6c7c;
        font-size: 9px;
    }

    .ssm-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        justify-content: flex-end;
    }

    .ssm-info {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 9px;
        padding: 8px 11px;
        border: 1px solid #a9e3c6;
        border-radius: 8px;
        color: #12643b;
        background: #edfff5;
        font-size: 8px;
    }

    .ssm-info.error {
        border-color: #efc6c6;
        color: #a72632;
        background: #fff1f2;
    }

    .ssm-info strong::before {
        display: inline-block;
        width: 8px;
        height: 8px;
        margin-right: 7px;
        border-radius: 50%;
        background: currentColor;
        content: '';
    }

    .ssm-filter {
        display: grid;
        grid-template-columns:
            minmax(230px, 1.5fr)
            minmax(145px, .7fr)
            minmax(145px, .7fr)
            minmax(180px, .9fr)
            auto
            auto;
        gap: 7px;
        align-items: end;
        margin-bottom: 9px;
        padding: 10px;
        border: 1px solid var(--aa-border);
        border-radius: 10px;
        background: #fff;
        box-shadow: var(--aa-shadow);
    }

    .ssm-field label {
        display: block;
        margin-bottom: 4px;
        color: #4f6073;
        font-size: 7px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .ssm-control {
        width: 100%;
        min-height: 33px;
        padding: 0 9px;
        border: 1px solid #d5dde6;
        border-radius: 7px;
        color: #172b43;
        background: #fff;
        font-size: 9px;
        outline: none;
    }

    .ssm-control:focus {
        border-color: #0f78ef;
        box-shadow: 0 0 0 3px rgba(15, 120, 239, .08);
    }

    .ssm-summary {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 7px;
        margin-bottom: 9px;
    }

    .ssm-stage-card {
        position: relative;
        min-width: 0;
        min-height: 72px;
        padding: 10px 11px 10px 47px;
        overflow: hidden;
        border: 1px solid var(--stage-border);
        border-radius: 9px;
        background: var(--stage-bg);
        box-shadow: 0 4px 14px rgba(31, 47, 65, .05);
    }

    .ssm-stage-card::after {
        position: absolute;
        top: 0;
        right: 0;
        width: 5px;
        height: 100%;
        background: var(--stage-color);
        content: '';
    }

    .ssm-stage-icon {
        position: absolute;
        top: 12px;
        left: 10px;
        display: grid;
        width: 28px;
        height: 28px;
        place-items: center;
        border-radius: 8px;
        color: #fff;
        background: var(--stage-color);
        font-size: 9px;
        font-weight: 900;
    }

    .ssm-stage-card small {
        display: block;
        color: var(--stage-text);
        font-size: 7px;
        font-weight: 900;
        letter-spacing: .03em;
        text-transform: uppercase;
    }

    .ssm-stage-card strong {
        display: block;
        margin-top: 5px;
        color: #0c2343;
        font-size: 22px;
        line-height: 1;
    }

    .ssm-stage-card span {
        display: block;
        margin-top: 5px;
        overflow: hidden;
        color: #667587;
        font-size: 7px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ssm-stage-card.submitted {
        --stage-color: #ef7d00;
        --stage-border: #ffd3a3;
        --stage-bg: #fff8ef;
        --stage-text: #9b5700;
    }

    .ssm-stage-card.gl-qcc {
        --stage-color: #0f78ef;
        --stage-border: #b7d9ff;
        --stage-bg: #f2f8ff;
        --stage-text: #0d63b7;
    }

    .ssm-stage-card.sh {
        --stage-color: #0aa768;
        --stage-border: #b8ead2;
        --stage-bg: #f0fff7;
        --stage-text: #087847;
    }

    .ssm-stage-card.dh-pm {
        --stage-color: #7548c8;
        --stage-border: #d8c8f6;
        --stage-bg: #f8f4ff;
        --stage-text: #5d36a5;
    }

    .ssm-stage-card.selesai {
        --stage-color: #0f766e;
        --stage-border: #b9dedb;
        --stage-bg: #f0fbfa;
        --stage-text: #0b625c;
    }

    .ssm-table-card {
        overflow: hidden;
        border: 1px solid var(--aa-border);
        border-radius: 10px;
        background: #fff;
        box-shadow: var(--aa-shadow);
    }

    .ssm-table-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 10px 11px;
        border-bottom: 1px solid var(--aa-border);
    }

    .ssm-table-head h2 {
        margin: 0;
        font-size: 12px;
    }

    .ssm-table-head p {
        margin: 2px 0 0;
        color: #68778a;
        font-size: 7px;
    }

    .ssm-table-scroll {
        width: 100%;
        overflow-x: auto;
    }

    .ssm-table {
        width: 100%;
        min-width: 1280px;
        border-collapse: collapse;
    }

    .ssm-table th {
        padding: 8px 9px;
        border-bottom: 1px solid #e0e6ec;
        color: #536273;
        background: #f7f9fb;
        font-size: 7px;
        font-weight: 900;
        letter-spacing: .04em;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .ssm-table td {
        padding: 8px 9px;
        border-bottom: 1px solid #edf0f4;
        color: #24354b;
        font-size: 8px;
        vertical-align: top;
    }

    .ssm-table tbody tr:hover {
        background: #fbfcfe;
    }

    .ssm-title-cell {
        min-width: 280px;
        max-width: 390px;
        font-weight: 700;
        line-height: 1.35;
    }

    .ssm-status {
        display: inline-flex;
        min-height: 22px;
        align-items: center;
        padding: 0 7px;
        border-radius: 999px;
        color: #516176;
        background: #edf1f5;
        font-size: 7px;
        font-weight: 900;
        white-space: nowrap;
    }

    .ssm-status.submitted {
        color: #9b5700;
        background: #fff0d8;
    }

    .ssm-status.verified {
        color: #0b7044;
        background: #ddf5e8;
    }

    .ssm-status.approved {
        color: #0d63b7;
        background: #e5f2ff;
    }

    .ssm-status.rejected {
        color: #a52a35;
        background: #ffe8eb;
    }

    .ssm-docs {
        display: flex;
        gap: 6px;
        white-space: nowrap;
    }

    .ssm-docs a {
        color: #0f78ef;
        font-size: 8px;
        font-weight: 900;
        text-decoration: none;
    }

    .ssm-docs a:hover {
        text-decoration: underline;
    }

    .ssm-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 9px 11px;
        border-top: 1px solid #e3e8ed;
        color: #68778a;
        font-size: 8px;
    }

    .ssm-page-actions {
        display: flex;
        gap: 6px;
    }

    .ssm-page-button {
        display: inline-flex;
        min-height: 27px;
        align-items: center;
        justify-content: center;
        padding: 0 9px;
        border: 1px solid #cfd8e2;
        border-radius: 6px;
        color: #172b43;
        background: #fff;
        font-size: 7px;
        font-weight: 900;
        text-decoration: none;
    }

    .ssm-page-button.disabled {
        pointer-events: none;
        opacity: .4;
    }

    .ssm-empty {
        padding: 24px 10px !important;
        color: #7b8797 !important;
        text-align: center;
    }

    @media (max-width: 1150px) {
        .ssm-filter {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .ssm-summary {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 700px) {
        .ssm-head,
        .ssm-info,
        .ssm-table-head,
        .ssm-pagination {
            align-items: stretch;
            flex-direction: column;
        }

        .ssm-actions {
            justify-content: flex-start;
        }

        .ssm-filter,
        .ssm-summary {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="ssm-head">
    <div>
        <h1>Monitoring Data SS</h1>
        <p>
            Database Suggestion System Produksi Site BA.
            Pencarian dan filter ini masih READ ONLY.
        </p>
    </div>

    <div class="ssm-actions">
        <a
            href="{{ route('admin-all.suggestion.index') }}"
            class="aa-action-button"
        >
            ← Dashboard Suggestion
        </a>

        <a
            href="{{ $suggestion['spreadsheet_url'] ?? '#' }}"
            target="_blank"
            rel="noopener noreferrer"
            class="aa-action-button primary"
        >
            Buka Spreadsheet
        </a>
    </div>
</div>

@if(($suggestionIntegration['connected'] ?? false) === true)
    <div class="ssm-info">
        <strong>
            DATABASE_SS terhubung.
            {{ number_format($suggestionData['database']['total'] ?? 0) }}
            data terbaca.
        </strong>

        <span>
            Login:
            {{ $suggestionAccess['name'] ?? '-' }}
            •
            {{ $suggestionAccess['access'] ?? '-' }}
            •
            {{ $suggestionAccess['status'] ?? '-' }}
        </span>
    </div>
@else
    <div class="ssm-info error">
        <strong>
            Integrasi Google Sheets bermasalah.
        </strong>

        <span>
            {{ $suggestionIntegration['message'] ?? '-' }}
        </span>
    </div>
@endif

<form
    method="GET"
    action="{{ route('admin-all.suggestion.monitoring') }}"
    class="ssm-filter"
>
    <div class="ssm-field">
        <label for="q">
            Cari Data
        </label>

        <input
            id="q"
            type="search"
            name="q"
            value="{{ $filters['q'] ?? '' }}"
            class="ssm-control"
            placeholder="No SS / NRP / Nama / Lokasi / Judul..."
        >
    </div>

    <div class="ssm-field">
        <label for="month">
            Bulan Submit
        </label>

        <select
            id="month"
            name="month"
            class="ssm-control"
        >
            <option value="">
                Semua Bulan
            </option>

            @foreach($monthNames as $number => $name)
                <option
                    value="{{ $number }}"
                    @selected(
                        (int) ($filters['month'] ?? 0)
                        === $number
                    )
                >
                    {{ $name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="ssm-field">
        <label for="year">
            Tahun Submit
        </label>

        <select
            id="year"
            name="year"
            class="ssm-control"
        >
            <option value="">
                Semua Tahun
            </option>

            @foreach($monitoring['available_years'] ?? [] as $yearItem)
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

    <div class="ssm-field">
        <label for="status">
            Status
        </label>

        <select
            id="status"
            name="status"
            class="ssm-control"
        >
            <option value="">
                Semua Status
            </option>

            @foreach($statusOptions as $statusItem)
                <option
                    value="{{ $statusItem['key'] }}"
                    @selected(
                        ($filters['status'] ?? null)
                        === $statusItem['key']
                    )
                >
                    {{ $statusItem['label'] }}
                    ({{ $statusItem['count'] }})
                </option>
            @endforeach
        </select>
    </div>

    <button
        type="submit"
        class="aa-action-button primary"
    >
        Terapkan
    </button>

    <a
        href="{{ route('admin-all.suggestion.monitoring') }}"
        class="aa-action-button"
    >
        Reset
    </a>
</form>

<div class="ssm-summary">
    <div class="ssm-stage-card submitted">
        <span class="ssm-stage-icon">1</span>

        <small>Submitted</small>

        <strong>
            {{ number_format($stageCounts['submitted'] ?? 0) }}
        </strong>

        <span>Menunggu verifikasi GL / QCC</span>
    </div>

    <div class="ssm-stage-card gl-qcc">
        <span class="ssm-stage-icon">2</span>

        <small>Verifikasi GL / QCC</small>

        <strong>
            {{ number_format($stageCounts['gl_qcc'] ?? 0) }}
        </strong>

        <span>Sudah lolos GL / Tim QCC</span>
    </div>

    <div class="ssm-stage-card sh">
        <span class="ssm-stage-icon">3</span>

        <small>Persetujuan SH</small>

        <strong>
            {{ number_format($stageCounts['sh'] ?? 0) }}
        </strong>

        <span>Sudah disetujui Section Head</span>
    </div>

    <div class="ssm-stage-card dh-pm">
        <span class="ssm-stage-icon">4</span>

        <small>Persetujuan DH / PM</small>

        <strong>
            {{ number_format($stageCounts['dh_pm'] ?? 0) }}
        </strong>

        <span>Sudah disetujui DH / PM</span>
    </div>

    <div class="ssm-stage-card selesai">
        <span class="ssm-stage-icon">✓</span>

        <small>Selesai</small>

        <strong>
            {{ number_format($stageCounts['selesai'] ?? 0) }}
        </strong>

        <span>Status final Suggestion System</span>
    </div>
</div>

<div class="ssm-table-card">
    <div class="ssm-table-head">
        <div>
            <h2>Database Suggestion System</h2>

            <p>
                Menampilkan
                {{ number_format($monitoringRows->count()) }}
                data di halaman ini dari
                {{ number_format($monitoringRows->total()) }}
                hasil filter.
            </p>
        </div>

        @if(!empty($filters['nrp']))
            <a
                href="{{ route('admin-all.suggestion.monitoring', array_filter([
                    'q' => $filters['q'] ?? null,
                    'month' => $filters['month'] ?? null,
                    'year' => $filters['year'] ?? null,
                    'status' => $filters['status'] ?? null,
                ])) }}"
                class="aa-action-button"
            >
                Hapus Filter NRP {{ $filters['nrp'] }}
            </a>
        @endif
    </div>

    <div class="ssm-table-scroll">
        <table class="ssm-table">
            <thead>
                <tr>
                    <th>No SS</th>
                    <th>Submit</th>
                    <th>NRP</th>
                    <th>Nama</th>
                    <th>Departemen</th>
                    <th>Lokasi</th>
                    <th>Judul Suggestion</th>
                    <th>Status</th>
                    <th>GL / QCC</th>
                    <th>SH</th>
                    <th>Print</th>
                    <th>Dokumen</th>
                </tr>
            </thead>

            <tbody>
                @forelse($monitoringRows as $row)
                    <tr>
                        <td>
                            <strong>
                                {{ $row['NO_SS'] ?? '-' }}
                            </strong>
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
                            {{ $row['DEPARTEMEN'] ?? '-' }}
                        </td>

                        <td>
                            {{ $row['LOKASI'] ?? '-' }}
                        </td>

                        <td class="ssm-title-cell">
                            {{ $row['JUDUL_SS'] ?? '-' }}
                        </td>

                        <td>
                            <span
                                class="ssm-status {{ $statusClass($row['STATUS'] ?? '') }}"
                            >
                                {{ $statusLabel($row['STATUS'] ?? '') }}
                            </span>
                        </td>

                        <td>
                            {{ $row['STATUS_GL_QCC'] ?? '-' }}
                        </td>

                        <td>
                            {{ $row['STATUS_SH'] ?? '-' }}
                        </td>

                        <td>
                            {{ $row['PRINT_STATUS'] ?? '-' }}
                        </td>

                        <td>
                            <span class="ssm-docs">
                                @if(!empty($row['FOLDER_SS_URL']))
                                    <a
                                        href="{{ $row['FOLDER_SS_URL'] }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        Folder
                                    </a>
                                @endif

                                @if(!empty($row['FILE_EXCEL_URL']))
                                    <a
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
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td
                            colspan="12"
                            class="ssm-empty"
                        >
                            Tidak ada data Suggestion System
                            yang cocok dengan filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="ssm-pagination">
        <span>
            Halaman
            {{ $monitoringRows->currentPage() }}
            dari
            {{ max(1, $monitoringRows->lastPage()) }}
        </span>

        <div class="ssm-page-actions">
            <a
                href="{{ $monitoringRows->previousPageUrl() ?? '#' }}"
                class="ssm-page-button {{ $monitoringRows->onFirstPage() ? 'disabled' : '' }}"
            >
                ← Sebelumnya
            </a>

            <a
                href="{{ $monitoringRows->nextPageUrl() ?? '#' }}"
                class="ssm-page-button {{ $monitoringRows->hasMorePages() ? '' : 'disabled' }}"
            >
                Berikutnya →
            </a>
        </div>
    </div>
</div>
@endsection
