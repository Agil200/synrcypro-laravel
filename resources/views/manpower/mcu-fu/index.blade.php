<style>
    .mcu-page {
        min-width: 0;
        color: #172033;
        font-family: Arial, Helvetica, sans-serif;
    }

    .mcu-card {
        overflow: hidden;
        border: 1px solid #d8dee7;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 4px 14px rgba(15, 23, 42, .05);
    }

    .mcu-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 20px;
        border-bottom: 1px solid #d8dee7;
    }

    .mcu-title {
        margin: 0 0 5px;
        font-size: 22px;
        font-weight: 900;
    }

    .mcu-subtitle {
        margin: 0;
        color: #667085;
        font-size: 11px;
    }

    .mcu-header-actions,
    .mcu-filter-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .mcu-button {
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

    .mcu-button.secondary { background: #4b5563; }
    .mcu-button.green { background: #16864d; }
    .mcu-button.blue { background: #1976d2; }

    .mcu-alert {
        margin: 14px 20px 0;
        padding: 11px 13px;
        border-radius: 8px;
        font-size: 10px;
        font-weight: 700;
        line-height: 1.5;
    }

    .mcu-alert.error {
        border: 1px solid #fecaca;
        color: #991b1b;
        background: #fef2f2;
    }

    .mcu-alert.success {
        border: 1px solid #bbf7d0;
        color: #166534;
        background: #f0fdf4;
    }

    .mcu-alert.warning {
        border: 1px solid #fde68a;
        color: #92400e;
        background: #fffbeb;
    }

    .mcu-toolbar {
        display: grid;
        grid-template-columns: minmax(210px, 1.25fr) repeat(3, minmax(150px, .7fr)) auto;
        gap: 10px;
        align-items: end;
        padding: 16px 20px;
        border-bottom: 1px solid #e4e8ef;
        background: #fafbfc;
    }

    .mcu-field {
        display: flex;
        min-width: 0;
        flex-direction: column;
        gap: 6px;
    }

    .mcu-field label {
        color: #475467;
        font-size: 9px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .mcu-input,
    .mcu-select {
        width: 100%;
        height: 38px;
        padding: 0 11px;
        border: 1px solid #cfd6df;
        border-radius: 8px;
        background: #fff;
        outline: none;
        font-size: 10px;
    }

    .mcu-stat-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
        padding: 16px 20px;
        background: #fff;
    }

    .mcu-stat {
        position: relative;
        min-height: 73px;
        padding: 12px 14px;
        overflow: hidden;
        border: 1px solid #d8dee7;
        border-radius: 10px;
        background: #fff;
    }

    .mcu-stat::after {
        position: absolute;
        right: 0;
        bottom: 0;
        left: 0;
        height: 4px;
        background: #667085;
        content: '';
    }

    .mcu-stat.hadir::after { background: #1976d2; }
    .mcu-stat.absen::after { background: #dc2626; }
    .mcu-stat.done::after { background: #8b5cf6; }
    .mcu-stat.filtered::after { background: #16864d; }

    .mcu-stat span {
        display: block;
        color: #667085;
        font-size: 8px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .mcu-stat strong {
        display: block;
        margin-top: 9px;
        font-size: 24px;
        line-height: 1;
        text-align: center;
    }

    .mcu-meta {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 0 20px 10px;
        color: #667085;
        font-size: 9px;
        font-weight: 700;
    }

    .mcu-table-wrap {
        width: 100%;
        max-height: calc(100vh - 465px);
        min-height: 330px;
        overflow: auto;
        border-top: 1px solid #e4e8ef;
        border-bottom: 1px solid #e4e8ef;
        scrollbar-gutter: stable;
    }

    .mcu-table {
        width: 100%;
        min-width: 1180px;
        border-collapse: collapse;
    }

    .mcu-table th {
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

    .mcu-table td {
        padding: 9px 11px;
        border-bottom: 1px solid #edf0f4;
        color: #344054;
        background: #fff;
        font-size: 10px;
        vertical-align: middle;
        white-space: nowrap;
    }

    .mcu-table tbody tr:hover td { background: #f7fbff; }

    .mcu-table th:nth-child(1),
    .mcu-table td:nth-child(1) {
        position: sticky;
        left: 0;
        z-index: 4;
        min-width: 55px;
    }

    .mcu-table th:nth-child(2),
    .mcu-table td:nth-child(2) {
        position: sticky;
        left: 55px;
        z-index: 4;
        min-width: 105px;
    }

    .mcu-table th:nth-child(3),
    .mcu-table td:nth-child(3) {
        position: sticky;
        left: 160px;
        z-index: 4;
        min-width: 220px;
        box-shadow: 4px 0 7px rgba(15, 23, 42, .07);
    }

    .mcu-table th:nth-child(-n+3) {
        z-index: 9;
        background: #f6f8fb;
    }

    .mcu-table td:nth-child(-n+3) { background: #fff; }
    .mcu-table tbody tr:hover td:nth-child(-n+3) { background: #f7fbff; }

    .mcu-name { font-weight: 900; }
    .mcu-nrp { font-family: Consolas, monospace; font-weight: 800; }

    .mcu-badge {
        display: inline-flex;
        min-width: 84px;
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

    .mcu-badge.hadir { color: #fff; background: #1976d2; }
    .mcu-badge.tidak-hadir { color: #fff; background: #dc2626; }
    .mcu-badge.review { color: #fff; background: #16864d; }
    .mcu-badge.done { color: #5b21b6; background: #ede9fe; }

    .mcu-empty {
        padding: 38px !important;
        color: #667085 !important;
        text-align: center;
        font-weight: 800;
    }

    .mcu-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 20px;
        color: #667085;
        font-size: 9px;
        font-weight: 700;
    }

    .mcu-pagination { display: flex; align-items: center; gap: 7px; }

    .mcu-page-link {
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

    .mcu-page-link.disabled {
        color: #98a2b3;
        background: #f2f4f7;
        pointer-events: none;
    }

    @media (max-width: 1150px) {
        .mcu-toolbar { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .mcu-stat-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }

    @media (max-width: 700px) {
        .mcu-header { align-items: flex-start; flex-direction: column; }
        .mcu-toolbar,
        .mcu-stat-grid { grid-template-columns: 1fr; }
        .mcu-meta,
        .mcu-footer { align-items: flex-start; flex-direction: column; }
    }
</style>

@php
    $normal = static function ($value): string {
        $value = strtoupper(trim((string) $value));
        return trim(preg_replace('/\s+/', ' ', $value) ?? $value);
    };
@endphp

<div class="mcu-page">
    <section class="mcu-card">
        <header class="mcu-header">
            <div>
                <h1 class="mcu-title">Monitoring MCU &amp; Follow Up</h1>
                <p class="mcu-subtitle">
                    Daftar kehadiran MCU karyawan dari Google Spreadsheet tab PRO.
                </p>
            </div>

            <div class="mcu-header-actions">
                @if ($sourceUrl)
                    <a
                        href="{{ $sourceUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mcu-button secondary"
                    >
                        Sumber Spreadsheet
                    </a>
                @endif

                @if (Route::has('google.oauth.redirect'))
                    <a
                        href="{{ route('google.oauth.redirect', ['return' => route('mcu-fu.index')]) }}"
                        class="mcu-button blue"
                    >
                        Hubungkan Google
                    </a>
                @endif

                <form method="POST" action="{{ route('mcu-fu.refresh') }}">
                    @csrf
                    <button type="submit" class="mcu-button green">
                        Sinkronkan Data
                    </button>
                </form>
            </div>
        </header>

        @if (session('success'))
            <div class="mcu-alert success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="mcu-alert error">{{ session('error') }}</div>
        @endif

        @if ($sheetError)
            <div class="mcu-alert error">
                Data Google Spreadsheet belum dapat dibaca: {{ $sheetError }}
            </div>
        @elseif ($isStale)
            <div class="mcu-alert warning">
                Google Spreadsheet sedang tidak dapat diakses. Dashboard memakai data sinkronisasi terakhir.
            </div>
        @endif

        <form method="GET" action="{{ route('mcu-fu.index') }}" class="mcu-toolbar">
            <div class="mcu-field">
                <label for="mcuSearch">Cari NRP / nama / jabatan</label>
                <input
                    type="search"
                    name="search"
                    id="mcuSearch"
                    class="mcu-input"
                    value="{{ $search }}"
                    placeholder="Ketik kata pencarian"
                >
            </div>

            <div class="mcu-field">
                <label for="mcuKehadiran">Kehadiran</label>
                <select name="kehadiran" id="mcuKehadiran" class="mcu-select">
                    <option value="">Semua kehadiran</option>
                    @foreach ($kehadiranOptions as $option)
                        <option value="{{ $option }}" @selected($selectedKehadiran === $option)>
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mcu-field">
                <label for="mcuKeterangan">Keterangan</label>
                <select name="keterangan" id="mcuKeterangan" class="mcu-select">
                    <option value="">Semua keterangan</option>
                    @foreach ($keteranganOptions as $option)
                        <option value="{{ $option }}" @selected($selectedKeterangan === $option)>
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mcu-field">
                <label for="mcuJenis">Jenis MCU</label>
                <select name="jenis_mcu" id="mcuJenis" class="mcu-select">
                    <option value="">Semua jenis MCU</option>
                    @foreach ($jenisMcuOptions as $option)
                        <option value="{{ $option }}" @selected($selectedJenisMcu === $option)>
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mcu-filter-actions">
                <button type="submit" class="mcu-button blue">Cari</button>
                <a href="{{ route('mcu-fu.index') }}" class="mcu-button secondary">Reset</a>
            </div>
        </form>

        <div class="mcu-stat-grid">
            <article class="mcu-stat">
                <span>Total data</span>
                <strong>{{ number_format($statistics['total']) }}</strong>
            </article>

            <article class="mcu-stat filtered">
                <span>Hasil filter</span>
                <strong>{{ number_format($statistics['filtered']) }}</strong>
            </article>

            <article class="mcu-stat hadir">
                <span>Hadir</span>
                <strong>{{ number_format($statistics['hadir']) }}</strong>
            </article>

            <article class="mcu-stat absen">
                <span>Tidak hadir</span>
                <strong>{{ number_format($statistics['tidak_hadir']) }}</strong>
            </article>

            <article class="mcu-stat done">
                <span>Done review</span>
                <strong>{{ number_format($statistics['done_review']) }}</strong>
            </article>
        </div>

        <div class="mcu-meta">
            <span>
                Menampilkan {{ $mcuRows->firstItem() ?? 0 }}–{{ $mcuRows->lastItem() ?? 0 }}
                dari {{ $mcuRows->total() }} data.
            </span>
            <span>Sinkronisasi terakhir: {{ $lastSyncedAt ?: '-' }}</span>
        </div>

        <div class="mcu-table-wrap">
            <table class="mcu-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NRP</th>
                        <th>Nama</th>
                        <th>Dept</th>
                        <th>Jabatan</th>
                        <th>Tanggal MCU</th>
                        <th>Kehadiran</th>
                        <th>Keterangan</th>
                        <th>Jenis MCU</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($mcuRows as $row)
                        @php
                            $hadirNormal = $normal($row['kehadiran'] ?? '');
                            $ketNormal = $normal($row['keterangan'] ?? '');

                            $hadirClass = str_contains($hadirNormal, 'TIDAK HADIR')
                                ? 'tidak-hadir'
                                : ($hadirNormal === 'HADIR' ? 'hadir' : '');

                            $ketClass = str_contains($ketNormal, 'DONE REVIEW')
                                ? 'done'
                                : (str_contains($ketNormal, 'REVIEW') ? 'review' : '');
                        @endphp

                        <tr>
                            <td>{{ $row['no'] ?: $row['sheet_row'] }}</td>
                            <td class="mcu-nrp">{{ $row['nrp'] ?: '-' }}</td>
                            <td class="mcu-name">{{ $row['nama'] ?: '-' }}</td>
                            <td>{{ $row['dept'] ?: '-' }}</td>
                            <td>{{ $row['jabatan'] ?: '-' }}</td>
                            <td>{{ $row['tanggal_mcu'] ?: '-' }}</td>
                            <td>
                                <span class="mcu-badge {{ $hadirClass }}">
                                    {{ $row['kehadiran'] ?: '-' }}
                                </span>
                            </td>
                            <td>
                                <span class="mcu-badge {{ $ketClass }}">
                                    {{ $row['keterangan'] ?: '-' }}
                                </span>
                            </td>
                            <td>{{ $row['jenis_mcu'] ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="mcu-empty">
                                Data MCU belum ditemukan. Hubungkan Google lalu tekan Sinkronkan Data.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <footer class="mcu-footer">
            <span>Halaman {{ $mcuRows->currentPage() }} dari {{ $mcuRows->lastPage() }}</span>

            <nav class="mcu-pagination" aria-label="Pagination MCU">
                <a
                    href="{{ $mcuRows->previousPageUrl() ?: '#' }}"
                    class="mcu-page-link {{ $mcuRows->onFirstPage() ? 'disabled' : '' }}"
                >
                    ‹ Sebelumnya
                </a>

                <a
                    href="{{ $mcuRows->nextPageUrl() ?: '#' }}"
                    class="mcu-page-link {{ $mcuRows->hasMorePages() ? '' : 'disabled' }}"
                >
                    Berikutnya ›
                </a>
            </nav>
        </footer>
    </section>
</div>
