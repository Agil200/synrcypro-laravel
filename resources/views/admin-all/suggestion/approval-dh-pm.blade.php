@extends('admin-all.layout')

@section('admin-content')
<div class="dhpm-page">
<div class="aa-page-title">
    <div>
        <h1>Persetujuan DH / PM</h1>
    </div>

    <div class="aa-title-actions">
        <a href="{{ route('admin-all.suggestion.monitoring') }}" class="aa-action-button">
            Monitoring Data
        </a>

        <a href="{{ route('admin-all.suggestion.index') }}" class="aa-action-button">
            Dashboard Suggestion
        </a>
    </div>
</div>

@if (session('success'))
    <div class="aa-info-strip">
        <strong>{{ session('success') }}</strong>
    </div>
@endif

@if (session('error'))
    <div class="aa-info-strip" style="background:#fff1f2;border-color:#fecaca;color:#b91c1c;">
        <strong>{{ session('error') }}</strong>
    </div>
@endif

@if ($suggestionIntegration['connected'] ?? false)
    <div class="aa-info-strip">
        <strong>DATABASE_SS terhubung.</strong>
    </div>
@else
    <div class="aa-info-strip" style="background:#fff1f2;border-color:#fecaca;color:#b91c1c;">
        <strong>
            {{ $suggestionIntegration['message'] ?? 'Integrasi tidak tersedia.' }}
        </strong>
    </div>
@endif

<style>
    .dhpm-stats {
        display:grid;
        grid-template-columns:repeat(3,minmax(0,1fr));
        gap:10px;
        margin-top:10px;
    }

    .dhpm-stat {
        position:relative;
        min-width:0;
        min-height:78px;
        padding:11px 12px 10px 48px;
        border:1px solid var(--stat-border, #d9e0e7);
        background:var(--stat-bg, #fff);
    }

    .dhpm-stat-icon {
        position:absolute;
        top:13px;
        left:11px;
        display:grid;
        width:28px;
        height:28px;
        place-items:center;
        border-radius:8px;
        color:#fff;
        background:var(--stat-color, #64748b);
        font-size:8px;
        font-weight:900;
    }

    .dhpm-stat.pending {
        --stat-color:#ef7d00;
        --stat-border:#ffd3a3;
        --stat-bg:#fff8ef;
        --stat-text:#9b5700;
    }

    .dhpm-stat.approved {
        --stat-color:#0f78ef;
        --stat-border:#b7d9ff;
        --stat-bg:#f2f8ff;
        --stat-text:#0d63b7;
    }

    .dhpm-stat.rejected {
        --stat-color:#dc3545;
        --stat-border:#f1bdc3;
        --stat-bg:#fff3f4;
        --stat-text:#a72632;
    }

    .dhpm-stat small {
        display:block;
        color:var(--stat-text, #657387);
        font-size:8px;
        font-weight:800;
        letter-spacing:.05em;
        text-transform:uppercase;
    }

    .dhpm-stat strong {
        display:block;
        margin-top:4px;
        color:#0f2b46;
        font-size:23px;
        line-height:1;
    }

    .dhpm-stat span {
        display:block;
        margin-top:7px;
        color:#7a8797;
        font-size:10px;
    }

    .dhpm-table-wrap {
        overflow-x:auto;
    }

    .dhpm-table {
        width:100%;
        min-width:1080px;
        border-collapse:collapse;
        font-size:10px;
    }

    .dhpm-table th {
        padding:10px 12px;
        background:#f5f7fa;
        color:#405064;
        text-align:left;
        font-size:9px;
        font-weight:900;
        letter-spacing:.04em;
        text-transform:uppercase;
        white-space:nowrap;
    }

    .dhpm-table td {
        padding:11px 12px;
        border-top:1px solid #e6ebf0;
        color:#24364b;
        vertical-align:middle;
    }

    .dhpm-table .ss-link {
        color:#0f78ef;
        font-weight:900;
        text-decoration:none;
        white-space:nowrap;
    }

    .dhpm-badge {
        display:inline-flex;
        align-items:center;
        min-height:24px;
        padding:4px 9px;
        border-radius:999px;
        font-size:9px;
        font-weight:900;
        white-space:nowrap;
    }

    .dhpm-badge.pending {
        background:#fff3cd;
        color:#8a5700;
    }

    .dhpm-badge.approved {
        background:#dcfce7;
        color:#087443;
    }

    .dhpm-badge.rejected {
        background:#fee2e2;
        color:#b91c1c;
    }

    .dhpm-badge.waiting {
        background:#eef2f6;
        color:#657387;
    }

    @media (max-width: 900px) {
        .dhpm-stats {
            grid-template-columns:repeat(2,minmax(0,1fr));
        }
    }

/* ==========================================================
       FIXED INNER SHELL — PERSETUJUAN DH / PM
       Page title, connection strip, statistik dan queue header tetap.
       Hanya baris queue yang scroll.
       ========================================================== */
    #adminAllShell .aa-main {
        min-height: 0 !important;
        overflow: hidden !important;
    }

    #adminAllShell .aa-content {
        height: 100% !important;
        min-height: 0 !important;
        overflow: hidden !important;
    }

    .dhpm-page {
        width: 100%;
        height: 100%;
        min-height: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .dhpm-page > .aa-page-title,
    .dhpm-page > .aa-info-strip,
    .dhpm-stats {
        flex: 0 0 auto;
    }

    .dhpm-page > .aa-section {
        flex: 1 1 auto;
        min-height: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .dhpm-page > .aa-section > .aa-card-head {
        flex: 0 0 auto;
        position: relative;
        z-index: 5;
        background: #fff;
    }

    .dhpm-page > .aa-section > .aa-card-body {
        flex: 1 1 auto;
        min-height: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .dhpm-table-wrap {
        flex: 1 1 auto;
        min-height: 0;
        overflow: auto !important;
        overscroll-behavior: contain;
        scrollbar-gutter: stable;
    }

    .dhpm-table th {
        position: sticky;
        top: 0;
        z-index: 4;
        background: #f5f7fa;
    }

</style>

<div class="dhpm-stats">
    <div class="aa-card dhpm-stat pending">
        <span class="dhpm-stat-icon">!</span>
        <small>WAITING</small>
        <strong>{{ $dhPmQueue['summary']['pending'] ?? 0 }}</strong>
        <span>Approved by SH, waiting for DH/PM.</span>
    </div>

    <div class="aa-card dhpm-stat approved">
        <span class="dhpm-stat-icon">✓</span>
        <small>APPROVE</small>
        <strong>{{ $dhPmQueue['summary']['approved'] ?? 0 }}</strong>
        <span>DH/PM approval completed.</span>
    </div>

    <div class="aa-card dhpm-stat rejected">
        <span class="dhpm-stat-icon">×</span>
        <small>REJECT</small>
        <strong>{{ $dhPmQueue['summary']['rejected'] ?? 0 }}</strong>
        <span>Rejected at DH/PM stage.</span>
    </div>
</div>

<section class="aa-card aa-section" style="margin-top:10px;">
    <div class="aa-card-head">
        <div>
            <h2>Queue Persetujuan DH / PM</h2>
            <p>Klik NO SS / REVIEW DETAIL untuk menjalankan APPROVE / REJECT pada akun DH/PM atau ADMIN yang aktif.</p>
        </div>

        @if ($canReviewDhPm)
            <span class="aa-status active">DH/PM ACCESS</span>
        @else
        @endif
    </div>

    <div class="aa-card-body" style="padding:0;">
        <div class="dhpm-table-wrap">
            <table class="dhpm-table">
                <thead>
                    <tr>
                        <th>No SS</th>
                        <th>Submit</th>
                        <th>NRP</th>
                        <th>Nama</th>
                        <th>Departemen</th>
                        <th>Lokasi</th>
                        <th>Judul</th>
                        <th>SH By</th>
                        <th>Status DH / PM</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse (($dhPmQueue['rows'] ?? []) as $row)
                        @php
                            $mainStatus = strtoupper(
                                trim((string) ($row['STATUS'] ?? ''))
                            );

                            $statusDhPm = strtoupper(
                                trim((string) ($row['STATUS_DH_PM'] ?? ''))
                            );

                            $isPending = $mainStatus === 'APPROVED_SH';
                            $isApproved = $mainStatus === 'APPROVED_DH_PM';
                            $isRejected = $mainStatus === 'REJECTED_DH_PM';
                        @endphp

                        <tr>
                            <td>
                                <a
                                    href="{{ route('admin-all.suggestion.detail', ['noSs' => $row['NO_SS'] ?? '']) }}"
                                    class="ss-link"
                                >
                                    {{ $row['NO_SS'] ?? '-' }}
                                </a>
                            </td>

                            <td>{{ $row['SUBMIT_AT'] ?? '-' }}</td>
                            <td>{{ $row['NRP'] ?? '-' }}</td>
                            <td>{{ $row['NAMA_KARYAWAN'] ?? '-' }}</td>
                            <td>{{ $row['DEPARTEMEN'] ?? '-' }}</td>
                            <td>{{ $row['LOKASI'] ?? '-' }}</td>
                            <td>{{ $row['JUDUL_SS'] ?? '-' }}</td>
                            <td>{{ $row['SH_BY'] ?? '-' }}</td>

                            <td>
                                @if ($isPending)
                                    <span class="dhpm-badge pending">WAITING</span>
                                @elseif ($isApproved)
                                    <span class="dhpm-badge approved">APPROVE</span>
                                @elseif ($isRejected)
                                    <span class="dhpm-badge rejected">REJECT</span>
                                @else
                                    <span class="dhpm-badge waiting">
                                        {{ $statusDhPm !== '' ? $statusDhPm : 'WAITING' }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                <a
                                    href="{{ route('admin-all.suggestion.detail', [
                                        'noSs' => $row['NO_SS'] ?? '',
                                        'from' => 'dh-pm',
                                    ]) }}"
                                    class="aa-action-button {{ ($canReviewDhPm && $isPending) ? 'primary' : '' }}"
                                >
                                    {{ ($canReviewDhPm && $isPending) ? 'REVIEW DETAIL' : 'VIEW' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="padding:32px;text-align:center;color:#697789;">
                                Belum ada Suggestion pada tahap DH / PM.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
</div>

@endsection