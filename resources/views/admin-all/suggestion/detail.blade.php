@extends('admin-all.layout')

@section('title', 'Detail Suggestion System')

@section('admin-content')
@php
    $row = $suggestionRow ?? [];

    $statusLabel = static function (?string $status): string {
        $status = strtoupper(trim((string) $status));

        if ($status === '') {
            return '-';
        }

        return match ($status) {
            'VERIFIED_GL_QCC' => 'Verified GL / QCC',
            'APPROVED_SH' => 'Approved SH',
            'APPROVED_DH_PM' => 'SELESAI',
            default => ucwords(
                strtolower(
                    str_replace('_', ' ', $status)
                )
            ),
        };
    };

    $isChecked = static function ($value): bool {
        $value = trim((string) $value);

        return in_array(
            strtoupper($value),
            ['✓', 'CHECK', 'CHECKED', 'YES', 'YA', 'TRUE', '1'],
            true
        );
    };

    $accessAllowed =
        ($suggestionAccess['allowed'] ?? false) === true;

    $accessRole =
        strtoupper(
            trim(
                (string) ($suggestionAccess['access'] ?? '')
            )
        );

    $workflowAccessLabel = $accessAllowed
        ? ($accessRole ?: 'AKTIF')
        : 'VIEW ONLY';

    $mainStatus =
        strtoupper(
            trim((string) ($row['STATUS'] ?? ''))
        );

    $isFinished = in_array(
        $mainStatus,
        ['APPROVED_DH_PM', 'SELESAI', 'DONE', 'COMPLETED'],
        true
    );

    $glStatus = strtoupper(
        trim((string) ($row['STATUS_GL_QCC'] ?? ''))
    );

    $shStatus = strtoupper(
        trim((string) ($row['STATUS_SH'] ?? ''))
    );

    $dhPmStatus = strtoupper(
        trim((string) ($row['STATUS_DH_PM'] ?? ''))
    );

    $stageCompleted = [
        'submit' => true,

        'gl' =>
            in_array($glStatus, ['VERIFIED', 'APPROVED'], true)
            || in_array(
                $mainStatus,
                [
                    'VERIFIED_GL_QCC',
                    'APPROVED_SH',
                    'APPROVED_DH_PM',
                    'SELESAI',
                    'DONE',
                    'COMPLETED',
                ],
                true
            ),

        'sh' =>
            in_array($shStatus, ['VERIFIED', 'APPROVED'], true)
            || in_array(
                $mainStatus,
                [
                    'APPROVED_SH',
                    'APPROVED_DH_PM',
                    'SELESAI',
                    'DONE',
                    'COMPLETED',
                ],
                true
            ),

        'dh' =>
            in_array($dhPmStatus, ['VERIFIED', 'APPROVED'], true)
            || in_array(
                $mainStatus,
                [
                    'APPROVED_DH_PM',
                    'SELESAI',
                    'DONE',
                    'COMPLETED',
                ],
                true
            ),

        'done' => $isFinished,
    ];

    $stageCurrent = [
        'submit' => false,
        'gl' => ! $stageCompleted['gl'],
        'sh' =>
            $stageCompleted['gl']
            && ! $stageCompleted['sh'],
        'dh' =>
            $stageCompleted['sh']
            && ! $stageCompleted['dh'],
        'done' =>
            $stageCompleted['dh']
            && ! $stageCompleted['done'],
    ];

    /*
     * PDF_URL = URL PDF RESMI yang dibuat Apps Script
     * pada folder 04_HASIL_CETAK.
     * Laravel hanya membuka file existing.
     */
    $officialPdfUrl = trim(
        (string) ($row['PDF_URL'] ?? '')
    );

    $printStatus = strtoupper(
        trim((string) ($row['PRINT_STATUS'] ?? ''))
    );

    $formReadyToPrint =
        $officialPdfUrl !== ''
        && $printStatus === 'SIAP DICETAK';
@endphp

<style>
    /*
     * Fixed application shell:
     * sidebar + header + footer tetap,
     * hanya area data tengah yang scroll.
     */
    #adminAllShell {
        height: 100vh !important;
        min-height: 100vh !important;
        overflow: hidden !important;
        grid-template-rows:
            var(--aa-top)
            minmax(0, 1fr)
            var(--aa-footer) !important;
    }

    #adminAllShell .aa-main {
        min-height: 0 !important;
        overflow-x: hidden !important;
        overflow-y: auto !important;
    }

    #adminAllShell .aa-sidebar {
        min-height: 0 !important;
        overflow: hidden !important;
    }

    #adminAllShell .aa-navigation {
        min-height: 0 !important;
        overflow-y: auto !important;
    }

    .ssd-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 8px;
    }

    .ssd-head h1 {
        margin: 0;
        color: #051d39;
        font-size: clamp(21px, 2vw, 27px);
        letter-spacing: -.03em;
    }

    .ssd-head p {
        margin: 3px 0 0;
        color: #5d6c7c;
        font-size: 9px;
    }

    .ssd-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        justify-content: flex-end;
    }

    .ssd-flash {
        margin-bottom: 8px;
        padding: 9px 11px;
        border: 1px solid #cfd8e2;
        border-radius: 8px;
        font-size: 8px;
        font-weight: 800;
        line-height: 1.45;
    }

    .ssd-flash.success {
        border-color: #b8ead2;
        color: #0a6b42;
        background: #f0fff7;
    }

    .ssd-flash.error {
        border-color: #f1bdc3;
        color: #a72632;
        background: #fff3f4;
    }

    .ssd-action-form {
        display: grid;
        gap: 8px;
        margin-top: 9px;
        padding-top: 9px;
        border-top: 1px solid #e0e6eb;
    }

    .ssd-action-form label {
        color: #4f6073;
        font-size: 7px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .ssd-action-form textarea {
        width: 100%;
        min-height: 78px;
        resize: vertical;
        padding: 9px 10px;
        border: 1px solid #d5dde6;
        border-radius: 8px;
        color: #172b43;
        background: #fff;
        font: inherit;
        font-size: 8px;
        outline: none;
    }

    .ssd-action-form textarea:focus {
        border-color: #0f78ef;
        box-shadow: 0 0 0 3px rgba(15,120,239,.08);
    }

    .ssd-workflow-buttons {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 7px;
    }

    .ssd-workflow-btn {
        min-height: 33px;
        border: 0;
        border-radius: 7px;
        color: #fff;
        font-size: 8px;
        font-weight: 900;
        cursor: pointer;
    }

    .ssd-workflow-btn.verify {
        background: #0f78ef;
    }

    .ssd-workflow-btn.revision {
        color: #3f3000;
        background: #f5b800;
    }

    .ssd-workflow-btn.reject {
        background: #dc3545;
    }

    .ssd-workflow-btn:hover {
        filter: brightness(.97);
    }

    .ssd-note-error {
        color: #b42318;
        font-size: 7px;
        font-weight: 800;
    }

    .ssd-info-strip {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 9px;
        padding: 8px 11px;
        border: 1px solid #b6dfca;
        border-radius: 8px;
        color: #12643b;
        background: #edfff5;
        font-size: 8px;
    }

    .ssd-info-strip strong::before {
        display: inline-block;
        width: 8px;
        height: 8px;
        margin-right: 7px;
        border-radius: 50%;
        background: #20b76b;
        content: '';
    }

    .ssd-summary {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 7px;
        margin-bottom: 9px;
    }

    .ssd-stage {
        position: relative;
        min-width: 0;
        min-height: 82px;
        padding: 11px 10px 10px 48px;
        overflow: hidden;
        border: 1px solid var(--stage-border);
        border-radius: 10px;
        background: var(--stage-bg);
        box-shadow: 0 4px 14px rgba(31, 47, 65, .05);
    }

    .ssd-stage::after {
        position: absolute;
        top: 0;
        right: 0;
        width: 5px;
        height: 100%;
        background: var(--stage-color);
        content: '';
    }

    .ssd-stage-icon {
        position: absolute;
        top: 12px;
        left: 10px;
        display: grid;
        width: 28px;
        height: 28px;
        place-items: center;
        border-radius: 8px;
        color: #fff;
        background: var(--stage-color);
        font-size: 9px;
        font-weight: 900;
    }

    .ssd-stage small {
        display: block;
        color: var(--stage-text);
        font-size: 7px;
        font-weight: 900;
        letter-spacing: .03em;
        text-transform: uppercase;
    }

    .ssd-stage strong {
        display: block;
        margin-top: 5px;
        color: #0c2343;
        font-size: 10px;
        line-height: 1.3;
    }

    .ssd-stage span {
        display: block;
        margin-top: 5px;
        overflow: hidden;
        color: #667587;
        font-size: 7px;
        line-height: 1.35;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ssd-stage.submit {
        --stage-color: #ef7d00;
        --stage-border: #ffd3a3;
        --stage-bg: #fff8ef;
        --stage-text: #9b5700;
    }

    .ssd-stage.gl {
        --stage-color: #0f78ef;
        --stage-border: #b7d9ff;
        --stage-bg: #f2f8ff;
        --stage-text: #0d63b7;
    }

    .ssd-stage.sh {
        --stage-color: #0aa768;
        --stage-border: #b8ead2;
        --stage-bg: #f0fff7;
        --stage-text: #087847;
    }

    .ssd-stage.dh {
        --stage-color: #7548c8;
        --stage-border: #d8c8f6;
        --stage-bg: #f8f4ff;
        --stage-text: #5d36a5;
    }

    .ssd-stage.done {
        --stage-color: #0f766e;
        --stage-border: #b9dedb;
        --stage-bg: #f0fbfa;
        --stage-text: #0b625c;
    }

    .ssd-stage.completed {
        box-shadow:
            inset 0 0 0 1px var(--stage-color),
            0 5px 16px rgba(31, 47, 65, .07);
    }

    .ssd-stage.current {
        box-shadow:
            inset 0 0 0 2px var(--stage-color),
            0 7px 18px rgba(31, 47, 65, .09);
    }

    .ssd-stage-state {
        position: absolute !important;
        top: 9px;
        right: 12px;
        display: inline-grid !important;
        min-width: 20px;
        height: 20px;
        place-items: center;
        margin: 0 !important;
        padding: 0 5px;
        border-radius: 999px;
        color: #fff !important;
        background: var(--stage-color);
        font-size: 9px !important;
        font-weight: 900;
        line-height: 1 !important;
        white-space: nowrap;
    }

    .ssd-stage-state.waiting {
        color: var(--stage-text) !important;
        background: rgba(255,255,255,.82);
        border: 1px solid var(--stage-border);
    }

    .ssd-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(330px, .65fr);
        gap: 9px;
        align-items: start;
    }

    .ssd-stack {
        display: grid;
        gap: 9px;
    }

    .ssd-card {
        min-width: 0;
        border: 1px solid var(--aa-border);
        border-radius: 10px;
        background: #fff;
        box-shadow: var(--aa-shadow);
    }

    .ssd-card-head {
        padding: 10px 12px;
        border-bottom: 1px solid var(--aa-border);
    }

    .ssd-card-head h2 {
        margin: 0;
        font-size: 12px;
    }

    .ssd-card-head p {
        margin: 2px 0 0;
        color: #68778a;
        font-size: 7px;
    }

    .ssd-card-body {
        padding: 11px 12px;
    }

    .ssd-meta-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 7px;
    }

    .ssd-meta {
        min-width: 0;
        padding: 9px;
        border: 1px solid #e0e6eb;
        border-radius: 8px;
        background: #fbfcfd;
    }

    .ssd-meta small {
        display: block;
        color: #6a7788;
        font-size: 7px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .ssd-meta strong {
        display: block;
        margin-top: 4px;
        overflow-wrap: anywhere;
        color: #162a43;
        font-size: 9px;
        line-height: 1.35;
    }

    .ssd-narrative {
        display: grid;
        gap: 8px;
    }

    .ssd-text-box {
        padding: 10px;
        border: 1px solid #e0e6eb;
        border-radius: 8px;
        background: #fbfcfd;
    }

    .ssd-text-box strong {
        display: block;
        margin-bottom: 5px;
        color: #19314e;
        font-size: 9px;
    }

    .ssd-text-box p {
        margin: 0;
        color: #405267;
        font-size: 9px;
        line-height: 1.55;
        white-space: pre-line;
    }

    .ssd-qcdsm {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 7px;
    }

    .ssd-qcdsm-card {
        min-width: 0;
        padding: 9px;
        border: 1px solid #e0e6eb;
        border-radius: 8px;
        background: #fff;
    }

    .ssd-qcdsm-card.active {
        border-color: #b8ead2;
        background: #f0fff7;
    }

    .ssd-qcdsm-badge {
        display: grid;
        width: 27px;
        height: 27px;
        place-items: center;
        border-radius: 8px;
        color: #fff;
        background: #8a96a5;
        font-size: 10px;
        font-weight: 900;
    }

    .ssd-qcdsm-card.active .ssd-qcdsm-badge {
        background: #0aa768;
    }

    .ssd-qcdsm-card strong {
        display: block;
        margin-top: 7px;
        font-size: 8px;
    }

    .ssd-qcdsm-card p {
        margin: 5px 0 0;
        color: #657386;
        font-size: 7px;
        line-height: 1.4;
    }

    .ssd-doc-list,
    .ssd-access-list {
        display: grid;
        gap: 7px;
    }

    .ssd-doc-link,
    .ssd-access-row {
        display: grid;
        grid-template-columns: 32px minmax(0, 1fr) auto;
        align-items: center;
        gap: 8px;
        padding: 9px;
        border: 1px solid #e0e6eb;
        border-radius: 8px;
        color: inherit;
        background: #fff;
        text-decoration: none;
    }

    .ssd-doc-link:hover {
        border-color: #b8c5d1;
        text-decoration: none;
    }

    .ssd-doc-icon-svg {
        display: grid;
        width: 32px;
        height: 32px;
        place-items: center;
        border-radius: 8px;
        flex: 0 0 32px;
    }

    .ssd-doc-icon-svg svg {
        display: block;
        width: 19px;
        height: 19px;
    }

    .ssd-doc-icon-svg.folder {
        color: #a86500;
        background: #fff2d7;
    }

    .ssd-doc-icon-svg.excel {
        color: #08794a;
        background: #e4f8ed;
    }

    .ssd-doc-icon-svg.pdf {
        color: #b32635;
        background: #ffe9ec;
    }

    .ssd-doc-icon,
    .ssd-access-icon {
        display: grid;
        width: 30px;
        height: 30px;
        place-items: center;
        border-radius: 8px;
        color: #fff;
        background: #0f78ef;
        font-size: 11px;
        font-weight: 900;
    }

    .ssd-doc-link strong,
    .ssd-access-row strong {
        display: block;
        font-size: 8px;
    }

    .ssd-doc-link small,
    .ssd-access-row small {
        display: block;
        margin-top: 2px;
        color: #68778a;
        font-size: 7px;
    }

    .ssd-arrow {
        color: #607084;
        font-size: 16px;
    }

    .ssd-access-icon.view {
        background: #657386;
    }

    .ssd-access-icon.role {
        background: #ef7d00;
    }

    .ssd-history {
        margin: 0;
        padding: 10px;
        border: 1px solid #e0e6eb;
        border-radius: 8px;
        color: #405267;
        background: #f8fafc;
        font-family: Consolas, monospace;
        font-size: 8px;
        line-height: 1.55;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }

    .ssd-readonly {
        padding: 9px 10px;
        border: 1px solid #c9d7e6;
        border-radius: 8px;
        color: #405267;
        background: #f5f8fb;
        font-size: 8px;
        line-height: 1.5;
    }

    @media (max-width: 1100px) {
        .ssd-summary {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .ssd-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 760px) {
        .ssd-head,
        .ssd-info-strip {
            align-items: stretch;
            flex-direction: column;
        }

        .ssd-actions {
            justify-content: flex-start;
        }

        .ssd-summary,
        .ssd-meta-grid,
        .ssd-qcdsm {
            grid-template-columns: 1fr;
        }
    }





.ssd-workflow-btn.sh-approve {
    background:#0aa768;
    border-color:#0aa768;
    color:#fff;
}

.ssd-workflow-btn.sh-approve:hover {
    filter:brightness(.96);
}

.ssd-workflow-btn.dhpm-approve {
    background:#7048d8;
    border-color:#7048d8;
    color:#fff;
}

.ssd-workflow-btn.dhpm-approve:hover {
    filter:brightness(.96);
}

</style>

<div class="ssd-head">
    <div>
        <h1>
            Detail Suggestion System
        </h1>

        <p>
            {{ $row['NO_SS'] ?? '-' }}
            •
            {{ $row['NAMA_KARYAWAN'] ?? '-' }}
            •
            Detail masih READ ONLY pada STEP 5A.
        </p>
    </div>

    <div class="ssd-actions">
        <a
            href="{{ route('admin-all.suggestion.monitoring') }}"
            class="aa-action-button"
        >
            ← Kembali
        </a>

        <a
            href="{{ route('admin-all.suggestion.index') }}"
            class="aa-action-button"
        >
            Dashboard Suggestion
        </a>
    </div>
</div>

@if(session('success'))
    <div class="ssd-flash success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="ssd-flash error">
        {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="ssd-flash error">
        {{ $errors->first() }}
    </div>
@endif

<div class="ssd-info-strip">
    <strong>
        {{ $row['NO_SS'] ?? '-' }}
        •
        {{ $statusLabel($row['STATUS'] ?? '') }}
    </strong>

    <span>
        Login:
        {{ auth()->user()?->email ?? '-' }}
        •
        Akses workflow:
        {{ $workflowAccessLabel }}
    </span>
</div>

<div class="ssd-summary">
    <div class="ssd-stage submit completed">
        <span class="ssd-stage-icon">✓</span>
        <span class="ssd-stage-state">✓</span>
        <small>Submitted</small>
        <strong>Submitted</strong>
        <span>{{ $row['SUBMIT_AT'] ?? '-' }}</span>
    </div>

    <div class="ssd-stage gl {{ $stageCompleted['gl'] ? 'completed' : ($stageCurrent['gl'] ? 'current' : '') }}">
        <span class="ssd-stage-icon">{{ $stageCompleted['gl'] ? '✓' : '2' }}</span>
        <span class="ssd-stage-state {{ $stageCompleted['gl'] ? '' : 'waiting' }}">
            {{ $stageCompleted['gl'] ? '✓' : ($stageCurrent['gl'] ? '●' : '—') }}
        </span>
        <small>Verifikasi GL / QCC</small>
        <strong>{{ $statusLabel($row['STATUS_GL_QCC'] ?? '') }}</strong>
        <span>
            {{ $row['GL_QCC_AT'] ?? '-' }}
            @if(!empty($row['GL_QCC_BY']))
                • {{ $row['GL_QCC_BY'] }}
            @endif
        </span>
    </div>

    <div class="ssd-stage sh {{ $stageCompleted['sh'] ? 'completed' : ($stageCurrent['sh'] ? 'current' : '') }}">
        <span class="ssd-stage-icon">{{ $stageCompleted['sh'] ? '✓' : '3' }}</span>
        <span class="ssd-stage-state {{ $stageCompleted['sh'] ? '' : 'waiting' }}">
            {{ $stageCompleted['sh'] ? '✓' : ($stageCurrent['sh'] ? '●' : '—') }}
        </span>
        <small>Persetujuan SH</small>
        <strong>{{ $statusLabel($row['STATUS_SH'] ?? '') }}</strong>
        <span>
            {{ $row['SH_AT'] ?? '-' }}
            @if(!empty($row['SH_BY']))
                • {{ $row['SH_BY'] }}
            @endif
        </span>
    </div>

    <div class="ssd-stage dh {{ $stageCompleted['dh'] ? 'completed' : ($stageCurrent['dh'] ? 'current' : '') }}">
        <span class="ssd-stage-icon">{{ $stageCompleted['dh'] ? '✓' : '4' }}</span>
        <span class="ssd-stage-state {{ $stageCompleted['dh'] ? '' : 'waiting' }}">
            {{ $stageCompleted['dh'] ? '✓' : ($stageCurrent['dh'] ? '●' : '—') }}
        </span>
        <small>Persetujuan DH / PM</small>
        <strong>{{ $statusLabel($row['STATUS_DH_PM'] ?? '') }}</strong>
        <span>
            {{ $row['DH_PM_AT'] ?? '-' }}
            @if(!empty($row['DH_PM_BY']))
                • {{ $row['DH_PM_BY'] }}
            @endif
        </span>
    </div>

    <div class="ssd-stage done {{ $stageCompleted['done'] ? 'completed' : ($stageCurrent['done'] ? 'current' : '') }}">
        <span class="ssd-stage-icon">{{ $stageCompleted['done'] ? '✓' : '5' }}</span>
        <span class="ssd-stage-state {{ $stageCompleted['done'] ? '' : 'waiting' }}">
            {{ $stageCompleted['done'] ? '✓' : ($stageCurrent['done'] ? '●' : '—') }}
        </span>
        <small>Selesai</small>
        <strong>
            {{ $isFinished ? 'Selesai' : 'Belum Selesai' }}
        </strong>
        <span>
            Status utama:
            {{ $statusLabel($row['STATUS'] ?? '') }}
        </span>
    </div>
</div>

<div class="ssd-grid">
    <div class="ssd-stack">
        <section class="ssd-card">
            <div class="ssd-card-head">
                <h2>Informasi Suggestion</h2>
                <p>Identitas utama pengajuan.</p>
            </div>

            <div class="ssd-card-body">
                <div class="ssd-meta-grid">
                    <div class="ssd-meta">
                        <small>No SS</small>
                        <strong>{{ $row['NO_SS'] ?? '-' }}</strong>
                    </div>

                    <div class="ssd-meta">
                        <small>NRP</small>
                        <strong>{{ $row['NRP'] ?? '-' }}</strong>
                    </div>

                    <div class="ssd-meta">
                        <small>Nama Karyawan</small>
                        <strong>{{ $row['NAMA_KARYAWAN'] ?? '-' }}</strong>
                    </div>

                    <div class="ssd-meta">
                        <small>Departemen</small>
                        <strong>{{ $row['DEPARTEMEN'] ?? '-' }}</strong>
                    </div>

                    <div class="ssd-meta">
                        <small>Submit</small>
                        <strong>{{ $row['SUBMIT_AT'] ?? '-' }}</strong>
                    </div>

                    <div class="ssd-meta">
                        <small>Tanggal Penemuan</small>
                        <strong>{{ $row['TANGGAL_PENEMUAN'] ?? '-' }}</strong>
                    </div>

                    <div class="ssd-meta">
                        <small>Lokasi</small>
                        <strong>{{ $row['LOKASI'] ?? '-' }}</strong>
                    </div>

                    <div class="ssd-meta">
                        <small>Update Terakhir</small>
                        <strong>{{ $row['UPDATE_AT'] ?? '-' }}</strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="ssd-card">
            <div class="ssd-card-head">
                <h2>{{ $row['JUDUL_SS'] ?? 'Judul Suggestion' }}</h2>
                <p>Masalah, ide perbaikan, dan rencana penerapan.</p>
            </div>

            <div class="ssd-card-body">
                <div class="ssd-narrative">
                    <div class="ssd-text-box">
                        <strong>1. Masalah / Kondisi Awal</strong>
                        <p>{{ $row['R1_MASALAH_TEXT'] ?? '-' }}</p>
                    </div>

                    <div class="ssd-text-box">
                        <strong>2. Ide / Improvement</strong>
                        <p>{{ $row['R1_IDE_TEXT'] ?? '-' }}</p>
                    </div>

                    <div class="ssd-text-box">
                        <strong>3. Penerapan</strong>
                        <p>{{ $row['R2_PENERAPAN_TEXT'] ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="ssd-card">
            <div class="ssd-card-head">
                <h2>Manfaat Q / C / D / S / M</h2>
                <p>Benefit yang dipilih oleh pengaju.</p>
            </div>

            <div class="ssd-card-body">
                <div class="ssd-qcdsm">
                    @foreach([
                        ['key' => 'Q', 'label' => 'Quality'],
                        ['key' => 'C', 'label' => 'Cost'],
                        ['key' => 'D', 'label' => 'Delivery'],
                        ['key' => 'S', 'label' => 'Safety'],
                        ['key' => 'M', 'label' => 'Morale'],
                    ] as $benefit)
                        @php
                            $active = $isChecked(
                                $row[$benefit['key']] ?? ''
                            );

                            $textKey =
                                $benefit['key'].'_TEXT';
                        @endphp

                        <div class="ssd-qcdsm-card {{ $active ? 'active' : '' }}">
                            <span class="ssd-qcdsm-badge">
                                {{ $benefit['key'] }}
                            </span>

                            <strong>
                                {{ $benefit['label'] }}
                                {{ $active ? '✓' : '' }}
                            </strong>

                            <p>
                                {{ $row[$textKey] ?? '-' }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="ssd-card">
            <div class="ssd-card-head">
                <h2>Catatan Proses</h2>
                <p>Riwayat workflow yang sudah tercatat pada DATABASE_SS.</p>
            </div>

            <div class="ssd-card-body">
                <pre class="ssd-history">{{ trim((string) ($row['CATATAN_PROSES'] ?? '')) !== '' ? $row['CATATAN_PROSES'] : 'Belum ada catatan proses.' }}</pre>
            </div>
        </section>
    </div>

    <div class="ssd-stack">
        <section class="ssd-card">
            <div class="ssd-card-head">
                <h2>Akses Workflow</h2>
                <p>Workflow action mengikuti ACCESS_ATASAN dan status Suggestion.</p>
            </div>

            <div class="ssd-card-body">
                <div class="ssd-access-list">
                    <div class="ssd-access-row">
                        <span class="ssd-access-icon {{ $accessAllowed ? 'role' : 'view' }}">
                            {{ $accessAllowed ? ($accessRole ?: 'A') : 'V' }}
                        </span>

                        <span>
                            <strong>
                                {{ $accessAllowed ? 'Akses '.$workflowAccessLabel : 'View Only' }}
                            </strong>

                            <small>
                                {{ $suggestionAccess['message'] ?? '-' }}
                            </small>
                        </span>

                        <span class="aa-status {{ $accessAllowed ? 'active' : '' }}">
                            {{ $suggestionAccess['status'] ?? 'VIEW' }}
                        </span>
                    </div>
                </div>

                @if(($canActGl ?? false) === true)
                    <form
                        method="POST"
                        action="{{ route('admin-all.suggestion.verification-gl.action', ['noSs' => $row['NO_SS']]) }}"
                        class="ssd-action-form"
                        id="glWorkflowForm"
                    >
                        @csrf

                        <input
                            type="hidden"
                            name="decision"
                            id="glWorkflowDecision"
                            value="{{ old('decision') }}"
                        >

                        <label for="glWorkflowNote">
                            Catatan Reviewer
                        </label>

                        <textarea
                            id="glWorkflowNote"
                            name="note"
                            maxlength="1000"
                            placeholder="Opsional untuk VERIFIED. Wajib minimal 5 karakter untuk REVISI / TOLAK."
                        >{{ old('note') }}</textarea>

                        @error('note')
                            <span class="ssd-note-error">
                                {{ $message }}
                            </span>
                        @enderror

                        @error('decision')
                            <span class="ssd-note-error">
                                {{ $message }}
                            </span>
                        @enderror

                        <div class="ssd-workflow-buttons">
                            <button
                                type="submit"
                                class="ssd-workflow-btn verify"
                                data-decision="VERIFIED"
                            >
                                ✓ VERIFIED
                            </button>

                            <button
                                type="submit"
                                class="ssd-workflow-btn revision"
                                data-decision="REVISION"
                            >
                                ↺ REVISI
                            </button>

                            <button
                                type="submit"
                                class="ssd-workflow-btn reject"
                                data-decision="REJECTED"
                            >
                                × TOLAK
                            </button>
                        </div>

                        <div class="ssd-readonly">
                            Action akan diteruskan ke Apps Script existing.
                            Laravel tidak menulis status workflow langsung ke Google Sheets.
                        </div>
                    </form>
                @elseif(($canActSh ?? false) === true)
                    <form
                        method="POST"
                        action="{{ route('admin-all.suggestion.approval-sh.action', ['noSs' => $row['NO_SS']]) }}"
                        class="ssd-action-form"
                        id="shWorkflowForm"
                    >
                        @csrf

                        <input
                            type="hidden"
                            name="decision"
                            id="shWorkflowDecision"
                            value="{{ old('decision') }}"
                        >

                        <label for="shWorkflowNote">
                            Catatan Persetujuan SH
                        </label>

                        <textarea
                            id="shWorkflowNote"
                            name="note"
                            maxlength="1000"
                            placeholder="Opsional untuk SETUJUI. Wajib minimal 5 karakter untuk TOLAK."
                        >{{ old('note') }}</textarea>

                        @error('note')
                            <span class="ssd-note-error">
                                {{ $message }}
                            </span>
                        @enderror

                        @error('decision')
                            <span class="ssd-note-error">
                                {{ $message }}
                            </span>
                        @enderror

                        <div class="ssd-workflow-buttons sh-buttons">
                            <button
                                type="submit"
                                class="ssd-workflow-btn sh-approve"
                                data-decision="APPROVED"
                            >
                                ✓ SETUJUI
                            </button>

                            <button
                                type="submit"
                                class="ssd-workflow-btn reject"
                                data-decision="REJECTED"
                            >
                                × TOLAK
                            </button>
                        </div>

                        <div class="ssd-readonly">
                            Action SH diteruskan ke Apps Script existing.
                            Laravel tidak menulis status workflow langsung ke Google Sheets.
                        </div>
                    </form>
                @elseif(($canActDhPm ?? false) === true)
                    <form
                        method="POST"
                        action="{{ route('admin-all.suggestion.approval-dh-pm.action', ['noSs' => $row['NO_SS']]) }}"
                        class="ssd-action-form"
                        id="dhPmWorkflowForm"
                    >
                        @csrf

                        <input
                            type="hidden"
                            name="decision"
                            id="dhPmWorkflowDecision"
                            value="{{ old('decision') }}"
                        >

                        <label for="dhPmWorkflowNote">
                            Catatan Persetujuan DH / PM
                        </label>

                        <textarea
                            id="dhPmWorkflowNote"
                            name="note"
                            maxlength="1000"
                            placeholder="Optional untuk APPROVE. Wajib minimal 5 karakter untuk REJECT."
                        >{{ old('note') }}</textarea>

                        @error('note')
                            <span class="ssd-note-error">
                                {{ $message }}
                            </span>
                        @enderror

                        @error('decision')
                            <span class="ssd-note-error">
                                {{ $message }}
                            </span>
                        @enderror

                        <div class="ssd-workflow-buttons">
                            <button
                                type="submit"
                                class="ssd-workflow-btn dhpm-approve"
                                data-decision="APPROVED"
                            >
                                ✓ APPROVE
                            </button>

                            <button
                                type="submit"
                                class="ssd-workflow-btn reject"
                                data-decision="REJECTED"
                            >
                                × REJECT
                            </button>
                        </div>

                        <div class="ssd-readonly">
                            Action DH/PM diteruskan ke Apps Script existing.
                            Laravel tidak menulis status workflow langsung ke Google Sheets.
                        </div>
                    </form>
                @elseif(($canReviewGl ?? false) === true)
                    <div class="ssd-readonly" style="margin-top:8px">
                        @if(strtoupper(trim((string) ($row['STATUS'] ?? ''))) === 'VERIFIED_GL_QCC')
                            <strong>Tahap Verifikasi GL/QCC telah selesai.</strong><br>
                            Suggestion saat ini menunggu Persetujuan SH.
                        @elseif(strtoupper(trim((string) ($row['STATUS'] ?? ''))) === 'APPROVED_SH')
                            <strong>Tahap Persetujuan SH telah selesai.</strong><br>
                            Suggestion saat ini menunggu Persetujuan DH/PM.
                        @elseif(strtoupper(trim((string) ($row['STATUS'] ?? ''))) === 'APPROVED_DH_PM')
                            <strong>Seluruh tahap persetujuan utama telah selesai.</strong>
                        @else
                            Tahap Verifikasi GL/QCC sudah tidak memerlukan aksi dari akun ini.
                        @endif
                    </div>
                @elseif(($canReviewSh ?? false) === true)
                    <div class="ssd-readonly" style="margin-top:8px">
                        @php
                            $statusForShMessage = strtoupper(trim((string) ($row['STATUS'] ?? '')));
                        @endphp

                        @if($statusForShMessage === 'APPROVED_SH')
                            <strong>Tahap Persetujuan SH telah selesai.</strong><br>
                            Suggestion saat ini menunggu Persetujuan DH/PM.
                        @elseif($statusForShMessage === 'REJECTED_SH')
                            <strong>Suggestion telah ditolak pada tahap SH.</strong><br>
                            Lihat Catatan Proses untuk alasan penolakan.
                        @else
                            Akun memiliki akses Persetujuan SH, tetapi Suggestion ini belum atau tidak lagi
                            berada pada tahap yang dapat diproses SH.
                        @endif
                    </div>
                @elseif(($canReviewDhPm ?? false) === true)
                    <div class="ssd-readonly" style="margin-top:8px">
                        @php
                            $statusForDhPmMessage = strtoupper(trim((string) ($row['STATUS'] ?? '')));
                        @endphp

                        @if($statusForDhPmMessage === 'APPROVED_DH_PM')
                            <strong>Persetujuan DH/PM telah selesai.</strong><br>
                            Suggestion berstatus SELESAI.
                        @elseif($statusForDhPmMessage === 'REJECTED_DH_PM')
                            <strong>Suggestion telah di-REJECT pada tahap DH/PM.</strong><br>
                            Lihat Catatan Proses untuk alasan penolakan.
                        @else
                            Akun memiliki akses DH/PM, tetapi Suggestion ini belum atau tidak lagi
                            berada pada tahap yang dapat diproses DH/PM.
                        @endif
                    </div>
                @else
                    <div class="ssd-readonly" style="margin-top:8px">
                        VIEW ONLY. Email login tidak memiliki hak aksi pada tahap workflow Suggestion ini.
                    </div>
                @endif
            </div>
        </section>

        <section class="ssd-card">
            <div class="ssd-card-head">
                <h2>Dokumen & Print</h2>
                <p>Shortcut file yang sudah tersimpan pada workflow existing.</p>
            </div>

            <div class="ssd-card-body">
                <div class="ssd-doc-list">
                    @if(!empty($row['FOLDER_SS_URL']))
                        <a
                            href="{{ $row['FOLDER_SS_URL'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="ssd-doc-link"
                        >
                            <span class="ssd-doc-icon-svg folder" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M3 6.5A2.5 2.5 0 0 1 5.5 4H10l2 2h6.5A2.5 2.5 0 0 1 21 8.5v8A2.5 2.5 0 0 1 18.5 19h-13A2.5 2.5 0 0 1 3 16.5v-10Z" fill="currentColor" opacity=".18"/>
                                    <path d="M3 8h18M5.5 4H10l2 2h6.5A2.5 2.5 0 0 1 21 8.5v8A2.5 2.5 0 0 1 18.5 19h-13A2.5 2.5 0 0 1 3 16.5v-10A2.5 2.5 0 0 1 5.5 4Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                </svg>
                            </span>

                            <span>
                                <strong>Folder Suggestion</strong>
                                <small>Google Drive</small>
                            </span>

                            <span class="ssd-arrow">›</span>
                        </a>
                    @endif

                    @if(!empty($row['FILE_EXCEL_URL']))
                        <a
                            href="{{ $row['FILE_EXCEL_URL'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="ssd-doc-link"
                        >
                            <span class="ssd-doc-icon-svg excel" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M7 3.5h8l4 4V20a1 1 0 0 1-1 1H7a2 2 0 0 1-2-2V5.5a2 2 0 0 1 2-2Z" fill="currentColor" opacity=".14"/>
                                    <path d="M15 3.5V8h4M8 11h8M8 14h8M8 17h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M7 3.5h8l4 4V20a1 1 0 0 1-1 1H7a2 2 0 0 1-2-2V5.5a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                </svg>
                            </span>

                            <span>
                                <strong>File Suggestion</strong>
                                <small>Spreadsheet / file existing</small>
                            </span>

                            <span class="ssd-arrow">›</span>
                        </a>
                    @endif

                    @if($formReadyToPrint)
                        <a
                            href="{{ $officialPdfUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="ssd-doc-link"
                            title="Buka PDF resmi hasil Apps Script"
                        >
                            <span class="ssd-doc-icon-svg pdf" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M7 3.5h8l4 4V20a1 1 0 0 1-1 1H7a2 2 0 0 1-2-2V5.5a2 2 0 0 1 2-2Z" fill="currentColor" opacity=".13"/>
                                    <path d="M15 3.5V8h4M7 3.5h8l4 4V20a1 1 0 0 1-1 1H7a2 2 0 0 1-2-2V5.5a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                    <path d="M8 16.5v-5h1.7a1.5 1.5 0 0 1 0 3H8m5 2v-5h1.4c1.4 0 2.1.9 2.1 2.5s-.7 2.5-2.1 2.5H13Z" stroke="currentColor" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>

                            <span>
                                <strong>
                                    {{ $row['PRINT_STATUS'] ?? '-' }}
                                </strong>

                                <small>
                                    PDF resmi • 04_HASIL_CETAK
                                    • Print at:
                                    {{ $row['PRINT_AT'] ?? '-' }}
                                </small>
                            </span>

                            <span class="ssd-arrow">›</span>
                        </a>
                    @else
                        <div class="ssd-access-row">
                            <span class="ssd-doc-icon-svg pdf" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M7 3.5h8l4 4V20a1 1 0 0 1-1 1H7a2 2 0 0 1-2-2V5.5a2 2 0 0 1 2-2Z" fill="currentColor" opacity=".13"/>
                                    <path d="M15 3.5V8h4M7 3.5h8l4 4V20a1 1 0 0 1-1 1H7a2 2 0 0 1-2-2V5.5a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                    <path d="M8 16.5v-5h1.7a1.5 1.5 0 0 1 0 3H8m5 2v-5h1.4c1.4 0 2.1.9 2.1 2.5s-.7 2.5-2.1 2.5H13Z" stroke="currentColor" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>

                            <span>
                                <strong>
                                    {{ $row['PRINT_STATUS'] ?? '-' }}
                                </strong>

                                <small>
                                    @if($printStatus === 'PERLU GENERATE ULANG')
                                        PDF resmi perlu digenerate ulang oleh Apps Script.
                                    @elseif($printStatus === 'BELUM DIBUAT')
                                        PDF resmi belum dibuat.
                                    @elseif($officialPdfUrl === '')
                                        PDF_URL belum tersinkron. Jalankan backfillPdfUrlFromExistingNotes().
                                    @else
                                        Form belum berstatus SIAP DICETAK.
                                    @endif
                                </small>
                            </span>

                            <span class="aa-status">Belum Siap</span>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <section class="ssd-card">
            <div class="ssd-card-head">
                <h2>Workflow Detail</h2>
                <p>Data actor dan timestamp dari spreadsheet.</p>
            </div>

            <div class="ssd-card-body">
                <div class="ssd-meta-grid" style="grid-template-columns:1fr 1fr">
                    <div class="ssd-meta">
                        <small>GL / QCC By</small>
                        <strong>{{ $row['GL_QCC_BY'] ?? '-' }}</strong>
                    </div>

                    <div class="ssd-meta">
                        <small>GL / QCC At</small>
                        <strong>{{ $row['GL_QCC_AT'] ?? '-' }}</strong>
                    </div>

                    <div class="ssd-meta">
                        <small>SH By</small>
                        <strong>{{ $row['SH_BY'] ?? '-' }}</strong>
                    </div>

                    <div class="ssd-meta">
                        <small>SH At</small>
                        <strong>{{ $row['SH_AT'] ?? '-' }}</strong>
                    </div>

                    <div class="ssd-meta">
                        <small>DH / PM By</small>
                        <strong>{{ $row['DH_PM_BY'] ?? '-' }}</strong>
                    </div>

                    <div class="ssd-meta">
                        <small>DH / PM At</small>
                        <strong>{{ $row['DH_PM_AT'] ?? '-' }}</strong>
                    </div>

                    <div class="ssd-meta">
                        <small>GL OPD</small>
                        <strong>{{ $row['STATUS_GL_OPD'] ?? '-' }}</strong>
                    </div>

                    <div class="ssd-meta">
                        <small>GL OPD At</small>
                        <strong>{{ $row['GL_OPD_AT'] ?? '-' }}</strong>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    function bindWorkflowForm(options) {
        const form = document.getElementById(options.formId);

        if (!form) {
            return;
        }

        const note = document.getElementById(options.noteId);
        const decisionInput = document.getElementById(options.decisionId);

        form.addEventListener('submit', function (event) {
            const submitter = event.submitter;
            const decision = submitter
                ? String(submitter.dataset.decision || '')
                : '';

            if (!decision) {
                event.preventDefault();
                alert('Keputusan workflow tidak terbaca. Silakan klik ulang tombol.');
                return;
            }

            if (decisionInput) {
                decisionInput.value = decision;
            }

            const noteValue = note
                ? String(note.value || '').trim()
                : '';

            if (
                options.noteRequiredDecisions.includes(decision)
                && noteValue.length < 5
            ) {
                event.preventDefault();
                alert(options.noteRequiredMessage);

                if (note) {
                    note.focus();
                }

                return;
            }

            const label = options.labels[decision] || decision;

            if (
                !confirm(
                    'Konfirmasi keputusan '
                    + label
                    + ' untuk {{ $row['NO_SS'] ?? '' }}?'
                )
            ) {
                event.preventDefault();
                return;
            }

            /*
             * Hidden decision sudah diisi sebelum tombol di-disable,
             * sehingga field decision tetap terkirim ke Laravel.
             */
            form.querySelectorAll('button[type="submit"]')
                .forEach(function (button) {
                    button.disabled = true;
                });
        });
    }

    bindWorkflowForm({
        formId: 'glWorkflowForm',
        noteId: 'glWorkflowNote',
        decisionId: 'glWorkflowDecision',
        noteRequiredDecisions: ['REVISION', 'REJECTED'],
        noteRequiredMessage:
            'Catatan / alasan wajib minimal 5 karakter untuk REVISI atau TOLAK.',
        labels: {
            VERIFIED: 'VERIFIED',
            REVISION: 'REVISI',
            REJECTED: 'TOLAK'
        }
    });

    bindWorkflowForm({
        formId: 'shWorkflowForm',
        noteId: 'shWorkflowNote',
        decisionId: 'shWorkflowDecision',
        noteRequiredDecisions: ['REJECTED'],
        noteRequiredMessage:
            'Catatan / alasan wajib minimal 5 karakter untuk TOLAK.',
        labels: {
            APPROVED: 'SETUJUI SH',
            REJECTED: 'TOLAK SH'
        }
    });

    bindWorkflowForm({
        formId: 'dhPmWorkflowForm',
        noteId: 'dhPmWorkflowNote',
        decisionId: 'dhPmWorkflowDecision',
        noteRequiredDecisions: ['REJECTED'],
        noteRequiredMessage:
            'Catatan / alasan wajib minimal 5 karakter untuk REJECT.',
        labels: {
            APPROVED: 'APPROVE DH/PM',
            REJECTED: 'REJECT DH/PM'
        }
    });
});
</script>

@endsection