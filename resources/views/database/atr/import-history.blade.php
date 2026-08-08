@php
    $historyFilters = $historyFilters ?? [
        'year' => request('year', ''),
        'month' => request('month', ''),
        'status' => request('status', ''),
        'search' => request('search', ''),
    ];

    $yearOptions = $yearOptions ?? collect();
    $monthsByYear = $monthsByYear ?? [];

    $monthLabels = [
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maret',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Agustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember',
    ];

    $statusLabel = static function (?string $status): string {
        return match (strtoupper((string) $status)) {
            'COMPLETED' => 'AKTIF',
            'REPLACED' => 'DIGANTI',
            'CANCELLED' => 'DIBATALKAN',
            'PROCESSING' => 'DIPROSES',
            'FAILED' => 'GAGAL',
            default => strtoupper((string) $status) ?: '-',
        };
    };

    $hasActiveFilter =
        ($historyFilters['year'] ?? '') !== '' ||
        ($historyFilters['month'] ?? '') !== '' ||
        ($historyFilters['status'] ?? '') !== '' ||
        ($historyFilters['search'] ?? '') !== '';
@endphp

<style>
.atri-title{margin-bottom:12px}.atri-title h1{font-size:24px;margin:0;color:#10213d}.atri-title p{margin:3px 0 0;color:#60708a;font-size:12px}.atri-card{background:#fff;border:1px solid #d9e2ee;border-radius:14px;box-shadow:0 6px 18px rgba(15,35,65,.06);overflow:hidden}.atri-head{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:14px 16px;border-bottom:1px solid #e1e8f1}.atri-head h2{font-size:15px;margin:0;color:#172640}.atri-head small{display:block;margin-top:4px;color:#74839a;font-size:9px}.atri-btn{height:36px;border-radius:9px;padding:0 14px;background:#1677ff;color:#fff;text-decoration:none;font-size:10px;font-weight:800;display:inline-flex;align-items:center;justify-content:center;border:0;cursor:pointer;white-space:nowrap}.atri-filter{padding:13px 16px;border-bottom:1px solid #e7edf4;background:#fbfcfe}.atri-quick{display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-bottom:10px}.atri-quick-label{font-size:8px;font-weight:900;color:#66778d;text-transform:uppercase;margin-right:2px}.atri-quick-btn{display:inline-flex;height:29px;align-items:center;padding:0 10px;border-radius:8px;border:1px solid #d6e0eb;background:#fff;color:#40536e;text-decoration:none;font-size:8px;font-weight:900}.atri-quick-btn:hover{border-color:#1677ff;color:#1677ff}.atri-quick-btn.active{background:#eaf3ff;border-color:#8bbcff;color:#145fb8}.atri-filter-grid{display:grid;grid-template-columns:150px 170px 185px minmax(260px,1fr) auto auto;gap:9px;align-items:end}.atri-field label{display:block;margin:0 0 5px;color:#566a84;font-size:8px;font-weight:900;text-transform:uppercase;letter-spacing:.04em}.atri-control{width:100%;height:36px;padding:0 10px;border:1px solid #cbd7e6;border-radius:9px;background:#fff;color:#172640;font-size:10px;outline:none}.atri-control:focus{border-color:#1677ff;box-shadow:0 0 0 3px rgba(22,119,255,.10)}.atri-filter-btn,.atri-reset-btn{height:36px;padding:0 14px;border-radius:9px;font-size:9px;font-weight:900;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;white-space:nowrap}.atri-filter-btn{border:0;background:#1677ff;color:#fff;cursor:pointer}.atri-reset-btn{border:1px solid #d4deea;background:#f2f5f9;color:#344760}.atri-filter-meta{display:flex;justify-content:space-between;gap:10px;margin-top:9px;color:#7a889a;font-size:8px}.atri-active-filter{display:inline-flex;gap:5px;flex-wrap:wrap;justify-content:flex-end}.atri-chip{display:inline-flex;padding:3px 7px;border-radius:999px;background:#edf4fc;color:#45617f;font-weight:800}.atri-page-shell{height:100%;min-height:0;display:flex;flex-direction:column;overflow:hidden}
.atri-history-card{flex:1 1 auto;min-height:0;display:flex;flex-direction:column;overflow:hidden}
.atri-history-card>.atri-head,
.atri-history-card>.atri-filter,
.atri-history-card>.atri-pagination{flex:0 0 auto}
.atri-wrap{flex:1 1 auto;min-height:0;overflow:auto;overscroll-behavior:contain;scrollbar-gutter:stable}
.atri-table{width:100%;border-collapse:collapse;table-layout:fixed;min-width:860px}.atri-table th{position:sticky;top:0;z-index:4;background:#f6f8fb;padding:10px 12px;font-size:8px;color:#596a80;text-transform:uppercase;text-align:left;letter-spacing:.04em;box-shadow:0 1px 0 #dfe7f1}.atri-table td{padding:12px;border-top:1px solid #e7edf4;font-size:9px;color:#314761;vertical-align:middle}.atri-table th:nth-child(1),.atri-table td:nth-child(1){width:39%}.atri-table th:nth-child(2),.atri-table td:nth-child(2){width:15%}.atri-table th:nth-child(3),.atri-table td:nth-child(3){width:16%}.atri-table th:nth-child(4),.atri-table td:nth-child(4){width:19%}.atri-table th:nth-child(5),.atri-table td:nth-child(5){width:11%;text-align:right}.atri-name{font-size:10px;font-weight:900;color:#172640;word-break:break-word}.atri-file-meta{display:flex;flex-wrap:wrap;gap:5px 9px;margin-top:5px;color:#718096;font-size:8px;line-height:1.5}.atri-file-meta strong{color:#4a5c73}.atri-mode{display:inline-flex;align-items:center;justify-content:center;padding:5px 8px;border-radius:999px;font-size:8px;font-weight:900;white-space:nowrap}.atri-mode.NEW{background:#e8f2ff;color:#175fae}.atri-mode.REPLACE{background:#fff0d8;color:#9a5d00}.atri-mode.APPEND{background:#e8f8ef;color:#087845}.atri-period-main{font-size:10px;font-weight:900;color:#213854}.atri-period-code{margin-top:3px;color:#8a98aa;font-size:7px}.atri-badge{display:inline-flex;padding:5px 8px;border-radius:999px;font-size:8px;font-weight:900}.atri-badge.COMPLETED{background:#dff7ea;color:#078446}.atri-badge.PROCESSING{background:#e7f1ff;color:#1b62b1}.atri-badge.FAILED{background:#ffe0e4;color:#c61f35}.atri-badge.CANCELLED{background:#edf1f5;color:#596779}.atri-badge.REPLACED{background:#fff1c9;color:#9d6900}.atri-status-note{margin-top:5px;color:#738095;font-size:8px;line-height:1.45}.atri-status-note strong{color:#566479}.atri-empty{flex:1 1 auto;min-height:220px;display:grid;place-items:center;padding:45px;text-align:center;color:#7b899c}.atri-flash{padding:11px 14px;border-radius:10px;margin-bottom:10px;font-size:11px}.atri-flash.success{background:#e5f8ed;color:#087b42;border:1px solid #bcebd0}.atri-flash.error{background:#ffe8eb;color:#b11d32;border:1px solid #ffc8d0}.atri-cancel-btn{height:30px;padding:0 10px;border:1px solid #e6384b;border-radius:8px;background:#fff;color:#c52739;font-size:8px;font-weight:900;cursor:pointer;white-space:nowrap}.atri-cancel-btn:hover{background:#fff0f2}.atri-detail-btn{height:30px;padding:0 11px;border:1px solid #9fb4ce;border-radius:8px;background:#fff;color:#31577e;font-size:8px;font-weight:900;cursor:pointer;white-space:nowrap}.atri-detail-btn:hover{background:#eef5fc;border-color:#6d9dd2}.atri-no-action{color:#9aa6b4;font-size:8px;font-weight:800}.atri-detail-grid{display:grid;grid-template-columns:145px minmax(0,1fr);border:1px solid #e1e8f1;border-radius:11px;overflow:hidden}.atri-detail-grid>div{padding:9px 11px;border-bottom:1px solid #e7edf4;font-size:9px;line-height:1.45}.atri-detail-grid>div:nth-last-child(-n+2){border-bottom:0}.atri-detail-label{background:#f7f9fc;color:#64748b;font-weight:900}.atri-detail-value{color:#172640;font-weight:800;word-break:break-word}.atri-detail-reason{margin-top:12px;padding:11px;border-radius:10px;background:#f8fafc;border:1px solid #e1e8f1}.atri-detail-reason small{display:block;color:#64748b;font-size:8px;font-weight:900;text-transform:uppercase;margin-bottom:5px}.atri-detail-reason p{margin:0;color:#253b58;font-size:10px;line-height:1.55;white-space:pre-wrap}.atri-pagination{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 16px;border-top:1px solid #e7edf4;background:#fff}.atri-pagination-info{color:#6f7e91;font-size:8px}.atri-page-list{display:flex;align-items:center;gap:5px}.atri-page{min-width:30px;height:30px;padding:0 8px;border:1px solid #d4deea;border-radius:8px;background:#fff;color:#344760;text-decoration:none;font-size:9px;font-weight:900;display:inline-flex;align-items:center;justify-content:center}.atri-page:hover{border-color:#1677ff;color:#1677ff}.atri-page.active{border-color:#1677ff;background:#1677ff;color:#fff}.atri-page.disabled{pointer-events:none;opacity:.4}.atri-modal{position:fixed;inset:0;background:rgba(7,18,35,.63);display:none;align-items:center;justify-content:center;z-index:5100;padding:16px}.atri-modal.open{display:flex}.atri-dialog{background:#fff;border-radius:16px;width:min(520px,100%);box-shadow:0 25px 70px rgba(0,0,0,.28);overflow:hidden}.atri-modal-head{display:flex;align-items:center;justify-content:space-between;padding:16px 18px;border-bottom:1px solid #e1e8f1}.atri-modal-head h2{font-size:16px;color:#172640;margin:0}.atri-close{border:0;background:none;font-size:22px;color:#8a98aa;cursor:pointer}.atri-modal-body{padding:17px}.atri-warning{padding:12px;border:1px solid #ffd2d8;background:#fff4f5;color:#8e2431;border-radius:10px;font-size:10px;line-height:1.55;margin-bottom:12px}.atri-summary{background:#f5f8fc;border-radius:10px;padding:11px;margin-bottom:12px}.atri-summary div{display:flex;justify-content:space-between;gap:15px;font-size:10px;margin-bottom:5px}.atri-summary div:last-child{margin-bottom:0}.atri-summary span{color:#738095}.atri-summary b{color:#1c304c;text-align:right}.atri-textarea{width:100%;min-height:95px;padding:10px;border:1px solid #cbd7e6;border-radius:9px;resize:vertical;font:inherit}.atri-modal-actions{display:flex;justify-content:flex-end;gap:8px;padding:13px 17px;border-top:1px solid #e1e8f1}.atri-back,.atri-danger{height:34px;padding:0 13px;border-radius:8px;font-size:9px;font-weight:900;cursor:pointer}.atri-back{border:1px solid #d4deea;background:#fff;color:#41536b}.atri-danger{border:0;background:#e6384b;color:#fff}
@media(max-width:1200px){.atri-filter-grid{grid-template-columns:1fr 1fr 1fr}.atri-search-field{grid-column:span 2}}@media(max-height:720px) and (min-width:761px){.atri-page-shell{height:auto;overflow:visible}.atri-history-card{min-height:520px}.atri-wrap{max-height:360px}}
@media(max-width:760px){.atri-page-shell{height:auto;overflow:visible}.atri-history-card{display:block}.atri-wrap{max-height:440px}.atri-head,.atri-pagination{align-items:flex-start;flex-direction:column}.atri-filter-grid{grid-template-columns:1fr}.atri-search-field{grid-column:auto}.atri-filter-meta{align-items:flex-start;flex-direction:column}.atri-active-filter{justify-content:flex-start}}
</style>

<div class="atri-page-shell">
<div class="atri-title">
    <h1>Riwayat Import ATR</h1>
    <p>Audit import ATR dengan filter tahun, bulan, status, pencarian file, dan pagination.</p>
</div>

@if (session('success'))
    <div class="atri-flash success">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="atri-flash error">{{ $errors->first() }}</div>
@endif

<section class="atri-card atri-history-card">
    <div class="atri-head">
        <div>
            <h2>Riwayat Import</h2>
            <small>Maksimal 10 riwayat per halaman. Data CANCELLED dan REPLACED tetap disimpan untuk audit.</small>
        </div>
        <a href="{{ route('database.atr.upload') }}" class="atri-btn">UPLOAD BARU</a>
    </div>

    <form method="GET" action="{{ route('database.atr.history') }}" class="atri-filter" id="atriHistoryFilterForm">
        <div class="atri-quick">
            <span class="atri-quick-label">Filter Cepat</span>
            <a href="{{ route('database.atr.history') }}" class="atri-quick-btn {{ ! $hasActiveFilter ? 'active' : '' }}">SEMUA</a>
            <a href="{{ route('database.atr.history', ['year' => now()->format('Y')]) }}" class="atri-quick-btn {{ ($historyFilters['year'] ?? '') === now()->format('Y') && ($historyFilters['month'] ?? '') === '' ? 'active' : '' }}">TAHUN INI</a>
            <a href="{{ route('database.atr.history', ['year' => now()->format('Y'), 'month' => now()->format('m')]) }}" class="atri-quick-btn {{ ($historyFilters['year'] ?? '') === now()->format('Y') && ($historyFilters['month'] ?? '') === now()->format('m') ? 'active' : '' }}">BULAN INI</a>
        </div>

        <div class="atri-filter-grid">
            <div class="atri-field">
                <label for="atriHistoryYear">Tahun</label>
                <select name="year" id="atriHistoryYear" class="atri-control">
                    <option value="">Semua Tahun</option>
                    @foreach ($yearOptions as $yearOption)
                        <option value="{{ $yearOption }}" @selected(($historyFilters['year'] ?? '') === $yearOption)>{{ $yearOption }}</option>
                    @endforeach
                </select>
            </div>

            <div class="atri-field">
                <label for="atriHistoryMonth">Bulan</label>
                <select name="month" id="atriHistoryMonth" class="atri-control" data-selected="{{ $historyFilters['month'] ?? '' }}">
                    <option value="">Semua Bulan</option>
                    @foreach ($monthLabels as $monthValue => $monthLabel)
                        <option value="{{ $monthValue }}" @selected(($historyFilters['month'] ?? '') === $monthValue)>{{ $monthLabel }}</option>
                    @endforeach
                </select>
            </div>

            <div class="atri-field">
                <label for="atriHistoryStatus">Status</label>
                <select name="status" id="atriHistoryStatus" class="atri-control">
                    <option value="">Semua Status</option>
                    <option value="COMPLETED" @selected(($historyFilters['status'] ?? '') === 'COMPLETED')>Aktif</option>
                    <option value="REPLACED" @selected(($historyFilters['status'] ?? '') === 'REPLACED')>Diganti</option>
                    <option value="CANCELLED" @selected(($historyFilters['status'] ?? '') === 'CANCELLED')>Dibatalkan</option>
                    <option value="PROCESSING" @selected(($historyFilters['status'] ?? '') === 'PROCESSING')>Diproses</option>
                    <option value="FAILED" @selected(($historyFilters['status'] ?? '') === 'FAILED')>Gagal</option>
                </select>
            </div>

            <div class="atri-field atri-search-field">
                <label for="atriHistorySearch">Cari File</label>
                <input type="search" name="search" id="atriHistorySearch" class="atri-control" value="{{ $historyFilters['search'] ?? '' }}" placeholder="Contoh: REV01, 2026-07, ATR_PRODUKSI..." maxlength="150">
            </div>

            <button type="submit" class="atri-filter-btn">TERAPKAN</button>
            <a href="{{ route('database.atr.history') }}" class="atri-reset-btn">RESET</a>
        </div>

        <div class="atri-filter-meta">
            <span>{{ number_format($imports->total()) }} riwayat ditemukan</span>
            <span class="atri-active-filter">
                @if (($historyFilters['year'] ?? '') !== '')
                    <span class="atri-chip">Tahun {{ $historyFilters['year'] }}</span>
                @endif
                @if (($historyFilters['month'] ?? '') !== '')
                    <span class="atri-chip">{{ $monthLabels[$historyFilters['month']] ?? $historyFilters['month'] }}</span>
                @endif
                @if (($historyFilters['status'] ?? '') !== '')
                    <span class="atri-chip">{{ $statusLabel($historyFilters['status']) }}</span>
                @endif
                @if (($historyFilters['search'] ?? '') !== '')
                    <span class="atri-chip">Cari: {{ $historyFilters['search'] }}</span>
                @endif
                @if (! $hasActiveFilter)
                    <span>Menampilkan seluruh riwayat</span>
                @endif
            </span>
        </div>
    </form>

    @if ($imports->isEmpty())
        <div class="atri-empty">
            {{ $hasActiveFilter ? 'Riwayat import tidak ditemukan pada filter ini.' : 'Belum ada file ATR yang diimpor.' }}
        </div>
    @else
        <div class="atri-wrap">
            <table class="atri-table">
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Periode</th>
                        <th>Mode</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($imports as $import)
                    @php
                        $modeValue = strtoupper((string) ($import->import_mode ?: 'NEW'));
                        $modeLabel = match ($modeValue) {
                            'REPLACE' => 'REVISI / GANTI',
                            'APPEND' => 'TAMBAH NRP',
                            default => 'PERIODE BARU',
                        };
                    @endphp
                    <tr>
                        <td>
                            <div class="atri-name">{{ $import->file_name }}</div>
                            <div class="atri-file-meta">
                                <span><strong>{{ number_format($import->imported_rows) }}</strong> baris</span>
                                <span>Uploader: <strong>{{ $import->uploader?->name ?? $import->uploader?->email ?? '-' }}</strong></span>
                                <span>{{ ($import->imported_at ?? $import->created_at)?->locale('id')->translatedFormat('d M Y H:i') }}</span>
                                @if ($import->replaces_import_id)
                                    <span>Revisi import <strong>#{{ $import->replaces_import_id }}</strong></span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if ($import->period_min)
                                <div class="atri-period-main">{{ $import->period_min->locale('id')->translatedFormat('F Y') }}</div>
                                <div class="atri-period-code">{{ $import->period_min->format('Y-m') }}</div>
                                @if ($import->period_max && ! $import->period_max->equalTo($import->period_min))
                                    <div class="atri-status-note">s/d {{ $import->period_max->locale('id')->translatedFormat('F Y') }}</div>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <span class="atri-mode {{ $modeValue }}">{{ $modeLabel }}</span>
                            @if ($modeValue === 'REPLACE' && $import->replaces_import_id)
                                <div class="atri-status-note">Mengganti import <strong>#{{ $import->replaces_import_id }}</strong></div>
                            @elseif ($modeValue === 'APPEND')
                                <div class="atri-status-note">Menambahkan NRP baru ke periode aktif.</div>
                            @else
                                <div class="atri-status-note">Upload awal untuk periode ini.</div>
                            @endif
                        </td>
                        <td>
                            <span class="atri-badge {{ $import->status }}">{{ $statusLabel($import->status) }}</span>
                            @if ($import->status === 'REPLACED')
                                <div class="atri-status-note">Digantikan oleh:<br><strong>{{ $import->replacementImport?->file_name ?? 'import revisi berikutnya' }}</strong></div>
                            @endif
                            @if ($import->status === 'CANCELLED')
                                <div class="atri-status-note">
                                    <strong>{{ $import->cancelled_at?->locale('id')->translatedFormat('d M Y H:i') ?? '-' }}</strong><br>
                                    Oleh: {{ $import->canceller?->name ?? $import->canceller?->email ?? '-' }}<br>
                                    Alasan: {{ $import->cancellation_reason ?: '-' }}
                                </div>
                            @endif
                        </td>
                        <td>
                            @if ($import->status === 'COMPLETED')
                                <button
                                    type="button"
                                    class="atri-cancel-btn"
                                    data-cancel-import
                                    data-url="{{ route('database.atr.imports.cancel', $import) }}"
                                    data-file="{{ $import->file_name }}"
                                    data-period="{{ $import->period_min?->locale('id')->translatedFormat('F Y') ?? '-' }}"
                                    data-total="{{ $import->imported_rows }}"
                                >BATALKAN IMPORT</button>
                            @elseif ($import->status === 'CANCELLED')
                                <button
                                    type="button"
                                    class="atri-detail-btn"
                                    data-import-detail
                                    data-id="{{ $import->id }}"
                                    data-file="{{ $import->file_name }}"
                                    data-period="{{ $import->period_min?->locale('id')->translatedFormat('F Y') ?? '-' }}"
                                    data-period-code="{{ $import->period_min?->format('Y-m') ?? '-' }}"
                                    data-mode="{{ $modeLabel }}"
                                    data-status="{{ $statusLabel($import->status) }}"
                                    data-total="{{ $import->total_rows }}"
                                    data-valid="{{ $import->valid_rows }}"
                                    data-invalid="{{ $import->invalid_rows }}"
                                    data-imported="{{ $import->imported_rows }}"
                                    data-uploader="{{ $import->uploader?->name ?? $import->uploader?->email ?? '-' }}"
                                    data-imported-at="{{ ($import->imported_at ?? $import->created_at)?->locale('id')->translatedFormat('d F Y H:i') ?? '-' }}"
                                    data-cancelled-at="{{ $import->cancelled_at?->locale('id')->translatedFormat('d F Y H:i') ?? '-' }}"
                                    data-cancelled-by="{{ $import->canceller?->name ?? $import->canceller?->email ?? '-' }}"
                                    data-reason="{{ $import->cancellation_reason ?: '-' }}"
                                >DETAIL</button>
                            @elseif ($import->status === 'REPLACED')
                                <button
                                    type="button"
                                    class="atri-detail-btn"
                                    data-import-detail
                                    data-id="{{ $import->id }}"
                                    data-file="{{ $import->file_name }}"
                                    data-period="{{ $import->period_min?->locale('id')->translatedFormat('F Y') ?? '-' }}"
                                    data-period-code="{{ $import->period_min?->format('Y-m') ?? '-' }}"
                                    data-mode="{{ $modeLabel }}"
                                    data-status="{{ $statusLabel($import->status) }}"
                                    data-total="{{ $import->total_rows }}"
                                    data-valid="{{ $import->valid_rows }}"
                                    data-invalid="{{ $import->invalid_rows }}"
                                    data-imported="{{ $import->imported_rows }}"
                                    data-uploader="{{ $import->uploader?->name ?? $import->uploader?->email ?? '-' }}"
                                    data-imported-at="{{ ($import->imported_at ?? $import->created_at)?->locale('id')->translatedFormat('d F Y H:i') ?? '-' }}"
                                    data-cancelled-at="-"
                                    data-cancelled-by="-"
                                    data-reason="Import ini telah diganti oleh snapshot/revisi berikutnya."
                                >DETAIL</button>
                            @else
                                <span class="atri-no-action">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="atri-pagination">
            <div class="atri-pagination-info">
                Menampilkan {{ number_format($imports->firstItem() ?? 0) }}–{{ number_format($imports->lastItem() ?? 0) }} dari {{ number_format($imports->total()) }} riwayat
            </div>

            @if ($imports->hasPages())
                @php
                    $currentPage = $imports->currentPage();
                    $lastPage = $imports->lastPage();
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($lastPage, $currentPage + 2);
                @endphp

                <div class="atri-page-list" aria-label="Pagination riwayat import ATR">
                    <a href="{{ $imports->previousPageUrl() ?: '#' }}" class="atri-page {{ $imports->onFirstPage() ? 'disabled' : '' }}">‹</a>
                    @if ($startPage > 1)
                        <a href="{{ $imports->url(1) }}" class="atri-page">1</a>
                        @if ($startPage > 2)<span class="atri-page disabled">…</span>@endif
                    @endif
                    @for ($page = $startPage; $page <= $endPage; $page++)
                        <a href="{{ $imports->url($page) }}" class="atri-page {{ $page === $currentPage ? 'active' : '' }}">{{ $page }}</a>
                    @endfor
                    @if ($endPage < $lastPage)
                        @if ($endPage < $lastPage - 1)<span class="atri-page disabled">…</span>@endif
                        <a href="{{ $imports->url($lastPage) }}" class="atri-page">{{ $lastPage }}</a>
                    @endif
                    <a href="{{ $imports->nextPageUrl() ?: '#' }}" class="atri-page {{ $imports->hasMorePages() ? '' : 'disabled' }}">›</a>
                </div>
            @endif
        </div>
    @endif
</section>
</div>

<div class="atri-modal" id="atriDetailModal" aria-hidden="true">
    <div class="atri-dialog">
        <div class="atri-modal-head">
            <h2>Detail Riwayat Import ATR</h2>
            <button type="button" class="atri-close" id="atriDetailClose">×</button>
        </div>

        <div class="atri-modal-body">
            <div class="atri-detail-grid">
                <div class="atri-detail-label">Import ID</div>
                <div class="atri-detail-value" id="atriDetailId">-</div>

                <div class="atri-detail-label">Nama File</div>
                <div class="atri-detail-value" id="atriDetailFile">-</div>

                <div class="atri-detail-label">Periode</div>
                <div class="atri-detail-value"><span id="atriDetailPeriod">-</span> <span style="color:#94a3b8;font-weight:700">(<span id="atriDetailPeriodCode">-</span>)</span></div>

                <div class="atri-detail-label">Mode</div>
                <div class="atri-detail-value" id="atriDetailMode">-</div>

                <div class="atri-detail-label">Status</div>
                <div class="atri-detail-value" id="atriDetailStatus">-</div>

                <div class="atri-detail-label">Total / Valid / Invalid</div>
                <div class="atri-detail-value"><span id="atriDetailTotal">0</span> / <span id="atriDetailValid">0</span> / <span id="atriDetailInvalid">0</span></div>

                <div class="atri-detail-label">Baris Diimpor</div>
                <div class="atri-detail-value" id="atriDetailImported">0</div>

                <div class="atri-detail-label">Uploader</div>
                <div class="atri-detail-value" id="atriDetailUploader">-</div>

                <div class="atri-detail-label">Waktu Import</div>
                <div class="atri-detail-value" id="atriDetailImportedAt">-</div>

                <div class="atri-detail-label">Dibatalkan Oleh</div>
                <div class="atri-detail-value" id="atriDetailCancelledBy">-</div>

                <div class="atri-detail-label">Waktu Pembatalan</div>
                <div class="atri-detail-value" id="atriDetailCancelledAt">-</div>
            </div>

            <div class="atri-detail-reason">
                <small>Alasan / Catatan Audit</small>
                <p id="atriDetailReason">-</p>
            </div>
        </div>

        <div class="atri-modal-actions">
            <button type="button" class="atri-back" id="atriDetailBack">TUTUP</button>
        </div>
    </div>
</div>

<div class="atri-modal" id="atriCancelModal" aria-hidden="true">
    <div class="atri-dialog">
        <div class="atri-modal-head">
            <h2>⚠ Batalkan Import ATR</h2>
            <button type="button" class="atri-close" id="atriCancelClose">×</button>
        </div>
        <form method="POST" id="atriCancelForm">
            @csrf
            <div class="atri-modal-body">
                <div class="atri-warning">Import akan diberi status <strong>CANCELLED</strong>. Riwayat dan file arsip tetap tersimpan untuk audit.</div>
                <div class="atri-summary">
                    <div><span>File</span><b id="atriCancelFile">-</b></div>
                    <div><span>Periode</span><b id="atriCancelPeriod">-</b></div>
                    <div><span>Baris ATR</span><b id="atriCancelTotal">-</b></div>
                </div>
                <div class="atri-field">
                    <label>Alasan Pembatalan *</label>
                    <textarea name="cancel_reason" class="atri-textarea" minlength="5" maxlength="500" placeholder="Contoh: salah periode / salah file / data sumber belum final..." required></textarea>
                </div>
            </div>
            <div class="atri-modal-actions">
                <button type="button" class="atri-back" id="atriCancelBack">KEMBALI</button>
                <button type="submit" class="atri-danger">BATALKAN IMPORT</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const monthsByYear = @json($monthsByYear);
    const monthLabels = @json($monthLabels);
    const yearSelect = document.getElementById('atriHistoryYear');
    const monthSelect = document.getElementById('atriHistoryMonth');

    function allowedMonthsForYear(year) {
        if (year && Array.isArray(monthsByYear[year])) {
            return monthsByYear[year];
        }

        const merged = new Set();
        Object.values(monthsByYear).forEach(function (months) {
            (months || []).forEach(function (month) { merged.add(month); });
        });

        return Array.from(merged).sort();
    }

    function rebuildMonthOptions() {
        if (!monthSelect) return;

        const selected = monthSelect.value || monthSelect.dataset.selected || '';
        const allowed = allowedMonthsForYear(yearSelect?.value || '');
        monthSelect.innerHTML = '<option value="">Semua Bulan</option>';

        allowed.forEach(function (month) {
            const option = document.createElement('option');
            option.value = month;
            option.textContent = monthLabels[month] || month;
            option.selected = month === selected;
            monthSelect.appendChild(option);
        });

        if (selected && !allowed.includes(selected)) {
            monthSelect.value = '';
        }

        monthSelect.dataset.selected = '';
    }

    yearSelect?.addEventListener('change', rebuildMonthOptions);
    rebuildMonthOptions();

    const modal = document.getElementById('atriCancelModal');
    const form = document.getElementById('atriCancelForm');
    const file = document.getElementById('atriCancelFile');
    const period = document.getElementById('atriCancelPeriod');
    const total = document.getElementById('atriCancelTotal');

    function closeModal() {
        modal?.classList.remove('open');
        modal?.setAttribute('aria-hidden', 'true');
        form?.reset();
    }

    document.querySelectorAll('[data-cancel-import]').forEach(function (button) {
        button.addEventListener('click', function () {
            form.action = this.dataset.url || '';
            file.textContent = this.dataset.file || '-';
            period.textContent = this.dataset.period || '-';
            total.textContent = this.dataset.total || '0';
            modal?.classList.add('open');
            modal?.setAttribute('aria-hidden', 'false');
        });
    });

    document.getElementById('atriCancelClose')?.addEventListener('click', closeModal);
    document.getElementById('atriCancelBack')?.addEventListener('click', closeModal);
    modal?.addEventListener('click', function (event) { if (event.target === modal) closeModal(); });

    const detailModal = document.getElementById('atriDetailModal');

    const detailTargets = {
        id: document.getElementById('atriDetailId'),
        file: document.getElementById('atriDetailFile'),
        period: document.getElementById('atriDetailPeriod'),
        periodCode: document.getElementById('atriDetailPeriodCode'),
        mode: document.getElementById('atriDetailMode'),
        status: document.getElementById('atriDetailStatus'),
        total: document.getElementById('atriDetailTotal'),
        valid: document.getElementById('atriDetailValid'),
        invalid: document.getElementById('atriDetailInvalid'),
        imported: document.getElementById('atriDetailImported'),
        uploader: document.getElementById('atriDetailUploader'),
        importedAt: document.getElementById('atriDetailImportedAt'),
        cancelledBy: document.getElementById('atriDetailCancelledBy'),
        cancelledAt: document.getElementById('atriDetailCancelledAt'),
        reason: document.getElementById('atriDetailReason'),
    };

    function closeDetailModal() {
        detailModal?.classList.remove('open');
        detailModal?.setAttribute('aria-hidden', 'true');
    }

    document.querySelectorAll('[data-import-detail]').forEach(function (button) {
        button.addEventListener('click', function () {
            Object.entries(detailTargets).forEach(function ([key, element]) {
                if (!element) return;
                const datasetKey = key.replace(/[A-Z]/g, function (letter) {
                    return '-' + letter.toLowerCase();
                });
                element.textContent = button.getAttribute('data-' + datasetKey) || '-';
            });

            detailModal?.classList.add('open');
            detailModal?.setAttribute('aria-hidden', 'false');
        });
    });

    document.getElementById('atriDetailClose')?.addEventListener('click', closeDetailModal);
    document.getElementById('atriDetailBack')?.addEventListener('click', closeDetailModal);
    detailModal?.addEventListener('click', function (event) {
        if (event.target === detailModal) closeDetailModal();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeModal();
            closeDetailModal();
        }
    });
});
</script>