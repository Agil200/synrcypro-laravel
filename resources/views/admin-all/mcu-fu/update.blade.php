@extends('admin-all.layout')

@section('title', 'Update MCU & Follow Up — SYNRGYPRO')

@push('styles')
<style>
    /*
    |--------------------------------------------------------------------------
    | FIXED SHELL — UPDATE MCU & FOLLOW UP
    |--------------------------------------------------------------------------
    | Header/sidebar/footer tetap milik layout.
    | Judul + filter + table header tetap.
    | HANYA body data tabel yang scroll.
    |--------------------------------------------------------------------------
    */

    .aa-main {
        overflow: hidden;
    }

    .aa-main > .aa-content {
        height: 100%;
        min-height: 0;
        overflow: hidden;
    }

    .mfu-page {
        display: flex;
        width: 100%;
        height: 100%;
        min-height: 0;
        flex-direction: column;
        gap: 8px;
        overflow: hidden;
    }

    .mfu-page > .aa-page-title,
    .mfu-page > .mfu-alert,
    .mfu-page > .mfu-filter-shell {
        flex: 0 0 auto;
    }

    .mfu-filter-shell {
        display: grid;
        grid-template-columns:
            190px
            110px
            130px
            150px
            minmax(220px, 1fr)
            70px
            66px
            auto;
        align-items: end;
        gap: 7px;
        padding: 8px;
        border: 1px solid #d7e0e8;
        border-radius: 9px;
        background: #fff;
        box-shadow: 0 3px 12px rgba(31, 47, 65, .04);
    }

    .mfu-field {
        display: grid;
        gap: 3px;
        min-width: 0;
    }

    .mfu-field label {
        color: #607285;
        font-size: 7px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .mfu-field input,
    .mfu-field select {
        width: 100%;
        min-width: 0;
        min-height: 31px;
        padding: 5px 8px;
        border: 1px solid #cbd6e1;
        border-radius: 6px;
        color: #17324a;
        background: #fff;
        font-size: 8px;
        outline: none;
    }

    .mfu-button {
        display: inline-flex;
        min-height: 31px;
        align-items: center;
        justify-content: center;
        padding: 6px 9px;
        border: 1px solid #cbd6e1;
        border-radius: 6px;
        color: #1d354c;
        background: #fff;
        font-size: 7px;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
        cursor: pointer;
    }

    .mfu-button.primary {
        border-color: #0f78ef;
        color: #fff;
        background: #0f78ef;
    }

    .mfu-filter-result {
        justify-self: end;
        align-self: center;
        color: #607285;
        font-size: 7px;
        font-weight: 900;
        white-space: nowrap;
    }

    .mfu-alert {
        padding: 8px 10px;
        border-radius: 7px;
        font-size: 8px;
        font-weight: 800;
    }

    .mfu-alert.success {
        border: 1px solid #a9e3c6;
        color: #12643b;
        background: #edfff5;
    }

    .mfu-alert.error {
        border: 1px solid #f0c2c5;
        color: #9b1c25;
        background: #fff1f2;
    }

    .mfu-table-toolbar {
        display: flex;
        flex: 0 0 auto;
        min-height: 38px;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 6px 8px;
        border-bottom: 1px solid #e1e7ec;
        background: #fff;
    }

    .mfu-rows-form {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #516579;
        font-size: 7px;
        font-weight: 800;
    }

    .mfu-rows-form select {
        min-width: 66px;
        min-height: 28px;
        padding: 4px 7px;
        border: 1px solid #cbd6e1;
        border-radius: 6px;
        color: #18344c;
        background: #fff;
        font-size: 8px;
        font-weight: 900;
        outline: none;
    }

    .mfu-auto-sync {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 6px;
        border-radius: 999px;
        color: #0b6a43;
        background: #eaf9f1;
        font-size: 6px;
        font-weight: 900;
        white-space: nowrap;
    }

    .mfu-auto-sync.paused {
        color: #9a5a0c;
        background: #fff4dd;
    }

    .mfu-auto-sync-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: currentColor;
    }

    .mfu-toolbar-count {
        color: #65768a;
        font-size: 7px;
        font-weight: 900;
        white-space: nowrap;
    }

    .mfu-status-mcu {
        display: inline-flex;
        width: max-content;
        min-width: 62px;
        align-items: center;
        justify-content: center;
        padding: 5px 8px;
        border-radius: 999px;
        font-size: 7px;
        font-weight: 900;
    }

    .mfu-status-mcu.done {
        color: #0c6c40;
        background: #e8f8ef;
        border: 1px solid #bce8cf;
    }

    .mfu-status-mcu.not-yet {
        color: #a32630;
        background: #ffecee;
        border: 1px solid #f1c8cc;
    }

    .mfu-dialog-person {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 5px;
        margin-top: 3px;
        color: #617286;
        font-size: 7px;
        font-weight: 800;
    }

    .mfu-dialog-person strong {
        color: #18344c;
        font-size: 8px;
    }

    .mfu-table-card {
        display: flex;
        min-height: 0;
        flex: 1 1 auto;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid #d9e0e7;
        border-radius: 9px;
        background: #fff;
    }

    .mfu-table-wrap {
        min-height: 0;
        flex: 1 1 auto;
        overflow: auto;
        scrollbar-gutter: stable;
        overscroll-behavior: contain;
    }

    .mfu-table {
        width: 100%;
        min-width: 1180px;
        border-collapse: collapse;
        font-size: 8px;
    }

    .mfu-table th {
        position: sticky;
        top: 0;
        z-index: 2;
        padding: 8px;
        border-bottom: 1px solid #d5dee7;
        color: #fff;
        background: #173b63;
        font-size: 7px;
        text-align: left;
        white-space: nowrap;
    }

    .mfu-table td {
        padding: 7px 8px;
        border-bottom: 1px solid #edf1f4;
        vertical-align: middle;
    }

    .mfu-table tbody tr:hover td {
        background: #f8fbfd;
    }

    .mfu-name strong {
        display: block;
        color: #122d45;
        font-size: 8px;
    }

    .mfu-name span {
        display: block;
        margin-top: 2px;
        color: #718297;
        font-size: 7px;
    }

    .mfu-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 6px;
        border-radius: 999px;
        color: #33536d;
        background: #eef4f8;
        font-size: 6.5px;
        font-weight: 900;
    }

    .mfu-expiry {
        display: grid;
        gap: 3px;
    }

    .mfu-expiry strong {
        color: #19364f;
        font-size: 8px;
    }

    .mfu-status {
        display: inline-flex;
        width: max-content;
        padding: 3px 6px;
        border-radius: 999px;
        font-size: 6px;
        font-weight: 900;
    }

    .mfu-status.safe {
        color: #117044;
        background: #e9f9f0;
    }

    .mfu-status.warning {
        color: #a05b08;
        background: #fff4dd;
    }

    .mfu-status.expired {
        color: #a52631;
        background: #ffe9eb;
    }

    .mfu-status.no-data {
        color: #64748b;
        background: #edf1f5;
    }

    .mfu-edit-btn {
        min-width: 72px;
        border: 0;
        border-radius: 6px;
        padding: 7px 9px;
        color: #fff;
        background: #0f78ef;
        font-size: 7px;
        font-weight: 900;
        cursor: pointer;
    }

    .mfu-pagination {
        flex: 0 0 auto;
        padding: 6px 8px;
        border-top: 1px solid #e1e7ec;
        background: #fff;
        font-size: 8px;
    }

    /*
     * Fix SVG pagination Laravel yang sebelumnya membesar memenuhi halaman.
     */
    .mfu-pagination svg {
        width: 14px !important;
        height: 14px !important;
    }

    .mfu-pagination nav > div:first-child {
        display: none;
    }

    .mfu-pagination nav > div:last-child {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .mfu-pagination nav span,
    .mfu-pagination nav a {
        font-size: 8px;
    }

    /*
    |--------------------------------------------------------------------------
    | Dialog / Unified Form
    |--------------------------------------------------------------------------
    */

    .mfu-dialog {
        width: min(900px, calc(100vw - 40px));
        max-height: calc(100vh - 42px);
        padding: 0;
        border: 0;
        border-radius: 12px;
        box-shadow: 0 22px 70px rgba(15, 32, 49, .28);
    }

    .mfu-dialog::backdrop {
        background: rgba(15, 30, 45, .58);
    }

    .mfu-dialog-shell {
        display: flex;
        max-height: calc(100vh - 42px);
        flex-direction: column;
        overflow: hidden;
        background: #f6f8fa;
    }

    .mfu-dialog-head {
        display: flex;
        flex: 0 0 auto;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        border-bottom: 1px solid #d8e0e7;
        background: #fff;
    }

    .mfu-dialog-head h2 {
        margin: 0;
        color: #102d47;
        font-size: 14px;
    }

    .mfu-dialog-head p {
        margin: 2px 0 0;
        color: #718196;
        font-size: 7px;
    }

    .mfu-close {
        width: 30px;
        height: 30px;
        border: 1px solid #d3dce5;
        border-radius: 7px;
        color: #475d71;
        background: #fff;
        font-size: 15px;
        cursor: pointer;
    }

    .mfu-dialog-body {
        display: grid;
        min-height: 0;
        flex: 1 1 auto;
        gap: 9px;
        padding: 10px;
        overflow-y: auto;
    }

    .mfu-identity {
        display: grid;
        grid-template-columns: 1.4fr .7fr 1fr;
        gap: 7px;
    }

    .mfu-info-box {
        padding: 8px 10px;
        border: 1px solid #d8e0e7;
        border-radius: 8px;
        background: #fff;
    }

    .mfu-info-box span {
        display: block;
        color: #78879a;
        font-size: 6px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .mfu-info-box strong {
        display: block;
        margin-top: 3px;
        color: #15324b;
        font-size: 9px;
    }

    .mfu-validity {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 7px;
    }

    .mfu-section {
        overflow: hidden;
        border: 1px solid #d8e0e7;
        border-radius: 9px;
        background: #fff;
    }

    .mfu-section-title {
        padding: 8px 10px;
        border-bottom: 1px solid #e1e7ec;
        color: #16334c;
        background: #f7fafc;
        font-size: 8px;
        font-weight: 900;
    }

    .mfu-section-title.mcu {
        color: #155ca4;
        background: #eef6ff;
    }

    .mfu-section-title.fu {
        color: #157050;
        background: #eefaf5;
    }

    .mfu-section-title.simper {
        color: #9a5a0c;
        background: #fff7e8;
    }

    .mfu-form-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
        padding: 9px;
    }

    .mfu-form-grid.five {
        grid-template-columns:
            repeat(3, minmax(0, 1fr));
    }

    .mfu-form-field {
        display: grid;
        gap: 4px;
    }

    .mfu-form-field label {
        color: #637386;
        font-size: 6px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .mfu-form-field input,
    .mfu-form-field select {
        width: 100%;
        min-width: 0;
        min-height: 34px;
        padding: 6px 8px;
        border: 1px solid #cdd7e1;
        border-radius: 6px;
        color: #18344c;
        background: #fff;
        font-size: 8px;
        outline: none;
    }

    .mfu-simper-note {
        padding: 0 9px 9px;
        color: #66788b;
        font-size: 7px;
        line-height: 1.45;
    }

    .mfu-simper-note strong {
        color: #a05b08;
    }

    .mfu-dialog-footer {
        display: flex;
        flex: 0 0 auto;
        align-items: center;
        justify-content: flex-end;
        gap: 7px;
        padding: 9px 12px;
        border-top: 1px solid #d8e0e7;
        background: #fff;
    }

    .mfu-readonly {
        display: flex;
        min-height: 34px;
        align-items: center;
        padding: 6px 8px;
        border: 1px solid #d7e0e8;
        border-radius: 6px;
        color: #314c63;
        background: #f5f8fa;
        font-size: 8px;
        font-weight: 900;
    }

    .mfu-save[disabled] {
        opacity: .45;
        cursor: not-allowed;
    }

    .mfu-change-count {
        display: none;
        padding: 4px 7px;
        border-radius: 999px;
        color: #0f5c9d;
        background: #eaf4ff;
        font-size: 7px;
        font-weight: 900;
    }

    .mfu-change-count.show {
        display: inline-flex;
    }

    .mfu-save {
        min-height: 33px;
        padding: 7px 14px;
        border: 0;
        border-radius: 7px;
        color: #fff;
        background: #0f78ef;
        font-size: 8px;
        font-weight: 900;
        cursor: pointer;
    }

    @media (max-width: 1100px) {
        .mfu-filter-shell {
            grid-template-columns:
                180px 105px 125px 145px
                minmax(180px, 1fr)
                68px 64px;
            overflow-x: auto;
        }

        .mfu-filter-result {
            display: none;
        }
    }

    @media (max-width: 700px) {
        .mfu-identity,
        .mfu-validity,
        .mfu-form-grid,
        .mfu-form-grid.five {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('admin-content')
@php
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

    $service = app(
        \App\Services\McuFuInternalService::class
    );
@endphp

<div class="mfu-page">

    <div class="aa-page-title">
        <div>
            <h1>Update MCU &amp; Follow Up</h1>
            <p>
                MCU, Follow Up, dan masa berlaku SIM/SIB DLT dalam satu halaman.
            </p>
        </div>

        <div class="aa-title-actions">
            <a
                href="{{ route('admin-all.mcu-fu.index') }}"
                class="mfu-button"
            >
                DASHBOARD
            </a>

            <a
                href="{{ route('admin-all.mcu-fu.history') }}"
                class="mfu-button"
            >
                RIWAYAT UPDATE
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mfu-alert success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mfu-alert error">
            {{ session('error') }}
        </div>
    @endif

    @if (!empty($error))
        <div class="mfu-alert error">
            {{ $error }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mfu-alert error">
            {{ $errors->first() }}
        </div>
    @endif

    <form
        method="GET"
        action="{{ route('admin-all.mcu-fu.update') }}"
        class="mfu-filter-shell"
    >
        <div class="mfu-field">
            <label for="dateType">
                Filter Berdasarkan
            </label>

            <select
                name="date_type"
                id="dateType"
            >
                <option
                    value="jadwal_mcu"
                    @selected(($filters['date_type'] ?? '') === 'jadwal_mcu')
                >
                    MCU — Jadwal MCU
                </option>

                <option
                    value="exp_mcu"
                    @selected(($filters['date_type'] ?? '') === 'exp_mcu')
                >
                    MCU — EXP MCU
                </option>

                <option
                    value="follow_up"
                    @selected(($filters['date_type'] ?? '') === 'follow_up')
                >
                    Follow Up — Jadwal FU
                </option>

                <option
                    value="simper"
                    @selected(($filters['date_type'] ?? '') === 'simper')
                >
                    EXP SIM / SIB DLT
                </option>
            </select>
        </div>

        <div class="mfu-field">
            <label for="filterYear">
                Tahun
            </label>

            <select
                name="year"
                id="filterYear"
            >
                <option value="">
                    Semua
                </option>

                @foreach ($years as $year)
                    <option
                        value="{{ $year }}"
                        @selected((int) ($filters['year'] ?? 0) === (int) $year)
                    >
                        {{ $year }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mfu-field">
            <label for="filterMonth">
                Bulan
            </label>

            <select
                name="month"
                id="filterMonth"
            >
                <option value="">
                    Semua Bulan
                </option>

                @foreach ($monthNames as $number => $label)
                    <option
                        value="{{ $number }}"
                        @selected((int) ($filters['month'] ?? 0) === $number)
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mfu-field">
            <label for="simperExp">
                SIM/SIB EXP
            </label>

            <select
                name="simper_exp"
                id="simperExp"
            >
                <option value="">
                    Semua Status
                </option>

                <option
                    value="H-40"
                    @selected(($filters['simper_exp'] ?? '') === 'H-40')
                >
                    H-40
                </option>

                <option
                    value="H-30"
                    @selected(($filters['simper_exp'] ?? '') === 'H-30')
                >
                    H-30
                </option>

                <option
                    value="H-14"
                    @selected(($filters['simper_exp'] ?? '') === 'H-14')
                >
                    H-14
                </option>

                <option
                    value="H-7"
                    @selected(($filters['simper_exp'] ?? '') === 'H-7')
                >
                    H-7
                </option>

                <option
                    value="EXPIRED"
                    @selected(($filters['simper_exp'] ?? '') === 'EXPIRED')
                >
                    EXPIRED
                </option>
            </select>
        </div>

        <div class="mfu-field">
            <label for="filterSearch">
                Nama / NRP
            </label>

            <input
                type="text"
                name="q"
                id="filterSearch"
                value="{{ request('q') }}"
                placeholder="Cari nama atau NRP..."
            >
        </div>

        <input
            type="hidden"
            name="per_page"
            value="{{ $perPage ?? 20 }}"
        >

        {{-- Preserve dashboard drill-down when user searches again --}}
        @foreach (
            [
                'hasil_mcu',
                'status_mcu',
                'status_fu',
                'jabatan',
                'fu_stage',
                'follow_up_value',
            ] as $hiddenFilter
        )
            @if (
                request($hiddenFilter) !== null &&
                request($hiddenFilter) !== ''
            )
                <input
                    type="hidden"
                    name="{{ $hiddenFilter }}"
                    value="{{ request($hiddenFilter) }}"
                >
            @endif
        @endforeach

        <button
            type="submit"
            class="mfu-button primary"
        >
            SEARCH
        </button>

        <a
            href="{{ route('admin-all.mcu-fu.update') }}"
            class="mfu-button"
        >
            RESET
        </a>

        <div class="mfu-filter-result">
            {{ number_format($data->total()) }} DATA
        </div>
    </form>

    <div class="mfu-table-card">

        <div class="mfu-table-toolbar">
            <form
                method="GET"
                action="{{ route('admin-all.mcu-fu.update') }}"
                class="mfu-rows-form"
            >
                @foreach (request()->except(['per_page', 'page']) as $queryName => $queryValue)
                    @if (!is_array($queryValue))
                        <input
                            type="hidden"
                            name="{{ $queryName }}"
                            value="{{ $queryValue }}"
                        >
                    @endif
                @endforeach

                <span>Show</span>

                <select
                    name="per_page"
                    onchange="this.form.submit()"
                    aria-label="Rows per page"
                >
                    @foreach ([20, 50, 100] as $size)
                        <option
                            value="{{ $size }}"
                            @selected((int) ($perPage ?? 20) === $size)
                        >
                            {{ $size }}
                        </option>
                    @endforeach
                </select>

                <span>rows</span>
            </form>

            <div
                style="display:flex;align-items:center;gap:6px;"
            >
                <span
                    class="mfu-auto-sync"
                    data-auto-sync-status
                    title="Auto sync 120 detik. Pause otomatis saat form edit dibuka atau ada perubahan belum disimpan."
                >
                    <span class="mfu-auto-sync-dot"></span>
                    <span data-auto-sync-label>AUTO SYNC 120s</span>
                </span>

                <div class="mfu-toolbar-count">
                    Showing
                    {{ number_format($data->firstItem() ?? 0) }}
                    –
                    {{ number_format($data->lastItem() ?? 0) }}
                    of
                    {{ number_format($data->total()) }}
                    data
                </div>
            </div>
        </div>

        <div class="mfu-table-wrap">
            <table class="mfu-table">
                <thead>
                    <tr>
                        <th>ROW</th>
                        <th>NRP / NAMA</th>
                        <th>JABATAN</th>
                        <th>EXP MCU</th>
                        <th>EXP SIM/SIB DLT</th>
                        <th>HASIL MCU</th>
                        <th>STATUS MCU</th>
                        <th>FOLLOW UP</th>
                        <th>STATUS FU</th>
                        <th>ACTION</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($data as $row)
                        @php
                            $mcuExpiry =
                                $service->expiryMeta(
                                    $row['exp_mcu']
                                );

                            $simperExpiry =
                                $service->expiryMeta(
                                    $row['expired_sim_dlt']
                                );

                            $mcuClass = match (
                                $mcuExpiry['status']
                            ) {
                                'SAFE' => 'safe',
                                'WARNING' => 'warning',
                                'EXPIRED' => 'expired',
                                default => 'no-data',
                            };

                            $simperClass = match (
                                $simperExpiry['status']
                            ) {
                                'SAFE' => 'safe',
                                'WARNING' => 'warning',
                                'EXPIRED' => 'expired',
                                default => 'no-data',
                            };

                            $missingSheetSimper =
                                $service->isMissingSimper(
                                    $row['expired_sim_dlt_sheet']
                                    ?? ''
                                );

                            $dialogId =
                                'mfu-dialog-' .
                                $row['sheet_row'];
                        @endphp

                        <tr>
                            <td>
                                <span class="mfu-badge">
                                    {{ $row['sheet_row'] }}
                                </span>
                            </td>

                            <td class="mfu-name">
                                <strong>
                                    {{ $row['nama'] ?: '-' }}
                                </strong>
                                <span>
                                    {{ $row['nrp'] ?: '-' }}
                                </span>
                            </td>

                            <td>
                                {{ $row['jabatan'] ?: '-' }}
                            </td>

                            <td>
                                <div class="mfu-expiry">
                                    <strong>
                                        {{ $mcuExpiry['date'] ?: '-' }}
                                    </strong>

                                    <span class="mfu-status {{ $mcuClass }}">
                                        {{ $mcuExpiry['label'] }}
                                    </span>
                                </div>
                            </td>

                            <td>
                                <div class="mfu-expiry">
                                    <strong>
                                        {{ $simperExpiry['date'] ?: '-' }}
                                    </strong>

                                    <span class="mfu-status {{ $simperClass }}">
                                        {{ $simperExpiry['label'] }}
                                    </span>

                                    <span class="mfu-badge">
                                        {{ $row['expired_sim_dlt_source'] ?? '-' }}
                                    </span>
                                </div>
                            </td>

                            <td>
                                <span class="mfu-badge">
                                    {{ $row['hasil_mcu'] ?: '-' }}
                                </span>
                            </td>

                            <td>
                                <span class="mfu-badge">
                                    {{ $row['status_mcu'] ?: '-' }}
                                </span>
                            </td>

                            <td>
                                {{ collect([
                                    $row['follow_up_1'],
                                    $row['follow_up_2'],
                                    $row['follow_up_3'],
                                ])->filter()->implode(' / ') ?: '-' }}
                            </td>

                            <td>
                                <span class="mfu-badge">
                                    {{ $row['status_fu'] ?: '-' }}
                                </span>
                            </td>

                            <td>
                                <button
                                    type="button"
                                    class="mfu-edit-btn"
                                    onclick="document.getElementById('{{ $dialogId }}').showModal()"
                                >
                                    EDIT DATA
                                </button>

                                <dialog
                                    id="{{ $dialogId }}"
                                    class="mfu-dialog"
                                >
                                    <form
                                        method="POST"
                                        action="{{ route('admin-all.mcu-fu.update.save', $row['sheet_row']) }}"
                                        class="mfu-dialog-shell"
                                    >
                                        @csrf
                                        @method('PUT')

                                        <input type="hidden" name="_return_date_type" value="{{ request('date_type') }}">
                                        <input type="hidden" name="_return_year" value="{{ request('year') }}">
                                        <input type="hidden" name="_return_month" value="{{ request('month') }}">
                                        <input type="hidden" name="_return_simper_exp" value="{{ request('simper_exp') }}">
                                        <input type="hidden" name="_return_q" value="{{ request('q') }}">
                                        <input type="hidden" name="_return_hasil_mcu" value="{{ request('hasil_mcu') }}">
                                        <input type="hidden" name="_return_status_mcu" value="{{ request('status_mcu') }}">
                                        <input type="hidden" name="_return_status_fu" value="{{ request('status_fu') }}">
                                        <input type="hidden" name="_return_jabatan" value="{{ request('jabatan') }}">
                                        <input type="hidden" name="_return_fu_stage" value="{{ request('fu_stage') }}">
                                        <input type="hidden" name="_return_follow_up_value" value="{{ request('follow_up_value') }}">
                                        <input type="hidden" name="_return_page" value="{{ request('page') }}">
                                        <input type="hidden" name="_return_per_page" value="{{ $perPage ?? 20 }}">

                                        <div class="mfu-dialog-head">
                                            <div>
                                                <h2>
                                                    Update MCU &amp; Follow Up
                                                </h2>

                                                <div class="mfu-dialog-person">
                                                    <strong>
                                                        {{ $row['nama'] ?: '-' }}
                                                    </strong>

                                                    <span>•</span>

                                                    <span>
                                                        NRP {{ $row['nrp'] ?: '-' }}
                                                    </span>

                                                    <span>•</span>

                                                    <span>
                                                        Row {{ $row['sheet_row'] }}
                                                    </span>
                                                </div>
                                            </div>

                                            <button
                                                type="button"
                                                class="mfu-close"
                                                onclick="document.getElementById('{{ $dialogId }}').close()"
                                            >
                                                ×
                                            </button>
                                        </div>

                                        <div class="mfu-dialog-body">

                                            <div class="mfu-identity">
                                                <div class="mfu-info-box">
                                                    <span>Nama</span>
                                                    <strong>
                                                        {{ $row['nama'] ?: '-' }}
                                                    </strong>
                                                </div>

                                                <div class="mfu-info-box">
                                                    <span>NRP</span>
                                                    <strong>
                                                        {{ $row['nrp'] ?: '-' }}
                                                    </strong>
                                                </div>

                                                <div class="mfu-info-box">
                                                    <span>Jabatan</span>
                                                    <strong>
                                                        {{ $row['jabatan'] ?: '-' }}
                                                    </strong>
                                                </div>
                                            </div>

                                            <div class="mfu-validity">
                                                <div class="mfu-info-box">
                                                    <span>EXP MCU</span>
                                                    <strong>
                                                        {{ $mcuExpiry['date'] ?: 'Belum ada data' }}
                                                    </strong>
                                                    <div style="margin-top:5px;">
                                                        <span class="mfu-status {{ $mcuClass }}">
                                                            {{ $mcuExpiry['label'] }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="mfu-info-box">
                                                    <span>EXP SIM/SIB DLT</span>
                                                    <strong>
                                                        {{ $simperExpiry['date'] ?: 'Belum ada data' }}
                                                    </strong>
                                                    <div style="display:flex;gap:5px;flex-wrap:wrap;margin-top:5px;">
                                                        <span class="mfu-status {{ $simperClass }}">
                                                            {{ $simperExpiry['label'] }}
                                                        </span>
                                                        <span class="mfu-badge">
                                                            {{ $row['expired_sim_dlt_source'] ?? '-' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mfu-section">
                                                <div class="mfu-section-title mcu">
                                                    MCU
                                                </div>

                                                <div class="mfu-form-grid">
                                                    <div class="mfu-form-field">
                                                        <label>EXP MCU</label>
                                                        <input
                                                            type="date"
                                                            name="exp_mcu"
                                                            value="{{ $service->htmlDate($row['exp_mcu']) }}"
                                                        >
                                                    </div>

                                                    <div class="mfu-form-field">
                                                        <label>JADWAL MCU</label>
                                                        <input
                                                            type="date"
                                                            name="jadwal_mcu"
                                                            value="{{ $service->htmlDate($row['jadwal_mcu']) }}"
                                                        >
                                                    </div>

                                                    <div class="mfu-form-field">
                                                        <label>HASIL MCU</label>
                                                        <select name="hasil_mcu">
                                                            <option value="">
                                                                -- HASIL MCU --
                                                            </option>

                                                            @if (
                                                                $row['hasil_mcu'] &&
                                                                !in_array(
                                                                    $row['hasil_mcu'],
                                                                    $options['hasil_mcu'],
                                                                    true
                                                                )
                                                            )
                                                                <option
                                                                    value="{{ $row['hasil_mcu'] }}"
                                                                    selected
                                                                >
                                                                    {{ $row['hasil_mcu'] }}
                                                                </option>
                                                            @endif

                                                            @foreach ($options['hasil_mcu'] as $option)
                                                                <option
                                                                    value="{{ $option }}"
                                                                    @selected($row['hasil_mcu'] === $option)
                                                                >
                                                                    {{ $option }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="mfu-form-field">
                                                        <label>STATUS MCU</label>

                                                        <div class="mfu-readonly">
                                                            <span
                                                                class="mfu-status-mcu {{ strtoupper(trim((string) $row['status_mcu'])) === 'DONE' ? 'done' : 'not-yet' }}"
                                                            >
                                                                {{ $row['status_mcu'] ?: '-' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mfu-section">
                                                <div class="mfu-section-title fu">
                                                    FOLLOW UP
                                                </div>

                                                <div class="mfu-form-grid five">
                                                    @foreach (
                                                        [
                                                            'follow_up_1' => 'FOLLOW UP 1',
                                                            'follow_up_2' => 'FOLLOW UP 2',
                                                            'follow_up_3' => 'FOLLOW UP 3',
                                                        ] as $field => $label
                                                    )
                                                        <div class="mfu-form-field">
                                                            <label>{{ $label }}</label>

                                                            <select name="{{ $field }}">
                                                                <option value="">
                                                                    -- {{ $label }} --
                                                                </option>

                                                                @if (
                                                                    $row[$field] &&
                                                                    !in_array(
                                                                        $row[$field],
                                                                        $options['follow_up'],
                                                                        true
                                                                    )
                                                                )
                                                                    <option
                                                                        value="{{ $row[$field] }}"
                                                                        selected
                                                                    >
                                                                        {{ $row[$field] }}
                                                                    </option>
                                                                @endif

                                                                @foreach ($options['follow_up'] as $option)
                                                                    <option
                                                                        value="{{ $option }}"
                                                                        @selected($row[$field] === $option)
                                                                    >
                                                                        {{ $option }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    @endforeach

                                                    <div class="mfu-form-field">
                                                        <label>JADWAL FU</label>
                                                        <input
                                                            type="date"
                                                            name="jadwal_fu"
                                                            value="{{ $service->htmlDate($row['jadwal_fu']) }}"
                                                        >
                                                    </div>

                                                    <div class="mfu-form-field">
                                                        <label>STATUS FU</label>

                                                        <select name="status_fu">
                                                            <option value="">
                                                                -- STATUS FU --
                                                            </option>

                                                            @if (
                                                                $row['status_fu'] &&
                                                                !in_array(
                                                                    $row['status_fu'],
                                                                    $options['status_fu'],
                                                                    true
                                                                )
                                                            )
                                                                <option
                                                                    value="{{ $row['status_fu'] }}"
                                                                    selected
                                                                >
                                                                    {{ $row['status_fu'] }}
                                                                </option>
                                                            @endif

                                                            @foreach ($options['status_fu'] as $option)
                                                                <option
                                                                    value="{{ $option }}"
                                                                    @selected($row['status_fu'] === $option)
                                                                >
                                                                    {{ $option }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mfu-section">
                                                <div class="mfu-section-title simper">
                                                    EXP SIM / SIB DLT
                                                </div>

                                                @if ($missingSheetSimper)
                                                    <div class="mfu-form-grid">
                                                        <div class="mfu-form-field">
                                                            <label>
                                                                EXP SIM/SIB DLT MANUAL
                                                            </label>

                                                            <input
                                                                type="date"
                                                                name="manual_expired_sim_dlt"
                                                                value="{{ $service->htmlDate($row['expired_sim_dlt_manual'] ?? '') }}"
                                                            >
                                                        </div>

                                                        <div
                                                            class="mfu-form-field"
                                                            style="grid-column: span 2;"
                                                        >
                                                            <label>
                                                                Catatan Manual
                                                            </label>

                                                            <input
                                                                type="text"
                                                                name="manual_simper_note"
                                                                placeholder="Contoh: belum tersedia pada database SHE"
                                                            >
                                                        </div>
                                                    </div>

                                                    <div class="mfu-simper-note">
                                                        <strong>
                                                            Data Spreadsheet belum tersedia.
                                                        </strong>
                                                        Input manual disimpan di database SYNRGYPRO sebagai fallback.
                                                        Website tidak menimpa formula kolom E MCU&amp;FU.
                                                    </div>
                                                @else
                                                    <div class="mfu-form-grid">
                                                        <div class="mfu-info-box">
                                                            <span>Data Spreadsheet</span>
                                                            <strong>
                                                                {{ $row['expired_sim_dlt_sheet'] ?: '-' }}
                                                            </strong>
                                                        </div>

                                                        <div
                                                            class="mfu-info-box"
                                                            style="grid-column: span 2;"
                                                        >
                                                            <span>Sumber</span>
                                                            <strong>
                                                                Otomatis dari Spreadsheet / Database SIM-SIB DLT
                                                            </strong>
                                                        </div>
                                                    </div>

                                                    <div class="mfu-simper-note">
                                                        EXP SIM/SIB DLT sudah tersedia dari Spreadsheet sehingga
                                                        field ini dibuat read only. Manual hanya dipakai jika sumber
                                                        Spreadsheet kosong.
                                                    </div>
                                                @endif
                                            </div>

                                        </div>

                                        <div class="mfu-dialog-footer">
                                            <span
                                                class="mfu-change-count"
                                                data-change-counter
                                            >
                                                0 PERUBAHAN
                                            </span>

                                            <button
                                                type="button"
                                                class="mfu-button"
                                                onclick="document.getElementById('{{ $dialogId }}').close()"
                                            >
                                                BATAL
                                            </button>

                                            <button
                                                type="submit"
                                                class="mfu-save"
                                                data-save-button
                                                data-employee-name="{{ $row['nama'] }}"
                                                disabled
                                            >
                                                SIMPAN UPDATE
                                            </button>
                                        </div>
                                    </form>
                                </dialog>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="10"
                                style="padding:26px;text-align:center;color:#718195;"
                            >
                                Data tidak ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mfu-pagination">
            <div
                style="
                    display:flex;
                    align-items:center;
                    justify-content:space-between;
                    gap:10px;
                "
            >
                <div>
                    Showing
                    {{ number_format($data->firstItem() ?? 0) }}
                    to
                    {{ number_format($data->lastItem() ?? 0) }}
                    of
                    {{ number_format($data->total()) }}
                    results
                </div>

                <div
                    style="
                        display:flex;
                        align-items:center;
                        gap:6px;
                    "
                >
                    @if ($data->onFirstPage())
                        <span
                            class="mfu-button"
                            style="opacity:.45;pointer-events:none;"
                        >
                            ‹ PREVIOUS
                        </span>
                    @else
                        <a
                            href="{{ $data->previousPageUrl() }}"
                            class="mfu-button"
                        >
                            ‹ PREVIOUS
                        </a>
                    @endif

                    <span class="mfu-badge">
                        PAGE {{ $data->currentPage() }}
                        / {{ max(1, $data->lastPage()) }}
                    </span>

                    @if ($data->hasMorePages())
                        <a
                            href="{{ $data->nextPageUrl() }}"
                            class="mfu-button"
                        >
                            NEXT ›
                        </a>
                    @else
                        <span
                            class="mfu-button"
                            style="opacity:.45;pointer-events:none;"
                        >
                            NEXT ›
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.mfu-dialog form').forEach(function (form) {
        const saveButton = form.querySelector('[data-save-button]');
        const counter = form.querySelector('[data-change-counter]');

        if (!saveButton || !counter) {
            return;
        }

        const tracked = Array.from(
            form.querySelectorAll(
                'input[name]:not([type="hidden"]), select[name], textarea[name]'
            )
        );

        tracked.forEach(function (field) {
            field.dataset.initialValue = field.value ?? '';
        });

        const refreshChanges = function () {
            const changed = tracked.filter(function (field) {
                return (field.value ?? '') !== (field.dataset.initialValue ?? '');
            });

            const count = changed.length;

            saveButton.disabled = count === 0;
            counter.textContent = count + ' PERUBAHAN';
            counter.classList.toggle('show', count > 0);

            saveButton.textContent = count > 0
                ? 'SIMPAN ' + count + ' PERUBAHAN'
                : 'SIMPAN UPDATE';

            return changed;
        };

        tracked.forEach(function (field) {
            field.addEventListener('input', refreshChanges);
            field.addEventListener('change', refreshChanges);
        });

        form.addEventListener('submit', function (event) {
            const changed = refreshChanges();

            if (changed.length === 0) {
                event.preventDefault();
                return;
            }

            const labels = changed.map(function (field) {
                const wrapper = field.closest('.mfu-form-field');
                const label = wrapper?.querySelector('label');

                return label
                    ? label.textContent.trim()
                    : field.name;
            });

            const employee = saveButton.dataset.employeeName || 'karyawan';

            const message =
                'Simpan ' +
                changed.length +
                ' perubahan untuk ' +
                employee +
                '?\n\n' +
                labels.join(', ');

            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });

        refreshChanges();
    });

    const autoSyncIntervalMs = 120 * 1000;
    const status = document.querySelector('[data-auto-sync-status]');
    const statusLabel = document.querySelector('[data-auto-sync-label]');
    let lastAutoSyncAt = Date.now();

    const hasOpenDialog = function () {
        return !!document.querySelector('.mfu-dialog[open]');
    };

    const hasUnsavedChanges = function () {
        return Array.from(
            document.querySelectorAll('[data-save-button]')
        ).some(function (button) {
            return !button.disabled;
        });
    };

    const userIsInteracting = function () {
        const active = document.activeElement;

        return !!active && active.matches(
            'input, select, textarea'
        );
    };

    const refreshAutoSyncBadge = function () {
        const paused =
            hasOpenDialog() ||
            hasUnsavedChanges();

        if (!status || !statusLabel) {
            return;
        }

        status.classList.toggle(
            'paused',
            paused
        );

        statusLabel.textContent = paused
            ? 'AUTO SYNC PAUSED'
            : 'AUTO SYNC 120s';
    };

    const safeAutoSync = function () {
        refreshAutoSyncBadge();

        if (
            document.hidden ||
            hasOpenDialog() ||
            hasUnsavedChanges() ||
            userIsInteracting()
        ) {
            return;
        }

        lastAutoSyncAt = Date.now();
        window.location.reload();
    };

    window.setInterval(
        safeAutoSync,
        autoSyncIntervalMs
    );

    document.addEventListener(
        'visibilitychange',
        function () {
            refreshAutoSyncBadge();

            if (
                !document.hidden &&
                (Date.now() - lastAutoSyncAt) >= autoSyncIntervalMs
            ) {
                window.setTimeout(
                    safeAutoSync,
                    350
                );
            }
        }
    );

    document.addEventListener(
        'input',
        refreshAutoSyncBadge
    );

    document.addEventListener(
        'change',
        refreshAutoSyncBadge
    );

    document.querySelectorAll('.mfu-edit-btn').forEach(function (button) {
        button.addEventListener(
            'click',
            function () {
                window.setTimeout(
                    refreshAutoSyncBadge,
                    50
                );
            }
        );
    });

    document.querySelectorAll('.mfu-dialog').forEach(function (dialog) {
        dialog.addEventListener(
            'close',
            refreshAutoSyncBadge
        );
    });

    refreshAutoSyncBadge();
});
</script>

@endsection