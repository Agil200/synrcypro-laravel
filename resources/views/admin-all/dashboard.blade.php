@extends('admin-all.layout')

@section('admin-content')
@php
    $ss = $controlCenter['suggestion'] ?? [];
    $integrations = $controlCenter['integrations'] ?? [];

    $suggestionUrl = \Illuminate\Support\Facades\Route::has('admin-all.suggestion.index')
        ? route('admin-all.suggestion.index')
        : '#';

    // Kartu modul utama (Stock Opname dihapus dari tampilan grid atas sesuai permintaan)
    $moduleCards = [
        [
            'key' => 'suggestion',
            'letter' => 'S',
            'name' => 'Suggestion System',
            'value' => $ss['total'] ?? 0,
            'metric' => 'Total SS',
            'note' => ($ss['pending_gl_qcc'] ?? 0).' pending GL/QCC',
            'status' => ($ss['connected'] ?? false) ? 'LIVE' : 'OFFLINE',
            'color' => '#ef7d00',
            'url' => $suggestionUrl,
            'live' => ($ss['connected'] ?? false),
        ],
        [
            'key' => 'ifuts',
            'letter' => 'I',
            'name' => 'IFUTS TICKETING',
            'value' => '—',
            'metric' => 'Produksi',
            'note' => 'Backend tahap berikut',
            'status' => 'NEXT',
            'color' => '#09879a',
            'url' => '#module-ifuts',
            'live' => false,
        ],
        [
            'key' => 'mcu',
            'letter' => 'M',
            'name' => 'MCU & FU Internal',
            'value' => '—',
            'metric' => 'MCU / Follow Up',
            'note' => 'Backend tahap berikut',
            'status' => 'NEXT',
            'color' => '#0aa768',
            'url' => '#module-mcu',
            'live' => false,
        ],
        [
            'key' => 'archive',
            'letter' => 'E',
            'name' => 'E-Arsip',
            'value' => $summary['archive_folders'] ?? 0,
            'metric' => 'Folder Utama',
            'note' => 'Prosedur & Form Admin',
            'status' => 'READY',
            'color' => '#5946b8',
            'url' => $archiveFolders->get(0)['url'] ?? '#',
            'live' => true,
        ],
    ];
@endphp

<style>
    .cc-page-title {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 8px;
    }

    .cc-page-title h1 {
        margin: 0;
        color: #051d39;
        font-size: clamp(21px, 2vw, 28px);
        letter-spacing: -.03em;
    }

    .cc-page-title p {
        margin: 3px 0 0;
        color: #5d6c7c;
        font-size: 9px;
        line-height: 1.4;
    }

    .cc-title-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        justify-content: flex-end;
    }

    .cc-system-strip {
        display: flex;
        min-height: 33px;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
        padding: 7px 10px;
        border: 1px solid #b6dfca;
        border-radius: 8px;
        background: #edfff5;
    }

    .cc-system-strip strong {
        color: #145e3b;
        font-size: 8px;
    }

    .cc-health {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .cc-health-item {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        min-height: 21px;
        padding: 0 7px;
        border-radius: 999px;
        color: #526174;
        background: #fff;
        font-size: 7px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .cc-health-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #9aa4af;
    }

    .cc-health-item.ok .cc-health-dot {
        background: #18ad67;
        box-shadow: 0 0 0 3px rgba(24, 173, 103, .11);
    }

    .cc-health-item.warn .cc-health-dot {
        background: #ef7d00;
    }

    /* Diubah menjadi 4 kolom karena card stock opname di atas arsip dihapus */
    .cc-module-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px;
        margin-bottom: 10px;
    }

    .cc-module-card {
        position: relative;
        display: block;
        min-width: 0;
        min-height: 104px;
        padding: 12px 11px 10px 52px;
        overflow: hidden;
        border: 1px solid #d9e0e7;
        border-radius: 10px;
        color: inherit;
        background: #fff;
        box-shadow: 0 5px 18px rgba(31, 47, 65, .06);
        text-decoration: none;
        transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
    }

    .cc-module-card:hover {
        transform: translateY(-2px);
        border-color: #b8c5d1;
        box-shadow: 0 9px 23px rgba(31, 47, 65, .10);
        text-decoration: none;
    }

    .cc-module-icon {
        position: absolute;
        top: 13px;
        left: 11px;
        display: grid;
        width: 33px;
        height: 33px;
        place-items: center;
        border-radius: 9px;
        color: #fff;
        background: var(--module-color);
        font-size: 13px;
        font-weight: 900;
    }

    .cc-module-card small {
        display: block;
        color: #637083;
        font-size: 7px;
        font-weight: 900;
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    .cc-module-value {
        display: block;
        margin-top: 4px;
        color: #091f3c;
        font-size: 24px;
        font-weight: 900;
        line-height: 1;
    }

    .cc-module-note {
        display: block;
        margin-top: 5px;
        color: #68778a;
        font-size: 7px;
        line-height: 1.3;
    }

    .cc-module-status {
        position: absolute;
        top: 10px;
        right: 9px;
        padding: 3px 6px;
        border-radius: 999px;
        color: #68717d;
        background: #edf1f4;
        font-size: 6px;
        font-weight: 900;
    }

    .cc-module-status.live {
        color: #11633a;
        background: #ddf5e7;
    }

    .cc-main-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 9px;
    }

    .cc-stack {
        display: grid;
        gap: 9px;
    }

    .cc-panel {
        min-width: 0;
        border: 1px solid #d9e0e7;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 5px 18px rgba(31, 47, 65, .05);
    }

    .cc-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 10px 12px;
        border-bottom: 1px solid #e0e6ec;
    }

    .cc-panel-head h2 {
        margin: 0;
        font-size: 12px;
    }

    .cc-panel-head p {
        margin: 2px 0 0;
        color: #68778a;
        font-size: 7px;
    }

    .cc-panel-body {
        padding: 10px 11px;
    }

    .cc-action-list,
    .cc-activity-list,
    .cc-op-list {
        display: grid;
        gap: 7px;
    }

    .cc-action-row {
        display: grid;
        grid-template-columns: 31px minmax(0, 1fr) auto;
        align-items: center;
        gap: 9px;
        padding: 9px;
        border: 1px solid #e1e6ec;
        border-radius: 8px;
        background: #fff;
    }

    .cc-action-icon {
        display: grid;
        width: 30px;
        height: 30px;
        place-items: center;
        border-radius: 8px;
        color: #fff;
        background: #ef7d00;
        font-size: 11px;
        font-weight: 900;
    }

    .cc-action-row strong {
        display: block;
        font-size: 9px;
    }

    .cc-action-row small {
        display: block;
        margin-top: 2px;
        color: #68778a;
        font-size: 7px;
    }

    .cc-action-button {
        display: inline-flex;
        min-height: 26px;
        align-items: center;
        justify-content: center;
        padding: 0 8px;
        border: 1px solid #cfd8e2;
        border-radius: 6px;
        color: #172b43;
        background: #fff;
        font-size: 7px;
        font-weight: 900;
        text-decoration: none;
        text-transform: uppercase;
    }

    .cc-action-button.primary {
        border-color: #0f78ef;
        color: #fff;
        background: #0f78ef;
    }

    .cc-op-row {
        display: grid;
        grid-template-columns: 135px minmax(0, 1fr) auto;
        align-items: center;
        gap: 9px;
        padding: 8px 9px;
        border: 1px solid #e1e6ec;
        border-radius: 8px;
    }

    .cc-op-row strong {
        font-size: 8px;
    }

    .cc-op-bar {
        height: 7px;
        overflow: hidden;
        border-radius: 999px;
        background: #edf1f5;
    }

    .cc-op-bar span {
        display: block;
        height: 100%;
        min-width: 4px;
        border-radius: inherit;
        background: var(--bar-color, #0f78ef);
    }

    .cc-op-state {
        padding: 3px 7px;
        border-radius: 999px;
        color: #68717d;
        background: #edf1f4;
        font-size: 6px;
        font-weight: 900;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .cc-op-state.live {
        color: #11633a;
        background: #ddf5e7;
    }

    .cc-activity-row {
        display: grid;
        grid-template-columns: 58px minmax(0, 1fr) auto;
        gap: 9px;
        align-items: center;
        padding: 8px 2px;
        border-bottom: 1px solid #edf0f4;
    }

    .cc-activity-row:last-child {
        border-bottom: 0;
    }

    .cc-activity-time {
        color: #6b7889;
        font-size: 7px;
        font-weight: 800;
    }

    .cc-activity-row strong {
        display: block;
        font-size: 8px;
    }

    .cc-activity-row small {
        display: block;
        margin-top: 2px;
        overflow: hidden;
        color: #68778a;
        font-size: 7px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .cc-source-badge {
        padding: 3px 6px;
        border-radius: 999px;
        color: #a85b00;
        background: #fff0db;
        font-size: 6px;
        font-weight: 900;
        white-space: nowrap;
    }

    .cc-empty {
        padding: 16px 8px;
        color: #7b8797;
        font-size: 8px;
        text-align: center;
    }

    @media (max-width: 1200px) {
        .cc-module-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .cc-main-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 760px) {
        .cc-page-title,
        .cc-system-strip {
            align-items: stretch;
            flex-direction: column;
        }

        .cc-title-actions {
            justify-content: flex-start;
        }

        .cc-module-grid {
            grid-template-columns: 1fr;
        }

        .cc-op-row {
            grid-template-columns: 110px minmax(0, 1fr);
        }

        .cc-op-state {
            grid-column: 1 / -1;
            justify-self: start;
        }
    }
</style>

<div class="cc-page-title">
    <div>
        <h1>Dashboard Admin All</h1>
        <p>
            Control Center administrasi internal Departemen Produksi — Site BA.
            Satu pintu untuk module utama.
        </p>
    </div>

    <div class="cc-title-actions">
        <a href="{{ route('dashboard') }}" class="aa-action-button">
            ⌂ Dashboard Utama
        </a>

        <a
            href="{{ $archiveFolders->get(0)['url'] ?? '#' }}"
            target="_blank"
            rel="noopener noreferrer"
            class="aa-action-button"
        >
            ▣ Prosedur Departemen
        </a>

        <a
            href="{{ $archiveFolders->get(1)['url'] ?? '#' }}"
            target="_blank"
            rel="noopener noreferrer"
            class="aa-action-button primary"
        >
            ▣ Kumpulan Form Admin
        </a>
    </div>
</div>

<div class="cc-system-strip">
    <strong>
        Control Center Admin All aktif.
        Suggestion System sudah memakai data real;
        module lain diaktifkan bertahap.
    </strong>

    <div class="cc-health">
        <span class="cc-health-item {{ ($integrations['google_sheets'] ?? false) ? 'ok' : 'warn' }}">
            <span class="cc-health-dot"></span>
            Google Sheets
        </span>

        <span class="cc-health-item {{ ($integrations['laravel'] ?? false) ? 'ok' : 'warn' }}">
            <span class="cc-health-dot"></span>
            Laravel
        </span>

        <span class="cc-health-item {{ ($integrations['google_drive'] ?? false) ? 'ok' : 'warn' }}">
            <span class="cc-health-dot"></span>
            Google Drive
        </span>
    </div>
</div>

<div class="cc-module-grid">
    @foreach($moduleCards as $module)
        <a
            href="{{ $module['url'] }}"
            class="cc-module-card"
            style="--module-color:{{ $module['color'] }}"
        >
            <span class="cc-module-icon">
                {{ $module['letter'] }}
            </span>

            <span class="cc-module-status {{ $module['live'] ? 'live' : '' }}">
                {{ $module['status'] }}
            </span>

            <small>{{ $module['name'] }}</small>

            <span class="cc-module-value">
                {{ $module['value'] }}
            </span>

            <span class="cc-module-note">
                {{ $module['metric'] }}
                •
                {{ $module['note'] }}
            </span>
        </a>
    @endforeach
</div>

<div class="cc-main-grid">
    <div class="cc-stack">
        <section class="cc-panel">
            <div class="cc-panel-head">
                <div>
                    <h2>Perlu Tindakan Saya</h2>
                    <p>
                        Prioritas kerja yang sudah dapat dihitung dari backend aktif.
                    </p>
                </div>

                <span class="aa-status active">
                    Live
                </span>
            </div>

            <div class="cc-panel-body">
                <div class="cc-action-list">
                    <div class="cc-action-row">
                        <span class="cc-action-icon">
                            GL
                        </span>

                        <span>
                            <strong>
                                {{ number_format($ss['pending_gl_qcc'] ?? 0) }}
                                Suggestion menunggu proses GL / QCC
                            </strong>

                            <small>
                                Bersumber langsung dari STATUS_GL_QCC pada DATABASE_SS.
                            </small>
                        </span>

                        <a
                            href="{{ route('admin-all.suggestion.index', ['status' => 'SUBMITTED']) }}#data-ss"
                            class="cc-action-button primary"
                        >
                            Buka
                        </a>
                    </div>

                    <div class="cc-action-row">
                        <span
                            class="cc-action-icon"
                            style="background:#0aa768"
                        >
                            SH
                        </span>

                        <span>
                            <strong>
                                {{ number_format($ss['pending_sh'] ?? 0) }}
                                Suggestion menunggu persetujuan SH
                            </strong>

                            <small>
                                Suggestion yang sudah masuk tahap persetujuan Section Head.
                            </small>
                        </span>

                        <a
                            href="{{ route('admin-all.suggestion.index', ['status' => 'VERIFIED_GL_QCC']) }}#data-ss"
                            class="cc-action-button"
                        >
                            Buka
                        </a>
                    </div>

                    <div class="cc-action-row">
                        <span
                            class="cc-action-icon"
                            style="background:#657386"
                        >
                            +
                        </span>

                        <span>
                            <strong>
                                IFUTS, MCU & FU, dan Stock Opname
                            </strong>

                            <small>
                                Panel tindakan otomatis akan aktif setelah backend masing-masing module tersambung.
                            </small>
                        </span>

                        <span class="cc-op-state">
                            Next
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <section class="cc-panel">
            <div class="cc-panel-head">
                <div>
                    <h2>Ringkasan Operasional Module</h2>
                    <p>
                        Status kesiapan control center tanpa membuat data palsu untuk module yang belum terintegrasi.
                    </p>
                </div>
            </div>

            <div class="cc-panel-body">
                <div class="cc-op-list">
                    <div class="cc-op-row">
                        <strong>Suggestion System</strong>

                        <div class="cc-op-bar">
                            <span
                                style="
                                    --bar-color:#ef7d00;
                                    width:{{ ($ss['total'] ?? 0) > 0 ? '100%' : '4%' }};
                                "
                            ></span>
                        </div>

                        <span class="cc-op-state {{ ($ss['connected'] ?? false) ? 'live' : '' }}">
                            {{ ($ss['connected'] ?? false) ? ($ss['total'].' Data') : 'Offline' }}
                        </span>
                    </div>

                    <div
                        class="cc-op-row"
                        id="module-ifuts"
                    >
                        <strong>IFUTS TICKETING</strong>

                        <div class="cc-op-bar">
                            <span style="--bar-color:#09879a;width:4%"></span>
                        </div>

                        <span class="cc-op-state">
                            Menunggu Integrasi
                        </span>
                    </div>

                    <div
                        class="cc-op-row"
                        id="module-mcu"
                    >
                        <strong>MCU & FU Internal</strong>

                        <div class="cc-op-bar">
                            <span style="--bar-color:#0aa768;width:4%">`</span>
                        </div>

                        <span class="cc-op-state">
                            Menunggu Integrasi
                        </span>
                    </div>

                    <div class="cc-op-row">
                        <strong>E-Arsip</strong>

                        <div class="cc-op-bar">
                            <span style="--bar-color:#5946b8;width:100%"></span>
                        </div>

                        <span class="cc-op-state live">
                            {{ $summary['archive_folders'] ?? 0 }} Folder Ready
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <section class="cc-panel">
            <div class="cc-panel-head">
                <div>
                    <h2>Aktivitas Terbaru</h2>
                    <p>
                        Feed real yang sudah tersedia saat ini dari Suggestion System.
                    </p>
                </div>

                <a
                    href="{{ $suggestionUrl }}"
                    class="cc-action-button"
                >
                    Lihat Scientific
                </a>
            </div>

            <div class="cc-panel-body">
                @if(count($ss['latest'] ?? []) > 0)
                    <div class="cc-activity-list">
                        @foreach($ss['latest'] as $row)
                            <div class="cc-activity-row">
                                <span class="cc-activity-time">
                                    {{ $row['SUBMIT_AT'] ?? '-' }}
                                </span>

                                <span>
                                    <strong>
                                        {{ $row['NO_SS'] ?? '-' }}
                                        —
                                        {{ $row['NAMA_KARYAWAN'] ?? '-' }}
                                    </strong>

                                    <small>
                                        {{ $row['JUDUL_SS'] ?? '-' }}
                                    </small>
                                </span>

                                <span class="cc-source-badge">
                                    Suggestion
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="cc-empty">
                        Belum ada aktivitas real yang dapat ditampilkan.
                    </div>
                @endif
            </div>
        </section>
    </div>

</div>
@endsection