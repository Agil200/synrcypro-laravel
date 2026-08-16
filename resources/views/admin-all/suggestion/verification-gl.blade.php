@extends('admin-all.layout')

@section('title', 'Verifikasi GL / QCC')

@section('admin-content')
@php
    $summary = $glQueue['summary'] ?? [];
    $rows = $glQueue['rows'] ?? [];

    $accessRole = strtoupper(
        trim((string) ($suggestionAccess['access'] ?? ''))
    );

    $statusTone = static function (?string $status): string {
        $status = strtoupper(trim((string) $status));

        return match ($status) {
            'SUBMITTED' => 'pending',
            'REVISION_GL_QCC' => 'revision',
            'VERIFIED_GL_QCC' => 'verified',
            'REJECTED_GL_QCC' => 'rejected',
            default => '',
        };
    };

    $statusLabel = static function (?string $status): string {
        $status = strtoupper(trim((string) $status));

        return match ($status) {
            'SUBMITTED' => 'Menunggu Verifikasi',
            'REVISION_GL_QCC' => 'Perlu Revisi',
            'VERIFIED_GL_QCC' => 'Verified GL / QCC',
            'REJECTED_GL_QCC' => 'Ditolak GL / QCC',
            default => ucwords(
                strtolower(
                    str_replace('_', ' ', $status)
                )
            ),
        };
    };
@endphp

<style>
    .glv-flash {
        margin-bottom: 8px;
        padding: 9px 11px;
        border: 1px solid #cfd8e2;
        border-radius: 8px;
        font-size: 8px;
        font-weight: 800;
        line-height: 1.45;
    }

    .glv-flash.success {
        border-color: #b8ead2;
        color: #0a6b42;
        background: #f0fff7;
    }

    .glv-flash.error {
        border-color: #f1bdc3;
        color: #a72632;
        background: #fff3f4;
    }

    .glv-page {
        width: 100%;
        min-width: 0;
    }

    .glv-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 8px;
    }

    .glv-head h1 {
        margin: 0;
        color: #051d39;
        font-size: clamp(21px, 2vw, 27px);
        letter-spacing: -.03em;
    }

    .glv-head p {
        margin: 3px 0 0;
        color: #5d6c7c;
        font-size: 9px;
    }

    .glv-access {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 9px;
        padding: 9px 11px;
        border: 1px solid #d9e0e7;
        border-radius: 9px;
        background: #fff;
        box-shadow: var(--aa-shadow);
    }

    .glv-access.allowed {
        border-color: #b8ead2;
        background: #f0fff7;
    }

    .glv-access.view-only {
        border-color: #d9e0e7;
        background: #f7f9fb;
    }

    .glv-access strong {
        display: block;
        color: #132b47;
        font-size: 9px;
    }

    .glv-access small {
        display: block;
        margin-top: 3px;
        color: #68778a;
        font-size: 7px;
        line-height: 1.4;
    }

    .glv-access-badge {
        display: inline-flex;
        min-height: 25px;
        align-items: center;
        padding: 0 9px;
        border-radius: 999px;
        color: #516176;
        background: #e9eef3;
        font-size: 7px;
        font-weight: 900;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .glv-access.allowed .glv-access-badge {
        color: #0a6b42;
        background: #dff6e9;
    }

    .glv-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 7px;
        margin-bottom: 9px;
    }

    .glv-stat {
        position: relative;
        min-width: 0;
        min-height: 71px;
        padding: 10px 11px 10px 45px;
        border: 1px solid var(--stat-border);
        border-radius: 9px;
        background: var(--stat-bg);
    }

    .glv-stat-icon {
        position: absolute;
        top: 12px;
        left: 10px;
        display: grid;
        width: 27px;
        height: 27px;
        place-items: center;
        border-radius: 8px;
        color: #fff;
        background: var(--stat-color);
        font-size: 9px;
        font-weight: 900;
    }

    .glv-stat small {
        display: block;
        color: var(--stat-text);
        font-size: 7px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .glv-stat strong {
        display: block;
        margin-top: 4px;
        color: #0c2343;
        font-size: 22px;
        line-height: 1;
    }

    .glv-stat span {
        display: block;
        margin-top: 5px;
        color: #68778a;
        font-size: 7px;
    }

    .glv-stat.pending {
        --stat-color:#ef7d00;
        --stat-border:#ffd3a3;
        --stat-bg:#fff8ef;
        --stat-text:#9b5700;
    }

    .glv-stat.revision {
        --stat-color:#d89b00;
        --stat-border:#f2dda0;
        --stat-bg:#fffbea;
        --stat-text:#856000;
    }

    .glv-stat.verified {
        --stat-color:#0f78ef;
        --stat-border:#b7d9ff;
        --stat-bg:#f2f8ff;
        --stat-text:#0d63b7;
    }

    .glv-stat.rejected {
        --stat-color:#dc3545;
        --stat-border:#f1bdc3;
        --stat-bg:#fff3f4;
        --stat-text:#a72632;
    }

    .glv-card {
        min-width: 0;
        overflow: hidden;
        border: 1px solid var(--aa-border);
        border-radius: 10px;
        background: #fff;
        box-shadow: var(--aa-shadow);
    }

    .glv-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 10px 11px;
        border-bottom: 1px solid var(--aa-border);
    }

    .glv-card-head h2 {
        margin: 0;
        font-size: 12px;
    }

    .glv-card-head p {
        margin: 2px 0 0;
        color: #68778a;
        font-size: 7px;
    }

    .glv-table-wrap {
        width: 100%;
        max-height: calc(100dvh - 390px);
        min-height: 0;
        overflow: auto;
        overscroll-behavior: contain;
        scrollbar-gutter: stable;
    }

    .glv-table {
        width: 100%;
        min-width: 1050px;
        border-collapse: collapse;
    }

    .glv-table th {
        position: sticky;
        top: 0;
        z-index: 3;
        padding: 8px 9px;
        border-bottom: 1px solid #e0e6ec;
        color: #536273;
        background: #f7f9fb;
        font-size: 7px;
        font-weight: 900;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .glv-table td {
        padding: 8px 9px;
        border-bottom: 1px solid #edf0f4;
        color: #24354b;
        font-size: 8px;
        vertical-align: middle;
    }

    .glv-table tbody tr:hover {
        background: #fbfcfe;
    }

    .glv-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .glv-table td:nth-child(6) {
        min-width: 240px;
        line-height: 1.35;
    }

    .glv-table td:nth-child(8) {
        max-width: 220px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .glv-no {
        color: #0f78ef;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
    }

    .glv-no:hover {
        text-decoration: underline;
    }

    .glv-status {
        display: inline-flex;
        min-height: 22px;
        align-items: center;
        padding: 0 7px;
        border-radius: 999px;
        font-size: 7px;
        font-weight: 900;
        white-space: nowrap;
    }

    .glv-status.pending {
        color:#9b5700;
        background:#fff0d8;
    }

    .glv-status.revision {
        color:#856000;
        background:#fff4bd;
    }

    .glv-status.verified {
        color:#0d63b7;
        background:#e5f2ff;
    }

    .glv-status.rejected {
        color:#a72632;
        background:#ffe8eb;
    }

    .glv-action {
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
        white-space: nowrap;
    }

    .glv-action.primary {
        border-color: #0f78ef;
        color: #fff;
        background: #0f78ef;
    }

    .glv-action.disabled {
        pointer-events: none;
        opacity: .48;
    }

    .glv-empty {
        padding: 25px 10px !important;
        color: #7b8797 !important;
        text-align: center;
    }

    @media (max-height: 760px) {
        .glv-table-wrap {
            max-height: calc(100dvh - 350px);
        }
    }

    @media (max-width: 850px) {
        .glv-table-wrap {
            max-height: calc(100dvh - 430px);
        }

        .glv-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .glv-head,
        .glv-access {
            align-items: stretch;
            flex-direction: column;
        }
    }
</style>

<div class="glv-page">
<div class="glv-head">
    <div>
        <h1>Verifikasi GL / QCC</h1>
        <p>
            Queue workflow Suggestion System.
            STEP 6A memvalidasi hak akses berdasarkan email login + ACCESS_ATASAN.
        </p>
    </div>

    <div class="aa-title-actions">
        <a
            href="{{ route('admin-all.suggestion.monitoring') }}"
            class="aa-action-button"
        >
            Monitoring Data
        </a>

        <a
            href="{{ route('admin-all.suggestion.index') }}"
            class="aa-action-button"
        >
            Dashboard Suggestion
        </a>


        @if($canReviewGl)
            <form
                method="POST"
                action="{{ route('admin-all.suggestion.verification-gl.bridge-check') }}"
                style="display:inline"
            >
                @csrf
                <button
                    type="submit"
                    class="aa-action-button primary"
                >
                    Test Apps Script Bridge
                </button>
            </form>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="glv-flash success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="glv-flash error">
        {{ session('error') }}
    </div>
@endif

<div class="glv-access {{ $canReviewGl ? 'allowed' : 'view-only' }}">
    <div>
        <strong>
            Login:
            {{ auth()->user()?->email ?? '-' }}
            •
            ACCESS_ATASAN:
            {{ $accessRole ?: 'TIDAK TERDAFTAR' }}
        </strong>

        <small>
            @if($canReviewGl)
                STATUS AKTIF dan AKSES {{ $accessRole }}.
                Akun ini berhak melakukan Verifikasi GL / QCC.
            @else
                Akun ini tetap dapat melihat data, tetapi tidak memiliki hak
                Verifikasi GL / QCC. User lain tetap VIEW ONLY.
            @endif
        </small>
    </div>

    <span class="glv-access-badge">
        {{ $canReviewGl ? 'GL/QCC ACCESS' : 'VIEW ONLY' }}
    </span>
</div>

<div class="glv-stats">
    <div class="glv-stat pending">
        <span class="glv-stat-icon">!</span>
        <small>Menunggu</small>
        <strong>{{ number_format($summary['pending'] ?? 0) }}</strong>
        <span>Submitted menunggu GL / QCC</span>
    </div>

    <div class="glv-stat revision">
        <span class="glv-stat-icon">R</span>
        <small>Perlu Revisi</small>
        <strong>{{ number_format($summary['revision'] ?? 0) }}</strong>
        <span>Masuk kembali ke tahap GL / QCC</span>
    </div>

    <div class="glv-stat verified">
        <span class="glv-stat-icon">✓</span>
        <small>Verified</small>
        <strong>{{ number_format($summary['verified'] ?? 0) }}</strong>
        <span>Sudah diverifikasi GL / QCC</span>
    </div>

    <div class="glv-stat rejected">
        <span class="glv-stat-icon">×</span>
        <small>Ditolak</small>
        <strong>{{ number_format($summary['rejected'] ?? 0) }}</strong>
        <span>Ditolak pada tahap GL / QCC</span>
    </div>
</div>

<section class="glv-card">
    <div class="glv-card-head">
        <div>
            <h2>Queue Verifikasi GL / QCC</h2>
            <p>
                STEP 6B aktif. Klik NO SS / Review Detail untuk membuka detail dan menjalankan VERIFIED / REVISI / TOLAK melalui Apps Script existing.
            </p>
        </div>

        <span class="aa-status {{ $canReviewGl ? 'active' : '' }}">
            {{ $canReviewGl ? 'Akses Aktif' : 'View Only' }}
        </span>
    </div>

    <div class="glv-table-wrap">
        <table class="glv-table">
            <thead>
                <tr>
                    <th>No SS</th>
                    <th>Submit</th>
                    <th>NRP</th>
                    <th>Nama</th>
                    <th>Lokasi</th>
                    <th>Judul</th>
                    <th>Status</th>
                    <th>GL / QCC By</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>
                            <a
                                href="{{ route('admin-all.suggestion.detail', ['noSs' => $row['NO_SS']]) }}"
                                class="glv-no"
                            >
                                {{ $row['NO_SS'] }}
                            </a>
                        </td>

                        <td>{{ $row['SUBMIT_AT'] ?? '-' }}</td>
                        <td>{{ $row['NRP'] ?? '-' }}</td>
                        <td><strong>{{ $row['NAMA_KARYAWAN'] ?? '-' }}</strong></td>
                        <td>{{ $row['LOKASI'] ?? '-' }}</td>
                        <td>{{ $row['JUDUL_SS'] ?? '-' }}</td>

                        <td>
                            <span class="glv-status {{ $statusTone($row['STATUS'] ?? '') }}">
                                {{ $statusLabel($row['STATUS'] ?? '') }}
                            </span>
                        </td>

                        <td>{{ $row['GL_QCC_BY'] ?? '-' }}</td>

                        <td>
                            @if(
                                $canReviewGl
                                && in_array(
                                    strtoupper(trim((string) ($row['STATUS'] ?? ''))),
                                    ['SUBMITTED', 'REVISION_GL_QCC'],
                                    true
                                )
                            )
                                <a
                                    href="{{ route('admin-all.suggestion.detail', ['noSs' => $row['NO_SS']]) }}"
                                    class="glv-action primary"
                                >
                                    Review Detail
                                </a>
                            @else
                                <a
                                    href="{{ route('admin-all.suggestion.detail', ['noSs' => $row['NO_SS']]) }}"
                                    class="glv-action"
                                >
                                    Lihat
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="glv-empty">
                            Belum ada data pada queue GL / QCC.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
</div>
@endsection
