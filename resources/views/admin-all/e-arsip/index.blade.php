@extends('admin-all.layout')

@section('admin-content')
<style>
    .aa-main {
        overflow: hidden !important;
    }

    .aa-content {
        height: 100%;
        min-height: 0;
    }

    .ea-page {
        display: flex;
        width: 100%;
        height: 100%;
        min-height: 0;
        flex-direction: column;
        gap: 8px;
        overflow: hidden;
    }

    .ea-title {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        flex: 0 0 auto;
    }

    .ea-title h1 {
        margin: 0;
        color: #071f3d;
        font-size: clamp(20px,2vw,27px);
    }

    .ea-title p {
        margin: 3px 0 0;
        color: #647488;
        font-size: 8px;
    }

    .ea-title-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        justify-content: flex-end;
    }

    .ea-btn {
        display: inline-flex;
        min-height: 30px;
        align-items: center;
        justify-content: center;
        padding: 6px 10px;
        border: 1px solid #ccd7e1;
        border-radius: 7px;
        color: #17304e;
        background: #fff;
        font-size: 7px;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
    }

    button.ea-btn {
        cursor: pointer;
    }

    .ea-btn.primary {
        border-color: #0f78ef;
        color: #fff;
        background: #0f78ef;
    }

    .ea-btn.warn {
        border-color: #d68b1e;
        color: #87540c;
        background: #fff7e7;
    }

    .ea-btn.danger {
        border-color: #e4a4aa;
        color: #ad1f2c;
        background: #fff1f2;
    }

    .ea-btn.trash{border-color:#d8c4ef;color:#68439a;background:#f8f3ff}
    .ea-btn.disabled{pointer-events:none;opacity:.55;cursor:not-allowed}

    .ea-alert {
        flex: 0 0 auto;
        padding: 8px 10px;
        border: 1px solid #afe1c5;
        border-radius: 8px;
        color: #16613c;
        background: #effcf5;
        font-size: 8px;
        font-weight: 700;
    }

    .ea-filter {
        display: grid;
        grid-template-columns: minmax(250px,1fr) 190px 150px auto auto;
        gap: 7px;
        flex: 0 0 auto;
        padding: 9px;
        border: 1px solid #d7e0e7;
        border-radius: 9px;
        background: #fff;
    }

    .ea-field label {
        display: block;
        margin-bottom: 4px;
        color: #5c6f82;
        font-size: 6px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .ea-field input,
    .ea-field select {
        width: 100%;
        height: 31px;
        padding: 0 9px;
        border: 1px solid #cbd7e1;
        border-radius: 7px;
        color: #17304e;
        background: #fff;
        font-size: 8px;
        outline: none;
    }

    .ea-filter-button {
        align-self: end;
    }

    .ea-stats {
        display: grid;
        grid-template-columns: repeat(4,minmax(0,1fr));
        gap: 7px;
        flex: 0 0 auto;
    }

    .ea-stat {
        min-height: 65px;
        padding: 9px 10px;
        border: 1px solid #dce3e9;
        border-radius: 9px;
        background: #fff;
    }

    .ea-stat small {
        display: block;
        color: #69798b;
        font-size: 6px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .ea-stat strong {
        display: block;
        margin-top: 4px;
        color: #0b2a4c;
        font-size: 22px;
        line-height: 1;
    }

    .ea-stat span {
        display: block;
        margin-top: 4px;
        color: #7b8998;
        font-size: 6px;
    }

    .ea-table-card {
        display: flex;
        min-height: 0;
        flex: 1 1 auto;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid #d5dfe7;
        border-radius: 9px;
        background: #fff;
    }

    .ea-table-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        flex: 0 0 auto;
        padding: 8px 10px;
        border-bottom: 1px solid #e0e6eb;
    }

    .ea-table-head strong {
        color: #122e4d;
        font-size: 9px;
    }

    .ea-table-head small {
        color: #748294;
        font-size: 7px;
    }

    .ea-table-wrap {
        min-height: 0;
        flex: 1 1 auto;
        overflow: auto;
    }

    .ea-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        font-size: 7px;
    }

    .ea-table thead {
        position: sticky;
        top: 0;
        z-index: 3;
    }

    .ea-table th {
        padding: 8px 8px;
        color: #fff;
        background: #173f68;
        font-size: 6px;
        font-weight: 900;
        text-align: left;
        text-transform: uppercase;
    }

    .ea-table td {
        padding: 8px;
        border-bottom: 1px solid #edf1f4;
        color: #40556d;
        vertical-align: middle;
    }

    .ea-table tbody tr:hover {
        background: #f8fbfd;
    }

    .ea-name-cell {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 9px;
    }

    .ea-file-icon {
        display: grid;
        width: 31px;
        height: 31px;
        flex: 0 0 31px;
        place-items: center;
        border: 1px solid #d9e3ea;
        border-radius: 8px;
        background: #f7fafc;
    }

    .ea-file-icon svg {
        width: 18px;
        height: 18px;
        fill: none;
        stroke: currentColor;
        stroke-linecap: round;
        stroke-linejoin: round;
        stroke-width: 1.8;
    }

    .ea-file-icon.folder {
        color: #d48a12;
        background: #fff8e8;
        border-color: #f0d6a9;
    }

    .ea-file-icon.spreadsheet {
        color: #168957;
        background: #effbf5;
        border-color: #bde4cf;
    }

    .ea-file-icon.docs {
        color: #2d6fc2;
        background: #eef6ff;
        border-color: #c4dcf8;
    }

    .ea-file-icon.form {
        color: #7449af;
        background: #f6f0ff;
        border-color: #d9c7ee;
    }

    .ea-file-icon.slides {
        color: #cf7d14;
        background: #fff6e8;
        border-color: #efd3a6;
    }

    .ea-file-icon.drive {
        color: #53677b;
        background: #f3f6f8;
        border-color: #d8e0e7;
    }

    .ea-name-text {
        min-width: 0;
        flex: 1;
    }

    .ea-type-label {
        display: inline-flex;
        min-height: 16px;
        align-items: center;
        margin-top: 3px;
        padding: 1px 5px;
        border-radius: 999px;
        color: #66788a;
        background: #eef2f5;
        font-size: 5px;
        font-weight: 900;
        letter-spacing: .03em;
    }

    .ea-category-badge {
        display: inline-flex;
        min-height: 20px;
        align-items: center;
        padding: 2px 7px;
        border: 1px solid #d5e1ec;
        border-radius: 999px;
        color: #315b82;
        background: #f1f7fc;
        font-size: 6px;
        font-weight: 900;
        white-space: nowrap;
    }

    .ea-table-controls {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .ea-per-page-form {
        display: flex;
        align-items: center;
        gap: 5px;
        margin: 0;
        color: #647588;
        font-size: 7px;
        font-weight: 800;
    }

    .ea-per-page-form select {
        height: 27px;
        padding: 0 22px 0 8px;
        border: 1px solid #cad6df;
        border-radius: 6px;
        color: #16314f;
        background: #fff;
        font-size: 7px;
        font-weight: 900;
        outline: none;
        cursor: pointer;
    }

    .ea-name strong {
        display: block;
        color: #112c49;
        font-size: 8px;
    }

    .ea-name small,
    .ea-url {
        display: block;
        margin-top: 2px;
        overflow: hidden;
        color: #7a8897;
        font-size: 6px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ea-badge {
        display: inline-flex;
        min-height: 20px;
        align-items: center;
        padding: 2px 7px;
        border-radius: 999px;
        color: #11633a;
        background: #ddf5e7;
        font-size: 6px;
        font-weight: 900;
    }

    .ea-badge.off {
        color: #7a4b06;
        background: #fff0d6;
    }

    .ea-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        align-items: center;
    }

    .ea-actions form {
        margin: 0;
    }

    .ea-empty {
        padding: 28px 12px !important;
        color: #768697 !important;
        text-align: center;
    }

    .ea-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        flex: 0 0 auto;
        padding: 7px 10px;
        border-top: 1px solid #e4e9ed;
        font-size: 7px;
    }

    .ea-page-links {
        display: flex;
        gap: 5px;
    }

    .ea-page-link {
        display: inline-flex;
        min-width: 28px;
        height: 26px;
        align-items: center;
        justify-content: center;
        padding: 0 7px;
        border: 1px solid #ccd7e1;
        border-radius: 6px;
        color: #17304e;
        background: #fff;
        font-size: 7px;
        font-weight: 900;
        text-decoration: none;
    }

    .ea-page-link.disabled {
        pointer-events: none;
        opacity: .45;
    }

    @media(max-width:1100px) {
        .ea-filter {
            grid-template-columns: 1fr 1fr 1fr;
        }

        .ea-filter-button {
            align-self: stretch;
        }
    }

    @media(max-width:760px) {
        .ea-title {
            flex-direction: column;
        }

        .ea-filter,
        .ea-stats {
            grid-template-columns: 1fr;
        }

        .ea-table {
            min-width: 900px;
        }
    }
</style>

<div class="ea-page">
    <div class="ea-title">
        <div>
            <h1>E-Arsip</h1>
            <p>
                Registry link Google Drive Departemen Produksi. File fisik tetap berada di Google Drive.
            </p>
        </div>

        @php
            $trashRouteReady =
                \Illuminate\Support\Facades\Route::has('admin-all.e-arsip.trash');
        @endphp

        <div class="ea-title-actions">
            <a
                href="{{ $trashRouteReady ? route('admin-all.e-arsip.trash') : '#' }}"
                class="ea-btn trash {{ $trashRouteReady ? '' : 'disabled' }}"
                title="{{ $trashRouteReady ? 'Buka Sampah E-Arsip' : 'Frontend siap — backend Trash/Restore belum diaktifkan' }}"
            >
                ♲ SAMPAH
            </a>

            <a
                href="{{ route('admin-all.e-arsip.create') }}"
                class="ea-btn primary"
            >
                + TAMBAH ARSIP
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="ea-alert">
            {{ session('success') }}
        </div>
    @endif

    <form
        method="GET"
        action="{{ route('admin-all.e-arsip.index') }}"
        class="ea-filter"
    >
        <div class="ea-field">
            <label>Search</label>
            <input
                type="text"
                name="q"
                value="{{ $search }}"
                placeholder="Nama arsip, kategori, deskripsi..."
            >
        </div>

        <div class="ea-field">
            <label>Kategori</label>
            <select name="category">
                <option value="">Semua Kategori</option>

                @foreach($categories as $item)
                    <option
                        value="{{ $item }}"
                        {{ $category === $item ? 'selected' : '' }}
                    >
                        {{ $item }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="ea-field">
            <label>Status</label>
            <select name="status">
                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>
                    Semua Status
                </option>
                <option value="active" {{ $status === 'active' ? 'selected' : '' }}>
                    Aktif
                </option>
                <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>
                    Nonaktif
                </option>
            </select>
        </div>

        <input
            type="hidden"
            name="per_page"
            value="{{ $perPage }}"
        >

        <button
            type="submit"
            class="ea-btn primary ea-filter-button"
        >
            SEARCH
        </button>

        <a
            href="{{ route('admin-all.e-arsip.index') }}"
            class="ea-btn ea-filter-button"
        >
            RESET
        </a>
    </form>

    <div class="ea-stats">
        <div class="ea-stat">
            <small>Total Arsip</small>
            <strong>{{ number_format($stats['total']) }}</strong>
            <span>Registry tersimpan</span>
        </div>

        <div class="ea-stat">
            <small>Aktif</small>
            <strong>{{ number_format($stats['active']) }}</strong>
            <span>Siap digunakan</span>
        </div>

        <div class="ea-stat">
            <small>Nonaktif</small>
            <strong>{{ number_format($stats['inactive']) }}</strong>
            <span>Disimpan tetapi tidak aktif</span>
        </div>

        <div class="ea-stat">
            <small>Kategori</small>
            <strong>{{ number_format($stats['categories']) }}</strong>
            <span>Kategori registry</span>
        </div>
    </div>

    <section class="ea-table-card">
        <div class="ea-table-head">
            <strong>DAFTAR LINK GOOGLE DRIVE</strong>

            <div class="ea-table-controls">
                <form
                    method="GET"
                    action="{{ route('admin-all.e-arsip.index') }}"
                    class="ea-per-page-form"
                >
                    <input type="hidden" name="q" value="{{ $search }}">
                    <input type="hidden" name="category" value="{{ $category }}">
                    <input type="hidden" name="status" value="{{ $status }}">

                    <span>Show</span>

                    <select
                        name="per_page"
                        onchange="this.form.submit()"
                    >
                        @foreach([20, 50, 100] as $rowOption)
                            <option
                                value="{{ $rowOption }}"
                                {{ $perPage === $rowOption ? 'selected' : '' }}
                            >
                                {{ $rowOption }}
                            </option>
                        @endforeach
                    </select>

                    <span>rows</span>
                </form>

                <small>
                    {{ number_format($archives->total()) }} data
                </small>
            </div>
        </div>

        <div class="ea-table-wrap">
            <table class="ea-table">
                <thead>
                    <tr>
                        <th style="width:25%">Nama Arsip</th>
                        <th style="width:14%">Kategori</th>
                        <th style="width:25%">Deskripsi</th>
                        <th style="width:8%">Urutan</th>
                        <th style="width:9%">Status</th>
                        <th style="width:19%">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($archives as $archive)
                        <tr>
                            <td class="ea-name">
                                <div class="ea-name-cell">
                                    @php
                                        $linkType = $archive->link_type;
                                        $linkTypeClass = strtolower($linkType);
                                    @endphp

                                    <span class="ea-file-icon {{ $linkTypeClass }}">
                                        @switch($linkType)
                                            @case('SPREADSHEET')
                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M6 2h8l4 4v16H6z"/>
                                                    <path d="M14 2v5h5"/>
                                                    <path d="M9 11h6M9 15h6M12 10v7"/>
                                                </svg>
                                                @break

                                            @case('DOCS')
                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M6 2h8l4 4v16H6z"/>
                                                    <path d="M14 2v5h5"/>
                                                    <path d="M9 11h6M9 15h6M9 18h4"/>
                                                </svg>
                                                @break

                                            @case('FORM')
                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M6 2h8l4 4v16H6z"/>
                                                    <path d="M14 2v5h5"/>
                                                    <circle cx="9" cy="12" r="1"/>
                                                    <path d="M12 12h4"/>
                                                    <circle cx="9" cy="16" r="1"/>
                                                    <path d="M12 16h4"/>
                                                </svg>
                                                @break

                                            @case('SLIDES')
                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M6 2h8l4 4v16H6z"/>
                                                    <path d="M14 2v5h5"/>
                                                    <rect x="9" y="11" width="6" height="5" rx="1"/>
                                                </svg>
                                                @break

                                            @case('FOLDER')
                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/>
                                                </svg>
                                                @break

                                            @default
                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M7 18a4.6 4.6 0 0 1 .7-9.1A6 6 0 0 1 19 11a3.5 3.5 0 0 1-.5 7H7Z"/>
                                                </svg>
                                        @endswitch
                                    </span>

                                    <span class="ea-name-text">
                                        <strong>{{ $archive->name }}</strong>

                                        <span class="ea-type-label">
                                            {{ $linkType }}
                                        </span>

                                        <small class="ea-url">
                                            {{ $archive->drive_url }}
                                        </small>
                                    </span>
                                </div>
                            </td>

                            <td>
                                <span class="ea-category-badge">
                                    {{ $archive->category }}
                                </span>
                            </td>

                            <td>
                                {{ $archive->description ?: '-' }}
                            </td>

                            <td>
                                {{ $archive->sort_order }}
                            </td>

                            <td>
                                <span class="ea-badge {{ $archive->is_active ? '' : 'off' }}">
                                    {{ $archive->is_active ? 'AKTIF' : 'NONAKTIF' }}
                                </span>
                            </td>

                            <td>
                                <div class="ea-actions">
                                    <a
                                        href="{{ $archive->drive_url }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="ea-btn primary"
                                    >
                                        BUKA ↗
                                    </a>

                                    <a
                                        href="{{ route('admin-all.e-arsip.edit', $archive) }}"
                                        class="ea-btn"
                                    >
                                        EDIT
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin-all.e-arsip.toggle', $archive) }}"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="ea-btn warn"
                                        >
                                            {{ $archive->is_active ? 'NONAKTIFKAN' : 'AKTIFKAN' }}
                                        </button>
                                    </form>

                                    <form
                                        method="POST"
                                        action="{{ route('admin-all.e-arsip.destroy', $archive) }}"
                                        onsubmit="return confirm('Hapus {{ addslashes($archive->name) }} dari registry E-Arsip? Folder/file Google Drive TIDAK akan dihapus.');"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="ea-btn danger"
                                        >
                                            DELETE
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="6"
                                class="ea-empty"
                            >
                                Belum ada E-Arsip sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ea-pagination">
            <span>
                Showing
                {{ $archives->firstItem() ?? 0 }}
                –
                {{ $archives->lastItem() ?? 0 }}
                of
                {{ $archives->total() }}
                • {{ $perPage }} rows/page
            </span>

            <div class="ea-page-links">
                <a
                    href="{{ $archives->previousPageUrl() ?: '#' }}"
                    class="ea-page-link {{ $archives->onFirstPage() ? 'disabled' : '' }}"
                >
                    ← PREV
                </a>

                <span class="ea-page-link">
                    {{ $archives->currentPage() }}
                    /
                    {{ max(1, $archives->lastPage()) }}
                </span>

                <a
                    href="{{ $archives->nextPageUrl() ?: '#' }}"
                    class="ea-page-link {{ $archives->hasMorePages() ? '' : 'disabled' }}"
                >
                    NEXT →
                </a>
            </div>
        </div>
    </section>
</div>
@endsection
