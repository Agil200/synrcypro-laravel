@extends('admin-all.layout')

@section('title', 'Prioritas & Reminder MCU & FU — SYNRGYPRO')

@push('styles')
<style>
    /*
    |--------------------------------------------------------------------------
    | PRIORITY PAGE — FIXED SHELL
    |--------------------------------------------------------------------------
    | Header / sidebar / footer tetap diam.
    | Scroll hanya terjadi pada area tabel.
    */

    .aa-main {
        overflow: hidden !important;
    }

    .aa-content {
        height: 100%;
        min-height: 0;
    }

    .mpr-page {
        display: flex;
        width: 100%;
        height: 100%;
        max-height: 100%;
        min-height: 0;
        flex-direction: column;
        gap: 8px;
        overflow: hidden;
    }

    .mpr-filter {
        display: grid;
        flex: 0 0 auto;
        grid-template-columns:
            170px
            150px
            minmax(220px, 1fr)
            70px
            64px;
        gap: 7px;
        align-items: end;
        padding: 8px;
        border: 1px solid #d7e0e8;
        border-radius: 9px;
        background: #fff;
    }

    .mpr-field {
        display: grid;
        gap: 3px;
    }

    .mpr-field label {
        color: #607285;
        font-size: 7px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .mpr-field input,
    .mpr-field select {
        min-height: 31px;
        width: 100%;
        padding: 5px 8px;
        border: 1px solid #cbd6e1;
        border-radius: 6px;
        color: #17324a;
        background: #fff;
        font-size: 8px;
        outline: none;
    }

    .mpr-btn {
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
    }

    .mpr-btn.primary {
        border-color: #0f78ef;
        color: #fff;
        background: #0f78ef;
    }

    .mpr-kpis {
        display: grid;
        flex: 0 0 auto;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 7px;
    }

    .mpr-kpi {
        position: relative;
        min-width: 0;
        min-height: 68px;
        padding: 10px 11px;
        overflow: hidden;
        border: 1px solid #d9e0e7;
        border-radius: 9px;
        background: #fff;
    }

    .mpr-kpi::before {
        position: absolute;
        inset: 0 auto 0 0;
        width: 3px;
        background: var(--tone, #173b63);
        content: '';
    }

    .mpr-kpi span {
        color: #617285;
        font-size: 6px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .mpr-kpi strong {
        display: block;
        margin-top: 4px;
        color: #102d47;
        font-size: 22px;
        line-height: 1;
    }

    .mpr-kpi small {
        display: block;
        margin-top: 4px;
        color: #7a8998;
        font-size: 6px;
    }

    .mpr-table-card {
        display: flex;
        min-height: 0;
        flex: 1 1 auto;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid #d9e0e7;
        border-radius: 9px;
        background: #fff;
    }

    .mpr-toolbar {
        display: flex;
        min-height: 36px;
        flex: 0 0 auto;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 6px 8px;
        border-bottom: 1px solid #e1e7ec;
    }

    .mpr-rows {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #516579;
        font-size: 7px;
        font-weight: 800;
    }

    .mpr-rows select {
        min-width: 64px;
        min-height: 28px;
        padding: 4px 7px;
        border: 1px solid #cbd6e1;
        border-radius: 6px;
        background: #fff;
        font-size: 8px;
        font-weight: 900;
    }

    .mpr-sync {
        padding: 3px 7px;
        border-radius: 999px;
        color: #0b6a43;
        background: #eaf9f1;
        font-size: 6px;
        font-weight: 900;
    }

    .mpr-table-wrap {
        min-height: 0;
        flex: 1 1 auto;
        overflow: auto;
        scrollbar-gutter: stable;
    }

    .mpr-table {
        width: 100%;
        min-width: 1040px;
        border-collapse: collapse;
        font-size: 8px;
    }

    .mpr-table th {
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

    .mpr-table td {
        padding: 7px 8px;
        border-bottom: 1px solid #edf1f4;
        vertical-align: middle;
    }

    .mpr-table tbody tr:hover td {
        background: #f8fbfd;
    }

    .mpr-name strong {
        display: block;
        color: #122d45;
        font-size: 8px;
    }

    .mpr-name span {
        display: block;
        margin-top: 2px;
        color: #718297;
        font-size: 7px;
    }

    .mpr-badge {
        display: inline-flex;
        width: max-content;
        align-items: center;
        padding: 3px 7px;
        border-radius: 999px;
        font-size: 6px;
        font-weight: 900;
    }

    .mpr-badge.expired,
    .mpr-badge.overdue {
        color: #a52631;
        background: #ffe9eb;
    }

    .mpr-badge.h7 {
        color: #9c3009;
        background: #fff0e9;
    }

    .mpr-badge.h14 {
        color: #9b6205;
        background: #fff4d8;
    }

    .mpr-badge.h30 {
        color: #7a6710;
        background: #fff9df;
    }

    .mpr-badge.h40 {
        color: #155ca4;
        background: #eaf4ff;
    }

    .mpr-badge.pending {
        color: #6b4aac;
        background: #f0eaff;
    }

    .mpr-type {
        font-weight: 900;
        color: #173b63;
    }

    .mpr-footer {
        display: flex;
        flex: 0 0 auto;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 6px 8px;
        border-top: 1px solid #e1e7ec;
        font-size: 7px;
    }

    .mpr-pages {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    @media (max-width: 1100px) {
        .mpr-kpis {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
</style>
@endpush

@section('admin-content')
<div class="mpr-page">

    <div class="aa-page-title">
        <div>
            <h1>Prioritas &amp; Reminder</h1>
            <p>Worklist MCU, SIM/SIB DLT, dan Follow Up yang perlu ditindaklanjuti.</p>
        </div>

        <div class="aa-title-actions">
            <a
                href="{{ route('admin-all.mcu-fu.update') }}"
                class="aa-action-button primary"
            >
                UPDATE MCU &amp; FOLLOW UP
            </a>

            <a
                href="{{ route('admin-all.mcu-fu.history') }}"
                class="aa-action-button"
            >
                RIWAYAT UPDATE
            </a>
        </div>
    </div>

    @if (!empty($error))
        <div
            style="
                padding:8px 10px;
                border:1px solid #f0c2c5;
                border-radius:7px;
                color:#9b1c25;
                background:#fff1f2;
                font-size:8px;
                font-weight:800;
            "
        >
            {{ $error }}
        </div>
    @endif

    <form
        method="GET"
        action="{{ route('admin-all.mcu-fu.priority') }}"
        class="mpr-filter"
    >
        <div class="mpr-field">
            <label>Jenis Prioritas</label>
            <select name="type">
                <option value="all" @selected(($filters['type'] ?? 'all') === 'all')>
                    Semua
                </option>
                <option value="mcu" @selected(($filters['type'] ?? '') === 'mcu')>
                    MCU
                </option>
                <option value="simper" @selected(($filters['type'] ?? '') === 'simper')>
                    SIM / SIB DLT
                </option>
                <option value="follow_up" @selected(($filters['type'] ?? '') === 'follow_up')>
                    Follow Up
                </option>
            </select>
        </div>

        <div class="mpr-field">
            <label>Status / Reminder</label>
            <select name="bucket">
                <option value="">Semua Status</option>
                @foreach (
                    [
                        'EXPIRED' => 'EXPIRED',
                        'OVERDUE' => 'FOLLOW UP OVERDUE',
                        'H-7' => 'H-7',
                        'H-14' => 'H-14',
                        'H-30' => 'H-30',
                        'H-40' => 'H-40',
                        'PENDING' => 'FOLLOW UP PENDING',
                    ] as $value => $label
                )
                    <option
                        value="{{ $value }}"
                        @selected(($filters['bucket'] ?? '') === $value)
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mpr-field">
            <label>Nama / NRP / Jabatan</label>
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Cari nama, NRP, atau jabatan..."
            >
        </div>

        <input
            type="hidden"
            name="per_page"
            value="{{ $perPage ?? 20 }}"
        >

        <button
            type="submit"
            class="mpr-btn primary"
        >
            SEARCH
        </button>

        <a
            href="{{ route('admin-all.mcu-fu.priority') }}"
            class="mpr-btn"
        >
            RESET
        </a>
    </form>

    <div class="mpr-kpis">
        <a
            href="{{ route('admin-all.mcu-fu.priority') }}"
            class="mpr-kpi"
            style="--tone:#173b63;text-decoration:none;"
        >
            <span>Total Prioritas</span>
            <strong>{{ number_format($summary['total'] ?? 0) }}</strong>
            <small>Seluruh worklist aktif</small>
        </a>

        <a
            href="{{ route('admin-all.mcu-fu.priority', ['bucket' => 'EXPIRED']) }}"
            class="mpr-kpi"
            style="--tone:#d62f3c;text-decoration:none;"
        >
            <span>Expired</span>
            <strong>{{ number_format($summary['expired'] ?? 0) }}</strong>
            <small>MCU / SIM-SIB expired</small>
        </a>

        <a
            href="{{ route('admin-all.mcu-fu.priority', ['bucket' => 'OVERDUE', 'type' => 'follow_up']) }}"
            class="mpr-kpi"
            style="--tone:#c93c48;text-decoration:none;"
        >
            <span>FU Overdue</span>
            <strong>{{ number_format($summary['overdue_fu'] ?? 0) }}</strong>
            <small>Jadwal FU terlewat</small>
        </a>

        <a
            href="{{ route('admin-all.mcu-fu.priority', ['bucket' => 'H-7']) }}"
            class="mpr-kpi"
            style="--tone:#f06425;text-decoration:none;"
        >
            <span>H-7</span>
            <strong>{{ number_format($summary['h7'] ?? 0) }}</strong>
            <small>Prioritas sangat dekat</small>
        </a>

        <a
            href="{{ route('admin-all.mcu-fu.priority', ['bucket' => 'H-14']) }}"
            class="mpr-kpi"
            style="--tone:#e6a00d;text-decoration:none;"
        >
            <span>H-14</span>
            <strong>{{ number_format($summary['h14'] ?? 0) }}</strong>
            <small>Perlu persiapan</small>
        </a>

        <a
            href="{{ route('admin-all.mcu-fu.priority', ['bucket' => 'H-30']) }}"
            class="mpr-kpi"
            style="--tone:#d4b222;text-decoration:none;"
        >
            <span>H-30</span>
            <strong>{{ number_format($summary['h30'] ?? 0) }}</strong>
            <small>Reminder awal</small>
        </a>
    </div>

    <div class="mpr-table-card">

        <div class="mpr-toolbar">
            <form
                method="GET"
                action="{{ route('admin-all.mcu-fu.priority') }}"
                class="mpr-rows"
            >
                @foreach (request()->except(['per_page', 'page']) as $name => $value)
                    @if (!is_array($value))
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endif
                @endforeach

                <span>Show</span>

                <select
                    name="per_page"
                    onchange="this.form.submit()"
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

            <span
                class="mpr-sync"
                data-priority-sync-state
                hidden
                aria-live="polite"
            ></span>
        </div>

        <div class="mpr-table-wrap">
            <table class="mpr-table">
                <thead>
                    <tr>
                        <th>NRP / NAMA</th>
                        <th>JABATAN</th>
                        <th>JENIS</th>
                        <th>DEADLINE</th>
                        <th>REMINDER</th>
                        <th>DETAIL</th>
                        <th>STATUS</th>
                        <th>ACTION</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($data as $task)
                        @php
                            $bucketClass = strtolower(
                                str_replace(
                                    '-',
                                    '',
                                    $task['bucket']
                                )
                            );

                            $updateUrl = route(
                                'admin-all.mcu-fu.update',
                                [
                                    'date_type' => $task['update_date_type'],
                                    'year' => '',
                                    'q' => $task['nrp'],
                                ]
                            );
                        @endphp

                        <tr>
                            <td class="mpr-name">
                                <strong>{{ $task['nama'] ?: '-' }}</strong>
                                <span>{{ $task['nrp'] ?: '-' }}</span>
                            </td>

                            <td>{{ $task['jabatan'] ?: '-' }}</td>

                            <td>
                                <span class="mpr-type">
                                    {{ $task['type'] }}
                                </span>
                            </td>

                            <td>
                                {{ $task['deadline_label'] ?: '-' }}
                            </td>

                            <td>
                                <span class="mpr-badge {{ $bucketClass }}">
                                    {{ $task['bucket'] }}
                                </span>
                                <div
                                    style="
                                        margin-top:3px;
                                        color:#718195;
                                        font-size:6px;
                                    "
                                >
                                    {{ $task['days_label'] ?: '-' }}
                                </div>
                            </td>

                            <td>
                                {{ $task['detail'] ?: '-' }}

                                @if (!empty($task['source']))
                                    <div
                                        style="
                                            margin-top:3px;
                                            color:#718195;
                                            font-size:6px;
                                        "
                                    >
                                        {{ $task['source'] }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                {{ $task['status_fu'] ?: '-' }}
                            </td>

                            <td>
                                <a
                                    href="{{ $updateUrl }}"
                                    class="mpr-btn primary"
                                >
                                    BUKA DATA
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="8"
                                style="
                                    padding:28px;
                                    text-align:center;
                                    color:#718195;
                                "
                            >
                                Tidak ada prioritas pada filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mpr-footer">
            <div>
                Showing
                {{ number_format($data->firstItem() ?? 0) }}
                to
                {{ number_format($data->lastItem() ?? 0) }}
                of
                {{ number_format($data->total()) }}
                results
            </div>

            <div class="mpr-pages">
                @if ($data->onFirstPage())
                    <span class="mpr-btn" style="opacity:.45;">‹ PREVIOUS</span>
                @else
                    <a href="{{ $data->previousPageUrl() }}" class="mpr-btn">
                        ‹ PREVIOUS
                    </a>
                @endif

                <span class="mpr-badge h40">
                    PAGE {{ $data->currentPage() }}
                    / {{ max(1, $data->lastPage()) }}
                </span>

                @if ($data->hasMorePages())
                    <a href="{{ $data->nextPageUrl() }}" class="mpr-btn">
                        NEXT ›
                    </a>
                @else
                    <span class="mpr-btn" style="opacity:.45;">NEXT ›</span>
                @endif
            </div>
        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    /*
    |--------------------------------------------------------------------------
    | SILENT AUTO SYNC
    |--------------------------------------------------------------------------
    | Tidak lagi memakai window.location.reload().
    | Laravel tetap dibaca setiap 60 detik, tetapi browser hanya mengganti
    | KPI + tabel di background sehingga shell SYNRGYPRO tidak berkedip.
    */

    const intervalMs = 60 * 1000;

    let lastSyncAt = Date.now();
    let syncing = false;

    const userIsInteracting = function () {
        const active = document.activeElement;

        if (
            active &&
            active.matches(
                'input, select, textarea, button'
            )
        ) {
            return true;
        }

        const selection =
            window.getSelection?.();

        return !!selection &&
            String(selection).trim() !== '';
    };

    const setSyncState = function (message) {
        const state =
            document.querySelector(
                '[data-priority-sync-state]'
            );

        if (state) {
            state.textContent = message;
        }
    };

    const syncFragments = async function () {
        if (
            syncing ||
            document.hidden ||
            userIsInteracting()
        ) {
            return;
        }

        syncing = true;
        setSyncState('SYNCING');

        const currentTableWrap =
            document.querySelector(
                '.mpr-table-wrap'
            );

        const tableScrollTop =
            currentTableWrap?.scrollTop || 0;

        const tableScrollLeft =
            currentTableWrap?.scrollLeft || 0;

        try {
            const response = await fetch(
                window.location.href,
                {
                    method: 'GET',
                    headers: {
                        'Accept': 'text/html',
                        'X-Requested-With':
                            'XMLHttpRequest',
                    },
                    cache: 'no-store',
                    credentials: 'same-origin',
                }
            );

            if (!response.ok) {
                throw new Error(
                    'Priority sync failed.'
                );
            }

            const html =
                await response.text();

            const parser =
                new DOMParser();

            const nextDocument =
                parser.parseFromString(
                    html,
                    'text/html'
                );

            const currentKpis =
                document.querySelector(
                    '.mpr-kpis'
                );

            const nextKpis =
                nextDocument.querySelector(
                    '.mpr-kpis'
                );

            const currentTableCard =
                document.querySelector(
                    '.mpr-table-card'
                );

            const nextTableCard =
                nextDocument.querySelector(
                    '.mpr-table-card'
                );

            if (
                currentKpis &&
                nextKpis
            ) {
                currentKpis.innerHTML =
                    nextKpis.innerHTML;
            }

            if (
                currentTableCard &&
                nextTableCard
            ) {
                currentTableCard.innerHTML =
                    nextTableCard.innerHTML;
            }

            const refreshedTableWrap =
                document.querySelector(
                    '.mpr-table-wrap'
                );

            if (refreshedTableWrap) {
                refreshedTableWrap.scrollTop =
                    tableScrollTop;

                refreshedTableWrap.scrollLeft =
                    tableScrollLeft;
            }

            lastSyncAt = Date.now();
            setSyncState('SYNCED');
        } catch (error) {
            /*
             * Silent failure:
             * jangan merusak data yang sedang tampil.
             * Resilient cache backend tetap menjadi fallback.
             */
            console.warn(
                '[MCU/FU Priority] silent sync skipped:',
                error
            );

            setSyncState('SYNC ERROR');
        } finally {
            syncing = false;
        }
    };

    window.setInterval(
        syncFragments,
        intervalMs
    );

    document.addEventListener(
        'visibilitychange',
        function () {
            if (
                !document.hidden &&
                (Date.now() - lastSyncAt) >= intervalMs
            ) {
                window.setTimeout(
                    syncFragments,
                    500
                );
            }
        }
    );
});
</script>
@endsection