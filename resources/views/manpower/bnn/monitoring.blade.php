<style>
    .bnn-page {
        min-width: 0;
        color: #172033;
        font-family: Arial, Helvetica, sans-serif;
    }

    .bnn-card {
        overflow: hidden;
        border: 1px solid #d8dee7;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 4px 14px rgba(15, 23, 42, .05);
    }

    .bnn-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 20px;
        border-bottom: 1px solid #d8dee7;
    }

    .bnn-title {
        margin: 0 0 5px;
        font-size: 22px;
        font-weight: 900;
    }

    .bnn-subtitle {
        margin: 0;
        color: #667085;
        font-size: 11px;
    }

    .bnn-header-actions,
    .bnn-filter-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .bnn-button {
        display: inline-flex;
        min-height: 37px;
        align-items: center;
        justify-content: center;
        padding: 0 13px;
        border: 0;
        border-radius: 8px;
        color: #fff;
        background: #db1725;
        cursor: pointer;
        font-size: 10px;
        font-weight: 900;
        text-decoration: none;
    }

    .bnn-button.secondary { background: #4b5563; }
    .bnn-button.green { background: #16864d; }
    .bnn-button.blue { background: #1976d2; }

    .bnn-alert {
        margin: 14px 20px 0;
        padding: 11px 13px;
        border-radius: 8px;
        font-size: 10px;
        font-weight: 700;
        line-height: 1.5;
    }

    .bnn-alert.error {
        border: 1px solid #fecaca;
        color: #991b1b;
        background: #fef2f2;
    }

    .bnn-alert.success {
        border: 1px solid #bbf7d0;
        color: #166534;
        background: #f0fdf4;
    }

    .bnn-alert.warning {
        border: 1px solid #fde68a;
        color: #92400e;
        background: #fffbeb;
    }

    .bnn-toolbar {
        display: grid;
        grid-template-columns: minmax(210px, 1.25fr) repeat(4, minmax(140px, .7fr)) auto;
        gap: 10px;
        align-items: end;
        padding: 16px 20px;
        border-bottom: 1px solid #e4e8ef;
        background: #fafbfc;
    }

    .bnn-field {
        display: flex;
        min-width: 0;
        flex-direction: column;
        gap: 6px;
    }

    .bnn-field label {
        color: #475467;
        font-size: 9px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .bnn-input,
    .bnn-select {
        width: 100%;
        height: 38px;
        padding: 0 11px;
        border: 1px solid #cfd6df;
        border-radius: 8px;
        background: #fff;
        outline: none;
        font-size: 10px;
    }

    .bnn-stat-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
        padding: 16px 20px;
        background: #fff;
    }

    .bnn-stat {
        position: relative;
        min-height: 73px;
        padding: 12px 14px;
        overflow: hidden;
        border: 1px solid #d8dee7;
        border-radius: 10px;
        background: #fff;
    }

    .bnn-stat::after {
        position: absolute;
        right: 0;
        bottom: 0;
        left: 0;
        height: 4px;
        background: #667085;
        content: '';
    }

    .bnn-stat.filtered::after { background: #16864d; }
    .bnn-stat.done::after { background: #1976d2; }
    .bnn-stat.pending::after { background: #dc2626; }
    .bnn-stat.mess::after { background: #f59e0b; }

    .bnn-stat span {
        display: block;
        color: #667085;
        font-size: 8px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .bnn-stat strong {
        display: block;
        margin-top: 9px;
        font-size: 24px;
        line-height: 1;
        text-align: center;
    }

    .bnn-meta {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 0 20px 10px;
        color: #667085;
        font-size: 9px;
        font-weight: 700;
    }

    .bnn-table-wrap {
        width: 100%;
        max-height: calc(100vh - 465px);
        min-height: 330px;
        overflow: auto;
        border-top: 1px solid #e4e8ef;
        border-bottom: 1px solid #e4e8ef;
        scrollbar-gutter: stable;
    }

    .bnn-table {
        width: 100%;
        min-width: 1760px;
        border-collapse: collapse;
    }

    .bnn-table th {
        position: sticky;
        z-index: 6;
        top: 0;
        padding: 10px 11px;
        border-bottom: 1px solid #d8dee7;
        color: #52637a;
        background: #f6f8fb;
        font-size: 9px;
        font-weight: 900;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .bnn-table td {
        padding: 9px 11px;
        border-bottom: 1px solid #edf0f4;
        color: #344054;
        background: #fff;
        font-size: 10px;
        vertical-align: middle;
        white-space: nowrap;
    }

    .bnn-table tbody tr:hover td { background: #fffaf8; }

    .bnn-table th:nth-child(1),
    .bnn-table td:nth-child(1) {
        position: sticky;
        left: 0;
        z-index: 4;
        min-width: 55px;
    }

    .bnn-table th:nth-child(2),
    .bnn-table td:nth-child(2) {
        position: sticky;
        left: 55px;
        z-index: 4;
        min-width: 105px;
    }

    .bnn-table th:nth-child(3),
    .bnn-table td:nth-child(3) {
        position: sticky;
        left: 160px;
        z-index: 4;
        min-width: 220px;
        box-shadow: 4px 0 7px rgba(15, 23, 42, .07);
    }

    .bnn-table th:nth-child(-n+3) {
        z-index: 9;
        background: #f6f8fb;
    }

    .bnn-table td:nth-child(-n+3) { background: #fff; }
    .bnn-table tbody tr:hover td:nth-child(-n+3) { background: #fffaf8; }

    .bnn-name { font-weight: 900; }
    .bnn-nrp { font-family: Consolas, monospace; font-weight: 800; }

    .bnn-badge {
        display: inline-flex;
        min-width: 94px;
        min-height: 22px;
        align-items: center;
        justify-content: center;
        padding: 3px 9px;
        border-radius: 999px;
        color: #374151;
        background: #e5e7eb;
        font-size: 8px;
        font-weight: 900;
        text-align: center;
    }

    .bnn-badge.done { color: #fff; background: #1976d2; }
    .bnn-badge.pending { color: #fff; background: #dc2626; }
    .bnn-badge.mess { color: #fff; background: #16864d; }
    .bnn-badge.sendiri { color: #fff; background: #2563eb; }
    .bnn-badge.bangko { color: #fff; background: #d97706; }

    .bnn-empty {
        padding: 38px !important;
        color: #667085 !important;
        text-align: center;
        font-weight: 800;
    }

    .bnn-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 20px;
        color: #667085;
        font-size: 9px;
        font-weight: 700;
    }

    .bnn-pagination {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .bnn-page-link {
        display: inline-flex;
        min-height: 30px;
        align-items: center;
        justify-content: center;
        padding: 0 10px;
        border: 1px solid #d0d5dd;
        border-radius: 7px;
        color: #344054;
        background: #fff;
        text-decoration: none;
    }

    .bnn-page-link.disabled {
        color: #98a2b3;
        background: #f2f4f7;
        pointer-events: none;
    }

    @media (max-width: 1150px) {
        .bnn-toolbar { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .bnn-stat-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }

    @media (max-width: 700px) {
        .bnn-header { align-items: flex-start; flex-direction: column; }
        .bnn-toolbar,
        .bnn-stat-grid { grid-template-columns: 1fr; }
        .bnn-meta,
        .bnn-footer { align-items: flex-start; flex-direction: column; }
    }
</style>

@php
    $normal = static function ($value): string {
        $value = strtoupper(trim((string) $value));
        $value = preg_replace('/[^A-Z0-9]+/u', ' ', $value) ?? $value;
        return trim(preg_replace('/\s+/', ' ', $value) ?? $value);
    };
@endphp

<div class="bnn-page">
    <section class="bnn-card">
        <header class="bnn-header">
            <div>
                <h1 class="bnn-title">Monitoring BNN &amp; Kehadiran</h1>
                <p class="bnn-subtitle">
                    Daftar pemeriksaan BNN karyawan dari Google Spreadsheet.
                </p>
            </div>

            <div class="bnn-header-actions">
                @if ($sourceUrl)
                    <a
                        href="{{ $sourceUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="bnn-button secondary"
                    >
                        Sumber Spreadsheet
                    </a>
                @endif

                @if (Route::has('google.oauth.redirect'))
                    <a
                        href="{{ route('google.oauth.redirect', ['return' => route('bnn.monitoring')]) }}"
                        class="bnn-button blue"
                    >
                        Hubungkan Google
                    </a>
                @endif

                <form method="POST" action="{{ route('bnn.refresh') }}">
                    @csrf
                    <button type="submit" class="bnn-button green">
                        Sinkronkan Data
                    </button>
                </form>
            </div>
        </header>

        @if (session('success'))
            <div class="bnn-alert success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="bnn-alert error">{{ session('error') }}</div>
        @endif

        @if ($sheetError)
            <div class="bnn-alert error">
                Data Google Spreadsheet belum dapat dibaca: {{ $sheetError }}
            </div>
        @elseif ($isStale)
            <div class="bnn-alert warning">
                Google Spreadsheet sedang tidak dapat diakses. Halaman memakai data sinkronisasi terakhir.
            </div>
        @endif

        <form method="GET" action="{{ route('bnn.monitoring') }}" class="bnn-toolbar">
            <div class="bnn-field">
                <label for="bnnSearch">Cari NRP / nama / posisi</label>
                <input
                    type="search"
                    name="search"
                    id="bnnSearch"
                    class="bnn-input"
                    value="{{ $search }}"
                    placeholder="Ketik kata pencarian"
                >
            </div>

            <div class="bnn-field">
                <label for="bnnStatus">Status test</label>
                <select name="status" id="bnnStatus" class="bnn-select">
                    <option value="">Semua status</option>
                    @foreach ($statusOptions as $option)
                        <option value="{{ $option }}" @selected($selectedStatus === $option)>
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="bnn-field">
                <label for="bnnAkomodasi">Akomodasi</label>
                <select name="akomodasi" id="bnnAkomodasi" class="bnn-select">
                    <option value="">Semua akomodasi</option>
                    @foreach ($akomodasiOptions as $option)
                        <option value="{{ $option }}" @selected($selectedAkomodasi === $option)>
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="bnn-field">
                <label for="bnnPerusahaan">Perusahaan</label>
                <select name="perusahaan" id="bnnPerusahaan" class="bnn-select">
                    <option value="">Semua perusahaan</option>
                    @foreach ($perusahaanOptions as $option)
                        <option value="{{ $option }}" @selected($selectedPerusahaan === $option)>
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="bnn-field">
                <label for="bnnTahun">Tahun pemeriksaan</label>
                <select name="tahun" id="bnnTahun" class="bnn-select">
                    <option value="">Semua tahun</option>
                    @foreach ($tahunOptions as $tahun)
                        <option
                            value="{{ $tahun }}"
                            @selected((string) $selectedTahun === (string) $tahun)
                        >
                            {{ $tahun }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="bnn-filter-actions">
                <button type="submit" class="bnn-button blue">Cari</button>
                <a href="{{ route('bnn.monitoring') }}" class="bnn-button secondary">Reset</a>
            </div>
        </form>

        <div class="bnn-stat-grid">
            <article class="bnn-stat">
                <span>Total data</span>
                <strong>{{ number_format($statistics['total']) }}</strong>
            </article>

            <article class="bnn-stat filtered">
                <span>Hasil filter</span>
                <strong>{{ number_format($statistics['filtered']) }}</strong>
            </article>

            <article class="bnn-stat done">
                <span>Sudah test</span>
                <strong>{{ number_format($statistics['sudah_test']) }}</strong>
            </article>

            <article class="bnn-stat pending">
                <span>Belum test</span>
                <strong>{{ number_format($statistics['belum_test']) }}</strong>
            </article>

            <article class="bnn-stat mess">
                <span>Diantar di mess</span>
                <strong>{{ number_format($statistics['diantar_mess']) }}</strong>
            </article>
        </div>

        <div class="bnn-meta">
            <span>
                Menampilkan {{ $bnnRows->firstItem() ?? 0 }}–{{ $bnnRows->lastItem() ?? 0 }}
                dari {{ $bnnRows->total() }} data.
            </span>
            <span>Sinkronisasi terakhir: {{ $lastSyncedAt ?: '-' }}</span>
        </div>

        <div class="bnn-table-wrap">
            <table class="bnn-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NRP</th>
                        <th>Nama</th>
                        <th>Jenis Kelamin</th>
                        <th>Perusahaan</th>
                        <th>Dept</th>
                        <th>Posisi</th>
                        <th>Usia</th>
                        <th>Kontak</th>
                        <th>NIK</th>
                        <th>Tanggal Pemeriksaan</th>
                        <th>Status</th>
                        <th>Akomodasi</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($bnnRows as $row)
                        @php
                            $statusNormal = $normal($row['status_test'] ?? '');
                            $akomodasiNormal = $normal($row['akomodasi'] ?? '');

                            $statusClass = $statusNormal === 'SUDAH TEST'
                                ? 'done'
                                : ($statusNormal === 'BELUM TEST' ? 'pending' : '');

                            $akomodasiClass = $akomodasiNormal === 'DIANTAR DI MESS'
                                ? 'mess'
                                : ($akomodasiNormal === 'BERANGKAT SENDIRI'
                                    ? 'sendiri'
                                    : ($akomodasiNormal === 'BANGKO' ? 'bangko' : ''));
                        @endphp

                        <tr>
                            <td>{{ $row['no'] ?: $row['sheet_row'] }}</td>
                            <td class="bnn-nrp">{{ $row['nrp'] ?: '-' }}</td>
                            <td class="bnn-name">{{ $row['nama'] ?: '-' }}</td>
                            <td>{{ $row['jenis_kelamin'] ?: '-' }}</td>
                            <td>{{ $row['perusahaan'] ?: '-' }}</td>
                            <td>{{ $row['dept'] ?: '-' }}</td>
                            <td>{{ $row['posisi'] ?: '-' }}</td>
                            <td>{{ $row['usia'] ?: '-' }}</td>
                            <td>{{ $row['kontak'] ?: '-' }}</td>
                            <td>{{ $row['nik'] ?: '-' }}</td>
                            <td>{{ $row['tanggal_pemeriksaan'] ?: '-' }}</td>
                            <td>
                                <span class="bnn-badge {{ $statusClass }}">
                                    {{ $row['status_test'] ?: '-' }}
                                </span>
                            </td>
                            <td>
                                <span class="bnn-badge {{ $akomodasiClass }}">
                                    {{ $row['akomodasi'] ?: '-' }}
                                </span>
                            </td>
                            <td>{{ $row['keterangan'] ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="bnn-empty">
                                Data BNN belum ditemukan. Hubungkan Google lalu tekan Sinkronkan Data.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <footer class="bnn-footer">
            <span>Halaman {{ $bnnRows->currentPage() }} dari {{ $bnnRows->lastPage() }}</span>

            <nav class="bnn-pagination" aria-label="Pagination BNN">
                <a
                    href="{{ $bnnRows->previousPageUrl() ?: '#' }}"
                    class="bnn-page-link {{ $bnnRows->onFirstPage() ? 'disabled' : '' }}"
                >
                    ‹ Sebelumnya
                </a>

                <a
                    href="{{ $bnnRows->nextPageUrl() ?: '#' }}"
                    class="bnn-page-link {{ $bnnRows->hasMorePages() ? '' : 'disabled' }}"
                >
                    Berikutnya ›
                </a>
            </nav>
        </footer>
    </section>
</div>