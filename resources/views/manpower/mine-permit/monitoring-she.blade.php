@php
    /*
    |--------------------------------------------------------------------------
    | DATA CONTOH UI
    |--------------------------------------------------------------------------
    | Data ini nanti dipindahkan ke MinePermitController dan dibaca
    | dari Google Spreadsheet.
    */

    $monitoringSheRows = [
        [
            'timestamp' => '07/06/2026 09:58:21',
            'nrp' => '00001',
            'nama' => 'Christine Brooks',
            'jabatan' => 'OPERATOR HD',
            'jenis_pengajuan' => 'SIM DLT',
            'status' => 'SELESAI',
        ],
        [
            'timestamp' => '07/06/2026 09:58:21',
            'nrp' => '00002',
            'nama' => 'Rosie Pearson',
            'jabatan' => 'OPERATOR DT',
            'jenis_pengajuan' => 'SIM DLT',
            'status' => 'SELESAI',
        ],
        [
            'timestamp' => '07/06/2026 09:58:21',
            'nrp' => '00003',
            'nama' => 'Darrell Caldwell',
            'jabatan' => 'OPERATOR DZ',
            'jenis_pengajuan' => 'SIM DLT',
            'status' => 'GAGAL',
        ],
        [
            'timestamp' => '07/06/2026 09:58:21',
            'nrp' => '00004',
            'nama' => 'Gilbert Johnston',
            'jabatan' => 'OPERATOR WT HD',
            'jenis_pengajuan' => 'SIM DLT',
            'status' => 'SELESAI',
        ],
        [
            'timestamp' => '07/06/2026 09:58:21',
            'nrp' => '00005',
            'nama' => 'Alan Cain',
            'jabatan' => 'OPERATOR DT 31-50 T',
            'jenis_pengajuan' => 'SIM DLT',
            'status' => 'SELESAI',
        ],
        [
            'timestamp' => '07/06/2026 09:58:21',
            'nrp' => '00006',
            'nama' => 'Alfred Murray',
            'jabatan' => 'OPERATOR VIBRO 20 T',
            'jenis_pengajuan' => 'SIM DLT',
            'status' => 'SELESAI',
        ],
        [
            'timestamp' => '07/06/2026 09:58:21',
            'nrp' => '00007',
            'nama' => 'Rochamat',
            'jabatan' => 'OPERATOR HD',
            'jenis_pengajuan' => 'SIM DLT',
            'status' => 'SELESAI',
        ],
        [
            'timestamp' => '07/06/2026 09:58:21',
            'nrp' => '00008',
            'nama' => 'Rosie Todd',
            'jabatan' => 'GROUP LEADER',
            'jenis_pengajuan' => 'SIB DLT',
            'status' => 'SELESAI',
        ],
    ];

    /*
    |--------------------------------------------------------------------------
    | FILTER DATA CONTOH
    |--------------------------------------------------------------------------
    */

    $search = strtolower(trim((string) request('search')));

    $filteredMonitoringSheRows = collect($monitoringSheRows)
        ->filter(function ($row) use ($search) {
            if ($search === '') {
                return true;
            }

            return
                str_contains(strtolower($row['timestamp']), $search) ||
                str_contains(strtolower($row['nrp']), $search) ||
                str_contains(strtolower($row['nama']), $search) ||
                str_contains(strtolower($row['jabatan']), $search) ||
                str_contains(strtolower($row['jenis_pengajuan']), $search) ||
                str_contains(strtolower($row['status']), $search);
        })
        ->values();

    /*
    |--------------------------------------------------------------------------
    | STATISTIK CONTOH UI
    |--------------------------------------------------------------------------
    | Angka ini tetap mengikuti desain Figma.
    | Nanti backend akan menghitungnya dari Spreadsheet.
    */

    $prosesPengajuanBulanIni = 25;
    $totalSelesai = 29;
    $totalGagal = 1;
@endphp

<style>
    .she-page {
        color: #111827;
        font-family: Arial, Helvetica, sans-serif;
    }

    .she-page-title {
        margin: 0 0 8px;
        font-size: 25px;
        font-weight: 800;
        line-height: 1.1;
    }

    .she-panel {
        position: relative;
        padding: 22px;
        overflow: visible;
        border-radius: 22px;
        background: #eeeeee;
    }

    /*
    |--------------------------------------------------------------------------
    | AREA STICKY
    |--------------------------------------------------------------------------
    | Bagian judul, pencarian, dan statistik tidak ikut turun saat
    | area konten di-scroll.
    */

    .she-sticky {
        position: sticky;
        z-index: 30;
        top: -22px;

        margin:
            -22px
            -22px
            14px;

        padding:
            22px
            22px
            12px;

        border-bottom: 1px solid #d6d9de;
        background: #eeeeee;
        box-shadow: 0 8px 14px rgba(15, 23, 42, 0.08);
    }

    .she-panel-title {
        margin: 0 0 20px;
        font-size: 16px;
        font-weight: 800;
    }

    .she-search-label {
        display: block;
        margin-bottom: 7px;
        font-size: 9px;
        font-weight: 600;
    }

    .she-search-row {
        display: grid;
        grid-template-columns:
            minmax(240px, 430px)
            105px
            115px;
        gap: 14px;
        align-items: center;
        margin-bottom: 18px;
    }

    .she-search-input {
        width: 100%;
        height: 37px;
        padding: 0 16px;
        border: 0;
        border-radius: 8px;
        outline: none;
        background: #ffffff;
        font-size: 11px;
    }

    .she-search-input:focus {
        box-shadow: 0 0 0 3px rgba(20, 125, 245, 0.14);
    }

    .she-button {
        display: inline-flex;
        height: 37px;
        align-items: center;
        justify-content: center;
        padding: 0 15px;
        border: 0;
        border-radius: 7px;
        color: #ffffff;
        cursor: pointer;
        font-size: 10px;
        font-weight: 800;
        text-decoration: none;
    }

    .she-button-search {
        background: #147df5;
    }

    .she-button-source {
        background: #686868;
    }

    .she-statistics {
        display: grid;
        max-width: 760px;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 0;
    }

    .she-stat-card {
        position: relative;
        min-height: 67px;
        padding: 10px 14px;
        overflow: hidden;
        border: 1px solid #b8b8b8;
        background: #ffffff;
    }

    .she-stat-card::after {
        position: absolute;
        right: 0;
        bottom: 0;
        left: 0;
        height: 4px;
        content: "";
    }

    .she-stat-card.proses::after {
        background: #555555;
    }

    .she-stat-card.selesai::after {
        background: #229423;
    }

    .she-stat-card.gagal::after {
        background: #ff202c;
    }

    .she-stat-label {
        display: block;
        margin-bottom: 7px;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .she-stat-value {
        display: block;
        font-size: 23px;
        font-weight: 800;
        line-height: 1;
        text-align: center;
    }

    .she-result-info {
        margin: 0 0 10px;
        color: #64748b;
        font-size: 9px;
        font-weight: 700;
    }

    .she-table-wrapper {
        overflow-x: auto;
        border: 1px solid #d7dce2;
        border-radius: 9px 9px 0 0;
        background: #ffffff;
    }

    .she-table {
        width: 100%;
        min-width: 850px;
        border-collapse: collapse;
    }

    .she-table th {
        padding: 9px 12px;
        border-bottom: 1px solid #d7dce2;
        color: #333333;
        background: #f9fafb;
        font-size: 9px;
        font-weight: 800;
        text-align: left;
        text-transform: uppercase;
    }

    .she-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #e5e7eb;
        color: #343434;
        font-size: 10px;
        white-space: nowrap;
    }

    .she-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .she-table tbody tr:hover {
        background: #f8fafc;
    }

    .she-status {
        display: inline-flex;
        min-width: 78px;
        min-height: 19px;
        align-items: center;
        justify-content: center;
        padding: 2px 9px;
        border-radius: 999px;
        color: #ffffff;
        font-size: 8px;
        font-weight: 800;
    }

    .she-status.selesai {
        background: #24915c;
    }

    .she-status.gagal {
        background: #ed1524;
    }

    .she-empty {
        padding: 26px 12px !important;
        color: #64748b !important;
        text-align: center;
        font-weight: 700;
    }

    @media (max-width: 850px) {
        .she-search-row,
        .she-statistics {
            grid-template-columns: 1fr;
        }

        .she-panel {
            padding: 16px;
            border-radius: 14px;
        }

        .she-sticky {
            top: -16px;

            margin:
                -16px
                -16px
                12px;

            padding:
                16px
                16px
                10px;
        }
    }
</style>

<div class="she-page">

    <h1 class="she-page-title">
        Mine Permit
    </h1>

    <section class="she-panel">

        <div class="she-sticky">

            <h2 class="she-panel-title">
                MONITORING MINE PERMIT SHE
            </h2>

            <form
                method="GET"
                action="{{ route('mine-permit.monitoring-she') }}"
            >
                <label
                    for="sheSearch"
                    class="she-search-label"
                >
                    Pencarian
                </label>

                <div class="she-search-row">

                    <input
                        id="sheSearch"
                        name="search"
                        type="search"
                        class="she-search-input"
                        placeholder="NRP/NAMA KARYAWAN"
                        value="{{ request('search') }}"
                    >

                    <button
                        type="submit"
                        class="she-button she-button-search"
                    >
                        SEARCH
                    </button>

                    <a
                        href="https://docs.google.com/spreadsheets/d/1IFufJElpiWRUcx96TwbktOjUm4_4qvhQpuUREWkp3c0/edit?gid=978127958#gid=978127958"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="she-button she-button-source"
                    >
                        SUMBER DATA
                    </a>

                </div>
            </form>

            <div class="she-statistics">

                <article class="she-stat-card proses">
                    <span class="she-stat-label">
                        Proses pengajuan bulan ini
                    </span>

                    <strong class="she-stat-value">
                        {{ $prosesPengajuanBulanIni }}
                    </strong>
                </article>

                <article class="she-stat-card selesai">
                    <span class="she-stat-label">
                        Status selesai
                    </span>

                    <strong class="she-stat-value">
                        {{ $totalSelesai }}
                    </strong>
                </article>

                <article class="she-stat-card gagal">
                    <span class="she-stat-label">
                        Status gagal
                    </span>

                    <strong class="she-stat-value">
                        {{ $totalGagal }}
                    </strong>
                </article>

            </div>

        </div>

        <p class="she-result-info">
            Menampilkan {{ $filteredMonitoringSheRows->count() }}
            dari {{ count($monitoringSheRows) }} data pengajuan
        </p>

        <div class="she-table-wrapper">

            <table class="she-table">

                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>NRP</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>Jenis Pengajuan</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($filteredMonitoringSheRows as $row)

                        <tr>
                            <td>{{ $row['timestamp'] }}</td>
                            <td>{{ $row['nrp'] }}</td>
                            <td>{{ $row['nama'] }}</td>
                            <td>{{ $row['jabatan'] }}</td>
                            <td>{{ $row['jenis_pengajuan'] }}</td>

                            <td>
                                <span
                                    class="she-status
                                        {{ strtolower($row['status']) === 'gagal'
                                            ? 'gagal'
                                            : 'selesai' }}"
                                >
                                    {{ $row['status'] }}
                                </span>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="6"
                                class="she-empty"
                            >
                                Data pengajuan tidak ditemukan.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </section>

</div>