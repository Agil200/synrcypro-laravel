<style>
    .internal-upload-page {
        min-width: 0;
        color: #172033;
        font-family: Arial, Helvetica, sans-serif;
    }

    .internal-upload-title {
        margin: 0 0 8px;
        color: #14213d;
        font-size: 28px;
        font-weight: 800;
    }

    .internal-upload-panel {
        display: flex;
        min-width: 0;
        height: calc(100vh - 155px);
        min-height: 520px;
        flex-direction: column;
        padding: 22px;
        overflow: hidden;
        border-radius: 20px;
        background: #eeeeee;
    }

    /*
     * Bagian judul, filter, statistik, dan tab status tidak ikut bergerak.
     * Hanya daftar kartu di bawahnya yang memiliki scroll sendiri.
     */
    .internal-upload-header {
        position: relative;
        z-index: 20;
        flex: 0 0 auto;
        margin:
            -22px
            -22px
            0;
        padding:
            22px
            22px
            12px;
        border-bottom: 1px solid #d6d9de;
        background: #eeeeee;
        box-shadow: 0 8px 14px rgba(15, 23, 42, 0.10);
    }

    .internal-upload-scroll-area {
        min-height: 0;
        flex: 1 1 auto;
        padding-top: 14px;
        padding-right: 5px;
        overflow-x: hidden;
        overflow-y: auto;
        overscroll-behavior: contain;
        scrollbar-gutter: stable;
    }

    .internal-upload-card {
        position: relative;
        z-index: 1;
    }

    .internal-upload-panel-title {
        margin: 0 0 18px;
        font-size: 16px;
        font-weight: 800;
    }

    .internal-upload-filter {
        display: grid;
        grid-template-columns:
            minmax(230px, 1fr)
            155px
            165px
            125px
            105px
            120px
            95px;
        gap: 12px;
        align-items: end;
        margin-bottom: 17px;
    }

    .internal-upload-field {
        display: flex;
        min-width: 0;
        flex-direction: column;
        gap: 6px;
    }

    .internal-upload-label {
        color: #374151;
        font-size: 9px;
        font-weight: 700;
    }

    .internal-upload-input,
    .internal-upload-select {
        width: 100%;
        height: 38px;
        padding: 0 13px;
        border: 1px solid #d5d9df;
        border-radius: 8px;
        outline: none;
        background: #ffffff;
        font-size: 11px;
    }

    .internal-upload-button {
        display: inline-flex;
        height: 38px;
        align-items: center;
        justify-content: center;
        padding: 0 14px;
        border: 0;
        border-radius: 8px;
        color: #ffffff;
        cursor: pointer;
        font-size: 10px;
        font-weight: 800;
        text-decoration: none;
    }

    .internal-upload-button.search {
        background: #167df4;
    }

    .internal-upload-button.source {
        background: #606060;
    }

    .internal-upload-button.refresh {
        background: #159447;
    }

    .internal-upload-statistics {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    .internal-upload-stat {
        position: relative;
        min-height: 76px;
        padding: 12px 13px;
        overflow: hidden;
        border: 1px solid #d5d9df;
        border-radius: 10px;
        background: #ffffff;
    }

    .internal-upload-stat::after {
        position: absolute;
        right: 0;
        bottom: 0;
        left: 0;
        height: 4px;
        content: "";
    }

    .internal-upload-stat.total::after {
        background: #374151;
    }

    .internal-upload-stat.upload::after {
        background: #147df5;
    }

    .internal-upload-stat.complete::after {
        background: #1c9c5b;
    }

    .internal-upload-stat.incomplete::after {
        background: #f0a000;
    }

    .internal-upload-stat.warning::after {
        background: #eab308;
    }

    .internal-upload-stat.expired::after {
        background: #e51e2a;
    }

    .internal-upload-stat-label {
        display: block;
        min-height: 22px;
        margin-bottom: 6px;
        color: #4b5563;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .internal-upload-stat-value {
        display: block;
        color: #111827;
        font-size: 23px;
        font-weight: 900;
        line-height: 1;
    }

    .internal-upload-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .internal-upload-tab {
        display: inline-flex;
        min-height: 30px;
        align-items: center;
        justify-content: center;
        padding: 0 13px;
        border: 1px solid #d5d9df;
        border-radius: 999px;
        color: #374151;
        background: #ffffff;
        font-size: 9px;
        font-weight: 800;
        text-decoration: none;
    }

    .internal-upload-tab.active {
        border-color: #172033;
        color: #ffffff;
        background: #172033;
    }

    .internal-upload-alert {
        margin: 0 0 13px;
        padding: 12px;
        border: 1px solid #fecaca;
        border-radius: 8px;
        color: #991b1b;
        background: #fef2f2;
        font-size: 11px;
        font-weight: 700;
    }

    .internal-upload-scroll-area::-webkit-scrollbar {
        width: 10px;
    }

    .internal-upload-scroll-area::-webkit-scrollbar-track {
        border-radius: 999px;
        background: #e5e7eb;
    }

    .internal-upload-scroll-area::-webkit-scrollbar-thumb {
        border: 2px solid #e5e7eb;
        border-radius: 999px;
        background: #9ca3af;
    }

    .internal-upload-meta {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 12px;
        color: #64748b;
        font-size: 10px;
        font-weight: 700;
    }

    .internal-upload-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 13px;
    }

    .internal-upload-card {
        min-width: 0;
        padding: 15px;
        border: 1px solid #d8dde4;
        border-radius: 11px;
        background: #ffffff;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
    }

    .internal-upload-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
    }

    .internal-upload-identity {
        min-width: 0;
    }

    .internal-upload-name {
        margin: 0 0 4px;
        overflow: hidden;
        font-size: 12px;
        font-weight: 900;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .internal-upload-nrp {
        color: #64748b;
        font-size: 9px;
        font-weight: 700;
    }

    .internal-upload-badges {
        display: flex;
        flex: 0 0 auto;
        flex-direction: column;
        align-items: flex-end;
        gap: 5px;
    }

    .internal-upload-status {
        display: inline-flex;
        min-height: 22px;
        align-items: center;
        justify-content: center;
        padding: 0 9px;
        border-radius: 999px;
        color: #ffffff;
        font-size: 8px;
        font-weight: 900;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .internal-upload-status.lengkap,
    .internal-upload-status.aktif {
        background: #20965b;
    }

    .internal-upload-status.belum-lengkap {
        background: #eb9b00;
    }

    .internal-upload-status.akan-expired {
        color: #111827;
        background: #facc15;
    }

    .internal-upload-status.expired {
        background: #df1d2a;
    }

    .internal-upload-status.tidak-diketahui {
        color: #374151;
        background: #e5e7eb;
    }

    .internal-upload-information {
        display: grid;
        grid-template-columns: 112px minmax(0, 1fr);
        gap: 7px;
        margin-bottom: 13px;
        font-size: 9px;
    }

    .internal-upload-information dt {
        color: #64748b;
        font-weight: 700;
    }

    .internal-upload-information dd {
        margin: 0;
        overflow: hidden;
        color: #1f2937;
        font-weight: 800;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .internal-upload-time {
        color: #167df4 !important;
    }

    .internal-upload-progress-head {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 6px;
        color: #4b5563;
        font-size: 9px;
        font-weight: 800;
    }

    .internal-upload-progress-track {
        height: 7px;
        overflow: hidden;
        border-radius: 999px;
        background: #e5e7eb;
    }

    .internal-upload-progress-bar {
        height: 100%;
        border-radius: inherit;
        background: #1880f7;
    }

    .internal-upload-details {
        margin-top: 13px;
        border-top: 1px solid #e5e7eb;
        padding-top: 11px;
    }

    .internal-upload-details summary {
        display: inline-flex;
        min-height: 29px;
        align-items: center;
        justify-content: center;
        padding: 0 12px;
        border-radius: 7px;
        color: #ffffff;
        background: #172033;
        cursor: pointer;
        font-size: 9px;
        font-weight: 800;
        list-style: none;
    }

    .internal-upload-document-list {
        display: grid;
        gap: 7px;
        margin: 12px 0 0;
        padding: 0;
        list-style: none;
    }

    .internal-upload-document {
        display: flex;
        min-height: 29px;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 4px 9px;
        border-radius: 6px;
        background: #f5f7fa;
        font-size: 9px;
        font-weight: 700;
    }

    .internal-upload-document-status.available {
        color: #168148;
    }

    .internal-upload-document-status.missing {
        color: #d51c29;
    }

    .document-link {
        margin-left: 7px;
        color: #167df4;
        font-weight: 800;
        text-decoration: none;
    }

    .internal-upload-empty {
        grid-column: 1 / -1;
        padding: 35px;
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        color: #64748b;
        background: #ffffff;
        text-align: center;
        font-size: 11px;
        font-weight: 700;
    }

    .pagination-box {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 16px;
    }

    .pagination-info {
        color: #64748b;
        font-size: 10px;
        font-weight: 700;
    }

    .pagination-links {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .page-link {
        display: inline-flex;
        min-width: 32px;
        height: 32px;
        align-items: center;
        justify-content: center;
        padding: 0 10px;
        border: 1px solid #d1d5db;
        border-radius: 7px;
        color: #374151;
        background: #ffffff;
        font-size: 10px;
        font-weight: 800;
        text-decoration: none;
    }

    .page-link.active {
        border-color: #167df4;
        color: #ffffff;
        background: #167df4;
    }

    .page-link.disabled {
        color: #9ca3af;
        background: #f3f4f6;
        pointer-events: none;
    }

    @media (max-width: 1450px) {
        .internal-upload-filter {
            grid-template-columns:
                minmax(230px, 1fr)
                145px
                155px
                120px;
        }

        .internal-upload-statistics {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 950px) {
        .internal-upload-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 700px) {
        .internal-upload-filter,
        .internal-upload-statistics,
        .internal-upload-grid {
            grid-template-columns: 1fr;
        }

        .internal-upload-panel {
            height: auto;
            min-height: 0;
            padding: 15px;
            overflow: visible;
        }

        .internal-upload-header {
            margin:
                -15px
                -15px
                0;
            padding:
                15px
                15px
                10px;
        }

        .internal-upload-scroll-area {
            overflow: visible;
            padding-right: 0;
        }
    }
</style>

<div class="internal-upload-page">
    @php
        $employeePaginator = $employeePaginator ?? null;

        $currentPage = $employeePaginator
            ? $employeePaginator->currentPage()
            : 1;

        $lastPage = $employeePaginator
            ? $employeePaginator->lastPage()
            : 1;

        $pageStart = max(1, $currentPage - 2);
        $pageEnd = min($lastPage, $currentPage + 2);
    @endphp

    <h1 class="internal-upload-title">
        Mine Permit
    </h1>

    <section class="internal-upload-panel">
        <div class="internal-upload-header">
            <h2 class="internal-upload-panel-title">
                MONITORING INTERNAL UPLOAD
            </h2>

            <form
                method="GET"
                action="{{ route(
                    'mine-permit.monitoring-internal-upload'
                ) }}"
                class="internal-upload-filter"
            >
                <div class="internal-upload-field">
                    <label
                        for="internalSearch"
                        class="internal-upload-label"
                    >
                        Pencarian
                    </label>

                    <input
                        id="internalSearch"
                        name="search"
                        type="search"
                        class="internal-upload-input"
                        placeholder="NRP / NAMA / JABATAN / VERSATILITY"
                        value="{{ $search ?? request('search') }}"
                    >
                </div>

                <div class="internal-upload-field">
                    <label
                        for="internalYear"
                        class="internal-upload-label"
                    >
                        Tahun Permit
                    </label>

                    <select
                        id="internalYear"
                        name="year"
                        class="internal-upload-select"
                    >
                        <option
                            value="all"
                            @selected(
                                ($selectedYear ?? 'all') === 'all'
                            )
                        >
                            Semua Tahun
                        </option>

                        @foreach (($availableYears ?? []) as $year)
                            <option
                                value="{{ $year }}"
                                @selected(
                                    (string) (
                                        $selectedYear ?? ''
                                    ) === (string) $year
                                )
                            >
                                Tahun {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="internal-upload-field">
                    <label
                        for="internalStatus"
                        class="internal-upload-label"
                    >
                        Status
                    </label>

                    <select
                        id="internalStatus"
                        name="status"
                        class="internal-upload-select"
                    >
                        @foreach ([
                            'semua' => 'Semua Status',
                            'lengkap' => 'Dokumen Lengkap',
                            'belum-lengkap' => 'Belum Lengkap',
                            'akan-expired' => 'Akan Expired',
                            'expired' => 'Expired',
                        ] as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(
                                    ($selectedStatus ?? 'semua')
                                    === $value
                                )
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="internal-upload-field">
                    <label
                        for="internalPerPage"
                        class="internal-upload-label"
                    >
                        Data per halaman
                    </label>

                    <select
                        id="internalPerPage"
                        name="per_page"
                        class="internal-upload-select"
                    >
                        @foreach ([18, 30, 60] as $option)
                            <option
                                value="{{ $option }}"
                                @selected(
                                    ($perPage ?? 30) === $option
                                )
                            >
                                {{ $option }} data
                            </option>
                        @endforeach
                    </select>
                </div>

                <button
                    type="submit"
                    class="internal-upload-button search"
                >
                    SEARCH
                </button>

                <a
                    href="https://docs.google.com/spreadsheets/d/1mTNAEp3x80EvskHgVFl4mXv6OL78-0G9xbgVFLjqO6E/edit?gid=742696375#gid=742696375"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="internal-upload-button source"
                >
                    SUMBER DATA
                </a>

                <a
                    href="{{ route(
                        'mine-permit.monitoring-internal-upload',
                        [
                            'refresh' => now()->timestamp,
                            'year' => 'all',
                            'status' => 'semua',
                            'per_page' => $perPage ?? 30,
                        ]
                    ) }}"
                    class="internal-upload-button refresh"
                    title="Ambil ulang data dan hapus seluruh pencarian/filter"
                >
                    REFRESH
                </a>
            </form>

            <div class="internal-upload-statistics">
                <article class="internal-upload-stat total">
                    <span class="internal-upload-stat-label">
                        Total Data {{ $selectedYearLabel ?? 'Semua Tahun' }}
                    </span>

                    <strong class="internal-upload-stat-value">
                        {{ $totalData ?? 0 }}
                    </strong>
                </article>

                <article class="internal-upload-stat upload">
                    <span class="internal-upload-stat-label">
                        Upload Bulan Ini
                    </span>

                    <strong class="internal-upload-stat-value">
                        {{ $totalUploadBulanIni ?? 0 }}
                    </strong>
                </article>

                <article class="internal-upload-stat complete">
                    <span class="internal-upload-stat-label">
                        Dokumen Lengkap
                    </span>

                    <strong class="internal-upload-stat-value">
                        {{ $totalLengkap ?? 0 }}
                    </strong>
                </article>

                <article class="internal-upload-stat incomplete">
                    <span class="internal-upload-stat-label">
                        Belum Lengkap
                    </span>

                    <strong class="internal-upload-stat-value">
                        {{ $totalBelumLengkap ?? 0 }}
                    </strong>
                </article>

                <article class="internal-upload-stat warning">
                    <span class="internal-upload-stat-label">
                        Akan Expired ≤ 30 Hari
                    </span>

                    <strong class="internal-upload-stat-value">
                        {{ $totalAkanExpired ?? 0 }}
                    </strong>
                </article>

                <article class="internal-upload-stat expired">
                    <span class="internal-upload-stat-label">
                        Expired
                    </span>

                    <strong class="internal-upload-stat-value">
                        {{ $totalExpired ?? 0 }}
                    </strong>
                </article>
            </div>

            <nav
                class="internal-upload-tabs"
                aria-label="Filter status"
            >
                @foreach ([
                    'semua' => 'SEMUA',
                    'lengkap' => 'LENGKAP',
                    'belum-lengkap' => 'BELUM LENGKAP',
                    'akan-expired' => 'AKAN EXPIRED',
                    'expired' => 'EXPIRED',
                ] as $statusValue => $statusLabel)
                    <a
                        href="{{ route(
                            'mine-permit.monitoring-internal-upload',
                            [
                                'status' => $statusValue,
                                'year' => $selectedYear ?? 'all',
                                'search' => $search
                                    ?? request('search'),
                                'per_page' => $perPage ?? 30,
                            ]
                        ) }}"
                        class="internal-upload-tab
                            {{ ($selectedStatus ?? 'semua')
                                === $statusValue
                                    ? 'active'
                                    : '' }}"
                    >
                        {{ $statusLabel }}
                    </a>
                @endforeach
            </nav>
        </div>

        <div class="internal-upload-scroll-area">

        @if ($sheetError ?? null)
            <div class="internal-upload-alert">
                {{ $sheetError }}
            </div>
        @endif

        <div class="internal-upload-meta">
            <span>
                Menampilkan
                {{ $employeePaginator?->firstItem() ?? 0 }}
                –
                {{ $employeePaginator?->lastItem() ?? 0 }}
                dari
                {{ $employeePaginator?->total() ?? 0 }}
                hasil ·
                {{ $selectedYearLabel ?? 'Semua Tahun' }}
            </span>

            <span>
                Sinkronisasi terakhir:
                {{ $lastSyncedAt ?? '-' }}
            </span>
        </div>

        <div class="internal-upload-grid">
            @forelse (($employeePaginator ?? []) as $employee)
                @php
                    $totalDokumen = (int) (
                        $employee['total_dokumen'] ?? 0
                    );

                    $dokumenTerisi = (int) (
                        $employee['dokumen_terisi'] ?? 0
                    );

                    $percentage = $totalDokumen > 0
                        ? round(
                            ($dokumenTerisi / $totalDokumen)
                            * 100
                        )
                        : 0;

                    $documentStatus =
                        $employee['document_status']
                        ?? 'belum-lengkap';

                    $permitStatus =
                        $employee['permit_status']
                        ?? 'tidak-diketahui';

                    $documentLabel =
                        $documentStatus === 'lengkap'
                            ? 'Dokumen Lengkap'
                            : 'Belum Lengkap';

                    $permitLabel = match ($permitStatus) {
                        'aktif' => 'Permit Aktif',
                        'akan-expired' => 'Akan Expired',
                        'expired' => 'Expired',
                        default => 'Tanggal Tidak Diketahui',
                    };

                    $daysRemaining =
                        $employee['days_remaining'] ?? null;

                    $remainingLabel = match (true) {
                        $daysRemaining === null => '-',

                        $daysRemaining < 0 =>
                            'Lewat ' .
                            abs($daysRemaining) .
                            ' hari',

                        $daysRemaining === 0 =>
                            'Berakhir hari ini',

                        default =>
                            $daysRemaining .
                            ' hari lagi',
                    };
                @endphp

                <article class="internal-upload-card">
                    <div class="internal-upload-card-header">
                        <div class="internal-upload-identity">
                            <h3 class="internal-upload-name">
                                {{ $employee['nama'] ?: '-' }}
                            </h3>

                            <span class="internal-upload-nrp">
                                NRP:
                                {{ $employee['nrp'] ?: '-' }}
                            </span>
                        </div>

                        <div class="internal-upload-badges">
                            <span
                                class="internal-upload-status
                                    {{ $documentStatus }}"
                            >
                                {{ $documentLabel }}
                            </span>

                            <span
                                class="internal-upload-status
                                    {{ $permitStatus }}"
                            >
                                {{ $permitLabel }}
                            </span>
                        </div>
                    </div>

                    <dl class="internal-upload-information">
                        <dt>Jabatan/Unit</dt>
                        <dd>{{ $employee['jabatan'] ?: '-' }}</dd>

                        <dt>Jabatan</dt>
                        <dd>
                            {{ $employee['jabatan_tambahan']
                                ?: '-' }}
                        </dd>

                        <dt>Versatility Unit</dt>
                        <dd>
                            {{ $employee['versatility'] ?: '-' }}
                        </dd>

                        <dt>Berlaku Permit</dt>
                        <dd>
                            {{ $employee['tanggal_berlaku']
                                ?: '-' }}
                        </dd>

                        <dt>Sisa Berlaku</dt>
                        <dd>{{ $remainingLabel }}</dd>

                        <dt>Timestamp Upload</dt>
                        <dd class="internal-upload-time">
                            {{ $employee['uploaded_at'] ?: '-' }}
                        </dd>
                    </dl>

                    <div class="internal-upload-progress-head">
                        <span>Kelengkapan dokumen</span>

                        <span>
                            {{ $dokumenTerisi }}
                            /
                            {{ $totalDokumen }}
                            ·
                            {{ $percentage }}%
                        </span>
                    </div>

                    <div
                        class="internal-upload-progress-track"
                        aria-label="Kelengkapan {{ $percentage }} persen"
                    >
                        <div
                            class="internal-upload-progress-bar"
                            style="width: {{ $percentage }}%;"
                        ></div>
                    </div>

                    <details class="internal-upload-details">
                        <summary>
                            DETAIL DOKUMEN
                        </summary>

                        <ul class="internal-upload-document-list">
                            @foreach (
                                ($employee['documents'] ?? [])
                                as $documentName => $document
                            )
                                @php
                                    $available =
                                        (bool) (
                                            $document['available']
                                            ?? false
                                        );

                                    $documentUrl =
                                        $document['url'] ?? null;
                                @endphp

                                <li class="internal-upload-document">
                                    <span>{{ $documentName }}</span>

                                    <span
                                        class="internal-upload-document-status
                                            {{ $available
                                                ? 'available'
                                                : 'missing' }}"
                                    >
                                        {{ $available
                                            ? '✓ Tersedia'
                                            : '✕ Belum Upload' }}

                                        @if (
                                            $available &&
                                            $documentUrl
                                        )
                                            <a
                                                href="{{ $documentUrl }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="document-link"
                                            >
                                                Lihat
                                            </a>
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </details>
                </article>
            @empty
                <div class="internal-upload-empty">
                    Tidak ada data untuk filter yang dipilih.
                    Coba pilih “Semua Tahun”.
                </div>
            @endforelse
        </div>

        @if (
            $employeePaginator &&
            $employeePaginator->hasPages()
        )
            <nav
                class="pagination-box"
                aria-label="Pagination Internal Upload"
            >
                <span class="pagination-info">
                    Halaman {{ $currentPage }}
                    dari {{ $lastPage }}
                </span>

                <div class="pagination-links">
                    <a
                        href="{{ $employeePaginator
                            ->previousPageUrl() ?? '#' }}"
                        class="page-link
                            {{ $employeePaginator
                                ->onFirstPage()
                                    ? 'disabled'
                                    : '' }}"
                    >
                        ‹
                    </a>

                    @for (
                        $page = $pageStart;
                        $page <= $pageEnd;
                        $page++
                    )
                        <a
                            href="{{ $employeePaginator
                                ->url($page) }}"
                            class="page-link
                                {{ $page === $currentPage
                                    ? 'active'
                                    : '' }}"
                        >
                            {{ $page }}
                        </a>
                    @endfor

                    <a
                        href="{{ $employeePaginator
                            ->nextPageUrl() ?? '#' }}"
                        class="page-link
                            {{ $employeePaginator
                                ->hasMorePages()
                                    ? ''
                                    : 'disabled' }}"
                    >
                        ›
                    </a>
                </div>
            </nav>
        @endif

        </div>
    </section>
</div>