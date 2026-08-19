@extends('admin-all.layout')

@section('title', 'IFUTS TICKETING')

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
            'local' => 0,
            'non_local' => 0,
            'out_scheduled' => 0,
            'in_scheduled' => 0,
            'regular_out' => 0,
            'additional_out' => 0,
            'regular_in' => 0,
            'additional_in' => 0,
        ],
        'category_chart' => [],
        'position_chart' => [],
        'poh_chart' => [],
        'locality_chart' => [],
        'month_chart' => [],
        'year_chart' => [],
        'route_out_chart' => [],
        'route_in_chart' => [],
        'rows' => [],
        'data_total' => 0,
        'all_total' => 0,
    ];

    $filters = $dashboard['filters'] ?? [];
    $summary = $dashboard['summary'] ?? [];

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

    $palette = [
        '#09879a',
        '#1479ef',
        '#ef7d00',
        '#0aa768',
        '#7c3aed',
        '#e11d48',
        '#64748b',
        '#06b6d4',
        '#84cc16',
        '#f59e0b',
    ];

    $prepareDonut = static function (array $items, int $limit = 8): array {
        $items = array_values(
            array_filter(
                $items,
                static fn ($item) => (int) ($item['count'] ?? 0) > 0
            )
        );

        if (count($items) <= $limit) {
            return $items;
        }

        $head = array_slice($items, 0, max(1, $limit - 1));
        $tail = array_slice($items, max(1, $limit - 1));
        $otherCount = array_sum(
            array_map(
                static fn ($item) => (int) ($item['count'] ?? 0),
                $tail
            )
        );

        $head[] = [
            'key' => 'LAINNYA',
            'label' => 'LAINNYA',
            'count' => $otherCount,
        ];

        return $head;
    };

    $buildConic = static function (array $items) use ($palette): string {
        $total = array_sum(
            array_map(
                static fn ($item) => (int) ($item['count'] ?? 0),
                $items
            )
        );

        if ($total <= 0) {
            return 'conic-gradient(#e8edf3 0 100%)';
        }

        $cursor = 0.0;
        $segments = [];

        foreach ($items as $index => $item) {
            $count = (int) ($item['count'] ?? 0);
            $start = $cursor;
            $cursor += ($count / $total) * 100;
            $color = $palette[$index % count($palette)];

            $segments[] = $color.' '.number_format($start, 4, '.', '').'% '.number_format($cursor, 4, '.', '').'%';
        }

        return 'conic-gradient('.implode(', ', $segments).')';
    };

    $categoryDonut = $prepareDonut($dashboard['category_chart'] ?? [], 8);
    $categoryGradient = $buildConic($categoryDonut);

    $topPoh = array_slice($dashboard['poh_chart'] ?? [], 0, 7);
    $topPositions = array_slice($dashboard['position_chart'] ?? [], 0, 7);
    $topRouteOut = array_slice($dashboard['route_out_chart'] ?? [], 0, 6);
    $topRouteIn = array_slice($dashboard['route_in_chart'] ?? [], 0, 6);
    $monthChart = $dashboard['month_chart'] ?? [];

    $maxPoh = max(1, ...array_map(static fn ($item) => (int) ($item['count'] ?? 0), $topPoh ?: [['count' => 1]]));
    $maxPosition = max(1, ...array_map(static fn ($item) => (int) ($item['count'] ?? 0), $topPositions ?: [['count' => 1]]));
    $maxRouteOut = max(1, ...array_map(static fn ($item) => (int) ($item['count'] ?? 0), $topRouteOut ?: [['count' => 1]]));
    $maxRouteIn = max(1, ...array_map(static fn ($item) => (int) ($item['count'] ?? 0), $topRouteIn ?: [['count' => 1]]));
    $maxMonth = max(1, ...array_map(static fn ($item) => (int) ($item['count'] ?? 0), $monthChart ?: [['count' => 1]]));

    $hasSecondaryFilter = collect([
        $filters['month'] ?? null,
        $filters['category'] ?? null,
        $filters['position'] ?? null,
        $filters['poh'] ?? null,
    ])->filter(static fn ($value) => $value !== null && $value !== '')->isNotEmpty();

    /*
    |--------------------------------------------------------------------------
    | Drill-down Dashboard -> Monitoring Ticket
    |--------------------------------------------------------------------------
    | Klik pada chart/legend/bar akan membuka Monitoring Ticket dengan filter
    | dashboard yang sedang aktif + filter item yang diklik.
    */
    $monitoringUrl = static function (array $override = []) use ($filters, $selectedYear): string {
        $query = [
            'year' => $filters['year'] ?? $selectedYear,
            'month' => $filters['month'] ?? null,
            'category' => $filters['category'] ?? null,
            'position' => $filters['position'] ?? null,
            'poh' => $filters['poh'] ?? null,
            'per_page' => 20,
        ];

        foreach ($override as $key => $value) {
            $query[$key] = $value;
        }

        $query = array_filter(
            $query,
            static fn ($value) => $value !== null && $value !== ''
        );

        return route('admin-all.ifuts.monitoring')
            .($query !== [] ? '?'.http_build_query($query) : '');
    };
@endphp

<style>
    /* =========================================================
       IFUTS DASHBOARD — FIXED INNER SHELL
       Header/sidebar/footer berasal dari admin-all.layout.
       Title, status, filter, 3 KPI utama dan chart tetap.
       Dashboard ini PURE ANALYTIC: tidak merender rows data.
       Rows hanya tersedia pada halaman Monitoring Ticket.
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

    .if-page {
        display: flex;
        width: 100%;
        height: 100%;
        min-height: 0;
        flex-direction: column;
        overflow: hidden;
    }

    .if-head,
    .if-status-strip,
    .if-filter-card,
    .if-summary-grid {
        flex: 0 0 auto;
    }

    .if-analytics {
        min-height: 0;
        flex: 1 1 auto;
    }

    .if-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 7px;
    }

    .if-title {
        margin: 0;
        color: #0d2c59;
        font-size: clamp(20px, 1.8vw, 26px);
        font-weight: 900;
        letter-spacing: -.03em;
        line-height: 1.05;
    }
.if-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 6px;
    }

    .if-btn {
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
        transition: transform .15s ease, box-shadow .15s ease;
        white-space: nowrap;
    }

    .if-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 5px 14px rgba(31, 47, 65, .08);
        text-decoration: none;
    }

    .if-btn.primary {
        border-color: #09879a;
        color: #fff;
        background: #09879a;
    }

    .if-btn.reset {
        border-color: #e3b6b6;
        color: #a92731;
        background: #fff7f7;
    }

    .if-status-strip {
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

    .if-status-strip.error {
        border-color: #f0c4c8;
        color: #a72632;
        background: #fff2f3;
    }

    .if-status-left,
    .if-status-badges {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
    }

    .if-dot {
        width: 8px;
        height: 8px;
        flex: 0 0 8px;
        border-radius: 50%;
        background: currentColor;
        box-shadow: 0 0 0 3px rgba(17, 100, 61, .10);
    }

    .if-badge {
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

    .if-badge.readonly {
        color: #0d63b7;
        background: #e8f3ff;
    }

    .if-filter-card {
        margin-bottom: 7px;
        padding: 8px;
        border: 1px solid #d9e0e7;
        border-radius: 9px;
        background: #fff;
        box-shadow: 0 4px 14px rgba(31, 47, 65, .04);
    }

    .if-filter-grid {
        display: grid;
        grid-template-columns: 110px 125px minmax(150px, 1fr) minmax(170px, 1fr) minmax(150px, .85fr) auto;
        gap: 7px;
        align-items: end;
    }

    .if-field label {
        display: block;
        margin: 0 0 4px;
        color: #526174;
        font-size: 7px;
        font-weight: 900;
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    .if-control {
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

    .if-control:focus {
        border-color: #09879a;
        box-shadow: 0 0 0 3px rgba(9, 135, 154, .09);
    }

    .if-apply {
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

    .if-filter-actions {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .if-filter-reset {
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

    .if-filter-reset:hover {
        color: #8e1f28;
        background: #fff0f1;
        text-decoration: none;
    }

    .if-summary-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 7px;
        margin-bottom: 7px;
    }

    .if-summary {
        position: relative;
        min-width: 0;
        min-height: 52px;
        padding: 8px 9px 7px 42px;
        overflow: hidden;
        border: 1px solid #d9e0e7;
        border-radius: 9px;
        background: #fff;
        box-shadow: 0 4px 14px rgba(31, 47, 65, .04);
    }

    .if-summary-icon {
        position: absolute;
        top: 9px;
        left: 9px;
        display: grid;
        width: 26px;
        height: 26px;
        place-items: center;
        border-radius: 7px;
        color: #fff;
        background: var(--sum-color, #09879a);
        font-size: 8px;
        font-weight: 900;
    }

    .if-summary small {
        display: block;
        overflow: hidden;
        color: #657386;
        font-size: 6px;
        font-weight: 900;
        letter-spacing: .05em;
        text-overflow: ellipsis;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .if-summary strong {
        display: block;
        margin-top: 2px;
        color: #10243d;
        font-size: 18px;
        font-weight: 900;
        line-height: 1;
    }

    .if-summary span {
        display: block;
        margin-top: 2px;
        overflow: hidden;
        color: #7b8797;
        font-size: 6px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .if-analytics {
        width: 100%;
        margin-bottom: 0;
        overflow: hidden;
    }

    .if-chart-grid {
        display: grid;
        width: 100%;
        height: 100%;
        min-height: 0;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        grid-template-rows: repeat(2, minmax(0, 1fr));
        gap: 7px;
    }

    .if-chart-card {
        display: flex;
        height: auto;
        min-height: 0;
        min-width: 0;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid #d9e0e7;
        border-radius: 9px;
        background: #fff;
        box-shadow: 0 4px 14px rgba(31, 47, 65, .04);
    }

    .if-chart-card:hover {
        border-color: #c5d1dc;
        box-shadow: 0 7px 18px rgba(31, 47, 65, .07);
    }

    .if-chart-head {
        display: flex;
        min-height: 36px;
        flex: 0 0 36px;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 7px 10px;
        border-bottom: 1px solid #edf0f4;
    }

    .if-chart-head strong {
        display: block;
        color: #172b43;
        font-size: 9px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .if-chart-head span {
        color: #8a95a5;
        font-size: 7px;
        font-weight: 800;
        white-space: nowrap;
    }

    .if-chart-body {
        display: flex;
        min-height: 0;
        flex: 1 1 auto;
        align-items: center;
        justify-content: center;
        padding: 11px 13px;
        overflow: hidden;
    }

    .if-donut-layout {
        display: grid;
        width: 100%;
        max-width: 430px;
        grid-template-columns: 132px minmax(0, 1fr);
        align-items: center;
        justify-content: center;
        gap: 18px;
    }

    .if-donut-link {
        display: grid;
        width: 122px;
        height: 122px;
        place-items: center;
        border-radius: 50%;
        text-decoration: none;
        transition: transform .15s ease;
    }

    .if-donut-link:hover {
        transform: scale(1.035);
        text-decoration: none;
    }

    .if-donut {
        position: relative;
        width: 118px;
        height: 118px;
        border-radius: 50%;
        background: var(--donut-bg);
    }

    .if-donut::after {
        position: absolute;
        inset: 27px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        color: #172b43;
        background: #fff;
        content: attr(data-total);
        font-size: 16px;
        font-weight: 900;
    }

    .if-legend {
        display: grid;
        align-content: center;
        gap: 6px;
        min-width: 0;
    }

    .if-legend-row {
        display: grid;
        min-width: 0;
        grid-template-columns: 8px minmax(0, 1fr) auto;
        align-items: center;
        gap: 7px;
        padding: 4px 5px;
        border-radius: 6px;
        color: #526174;
        font-size: 8px;
        text-decoration: none;
        transition: background .15s ease, transform .15s ease;
    }

    .if-legend-row:hover {
        color: #172b43;
        background: #f4f8fb;
        transform: translateX(2px);
        text-decoration: none;
    }

    .if-legend-dot {
        width: 8px;
        height: 8px;
        border-radius: 2px;
        background: var(--legend-color);
    }

    .if-legend-label {
        overflow: hidden;
        font-weight: 800;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .if-legend-count {
        color: #172b43;
        font-weight: 900;
    }

    .if-bars {
        display: grid;
        width: 100%;
        max-width: 520px;
        align-content: center;
        gap: 8px;
    }

    .if-bar-row {
        display: grid;
        min-width: 0;
        grid-template-columns: minmax(90px, .9fr) minmax(110px, 1.35fr) 34px;
        align-items: center;
        gap: 9px;
        padding: 3px 5px;
        border-radius: 6px;
        color: inherit;
        text-decoration: none;
        transition: background .15s ease, transform .15s ease;
    }

    .if-bar-row:hover {
        background: #f4f8fb;
        transform: translateX(2px);
        text-decoration: none;
    }

    .if-bar-label {
        overflow: hidden;
        color: #526174;
        font-size: 8px;
        font-weight: 800;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .if-bar-track {
        height: 9px;
        overflow: hidden;
        border-radius: 999px;
        background: #edf1f5;
    }

    .if-bar-fill {
        display: block;
        height: 100%;
        min-width: 2px;
        border-radius: inherit;
        background: #09879a;
    }

    .if-bar-value {
        color: #172b43;
        font-size: 8px;
        font-weight: 900;
        text-align: right;
    }

    .if-month-bars {
        display: flex;
        width: 100%;
        max-width: 540px;
        height: 92%;
        min-height: 120px;
        align-items: flex-end;
        justify-content: space-between;
        gap: 5px;
    }

    .if-month-col {
        display: grid;
        height: 100%;
        min-width: 0;
        flex: 1 1 0;
        grid-template-rows: minmax(0, 1fr) 16px;
        align-items: end;
        gap: 3px;
        padding: 3px 2px;
        border-radius: 6px;
        color: inherit;
        text-decoration: none;
        transition: background .15s ease, transform .15s ease;
    }

    .if-month-col:hover {
        background: #f4f8fb;
        transform: translateY(-2px);
        text-decoration: none;
    }

    .if-month-track {
        display: flex;
        width: 100%;
        height: 100%;
        min-height: 20px;
        align-items: flex-end;
        justify-content: center;
        overflow: hidden;
        border-radius: 4px 4px 2px 2px;
        background: #f3f6f9;
    }

    .if-month-fill {
        display: block;
        width: 76%;
        min-height: 2px;
        border-radius: 3px 3px 0 0;
        background: #1479ef;
    }

    .if-month-label {
        color: #68778a;
        font-size: 7px;
        font-weight: 900;
        text-align: center;
        text-transform: uppercase;
    }


    .if-chart-drill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        color: #6d7b8d !important;
    }

    .if-chart-drill::after {
        color: #09879a;
        content: '↗';
        font-size: 8px;
        font-weight: 900;
    }

    .if-empty-chart {
        display: grid;
        height: 100%;
        place-items: center;
        color: #8b96a8;
        font-size: 7px;
        text-align: center;
    }


    @media (max-width: 1180px) {
        .if-filter-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .if-filter-actions {
            width: 100%;
        }

        .if-apply,
        .if-filter-reset {
            flex: 1 1 0;
        }

        .if-summary-grid {
            grid-template-columns: repeat(3, minmax(150px, 1fr));
            overflow-x: auto;
        }
    }

    @media (max-width: 720px) {
        #adminAllShell .aa-main {
            overflow: hidden !important;
        }

        .if-head,
        .if-status-strip {
            align-items: stretch;
            flex-direction: column;
        }

        .if-actions {
            justify-content: flex-start;
        }

        .if-filter-grid {
            grid-template-columns: 1fr 1fr;
        }

        .if-summary-grid {
            grid-template-columns: repeat(3, 150px);
        }

        .if-analytics {
            overflow-y: auto;
        }

        .if-chart-grid {
            height: auto;
            grid-template-columns: 1fr;
            grid-template-rows: none;
        }

        .if-chart-card {
            min-height: 210px;
        }

        .if-donut-layout {
            grid-template-columns: 110px minmax(0, 1fr);
            gap: 10px;
        }

        .if-donut-link {
            width: 100px;
            height: 100px;
        }

        .if-donut {
            width: 96px;
            height: 96px;
        }

        .if-donut::after {
            inset: 22px;
            font-size: 13px;
        }
    }
</style>

<div class="if-page">
    <div class="if-head">
        <div>
            <h1 class="if-title">IFUTS TICKETING</h1>
        </div>

        <div class="if-actions">
            <a href="{{ route('admin-all') }}" class="if-btn">← Admin All</a>

            <a
                href="{{ $spreadsheetUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                class="if-btn primary"
            >
                ▣ Spreadsheet
            </a>

        </div>
    </div>

    @if(($ifutsIntegration['connected'] ?? false) === true)
        <div class="if-status-strip">
            <div class="if-status-left">
                <span class="if-dot"></span>
                <strong>Google Sheets terhubung.</strong>
                <span>
                    {{ number_format($dashboard['data_total'] ?? 0) }} data tahun {{ $selectedYear }}
                    @if(!empty($ifutsSource['header_row']))
                        • header row {{ $ifutsSource['header_row'] }}
                    @endif
                </span>
            </div>

            <div class="if-status-badges">
                <span class="if-badge">Tahun {{ $selectedYear }}</span>
                <span class="if-badge">IFUTS Produksi</span>
                <span class="if-badge readonly">Read Only</span>
            </div>
        </div>
    @else
        <div class="if-status-strip error">
            <div class="if-status-left">
                <span class="if-dot"></span>
                <strong>Google Sheets IFUTS belum terhubung.</strong>
                <span>{{ $ifutsIntegration['message'] ?? 'Integrasi belum tersedia.' }}</span>
            </div>

            <div class="if-status-badges">
                <span class="if-badge readonly">Read Only</span>
            </div>
        </div>
    @endif

    <form
        method="GET"
        action="{{ route('admin-all.ifuts.index') }}"
        class="if-filter-card"
    >
        <div class="if-filter-grid">
            <div class="if-field">
                <label for="if-year">Tahun OUT</label>
                <select id="if-year" name="year" class="if-control">
                    @foreach($yearOptions as $year)
                        <option
                            value="{{ $year }}"
                            @selected((string) ($filters['year'] ?? '') === (string) $year)
                        >
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="if-field">
                <label for="if-month">Bulan OUT</label>
                <select id="if-month" name="month" class="if-control">
                    <option value="">Semua Bulan</option>
                    @foreach($monthNames as $monthNumber => $monthName)
                        <option
                            value="{{ $monthNumber }}"
                            @selected((string) ($filters['month'] ?? '') === (string) $monthNumber)
                        >
                            {{ $monthName }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="if-field">
                <label for="if-category">Kategori</label>
                <select id="if-category" name="category" class="if-control">
                    <option value="">Semua Kategori</option>
                    @foreach($dashboard['available_categories'] ?? [] as $option)
                        <option
                            value="{{ $option }}"
                            @selected(strtoupper(trim((string) ($filters['category'] ?? ''))) === strtoupper(trim((string) $option)))
                        >
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="if-field">
                <label for="if-position">Jabatan</label>
                <select id="if-position" name="position" class="if-control">
                    <option value="">Semua Jabatan</option>
                    @foreach($dashboard['available_positions'] ?? [] as $option)
                        <option
                            value="{{ $option }}"
                            @selected(strtoupper(trim((string) ($filters['position'] ?? ''))) === strtoupper(trim((string) $option)))
                        >
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="if-field">
                <label for="if-poh">POH</label>
                <select id="if-poh" name="poh" class="if-control">
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

            <div class="if-filter-actions">
                <button type="submit" class="if-apply">Search</button>
                <a
                    href="{{ route('admin-all.ifuts.index') }}"
                    class="if-filter-reset"
                >
                    ↻ Reset
                </a>
            </div>
        </div>
    </form>

    <div class="if-summary-grid">
        <div class="if-summary" style="--sum-color:#09879a">
            <span class="if-summary-icon">Σ</span>
            <small>Total Data</small>
            <strong>{{ number_format($summary['total'] ?? 0) }}</strong>
            <span>
                {{ $hasSecondaryFilter ? 'Hasil filter tahun '.$selectedYear : 'Data tahun '.$selectedYear }}
            </span>
        </div>

        <div class="if-summary" style="--sum-color:#7c3aed">
            <span class="if-summary-icon">R</span>
            <small>Reguler OUT</small>
            <strong>{{ number_format($summary['regular_out'] ?? 0) }}</strong>
            <span>Ket tiket OUT reguler</span>
        </div>

        <div class="if-summary" style="--sum-color:#ef7d00">
            <span class="if-summary-icon">T</span>
            <small>Tambahan OUT</small>
            <strong>{{ number_format($summary['additional_out'] ?? 0) }}</strong>
            <span>Ket tiket OUT tambahan</span>
        </div>
    </div>

    <div class="if-analytics" aria-label="Analitik IFUTS">
        <div class="if-chart-grid">
            <section class="if-chart-card">
                <div class="if-chart-head">
                    <strong>Kategori</strong>
                    <span class="if-chart-drill">Monitoring</span>
                </div>
                <div class="if-chart-body">
                    @if(count($categoryDonut) > 0)
                        <div class="if-donut-layout">
                            <a
                                href="{{ $monitoringUrl() }}"
                                class="if-donut-link"
                                title="Buka Monitoring Ticket"
                            >
                                <div
                                    class="if-donut"
                                    style="--donut-bg:{{ $categoryGradient }}"
                                    data-total="{{ number_format(array_sum(array_column($categoryDonut, 'count'))) }}"
                                ></div>
                            </a>

                            <div class="if-legend">
                                @foreach($categoryDonut as $index => $item)
                                    <a
                                        href="{{ $monitoringUrl(['category' => $item['label'] ?? null]) }}"
                                        class="if-legend-row"
                                        title="Lihat {{ $item['label'] ?? '-' }} di Monitoring Ticket"
                                    >
                                        <span
                                            class="if-legend-dot"
                                            style="--legend-color:{{ $palette[$index % count($palette)] }}"
                                        ></span>
                                        <span class="if-legend-label">
                                            {{ $item['label'] ?? '-' }}
                                        </span>
                                        <span class="if-legend-count">{{ $item['count'] ?? 0 }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="if-empty-chart">Belum ada data kategori.</div>
                    @endif
                </div>
            </section>

            <section class="if-chart-card">
                <div class="if-chart-head">
                    <strong>POH</strong>
                    <span class="if-chart-drill">Top {{ count($topPoh) }}</span>
                </div>
                <div class="if-chart-body">
                    @if(count($topPoh) > 0)
                        <div class="if-bars">
                            @foreach($topPoh as $item)
                                @php
                                    $width = max(2, ((int) ($item['count'] ?? 0) / $maxPoh) * 100);
                                @endphp
                                <a
                                    href="{{ $monitoringUrl(['poh' => $item['label'] ?? null]) }}"
                                    class="if-bar-row"
                                    title="Lihat POH {{ $item['label'] ?? '-' }} di Monitoring Ticket"
                                >
                                    <span class="if-bar-label">
                                        {{ $item['label'] ?? '-' }}
                                    </span>
                                    <span class="if-bar-track">
                                        <span class="if-bar-fill" style="width:{{ $width }}%"></span>
                                    </span>
                                    <span class="if-bar-value">{{ $item['count'] ?? 0 }}</span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="if-empty-chart">Belum ada data POH.</div>
                    @endif
                </div>
            </section>

            <section class="if-chart-card">
                <div class="if-chart-head">
                    <strong>Jabatan</strong>
                    <span class="if-chart-drill">Top {{ count($topPositions) }}</span>
                </div>
                <div class="if-chart-body">
                    @if(count($topPositions) > 0)
                        <div class="if-bars">
                            @foreach($topPositions as $item)
                                @php
                                    $width = max(2, ((int) ($item['count'] ?? 0) / $maxPosition) * 100);
                                @endphp
                                <a
                                    href="{{ $monitoringUrl(['position' => $item['label'] ?? null]) }}"
                                    class="if-bar-row"
                                    title="Lihat jabatan {{ $item['label'] ?? '-' }} di Monitoring Ticket"
                                >
                                    <span class="if-bar-label">{{ $item['label'] ?? '-' }}</span>
                                    <span class="if-bar-track"><span class="if-bar-fill" style="width:{{ $width }}%"></span></span>
                                    <span class="if-bar-value">{{ $item['count'] ?? 0 }}</span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="if-empty-chart">Belum ada data jabatan.</div>
                    @endif
                </div>
            </section>

            <section class="if-chart-card">
                <div class="if-chart-head">
                    <strong>Bulan</strong>
                    <span class="if-chart-drill">TGL OUT</span>
                </div>
                <div class="if-chart-body">
                    @if(count($monthChart) > 0)
                        <div class="if-month-bars">
                            @foreach($monthChart as $item)
                                @php
                                    $height = max(2, ((int) ($item['count'] ?? 0) / $maxMonth) * 100);
                                @endphp
                                <a
                                    href="{{ $monitoringUrl(['month' => $item['key'] ?? null]) }}"
                                    class="if-month-col"
                                    title="Lihat {{ $item['label'] ?? '-' }} di Monitoring Ticket: {{ $item['count'] ?? 0 }} data"
                                >
                                    <div class="if-month-track">
                                        <span class="if-month-fill" style="height:{{ $height }}%"></span>
                                    </div>
                                    <span class="if-month-label">{{ $item['label'] ?? '-' }}</span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="if-empty-chart">Belum ada TGL OUT.</div>
                    @endif
                </div>
            </section>


            <section class="if-chart-card">
                <div class="if-chart-head">
                    <strong>Rute OUT</strong>
                    <span class="if-chart-drill">Top {{ count($topRouteOut) }}</span>
                </div>
                <div class="if-chart-body">
                    @if(count($topRouteOut) > 0)
                        <div class="if-bars">
                            @foreach($topRouteOut as $item)
                                @php
                                    $width = max(2, ((int) ($item['count'] ?? 0) / $maxRouteOut) * 100);
                                @endphp
                                <a
                                    href="{{ $monitoringUrl(['search' => $item['label'] ?? null]) }}"
                                    class="if-bar-row"
                                    title="Lihat rute OUT {{ $item['label'] ?? '-' }} di Monitoring Ticket"
                                >
                                    <span class="if-bar-label">{{ $item['label'] ?? '-' }}</span>
                                    <span class="if-bar-track"><span class="if-bar-fill" style="width:{{ $width }}%;background:#1479ef"></span></span>
                                    <span class="if-bar-value">{{ $item['count'] ?? 0 }}</span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="if-empty-chart">Belum ada rute OUT.</div>
                    @endif
                </div>
            </section>

            <section class="if-chart-card">
                <div class="if-chart-head">
                    <strong>Rute IN</strong>
                    <span class="if-chart-drill">Top {{ count($topRouteIn) }}</span>
                </div>
                <div class="if-chart-body">
                    @if(count($topRouteIn) > 0)
                        <div class="if-bars">
                            @foreach($topRouteIn as $item)
                                @php
                                    $width = max(2, ((int) ($item['count'] ?? 0) / $maxRouteIn) * 100);
                                @endphp
                                <a
                                    href="{{ $monitoringUrl(['search' => $item['label'] ?? null]) }}"
                                    class="if-bar-row"
                                    title="Lihat rute IN {{ $item['label'] ?? '-' }} di Monitoring Ticket"
                                >
                                    <span class="if-bar-label">{{ $item['label'] ?? '-' }}</span>
                                    <span class="if-bar-track"><span class="if-bar-fill" style="width:{{ $width }}%;background:#0aa768"></span></span>
                                    <span class="if-bar-value">{{ $item['count'] ?? 0 }}</span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="if-empty-chart">Belum ada rute IN.</div>
                    @endif
                </div>
            </section>
        </div>
    </div>

</div>
@endsection