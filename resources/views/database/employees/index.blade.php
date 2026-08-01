@php
    $syncStatus = (string) (
        $syncMeta['status'] ?? 'error'
    );

    $syncError = trim(
        (string) (
            $syncMeta['error'] ?? ''
        )
    );

    $syncedAt = null;

    if (!empty($syncMeta['synced_at'])) {
        try {
            $syncedAt = \Illuminate\Support\Carbon::parse(
                $syncMeta['synced_at']
            )
                ->timezone(
                    config(
                        'app.timezone',
                        'Asia/Jakarta'
                    )
                )
                ->translatedFormat(
                    'd M Y, H:i'
                );
        } catch (\Throwable) {
            $syncedAt = (string) $syncMeta['synced_at'];
        }
    }

    $statusLabel = match ($syncStatus) {
        'synced' => 'Baru Disinkronkan',
        'cached' => 'Menggunakan Cache',
        'stale' => 'Cache Terakhir',
        default => 'Belum Terhubung',
    };

    $statusClass = match ($syncStatus) {
        'synced', 'cached' => 'success',
        'stale' => 'warning',
        default => 'danger',
    };
@endphp

<div class="db-page-title employee-page-head">
    <div>
        <h1>Database Karyawan</h1>
        <p>
            Data real MASTER_DATABASE dengan cache dan sinkronisasi.
        </p>
    </div>

    <div class="employee-head-actions">
        @if ($googleConnected ?? false)
            <span class="employee-connection connected">
                ● Google Terhubung
            </span>
        @else
            <a
                href="{{ route('google.oauth.redirect') }}"
                class="db-button secondary"
            >
                HUBUNGKAN GOOGLE
            </a>
        @endif

        @if (!empty($sourceUrl))
            <a
                href="{{ $sourceUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                class="db-button dark"
            >
                SOURCE DATA
            </a>
        @endif

        <a
            href="{{ route(
                'database.employees.mapping-diagnostics'
            ) }}"
            class="db-button secondary"
        >
            CEK MAPPING
        </a>

        <form
            method="POST"
            action="{{ route('database.employees.sync') }}"
            id="employeeSyncForm"
        >
            @csrf

            <button
                type="submit"
                class="db-button employee-sync-button"
                id="employeeSyncButton"
            >
                <span id="employeeSyncButtonText">
                    ↻ SINKRONKAN DATA
                </span>
            </button>
        </form>
    </div>
</div>

@if (session('success'))
    <div class="employee-alert success">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="employee-alert danger">
        {{ session('error') }}
    </div>
@endif

<div class="employee-sticky-zone" id="employeeStickyZone">
<section class="employee-sync-panel {{ $statusClass }}">
    <div class="employee-sync-main">
        <span class="employee-sync-dot"></span>

        <div>
            <strong>{{ $statusLabel }}</strong>

            <small>
                @if ($syncedAt)
                    Terakhir sinkron:
                    {{ $syncedAt }} WIB
                @else
                    Belum ada sinkronisasi berhasil
                @endif
            </small>
        </div>
    </div>

    <div class="employee-sync-meta">
        <span>
            Data:
            <strong>
                {{ number_format(
                    (int) (
                        $syncMeta['mapped_rows'] ?? 0
                    )
                ) }}
            </strong>
        </span>

        <span>
            Duplikat NRP:
            <strong>
                {{ number_format(
                    (int) (
                        $syncMeta['duplicate_rows'] ?? 0
                    )
                ) }}
            </strong>
        </span>

        <span>
            Range:
            <strong>
                {{ $syncMeta['range'] ?? '-' }}
            </strong>
        </span>
    </div>

    @if ($syncError !== '')
        <p class="employee-sync-error">
            {{ $syncError }}

            @if ($syncStatus === 'stale')
                Data cache terakhir tetap ditampilkan.
            @endif
        </p>
    @endif
</section>

@if (!empty($syncMeta['missing_fields']))
    @php
        $missingFieldLabels = [
            'jabatan' => 'Jabatan/Posisi',
            'departemen' => 'Departemen',
            'perusahaan' => 'Perusahaan',
            'site' => 'Site',
            'kamar' => 'Kamar',
            'gedung_kamar' => 'Gedung/Kamar Gabungan',
        ];

        $missingLabels = collect(
            $syncMeta['missing_fields']
        )
            ->map(
                fn ($field) =>
                    $missingFieldLabels[$field] ??
                    strtoupper(
                        str_replace(
                            '_',
                            ' ',
                            (string) $field
                        )
                    )
            )
            ->implode(', ');
    @endphp

    <div class="employee-mapping-warning">
        <strong>Kolom belum tersedia pada MASTER_DATABASE:</strong>
        {{ $missingLabels }}.

        Nilai tersebut ditampilkan sebagai tanda “-” sampai
        tersedia dari sumber lain atau ditambahkan ke spreadsheet.
    </div>
@endif

<form
    method="GET"
    class="db-panel employee-filter-grid"
>
    <div class="db-field">
        <label for="employeeSearch">
            Nama / NRP / Jabatan
        </label>

        <input
            id="employeeSearch"
            name="search"
            type="search"
            class="db-input"
            value="{{ $search ?? '' }}"
            placeholder="Cari nama, NRP, atau jabatan…"
        >
    </div>

    <div class="db-field">
        <label for="residence">
            Tempat Tinggal
        </label>

        <select
            id="residence"
            name="residence"
            class="db-select"
        >
            <option value="all">
                Semua Status
            </option>

            <option
                value="mess"
                @selected(
                    ($residence ?? 'all') === 'mess'
                )
            >
                Mess
            </option>

            <option
                value="non-mess"
                @selected(
                    ($residence ?? 'all') === 'non-mess'
                )
            >
                Non Mess
            </option>
        </select>
    </div>

    <div class="db-field">
        <label for="perPage">
            Data per Halaman
        </label>

        <select
            id="perPage"
            name="per_page"
            class="db-select"
        >
            @foreach ([25, 50, 100] as $size)
                <option
                    value="{{ $size }}"
                    @selected(
                        ($perPage ?? 25) === $size
                    )
                >
                    {{ $size }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="db-actions">
        <button
            class="db-button"
            type="submit"
        >
            CARI
        </button>

        <a
            href="{{ route('database.employees') }}"
            class="db-button secondary"
        >
            RESET
        </a>
    </div>
</form>

<div class="employee-kpi-grid">
    <article class="db-kpi-card">
        <span class="db-kpi-icon">👥</span>
        <div>
            <small>Total Karyawan</small>
            <strong>
                {{ number_format(
                    $employeeStats['total'] ?? 0
                ) }}
            </strong>
        </div>
    </article>

    <article class="db-kpi-card">
        <span class="db-kpi-icon">🏠</span>
        <div>
            <small>Tinggal di Mess</small>
            <strong>
                {{ number_format(
                    $employeeStats['mess'] ?? 0
                ) }}
            </strong>
        </div>
    </article>

    <article class="db-kpi-card">
        <span class="db-kpi-icon">🚶</span>
        <div>
            <small>Tinggal Non Mess</small>
            <strong>
                {{ number_format(
                    $employeeStats['non_mess'] ?? 0
                ) }}
            </strong>
        </div>
    </article>

    <article class="db-kpi-card">
        <span class="db-kpi-icon">?</span>
        <div>
            <small>Status Belum Lengkap</small>
            <strong>
                {{ number_format(
                    $employeeStats['unknown'] ?? 0
                ) }}
            </strong>
        </div>
    </article>
</div>
</div>

<section class="db-table-card employee-table-card">
    <div class="db-card-header">
        <div>
            <h2>Data Ringkasan Karyawan</h2>
            <small>
                Menampilkan
                {{ number_format($employees->firstItem() ?? 0) }}
                –
                {{ number_format($employees->lastItem() ?? 0) }}
                dari
                {{ number_format($employees->total()) }}
                hasil
            </small>
        </div>
    </div>

    <div class="db-table-wrap employee-table-scroll">
        <table class="db-table employee-real-table">
            <thead>
                <tr>
                    <th>NRP</th>
                    <th>Nama Karyawan</th>
                    <th>Jabatan</th>
                    <th>Status Karyawan</th>
                    <th>Status Tinggal</th>
                    <th>Gedung/Kamar</th>
                    <th>No. HP</th>
                    <th>Email</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($employees as $employee)
                    @php
                        $phoneRaw = (string) (
                            $employee['no_hp'] ?? ''
                        );

                        $phoneDigits = preg_replace(
                            '/\D+/',
                            '',
                            $phoneRaw
                        ) ?? '';

                        if (
                            str_starts_with(
                                $phoneDigits,
                                '0'
                            )
                        ) {
                            $phoneDigits =
                                '62' .
                                substr(
                                    $phoneDigits,
                                    1
                                );
                        }

                        $employmentStatus =
                            strtoupper(
                                trim(
                                    (string) (
                                        $employee[
                                            'status_karyawan'
                                        ] ?? '-'
                                    )
                                )
                            );

                        $employmentBadge =
                            in_array(
                                $employmentStatus,
                                [
                                    'AKTIF',
                                    'ACTIVE',
                                ],
                                true
                            )
                                ? 'green'
                                : 'orange';
                    @endphp

                    <tr>
                        <td>
                            {{ $employee['nrp'] ?? '-' }}
                        </td>

                        <td>
                            <strong>
                                {{ $employee['nama'] ?? '-' }}
                            </strong>
                        </td>

                        <td>
                            {{ $employee['jabatan'] ?? '-' }}
                        </td>

                        <td>
                            <span
                                class="db-badge {{ $employmentBadge }}"
                            >
                                {{ $employmentStatus }}
                            </span>
                        </td>

                        <td>
                            {{ $employee['status_tinggal'] ?? '-' }}
                        </td>

                        <td>
                            {{ $employee['gedung'] ?? '-' }}
                            /
                            {{ $employee['kamar'] ?? '-' }}
                        </td>

                        <td>
                            {{ $employee['no_hp'] ?? '-' }}
                        </td>

                        <td>
                            {{ $employee['email'] ?? '-' }}
                        </td>

                        <td>
                            <details class="employee-detail">
                                <summary class="db-button">
                                    DETAIL
                                </summary>

                                <div class="employee-detail-card">
                                    <div class="employee-detail-title">
                                        <strong>
                                            {{ $employee['nama'] ?? '-' }}
                                        </strong>

                                        <small>
                                            NRP:
                                            {{ $employee['nrp'] ?? '-' }}
                                        </small>
                                    </div>

                                    <dl class="employee-detail-list">
                                        <div>
                                            <dt>Jabatan</dt>
                                            <dd>
                                                {{ $employee['jabatan'] ?? '-' }}
                                            </dd>
                                        </div>

                                        <div>
                                            <dt>Departemen</dt>
                                            <dd>
                                                {{ $employee['departemen'] ?? '-' }}
                                            </dd>
                                        </div>

                                        <div>
                                            <dt>Perusahaan/Site</dt>
                                            <dd>
                                                {{ $employee['perusahaan'] ?? '-' }}
                                                /
                                                {{ $employee['site'] ?? '-' }}
                                            </dd>
                                        </div>

                                        <div>
                                            <dt>Tanggal Lahir</dt>
                                            <dd>
                                                {{ $employee['tanggal_lahir'] ?? '-' }}
                                            </dd>
                                        </div>

                                        <div>
                                            <dt>Tempat Tinggal</dt>
                                            <dd>
                                                {{ $employee['status_tinggal'] ?? '-' }}
                                                ·
                                                {{ $employee['gedung'] ?? '-' }}
                                                /
                                                {{ $employee['kamar'] ?? '-' }}
                                            </dd>
                                        </div>
                                    </dl>

                                    <div class="db-actions">
                                        @if ($phoneDigits !== '')
                                            <a
                                                href="https://wa.me/{{ $phoneDigits }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="db-button green"
                                            >
                                                WHATSAPP
                                            </a>
                                        @endif

                                        @if (
                                            !empty($employee['email']) &&
                                            $employee['email'] !== '-'
                                        )
                                            <a
                                                href="mailto:{{ $employee['email'] }}"
                                                class="db-button dark"
                                            >
                                                KIRIM EMAIL
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td
                            colspan="9"
                            class="db-empty"
                        >
                            @if ($syncError !== '')
                                Data belum dapat dimuat:
                                {{ $syncError }}
                            @else
                                Data tidak ditemukan.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($employees->hasPages())
        <div class="employee-pagination">
            <div>
                Halaman
                <strong>{{ $employees->currentPage() }}</strong>
                dari
                <strong>{{ $employees->lastPage() }}</strong>
            </div>

            <div class="employee-pagination-actions">
                @if ($employees->onFirstPage())
                    <span class="employee-page-button disabled">
                        ‹ Sebelumnya
                    </span>
                @else
                    <a
                        href="{{ $employees->previousPageUrl() }}"
                        class="employee-page-button"
                    >
                        ‹ Sebelumnya
                    </a>
                @endif

                @if ($employees->hasMorePages())
                    <a
                        href="{{ $employees->nextPageUrl() }}"
                        class="employee-page-button"
                    >
                        Berikutnya ›
                    </a>
                @else
                    <span class="employee-page-button disabled">
                        Berikutnya ›
                    </span>
                @endif
            </div>
        </div>
    @endif
</section>

<style>
.employee-page-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
}

.employee-head-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: flex-end;
    gap: 7px;
}

.employee-head-actions form {
    margin: 0;
}

.employee-connection {
    display: inline-flex;
    min-height: 35px;
    align-items: center;
    padding: 0 11px;
    border-radius: 8px;
    font-size: 9px;
    font-weight: 900;
}

.employee-connection.connected {
    color: #087a45;
    background: #dcfce7;
}

.employee-sync-button {
    background: #147df5;
}

.employee-alert,
.employee-sync-panel {
    margin-bottom: 10px;
    border: 1px solid;
    border-radius: 10px;
}

.employee-alert {
    padding: 10px 12px;
    font-size: 10px;
    font-weight: 800;
}

.employee-alert.success {
    border-color: #86efac;
    color: #087a45;
    background: #f0fdf4;
}

.employee-alert.danger {
    border-color: #fca5a5;
    color: #b91c1c;
    background: #fef2f2;
}

.employee-sync-panel {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 11px 13px;
}

.employee-sync-panel.success {
    border-color: #86efac;
    background: #f0fdf4;
}

.employee-sync-panel.warning {
    border-color: #fcd34d;
    background: #fffbeb;
}

.employee-sync-panel.danger {
    border-color: #fca5a5;
    background: #fef2f2;
}

.employee-sync-main {
    display: flex;
    align-items: center;
    gap: 9px;
}

.employee-sync-main strong,
.employee-sync-main small {
    display: block;
}

.employee-sync-main strong {
    font-size: 11px;
}

.employee-sync-main small {
    margin-top: 2px;
    color: #64748b;
    font-size: 9px;
}

.employee-sync-dot {
    width: 10px;
    height: 10px;
    flex: 0 0 10px;
    border-radius: 50%;
    background: #16a34a;
}

.employee-sync-panel.warning .employee-sync-dot {
    background: #f59e0b;
}

.employee-sync-panel.danger .employee-sync-dot {
    background: #dc2626;
}

.employee-sync-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 13px;
    color: #64748b;
    font-size: 9px;
}

.employee-sync-meta strong {
    color: #172033;
}

.employee-sync-error {
    width: 100%;
    margin: 0;
    color: #b45309;
    font-size: 9px;
    font-weight: 700;
}

.employee-sync-panel.danger .employee-sync-error {
    color: #b91c1c;
}

.employee-filter-grid {
    display: grid;
    grid-template-columns:
        minmax(240px, 1fr)
        185px
        150px
        auto;
    gap: 9px;
    align-items: end;
}

.employee-kpi-grid {
    display: grid;
    grid-template-columns: repeat(
        4,
        minmax(0, 1fr)
    );
    gap: 10px;
    margin-bottom: 11px;
}

.employee-real-table {
    min-width: 1200px;
}

.employee-detail {
    position: relative;
}

.employee-detail summary {
    list-style: none;
}

.employee-detail summary::-webkit-details-marker {
    display: none;
}

.employee-detail-card {
    position: absolute;
    z-index: 50;
    top: calc(100% + 7px);
    right: 0;
    width: 360px;
    padding: 13px;
    border: 1px solid #dbe2ea;
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 18px 45px rgba(15, 23, 42, .18);
}

.employee-detail-title {
    padding-bottom: 9px;
    border-bottom: 1px solid #e2e8f0;
}

.employee-detail-title strong,
.employee-detail-title small {
    display: block;
}

.employee-detail-title small {
    margin-top: 3px;
    color: #64748b;
    font-size: 9px;
}

.employee-detail-list {
    margin: 10px 0;
}

.employee-detail-list > div {
    display: grid;
    grid-template-columns: 105px minmax(0, 1fr);
    gap: 8px;
    margin-bottom: 6px;
}

.employee-detail-list dt {
    color: #64748b;
    font-size: 9px;
}

.employee-detail-list dd {
    margin: 0;
    color: #172033;
    font-size: 9px;
    font-weight: 800;
}

.employee-pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 11px 13px;
    border-top: 1px solid #e2e8f0;
    color: #64748b;
    font-size: 9px;
}

.employee-pagination-actions {
    display: flex;
    gap: 7px;
}

.employee-page-button {
    display: inline-flex;
    min-height: 31px;
    align-items: center;
    padding: 0 10px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    color: #172033;
    background: #fff;
    font-weight: 800;
    text-decoration: none;
}

.employee-page-button.disabled {
    cursor: not-allowed;
    opacity: .45;
}

@media (max-width: 1100px) {
    .employee-page-head {
        flex-direction: column;
    }

    .employee-head-actions {
        justify-content: flex-start;
    }

    .employee-filter-grid {
        grid-template-columns: 1fr 1fr;
    }

    .employee-kpi-grid {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 680px) {
    .employee-filter-grid,
    .employee-kpi-grid {
        grid-template-columns: 1fr;
    }

    .employee-pagination {
        align-items: flex-start;
        flex-direction: column;
    }
}
.employee-sync-button:disabled {
    cursor: wait;
    opacity: .72;
}

.employee-sync-button.is-loading {
    pointer-events: none;
}


/*
|--------------------------------------------------------------------------
| Sticky Database Karyawan
|--------------------------------------------------------------------------
| Sticky zone menjaga status sinkron, filter, dan KPI tetap terlihat.
| Tabel memiliki scroll sendiri agar header dan identitas karyawan tidak
| bergerak saat daftar digulir.
*/

.employee-sticky-zone {
    position: sticky;
    z-index: 40;
    top: 0;
    margin: 0 -2px 10px;
    padding: 2px 2px 1px;
    background: var(--db-bg);
    box-shadow:
        0 10px 18px -18px rgba(15, 23, 42, .65);
}

.employee-sticky-zone .employee-kpi-grid {
    margin-bottom: 0;
}

.employee-table-card {
    overflow: visible;
}

.employee-table-scroll {
    position: relative;
    height: clamp(
        320px,
        calc(100vh - 420px),
        620px
    );
    overflow: auto;
    overscroll-behavior: contain;
    scrollbar-gutter: stable;
}

/* Header tabel tetap terlihat ketika isi tabel digulir. */
.employee-real-table thead th {
    position: sticky;
    z-index: 12;
    top: 0;
    background: #f8fafc;
    box-shadow:
        0 1px 0 #dce2e8,
        0 5px 12px rgba(15, 23, 42, .05);
}

/* NRP tetap terlihat saat tabel digeser ke kanan. */
.employee-real-table th:nth-child(1),
.employee-real-table td:nth-child(1) {
    position: sticky;
    left: 0;
    width: 92px;
    min-width: 92px;
    max-width: 92px;
}

/* Nama karyawan tetap terlihat saat tabel digeser ke kanan. */
.employee-real-table th:nth-child(2),
.employee-real-table td:nth-child(2) {
    position: sticky;
    left: 92px;
    width: 205px;
    min-width: 205px;
    max-width: 205px;
    box-shadow:
        1px 0 0 #e5e7eb,
        7px 0 12px -12px rgba(15, 23, 42, .65);
}

.employee-real-table tbody td:nth-child(1),
.employee-real-table tbody td:nth-child(2) {
    z-index: 6;
    background: #ffffff;
}

.employee-real-table thead th:nth-child(1),
.employee-real-table thead th:nth-child(2) {
    z-index: 18;
    background: #f8fafc;
}

.employee-real-table tbody tr:hover td {
    background: #f8fbff;
}

.employee-real-table tbody tr:hover td:nth-child(1),
.employee-real-table tbody tr:hover td:nth-child(2) {
    background: #f8fbff;
}

@media (max-width: 1100px) {
    .employee-table-scroll {
        height: clamp(
            300px,
            calc(100vh - 500px),
            520px
        );
    }
}

@media (max-width: 680px) {
    .employee-sticky-zone {
        position: static;
    }

    .employee-table-scroll {
        height: 55vh;
        min-height: 300px;
    }

    .employee-real-table th:nth-child(2),
    .employee-real-table td:nth-child(2) {
        left: 86px;
        width: 170px;
        min-width: 170px;
        max-width: 170px;
    }

    .employee-real-table th:nth-child(1),
    .employee-real-table td:nth-child(1) {
        width: 86px;
        min-width: 86px;
        max-width: 86px;
    }
}


.employee-mapping-warning {
    margin-bottom: 10px;
    padding: 10px 12px;
    border: 1px solid #fcd34d;
    border-radius: 9px;
    color: #92400e;
    background: #fffbeb;
    font-size: 9px;
    line-height: 1.5;
}

.employee-mapping-warning strong {
    color: #78350f;
}

</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const syncForm =
        document.getElementById('employeeSyncForm');

    const syncButton =
        document.getElementById('employeeSyncButton');

    const syncButtonText =
        document.getElementById('employeeSyncButtonText');

    syncForm?.addEventListener('submit', function () {
        if (!syncButton || !syncButtonText) {
            return;
        }

        syncButton.disabled = true;
        syncButton.classList.add('is-loading');
        syncButtonText.textContent =
            '↻ MENYINKRONKAN...';
    });

    document
        .querySelectorAll('.employee-detail')
        .forEach(function (detail) {
            detail.addEventListener(
                'toggle',
                function () {
                    if (!detail.open) {
                        return;
                    }

                    document
                        .querySelectorAll('.employee-detail')
                        .forEach(function (otherDetail) {
                            if (otherDetail !== detail) {
                                otherDetail.open = false;
                            }
                        });
                }
            );
        });
});
</script>