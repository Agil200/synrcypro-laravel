<style>
    .she-page {
        min-width: 0;
        color: #111827;
        font-family: Arial, Helvetica, sans-serif;
    }

    .she-page-title {
        margin: 0 0 8px;
        font-size: 25px;
        font-weight: 800;
        line-height: 1.1;
    }

    /*
     * Panel memenuhi tinggi layar.
     * Hanya isi tabel yang memiliki scroll.
     */
    .she-panel {
        position: relative;
        display: flex;
        min-width: 0;
        max-width: 100%;
        height: calc(100vh - 155px);
        min-height: 570px;
        flex-direction: column;
        padding: 22px;
        overflow: hidden;
        border-radius: 20px;
        background: #eeeeee;
    }

    .she-fixed-header {
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
        box-shadow: 0 8px 14px rgba(15, 23, 42, 0.09);
    }

    .she-panel-title {
        margin: 0 0 17px;
        font-size: 16px;
        font-weight: 800;
    }

    .she-filter-grid {
        display: grid;
        grid-template-columns:
            minmax(250px, 1fr)
            110px
            135px
            130px
            125px
            115px;
        gap: 10px;
        align-items: end;
    }

    .she-field {
        display: flex;
        min-width: 0;
        flex-direction: column;
        gap: 6px;
    }

    .she-label {
        color: #374151;
        font-size: 9px;
        font-weight: 700;
    }

    .she-input,
    .she-select {
        width: 100%;
        height: 38px;
        padding: 0 12px;
        border: 1px solid #d5d9df;
        border-radius: 8px;
        outline: none;
        color: #374151;
        background: #ffffff;
        font-size: 11px;
    }

    .she-input:focus,
    .she-select:focus {
        border-color: #147df5;
        box-shadow: 0 0 0 3px rgba(20, 125, 245, 0.12);
    }

    .she-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 9px;
        margin-top: 11px;
    }

    .she-button {
        display: inline-flex;
        min-width: 98px;
        height: 37px;
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

    .she-button:hover {
        filter: brightness(0.95);
    }

    .she-button.search {
        background: #147df5;
    }

    .she-button.source {
        background: #656565;
    }

    .she-button.refresh {
        background: #159447;
    }

    .she-button.export {
        background: #0f766e;
    }

    .she-statistics {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 11px;
        margin-top: 15px;
    }

    .she-stat {
        position: relative;
        min-height: 69px;
        padding: 11px 14px;
        overflow: hidden;
        border: 1px solid #c6cbd2;
        border-radius: 8px;
        background: #ffffff;
    }

    .she-stat::after {
        position: absolute;
        right: 0;
        bottom: 0;
        left: 0;
        height: 4px;
        content: "";
    }

    .she-stat.total::after {
        background: #334155;
    }

    .she-stat.bulan::after {
        background: #147df5;
    }

    .she-stat.selesai::after {
        background: #219653;
    }

    .she-stat.gagal::after {
        background: #ed1c2e;
    }

    .she-stat-label {
        display: block;
        margin-bottom: 7px;
        color: #4b5563;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .she-stat-value {
        display: block;
        font-size: 23px;
        font-weight: 900;
        line-height: 1;
    }

    .she-alert {
        flex: 0 0 auto;
        margin: 12px 0 0;
        padding: 12px;
        border: 1px solid #fecaca;
        border-radius: 8px;
        color: #991b1b;
        background: #fef2f2;
        font-size: 11px;
        font-weight: 700;
    }

    .she-meta {
        display: flex;
        flex: 0 0 auto;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 8px;
        padding: 11px 0 8px;
        color: #64748b;
        font-size: 9px;
        font-weight: 700;
    }

    /*
     * Scroll vertikal dan horizontal hanya terjadi di area tabel.
     */
    .she-table-wrapper {
        position: relative;
        min-height: 0;
        flex: 1 1 auto;
        overflow: auto;
        overscroll-behavior: contain;
        scrollbar-gutter: stable;
        border: 1px solid #d7dce2;
        border-radius: 9px;
        background: #ffffff;
    }

    .she-table-wrapper::-webkit-scrollbar {
        width: 11px;
        height: 11px;
    }

    .she-table-wrapper::-webkit-scrollbar-track {
        background: #e5e7eb;
    }

    .she-table-wrapper::-webkit-scrollbar-thumb {
        border: 2px solid #e5e7eb;
        border-radius: 999px;
        background: #9ca3af;
    }

    .she-table {
        width: max-content;
        min-width: 1900px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .she-table th {
        position: sticky;
        z-index: 8;
        top: 0;
        padding: 10px 12px;
        border-bottom: 1px solid #cbd5e1;
        color: #334155;
        background: #f8fafc;
        box-shadow: 0 1px 0 #d7dce2;
        font-size: 9px;
        font-weight: 800;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .she-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #e5e7eb;
        color: #334155;
        background: #ffffff;
        font-size: 10px;
        vertical-align: middle;
        white-space: nowrap;
    }

    .she-table tbody tr:hover td {
        background: #f0f7ff;
    }

    /*
     * Timestamp dan Nama tetap terlihat saat tabel digeser ke kanan.
     */
    .she-table th:nth-child(1),
    .she-table td:nth-child(1) {
        position: sticky;
        left: 0;
        width: 155px;
        min-width: 155px;
        max-width: 155px;
    }

    .she-table th:nth-child(2),
    .she-table td:nth-child(2) {
        position: sticky;
        left: 155px;
        width: 220px;
        min-width: 220px;
        max-width: 220px;
        box-shadow: 4px 0 7px rgba(15, 23, 42, 0.08);
    }

    .she-table th:nth-child(1),
    .she-table th:nth-child(2) {
        z-index: 15;
        background: #f8fafc;
    }

    .she-table td:nth-child(1),
    .she-table td:nth-child(2) {
        z-index: 4;
        background: #ffffff;
    }

    .she-table tbody tr:hover td:nth-child(1),
    .she-table tbody tr:hover td:nth-child(2) {
        background: #f0f7ff;
    }

    .she-cell-long {
        min-width: 190px;
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .she-new-badge {
        display: inline-flex;
        margin-left: 5px;
        padding: 2px 6px;
        border-radius: 999px;
        color: #ffffff;
        background: #147df5;
        font-size: 7px;
        font-weight: 900;
        vertical-align: middle;
    }

    .she-status {
        display: inline-flex;
        min-width: 84px;
        min-height: 20px;
        align-items: center;
        justify-content: center;
        padding: 3px 9px;
        border-radius: 999px;
        color: #ffffff;
        font-size: 8px;
        font-weight: 800;
        text-align: center;
        white-space: normal;
    }

    .she-status.selesai {
        background: #24915c;
    }

    .she-status.gagal,
    .she-status.expired {
        background: #ed1524;
    }

    .she-status.proses,
    .she-status.not-yet {
        color: #111827;
        background: #facc15;
    }

    .she-status.netral {
        color: #374151;
        background: #e5e7eb;
    }

    .she-empty {
        padding: 35px !important;
        color: #64748b !important;
        text-align: center;
        font-weight: 700;
    }

    .pagination-box {
        display: flex;
        flex: 0 0 auto;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding-top: 11px;
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
        border-color: #147df5;
        color: #ffffff;
        background: #147df5;
    }

    .page-link.disabled {
        color: #9ca3af;
        background: #f3f4f6;
        pointer-events: none;
    }

    .she-loading {
        position: fixed;
        z-index: 30000;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(15, 23, 42, 0.34);
        backdrop-filter: blur(2px);
    }

    .she-loading.active {
        display: flex;
    }

    .she-loading-box {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 15px 18px;
        border-radius: 12px;
        color: #111827;
        background: #ffffff;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.22);
        font-size: 11px;
        font-weight: 800;
    }

    .she-spinner {
        width: 22px;
        height: 22px;
        border: 3px solid #dbeafe;
        border-top-color: #147df5;
        border-radius: 50%;
        animation: she-spin 0.8s linear infinite;
    }

    @keyframes she-spin {
        to {
            transform: rotate(360deg);
        }
    }

    @media (max-width: 1350px) {
        .she-filter-grid {
            grid-template-columns:
                minmax(240px, 1fr)
                repeat(3, 125px);
        }

        .she-statistics {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 750px) {
        .she-panel {
            height: auto;
            min-height: 0;
            padding: 15px;
            overflow: visible;
        }

        .she-fixed-header {
            margin:
                -15px
                -15px
                0;
            padding:
                15px
                15px
                10px;
        }

        .she-filter-grid,
        .she-statistics {
            grid-template-columns: 1fr;
        }

        .she-actions {
            justify-content: stretch;
        }

        .she-button {
            flex: 1 1 135px;
        }

        .she-table-wrapper {
            height: 520px;
            flex: none;
        }
    }
</style>

<div class="she-page">
    @php
        $monitoringShePaginator =
            $monitoringShePaginator ?? null;

        $currentPage = $monitoringShePaginator
            ? $monitoringShePaginator->currentPage()
            : 1;

        $lastPage = $monitoringShePaginator
            ? $monitoringShePaginator->lastPage()
            : 1;

        $pageStart = max(1, $currentPage - 2);
        $pageEnd = min($lastPage, $currentPage + 2);

        $exportQuery = array_merge(
            request()->except([
                'page',
                'refresh',
                'export',
            ]),
            ['export' => 'csv']
        );
    @endphp

    <h1 class="she-page-title">
        Mine Permit
    </h1>

    <section class="she-panel">

        <div class="she-fixed-header">

            <h2 class="she-panel-title">
                MONITORING MINE PERMIT SHE
            </h2>

            <form
                id="sheFilterForm"
                method="GET"
                action="{{ route('mine-permit.monitoring-she') }}"
            >
                <div class="she-filter-grid">

                    <div class="she-field">
                        <label
                            for="sheSearch"
                            class="she-label"
                        >
                            Pencarian
                        </label>

                        <input
                            id="sheSearch"
                            name="search"
                            type="search"
                            class="she-input"
                            placeholder="NRP / NAMA / JABATAN / PERUSAHAAN"
                            value="{{ $search ?? request('search') }}"
                        >
                    </div>

                    <div class="she-field">
                        <label
                            for="sheYear"
                            class="she-label"
                        >
                            Tahun
                        </label>

                        <select
                            id="sheYear"
                            name="year"
                            class="she-select"
                        >
                            <option value="all">
                                Semua Tahun
                            </option>

                            @foreach (($availableYears ?? []) as $year)
                                <option
                                    value="{{ $year }}"
                                    @selected(
                                        (string) (
                                            $selectedYear ?? 'all'
                                        ) === (string) $year
                                    )
                                >
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="she-field">
                        <label
                            for="sheMonth"
                            class="she-label"
                        >
                            Bulan
                        </label>

                        <select
                            id="sheMonth"
                            name="month"
                            class="she-select"
                        >
                            <option value="all">
                                Semua Bulan
                            </option>

                            @foreach (($monthOptions ?? []) as $number => $label)
                                <option
                                    value="{{ $number }}"
                                    @selected(
                                        (string) (
                                            $selectedMonth ?? 'all'
                                        ) === (string) $number
                                    )
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="she-field">
                        <label
                            for="sheStatus"
                            class="she-label"
                        >
                            Status SHE
                        </label>

                        <select
                            id="sheStatus"
                            name="status"
                            class="she-select"
                        >
                            <option
                                value="all"
                                @selected(
                                    ($selectedStatus ?? 'all')
                                    === 'all'
                                )
                            >
                                Semua Status
                            </option>

                            <option
                                value="proses"
                                @selected(
                                    ($selectedStatus ?? 'all')
                                    === 'proses'
                                )
                            >
                                Proses
                            </option>

                            <option
                                value="selesai"
                                @selected(
                                    ($selectedStatus ?? 'all')
                                    === 'selesai'
                                )
                            >
                                Selesai
                            </option>

                            <option
                                value="gagal"
                                @selected(
                                    ($selectedStatus ?? 'all')
                                    === 'gagal'
                                )
                            >
                                Gagal
                            </option>
                        </select>
                    </div>

                    <div class="she-field">
                        <label
                            for="shePengajuan"
                            class="she-label"
                        >
                            Pengajuan
                        </label>

                        <select
                            id="shePengajuan"
                            name="pengajuan"
                            class="she-select"
                        >
                            <option value="all">
                                Semua Pengajuan
                            </option>

                            @foreach (
                                ($availablePengajuan ?? [])
                                as $pengajuan
                            )
                                <option
                                    value="{{ $pengajuan }}"
                                    @selected(
                                        (string) (
                                            $selectedPengajuan
                                            ?? 'all'
                                        ) === (string) $pengajuan
                                    )
                                >
                                    {{ $pengajuan }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="she-field">
                        <label
                            for="shePerPage"
                            class="she-label"
                        >
                            Data per halaman
                        </label>

                        <select
                            id="shePerPage"
                            name="per_page"
                            class="she-select"
                        >
                            @foreach ([25, 50, 100] as $option)
                                <option
                                    value="{{ $option }}"
                                    @selected(
                                        ($perPage ?? 50)
                                        === $option
                                    )
                                >
                                    {{ $option }} data
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="she-actions">

                    <button
                        type="submit"
                        class="she-button search"
                    >
                        SEARCH
                    </button>

                    <a
                        href="https://docs.google.com/spreadsheets/d/1IFufJElpiWRUcx96TwbktOjUm4_4qvhQpuUREWkp3c0/edit?gid=978127958#gid=978127958"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="she-button source"
                    >
                        SUMBER DATA
                    </a>

                    <a
                        id="sheRefreshButton"
                        href="{{ route(
                            'mine-permit.monitoring-she',
                            [
                                'refresh' => now()->timestamp,
                                'per_page' => $perPage ?? 50,
                            ]
                        ) }}"
                        class="she-button refresh"
                        title="Ambil data terbaru dan hapus semua filter"
                    >
                        REFRESH
                    </a>

                    <a
                        href="{{ route(
                            'mine-permit.monitoring-she',
                            $exportQuery
                        ) }}"
                        class="she-button export"
                        title="Unduh seluruh hasil filter sebagai CSV"
                    >
                        EXPORT CSV
                    </a>

                </div>
            </form>

            <div class="she-statistics">

                <article class="she-stat total">
                    <span class="she-stat-label">
                        Total hasil filter
                    </span>

                    <strong class="she-stat-value">
                        {{ $totalHasilFilter ?? 0 }}
                    </strong>
                </article>

                <article class="she-stat bulan">
                    <span class="she-stat-label">
                        Pengajuan bulan ini
                    </span>

                    <strong class="she-stat-value">
                        {{ $prosesPengajuanBulanIni ?? 0 }}
                    </strong>
                </article>

                <article class="she-stat selesai">
                    <span class="she-stat-label">
                        Status selesai
                    </span>

                    <strong class="she-stat-value">
                        {{ $totalSelesai ?? 0 }}
                    </strong>
                </article>

                <article class="she-stat gagal">
                    <span class="she-stat-label">
                        Status gagal
                    </span>

                    <strong class="she-stat-value">
                        {{ $totalGagal ?? 0 }}
                    </strong>
                </article>

            </div>

        </div>

        @if ($sheetError ?? null)
            <div class="she-alert">
                {{ $sheetError }}
            </div>
        @endif

        <div class="she-meta">
            <span>
                Menampilkan
                {{ $monitoringShePaginator?->firstItem() ?? 0 }}
                –
                {{ $monitoringShePaginator?->lastItem() ?? 0 }}
                dari
                {{ $monitoringShePaginator?->total() ?? 0 }}
                hasil
            </span>

            <span>
                Sinkronisasi terakhir:
                {{ $lastSyncedAt ?? '-' }}
            </span>
        </div>

        <div class="she-table-wrapper">

            <table class="she-table">

                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Nama Karyawan</th>
                        <th>Jabatan</th>
                        <th>Departemen</th>
                        <th>Perusahaan</th>
                        <th>Pengajuan</th>
                        <th>Jenis</th>
                        <th>NRP</th>
                        <th>Status SHE</th>
                        <th>Keterangan</th>
                        <th>Status BNN</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse (
                        ($monitoringShePaginator ?? [])
                        as $row
                    )
                        @php
                            $statusNormal = strtolower(
                                $row['status'] ?? 'proses'
                            );

                            $statusBnnRaw = strtolower(
                                $row['status_bnn'] ?? ''
                            );

                            $statusBnnClass = match (true) {
                                str_contains(
                                    $statusBnnRaw,
                                    'expired'
                                ) => 'expired',

                                str_contains(
                                    $statusBnnRaw,
                                    'not yet'
                                ) => 'not-yet',

                                default => 'netral',
                            };
                        @endphp

                        <tr>
                            <td title="{{ $row['timestamp'] }}">
                                {{ $row['timestamp'] ?: '-' }}

                                @if ($row['is_today'] ?? false)
                                    <span class="she-new-badge">
                                        BARU
                                    </span>
                                @endif
                            </td>

                            <td
                                class="she-cell-long"
                                title="{{ $row['nama'] }}"
                            >
                                {{ $row['nama'] ?: '-' }}
                            </td>

                            <td title="{{ $row['jabatan'] }}">
                                {{ $row['jabatan'] ?: '-' }}
                            </td>

                            <td title="{{ $row['departemen'] }}">
                                {{ $row['departemen'] ?: '-' }}
                            </td>

                            <td
                                class="she-cell-long"
                                title="{{ $row['perusahaan'] }}"
                            >
                                {{ $row['perusahaan'] ?: '-' }}
                            </td>

                            <td title="{{ $row['pengajuan'] }}">
                                {{ $row['pengajuan'] ?: '-' }}
                            </td>

                            <td title="{{ $row['jenis'] }}">
                                {{ $row['jenis'] ?: '-' }}
                            </td>

                            <td title="{{ $row['nrp'] }}">
                                {{ $row['nrp'] ?: '-' }}
                            </td>

                            <td>
                                <span
                                    class="she-status
                                        {{ $statusNormal === 'gagal'
                                            ? 'gagal'
                                            : (
                                                $statusNormal
                                                === 'selesai'
                                                    ? 'selesai'
                                                    : 'proses'
                                            ) }}"
                                    title="{{ $row['status_she'] }}"
                                >
                                    {{ $row['status_she']
                                        ?: 'PROSES' }}
                                </span>
                            </td>

                            <td
                                class="she-cell-long"
                                title="{{ $row['keterangan'] }}"
                            >
                                {{ $row['keterangan'] ?: '-' }}
                            </td>

                            <td>
                                <span
                                    class="she-status
                                        {{ $statusBnnClass }}"
                                    title="{{ $row['status_bnn'] }}"
                                >
                                    {{ $row['status_bnn'] ?: '-' }}
                                </span>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="11"
                                class="she-empty"
                            >
                                Data pengajuan tidak ditemukan.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if (
            $monitoringShePaginator &&
            $monitoringShePaginator->hasPages()
        )
            <nav
                class="pagination-box"
                aria-label="Pagination Monitoring SHE"
            >
                <span class="pagination-info">
                    Halaman {{ $currentPage }}
                    dari {{ $lastPage }}
                </span>

                <div class="pagination-links">

                    <a
                        href="{{ $monitoringShePaginator
                            ->previousPageUrl() ?? '#' }}"
                        class="page-link
                            {{ $monitoringShePaginator
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
                            href="{{ $monitoringShePaginator
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
                        href="{{ $monitoringShePaginator
                            ->nextPageUrl() ?? '#' }}"
                        class="page-link
                            {{ $monitoringShePaginator
                                ->hasMorePages()
                                    ? ''
                                    : 'disabled' }}"
                    >
                        ›
                    </a>

                </div>
            </nav>
        @endif

    </section>

    <div
        id="sheLoading"
        class="she-loading"
        aria-hidden="true"
    >
        <div class="she-loading-box">
            <span class="she-spinner"></span>
            <span>Mengambil data Google Spreadsheet…</span>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const loading = document.getElementById('sheLoading');
        const form = document.getElementById('sheFilterForm');
        const refreshButton =
            document.getElementById('sheRefreshButton');

        function showLoading() {
            if (loading) {
                loading.classList.add('active');
                loading.setAttribute('aria-hidden', 'false');
            }
        }

        if (form) {
            form.addEventListener('submit', showLoading);
        }

        if (refreshButton) {
            refreshButton.addEventListener('click', showLoading);
        }
    });
</script>