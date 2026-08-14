@php
    $summary = is_array($dashboardSummary ?? null)
        ? $dashboardSummary
        : [];

    $totals = is_array($summary['totals'] ?? null)
        ? $summary['totals']
        : [];

    $statusDistribution = collect(
        $summary['status_distribution'] ?? []
    );

    $residenceDistribution = collect(
        $summary['residence_distribution'] ?? []
    );

    $positionDistribution = collect(
        $summary['position_distribution'] ?? []
    );

    $syncMeta = is_array($summary['meta'] ?? null)
        ? $summary['meta']
        : [];

    $statusColors = [
        'AKTIF' => '#16a36a',
        'NEW HIRE' => '#2563eb',
        'RESIGN' => '#f59e0b',
        'PHK' => '#dc2626',
        'NON AKTIF' => '#7c3aed',
        'LAINNYA' => '#64748b',
        'BELUM DATA' => '#cbd5e1',
    ];

    $residenceColors = [
        'MESS' => '#0ea5e9',
        'NON MESS' => '#f97316',
        'BELUM DATA' => '#cbd5e1',
    ];

    $buildDonutGradient = function (
        $items,
        array $colors
    ): string {
        $cursor = 0.0;
        $segments = [];

        foreach ($items as $item) {
            $percentage = max(
                0,
                (float) ($item['percentage'] ?? 0)
            );

            if ($percentage <= 0) {
                continue;
            }

            $label = (string) ($item['label'] ?? '');
            $color = $colors[$label] ?? '#64748b';
            $end = min(100, $cursor + $percentage);

            $segments[] = sprintf(
                '%s %.2f%% %.2f%%',
                $color,
                $cursor,
                $end
            );

            $cursor = $end;
        }

        if ($cursor < 100) {
            $segments[] = sprintf(
                '#e5e7eb %.2f%% 100%%',
                $cursor
            );
        }

        return $segments !== []
            ? implode(', ', $segments)
            : '#e5e7eb 0% 100%';
    };

    $statusGradient = $buildDonutGradient(
        $statusDistribution,
        $statusColors
    );

    $residenceGradient = $buildDonutGradient(
        $residenceDistribution,
        $residenceColors
    );

    $maximumPositionCount = max(
        1,
        (int) $positionDistribution->max('count')
    );

    $syncStatus = strtolower(
        trim((string) ($syncMeta['status'] ?? 'cached'))
    );

    $syncStatusLabel = match ($syncStatus) {
        'synced' => 'Baru Disinkronkan',
        'stale' => 'Menggunakan Backup',
        'error' => 'Sinkronisasi Bermasalah',
        default => 'Data Cache Aktif',
    };

    $syncStatusClass = match ($syncStatus) {
        'synced', 'cached' => 'is-success',
        'stale' => 'is-warning',
        'error' => 'is-danger',
        default => 'is-neutral',
    };

    $syncedAtLabel = '-';

    try {
        $syncedAt = trim(
            (string) ($syncMeta['synced_at'] ?? '')
        );

        if ($syncedAt !== '') {
            $syncedAtLabel = \Illuminate\Support\Carbon::parse(
                $syncedAt
            )
                ->timezone(config('app.timezone'))
                ->translatedFormat('d M Y, H:i') . ' WIB';
        }
    } catch (\Throwable) {
        $syncedAtLabel = (string) (
            $syncMeta['synced_at'] ?? '-'
        );
    }

    $atrSummary = is_array(
        $atrDashboardSummary ?? null
    )
        ? $atrDashboardSummary
        : [];

    $atrAvailable = (bool) (
        $atrSummary['available'] ?? false
    );

    $atrStats = is_array(
        $atrSummary['stats'] ?? null
    )
        ? $atrSummary['stats']
        : [];

    $atrProgress = is_array(
        $atrSummary['progress'] ?? null
    )
        ? $atrSummary['progress']
        : [];
@endphp

<style>
    .employee-dashboard {
        --ed-navy: #17253d;
        --ed-navy-soft: #223552;
        --ed-blue: #1677f2;
        --ed-green: #16a36a;
        --ed-orange: #d65d22;
        --ed-text: #14213a;
        --ed-muted: #64748b;
        --ed-line: #dbe4ef;
        --ed-panel: #ffffff;
        --ed-background: #f4f7fb;
        display: grid;
        gap: 14px;
        min-width: 0;
        color: var(--ed-text);
    }

    .employee-dashboard * {
        box-sizing: border-box;
    }

    .employee-dashboard-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        padding: 0 2px;
    }

    .employee-dashboard-heading h1 {
        margin: 0;
        color: #071b39;
        font-size: clamp(22px, 2vw, 31px);
        font-weight: 900;
        line-height: 1.08;
        letter-spacing: -0.4px;
    }

    .employee-dashboard-heading p {
        margin: 6px 0 0;
        color: var(--ed-muted);
        font-size: 12px;
        font-weight: 600;
    }

    .employee-dashboard-heading-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 8px;
    }

    .employee-dashboard-button {
        display: inline-flex;
        min-height: 38px;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 8px 15px;
        border: 1px solid transparent;
        border-radius: 9px;
        background: var(--ed-blue);
        box-shadow: 0 7px 18px rgba(22, 119, 242, 0.18);
        color: #ffffff;
        font-size: 11px;
        font-weight: 900;
        text-decoration: none;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .employee-dashboard-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 24px rgba(22, 119, 242, 0.24);
    }

    .employee-dashboard-source-button {
        border-color: #d6e0ec;
        background: #ffffff;
        box-shadow: none;
        color: #21324d;
    }

    .employee-dashboard-source-button:hover {
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
    }

    .employee-dashboard-sync {
        display: flex;
        min-width: 0;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 14px;
        border: 1px solid #d7e2ef;
        border-radius: 11px;
        background: rgba(255, 255, 255, 0.88);
        box-shadow: 0 5px 15px rgba(15, 23, 42, 0.035);
    }

    .employee-dashboard-sync-main {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 10px;
    }

    .employee-dashboard-sync-dot {
        width: 10px;
        height: 10px;
        flex: 0 0 10px;
        border-radius: 50%;
        background: #94a3b8;
        box-shadow: 0 0 0 5px rgba(148, 163, 184, 0.13);
    }

    .employee-dashboard-sync.is-success {
        border-color: #b8ecd4;
        background: #f1fff8;
    }

    .employee-dashboard-sync.is-success
        .employee-dashboard-sync-dot {
        background: #16a36a;
        box-shadow: 0 0 0 5px rgba(22, 163, 106, 0.13);
    }

    .employee-dashboard-sync.is-warning {
        border-color: #f5dca1;
        background: #fffaf0;
    }

    .employee-dashboard-sync.is-warning
        .employee-dashboard-sync-dot {
        background: #f59e0b;
        box-shadow: 0 0 0 5px rgba(245, 158, 11, 0.13);
    }

    .employee-dashboard-sync.is-danger {
        border-color: #fecaca;
        background: #fff5f5;
    }

    .employee-dashboard-sync.is-danger
        .employee-dashboard-sync-dot {
        background: #dc2626;
        box-shadow: 0 0 0 5px rgba(220, 38, 38, 0.13);
    }

    .employee-dashboard-sync strong,
    .employee-dashboard-sync small {
        display: block;
    }

    .employee-dashboard-sync strong {
        color: #18304f;
        font-size: 11px;
        font-weight: 900;
    }

    .employee-dashboard-sync small {
        margin-top: 2px;
        color: #718096;
        font-size: 9px;
        font-weight: 600;
    }

    .employee-dashboard-sync-source {
        max-width: 47%;
        overflow: hidden;
        color: #64748b;
        font-size: 9px;
        font-weight: 700;
        text-align: right;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .employee-dashboard-cards {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }

    .employee-dashboard-card {
        position: relative;
        display: flex;
        min-width: 0;
        min-height: 98px;
        align-items: center;
        gap: 12px;
        overflow: hidden;
        padding: 16px;
        border: 1px solid #dbe4ef;
        border-radius: 13px;
        background: #ffffff;
        box-shadow: 0 7px 18px rgba(15, 23, 42, 0.045);
    }

    .employee-dashboard-card::after {
        position: absolute;
        right: -28px;
        bottom: -35px;
        width: 95px;
        height: 95px;
        border-radius: 50%;
        background: var(--card-glow, rgba(37, 99, 235, 0.08));
        content: '';
    }

    .employee-dashboard-card-icon {
        position: relative;
        z-index: 1;
        display: grid;
        width: 47px;
        height: 47px;
        flex: 0 0 47px;
        place-items: center;
        border-radius: 12px;
        background: var(--card-icon-background, #17253d);
        box-shadow: 0 8px 20px var(--card-shadow, rgba(23, 37, 61, 0.18));
        color: #ffffff;
        font-size: 22px;
    }

    .employee-dashboard-card-copy {
        position: relative;
        z-index: 1;
        min-width: 0;
    }

    .employee-dashboard-card-copy span,
    .employee-dashboard-card-copy small {
        display: block;
    }

    .employee-dashboard-card-copy span {
        color: #52647d;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: 0.25px;
        text-transform: uppercase;
    }

    .employee-dashboard-card-copy strong {
        display: block;
        margin-top: 3px;
        color: #0e1f38;
        font-size: clamp(25px, 2.1vw, 34px);
        font-weight: 950;
        line-height: 1;
    }

    .employee-dashboard-card-copy small {
        margin-top: 5px;
        overflow: hidden;
        color: #8190a4;
        font-size: 9px;
        font-weight: 650;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .employee-dashboard-card.is-active {
        --card-icon-background: #16a36a;
        --card-shadow: rgba(22, 163, 106, 0.2);
        --card-glow: rgba(22, 163, 106, 0.09);
    }

    .employee-dashboard-card.is-mess {
        --card-icon-background: #0ea5e9;
        --card-shadow: rgba(14, 165, 233, 0.2);
        --card-glow: rgba(14, 165, 233, 0.09);
    }

    .employee-dashboard-card.is-non-mess {
        --card-icon-background: #f97316;
        --card-shadow: rgba(249, 115, 22, 0.2);
        --card-glow: rgba(249, 115, 22, 0.09);
    }

    .employee-dashboard-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .employee-dashboard-panel {
        min-width: 0;
        border: 1px solid #dbe4ef;
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.045);
    }

    .employee-dashboard-panel-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 15px 17px 12px;
        border-bottom: 1px solid #e4ebf3;
    }

    .employee-dashboard-panel-header h2 {
        margin: 0;
        color: #10233e;
        font-size: 14px;
        font-weight: 950;
    }

    .employee-dashboard-panel-header p {
        margin: 4px 0 0;
        color: #718096;
        font-size: 9px;
        font-weight: 650;
    }

    .employee-dashboard-panel-badge {
        display: inline-flex;
        min-height: 25px;
        align-items: center;
        justify-content: center;
        padding: 5px 9px;
        border: 1px solid #d8e4f1;
        border-radius: 999px;
        background: #f5f8fc;
        color: #52647d;
        font-size: 8px;
        font-weight: 900;
        white-space: nowrap;
    }

    .employee-dashboard-donut-layout {
        display: grid;
        grid-template-columns: minmax(145px, 0.8fr) minmax(0, 1.2fr);
        align-items: center;
        gap: 18px;
        padding: 18px;
    }

    .employee-dashboard-donut {
        position: relative;
        width: min(100%, 175px);
        aspect-ratio: 1;
        justify-self: center;
        border-radius: 50%;
        background: conic-gradient(var(--donut-gradient));
        box-shadow:
            inset 0 0 0 1px rgba(255, 255, 255, 0.4),
            0 12px 28px rgba(15, 23, 42, 0.1);
    }

    .employee-dashboard-donut::before {
        position: absolute;
        inset: 22%;
        display: block;
        border: 1px solid #e3eaf3;
        border-radius: 50%;
        background: #ffffff;
        box-shadow: inset 0 3px 10px rgba(15, 23, 42, 0.04);
        content: '';
    }

    .employee-dashboard-donut-center {
        position: absolute;
        inset: 0;
        z-index: 1;
        display: grid;
        place-content: center;
        text-align: center;
    }

    .employee-dashboard-donut-center strong {
        color: #10233e;
        font-size: clamp(24px, 2vw, 31px);
        font-weight: 950;
        line-height: 1;
    }

    .employee-dashboard-donut-center span {
        margin-top: 5px;
        color: #718096;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: 0.45px;
        text-transform: uppercase;
    }

    .employee-dashboard-legend {
        display: grid;
        gap: 8px;
        min-width: 0;
    }

    .employee-dashboard-legend-item {
        display: grid;
        grid-template-columns: 10px minmax(0, 1fr) auto;
        align-items: center;
        gap: 8px;
        min-width: 0;
        padding-bottom: 7px;
        border-bottom: 1px dashed #e2e8f0;
    }

    .employee-dashboard-legend-item:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .employee-dashboard-legend-color {
        width: 9px;
        height: 9px;
        border-radius: 3px;
        background: var(--legend-color, #64748b);
    }

    .employee-dashboard-legend-label {
        min-width: 0;
        overflow: hidden;
        color: #34465f;
        font-size: 9px;
        font-weight: 850;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .employee-dashboard-legend-value {
        color: #10233e;
        font-size: 10px;
        font-weight: 950;
        white-space: nowrap;
    }

    .employee-dashboard-legend-value small {
        color: #8b99aa;
        font-size: 8px;
        font-weight: 750;
    }

    .employee-dashboard-position-panel {
        grid-column: 1 / -1;
    }

    .employee-dashboard-position-list {
        display: grid;
        gap: 9px;
        padding: 16px 18px 19px;
    }

    .employee-dashboard-position-row {
        display: grid;
        grid-template-columns: minmax(160px, 0.65fr) minmax(190px, 1.35fr) 64px;
        align-items: center;
        gap: 12px;
    }

    .employee-dashboard-position-name {
        min-width: 0;
        overflow: hidden;
        color: #33445c;
        font-size: 9px;
        font-weight: 850;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .employee-dashboard-position-track {
        height: 11px;
        overflow: hidden;
        border-radius: 999px;
        background: #edf2f7;
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.06);
    }

    .employee-dashboard-position-bar {
        width: max(1.5%, var(--position-width));
        height: 100%;
        border-radius: inherit;
        background:
            linear-gradient(
                90deg,
                #17253d 0%,
                #2563eb 68%,
                #22a6f2 100%
            );
        box-shadow: 0 3px 10px rgba(37, 99, 235, 0.18);
    }

    .employee-dashboard-position-value {
        color: #10233e;
        font-size: 10px;
        font-weight: 950;
        text-align: right;
        white-space: nowrap;
    }

    .employee-dashboard-position-value small {
        color: #8190a4;
        font-size: 8px;
        font-weight: 750;
    }

    .employee-dashboard-empty {
        padding: 30px 18px;
        color: #718096;
        font-size: 11px;
        font-weight: 700;
        text-align: center;
    }


    /* Dashboard -> Ringkasan & Pencarian */
    .employee-dashboard-clickable {
        color: inherit;
        text-decoration: none;
        cursor: pointer;
        transition:
            transform .16s ease,
            border-color .16s ease,
            box-shadow .16s ease;
    }

    .employee-dashboard-clickable:hover {
        transform: translateY(-1px);
        border-color: #b8cbe2;
        box-shadow: 0 9px 22px rgba(15, 23, 42, .08);
    }

    .employee-dashboard-clickable:focus-visible {
        outline: 3px solid rgba(22,119,242,.18);
        outline-offset: 2px;
    }

    .employee-dashboard-legend-link {
        color: inherit;
        text-decoration: none;
        border-radius: 6px;
        transition: background .15s ease;
    }

    .employee-dashboard-legend-link:hover {
        background: #f7faff;
    }

    .employee-dashboard-position-link {
        color: inherit;
        text-decoration: none;
        border-radius: 7px;
        transition:
            background .15s ease,
            transform .15s ease;
    }

    .employee-dashboard-position-link:hover {
        background: #f7faff;
        transform: translateX(2px);
    }

    .employee-dashboard-position-row.is-static {
        opacity: .82;
    }

    /* Widget ATR terbaru */
    .employee-dashboard-atr-panel {
        grid-column: 1 / -1;
    }

    .employee-dashboard-atr-body {
        display: grid;
        gap: 12px;
        padding: 15px 18px 18px;
    }

    .employee-dashboard-atr-kpis {
        display: grid;
        grid-template-columns: repeat(4, minmax(0,1fr));
        gap: 9px;
    }

    .employee-dashboard-atr-kpi {
        display: grid;
        gap: 3px;
        padding: 10px 11px;
        border: 1px solid #e0e8f2;
        border-radius: 9px;
        background: #fbfdff;
        color: inherit;
        text-decoration: none;
    }

    .employee-dashboard-atr-kpi span {
        color: #718096;
        font-size: 8px;
        font-weight: 850;
        text-transform: uppercase;
    }

    .employee-dashboard-atr-kpi strong {
        color: #10233e;
        font-size: 19px;
        font-weight: 950;
    }

    .employee-dashboard-atr-kpi.is-safe {
        border-top: 3px solid #16a36a;
    }

    .employee-dashboard-atr-kpi.is-monitoring {
        border-top: 3px solid #f59e0b;
    }

    .employee-dashboard-atr-kpi.is-call {
        border-top: 3px solid #e52e48;
    }

    .employee-dashboard-atr-kpi.is-no-data {
        border-top: 3px solid #94a3b8;
    }

    .employee-dashboard-atr-progress {
        display: grid;
        grid-template-columns: auto minmax(180px,1fr) auto;
        align-items: center;
        gap: 11px;
        padding: 10px 12px;
        border: 1px solid #e1e8f1;
        border-radius: 9px;
        background: #fff;
    }

    .employee-dashboard-atr-progress-copy strong {
        display: block;
        color: #17304e;
        font-size: 9px;
    }

    .employee-dashboard-atr-progress-copy span {
        display: block;
        margin-top: 2px;
        color: #7c8b9f;
        font-size: 7px;
    }

    .employee-dashboard-atr-track {
        height: 9px;
        overflow: hidden;
        border-radius: 999px;
        background: #e8edf4;
    }

    .employee-dashboard-atr-bar {
        width: var(--atr-progress);
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(
            90deg,
            #16a36a,
            #22a6f2
        );
    }

    .employee-dashboard-atr-progress-value {
        color: #10233e;
        font-size: 11px;
        font-weight: 950;
        white-space: nowrap;
    }

    .employee-dashboard-atr-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 7px;
    }

    .employee-dashboard-atr-empty {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 18px;
    }

    @media (max-width: 1050px) {
        .employee-dashboard-cards {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .employee-dashboard-donut-layout {
            grid-template-columns: 1fr;
        }

        .employee-dashboard-atr-kpis {
            grid-template-columns: repeat(2, minmax(0,1fr));
        }

        .employee-dashboard-legend {
            width: min(100%, 380px);
            justify-self: center;
        }
    }

    @media (max-width: 760px) {
        .employee-dashboard-heading {
            flex-direction: column;
        }

        .employee-dashboard-heading-actions {
            width: 100%;
            justify-content: flex-start;
        }

        .employee-dashboard-sync {
            align-items: flex-start;
            flex-direction: column;
        }

        .employee-dashboard-sync-source {
            max-width: 100%;
            text-align: left;
        }

        .employee-dashboard-grid {
            grid-template-columns: 1fr;
        }

        .employee-dashboard-position-panel,
        .employee-dashboard-atr-panel {
            grid-column: auto;
        }

        .employee-dashboard-atr-progress {
            grid-template-columns: 1fr;
        }

        .employee-dashboard-atr-actions {
            justify-content: flex-start;
        }

        .employee-dashboard-position-row {
            grid-template-columns: minmax(110px, 0.75fr) minmax(90px, 1.25fr) 52px;
            gap: 8px;
        }
    }

    @media (max-width: 520px) {
        .employee-dashboard-cards,
        .employee-dashboard-atr-kpis {
            grid-template-columns: 1fr;
        }

        .employee-dashboard-card {
            min-height: 88px;
        }

        .employee-dashboard-button {
            flex: 1 1 150px;
        }

        .employee-dashboard-position-row {
            grid-template-columns: minmax(0, 1fr) 48px;
        }

        .employee-dashboard-position-track {
            grid-column: 1 / -1;
            grid-row: 2;
        }
    }
</style>

<section class="employee-dashboard" aria-labelledby="employeeDashboardTitle">
    <header class="employee-dashboard-heading">
        <div>
            <h1 id="employeeDashboardTitle">
                Dashboard Database Karyawan
            </h1>

            <p>
                Ringkasan status karyawan, tempat tinggal, dan distribusi jabatan berdasarkan MASTER_DATABASE.
            </p>
        </div>

        <div class="employee-dashboard-heading-actions">
            <a
                href="{{ route('database.files') }}"
                class="employee-dashboard-button employee-dashboard-source-button"
            >
                <span aria-hidden="true">▣</span>
                PUSAT FILE
            </a>

            @if (!empty($sourceUrl))
                <a
                    href="{{ $sourceUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="employee-dashboard-button employee-dashboard-source-button"
                >
                    <span aria-hidden="true">▤</span>
                    SOURCE DATA
                </a>
            @endif

            <a
                href="{{ route('database.employees') }}"
                class="employee-dashboard-button"
            >
                <span aria-hidden="true">👥</span>
                BUKA DATABASE KARYAWAN
            </a>
        </div>
    </header>

    <div class="employee-dashboard-sync {{ $syncStatusClass }}">
        <div class="employee-dashboard-sync-main">
            <span
                class="employee-dashboard-sync-dot"
                aria-hidden="true"
            ></span>

            <div>
                <strong>{{ $syncStatusLabel }}</strong>
                <small>
                    Sinkronisasi terakhir: {{ $syncedAtLabel }}
                </small>
            </div>
        </div>

        <div class="employee-dashboard-sync-source">
            {{ $googleConnected ?? false
                ? 'Google Sheets terhubung'
                : 'Google Sheets belum terhubung' }}
            ·
            {{ $syncMeta['range'] ?? 'MASTER_DATABASE' }}
        </div>
    </div>

    <div class="employee-dashboard-cards">
        <a
            href="{{ route('database.employees') }}"
            class="employee-dashboard-card employee-dashboard-clickable"
            title="Buka seluruh Database Karyawan"
        >
            <div class="employee-dashboard-card-icon" aria-hidden="true">👥</div>
            <div class="employee-dashboard-card-copy">
                <span>Total Karyawan</span>
                <strong>{{ number_format((int) ($totals['employees'] ?? 0)) }}</strong>
                <small>Klik untuk lihat seluruh data</small>
            </div>
        </a>

        <a
            href="{{ route('database.employees', ['status' => 'AKTIF']) }}"
            class="employee-dashboard-card is-active employee-dashboard-clickable"
            title="Buka karyawan berstatus AKTIF"
        >
            <div class="employee-dashboard-card-icon" aria-hidden="true">✓</div>
            <div class="employee-dashboard-card-copy">
                <span>Karyawan Aktif</span>
                <strong>{{ number_format((int) ($totals['active'] ?? 0)) }}</strong>
                <small>Klik untuk filter status aktif</small>
            </div>
        </a>

        <a
            href="{{ route('database.employees', ['residence' => 'mess']) }}"
            class="employee-dashboard-card is-mess employee-dashboard-clickable"
            title="Buka karyawan yang tinggal di Mess"
        >
            <div class="employee-dashboard-card-icon" aria-hidden="true">🏠</div>
            <div class="employee-dashboard-card-copy">
                <span>Tinggal di Mess</span>
                <strong>{{ number_format((int) ($totals['mess'] ?? 0)) }}</strong>
                <small>Klik untuk filter Mess</small>
            </div>
        </a>

        <a
            href="{{ route('database.employees', ['residence' => 'non-mess']) }}"
            class="employee-dashboard-card is-non-mess employee-dashboard-clickable"
            title="Buka karyawan yang tinggal Non Mess"
        >
            <div class="employee-dashboard-card-icon" aria-hidden="true">🚶</div>
            <div class="employee-dashboard-card-copy">
                <span>Tinggal Non Mess</span>
                <strong>{{ number_format((int) ($totals['non_mess'] ?? 0)) }}</strong>
                <small>
                    Klik untuk filter · Belum data:
                    {{ number_format((int) ($totals['residence_unknown'] ?? 0)) }}
                </small>
            </div>
        </a>
    </div>

    <div class="employee-dashboard-grid">
        <article class="employee-dashboard-panel">
            <header class="employee-dashboard-panel-header">
                <div>
                    <h2>Status Karyawan</h2>
                    <p>Pengelompokan kondisi kepegawaian terbaru.</p>
                </div>

                <span class="employee-dashboard-panel-badge">
                    {{ number_format((int) ($totals['employees'] ?? 0)) }} DATA
                </span>
            </header>

            @if ($statusDistribution->isNotEmpty())
                <div class="employee-dashboard-donut-layout">
                    <div
                        class="employee-dashboard-donut"
                        style="--donut-gradient: {{ $statusGradient }};"
                        aria-label="Diagram status karyawan"
                    >
                        <div class="employee-dashboard-donut-center">
                            <strong>
                                {{ number_format((int) ($totals['employees'] ?? 0)) }}
                            </strong>
                            <span>Karyawan</span>
                        </div>
                    </div>

                    <div class="employee-dashboard-legend">
                        @foreach ($statusDistribution as $item)
                            @php
                                $label = (string) ($item['label'] ?? '-');
                                $color = $statusColors[$label] ?? '#64748b';
                            @endphp

                            <a
                                href="{{ route('database.employees', ['status' => $label]) }}"
                                class="employee-dashboard-legend-item employee-dashboard-legend-link"
                                title="Filter Status Karyawan: {{ $label }}"
                            >
                                <span
                                    class="employee-dashboard-legend-color"
                                    style="--legend-color: {{ $color }};"
                                    aria-hidden="true"
                                ></span>

                                <span class="employee-dashboard-legend-label">
                                    {{ $label }}
                                </span>

                                <strong class="employee-dashboard-legend-value">
                                    {{ number_format((int) ($item['count'] ?? 0)) }}
                                    <small>
                                        {{ number_format((float) ($item['percentage'] ?? 0), 1) }}%
                                    </small>
                                </strong>
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="employee-dashboard-empty">
                    Data status karyawan belum tersedia.
                </div>
            @endif
        </article>

        <article class="employee-dashboard-panel">
            <header class="employee-dashboard-panel-header">
                <div>
                    <h2>Tempat Tinggal</h2>
                    <p>Perbandingan karyawan mess dan non mess.</p>
                </div>

                <span class="employee-dashboard-panel-badge">
                    MESS / NON MESS
                </span>
            </header>

            @if ($residenceDistribution->isNotEmpty())
                <div class="employee-dashboard-donut-layout">
                    <div
                        class="employee-dashboard-donut"
                        style="--donut-gradient: {{ $residenceGradient }};"
                        aria-label="Diagram tempat tinggal karyawan"
                    >
                        <div class="employee-dashboard-donut-center">
                            <strong>
                                {{ number_format((int) ($totals['employees'] ?? 0)) }}
                            </strong>
                            <span>Total Data</span>
                        </div>
                    </div>

                    <div class="employee-dashboard-legend">
                        @foreach ($residenceDistribution as $item)
                            @php
                                $label = (string) ($item['label'] ?? '-');
                                $color = $residenceColors[$label] ?? '#64748b';
                            @endphp

                            @php
                                $residenceQuery = match ($label) {
                                    'MESS' => 'mess',
                                    'NON MESS' => 'non-mess',
                                    default => 'unknown',
                                };
                            @endphp

                            <a
                                href="{{ route('database.employees', ['residence' => $residenceQuery]) }}"
                                class="employee-dashboard-legend-item employee-dashboard-legend-link"
                                title="Filter Tempat Tinggal: {{ $label }}"
                            >
                                <span
                                    class="employee-dashboard-legend-color"
                                    style="--legend-color: {{ $color }};"
                                    aria-hidden="true"
                                ></span>

                                <span class="employee-dashboard-legend-label">
                                    {{ $label }}
                                </span>

                                <strong class="employee-dashboard-legend-value">
                                    {{ number_format((int) ($item['count'] ?? 0)) }}
                                    <small>
                                        {{ number_format((float) ($item['percentage'] ?? 0), 1) }}%
                                    </small>
                                </strong>
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="employee-dashboard-empty">
                    Data tempat tinggal belum tersedia.
                </div>
            @endif
        </article>

        <article class="employee-dashboard-panel employee-dashboard-position-panel">
            <header class="employee-dashboard-panel-header">
                <div>
                    <h2>Distribusi Jabatan</h2>
                    <p>
                        Menampilkan 12 jabatan dengan jumlah karyawan terbanyak; sisanya digabung sebagai Jabatan Lainnya.
                    </p>
                </div>

                <span class="employee-dashboard-panel-badge">
                    {{ number_format((int) ($summary['position_distinct_count'] ?? 0)) }} JABATAN
                </span>
            </header>

            @if ($positionDistribution->isNotEmpty())
                <div class="employee-dashboard-position-list">
                    @foreach ($positionDistribution as $item)
                        @php
                            $count = max(
                                0,
                                (int) ($item['count'] ?? 0)
                            );

                            $width = min(
                                100,
                                ($count / $maximumPositionCount) * 100
                            );
                        @endphp

                        @php
                            $positionLabel = (string) ($item['label'] ?? '-');
                            $positionClickable = ! in_array(
                                $positionLabel,
                                ['JABATAN LAINNYA', 'BELUM DATA JABATAN'],
                                true
                            );
                        @endphp

                        @if($positionClickable)
                            <a
                                href="{{ route('database.employees', ['search' => $positionLabel]) }}"
                                class="employee-dashboard-position-row employee-dashboard-position-link"
                                title="Filter jabatan: {{ $positionLabel }}"
                            >
                        @else
                            <div class="employee-dashboard-position-row is-static">
                        @endif

                            <div
                                class="employee-dashboard-position-name"
                                title="{{ $positionLabel }}"
                            >
                                {{ $positionLabel }}
                            </div>

                            <div
                                class="employee-dashboard-position-track"
                                role="img"
                                aria-label="{{ $positionLabel }}: {{ $count }} karyawan"
                            >
                                <div
                                    class="employee-dashboard-position-bar"
                                    style="--position-width: {{ number_format($width, 2, '.', '') }}%;"
                                ></div>
                            </div>

                            <div class="employee-dashboard-position-value">
                                {{ number_format($count) }}
                                <small>
                                    {{ number_format((float) ($item['percentage'] ?? 0), 1) }}%
                                </small>
                            </div>

                        @if($positionClickable)
                            </a>
                        @else
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="employee-dashboard-empty">
                    Data jabatan belum tersedia.
                </div>
            @endif
        </article>

        <article class="employee-dashboard-panel employee-dashboard-atr-panel">
            <header class="employee-dashboard-panel-header">
                <div>
                    <h2>Monitoring ATR Terbaru</h2>
                    <p>
                        Ringkasan monitoring ATR Produksi dari snapshot aktif terbaru.
                    </p>
                </div>

                <span class="employee-dashboard-panel-badge">
                    {{ $atrAvailable
                        ? mb_strtoupper((string) ($atrSummary['period_label'] ?? '-'))
                        : 'BELUM ADA DATA' }}
                </span>
            </header>

            @if($atrAvailable)
                <div class="employee-dashboard-atr-body">
                    <div class="employee-dashboard-atr-kpis">
                        <a
                            href="{{ route('database.atr.summary', ['period' => $atrSummary['period']]) }}"
                            class="employee-dashboard-atr-kpi is-safe employee-dashboard-clickable"
                        >
                            <span>Aman</span>
                            <strong>{{ number_format((int) ($atrStats['aman'] ?? 0)) }}</strong>
                        </a>

                        <a
                            href="{{ route('database.atr.summary', ['period' => $atrSummary['period']]) }}"
                            class="employee-dashboard-atr-kpi is-monitoring employee-dashboard-clickable"
                        >
                            <span>Monitoring</span>
                            <strong>{{ number_format((int) ($atrStats['monitoring'] ?? 0)) }}</strong>
                        </a>

                        <a
                            href="{{ route('database.atr.calls', ['period' => $atrSummary['period']]) }}"
                            class="employee-dashboard-atr-kpi is-call employee-dashboard-clickable"
                        >
                            <span>Pemanggilan</span>
                            <strong>{{ number_format((int) ($atrStats['pemanggilan'] ?? 0)) }}</strong>
                        </a>

                        <a
                            href="{{ route('database.atr.summary', ['period' => $atrSummary['period']]) }}"
                            class="employee-dashboard-atr-kpi is-no-data employee-dashboard-clickable"
                        >
                            <span>No Data</span>
                            <strong>{{ number_format((int) ($atrStats['no_data'] ?? 0)) }}</strong>
                        </a>
                    </div>

                    <div class="employee-dashboard-atr-progress">
                        <div class="employee-dashboard-atr-progress-copy">
                            <strong>Progress Pemanggilan</strong>
                            <span>
                                Sudah {{ number_format((int) ($atrProgress['sudah'] ?? 0)) }}
                                · Belum {{ number_format((int) ($atrProgress['belum'] ?? 0)) }}
                                · Perlu Ulang {{ number_format((int) ($atrProgress['ulang'] ?? 0)) }}
                            </span>
                        </div>

                        <div class="employee-dashboard-atr-track">
                            <div
                                class="employee-dashboard-atr-bar"
                                style="--atr-progress: {{ min(100, max(0, (float) ($atrProgress['percentage'] ?? 0))) }}%;"
                            ></div>
                        </div>

                        <div class="employee-dashboard-atr-progress-value">
                            {{ number_format((float) ($atrProgress['percentage'] ?? 0), 1) }}%
                        </div>
                    </div>

                    <div class="employee-dashboard-atr-actions">
                        <a
                            href="{{ route('database.atr.summary', ['period' => $atrSummary['period']]) }}"
                            class="employee-dashboard-button employee-dashboard-source-button"
                        >
                            RINGKASAN ATR
                        </a>

                        <a
                            href="{{ route('database.atr.calls', ['period' => $atrSummary['period']]) }}"
                            class="employee-dashboard-button"
                        >
                            DOKUMENTASI PEMANGGILAN
                        </a>
                    </div>
                </div>
            @else
                <div class="employee-dashboard-atr-empty">
                    <div>
                        <strong>Belum ada snapshot ATR aktif.</strong>
                        <div class="employee-dashboard-heading p">
                            {{ $atrSummary['reason'] ?? 'Upload data ATR untuk mulai monitoring.' }}
                        </div>
                    </div>

                    <a
                        href="{{ route('database.atr.upload') }}"
                        class="employee-dashboard-button"
                    >
                        BUKA MODUL ATR
                    </a>
                </div>
            @endif
        </article>
    </div>
</section>