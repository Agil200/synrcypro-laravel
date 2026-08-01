<style>
    .internal-upload-page {
        color: #172033;
        font-family: Arial, Helvetica, sans-serif;
    }

    .internal-upload-title {
        margin: 0 0 8px;
        color: #14213d;
        font-size: 28px;
        font-weight: 800;
        line-height: 1.1;
    }

    .internal-upload-panel {
        position: relative;
        padding: 22px;
        border-radius: 22px;
        background: #eeeeee;
    }

    /*
    |--------------------------------------------------------------------------
    | HEADER FILTER STICKY
    |--------------------------------------------------------------------------
    | Bagian judul, pencarian, statistik, dan tab status tetap terlihat
    | ketika daftar kartu karyawan digulir pada area konten Manpower.
    */

    .internal-upload-sticky {
        position: sticky;
        z-index: 30;
        top: 0;
        margin: -22px -22px 14px;
        padding: 22px 22px 12px;
        border-bottom: 1px solid #d6d9de;
        background: #eeeeee;
        box-shadow: 0 8px 14px rgba(15, 23, 42, 0.08);
    }

    .internal-upload-panel-title {
        margin: 0 0 20px;
        color: #111827;
        font-size: 16px;
        font-weight: 800;
    }

    .internal-upload-filter {
        display: grid;
        grid-template-columns:
            minmax(220px, 1fr)
            minmax(145px, 190px)
            105px
            80px
            120px;
        gap: 12px;
        align-items: end;
        margin-bottom: 18px;
    }

    .internal-upload-field {
        display: flex;
        min-width: 0;
        flex-direction: column;
        gap: 7px;
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
        border: 1px solid #d7dce2;
        border-radius: 8px;
        outline: none;
        color: #374151;
        background: #ffffff;
        font-size: 11px;
    }

    .internal-upload-input:focus,
    .internal-upload-select:focus {
        border-color: #1880f7;
        box-shadow: 0 0 0 3px rgba(24, 128, 247, 0.12);
    }

    .internal-upload-button {
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
        font-weight: 800;
        text-decoration: none;
    }

    .internal-upload-button.search {
        background: #167df4;
    }

    .internal-upload-button.reset {
        border: 1px solid #d1d5db;
        color: #374151;
        background: #ffffff;
    }

    .internal-upload-button.source {
        background: #606060;
    }

    .internal-upload-statistics {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }

    .internal-upload-stat {
        position: relative;
        min-height: 76px;
        padding: 13px 15px;
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

    .internal-upload-stat.complete::after {
        background: #1c9c5b;
    }

    .internal-upload-stat.incomplete::after {
        background: #f0a000;
    }

    .internal-upload-stat.expired::after {
        background: #e51e2a;
    }

    .internal-upload-stat-label {
        display: block;
        margin-bottom: 8px;
        color: #4b5563;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .internal-upload-stat-value {
        display: block;
        color: #111827;
        font-size: 25px;
        font-weight: 900;
        line-height: 1;
    }

    .internal-upload-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 0;
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

    .internal-upload-result-info {
        margin: 0 0 12px;
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
        color: #111827;
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

    .internal-upload-status {
        display: inline-flex;
        min-height: 23px;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        padding: 0 9px;
        border-radius: 999px;
        color: #ffffff;
        font-size: 8px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .internal-upload-status.lengkap {
        background: #20965b;
    }

    .internal-upload-status.belum-lengkap {
        background: #eb9b00;
    }

    .internal-upload-status.expired {
        background: #df1d2a;
    }

    .internal-upload-information {
        display: grid;
        grid-template-columns: 98px minmax(0, 1fr);
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
.internal-upload-information dd:last-child {
    color: #167df4;
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

    .internal-upload-details summary::-webkit-details-marker {
        display: none;
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
        min-height: 27px;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 0 9px;
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

    @media (max-width: 1200px) {
        .internal-upload-filter {
            grid-template-columns: 1fr 180px 100px 75px;
        }

        .internal-upload-button.source {
            grid-column: 1 / -1;
        }

        .internal-upload-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 800px) {
        .internal-upload-filter,
        .internal-upload-statistics,
        .internal-upload-grid {
            grid-template-columns: 1fr;
        }

        .internal-upload-panel {
            padding: 15px;
            border-radius: 14px;
        }

        .internal-upload-sticky {
            margin: -15px -15px 12px;
            padding: 15px 15px 10px;
        }
    }
</style>

<div class="internal-upload-page">

    <h1 class="internal-upload-title">
        Mine Permit
    </h1>

    <section class="internal-upload-panel">

        <div class="internal-upload-sticky">

            <h2 class="internal-upload-panel-title">
                MONITORING INTERNAL UPLOAD
            </h2>

            <form
            method="GET"
            action="{{ route('mine-permit.monitoring-internal-upload') }}"
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
                    placeholder="NRP / NAMA / JABATAN"
                    value="{{ request('search') }}"
                >
            </div>

            <div class="internal-upload-field">
                <label
                    for="internalStatus"
                    class="internal-upload-label"
                >
                    Status Dokumen
                </label>

                <select
                    id="internalStatus"
                    name="status"
                    class="internal-upload-select"
                >
                    <option
                        value="semua"
                        @selected($selectedStatus === 'semua')
                    >
                        Semua Status
                    </option>

                    <option
                        value="lengkap"
                        @selected($selectedStatus === 'lengkap')
                    >
                        Lengkap
                    </option>

                    <option
                        value="belum-lengkap"
                        @selected($selectedStatus === 'belum-lengkap')
                    >
                        Belum Lengkap
                    </option>

                    <option
                        value="expired"
                        @selected($selectedStatus === 'expired')
                    >
                        Expired
                    </option>
                </select>
            </div>

            <button
                type="submit"
                class="internal-upload-button search"
            >
                SEARCH
            </button>

            <a
                href="{{ route('mine-permit.monitoring-internal-upload') }}"
                class="internal-upload-button reset"
            >
                RESET
            </a>

            <a
                href="https://docs.google.com/spreadsheets/d/1mTNAEp3x80EvskHgVFl4mXv6OL78-0G9xbgVFLjqO6E/edit?gid=742696375#gid=742696375"
                target="_blank"
                rel="noopener noreferrer"
                class="internal-upload-button source"
            >
                SUMBER DATA
            </a>
        </form>

        <div class="internal-upload-statistics">

            <article class="internal-upload-stat total">
                <span class="internal-upload-stat-label">
                    Total Data
                </span>

                <strong class="internal-upload-stat-value">
                    {{ $totalData }}
                </strong>
            </article>

            <article class="internal-upload-stat complete">
                <span class="internal-upload-stat-label">
                    Dokumen Lengkap
                </span>

                <strong class="internal-upload-stat-value">
                    {{ $totalLengkap }}
                </strong>
            </article>

            <article class="internal-upload-stat incomplete">
                <span class="internal-upload-stat-label">
                    Belum Lengkap
                </span>

                <strong class="internal-upload-stat-value">
                    {{ $totalBelumLengkap }}
                </strong>
            </article>

            <article class="internal-upload-stat expired">
                <span class="internal-upload-stat-label">
                    Expired
                </span>

                <strong class="internal-upload-stat-value">
                    {{ $totalExpired }}
                </strong>
            </article>

        </div>

        <nav
            class="internal-upload-tabs"
            aria-label="Filter status dokumen"
        >
            <a
                href="{{ route('mine-permit.monitoring-internal-upload', [
                    'status' => 'semua',
                    'search' => request('search'),
                ]) }}"
                class="internal-upload-tab
                    {{ $selectedStatus === 'semua' ? 'active' : '' }}"
            >
                SEMUA
            </a>

            <a
                href="{{ route('mine-permit.monitoring-internal-upload', [
                    'status' => 'lengkap',
                    'search' => request('search'),
                ]) }}"
                class="internal-upload-tab
                    {{ $selectedStatus === 'lengkap' ? 'active' : '' }}"
            >
                LENGKAP
            </a>

            <a
                href="{{ route('mine-permit.monitoring-internal-upload', [
                    'status' => 'belum-lengkap',
                    'search' => request('search'),
                ]) }}"
                class="internal-upload-tab
                    {{ $selectedStatus === 'belum-lengkap' ? 'active' : '' }}"
            >
                BELUM LENGKAP
            </a>

            <a
                href="{{ route('mine-permit.monitoring-internal-upload', [
                    'status' => 'expired',
                    'search' => request('search'),
                ]) }}"
                class="internal-upload-tab
                    {{ $selectedStatus === 'expired' ? 'active' : '' }}"
            >
                EXPIRED
            </a>

        </nav>

        </div>

        <p class="internal-upload-result-info">
            Menampilkan {{ $filteredEmployees->count() }}
            dari {{ $totalData }} data karyawan
        </p>

        <div class="internal-upload-grid">

            @forelse ($filteredEmployees as $employee)

                @php
                    $percentage = $employee['total_dokumen'] > 0
                        ? round(
                            ($employee['dokumen_terisi'] /
                            $employee['total_dokumen']) * 100
                        )
                        : 0;

                    $statusLabel = match ($employee['status']) {
                        'lengkap' => 'Lengkap',
                        'expired' => 'Expired',
                        default => 'Belum Lengkap',
                    };
                @endphp

                <article class="internal-upload-card">

                    <div class="internal-upload-card-header">

                        <div class="internal-upload-identity">
                            <h3 class="internal-upload-name">
                                {{ $employee['nama'] }}
                            </h3>

                            <span class="internal-upload-nrp">
                                NRP: {{ $employee['nrp'] }}
                            </span>
                        </div>

                        <span
                            class="internal-upload-status
                                {{ $employee['status'] }}"
                        >
                            {{ $statusLabel }}
                        </span>

                    </div>

<dl class="internal-upload-information">

    <dt>Jabatan</dt>
    <dd>{{ $employee['jabatan'] }}</dd>

    <dt>Versatility</dt>
    <dd>{{ $employee['versatility'] }}</dd>

    <dt>Berlaku</dt>
    <dd>{{ $employee['tanggal_berlaku'] }}</dd>

    <dt>Upload terakhir</dt>
    <dd class="internal-upload-time">
        {{ $employee['uploaded_at'] }}
    </dd>

</dl>
                    <div class="internal-upload-progress-head">
                        <span>Kelengkapan dokumen</span>

                        <span>
                            {{ $employee['dokumen_terisi'] }}
                            /
                            {{ $employee['total_dokumen'] }}
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
                                $employee['documents']
                                as $documentName => $available
                            )

                                <li class="internal-upload-document">

                                    <span>
                                        {{ $documentName }}
                                    </span>

                                    <span
                                        class="internal-upload-document-status
                                            {{ $available
                                                ? 'available'
                                                : 'missing' }}"
                                    >
                                        {{ $available
                                            ? '✓ Tersedia'
                                            : '✕ Belum Upload' }}
                                    </span>

                                </li>

                            @endforeach

                        </ul>

                    </details>

                </article>

            @empty

                <div class="internal-upload-empty">
                    Data karyawan tidak ditemukan.
                </div>

            @endforelse

        </div>

    </section>

</div>