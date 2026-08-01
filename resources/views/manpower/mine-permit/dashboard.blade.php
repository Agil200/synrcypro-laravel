<style>
    .mp-dashboard {
        display: flex;
        width: 100%;
        height: 100%;
        min-height: 0;
        flex-direction: column;
        overflow: hidden;
        color: #111827;
        font-family: Arial, Helvetica, sans-serif;
    }

    .mp-dashboard-title {
        flex: 0 0 auto;
        margin: 0 0 8px;
        color: #10213d;
        font-size: 27px;
        font-weight: 900;
        line-height: 1.15;
    }

    .mp-dashboard-panel {
        position: relative;
        display: flex;
        min-width: 0;
        min-height: 0;
        flex: 1 1 auto;
        flex-direction: column;
        overflow: hidden;
        border-radius: 20px;
        background: #eeeeee;
    }

    /*
     * Header dashboard tidak memakai position: sticky.
     * Header menjadi bagian tetap dari panel, sedangkan hanya konten
     * grafik dan tabel di bawahnya yang memiliki scroll sendiri.
     * Cara ini lebih kokoh dan tidak membuat konten bocor dari belakang.
     */
    .mp-dashboard-sticky {
        position: relative;
        z-index: 20;
        flex: 0 0 auto;
        padding:
            20px
            21px
            13px;
        overflow: hidden;
        border-bottom: 1px solid #d6d9de;
        border-radius: 20px 20px 0 0;
        background: #eeeeee;
        box-shadow: 0 9px 18px rgba(15, 23, 42, 0.12);
    }

    .mp-dashboard-body {
        min-width: 0;
        min-height: 0;
        flex: 1 1 auto;
        padding:
            14px
            21px
            21px;
        overflow-x: hidden;
        overflow-y: auto;
        overscroll-behavior: contain;
        scrollbar-gutter: stable;
        background: #eeeeee;
    }

    .mp-dashboard-body::-webkit-scrollbar {
        width: 10px;
    }

    .mp-dashboard-body::-webkit-scrollbar-track {
        border-radius: 999px;
        background: #e2e8f0;
    }

    .mp-dashboard-body::-webkit-scrollbar-thumb {
        border: 2px solid #e2e8f0;
        border-radius: 999px;
        background: #94a3b8;
    }

    .mp-dashboard-sync-top {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 8px;
        margin: -5px 0 11px;
        color: #64748b;
        font-size: 8px;
        font-weight: 700;
    }

    .mp-dashboard-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 16px;
    }

    .mp-dashboard-heading {
        margin: 0;
        font-size: 17px;
        font-weight: 900;
    }

    .mp-dashboard-filter {
        display: flex;
        flex-wrap: wrap;
        align-items: end;
        justify-content: flex-end;
        gap: 10px;
    }

    .mp-field {
        display: flex;
        min-width: 140px;
        flex-direction: column;
        gap: 6px;
    }

    .mp-label {
        color: #475569;
        font-size: 9px;
        font-weight: 800;
    }

    .mp-select {
        height: 38px;
        padding: 0 12px;
        border: 1px solid #d2d7de;
        border-radius: 8px;
        color: #334155;
        background: #ffffff;
        font-size: 11px;
        outline: none;
    }

    .mp-button {
        display: inline-flex;
        height: 38px;
        align-items: center;
        justify-content: center;
        padding: 0 15px;
        border: 0;
        border-radius: 8px;
        color: #ffffff;
        cursor: pointer;
        font-size: 10px;
        font-weight: 900;
        text-decoration: none;
    }

    .mp-button.apply {
        background: #147df5;
    }

    .mp-button.refresh {
        background: #159447;
    }

    .mp-button.she {
        background: #334155;
    }

    .mp-button.internal {
        background: #7c3aed;
    }

    .mp-errors {
        display: grid;
        gap: 8px;
        margin-bottom: 14px;
    }

    .mp-error {
        padding: 11px 13px;
        border: 1px solid #fecaca;
        border-radius: 8px;
        color: #991b1b;
        background: #fef2f2;
        font-size: 10px;
        font-weight: 700;
    }

    .mp-primary-stats {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 0;
    }

    .mp-stat {
        position: relative;
        display: block;
        min-height: 74px;
        padding: 11px 13px;
        overflow: hidden;
        border: 1px solid #d2d7de;
        border-radius: 10px;
        color: inherit;
        background: #ffffff;
        text-decoration: none;
        transition:
            transform 0.16s ease,
            box-shadow 0.16s ease,
            border-color 0.16s ease;
    }

    .mp-stat:hover {
        transform: translateY(-2px);
        border-color: #aeb7c3;
        box-shadow: 0 8px 16px rgba(15, 23, 42, 0.10);
    }

    .mp-stat:focus-visible {
        outline: 3px solid rgba(20, 125, 245, 0.24);
        outline-offset: 2px;
    }

    .mp-stat-hint {
        position: absolute;
        right: 12px;
        bottom: 10px;
        color: #94a3b8;
        font-size: 7px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .mp-stat::after {
        position: absolute;
        right: 0;
        bottom: 0;
        left: 0;
        height: 4px;
        content: "";
    }

    .mp-stat.total::after {
        background: #334155;
    }

    .mp-stat.success::after {
        background: #16a05d;
    }

    .mp-stat.process::after {
        background: #f2b705;
    }

    .mp-stat.failed::after {
        background: #ed1c2e;
    }

    .mp-stat.document::after {
        background: #f59e0b;
    }

    .mp-stat.warning::after {
        background: #7c3aed;
    }

    .mp-stat-label {
        display: block;
        min-height: 23px;
        margin-bottom: 7px;
        color: #475569;
        font-size: 8px;
        font-weight: 900;
        line-height: 1.35;
        text-transform: uppercase;
    }

    .mp-stat-value {
        display: block;
        color: #111827;
        font-size: 25px;
        font-weight: 900;
        line-height: 1;
    }

    .mp-dashboard-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.65fr) minmax(320px, 1fr);
        gap: 14px;
        margin-bottom: 14px;
    }

    .mp-card {
        min-width: 0;
        padding: 16px;
        border: 1px solid #d8dde4;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 7px 18px rgba(15, 23, 42, 0.05);
    }

    .mp-card-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 13px;
    }

    .mp-card-title {
        margin: 0;
        color: #172033;
        font-size: 13px;
        font-weight: 900;
    }

    .mp-card-subtitle {
        color: #64748b;
        font-size: 9px;
        font-weight: 700;
    }

    .mp-trend {
        display: grid;
        height: 245px;
        grid-template-columns: repeat(12, minmax(28px, 1fr));
        gap: 8px;
        align-items: end;
        padding: 13px 6px 0;
        border-top: 1px solid #eef0f3;
    }

    .mp-month {
        display: flex;
        min-width: 0;
        height: 100%;
        flex-direction: column;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
    }

    .mp-month-value {
        color: #64748b;
        font-size: 8px;
        font-weight: 800;
    }

    .mp-bar {
        display: flex;
        width: min(28px, 75%);
        min-height: 3px;
        flex-direction: column-reverse;
        overflow: hidden;
        border-radius: 6px 6px 2px 2px;
        background: #f1f5f9;
        box-shadow: inset 0 0 0 1px #e2e8f0;
    }

    .mp-bar-segment {
        width: 100%;
    }

    .mp-bar-segment.success {
        background: #16a05d;
    }

    .mp-bar-segment.process {
        background: #f2b705;
    }

    .mp-bar-segment.failed {
        background: #ed1c2e;
    }

    .mp-month-label {
        color: #475569;
        font-size: 8px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .mp-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 12px;
        color: #475569;
        font-size: 9px;
        font-weight: 800;
    }

    .mp-legend-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .mp-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
    }

    .mp-dot.success {
        background: #16a05d;
    }

    .mp-dot.process {
        background: #f2b705;
    }

    .mp-dot.failed {
        background: #ed1c2e;
    }

    .mp-dot.active {
        background: #16a05d;
    }

    .mp-dot.warning {
        background: #f2b705;
    }

    .mp-dot.expired {
        background: #ed1c2e;
    }

    .mp-dot.unknown {
        background: #cbd5e1;
    }

    .mp-permit-summary {
        display: grid;
        grid-template-columns: 170px minmax(0, 1fr);
        gap: 17px;
        align-items: center;
        min-height: 245px;
    }

    .mp-donut {
        position: relative;
        width: 165px;
        height: 165px;
        border-radius: 50%;
    }

    .mp-donut::after {
        position: absolute;
        inset: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: #111827;
        background: #ffffff;
        content: attr(data-total);
        font-size: 25px;
        font-weight: 900;
        box-shadow: inset 0 0 0 1px #eef0f3;
    }

    .mp-permit-list {
        display: grid;
        gap: 10px;
    }

    .mp-permit-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding-bottom: 9px;
        border-bottom: 1px solid #eef0f3;
        font-size: 10px;
        font-weight: 800;
    }

    .mp-permit-row:last-child {
        border-bottom: 0;
    }

    .mp-permit-number {
        font-size: 15px;
        font-weight: 900;
    }

    .mp-table-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .mp-table-wrapper {
        max-height: 340px;
        overflow: auto;
        border: 1px solid #e0e4e9;
        border-radius: 9px;
    }

    .mp-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .mp-table th {
        position: sticky;
        z-index: 2;
        top: 0;
        padding: 9px 10px;
        border-bottom: 1px solid #d8dde4;
        color: #475569;
        background: #f8fafc;
        font-size: 8px;
        font-weight: 900;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .mp-table td {
        padding: 9px 10px;
        border-bottom: 1px solid #eef0f3;
        color: #334155;
        font-size: 9px;
        vertical-align: middle;
    }

    .mp-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .mp-table tbody tr:hover td {
        background: #f8fbff;
    }

    .mp-text-cut {
        max-width: 155px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .mp-badge {
        display: inline-flex;
        min-height: 19px;
        align-items: center;
        justify-content: center;
        padding: 2px 8px;
        border-radius: 999px;
        color: #ffffff;
        font-size: 7px;
        font-weight: 900;
        text-align: center;
        white-space: nowrap;
    }

    .mp-badge.success {
        background: #16a05d;
    }

    .mp-badge.process {
        color: #111827;
        background: #f2b705;
    }

    .mp-badge.failed {
        background: #ed1c2e;
    }

    .mp-badge.warning {
        color: #111827;
        background: #facc15;
    }

    .mp-empty {
        padding: 28px !important;
        color: #64748b !important;
        text-align: center;
        font-weight: 700;
    }

    .mp-footer-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 9px;
        margin-top: 14px;
    }

    .mp-sync {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 8px;
        margin-top: 13px;
        color: #64748b;
        font-size: 9px;
        font-weight: 700;
    }

    @media (max-width: 1350px) {
        .mp-primary-stats {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .mp-dashboard-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 950px) {
        .mp-table-grid {
            grid-template-columns: 1fr;
        }

        .mp-permit-summary {
            grid-template-columns: 1fr;
            justify-items: center;
        }
    }

    @media (max-width: 700px) {
        .mp-dashboard {
            height: auto;
            overflow: visible;
        }

        .mp-dashboard-panel {
            overflow: visible;
        }

        .mp-dashboard-sticky {
            padding:
                14px
                14px
                12px;
            border-radius: 14px 14px 0 0;
            box-shadow: 0 7px 14px rgba(15, 23, 42, 0.09);
        }

        .mp-dashboard-body {
            overflow: visible;
            padding:
                14px
                14px
                16px;
        }

        .mp-primary-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .mp-dashboard-panel {
            padding: 14px;
        }

        .mp-dashboard-filter {
            width: 100%;
            justify-content: stretch;
        }

        .mp-field,
        .mp-button {
            flex: 1 1 140px;
        }

        .mp-trend {
            overflow-x: auto;
            grid-template-columns: repeat(12, 42px);
        }
    }
</style>

<div class="mp-dashboard">
    @php
        $dashboardStats = $dashboardStats ?? [];
        $permitStats = $permitStats ?? [];
        $permitDegrees = $permitDegrees ?? [];

        $activeEnd = (float) (
            $permitDegrees['aktif'] ?? 0
        );

        $warningEnd = $activeEnd + (float) (
            $permitDegrees['akan_expired'] ?? 0
        );

        $expiredEnd = $warningEnd + (float) (
            $permitDegrees['expired'] ?? 0
        );

        $selectedPeriod = (
            ($selectedDashboardYear ?? 'all') === 'all'
                ? 'Semua Tahun'
                : (string) $selectedDashboardYear
        );

        if (
            ($selectedDashboardMonth ?? 'all') !== 'all'
        ) {
            $selectedPeriod .= ' · ' .
                (
                    $dashboardMonths[
                        $selectedDashboardMonth
                    ] ?? ''
                );
        }

        $dashboardSheQuery = [
            'year' => $selectedDashboardYear ?? 'all',
            'month' => $selectedDashboardMonth ?? 'all',
        ];

        $dashboardInternalQuery = [
            'year' => $selectedDashboardYear ?? 'all',
        ];
    @endphp

    <h1 class="mp-dashboard-title">
        Mine Permit
    </h1>

    <section class="mp-dashboard-panel">

        <div class="mp-dashboard-sticky">

        <div class="mp-dashboard-head">
            <div>
                <h2 class="mp-dashboard-heading">
                    DASHBOARD MINE PERMIT
                </h2>

                <span class="mp-card-subtitle">
                    Ringkasan Monitoring SHE dan Internal Upload
                </span>
            </div>

            <form
                method="GET"
                action="{{ route('mine-permit.dashboard') }}"
                class="mp-dashboard-filter"
            >
                <div class="mp-field">
                    <label
                        for="dashboardYear"
                        class="mp-label"
                    >
                        Tahun
                    </label>

                    <select
                        id="dashboardYear"
                        name="year"
                        class="mp-select"
                    >
                        <option value="all">
                            Semua Tahun
                        </option>

                        @foreach (
                            ($availableDashboardYears ?? [])
                            as $year
                        )
                            <option
                                value="{{ $year }}"
                                @selected(
                                    (string) (
                                        $selectedDashboardYear
                                        ?? ''
                                    ) === (string) $year
                                )
                            >
                                Tahun {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mp-field">
                    <label
                        for="dashboardMonth"
                        class="mp-label"
                    >
                        Bulan Pengajuan
                    </label>

                    <select
                        id="dashboardMonth"
                        name="month"
                        class="mp-select"
                    >
                        <option value="all">
                            Semua Bulan
                        </option>

                        @foreach (
                            ($dashboardMonths ?? [])
                            as $number => $month
                        )
                            <option
                                value="{{ $number }}"
                                @selected(
                                    (string) (
                                        $selectedDashboardMonth
                                        ?? 'all'
                                    ) === (string) $number
                                )
                            >
                                {{ $month }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button
                    type="submit"
                    class="mp-button apply"
                >
                    TERAPKAN
                </button>

                <a
                    href="{{ route(
                        'mine-permit.dashboard',
                        [
                            'refresh' => now()->timestamp,
                            'year' => 'all',
                            'month' => 'all',
                        ]
                    ) }}"
                    class="mp-button refresh"
                >
                    REFRESH
                </a>
            </form>
        </div>

        <div class="mp-dashboard-sync-top">
            <span>
                Periode aktif:
                {{ $selectedPeriod }}
            </span>

            <span>
                SHE:
                {{ $lastSyncedShe ?? '-' }}
                ·
                Internal:
                {{ $lastSyncedInternal ?? '-' }}
            </span>
        </div>

        <div class="mp-primary-stats">
            <a
                href="{{ route(
                    'mine-permit.monitoring-she',
                    $dashboardSheQuery
                ) }}"
                class="mp-stat total"
                title="Buka seluruh hasil Monitoring SHE pada periode ini"
            >
                <span class="mp-stat-label">
                    Total Pengajuan
                </span>

                <strong class="mp-stat-value">
                    {{ $dashboardStats['total_pengajuan'] ?? 0 }}
                </strong>

                <span class="mp-stat-hint">Lihat data</span>
            </a>

            <a
                href="{{ route(
                    'mine-permit.monitoring-she',
                    array_merge(
                        $dashboardSheQuery,
                        ['status' => 'selesai']
                    )
                ) }}"
                class="mp-stat success"
                title="Buka pengajuan berstatus selesai"
            >
                <span class="mp-stat-label">
                    Status Selesai
                </span>

                <strong class="mp-stat-value">
                    {{ $dashboardStats['selesai'] ?? 0 }}
                </strong>

                <span class="mp-stat-hint">Lihat selesai</span>
            </a>

            <a
                href="{{ route(
                    'mine-permit.monitoring-she',
                    array_merge(
                        $dashboardSheQuery,
                        ['status' => 'proses']
                    )
                ) }}"
                class="mp-stat process"
                title="Buka pengajuan yang masih diproses"
            >
                <span class="mp-stat-label">
                    Masih Proses
                </span>

                <strong class="mp-stat-value">
                    {{ $dashboardStats['proses'] ?? 0 }}
                </strong>

                <span class="mp-stat-hint">Lihat proses</span>
            </a>

            <a
                href="{{ route(
                    'mine-permit.monitoring-she',
                    array_merge(
                        $dashboardSheQuery,
                        ['status' => 'gagal']
                    )
                ) }}"
                class="mp-stat failed"
                title="Buka pengajuan berstatus gagal"
            >
                <span class="mp-stat-label">
                    Status Gagal
                </span>

                <strong class="mp-stat-value">
                    {{ $dashboardStats['gagal'] ?? 0 }}
                </strong>

                <span class="mp-stat-hint">Lihat gagal</span>
            </a>

            <a
                href="{{ route(
                    'mine-permit.monitoring-internal-upload',
                    array_merge(
                        $dashboardInternalQuery,
                        ['status' => 'belum-lengkap']
                    )
                ) }}"
                class="mp-stat document"
                title="Buka karyawan dengan dokumen belum lengkap"
            >
                <span class="mp-stat-label">
                    Dokumen Belum Lengkap
                </span>

                <strong class="mp-stat-value">
                    {{ $dashboardStats[
                        'dokumen_belum_lengkap'
                    ] ?? 0 }}
                </strong>

                <span class="mp-stat-hint">Lihat dokumen</span>
            </a>

            <a
                href="{{ route(
                    'mine-permit.monitoring-internal-upload',
                    $dashboardInternalQuery
                ) }}"
                class="mp-stat warning"
                title="Buka Monitoring Internal Upload untuk melihat permit bermasalah"
            >
                <span class="mp-stat-label">
                    Permit Perlu Perhatian
                </span>

                <strong class="mp-stat-value">
                    {{ $dashboardStats[
                        'permit_perlu_perhatian'
                    ] ?? 0 }}
                </strong>

                <span class="mp-stat-hint">Lihat permit</span>
            </a>
        </div>

        </div>

        <div class="mp-dashboard-body">

        @if (!empty($dashboardErrors ?? []))
            <div class="mp-errors">
                @foreach ($dashboardErrors as $error)
                    <div class="mp-error">
                        {{ $error }}
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mp-dashboard-grid">
            <article class="mp-card">
                <div class="mp-card-head">
                    <div>
                        <h3 class="mp-card-title">
                            Tren Pengajuan Bulanan
                        </h3>

                        <span class="mp-card-subtitle">
                            Tahun {{ $trendYear ?? '-' }}
                        </span>
                    </div>

                    <span class="mp-card-subtitle">
                        Periode dashboard: {{ $selectedPeriod }}
                    </span>
                </div>

                <div class="mp-trend">
                    @foreach (($monthlyTrend ?? []) as $month)
                        @php
                            $successHeight = round(
                                (
                                    ($month['selesai'] ?? 0)
                                    / ($monthlyTrendMax ?? 1)
                                ) * 100,
                                2
                            );

                            $processHeight = round(
                                (
                                    ($month['proses'] ?? 0)
                                    / ($monthlyTrendMax ?? 1)
                                ) * 100,
                                2
                            );

                            $failedHeight = round(
                                (
                                    ($month['gagal'] ?? 0)
                                    / ($monthlyTrendMax ?? 1)
                                ) * 100,
                                2
                            );
                        @endphp

                        <div class="mp-month">
                            <span class="mp-month-value">
                                {{ $month['total'] ?? 0 }}
                            </span>

                            <div
                                class="mp-bar"
                                style="height: 185px;"
                                title="{{ $month['label'] }}: {{ $month['total'] }} pengajuan"
                            >
                                <span
                                    class="mp-bar-segment success"
                                    style="height: {{ $successHeight }}%;"
                                ></span>

                                <span
                                    class="mp-bar-segment process"
                                    style="height: {{ $processHeight }}%;"
                                ></span>

                                <span
                                    class="mp-bar-segment failed"
                                    style="height: {{ $failedHeight }}%;"
                                ></span>
                            </div>

                            <span class="mp-month-label">
                                {{ $month['label'] }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <div class="mp-legend">
                    <span class="mp-legend-item">
                        <i class="mp-dot success"></i>
                        Selesai
                    </span>

                    <span class="mp-legend-item">
                        <i class="mp-dot process"></i>
                        Proses
                    </span>

                    <span class="mp-legend-item">
                        <i class="mp-dot failed"></i>
                        Gagal
                    </span>
                </div>
            </article>

            <article class="mp-card">
                <div class="mp-card-head">
                    <div>
                        <h3 class="mp-card-title">
                            Status Permit Karyawan
                        </h3>

                        <span class="mp-card-subtitle">
                            Data terbaru per NRP
                        </span>
                    </div>
                </div>

                <div class="mp-permit-summary">
                    <div
                        class="mp-donut"
                        data-total="{{ $permitTotal ?? 0 }}"
                        style="
                            background:
                                conic-gradient(
                                    #16a05d 0deg {{ $activeEnd }}deg,
                                    #f2b705 {{ $activeEnd }}deg {{ $warningEnd }}deg,
                                    #ed1c2e {{ $warningEnd }}deg {{ $expiredEnd }}deg,
                                    #cbd5e1 {{ $expiredEnd }}deg 360deg
                                );
                        "
                    ></div>

                    <div class="mp-permit-list">
                        <div class="mp-permit-row">
                            <span class="mp-legend-item">
                                <i class="mp-dot active"></i>
                                Aktif
                            </span>

                            <strong class="mp-permit-number">
                                {{ $permitStats['aktif'] ?? 0 }}
                            </strong>
                        </div>

                        <div class="mp-permit-row">
                            <span class="mp-legend-item">
                                <i class="mp-dot warning"></i>
                                Akan Expired
                            </span>

                            <strong class="mp-permit-number">
                                {{ $permitStats[
                                    'akan_expired'
                                ] ?? 0 }}
                            </strong>
                        </div>

                        <div class="mp-permit-row">
                            <span class="mp-legend-item">
                                <i class="mp-dot expired"></i>
                                Expired
                            </span>

                            <strong class="mp-permit-number">
                                {{ $permitStats['expired'] ?? 0 }}
                            </strong>
                        </div>

                        <div class="mp-permit-row">
                            <span class="mp-legend-item">
                                <i class="mp-dot unknown"></i>
                                Tidak Diketahui
                            </span>

                            <strong class="mp-permit-number">
                                {{ $permitStats[
                                    'tidak_diketahui'
                                ] ?? 0 }}
                            </strong>
                        </div>
                    </div>
                </div>
            </article>
        </div>

        <div class="mp-table-grid">
            <article class="mp-card">
                <div class="mp-card-head">
                    <div>
                        <h3 class="mp-card-title">
                            10 Pengajuan Terbaru
                        </h3>

                        <span class="mp-card-subtitle">
                            Monitoring SHE
                        </span>
                    </div>

                    <a
                        href="{{ route(
                            'mine-permit.monitoring-she'
                        ) }}"
                        class="mp-button she"
                    >
                        LIHAT SEMUA
                    </a>
                </div>

                <div class="mp-table-wrapper">
                    <table class="mp-table">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Nama</th>
                                <th>Pengajuan</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse (
                                ($latestSubmissions ?? [])
                                as $row
                            )
                                @php
                                    $statusClass = match (
                                        $row['status'] ?? 'PROSES'
                                    ) {
                                        'SELESAI' => 'success',
                                        'GAGAL' => 'failed',
                                        default => 'process',
                                    };
                                @endphp

                                <tr>
                                    <td>
                                        {{ $row['timestamp'] ?: '-' }}
                                    </td>

                                    <td
                                        class="mp-text-cut"
                                        title="{{ $row['nama'] }}"
                                    >
                                        {{ $row['nama'] ?: '-' }}
                                    </td>

                                    <td>
                                        {{ $row['pengajuan'] ?: '-' }}
                                    </td>

                                    <td>
                                        <span
                                            class="mp-badge {{ $statusClass }}"
                                            title="{{ $row['status_raw'] }}"
                                        >
                                            {{ $row['status_raw']
                                                ?: 'PROSES' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="4"
                                        class="mp-empty"
                                    >
                                        Data pengajuan tidak ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="mp-card">
                <div class="mp-card-head">
                    <div>
                        <h3 class="mp-card-title">
                            Permit Perlu Perhatian
                        </h3>

                        <span class="mp-card-subtitle">
                            Expired dan akan expired ≤ 30 hari
                        </span>
                    </div>

                    <a
                        href="{{ route(
                            'mine-permit.monitoring-internal-upload'
                        ) }}"
                        class="mp-button internal"
                    >
                        LIHAT INTERNAL
                    </a>
                </div>

                <div class="mp-table-wrapper">
                    <table class="mp-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>NRP</th>
                                <th>Berlaku</th>
                                <th>Kondisi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse (
                                ($attentionPermits ?? [])
                                as $permit
                            )
                                @php
                                    $isExpired =
                                        ($permit['permit_status'] ?? '')
                                        === 'EXPIRED';

                                    $permitClass = $isExpired
                                        ? 'failed'
                                        : 'warning';

                                    $condition = match (true) {
                                        $permit['days_remaining']
                                            === null =>
                                            'Tanggal tidak diketahui',

                                        $permit['days_remaining'] < 0 =>
                                            'Lewat ' .
                                            abs(
                                                $permit['days_remaining']
                                            ) .
                                            ' hari',

                                        $permit['days_remaining'] === 0 =>
                                            'Berakhir hari ini',

                                        default =>
                                            $permit['days_remaining'] .
                                            ' hari lagi',
                                    };
                                @endphp

                                <tr>
                                    <td
                                        class="mp-text-cut"
                                        title="{{ $permit['nama'] }}"
                                    >
                                        {{ $permit['nama'] ?: '-' }}
                                    </td>

                                    <td>
                                        {{ $permit['nrp'] ?: '-' }}
                                    </td>

                                    <td>
                                        {{ $permit[
                                            'tanggal_berlaku'
                                        ] ?: '-' }}
                                    </td>

                                    <td>
                                        <span
                                            class="mp-badge {{ $permitClass }}"
                                        >
                                            {{ $condition }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="4"
                                        class="mp-empty"
                                    >
                                        Tidak ada permit yang perlu perhatian.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </div>

        <div class="mp-footer-actions">
            <a
                href="{{ route(
                    'mine-permit.monitoring-she'
                ) }}"
                class="mp-button she"
            >
                BUKA MONITORING SHE
            </a>

            <a
                href="{{ route(
                    'mine-permit.monitoring-internal-upload'
                ) }}"
                class="mp-button internal"
            >
                BUKA INTERNAL UPLOAD
            </a>
        </div>

        </div>

    </section>
</div>