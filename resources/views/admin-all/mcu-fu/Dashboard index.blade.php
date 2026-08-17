@extends('admin-all.layout')

@section('title', 'Dashboard MCU & FU Internal — SYNRGYPRO')

@push('styles')
<style>
    .mfi-page {
        display: grid;
        gap: 9px;
    }

    .mfi-title-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 6px;
    }

    .mfi-action {
        display: inline-flex;
        min-height: 30px;
        align-items: center;
        justify-content: center;
        padding: 7px 10px;
        border: 1px solid #cfd8e2;
        border-radius: 7px;
        color: #16324c;
        background: #fff;
        font-size: 8px;
        font-weight: 900;
        text-decoration: none;
    }

    .mfi-action.primary {
        border-color: #0f78ef;
        color: #fff;
        background: #0f78ef;
    }

    .mfi-filter-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 8px 10px;
        border: 1px solid #d7e0e8;
        border-radius: 9px;
        background: #fff;
        box-shadow: 0 3px 12px rgba(31, 47, 65, .04);
    }

    .mfi-filter {
        display: flex;
        flex-wrap: wrap;
        align-items: end;
        gap: 7px;
    }

    .mfi-field {
        display: grid;
        gap: 3px;
    }

    .mfi-field label {
        color: #637386;
        font-size: 7px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .mfi-field select {
        min-width: 125px;
        min-height: 30px;
        padding: 5px 8px;
        border: 1px solid #cfd8e2;
        border-radius: 6px;
        color: #1c3348;
        background: #fff;
        font-size: 8px;
        font-weight: 800;
    }

    .mfi-filter-info {
        color: #617286;
        font-size: 7px;
        font-weight: 800;
        text-align: right;
    }

    .mfi-filter-info strong {
        display: block;
        margin-bottom: 2px;
        color: #19344e;
        font-size: 8px;
    }

    .mfi-alert {
        padding: 9px 11px;
        border: 1px solid #f0c2c5;
        border-radius: 8px;
        color: #9b1c25;
        background: #fff1f2;
        font-size: 8px;
        line-height: 1.45;
    }

    .mfi-kpis {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 8px;
    }

    .mfi-kpi {
        position: relative;
        display: block;
        min-width: 0;
        min-height: 84px;
        overflow: hidden;
        padding: 12px;
        border: 1px solid #dbe2e9;
        border-radius: 10px;
        color: inherit;
        background: #fff;
        box-shadow: 0 4px 14px rgba(31, 47, 65, .05);
        text-decoration: none;
        transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
    }

    .mfi-kpi:hover {
        transform: translateY(-1px);
        border-color: var(--mfi-color);
        box-shadow: 0 7px 18px rgba(31, 47, 65, .09);
    }

    .mfi-kpi::before {
        position: absolute;
        inset: 0 auto 0 0;
        width: 4px;
        background: var(--mfi-color);
        content: '';
    }

    .mfi-kpi-label {
        display: block;
        min-height: 20px;
        color: #667587;
        font-size: 7px;
        font-weight: 900;
        line-height: 1.3;
        text-transform: uppercase;
    }

    .mfi-kpi strong {
        display: block;
        margin-top: 4px;
        color: #11283e;
        font-size: 23px;
        line-height: 1;
    }

    .mfi-kpi small {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 5px;
        margin-top: 5px;
        color: #8290a0;
        font-size: 7px;
    }

    .mfi-kpi small b {
        color: var(--mfi-color);
        font-size: 9px;
    }

    .mfi-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 9px;
    }

    .mfi-card {
        min-width: 0;
        border: 1px solid #d9e1e8;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 4px 14px rgba(31, 47, 65, .05);
    }

    .mfi-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 9px 11px;
        border-bottom: 1px solid #e1e7ec;
    }

    .mfi-card-head h2 {
        margin: 0;
        color: #132b42;
        font-size: 11px;
    }

    .mfi-card-head p {
        margin: 2px 0 0;
        color: #718092;
        font-size: 7px;
    }

    .mfi-chart-type {
        padding: 3px 6px;
        border-radius: 999px;
        color: #536679;
        background: #f1f4f7;
        font-size: 6px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .mfi-card-body {
        padding: 11px;
    }

    .mfi-empty {
        display: grid;
        min-height: 145px;
        place-items: center;
        color: #8693a0;
        font-size: 8px;
    }

    /*
    |--------------------------------------------------------------------------
    | Donut
    |--------------------------------------------------------------------------
    */

    .mfi-donut-layout {
        display: grid;
        grid-template-columns: 155px minmax(0, 1fr);
        align-items: center;
        gap: 14px;
        min-height: 180px;
    }

    .mfi-donut {
        display: grid;
        place-items: center;
    }

    .mfi-donut svg {
        width: 145px;
        height: 145px;
        overflow: visible;
    }

    .mfi-donut-segment {
        cursor: pointer;
        transition: opacity .15s ease, stroke-width .15s ease;
    }

    .mfi-donut-segment:hover {
        opacity: .82;
        stroke-width: 13;
    }

    .mfi-donut-center-big {
        fill: #122b42;
        font-size: 12px;
        font-weight: 900;
        text-anchor: middle;
    }

    .mfi-donut-center-small {
        fill: #738296;
        font-size: 5px;
        font-weight: 800;
        text-anchor: middle;
        text-transform: uppercase;
    }

    .mfi-legend {
        display: grid;
        gap: 7px;
    }

    .mfi-legend-item {
        display: grid;
        grid-template-columns: 9px minmax(0, 1fr) auto;
        align-items: center;
        gap: 7px;
        padding: 5px 6px;
        border-radius: 6px;
        color: inherit;
        text-decoration: none;
        transition: background .15s ease;
    }

    .mfi-legend-item:hover {
        background: #f4f7f9;
    }

    .mfi-legend-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: var(--legend-color);
    }

    .mfi-legend-label {
        overflow: hidden;
        color: #3d5267;
        font-size: 7px;
        font-weight: 900;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .mfi-legend-value {
        color: #152f46;
        font-size: 8px;
        font-weight: 900;
    }

    /*
    |--------------------------------------------------------------------------
    | Horizontal Bars
    |--------------------------------------------------------------------------
    */

    .mfi-hbars {
        display: grid;
        gap: 9px;
        min-height: 180px;
        align-content: center;
    }

    .mfi-hbar {
        display: grid;
        grid-template-columns: minmax(90px, 145px) minmax(0, 1fr) 40px;
        align-items: center;
        gap: 8px;
        color: inherit;
        text-decoration: none;
    }

    .mfi-hbar-label {
        overflow: hidden;
        color: #405267;
        font-size: 7px;
        font-weight: 900;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .mfi-hbar-track {
        height: 10px;
        overflow: hidden;
        border-radius: 999px;
        background: #edf1f5;
    }

    .mfi-hbar-fill {
        width: var(--bar-width);
        height: 100%;
        min-width: 2px;
        border-radius: inherit;
        background: var(--bar-color);
        transition: filter .15s ease, transform .15s ease;
        transform-origin: left center;
    }

    .mfi-hbar:hover .mfi-hbar-fill {
        filter: saturate(1.2);
        transform: scaleX(1.01);
    }

    .mfi-hbar-value {
        color: #233b52;
        font-size: 8px;
        font-weight: 900;
        text-align: right;
    }

    /*
    |--------------------------------------------------------------------------
    | Vertical Bars
    |--------------------------------------------------------------------------
    */

    .mfi-vbars {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        align-items: end;
        gap: 18px;
        min-height: 180px;
        padding: 12px 18px 3px;
        border-bottom: 1px solid #dce4eb;
    }

    .mfi-vbar {
        display: grid;
        grid-template-rows: 18px 130px auto;
        gap: 5px;
        align-items: end;
        color: inherit;
        text-align: center;
        text-decoration: none;
    }

    .mfi-vbar-value {
        color: #18354e;
        font-size: 9px;
        font-weight: 900;
    }

    .mfi-vbar-track {
        position: relative;
        display: flex;
        height: 130px;
        align-items: end;
        justify-content: center;
        border-radius: 7px 7px 0 0;
        background:
            repeating-linear-gradient(
                to top,
                #f7f9fb 0,
                #f7f9fb 31px,
                #e6ebf0 32px
            );
    }

    .mfi-vbar-fill {
        width: min(44px, 72%);
        height: var(--bar-height);
        min-height: 2px;
        border-radius: 7px 7px 0 0;
        background: var(--bar-color);
        transition: opacity .15s ease, transform .15s ease;
        transform-origin: bottom;
    }

    .mfi-vbar:hover .mfi-vbar-fill {
        opacity: .84;
        transform: scaleY(1.02);
    }

    .mfi-vbar-label {
        color: #42576b;
        font-size: 7px;
        font-weight: 900;
    }

    /*
    |--------------------------------------------------------------------------
    | Top Jabatan
    |--------------------------------------------------------------------------
    */

    .mfi-rank {
        display: grid;
        gap: 7px;
    }

    .mfi-rank-row {
        display: grid;
        grid-template-columns: 20px minmax(155px, 210px) minmax(0, 1fr) 38px;
        align-items: center;
        gap: 8px;
        padding: 4px 5px;
        border-radius: 6px;
        color: inherit;
        text-decoration: none;
    }

    .mfi-rank-row:hover {
        background: #f6f9fb;
    }

    .mfi-rank-no {
        display: grid;
        width: 20px;
        height: 20px;
        place-items: center;
        border-radius: 6px;
        color: #fff;
        background: var(--rank-color);
        font-size: 7px;
        font-weight: 900;
    }

    .mfi-rank-label {
        overflow: hidden;
        color: #344b60;
        font-size: 7px;
        font-weight: 900;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .mfi-rank-track {
        height: 7px;
        overflow: hidden;
        border-radius: 999px;
        background: #edf1f5;
    }

    .mfi-rank-fill {
        width: var(--rank-width);
        height: 100%;
        min-width: 2px;
        border-radius: inherit;
        background: var(--rank-color);
    }

    .mfi-rank-value {
        color: #203b53;
        font-size: 8px;
        font-weight: 900;
        text-align: right;
    }

    @media (max-width: 1150px) {
        .mfi-kpis {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 850px) {
        .mfi-grid {
            grid-template-columns: 1fr;
        }

        .mfi-filter-card {
            align-items: stretch;
            flex-direction: column;
        }

        .mfi-filter-info {
            text-align: left;
        }
    }

    @media (max-width: 650px) {
        .mfi-kpis {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .mfi-donut-layout {
            grid-template-columns: 1fr;
        }

        .mfi-rank-row {
            grid-template-columns: 20px minmax(100px, 150px) minmax(0, 1fr) 32px;
        }
    }
</style>
@endpush

@section('admin-content')
@php
    $summary = $dashboard['summary'];
    $filters = $dashboard['filters'];

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

    $baseQuery = array_filter(
        [
            'year' => $filters['year'],
            'month' => $filters['month'],
        ],
        fn ($value) => $value !== null && $value !== ''
    );

    $dataUrl = function (
        string $routeName,
        array $extra = []
    ) use ($baseQuery): string {
        return route(
            $routeName,
            array_merge(
                $baseQuery,
                $extra
            )
        );
    };

    $palette = [
        '#0f78ef',
        '#16a36b',
        '#e58a17',
        '#8b5cf6',
        '#dc4b5a',
        '#0891b2',
        '#64748b',
        '#d97706',
        '#4f46e5',
        '#059669',
    ];

    $kpis = [
        [
            'label' => 'Total Data',
            'value' => $summary['total_data'],
            'note' => 'Data sesuai filter',
            'color' => '#173b63',
            'url' => $dataUrl('admin-all.mcu-fu.mcu'),
        ],
        [
            'label' => 'MCU Done',
            'value' => $summary['mcu_done'],
            'note' => 'Status MCU DONE',
            'color' => '#1f9d66',
            'url' => $dataUrl(
                'admin-all.mcu-fu.mcu',
                ['status_mcu' => 'DONE']
            ),
        ],
        [
            'label' => 'Fit To Work',
            'value' => $summary['fit_to_work'],
            'note' => 'Lihat data FIT TO WORK',
            'color' => '#1976d2',
            'url' => $dataUrl(
                'admin-all.mcu-fu.mcu',
                ['hasil_mcu' => 'FIT TO WORK']
            ),
        ],
        [
            'label' => 'Hasil Follow Up',
            'value' => $summary['hasil_follow_up'],
            'note' => 'Lihat data FOLLOW UP',
            'color' => '#e18a17',
            'url' => $dataUrl(
                'admin-all.mcu-fu.follow-up',
                ['hasil_mcu' => 'FOLLOW UP']
            ),
        ],
        [
            'label' => 'Follow Up Aktif',
            'value' => $summary['fu_active'],
            'note' => 'Lihat data Follow Up',
            'color' => '#8b5cf6',
            'url' => $dataUrl(
                'admin-all.mcu-fu.follow-up'
            ),
        ],
        [
            'label' => 'FU Completed',
            'value' => $summary['fu_completed'],
            'note' => 'Lihat COMPLETED',
            'color' => '#0f8f83',
            'url' => $dataUrl(
                'admin-all.mcu-fu.follow-up',
                ['status_fu' => 'COMPLETED']
            ),
        ],
    ];
@endphp

<div class="mfi-page">

    <div class="aa-page-title">
        <div>
            <h1>Dashboard MCU &amp; FU Internal</h1>
            <p>
                Monitoring Medical Check Up dan Follow Up Internal Produksi.
            </p>
        </div>

        <div class="mfi-title-actions">
            <a
                href="{{ route('admin-all.mcu-fu.mcu') }}"
                class="mfi-action primary"
            >
                INPUT / UPDATE MCU
            </a>

            <a
                href="{{ route('admin-all.mcu-fu.follow-up') }}"
                class="mfi-action"
            >
                INPUT FOLLOW UP
            </a>

            <a
                href="{{ route('admin-all.mcu-fu.history') }}"
                class="mfi-action"
            >
                RIWAYAT UPDATE
            </a>
        </div>
    </div>

    @if (!empty($error))
        <div class="mfi-alert">
            <strong>Data Google Spreadsheet belum dapat dibaca.</strong>
            <br>
            {{ $error }}
        </div>
    @endif

    <div class="mfi-filter-card">
        <form
            method="GET"
            action="{{ route('admin-all.mcu-fu.index') }}"
            class="mfi-filter"
        >
            <div class="mfi-field">
                <label for="mfiYear">Tahun</label>
                <select
                    name="year"
                    id="mfiYear"
                >
                    @foreach ($filters['years'] as $year)
                        <option
                            value="{{ $year }}"
                            @selected((int) $filters['year'] === (int) $year)
                        >
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mfi-field">
                <label for="mfiMonth">Bulan</label>
                <select
                    name="month"
                    id="mfiMonth"
                >
                    <option value="">
                        Semua Bulan
                    </option>

                    @foreach ($monthNames as $monthNumber => $monthLabel)
                        <option
                            value="{{ $monthNumber }}"
                            @selected((int) $filters['month'] === $monthNumber)
                        >
                            {{ $monthLabel }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button
                type="submit"
                class="mfi-action primary"
            >
                TERAPKAN FILTER
            </button>

            <a
                href="{{ route('admin-all.mcu-fu.index', ['year' => now()->year]) }}"
                class="mfi-action"
            >
                RESET
            </a>
        </form>

        <div class="mfi-filter-info">
            <strong>
                Acuan filter: {{ $filters['date_field'] }}
            </strong>

            Tahun
            {{ $filters['year'] ?? 'Semua' }}

            @if ($filters['month'])
                · {{ $monthNames[$filters['month']] ?? '' }}
            @else
                · Semua Bulan
            @endif

            · {{ number_format($summary['total_data']) }}
            dari {{ number_format($filters['total_all']) }} data
        </div>
    </div>

    <div class="mfi-kpis">
        @foreach ($kpis as $kpi)
            <a
                href="{{ $kpi['url'] }}"
                class="mfi-kpi"
                style="--mfi-color: {{ $kpi['color'] }}"
                title="Klik untuk melihat data"
            >
                <span class="mfi-kpi-label">
                    {{ $kpi['label'] }}
                </span>

                <strong>
                    {{ number_format($kpi['value']) }}
                </strong>

                <small>
                    <span>{{ $kpi['note'] }}</span>
                    <b>›</b>
                </small>
            </a>
        @endforeach
    </div>

    <div class="mfi-grid">

        {{-- ============================================================
             HASIL MCU — DONUT
        ============================================================= --}}
        <div class="mfi-card">
            <div class="mfi-card-head">
                <div>
                    <h2>Hasil MCU</h2>
                    <p>
                        Klik segmen atau legend untuk membuka data.
                    </p>
                </div>

                <span class="mfi-chart-type">
                    Donut Chart
                </span>
            </div>

            <div class="mfi-card-body">
                @if (empty($dashboard['hasil_mcu']))
                    <div class="mfi-empty">
                        Belum ada data hasil MCU pada periode ini.
                    </div>
                @else
                    @php
                        $items = $dashboard['hasil_mcu'];
                        $total = collect($items)->sum('total');
                        $radius = 40;
                        $circumference = 2 * pi() * $radius;
                        $running = 0;
                    @endphp

                    <div class="mfi-donut-layout">
                        <div class="mfi-donut">
                            <svg
                                viewBox="0 0 100 100"
                                role="img"
                                aria-label="Donut Hasil MCU"
                            >
                                <circle
                                    cx="50"
                                    cy="50"
                                    r="{{ $radius }}"
                                    fill="none"
                                    stroke="#edf1f5"
                                    stroke-width="11"
                                />

                                @foreach ($items as $index => $item)
                                    @php
                                        $share = $total > 0
                                            ? ($item['total'] / $total)
                                            : 0;

                                        $segmentLength =
                                            $share * $circumference;

                                        $visibleLength = max(
                                            0,
                                            $segmentLength - 0.8
                                        );

                                        $offset = -$running;

                                        $running += $segmentLength;

                                        $isFollowUp = str_contains(
                                            strtoupper($item['label']),
                                            'FOLLOW UP'
                                        );

                                        $targetRoute = $isFollowUp
                                            ? 'admin-all.mcu-fu.follow-up'
                                            : 'admin-all.mcu-fu.mcu';

                                        $targetUrl = $dataUrl(
                                            $targetRoute,
                                            ['hasil_mcu' => $item['label']]
                                        );

                                        $color =
                                            $palette[$index % count($palette)];
                                    @endphp

                                    <a
                                        href="{{ $targetUrl }}"
                                        aria-label="{{ $item['label'] }} {{ $item['total'] }}"
                                    >
                                        <circle
                                            class="mfi-donut-segment"
                                            cx="50"
                                            cy="50"
                                            r="{{ $radius }}"
                                            fill="none"
                                            stroke="{{ $color }}"
                                            stroke-width="11"
                                            stroke-linecap="butt"
                                            stroke-dasharray="{{ $visibleLength }} {{ max(0, $circumference - $visibleLength) }}"
                                            stroke-dashoffset="{{ $offset }}"
                                            transform="rotate(-90 50 50)"
                                        >
                                            <title>
                                                {{ $item['label'] }}:
                                                {{ $item['total'] }}
                                            </title>
                                        </circle>
                                    </a>
                                @endforeach

                                <text
                                    x="50"
                                    y="48"
                                    class="mfi-donut-center-big"
                                >
                                    {{ number_format($total) }}
                                </text>

                                <text
                                    x="50"
                                    y="57"
                                    class="mfi-donut-center-small"
                                >
                                    HASIL MCU
                                </text>
                            </svg>
                        </div>

                        <div class="mfi-legend">
                            @foreach ($items as $index => $item)
                                @php
                                    $isFollowUp = str_contains(
                                        strtoupper($item['label']),
                                        'FOLLOW UP'
                                    );

                                    $targetRoute = $isFollowUp
                                        ? 'admin-all.mcu-fu.follow-up'
                                        : 'admin-all.mcu-fu.mcu';

                                    $targetUrl = $dataUrl(
                                        $targetRoute,
                                        ['hasil_mcu' => $item['label']]
                                    );

                                    $color =
                                        $palette[$index % count($palette)];
                                @endphp

                                <a
                                    href="{{ $targetUrl }}"
                                    class="mfi-legend-item"
                                    title="Klik untuk lihat data {{ $item['label'] }}"
                                >
                                    <span
                                        class="mfi-legend-dot"
                                        style="--legend-color: {{ $color }}"
                                    ></span>

                                    <span class="mfi-legend-label">
                                        {{ $item['label'] }}
                                        · {{ number_format($item['share'], 1) }}%
                                    </span>

                                    <span class="mfi-legend-value">
                                        {{ number_format($item['total']) }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ============================================================
             STATUS MCU — HORIZONTAL BAR
        ============================================================= --}}
        <div class="mfi-card">
            <div class="mfi-card-head">
                <div>
                    <h2>Status MCU</h2>
                    <p>
                        Distribusi status proses MCU.
                    </p>
                </div>

                <span class="mfi-chart-type">
                    Bar Chart
                </span>
            </div>

            <div class="mfi-card-body">
                @if (empty($dashboard['status_mcu']))
                    <div class="mfi-empty">
                        Belum ada data status MCU pada periode ini.
                    </div>
                @else
                    <div class="mfi-hbars">
                        @foreach ($dashboard['status_mcu'] as $index => $item)
                            @php
                                $color =
                                    $palette[
                                        ($index + 1) % count($palette)
                                    ];
                            @endphp

                            <a
                                href="{{ $dataUrl('admin-all.mcu-fu.mcu', ['status_mcu' => $item['label']]) }}"
                                class="mfi-hbar"
                                title="Klik untuk melihat {{ $item['label'] }}"
                            >
                                <span class="mfi-hbar-label">
                                    {{ $item['label'] }}
                                </span>

                                <span class="mfi-hbar-track">
                                    <span
                                        class="mfi-hbar-fill"
                                        style="
                                            --bar-width: {{ $item['percent'] }}%;
                                            --bar-color: {{ $color }};
                                        "
                                    ></span>
                                </span>

                                <span class="mfi-hbar-value">
                                    {{ number_format($item['total']) }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- ============================================================
             FOLLOW UP STAGE — VERTICAL BAR
        ============================================================= --}}
        <div class="mfi-card">
            <div class="mfi-card-head">
                <div>
                    <h2>Follow Up</h2>
                    <p>
                        Klik batang untuk melihat data pada tahap FU.
                    </p>
                </div>

                <span class="mfi-chart-type">
                    Column Chart
                </span>
            </div>

            <div class="mfi-card-body">
                @if (empty($dashboard['follow_up']))
                    <div class="mfi-empty">
                        Belum ada data follow up pada periode ini.
                    </div>
                @else
                    <div class="mfi-vbars">
                        @foreach ($dashboard['follow_up'] as $index => $item)
                            @php
                                $stage = $index + 1;
                                $color =
                                    $palette[
                                        ($index + 3) % count($palette)
                                    ];
                            @endphp

                            <a
                                href="{{ $dataUrl('admin-all.mcu-fu.follow-up', ['fu_stage' => $stage]) }}"
                                class="mfi-vbar"
                                title="Klik untuk melihat {{ $item['label'] }}"
                            >
                                <span class="mfi-vbar-value">
                                    {{ number_format($item['total']) }}
                                </span>

                                <span class="mfi-vbar-track">
                                    <span
                                        class="mfi-vbar-fill"
                                        style="
                                            --bar-height: {{ $item['percent'] }}%;
                                            --bar-color: {{ $color }};
                                        "
                                    ></span>
                                </span>

                                <span class="mfi-vbar-label">
                                    {{ $item['label'] }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- ============================================================
             STATUS FU — DONUT
        ============================================================= --}}
        <div class="mfi-card">
            <div class="mfi-card-head">
                <div>
                    <h2>Status Follow Up</h2>
                    <p>
                        Status FU kolom M. Klik untuk membuka detail data.
                    </p>
                </div>

                <span class="mfi-chart-type">
                    Donut Chart
                </span>
            </div>

            <div class="mfi-card-body">
                @if (empty($dashboard['status_fu']))
                    <div class="mfi-empty">
                        Belum ada data status FU pada periode ini.
                    </div>
                @else
                    @php
                        $items = $dashboard['status_fu'];
                        $total = collect($items)->sum('total');
                        $radius = 40;
                        $circumference = 2 * pi() * $radius;
                        $running = 0;
                    @endphp

                    <div class="mfi-donut-layout">
                        <div class="mfi-donut">
                            <svg
                                viewBox="0 0 100 100"
                                role="img"
                                aria-label="Donut Status Follow Up"
                            >
                                <circle
                                    cx="50"
                                    cy="50"
                                    r="{{ $radius }}"
                                    fill="none"
                                    stroke="#edf1f5"
                                    stroke-width="11"
                                />

                                @foreach ($items as $index => $item)
                                    @php
                                        $share = $total > 0
                                            ? ($item['total'] / $total)
                                            : 0;

                                        $segmentLength =
                                            $share * $circumference;

                                        $visibleLength = max(
                                            0,
                                            $segmentLength - 0.8
                                        );

                                        $offset = -$running;
                                        $running += $segmentLength;

                                        $color =
                                            $palette[
                                                ($index + 5) % count($palette)
                                            ];

                                        $targetUrl = $dataUrl(
                                            'admin-all.mcu-fu.follow-up',
                                            ['status_fu' => $item['label']]
                                        );
                                    @endphp

                                    <a
                                        href="{{ $targetUrl }}"
                                        aria-label="{{ $item['label'] }} {{ $item['total'] }}"
                                    >
                                        <circle
                                            class="mfi-donut-segment"
                                            cx="50"
                                            cy="50"
                                            r="{{ $radius }}"
                                            fill="none"
                                            stroke="{{ $color }}"
                                            stroke-width="11"
                                            stroke-linecap="butt"
                                            stroke-dasharray="{{ $visibleLength }} {{ max(0, $circumference - $visibleLength) }}"
                                            stroke-dashoffset="{{ $offset }}"
                                            transform="rotate(-90 50 50)"
                                        >
                                            <title>
                                                {{ $item['label'] }}:
                                                {{ $item['total'] }}
                                            </title>
                                        </circle>
                                    </a>
                                @endforeach

                                <text
                                    x="50"
                                    y="48"
                                    class="mfi-donut-center-big"
                                >
                                    {{ number_format($total) }}
                                </text>

                                <text
                                    x="50"
                                    y="57"
                                    class="mfi-donut-center-small"
                                >
                                    STATUS FU
                                </text>
                            </svg>
                        </div>

                        <div class="mfi-legend">
                            @foreach ($items as $index => $item)
                                @php
                                    $color =
                                        $palette[
                                            ($index + 5) % count($palette)
                                        ];
                                @endphp

                                <a
                                    href="{{ $dataUrl('admin-all.mcu-fu.follow-up', ['status_fu' => $item['label']]) }}"
                                    class="mfi-legend-item"
                                    title="Klik untuk lihat {{ $item['label'] }}"
                                >
                                    <span
                                        class="mfi-legend-dot"
                                        style="--legend-color: {{ $color }}"
                                    ></span>

                                    <span class="mfi-legend-label">
                                        {{ $item['label'] }}
                                        · {{ number_format($item['share'], 1) }}%
                                    </span>

                                    <span class="mfi-legend-value">
                                        {{ number_format($item['total']) }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- ================================================================
         TOP JABATAN
    ================================================================= --}}
    <div class="mfi-card">
        <div class="mfi-card-head">
            <div>
                <h2>Top Jabatan</h2>
                <p>
                    10 jabatan dengan data MCU &amp; FU terbanyak.
                    Klik baris untuk membuka datanya.
                </p>
            </div>

            <span class="mfi-chart-type">
                Ranking Bar
            </span>
        </div>

        <div class="mfi-card-body">
            @if (empty($dashboard['jabatan']))
                <div class="mfi-empty">
                    Belum ada data jabatan pada periode ini.
                </div>
            @else
                <div class="mfi-rank">
                    @foreach ($dashboard['jabatan'] as $index => $item)
                        @php
                            $color =
                                $palette[
                                    $index % count($palette)
                                ];
                        @endphp

                        <a
                            href="{{ $dataUrl('admin-all.mcu-fu.mcu', ['jabatan' => $item['label']]) }}"
                            class="mfi-rank-row"
                            title="Klik untuk melihat {{ $item['label'] }}"
                        >
                            <span
                                class="mfi-rank-no"
                                style="--rank-color: {{ $color }}"
                            >
                                {{ $index + 1 }}
                            </span>

                            <span class="mfi-rank-label">
                                {{ $item['label'] }}
                            </span>

                            <span class="mfi-rank-track">
                                <span
                                    class="mfi-rank-fill"
                                    style="
                                        --rank-width: {{ $item['percent'] }}%;
                                        --rank-color: {{ $color }};
                                    "
                                ></span>
                            </span>

                            <span class="mfi-rank-value">
                                {{ number_format($item['total']) }}
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>
@endsection