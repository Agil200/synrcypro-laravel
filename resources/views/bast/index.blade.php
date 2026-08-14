@php
    $meta = is_array($catalogMeta ?? null) ? $catalogMeta : [];
    $connected = (bool) ($meta['connected'] ?? false);
    $typeIcons = [
        'FOLDER' => '▰',
        'DOKUMEN' => '▤',
        'SPREADSHEET' => '▦',
        'PRESENTASI' => '▧',
        'PDF' => 'PDF',
        'FILE' => '◆',
    ];
@endphp

<style>
    .drive-files-page {
        display: grid;
        gap: 16px;
        color: #13213a;
    }

    .drive-files-page * {
        box-sizing: border-box;
    }

    .drive-files-hero,
    .drive-files-toolbar,
    .drive-files-empty,
    .drive-file-card {
        border: 1px solid #d9e1eb;
        background: #ffffff;
        border-radius: 14px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    }

    .drive-files-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 20px;
    }

    .drive-files-title h1 {
        margin: 0;
        font-size: 24px;
        line-height: 1.15;
    }

    .drive-files-title p {
        margin: 7px 0 0;
        color: #64748b;
        font-size: 13px;
    }

    .drive-files-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .drive-files-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 38px;
        padding: 0 14px;
        border: 1px solid #cfd8e5;
        border-radius: 9px;
        background: #ffffff;
        color: #13213a;
        font-size: 11px;
        font-weight: 800;
        text-decoration: none;
        text-transform: uppercase;
    }

    .drive-files-button.is-primary {
        border-color: #2563eb;
        background: #2563eb;
        color: #ffffff;
    }

    .drive-files-status {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 11px 14px;
        border: 1px solid #bbf7d0;
        border-radius: 10px;
        background: #ecfdf5;
        color: #166534;
        font-size: 12px;
    }

    .drive-files-status.is-error {
        border-color: #fecaca;
        background: #fef2f2;
        color: #991b1b;
    }

    .drive-files-toolbar {
        padding: 14px;
    }

    .drive-files-form {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(180px, 240px) auto;
        gap: 10px;
    }

    .drive-files-input,
    .drive-files-select {
        width: 100%;
        min-height: 40px;
        border: 1px solid #cfd8e5;
        border-radius: 9px;
        background: #ffffff;
        color: #13213a;
        padding: 0 12px;
        font-size: 13px;
        outline: none;
    }

    .drive-files-input:focus,
    .drive-files-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10);
    }

    .drive-files-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .drive-file-card {
        display: grid;
        grid-template-columns: 48px minmax(0, 1fr);
        gap: 12px;
        padding: 15px;
        min-height: 156px;
    }

    .drive-file-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: #eff6ff;
        color: #2563eb;
        font-size: 17px;
        font-weight: 900;
    }

    .drive-file-copy {
        min-width: 0;
    }

    .drive-file-copy h2 {
        margin: 1px 0 5px;
        color: #0f172a;
        font-size: 14px;
        line-height: 1.3;
        overflow-wrap: anywhere;
    }

    .drive-file-copy p {
        display: -webkit-box;
        margin: 0;
        overflow: hidden;
        color: #64748b;
        font-size: 11px;
        line-height: 1.45;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    .drive-file-meta {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        margin-top: 10px;
    }

    .drive-file-pill {
        padding: 4px 7px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #475569;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .drive-file-footer {
        grid-column: 1 / -1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: auto;
        padding-top: 10px;
        border-top: 1px solid #edf1f6;
    }

    .drive-file-date {
        color: #64748b;
        font-size: 10px;
    }

    .drive-file-open {
        color: #2563eb;
        font-size: 10px;
        font-weight: 900;
        text-decoration: none;
        text-transform: uppercase;
    }

    .drive-files-empty {
        padding: 32px 20px;
        text-align: center;
    }

    .drive-files-empty h2 {
        margin: 0 0 8px;
        font-size: 17px;
    }

    .drive-files-empty p {
        max-width: 620px;
        margin: 0 auto;
        color: #64748b;
        font-size: 12px;
        line-height: 1.6;
    }

    .drive-files-pagination {
        display: flex;
        justify-content: flex-end;
    }

    @media (max-width: 980px) {
        .drive-files-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 720px) {
        .drive-files-hero {
            align-items: stretch;
            flex-direction: column;
        }

        .drive-files-form,
        .drive-files-grid {
            grid-template-columns: 1fr;
        }

        .drive-files-status {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>

<section class="drive-files-page" aria-labelledby="driveFilesTitle">
    <header class="drive-files-hero">
        <div class="drive-files-title">
            <h1 id="driveFilesTitle">Pusat File</h1>
            <p>
                Kumpulan file Google Drive yang dikelola langsung melalui Spreadsheet admin.
            </p>
        </div>

        <div class="drive-files-actions">
            <a href="{{ route('database.dashboard') }}" class="drive-files-button">
                Kembali
            </a>

            @if (!empty($meta['source_url']))
                <a
                    href="{{ $meta['source_url'] }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="drive-files-button"
                >
                    Kelola Spreadsheet
                </a>
            @endif

            <a href="{{ route('database.files') }}" class="drive-files-button is-primary">
                Refresh Data
            </a>
        </div>
    </header>

    <div class="drive-files-status {{ $connected ? '' : 'is-error' }}">
        <strong>{{ $meta['message'] ?? 'Status sumber data belum tersedia.' }}</strong>
        <span>
            {{ number_format((int) ($meta['total'] ?? 0)) }} file aktif
            · {{ $meta['range'] ?? 'A:Z' }}
            @if (!empty($meta['synced_at']))
                · {{ $meta['synced_at'] }}
            @endif
        </span>
    </div>

    <div class="drive-files-toolbar">
        <form method="GET" action="{{ route('database.files') }}" class="drive-files-form">
            <input
                type="search"
                name="search"
                value="{{ $search ?? '' }}"
                class="drive-files-input"
                placeholder="Cari judul, kategori, deskripsi, atau tipe file..."
            >

            <select name="category" class="drive-files-select">
                <option value="ALL">Semua kategori</option>
                @foreach ($categories ?? [] as $categoryOption)
                    <option
                        value="{{ $categoryOption }}"
                        {{ strcasecmp((string) ($selectedCategory ?? 'ALL'), $categoryOption) === 0
                            ? 'selected'
                            : '' }}
                    >
                        {{ $categoryOption }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="drive-files-button is-primary">
                Terapkan
            </button>
        </form>
    </div>

    @if (($files ?? collect())->count() > 0)
        <div class="drive-files-grid">
            @foreach ($files as $file)
                <article class="drive-file-card">
                    <div class="drive-file-icon" aria-hidden="true">
                        {{ $typeIcons[$file['type']] ?? '◆' }}
                    </div>

                    <div class="drive-file-copy">
                        <h2>{{ $file['title'] }}</h2>
                        <p>{{ $file['description'] ?: 'Tidak ada deskripsi.' }}</p>

                        <div class="drive-file-meta">
                            <span class="drive-file-pill">{{ $file['category'] }}</span>
                            <span class="drive-file-pill">{{ $file['type'] }}</span>
                            <span class="drive-file-pill">{{ $file['access'] }}</span>
                        </div>
                    </div>

                    <div class="drive-file-footer">
                        <span class="drive-file-date">
                            {{ $file['date'] ?: 'Tanggal belum diisi' }}
                        </span>

                        <a
                            href="{{ $file['url'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="drive-file-open"
                        >
                            Buka File ↗
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        @if ($files->hasPages())
            <div class="drive-files-pagination">
                {{ $files->links() }}
            </div>
        @endif
    @else
        <div class="drive-files-empty">
            <h2>Belum ada file yang dapat ditampilkan</h2>
            <p>
                Tambahkan link Google Drive pada Spreadsheet. Header yang dikenali antara lain:
                JUDUL, KATEGORI, DESKRIPSI, TIPE, LINK DRIVE, TANGGAL, AKSES, STATUS, dan URUTAN.
                Gunakan STATUS AKTIF agar file ditampilkan.
            </p>
        </div>
    @endif
</section>