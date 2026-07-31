@extends('layouts.app')

@section('title', 'BAST Asset — SYNRGYPRO')

@push('styles')
<style>
    :root {
        --bast-primary: #ef5b2a;
        --bast-primary-dark: #c94117;
        --bast-dark: #171717;
        --bast-text: #20242a;
        --bast-muted: #707782;
        --bast-border: #e7e9ed;
        --bast-bg: #f4f6f8;
    }

    .bast-page {
        min-height: calc(100vh - 70px);
        padding: 24px;
        background:
            radial-gradient(circle at top right, rgba(239, 91, 42, .09), transparent 320px),
            var(--bast-bg);
    }

    .bast-shell {
        max-width: 1500px;
        margin: 0 auto;
    }

    .bast-hero {
        position: relative;
        overflow: hidden;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        margin-bottom: 20px;
        padding: 24px 26px;
        color: #fff;
        border-radius: 18px;
        background: linear-gradient(110deg, #161616 0%, #2b2424 55%, #e65b2d 100%);
        box-shadow: 0 12px 30px rgba(35, 35, 35, .16);
    }

    .bast-hero::after {
        content: "";
        position: absolute;
        top: -80px;
        right: 110px;
        width: 220px;
        height: 220px;
        border: 35px solid rgba(255,255,255,.06);
        border-radius: 50%;
    }

    .bast-hero-content,
    .bast-hero-action {
        position: relative;
        z-index: 1;
    }

    .bast-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 7px;
        color: rgba(255,255,255,.72);
        font-size: .77rem;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .bast-hero h1 {
        margin: 0;
        font-size: clamp(1.45rem, 2.4vw, 2rem);
        font-weight: 800;
    }

    .bast-hero p {
        max-width: 720px;
        margin: 8px 0 0;
        color: rgba(255,255,255,.74);
        font-size: .92rem;
    }

    .btn-add-bast {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        min-height: 46px;
        padding: 0 18px;
        color: var(--bast-dark);
        background: #fff;
        border: 0;
        border-radius: 12px;
        font-weight: 800;
        box-shadow: 0 8px 20px rgba(0,0,0,.15);
        transition: .2s ease;
        white-space: nowrap;
    }

    .btn-add-bast:hover {
        color: var(--bast-primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(0,0,0,.2);
    }

    .bast-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }

    .stat-card {
        display: flex;
        align-items: center;
        gap: 14px;
        min-height: 94px;
        padding: 18px;
        background: #fff;
        border: 1px solid rgba(231,233,237,.95);
        border-radius: 15px;
        box-shadow: 0 6px 18px rgba(27, 31, 35, .05);
    }

    .stat-icon {
        width: 46px;
        height: 46px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        color: var(--bast-primary);
        background: rgba(239,91,42,.10);
        border-radius: 13px;
    }

    .stat-label {
        display: block;
        margin-bottom: 3px;
        color: var(--bast-muted);
        font-size: .76rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .stat-value {
        display: block;
        color: var(--bast-text);
        font-size: 1.08rem;
        font-weight: 800;
        line-height: 1.2;
    }

    .bast-card {
        overflow: hidden;
        background: #fff;
        border: 1px solid var(--bast-border);
        border-radius: 18px;
        box-shadow: 0 8px 24px rgba(27,31,35,.06);
    }

    .bast-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 20px;
        border-bottom: 1px solid var(--bast-border);
    }

    .bast-card-title {
        margin: 0;
        color: var(--bast-text);
        font-size: 1rem;
        font-weight: 800;
    }

    .bast-card-subtitle {
        margin: 4px 0 0;
        color: var(--bast-muted);
        font-size: .79rem;
    }

    .bast-tools {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .bast-search {
        position: relative;
        width: min(320px, 36vw);
    }

    .bast-search svg {
        position: absolute;
        top: 50%;
        left: 13px;
        transform: translateY(-50%);
        color: #9298a1;
        pointer-events: none;
    }

    .bast-search input {
        width: 100%;
        height: 42px;
        padding: 0 14px 0 39px;
        border: 1px solid var(--bast-border);
        border-radius: 11px;
        background: #f9fafb;
        color: var(--bast-text);
        outline: none;
        transition: .2s ease;
    }

    .bast-search input:focus {
        background: #fff;
        border-color: rgba(239,91,42,.65);
        box-shadow: 0 0 0 4px rgba(239,91,42,.10);
    }

    .btn-print-list {
        width: 42px;
        height: 42px;
        display: inline-grid;
        place-items: center;
        color: #3e434a;
        background: #fff;
        border: 1px solid var(--bast-border);
        border-radius: 11px;
        transition: .2s ease;
    }

    .btn-print-list:hover {
        color: var(--bast-primary-dark);
        background: #fff7f3;
        border-color: rgba(239,91,42,.35);
    }

    .bast-table {
        margin: 0;
        min-width: 950px;
    }

    .bast-table thead th {
        padding: 14px 16px;
        color: #737a85;
        background: #fafbfc;
        border-bottom: 1px solid var(--bast-border);
        font-size: .69rem;
        font-weight: 800;
        letter-spacing: .07em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .bast-table tbody td {
        padding: 15px 16px;
        color: #343940;
        border-color: #eff1f3;
        font-size: .87rem;
        vertical-align: middle;
    }

    .bast-table tbody tr:hover td {
        background: #fffaf7;
    }

    .employee-cell {
        display: flex;
        align-items: center;
        gap: 11px;
        min-width: 190px;
    }

    .employee-avatar {
        width: 36px;
        height: 36px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        color: #fff;
        background: linear-gradient(135deg, #242424, #ef5b2a);
        border-radius: 10px;
        font-size: .75rem;
        font-weight: 800;
    }

    .employee-name {
        display: block;
        color: var(--bast-text);
        font-weight: 750;
    }

    .employee-nrp {
        display: block;
        color: var(--bast-muted);
        font-size: .74rem;
    }

    .badge-position {
        display: inline-flex;
        align-items: center;
        padding: 6px 9px;
        color: #4f5660;
        background: #f0f2f5;
        border: 1px solid #e4e7eb;
        border-radius: 999px;
        font-size: .69rem;
        font-weight: 800;
    }

    .asset-code {
        color: #30353b;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: .8rem;
        font-weight: 700;
    }

    .btn-document {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 34px;
        padding: 0 11px;
        color: #bf321e;
        background: #fff5f3;
        border: 1px solid #ffd7d0;
        border-radius: 9px;
        font-size: .75rem;
        font-weight: 800;
        text-decoration: none;
        transition: .2s ease;
    }

    .btn-document:hover {
        color: #fff;
        background: #d9462d;
        border-color: #d9462d;
    }

    .empty-state {
        padding: 52px 20px !important;
        text-align: center;
    }

    .empty-icon {
        width: 68px;
        height: 68px;
        display: grid;
        place-items: center;
        margin: 0 auto 14px;
        color: var(--bast-primary);
        background: #fff3ed;
        border: 1px dashed #f4b59e;
        border-radius: 20px;
    }

    .empty-state h3 {
        margin: 0 0 6px;
        color: var(--bast-text);
        font-size: 1rem;
        font-weight: 800;
    }

    .empty-state p {
        margin: 0 auto 16px;
        max-width: 470px;
        color: var(--bast-muted);
        font-size: .83rem;
    }

    .empty-search-row {
        display: none;
    }

    .bast-pagination {
        padding: 15px 20px;
        border-top: 1px solid var(--bast-border);
    }

    .alert-bast {
        border: 0;
        border-radius: 13px;
        box-shadow: 0 5px 15px rgba(27,31,35,.05);
    }

    /* Modal pemilihan kategori */
    .category-modal .modal-content {
        overflow: hidden;
        border: 0;
        border-radius: 20px;
        box-shadow: 0 22px 70px rgba(0,0,0,.22);
    }

    .category-modal .modal-header {
        align-items: flex-start;
        padding: 22px 23px;
        color: #fff;
        background: linear-gradient(110deg, #171717 0%, #302525 58%, #e85b2c 100%);
        border: 0;
    }

    .category-modal .modal-title {
        font-size: 1.12rem;
        font-weight: 800;
    }

    .category-modal .btn-close {
        margin-top: 1px;
        filter: invert(1) grayscale(100%) brightness(200%);
    }

    .category-modal .modal-body {
        padding: 22px;
        background: #f7f8fa;
    }

    .category-intro {
        margin: 0 0 16px;
        color: #6f7680;
        font-size: .82rem;
    }

    .category-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .category-option {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 13px;
        min-height: 84px;
        padding: 15px;
        text-align: left;
        color: #2f343a;
        background: #fff;
        border: 1px solid #e3e6ea;
        border-radius: 14px;
        box-shadow: 0 5px 14px rgba(27,31,35,.04);
        transition: .2s ease;
    }

    .category-option:hover,
    .category-option:focus {
        color: #2f343a;
        background: #fff8f4;
        border-color: rgba(239,91,42,.55);
        box-shadow: 0 9px 20px rgba(239,91,42,.10);
        transform: translateY(-2px);
        outline: none;
    }

    .category-option:last-child:nth-child(odd) {
        grid-column: 1 / -1;
    }

    .category-option-icon {
        width: 46px;
        height: 46px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        color: var(--bast-primary);
        background: rgba(239,91,42,.10);
        border-radius: 13px;
    }

    .category-option-title {
        display: block;
        margin-bottom: 3px;
        color: #24282e;
        font-size: .86rem;
        font-weight: 800;
    }

    .category-option-note {
        display: block;
        color: #838a94;
        font-size: .72rem;
        line-height: 1.35;
    }

    .category-modal .modal-footer {
        padding: 14px 22px;
        background: #fff;
        border-top: 1px solid var(--bast-border);
    }

    @media (max-width: 575.98px) {
        .category-grid { grid-template-columns: 1fr; }
        .category-option:last-child:nth-child(odd) { grid-column: auto; }
    }

    /* Modal form */
    .bast-modal .modal-content {
        overflow: hidden;
        border: 0;
        border-radius: 20px;
        box-shadow: 0 22px 70px rgba(0,0,0,.22);
    }

    .bast-modal .modal-header {
        align-items: flex-start;
        padding: 21px 23px;
        color: #fff;
        background: linear-gradient(110deg, #171717 0%, #302525 58%, #e85b2c 100%);
        border: 0;
    }

    .bast-modal .modal-title {
        font-size: 1.12rem;
        font-weight: 800;
    }

    .modal-heading-note {
        margin-top: 4px;
        color: rgba(255,255,255,.68);
        font-size: .77rem;
    }

    .bast-modal .btn-close {
        margin-top: 1px;
        filter: invert(1) grayscale(100%) brightness(200%);
    }

    .bast-modal .modal-body {
        padding: 23px;
        background: #fbfbfc;
    }

    .form-section-title {
        display: flex;
        align-items: center;
        gap: 9px;
        margin: 3px 0 14px;
        color: #343940;
        font-size: .79rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .form-section-title::after {
        content: "";
        height: 1px;
        flex: 1;
        background: var(--bast-border);
    }

    .bast-modal .form-label {
        display: block;
        width: 100%;
        margin-bottom: 0;
        color: #454b53;
        font-size: .75rem;
        font-weight: 800;
    }

    .bast-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .bast-section-card {
        padding: 18px;
        background: #ffffff;
        border: 1px solid var(--bast-border);
        border-radius: 16px;
        box-shadow: 0 5px 14px rgba(27, 31, 35, .04);
    }

    .bast-modal .row.g-3 {
        --bs-gutter-x: 18px;
        --bs-gutter-y: 18px;
    }

    .bast-modal .form-control,
    .bast-modal .form-select {
        width: 100%;
    }

    .required-mark {
        color: var(--bast-primary);
    }

    .bast-modal .form-control,
    .bast-modal .form-select {
        min-height: 44px;
        border-color: #dde1e6;
        border-radius: 11px;
        color: #2f343a;
        font-size: .86rem;
        box-shadow: none;
    }

    .bast-modal .form-control:focus,
    .bast-modal .form-select:focus {
        border-color: rgba(239,91,42,.7);
        box-shadow: 0 0 0 4px rgba(239,91,42,.10);
    }

    .bast-modal .readonly-field {
        background: #f0f2f4;
        color: #656c76;
    }

    .upload-box {
        position: relative;
        padding: 18px;
        text-align: center;
        background: #fff;
        border: 1.5px dashed #ccd1d7;
        border-radius: 13px;
        transition: .2s ease;
    }

    .upload-box:hover {
        background: #fff8f4;
        border-color: #ef8c68;
    }

    .upload-box input[type="file"] {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    .upload-box strong {
        display: block;
        margin-top: 7px;
        color: #3f454d;
        font-size: .82rem;
    }

    .upload-box small {
        display: block;
        margin-top: 3px;
        color: #868d96;
        font-size: .73rem;
    }

    .bast-modal .modal-footer {
        padding: 16px 23px;
        background: #fff;
        border-top: 1px solid var(--bast-border);
    }

    .btn-modal-cancel,
    .btn-modal-save {
        min-height: 43px;
        padding: 0 18px;
        border-radius: 11px;
        font-size: .82rem;
        font-weight: 800;
    }

    .btn-modal-cancel {
        color: #555c65;
        background: #f1f3f5;
        border: 1px solid #e4e7eb;
    }

    .btn-modal-save {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #fff;
        background: linear-gradient(135deg, #202020, #ef5b2a);
        border: 0;
        box-shadow: 0 8px 18px rgba(239,91,42,.22);
    }

    .btn-modal-save:hover {
        color: #fff;
        transform: translateY(-1px);
    }

    @media (max-width: 991.98px) {
        .bast-page { padding: 18px; }
        .bast-stats { grid-template-columns: 1fr 1fr; }
        .bast-stats .stat-card:last-child { grid-column: 1 / -1; }
        .bast-card-header { align-items: flex-start; flex-direction: column; }
        .bast-tools { width: 100%; }
        .bast-search { width: 100%; }
    }

    @media (max-width: 767.98px) {
        .bast-page { padding: 13px; }
        .bast-hero { align-items: stretch; flex-direction: column; padding: 21px; border-radius: 15px; }
        .btn-add-bast { width: 100%; }
        .bast-stats { grid-template-columns: 1fr; }
        .bast-stats .stat-card:last-child { grid-column: auto; }
        .bast-card { border-radius: 14px; }
        .bast-card-header { padding: 16px; }
    }

    @media print {
        .bast-hero-action,
        .bast-tools,
        .sidebar,
        .navbar,
        .topbar,
        .bast-pagination {
            display: none !important;
        }

        .bast-page {
            padding: 0;
            background: #fff;
        }

        .bast-hero {
            color: #000;
            background: #fff;
            box-shadow: none;
            border: 1px solid #ddd;
        }

        .bast-hero p,
        .bast-eyebrow {
            color: #555;
        }

        .bast-card,
        .stat-card {
            box-shadow: none;
        }
    }
</style>
@endpush

@section('content')
@php
    $assetCount = method_exists($assets, 'total') ? $assets->total() : $assets->count();
@endphp

<div class="bast-page">
    <div class="bast-shell">
        <section class="bast-hero">
            <div class="bast-hero-content">
                <div class="bast-eyebrow">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 5h18v14H3z"/><path d="M7 9h10M7 13h6"/>
                    </svg>
                    Manajemen Asset
                </div>
                <h1>Berita Acara Serah Terima Asset</h1>
                <p>Kelola data penyerahan asset, identitas penerima, nomor perangkat, tanggal pengambilan, dan dokumen BAST dalam satu halaman.</p>
            </div>

            <div class="bast-hero-action">
                <button type="button" class="btn-add-bast" data-bs-toggle="modal" data-bs-target="#bastCategoryModal">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    Tambah Berita Acara
                </button>
            </div>
        </section>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show alert-bast" role="alert">
                <strong>Berhasil.</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show alert-bast" role="alert">
                <strong>Gagal.</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        @endif

        <div class="bast-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 2h9l5 5v15H6z"/><path d="M14 2v6h6M9 13h8M9 17h6"/>
                    </svg>
                </div>
                <div>
                    <span class="stat-label">Total Berita Acara</span>
                    <span class="stat-value">{{ number_format($assetCount) }} Dokumen</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="16" rx="2"/><path d="M8 2v4M16 2v4M3 9h18"/>
                    </svg>
                </div>
                <div>
                    <span class="stat-label">Kategori Asset</span>
                    <span class="stat-value">{{ $category }}</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 21h18M5 21V7l7-4 7 4v14M9 10h6M9 14h6"/>
                    </svg>
                </div>
                <div>
                    <span class="stat-label">Departemen</span>
                    <span class="stat-value">Produksi</span>
                </div>
            </div>
        </div>

        <section class="bast-card">
            <div class="bast-card-header">
                <div>
                    <h2 class="bast-card-title">Daftar BAST {{ $category }}</h2>
                    <p class="bast-card-subtitle">Cari berdasarkan nama, NRP, jabatan, nomor asset, atau serial number.</p>
                </div>

                <div class="bast-tools">
                    <label class="bast-search" for="bastSearch">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
                        </svg>
                        <input type="search" id="bastSearch" placeholder="Cari data BAST..." autocomplete="off">
                    </label>

                    <button type="button" class="btn-print-list" id="printBast" title="Cetak daftar BAST" aria-label="Cetak daftar BAST">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table bast-table align-middle" id="bastTable">
                    <thead>
                        <tr>
                            <th>Penerima</th>
                            <th>Jabatan</th>
                            <th>Nomor Asset</th>
                            <th>Serial Number</th>
                            <th>Tanggal Ambil</th>
                            <th class="text-center">Dokumen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $item)
                            @php
                                $initials = collect(explode(' ', trim($item->nama)))
                                    ->filter()
                                    ->take(2)
                                    ->map(fn ($word) => strtoupper(mb_substr($word, 0, 1)))
                                    ->implode('');
                            @endphp
                            <tr class="bast-data-row">
                                <td>
                                    <div class="employee-cell">
                                        <div class="employee-avatar">{{ $initials ?: 'U' }}</div>
                                        <div>
                                            <span class="employee-name">{{ $item->nama }}</span>
                                            <span class="employee-nrp">NRP {{ $item->nrp }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge-position">{{ $item->jabatan }}</span></td>
                                <td><span class="asset-code">{{ $item->no_asset ?: '-' }}</span></td>
                                <td><span class="asset-code">{{ $item->serial_number ?: '-' }}</span></td>
                                <td>
                                    {{ $item->tanggal_ambil
                                        ? \Carbon\Carbon::parse($item->tanggal_ambil)->translatedFormat('d M Y')
                                        : '-' }}
                                </td>
                                <td class="text-center">
                                    @if($item->file_pdf)
                                        <a href="{{ asset('storage/' . $item->file_pdf) }}" target="_blank" rel="noopener" class="btn-document">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 15h8M8 11h3"/>
                                            </svg>
                                            Lihat PDF
                                        </a>
                                    @else
                                        <span class="text-muted small">Belum ada file</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr id="initialEmptyRow">
                                <td colspan="6" class="empty-state">
                                    <div class="empty-icon">
                                        <svg width="31" height="31" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path d="M6 2h9l5 5v15H6z"/><path d="M14 2v6h6M9 13h6M9 17h4"/>
                                        </svg>
                                    </div>
                                    <h3>Belum ada berita acara</h3>
                                    <p>Data BAST untuk kategori <strong>{{ $category }}</strong> belum tersedia. Tambahkan dokumen pertama melalui tombol di bawah ini.</p>
                                    <button type="button" class="btn btn-dark btn-sm px-3 rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#bastCategoryModal">
                                        + Tambah Berita Acara
                                    </button>
                                </td>
                            </tr>
                        @endforelse

                        <tr id="emptySearchRow" class="empty-search-row">
                            <td colspan="6" class="empty-state">
                                <div class="empty-icon">
                                    <svg width="31" height="31" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5M8.5 8.5l5 5M13.5 8.5l-5 5"/>
                                    </svg>
                                </div>
                                <h3>Data tidak ditemukan</h3>
                                <p>Coba gunakan kata kunci lain untuk mencari data BAST.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if(method_exists($assets, 'links'))
                <div class="bast-pagination">
                    {{ $assets->withQueryString()->links() }}
                </div>
            @endif
        </section>
    </div>
</div>

{{-- Modal Pilih Kategori BAST --}}
<div class="modal fade category-modal" id="bastCategoryModal" tabindex="-1" aria-labelledby="bastCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="bastCategoryModalLabel">Pilih Kategori BAST</h5>
                    <div class="modal-heading-note">Pilih jenis asset sebelum mengisi berita acara serah terima.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body">
                <p class="category-intro">Kategori yang dipilih akan otomatis dimasukkan ke kolom <strong>Jenis Asset</strong> pada formulir.</p>

                <div class="category-grid">
                    <button type="button" class="category-option" data-bast-category="Senter P101X">
                        <span class="category-option-icon">
                            <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 3h6l1 4H8l1-4Z"/><path d="M8 7h8l-2 14h-4L8 7Z"/><path d="M10 11h4"/>
                            </svg>
                        </span>
                        <span>
                            <span class="category-option-title">BAST Senter P101X</span>
                            <span class="category-option-note">Serah terima perangkat senter tipe P101X.</span>
                        </span>
                    </button>

                    <button type="button" class="category-option" data-bast-category="Laser">
                        <span class="category-option-icon">
                            <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 12h7M15 12h5M14 8l2-2M14 16l2 2"/><circle cx="12" cy="12" r="2"/>
                            </svg>
                        </span>
                        <span>
                            <span class="category-option-title">BAST Laser</span>
                            <span class="category-option-note">Serah terima perangkat atau alat laser.</span>
                        </span>
                    </button>

                    <button type="button" class="category-option" data-bast-category="Laptop">
                        <span class="category-option-icon">
                            <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="4" y="4" width="16" height="11" rx="1"/><path d="M2 19h20M8 19l1-4h6l1 4"/>
                            </svg>
                        </span>
                        <span>
                            <span class="category-option-title">BAST Laptop</span>
                            <span class="category-option-note">Serah terima laptop dan perangkat pendukung.</span>
                        </span>
                    </button>

                    <button type="button" class="category-option" data-bast-category="Radio HT">
                        <span class="category-option-icon">
                            <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="7" y="5" width="10" height="16" rx="2"/><path d="M10 2h4M12 5V2M10 10h4M12 15h.01"/>
                            </svg>
                        </span>
                        <span>
                            <span class="category-option-title">BAST Radio HT</span>
                            <span class="category-option-note">Serah terima radio komunikasi handheld.</span>
                        </span>
                    </button>

                    <button type="button" class="category-option" data-bast-category="Lainnya">
                        <span class="category-option-icon">
                            <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 17.5h7M17.5 14v7"/>
                            </svg>
                        </span>
                        <span>
                            <span class="category-option-title">BAST Lainnya</span>
                            <span class="category-option-note">Gunakan untuk jenis asset di luar kategori utama.</span>
                        </span>
                    </button>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-modal-cancel" data-bs-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Tambah Berita Acara Asset --}}
<div class="modal fade bast-modal" id="bastFormModal" tabindex="-1" aria-labelledby="bastFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="bastFormModalLabel">Tambah Berita Acara Asset</h5>
                    <div class="modal-heading-note" id="bastFormCategoryNote">Isi data penerima dan asset dengan lengkap, lalu unggah dokumen BAST dalam format PDF.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <form action="{{ route('bast.store') }}" method="POST" enctype="multipart/form-data" id="bastForm">
                @csrf

                <div class="modal-body">
                    @if($errors->any())
                        <div class="alert alert-danger rounded-3">
                            <strong>Data belum dapat disimpan.</strong>
                            <ul class="mb-0 mt-2 ps-3 small">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-section-title">Data Penerima</div>
                    <div class="bast-section-card">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="bast-field">
                                    <label for="nrp" class="form-label">NRP <span class="required-mark">*</span></label>
                                    <input
                                        type="text"
                                        id="nrp"
                                        name="nrp"
                                        value="{{ old('nrp') }}"
                                        class="form-control @error('nrp') is-invalid @enderror"
                                        placeholder="Contoh: 12345678"
                                        required
                                        autocomplete="off"
                                    >
                                    @error('nrp')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="bast-field">
                                    <label for="nama" class="form-label">Nama Lengkap <span class="required-mark">*</span></label>
                                    <input
                                        type="text"
                                        id="nama"
                                        name="nama"
                                        value="{{ old('nama') }}"
                                        class="form-control @error('nama') is-invalid @enderror"
                                        placeholder="Masukkan nama lengkap penerima"
                                        required
                                        autocomplete="name"
                                    >
                                    @error('nama')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="bast-field">
                                    <label for="jabatan" class="form-label">Jabatan <span class="required-mark">*</span></label>
                                    <select id="jabatan" name="jabatan" class="form-select @error('jabatan') is-invalid @enderror" required>
                                        <option value="">Pilih jabatan</option>
                                        @foreach(['DUMPMAN', 'GL', 'SH', 'DH'] as $jabatan)
                                            <option value="{{ $jabatan }}" @selected(old('jabatan') === $jabatan)>{{ $jabatan }}</option>
                                        @endforeach
                                    </select>
                                    @error('jabatan')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="bast-field">
                                    <label for="departemen" class="form-label">Departemen</label>
                                    <input type="text" id="departemen" name="departemen" class="form-control readonly-field" value="{{ old('departemen', 'PRODUKSI') }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section-title mt-4">Data Asset</div>
                    <div class="bast-section-card">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="bast-field">
                                    <label for="jenis_asset" class="form-label">Jenis Asset</label>
                                    <input type="text" id="jenis_asset" name="jenis_asset" class="form-control readonly-field" value="{{ old('jenis_asset', $category) }}" readonly>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="bast-field">
                                    <label for="no_asset" class="form-label">Nomor Asset</label>
                                    <input
                                        type="text"
                                        id="no_asset"
                                        name="no_asset"
                                        value="{{ old('no_asset') }}"
                                        class="form-control @error('no_asset') is-invalid @enderror"
                                        placeholder="Contoh: 30243000462"
                                        autocomplete="off"
                                    >
                                    @error('no_asset')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="bast-field">
                                    <label for="serial_number" class="form-label">Serial Number</label>
                                    <input
                                        type="text"
                                        id="serial_number"
                                        name="serial_number"
                                        value="{{ old('serial_number') }}"
                                        class="form-control @error('serial_number') is-invalid @enderror"
                                        placeholder="Masukkan serial number"
                                        autocomplete="off"
                                    >
                                    @error('serial_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="bast-field">
                                    <label for="tanggal_ambil" class="form-label">Tanggal Pengambilan <span class="required-mark">*</span></label>
                                    <input
                                        type="date"
                                        id="tanggal_ambil"
                                        name="tanggal_ambil"
                                        value="{{ old('tanggal_ambil', date('Y-m-d')) }}"
                                        class="form-control @error('tanggal_ambil') is-invalid @enderror"
                                        required
                                    >
                                    @error('tanggal_ambil')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="col-md-7">
                                <div class="bast-field">
                                    <label class="form-label" for="file_pdf">Dokumen BAST (PDF)</label>
                                    <div class="upload-box @error('file_pdf') border-danger @enderror">
                                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#ef5b2a" stroke-width="2">
                                            <path d="M12 16V4M7 9l5-5 5 5"/><path d="M20 15v5H4v-5"/>
                                        </svg>
                                        <strong id="fileNameLabel">Klik atau tarik file PDF ke area ini</strong>
                                        <small>Maksimal ukuran file mengikuti validasi pada server.</small>
                                        <input type="file" id="file_pdf" name="file_pdf" accept="application/pdf,.pdf">
                                    </div>
                                    @error('file_pdf')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-modal-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-modal-save" id="saveBastButton">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/>
                        </svg>
                        Simpan Berita Acara
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('bastSearch');
        const dataRows = Array.from(document.querySelectorAll('.bast-data-row'));
        const emptySearchRow = document.getElementById('emptySearchRow');
        const printButton = document.getElementById('printBast');
        const fileInput = document.getElementById('file_pdf');
        const fileNameLabel = document.getElementById('fileNameLabel');
        const form = document.getElementById('bastForm');
        const saveButton = document.getElementById('saveBastButton');
        const categoryModalElement = document.getElementById('bastCategoryModal');
        const formModalElement = document.getElementById('bastFormModal');
        const categoryButtons = Array.from(document.querySelectorAll('[data-bast-category]'));
        const jenisAssetInput = document.getElementById('jenis_asset');
        const formModalTitle = document.getElementById('bastFormModalLabel');
        const formCategoryNote = document.getElementById('bastFormCategoryNote');

        if (
            categoryModalElement &&
            formModalElement &&
            categoryButtons.length &&
            typeof bootstrap !== 'undefined'
        ) {
            const categoryModal = bootstrap.Modal.getOrCreateInstance(categoryModalElement);
            const formModal = bootstrap.Modal.getOrCreateInstance(formModalElement);

            categoryButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    const selectedCategory = this.dataset.bastCategory;

                    if (jenisAssetInput) {
                        jenisAssetInput.value = selectedCategory;
                    }

                    if (formModalTitle) {
                        formModalTitle.textContent = 'Tambah BAST ' + selectedCategory;
                    }

                    if (formCategoryNote) {
                        formCategoryNote.textContent = 'Kategori terpilih: BAST ' + selectedCategory + '. Lengkapi data penerima, asset, dan dokumen PDF.';
                    }

                    categoryModalElement.addEventListener('hidden.bs.modal', function openFormAfterCategory() {
                        formModal.show();
                    }, { once: true });

                    categoryModal.hide();
                });
            });
        }

        if (searchInput && dataRows.length) {
            searchInput.addEventListener('input', function () {
                const keyword = this.value.trim().toLowerCase();
                let visibleRows = 0;

                dataRows.forEach(function (row) {
                    const matches = row.textContent.toLowerCase().includes(keyword);
                    row.style.display = matches ? '' : 'none';
                    if (matches) visibleRows++;
                });

                if (emptySearchRow) {
                    emptySearchRow.style.display = visibleRows === 0 ? 'table-row' : 'none';
                }
            });
        }

        if (printButton) {
            printButton.addEventListener('click', function () {
                window.print();
            });
        }

        if (fileInput && fileNameLabel) {
            fileInput.addEventListener('change', function () {
                fileNameLabel.textContent = this.files && this.files.length
                    ? this.files[0].name
                    : 'Klik atau tarik file PDF ke area ini';
            });
        }

        if (form && saveButton) {
            form.addEventListener('submit', function () {
                saveButton.disabled = true;
                saveButton.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Menyimpan...';
            });
        }

        @if($errors->any())
            const bastModalElement = document.getElementById('bastFormModal');
            const oldCategory = @json(old('jenis_asset', $category));

            if (jenisAssetInput && oldCategory) {
                jenisAssetInput.value = oldCategory;
            }

            if (formModalTitle && oldCategory) {
                formModalTitle.textContent = 'Tambah BAST ' + oldCategory;
            }

            if (formCategoryNote && oldCategory) {
                formCategoryNote.textContent = 'Kategori terpilih: BAST ' + oldCategory + '. Periksa kembali data yang belum valid.';
            }

            if (bastModalElement && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(bastModalElement).show();
            }
        @endif
    });
</script>
@endsection