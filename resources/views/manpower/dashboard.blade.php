@php
    $selectedPeriod = \Carbon\Carbon::createFromFormat(
        'Y-m',
        $bulan
    );

    $activePercent = $stSpStatus['total'] > 0
        ? round(
            ($stSpStatus['active'] / $stSpStatus['total']) * 100
        )
        : 0;

    $expiredPercent = $stSpStatus['total'] > 0
        ? 100 - $activePercent
        : 0;
@endphp

<style>
    .mp-dashboard {
        --mp-red: #d71920;
        --mp-navy: #172033;
        --mp-muted: #6b7587;
        --mp-border: #dce2e8;
        display: grid;
        gap: 16px;
        min-width: 0;
        padding-bottom: 5px;
    }

    .mp-dashboard * {
        box-sizing: border-box;
    }

    .mp-dashboard-card {
        border: 1px solid var(--mp-border);
        border-radius: 13px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .055);
    }

    .mp-dashboard-hero {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 18px;
        align-items: center;
        padding: 20px 22px;
        overflow: hidden;
        background:
            radial-gradient(
                circle at 85% 25%,
                rgba(255, 255, 255, .16),
                transparent 25%
            ),
            linear-gradient(
                110deg,
                #171717 0%,
                #2a292d 50%,
                #7d3526 78%,
                #d9662b 100%
            );
    }

    .mp-dashboard-hero h1 {
        margin: 0 0 6px;
        color: #ffffff;
        font-size: clamp(21px, 2.2vw, 30px);
        font-weight: 900;
        letter-spacing: -.02em;
    }

    .mp-dashboard-hero p {
        margin: 0;
        color: rgba(255, 255, 255, .78);
        font-size: 13px;
        line-height: 1.5;
    }

    .mp-dashboard-filter {
        display: flex;
        align-items: flex-end;
        gap: 8px;
    }

    .mp-dashboard-filter-field {
        display: grid;
        gap: 6px;
    }

    .mp-dashboard-filter label {
        color: rgba(255, 255, 255, .8);
        font-size: 11px;
        font-weight: 800;
    }

    .mp-dashboard-month {
        min-width: 175px;
        height: 40px;
        padding: 0 11px;
        border: 1px solid rgba(255, 255, 255, .35);
        border-radius: 8px;
        outline: none;
        color: #172033;
        background: #ffffff;
        font-family: inherit;
        font-size: 12px;
        font-weight: 700;
    }

    .mp-dashboard-refresh {
        display: inline-grid;
        width: 40px;
        height: 40px;
        place-items: center;
        border: 1px solid rgba(255, 255, 255, .36);
        border-radius: 8px;
        color: #ffffff;
        background: rgba(255, 255, 255, .12);
        font-size: 19px;
        font-weight: 900;
        text-decoration: none;
    }

    .mp-dashboard-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 13px;
    }

    .mp-summary-card {
        position: relative;
        min-width: 0;
        overflow: hidden;
        padding: 17px;
    }

    .mp-summary-card::after {
        position: absolute;
        top: -22px;
        right: -22px;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: rgba(23, 32, 51, .045);
        content: "";
    }

    .mp-summary-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 12px;
    }

    .mp-summary-label {
        min-width: 0;
        color: var(--mp-muted);
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .045em;
    }

    .mp-summary-icon {
        display: grid;
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        place-items: center;
        border-radius: 10px;
        color: #ffffff;
        background: #273248;
        font-size: 16px;
        font-weight: 900;
    }

    .mp-summary-value {
        margin-bottom: 4px;
        color: var(--mp-navy);
        font-size: 27px;
        font-weight: 900;
        line-height: 1;
    }

    .mp-summary-caption {
        color: var(--mp-muted);
        font-size: 11px;
        line-height: 1.4;
    }

    .mp-summary-card.month .mp-summary-icon {
        background: #2563eb;
    }

    .mp-summary-card.active .mp-summary-icon {
        background: #169b5b;
    }

    .mp-summary-card.expired .mp-summary-icon {
        background: #d71920;
    }

    .mp-dashboard-section {
        overflow: hidden;
    }

    .mp-section-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 15px;
        padding: 17px 19px;
        border-bottom: 1px solid var(--mp-border);
    }

    .mp-section-header h2 {
        margin: 0 0 4px;
        color: var(--mp-navy);
        font-size: 16px;
        font-weight: 900;
    }

    .mp-section-header p {
        margin: 0;
        color: var(--mp-muted);
        font-size: 11px;
        line-height: 1.45;
    }

    .mp-section-badge {
        display: inline-flex;
        min-height: 27px;
        align-items: center;
        padding: 0 10px;
        border-radius: 999px;
        color: #445064;
        background: #f0f3f6;
        font-size: 10px;
        font-weight: 800;
        white-space: nowrap;
    }

    .mp-feature-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        padding: 16px;
    }

    .mp-feature-card {
        position: relative;
        display: grid;
        grid-template-columns: 46px minmax(0, 1fr) auto;
        gap: 12px;
        align-items: center;
        min-width: 0;
        min-height: 104px;
        padding: 14px;
        overflow: hidden;
        border: 1px solid #e2e7ec;
        border-radius: 11px;
        color: inherit;
        background: #ffffff;
        text-decoration: none;
        transition:
            transform .18s ease,
            border-color .18s ease,
            box-shadow .18s ease;
    }

    .mp-feature-card:hover {
        transform: translateY(-2px);
        border-color: #bac3ce;
        box-shadow: 0 10px 23px rgba(15, 23, 42, .09);
    }

    .mp-feature-icon {
        display: grid;
        width: 46px;
        height: 46px;
        place-items: center;
        border-radius: 12px;
        color: #ffffff;
        background: #273248;
        font-size: 20px;
        font-weight: 900;
    }

    .mp-feature-content {
        min-width: 0;
    }

    .mp-feature-title {
        margin: 0 0 5px;
        overflow: hidden;
        color: var(--mp-navy);
        font-size: 13px;
        font-weight: 900;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .mp-feature-description {
        min-height: 29px;
        margin: 0;
        color: var(--mp-muted);
        font-size: 10px;
        line-height: 1.4;
    }

    .mp-feature-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 8px;
    }

    .mp-feature-pill {
        display: inline-flex;
        min-height: 21px;
        align-items: center;
        padding: 0 7px;
        border-radius: 999px;
        color: #4c5769;
        background: #f1f4f7;
        font-size: 9px;
        font-weight: 800;
    }

    .mp-feature-value {
        min-width: 42px;
        color: var(--mp-navy);
        font-size: 24px;
        font-weight: 900;
        text-align: right;
    }

    .mp-feature-card.orange .mp-feature-icon {
        background: #d97706;
    }

    .mp-feature-card.purple .mp-feature-icon {
        background: #7c3aed;
    }

    .mp-feature-card.blue .mp-feature-icon {
        background: #2563eb;
    }

    .mp-feature-card.green .mp-feature-icon {
        background: #169b5b;
    }

    .mp-feature-card.cyan .mp-feature-icon {
        background: #0891b2;
    }

    .mp-feature-card.yellow .mp-feature-icon {
        background: #d69e00;
    }

    .mp-feature-card.red .mp-feature-icon {
        background: #d71920;
    }

    .mp-feature-card.pink .mp-feature-icon {
        background: #db2777;
    }

    .mp-feature-card.navy .mp-feature-icon {
        background: #273248;
    }

    .mp-feature-card.unavailable {
        background: #fafbfc;
    }

    .mp-dashboard-columns {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(300px, .75fr);
        gap: 16px;
    }

    .mp-trend-body {
        padding: 17px 19px 19px;
    }

    .mp-trend-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 13px;
        margin-bottom: 17px;
    }

    .mp-legend-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #596476;
        font-size: 10px;
        font-weight: 700;
    }

    .mp-legend-dot {
        width: 9px;
        height: 9px;
        border-radius: 3px;
        background: #273248;
    }

    .mp-legend-dot.document {
        background: #273248;
    }

    .mp-legend-dot.coaching {
        background: #0891b2;
    }

    .mp-legend-dot.teguran {
        background: #d69e00;
    }

    .mp-legend-dot.peringatan {
        background: #d71920;
    }

    .mp-trend-list {
        display: grid;
        gap: 13px;
    }

    .mp-trend-row {
        display: grid;
        grid-template-columns: 72px minmax(0, 1fr) 34px;
        gap: 10px;
        align-items: center;
    }

    .mp-trend-label {
        color: #4b5565;
        font-size: 10px;
        font-weight: 800;
    }

    .mp-trend-track {
        display: flex;
        width: 100%;
        height: 13px;
        overflow: hidden;
        border-radius: 999px;
        background: #eef1f4;
    }

    .mp-trend-segment {
        height: 100%;
        min-width: 0;
    }

    .mp-trend-segment.document {
        background: #273248;
    }

    .mp-trend-segment.coaching {
        background: #0891b2;
    }

    .mp-trend-segment.teguran {
        background: #d69e00;
    }

    .mp-trend-segment.peringatan {
        background: #d71920;
    }

    .mp-trend-value {
        color: var(--mp-navy);
        font-size: 11px;
        font-weight: 900;
        text-align: right;
    }

    .mp-status-body {
        display: grid;
        gap: 16px;
        padding: 18px;
    }

    .mp-status-ring {
        position: relative;
        display: grid;
        width: 148px;
        height: 148px;
        place-items: center;
        margin: 0 auto;
        border-radius: 50%;
        background:
            conic-gradient(
                #169b5b 0
                calc(var(--active-percent) * 1%),
                #d71920
                calc(var(--active-percent) * 1%)
                100%
            );
    }

    .mp-status-ring::after {
        position: absolute;
        width: 106px;
        height: 106px;
        border-radius: 50%;
        background: #ffffff;
        content: "";
    }

    .mp-status-ring-content {
        position: relative;
        z-index: 1;
        text-align: center;
    }

    .mp-status-ring-content strong {
        display: block;
        color: var(--mp-navy);
        font-size: 26px;
        font-weight: 900;
    }

    .mp-status-ring-content span {
        color: var(--mp-muted);
        font-size: 10px;
        font-weight: 700;
    }

    .mp-status-list {
        display: grid;
        gap: 9px;
    }

    .mp-status-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 11px 12px;
        border: 1px solid #e3e7ec;
        border-radius: 9px;
        background: #fafbfc;
    }

    .mp-status-name {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #455064;
        font-size: 11px;
        font-weight: 800;
    }

    .mp-status-color {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #169b5b;
    }

    .mp-status-color.expired {
        background: #d71920;
    }

    .mp-status-number {
        color: var(--mp-navy);
        font-size: 15px;
        font-weight: 900;
    }

    .mp-activity-list {
        display: grid;
    }

    .mp-activity-item {
        display: grid;
        grid-template-columns: 38px minmax(0, 1fr) auto;
        gap: 11px;
        align-items: center;
        padding: 13px 18px;
        border-bottom: 1px solid #e8ebef;
        color: inherit;
        text-decoration: none;
    }

    .mp-activity-item:last-child {
        border-bottom: 0;
    }

    .mp-activity-item:hover {
        background: #fafbfc;
    }

    .mp-activity-icon {
        display: grid;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 11px;
        color: #ffffff;
        background: #273248;
        font-size: 12px;
        font-weight: 900;
    }

    .mp-activity-icon.green {
        background: #169b5b;
    }

    .mp-activity-icon.cyan {
        background: #0891b2;
    }

    .mp-activity-icon.yellow {
        background: #d69e00;
    }

    .mp-activity-icon.red {
        background: #d71920;
    }

    .mp-activity-icon.navy {
        background: #273248;
    }

    .mp-activity-main {
        min-width: 0;
    }

    .mp-activity-main strong {
        display: block;
        margin-bottom: 3px;
        overflow: hidden;
        color: var(--mp-navy);
        font-size: 11px;
        font-weight: 900;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .mp-activity-main span {
        display: block;
        overflow: hidden;
        color: var(--mp-muted);
        font-size: 10px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .mp-activity-date {
        color: #7b8492;
        font-size: 9px;
        font-weight: 700;
        white-space: nowrap;
    }

    .mp-dashboard-empty {
        padding: 30px 18px;
        color: var(--mp-muted);
        font-size: 11px;
        text-align: center;
    }

    .mp-quick-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        padding: 15px;
    }

    .mp-quick-link {
        display: flex;
        min-height: 47px;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 8px 10px;
        border: 1px solid #dce2e8;
        border-radius: 9px;
        color: #344054;
        background: #ffffff;
        font-size: 10px;
        font-weight: 900;
        text-decoration: none;
        text-align: center;
    }

    .mp-quick-link:hover {
        border-color: #aeb7c2;
        background: #f8fafc;
    }

    @media (max-width: 1150px) {
        .mp-dashboard-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .mp-feature-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .mp-dashboard-columns {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 720px) {
        .mp-dashboard-hero {
            grid-template-columns: 1fr;
        }

        .mp-dashboard-filter {
            width: 100%;
        }

        .mp-dashboard-filter-field {
            flex: 1;
        }

        .mp-dashboard-month {
            width: 100%;
            min-width: 0;
        }

        .mp-dashboard-summary,
        .mp-feature-grid,
        .mp-quick-grid {
            grid-template-columns: 1fr;
        }

        .mp-trend-row {
            grid-template-columns: 62px minmax(0, 1fr) 28px;
        }
    }
</style>

<div class="mp-dashboard">
    <section class="mp-dashboard-card mp-dashboard-hero">
        <div>
            <h1>
                Dashboard Manpower
            </h1>

            <p>
                Selamat datang,
                <strong>
                    {{ auth()->user()?->name ?? 'Pengguna' }}
                </strong>.
                Ringkasan ini mengambil data yang tersimpan
                pada seluruh modul yang sudah terhubung.
            </p>
        </div>

        <form
            method="GET"
            action="{{ route('manpower') }}"
            class="mp-dashboard-filter"
        >
            <div class="mp-dashboard-filter-field">
                <label for="dashboardMonth">
                    Periode dashboard
                </label>

                <input
                    type="month"
                    name="bulan"
                    id="dashboardMonth"
                    class="mp-dashboard-month"
                    value="{{ $bulan }}"
                    onchange="this.form.submit()"
                >
            </div>

            <a
                href="{{ route('manpower', ['bulan' => $bulan]) }}"
                class="mp-dashboard-refresh"
                title="Muat ulang data"
                aria-label="Muat ulang data"
            >
                ↻
            </a>
        </form>
    </section>

    <section class="mp-dashboard-summary">
        <article class="mp-dashboard-card mp-summary-card">
            <div class="mp-summary-head">
                <span class="mp-summary-label">
                    Total data tersimpan
                </span>
                <span class="mp-summary-icon">Σ</span>
            </div>

            <div class="mp-summary-value">
                {{ number_format($summary['total']) }}
            </div>

            <div class="mp-summary-caption">
                Gabungan data dari seluruh tabel yang terhubung.
            </div>
        </article>

        <article
            class="
                mp-dashboard-card
                mp-summary-card
                month
            "
        >
            <div class="mp-summary-head">
                <span class="mp-summary-label">
                    Data {{ $periodeLabel }}
                </span>
                <span class="mp-summary-icon">▦</span>
            </div>

            <div class="mp-summary-value">
                {{ number_format($summary['month']) }}
            </div>

            <div class="mp-summary-caption">
                Data dengan tanggal pada periode yang dipilih.
            </div>
        </article>

        <article
            class="
                mp-dashboard-card
                mp-summary-card
                active
            "
        >
            <div class="mp-summary-head">
                <span class="mp-summary-label">
                    ST/SP aktif
                </span>
                <span class="mp-summary-icon">✓</span>
            </div>

            <div class="mp-summary-value">
                {{ number_format($summary['active']) }}
            </div>

            <div class="mp-summary-caption">
                Masa berlaku surat belum berakhir.
            </div>
        </article>

        <article
            class="
                mp-dashboard-card
                mp-summary-card
                expired
            "
        >
            <div class="mp-summary-head">
                <span class="mp-summary-label">
                    ST/SP expired
                </span>
                <span class="mp-summary-icon">!</span>
            </div>

            <div class="mp-summary-value">
                {{ number_format($summary['expired']) }}
            </div>

            <div class="mp-summary-caption">
                Surat yang telah melewati expired date.
            </div>
        </article>
    </section>

    <section
        class="
            mp-dashboard-card
            mp-dashboard-section
        "
    >
        <div class="mp-section-header">
            <div>
                <h2>Seluruh fitur Manpower</h2>
                <p>
                    Angka utama menunjukkan total data yang tersimpan.
                </p>
            </div>

            <span class="mp-section-badge">
                Periode {{ $periodeLabel }}
            </span>
        </div>

        <div class="mp-feature-grid">
            @foreach ($features as $feature)
                <a
                    href="{{ $feature['url'] }}"
                    class="
                        mp-feature-card
                        {{ $feature['tone'] }}
                        {{
                            $feature['available']
                                ? ''
                                : 'unavailable'
                        }}
                    "
                    @if ($feature['external'] ?? false)
                        target="_blank"
                        rel="noopener noreferrer"
                    @endif
                >
                    <span class="mp-feature-icon">
                        {{ $feature['icon'] }}
                    </span>

                    <span class="mp-feature-content">
                        <span class="mp-feature-title">
                            {{ $feature['title'] }}
                        </span>

                        <span class="mp-feature-description">
                            {{ $feature['description'] }}
                        </span>

                        <span class="mp-feature-meta">
                            @if ($feature['available'])
                                <span class="mp-feature-pill">
                                    Bulan ini:
                                    {{ number_format($feature['month']) }}
                                </span>

                                <span class="mp-feature-pill">
                                    Terhubung
                                </span>
                            @else
                                <span class="mp-feature-pill">
                                    {{
                                        ($feature['external'] ?? false)
                                            ? 'Sumber eksternal'
                                            : 'Belum terhubung'
                                    }}
                                </span>
                            @endif
                        </span>
                    </span>

                    <span class="mp-feature-value">
                        {{
                            $feature['total'] === null
                                ? '—'
                                : number_format($feature['total'])
                        }}
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    <div class="mp-dashboard-columns">
        <section
            class="
                mp-dashboard-card
                mp-dashboard-section
            "
        >
            <div class="mp-section-header">
                <div>
                    <h2>Tren enam bulan</h2>
                    <p>
                        Document Out, Coaching, Teguran,
                        dan Surat Peringatan.
                    </p>
                </div>

                <span class="mp-section-badge">
                    Maksimum {{ number_format($maxTrend) }}
                </span>
            </div>

            <div class="mp-trend-body">
                <div class="mp-trend-legend">
                    <span class="mp-legend-item">
                        <i
                            class="
                                mp-legend-dot
                                document
                            "
                        ></i>
                        Document Out
                    </span>

                    <span class="mp-legend-item">
                        <i
                            class="
                                mp-legend-dot
                                coaching
                            "
                        ></i>
                        Coaching
                    </span>

                    <span class="mp-legend-item">
                        <i
                            class="
                                mp-legend-dot
                                teguran
                            "
                        ></i>
                        Teguran
                    </span>

                    <span class="mp-legend-item">
                        <i
                            class="
                                mp-legend-dot
                                peringatan
                            "
                        ></i>
                        Peringatan
                    </span>
                </div>

                <div class="mp-trend-list">
                    @foreach ($trend as $item)
                        @php
                            $documentWidth =
                                ($item['document_out'] / $maxTrend)
                                * 100;

                            $coachingWidth =
                                ($item['coaching'] / $maxTrend)
                                * 100;

                            $teguranWidth =
                                ($item['teguran'] / $maxTrend)
                                * 100;

                            $peringatanWidth =
                                ($item['peringatan'] / $maxTrend)
                                * 100;
                        @endphp

                        <div class="mp-trend-row">
                            <span class="mp-trend-label">
                                {{ $item['label'] }}
                            </span>

                            <div
                                class="mp-trend-track"
                                title="
                                    Document Out:
                                    {{ $item['document_out'] }},
                                    Coaching:
                                    {{ $item['coaching'] }},
                                    Teguran:
                                    {{ $item['teguran'] }},
                                    Peringatan:
                                    {{ $item['peringatan'] }}
                                "
                            >
                                <span
                                    class="
                                        mp-trend-segment
                                        document
                                    "
                                    style="
                                        width:
                                        {{ $documentWidth }}%
                                    "
                                ></span>

                                <span
                                    class="
                                        mp-trend-segment
                                        coaching
                                    "
                                    style="
                                        width:
                                        {{ $coachingWidth }}%
                                    "
                                ></span>

                                <span
                                    class="
                                        mp-trend-segment
                                        teguran
                                    "
                                    style="
                                        width:
                                        {{ $teguranWidth }}%
                                    "
                                ></span>

                                <span
                                    class="
                                        mp-trend-segment
                                        peringatan
                                    "
                                    style="
                                        width:
                                        {{ $peringatanWidth }}%
                                    "
                                ></span>
                            </div>

                            <span class="mp-trend-value">
                                {{ $item['total'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section
            class="
                mp-dashboard-card
                mp-dashboard-section
            "
        >
            <div class="mp-section-header">
                <div>
                    <h2>Status ST &amp; SP</h2>
                    <p>
                        Komposisi status seluruh surat.
                    </p>
                </div>
            </div>

            <div class="mp-status-body">
                <div
                    class="mp-status-ring"
                    style="
                        --active-percent:
                        {{ $activePercent }}
                    "
                >
                    <div class="mp-status-ring-content">
                        <strong>
                            {{ number_format($stSpStatus['total']) }}
                        </strong>
                        <span>Total surat</span>
                    </div>
                </div>

                <div class="mp-status-list">
                    <div class="mp-status-item">
                        <span class="mp-status-name">
                            <i class="mp-status-color"></i>
                            Aktif
                        </span>

                        <span class="mp-status-number">
                            {{
                                number_format(
                                    $stSpStatus['active']
                                )
                            }}
                        </span>
                    </div>

                    <div class="mp-status-item">
                        <span class="mp-status-name">
                            <i
                                class="
                                    mp-status-color
                                    expired
                                "
                            ></i>
                            Expired
                        </span>

                        <span class="mp-status-number">
                            {{
                                number_format(
                                    $stSpStatus['expired']
                                )
                            }}
                        </span>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <section
        class="
            mp-dashboard-card
            mp-dashboard-section
        "
    >
        <div class="mp-section-header">
            <div>
                <h2>Aktivitas terbaru</h2>
                <p>
                    Data terbaru dari Document Out,
                    Coaching, Teguran, dan Peringatan.
                </p>
            </div>

            <span class="mp-section-badge">
                Maksimal 10 aktivitas
            </span>
        </div>

        @forelse ($recentActivities as $activity)
            <a
                href="{{ $activity['url'] }}"
                class="mp-activity-item"
            >
                <span
                    class="
                        mp-activity-icon
                        {{ $activity['tone'] }}
                    "
                >
                    {{
                        match ($activity['tone']) {
                            'green' => 'APD',
                            'cyan' => 'CC',
                            'yellow' => 'ST',
                            'red' => 'SP',
                            default => 'DO',
                        }
                    }}
                </span>

                <span class="mp-activity-main">
                    <strong>
                        {{ $activity['module'] }}
                        ·
                        {{ $activity['title'] }}
                    </strong>

                    <span>
                        {{ $activity['description'] }}
                    </span>
                </span>

                <span class="mp-activity-date">
                    {{
                        \Carbon\Carbon::parse(
                            $activity['date']
                        )->translatedFormat('d M Y')
                    }}
                </span>
            </a>
        @empty
            <div class="mp-dashboard-empty">
                Belum ada aktivitas tersimpan pada modul
                yang sudah terhubung.
            </div>
        @endforelse
    </section>

    <section
        class="
            mp-dashboard-card
            mp-dashboard-section
        "
    >
        <div class="mp-section-header">
            <div>
                <h2>Akses cepat</h2>
                <p>
                    Buka halaman input dan monitoring utama.
                </p>
            </div>
        </div>

        <div class="mp-quick-grid">
            <a
                href="{{ route('document-out.index') }}"
                class="mp-quick-link"
            >
                ＋ Input Document Out
            </a>

            <a
                href="{{ route('cc-st-sp.coaching.index') }}"
                class="mp-quick-link"
            >
                ＋ Input Coaching
            </a>

            <a
                href="{{ route('cc-st-sp.teguran.index') }}"
                class="mp-quick-link"
            >
                ＋ Input Surat Teguran
            </a>

            <a
                href="{{
                    route(
                        'cc-st-sp.peringatan.index'
                    )
                }}"
                class="mp-quick-link"
            >
                ＋ Input Surat Peringatan
            </a>
        </div>
    </section>
</div>