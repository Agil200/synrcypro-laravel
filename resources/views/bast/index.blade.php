@php
    $categoryOptions = [
        'Senter P101X',
        'Laser',
        'Laptop',
        'Radio HT',
        'Lainnya',
    ];

    $selectedCategory = trim((string) ($category ?? 'Senter P101X'));

    if ($selectedCategory === '') {
        $selectedCategory = 'Senter P101X';
    }

    $isPaginator = is_object($assets)
        && method_exists($assets, 'total')
        && method_exists($assets, 'firstItem');

    $totalAssets = $isPaginator
        ? $assets->total()
        : collect($assets)->count();

    $firstItem = $isPaginator
        ? ($assets->firstItem() ?? 0)
        : ($totalAssets > 0 ? 1 : 0);

    $lastItem = $isPaginator
        ? ($assets->lastItem() ?? 0)
        : $totalAssets;
@endphp

@include('manpower.cc-st-sp.partials.styles')

<style>
    .bast-category-select {
        cursor: pointer;
    }

    .bast-name {
        min-width: 170px;
        font-weight: 700;
    }

    .bast-code {
        white-space: nowrap;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas,
            "Liberation Mono", monospace;
    }

    .bast-readonly {
        color: #677184;
        background: #f5f7fa;
    }

    .bast-search-empty {
        display: none;
    }

    .bast-note {
        margin-top: 7px;
        color: #798294;
        font-size: 11px;
        line-height: 1.45;
    }

    @media print {
        #openCreateBast,
        .ccsp-toolbar,
        .ccsp-footer {
            display: none !important;
        }
    }
</style>

<div class="ccsp-page">
    <section class="ccsp-card">
        <div class="ccsp-header">
            <div>
                <h1 class="ccsp-title">Berita Acara Serah Terima Asset</h1>
                <p class="ccsp-subtitle">
                    Monitoring penyerahan asset berdasarkan NRP, nama penerima,
                    jenis asset, dan dokumen PDF.
                </p>
            </div>

            <button
                type="button"
                class="ccsp-primary"
                id="openCreateBast"
            >
                ＋ Input BAST
            </button>
        </div>

        @if (session('success'))
            <div class="ccsp-alert ccsp-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="ccsp-alert ccsp-error">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="ccsp-alert ccsp-error">
                Data belum dapat disimpan.
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="ccsp-toolbar">
            <div class="ccsp-field">
                <label for="bastCategoryFilter">Kategori Asset</label>
                <select
                    id="bastCategoryFilter"
                    class="ccsp-input bast-category-select"
                >
                    @foreach ($categoryOptions as $option)
                        <option
                            value="{{ $option }}"
                            @selected($selectedCategory === $option)
                        >
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="ccsp-field">
                <label for="bastSearch">
                    Cari NRP / Nama / Jabatan / Asset
                </label>
                <input
                    type="search"
                    id="bastSearch"
                    class="ccsp-input"
                    placeholder="Ketik kata pencarian"
                    autocomplete="off"
                >
            </div>

            <div class="ccsp-stat">
                <span>Total Berita Acara</span>
                <strong>{{ number_format($totalAssets) }}</strong>
            </div>

            <div class="ccsp-stat">
                <span>Kategori Aktif</span>
                <strong>{{ $selectedCategory }}</strong>
            </div>
        </div>

        <div class="ccsp-table-wrap">
            <table class="ccsp-table" id="bastTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NRP</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>Jenis Asset</th>
                        <th>Nomor Asset</th>
                        <th>Serial Number</th>
                        <th>Tanggal Ambil</th>
                        <th>File PDF</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($assets as $item)
                        <tr class="js-bast-row">
                            <td>
                                {{
                                    $isPaginator
                                        ? (($assets->firstItem() ?? 1) + $loop->index)
                                        : ($loop->index + 1)
                                }}
                            </td>
                            <td>{{ $item->nrp ?: '-' }}</td>
                            <td class="bast-name">{{ $item->nama ?: '-' }}</td>
                            <td>{{ $item->jabatan ?: '-' }}</td>
                            <td>{{ $item->jenis_asset ?: $selectedCategory }}</td>
                            <td class="bast-code">{{ $item->no_asset ?: '-' }}</td>
                            <td class="bast-code">
                                {{ $item->serial_number ?: '-' }}
                            </td>
                            <td>
                                {{
                                    $item->tanggal_ambil
                                        ? \Carbon\Carbon::parse(
                                            $item->tanggal_ambil
                                        )->format('d/m/Y')
                                        : '-'
                                }}
                            </td>
                            <td>
                                @if ($item->file_pdf)
                                    <a
                                        href="{{ asset('storage/'.$item->file_pdf) }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="ccsp-file"
                                    >
                                        <span>📄</span>
                                        <span>Lihat PDF</span>
                                    </a>
                                @else
                                    <span>-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr id="initialBastEmpty">
                            <td colspan="9" class="ccsp-empty">
                                Belum ada data BAST untuk kategori
                                {{ $selectedCategory }}.
                            </td>
                        </tr>
                    @endforelse

                    <tr id="bastSearchEmpty" class="bast-search-empty">
                        <td colspan="9" class="ccsp-empty">
                            Data BAST tidak ditemukan.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="ccsp-footer">
            <span>
                Menampilkan {{ $firstItem }} sampai {{ $lastItem }}
                dari {{ $totalAssets }} data
            </span>

            @if ($isPaginator && $assets->hasPages())
                <nav class="ccsp-pagination">
                    <a
                        href="{{ $assets->previousPageUrl() ?: '#' }}"
                        class="ccsp-page-link
                            {{ $assets->onFirstPage() ? 'ccsp-disabled' : '' }}"
                    >
                        ‹
                    </a>

                    <span>
                        Halaman {{ $assets->currentPage() }}
                        dari {{ $assets->lastPage() }}
                    </span>

                    <a
                        href="{{ $assets->nextPageUrl() ?: '#' }}"
                        class="ccsp-page-link
                            {{ $assets->hasMorePages() ? '' : 'ccsp-disabled' }}"
                    >
                        ›
                    </a>
                </nav>
            @endif
        </div>
    </section>
</div>

{{-- Form tambah BAST --}}
<div class="ccsp-modal" id="createBastModal" aria-hidden="true" hidden>
    <div
        class="ccsp-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="createBastTitle"
    >
        <form
            method="POST"
            action="{{ route('bast.store') }}"
            enctype="multipart/form-data"
            id="createBastForm"
        >
            @csrf

            <header class="ccsp-reference-toolbar">
                <button
                    type="button"
                    class="ccsp-reference-close js-close-bast"
                    aria-label="Tutup"
                >
                    ×
                </button>

                <h2
                    class="ccsp-reference-title"
                    id="createBastTitle"
                >
                    BAST ASSET Form
                </h2>

                <div class="ccsp-reference-actions">
                    <button
                        type="button"
                        class="ccsp-form-cancel js-close-bast"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="ccsp-form-save"
                        id="saveBastButton"
                    >
                        Save
                    </button>
                </div>
            </header>

            <div class="ccsp-page-tabbar">
                <div class="ccsp-page-tab">Page 1</div>
            </div>

            <div class="ccsp-reference-body">
                <div class="ccsp-reference-row">
                    <label
                        for="bastNrp"
                        class="ccsp-reference-label"
                    >
                        NRP*
                    </label>

                    <div class="ccsp-reference-control">
                        <input
                            type="text"
                            name="nrp"
                            id="bastNrp"
                            class="ccsp-reference-input"
                            value="{{ old('nrp') }}"
                            maxlength="50"
                            autocomplete="off"
                            required
                        >
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="bastNama"
                        class="ccsp-reference-label"
                    >
                        NAMA*
                    </label>

                    <div class="ccsp-reference-control">
                        <input
                            type="text"
                            name="nama"
                            id="bastNama"
                            class="ccsp-reference-input"
                            value="{{ old('nama') }}"
                            maxlength="150"
                            autocomplete="name"
                            required
                        >
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="bastJabatan"
                        class="ccsp-reference-label"
                    >
                        JABATAN*
                    </label>

                    <div class="ccsp-reference-control">
                        <select
                            name="jabatan"
                            id="bastJabatan"
                            class="ccsp-reference-select"
                            required
                        >
                            <option value="">PILIH JABATAN</option>
                            @foreach (['DUMPMAN', 'GL', 'SH', 'DH'] as $jabatan)
                                <option
                                    value="{{ $jabatan }}"
                                    @selected(old('jabatan') === $jabatan)
                                >
                                    {{ $jabatan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="bastDepartemen"
                        class="ccsp-reference-label"
                    >
                        DEPARTEMEN
                    </label>

                    <div class="ccsp-reference-control">
                        <input
                            type="text"
                            name="departemen"
                            id="bastDepartemen"
                            class="ccsp-reference-input bast-readonly"
                            value="{{ old('departemen', 'PRODUKSI') }}"
                            readonly
                        >
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="bastJenisAsset"
                        class="ccsp-reference-label"
                    >
                        JENIS ASSET*
                    </label>

                    <div class="ccsp-reference-control">
                        <select
                            name="jenis_asset"
                            id="bastJenisAsset"
                            class="ccsp-reference-select"
                            required
                        >
                            @foreach ($categoryOptions as $option)
                                <option
                                    value="{{ $option }}"
                                    @selected(
                                        old(
                                            'jenis_asset',
                                            $selectedCategory
                                        ) === $option
                                    )
                                >
                                    {{ $option }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="bastNoAsset"
                        class="ccsp-reference-label"
                    >
                        NOMOR ASSET
                    </label>

                    <div class="ccsp-reference-control">
                        <input
                            type="text"
                            name="no_asset"
                            id="bastNoAsset"
                            class="ccsp-reference-input"
                            value="{{ old('no_asset') }}"
                            maxlength="255"
                            autocomplete="off"
                        >
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="bastSerialNumber"
                        class="ccsp-reference-label"
                    >
                        SERIAL NUMBER
                    </label>

                    <div class="ccsp-reference-control">
                        <input
                            type="text"
                            name="serial_number"
                            id="bastSerialNumber"
                            class="ccsp-reference-input"
                            value="{{ old('serial_number') }}"
                            maxlength="255"
                            autocomplete="off"
                        >
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="bastTanggalAmbil"
                        class="ccsp-reference-label"
                    >
                        TANGGAL AMBIL*
                    </label>

                    <div class="ccsp-reference-control">
                        <input
                            type="date"
                            name="tanggal_ambil"
                            id="bastTanggalAmbil"
                            class="ccsp-reference-input"
                            value="{{ old('tanggal_ambil', now()->format('Y-m-d')) }}"
                            required
                        >
                    </div>
                </div>

                <div class="ccsp-reference-row is-top">
                    <label
                        for="bastFile"
                        class="ccsp-reference-label"
                    >
                        LINK
                    </label>

                    <div class="ccsp-reference-control">
                        <input
                            type="file"
                            name="file_pdf"
                            id="bastFile"
                            class="ccsp-reference-upload-input js-bast-file"
                            accept="application/pdf,.pdf"
                        >

                        <label
                            for="bastFile"
                            class="ccsp-reference-upload"
                        >
                            <span class="ccsp-reference-upload-icon">
                                📄
                            </span>
                            <span
                                class="ccsp-reference-upload-name"
                                id="bastFileName"
                            >
                                Pilih file PDF
                            </span>
                        </label>

                        <div class="bast-note">
                            Gunakan dokumen BAST dalam format PDF.
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const createModal = document.getElementById('createBastModal');
    const openButton = document.getElementById('openCreateBast');
    const searchInput = document.getElementById('bastSearch');
    const categoryFilter = document.getElementById('bastCategoryFilter');
    const categoryInput = document.getElementById('bastJenisAsset');
    const searchEmpty = document.getElementById('bastSearchEmpty');
    const initialEmpty = document.getElementById('initialBastEmpty');
    const fileInput = document.getElementById('bastFile');
    const fileName = document.getElementById('bastFileName');
    const createForm = document.getElementById('createBastForm');
    const saveButton = document.getElementById('saveBastButton');

    const categoryUrl = @json(
        route('bast.index', ['category' => '__CATEGORY__'])
    );

    function openModal(modal) {
        if (!modal) {
            return;
        }

        modal.hidden = false;
        modal.removeAttribute('hidden');
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('ccsp-modal-open');
    }

    function closeModal(modal) {
        if (!modal) {
            return;
        }

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        modal.hidden = true;
        modal.setAttribute('hidden', '');

        if (!document.querySelector('.ccsp-modal.is-open')) {
            document.body.classList.remove('ccsp-modal-open');
        }
    }

    openButton?.addEventListener('click', function () {
        if (categoryInput && categoryFilter) {
            categoryInput.value = categoryFilter.value;
        }

        openModal(createModal);
    });

    document
        .querySelectorAll('.js-close-bast')
        .forEach(function (button) {
            button.addEventListener('click', function () {
                closeModal(button.closest('.ccsp-modal'));
            });
        });

    createModal?.addEventListener('click', function (event) {
        if (event.target === createModal) {
            closeModal(createModal);
        }
    });

    categoryFilter?.addEventListener('change', function () {
        window.location.href = categoryUrl.replace(
            '__CATEGORY__',
            encodeURIComponent(this.value)
        );
    });

    searchInput?.addEventListener('input', function () {
        const keyword = this.value.trim().toLowerCase();
        const rows = Array.from(
            document.querySelectorAll('.js-bast-row')
        );
        let visibleRows = 0;

        rows.forEach(function (row) {
            const isVisible = row.textContent
                .toLowerCase()
                .includes(keyword);

            row.style.display = isVisible ? '' : 'none';

            if (isVisible) {
                visibleRows += 1;
            }
        });

        if (searchEmpty) {
            searchEmpty.style.display =
                rows.length > 0 && visibleRows === 0
                    ? 'table-row'
                    : 'none';
        }

        if (initialEmpty) {
            initialEmpty.style.display = keyword === '' ? '' : 'none';
        }
    });

    fileInput?.addEventListener('change', function () {
        if (!fileName) {
            return;
        }

        fileName.textContent =
            this.files?.[0]?.name || 'Pilih file PDF';
    });

    createForm?.addEventListener('submit', function () {
        if (!saveButton) {
            return;
        }

        saveButton.disabled = true;
        saveButton.textContent = 'Menyimpan...';
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeModal(createModal);
        }
    });

    @if ($errors->any())
        openModal(createModal);
    @endif
});
</script>
