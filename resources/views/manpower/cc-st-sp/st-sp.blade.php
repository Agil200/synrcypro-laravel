@php
    $labelBulan = \Carbon\Carbon::createFromFormat('Y-m', $bulan)
        ->locale('id')
        ->translatedFormat('F Y');

    $indexRoute = $kategori === 'teguran'
        ? 'cc-st-sp.teguran.index'
        : 'cc-st-sp.peringatan.index';
@endphp

@include('manpower.cc-st-sp.partials.styles')

<div class="ccsp-page">
    <section class="ccsp-card">
        <div class="ccsp-header">
            <div>
                <h1 class="ccsp-title">{{ $pageTitle }}</h1>
                <p class="ccsp-subtitle">
                    Monitoring pelanggaran, masa berlaku 180 hari,
                    status, dan dokumen PDF.
                </p>
            </div>

            <button
                type="button"
                class="ccsp-primary"
                id="openCreateStSp"
            >
                ＋ Input ST &amp; SP
            </button>
        </div>

        @if (session('success'))
            <div class="ccsp-alert ccsp-success">
                {{ session('success') }}
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
            <form
                method="GET"
                action="{{ route($indexRoute) }}"
                class="ccsp-field"
            >
                <label for="stspBulan">Pilih Bulan</label>
                <input
                    type="month"
                    name="bulan"
                    id="stspBulan"
                    class="ccsp-input"
                    value="{{ $bulan }}"
                    onchange="this.form.submit()"
                >
            </form>

            <form
                method="GET"
                action="{{ route($indexRoute) }}"
                class="ccsp-field"
            >
                <input type="hidden" name="bulan" value="{{ $bulan }}">

                <label for="stspSearch">
                    Cari NRP / Pelanggaran / Atasan
                </label>

                <input
                    type="search"
                    name="search"
                    id="stspSearch"
                    class="ccsp-input"
                    value="{{ $search }}"
                    placeholder="Ketik kata pencarian lalu Enter"
                >
            </form>

            <div class="ccsp-stat">
                <span>Data {{ $labelBulan }}</span>
                <strong>
                    {{ number_format($statistik['bulanDipilih']) }}
                </strong>
            </div>

            <div class="ccsp-stat">
                <span>Status Aktif</span>
                <strong>
                    {{ number_format($statistik['aktif']) }}
                </strong>
            </div>
        </div>

        <div class="ccsp-table-wrap">
            <table class="ccsp-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NRP</th>
                        <th>Jenis Pelanggaran</th>
                        <th>Tanggal</th>
                        <th>Expired Date</th>
                        <th>Tempat Kejadian</th>
                        <th>Jenis</th>
                        <th>Deskripsi</th>
                        <th>Atasan</th>
                        <th>File PDF</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($records as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->nrp }}</td>
                            <td>{{ $item->jenis_pelanggaran }}</td>
                            <td>
                                {{ $item->tanggal?->format('d/m/Y') }}
                            </td>
                            <td>
                                {{
                                    $item->expired_date?->format(
                                        'd/m/Y'
                                    )
                                }}
                            </td>
                            <td>
                                {{ $item->tempat_kejadian ?: '-' }}
                            </td>
                            <td>{{ $item->jenis }}</td>
                            <td>{{ $item->deskripsi ?: '-' }}</td>
                            <td>{{ $item->atasan ?: '-' }}</td>
                            <td>
                                <a
                                    href="{{
                                        route(
                                            'cc-st-sp.st-sp.file',
                                            $item
                                        )
                                    }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="ccsp-file"
                                >
                                    <span>📄</span>
                                    <span>{{ $item->file_nama_asli }}</span>
                                </a>
                            </td>
                            <td>
                                <span
                                    class="ccsp-badge
                                        {{
                                            $item->status === 'AKTIF'
                                                ? 'active'
                                                : 'expired'
                                        }}"
                                >
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td>
                                <div class="ccsp-actions">
                                    <button
                                        type="button"
                                        class="ccsp-action js-edit-stsp"
                                        data-id="{{ $item->id }}"
                                        data-nrp="{{ $item->nrp }}"
                                        data-pelanggaran="{{
                                            $item->jenis_pelanggaran
                                        }}"
                                        data-tanggal="{{
                                            $item->tanggal?->format(
                                                'Y-m-d'
                                            )
                                        }}"
                                        data-tempat="{{
                                            $item->tempat_kejadian
                                        }}"
                                        data-jenis="{{ $item->jenis }}"
                                        data-deskripsi="{{
                                            $item->deskripsi
                                        }}"
                                        data-atasan="{{ $item->atasan }}"
                                        data-status="{{ $item->status }}"
                                    >
                                        Edit
                                    </button>

                                    <form
                                        method="POST"
                                        action="{{
                                            route(
                                                'cc-st-sp.st-sp.destroy',
                                                $item
                                            )
                                        }}"
                                        class="js-delete-stsp"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="
                                                ccsp-action
                                                ccsp-danger
                                            "
                                        >
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="ccsp-empty">
                                Belum ada data
                                {{ strtolower($pageTitle) }}
                                pada {{ $labelBulan }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ccsp-footer">
            <span>
                Menampilkan {{ $records->firstItem() ?? 0 }}
                sampai {{ $records->lastItem() ?? 0 }}
                dari {{ $records->total() }} data
            </span>

            @if ($records->hasPages())
                <nav class="ccsp-pagination">
                    <a
                        href="{{ $records->previousPageUrl() ?: '#' }}"
                        class="ccsp-page-link
                            {{
                                $records->onFirstPage()
                                    ? 'ccsp-disabled'
                                    : ''
                            }}"
                    >
                        ‹
                    </a>

                    <span>
                        Halaman {{ $records->currentPage() }}
                        dari {{ $records->lastPage() }}
                    </span>

                    <a
                        href="{{ $records->nextPageUrl() ?: '#' }}"
                        class="ccsp-page-link
                            {{
                                $records->hasMorePages()
                                    ? ''
                                    : 'ccsp-disabled'
                            }}"
                    >
                        ›
                    </a>
                </nav>
            @endif
        </div>
    </section>
</div>

{{-- Form tambah --}}
<div class="ccsp-modal" id="createStSpModal" aria-hidden="true">
    <div
        class="ccsp-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="createStSpTitle"
    >
        <form
            method="POST"
            action="{{ route('cc-st-sp.st-sp.store') }}"
            enctype="multipart/form-data"
        >
            @csrf

            <header class="ccsp-reference-toolbar">
                <button
                    type="button"
                    class="ccsp-reference-close js-close-stsp"
                    aria-label="Tutup"
                >
                    ×
                </button>

                <h2
                    class="ccsp-reference-title"
                    id="createStSpTitle"
                >
                    ST &amp; SP Form
                </h2>

                <div class="ccsp-reference-actions">
                    <button
                        type="button"
                        class="ccsp-form-cancel js-close-stsp"
                    >
                        Cancel
                    </button>

                    <button type="submit" class="ccsp-form-save">
                        Save
                    </button>
                </div>
            </header>

            <div class="ccsp-page-tabbar">
                <div class="ccsp-page-tab">Page 1</div>
            </div>

            <div class="ccsp-reference-body">
                <div class="ccsp-reference-row">
                    <label class="ccsp-reference-label">
                        NO
                    </label>

                    <div class="ccsp-reference-control">
                        <input
                            type="text"
                            class="ccsp-reference-input"
                            value="{{ $nextNumber }}"
                            disabled
                        >
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="stspNrp"
                        class="ccsp-reference-label"
                    >
                        NRP*
                    </label>

                    <div class="ccsp-reference-control">
                        <input
                            type="text"
                            name="nrp"
                            id="stspNrp"
                            class="ccsp-reference-input"
                            value="{{ old('nrp') }}"
                            maxlength="50"
                            required
                        >
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="stspPelanggaran"
                        class="ccsp-reference-label"
                    >
                        JENIS PELANGGARAN
                    </label>

                    <div class="ccsp-reference-control">
                        <select
                            name="jenis_pelanggaran"
                            id="stspPelanggaran"
                            class="ccsp-reference-select"
                            required
                        >
                            <option value="">
                                Pilih jenis pelanggaran
                            </option>
                            @foreach (
                                [
                                    'KEHADIRAN',
                                    'KEDISIPLINAN',
                                    'KESELAMATAN KERJA',
                                    'PELANGGARAN SOP',
                                    'ETIKA KERJA',
                                    'KINERJA',
                                    'LAINNYA',
                                ] as $pelanggaran
                            )
                                <option
                                    value="{{ $pelanggaran }}"
                                    @selected(
                                        old('jenis_pelanggaran')
                                            === $pelanggaran
                                    )
                                >
                                    {{ $pelanggaran }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="stspTanggal"
                        class="ccsp-reference-label"
                    >
                        TANGGAL
                    </label>

                    <div class="ccsp-reference-control">
                        <input
                            type="date"
                            name="tanggal"
                            id="stspTanggal"
                            class="
                                ccsp-reference-input
                                js-stsp-date
                            "
                            value="{{
                                old(
                                    'tanggal',
                                    now()->format('Y-m-d')
                                )
                            }}"
                            required
                        >
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="stspExpired"
                        class="ccsp-reference-label"
                    >
                        EXPIRED_DATE
                    </label>

                    <div class="ccsp-reference-control">
                        <input
                            type="date"
                            id="stspExpired"
                            class="
                                ccsp-reference-input
                                js-stsp-expired
                            "
                            disabled
                        >
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="stspTempat"
                        class="ccsp-reference-label"
                    >
                        TEMPAT KEJADIAN
                    </label>

                    <div class="ccsp-reference-control">
                        <input
                            type="text"
                            name="tempat_kejadian"
                            id="stspTempat"
                            class="ccsp-reference-input"
                            value="{{ old('tempat_kejadian') }}"
                            maxlength="255"
                        >
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="stspJenis"
                        class="ccsp-reference-label"
                    >
                        JENIS
                    </label>

                    <div class="ccsp-reference-control">
                        <select
                            name="jenis"
                            id="stspJenis"
                            class="ccsp-reference-select"
                            required
                        >
                            <option value="">Add or search</option>

                            @foreach ($allJenisList as $jenis)
                                <option
                                    value="{{ $jenis }}"
                                    @selected(
                                        old('jenis') === $jenis
                                    )
                                >
                                    {{ $jenis }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="stspDeskripsi"
                        class="ccsp-reference-label"
                    >
                        DESKRIPSI
                    </label>

                    <div class="ccsp-reference-control">
                        <input
                            type="text"
                            name="deskripsi"
                            id="stspDeskripsi"
                            class="ccsp-reference-input"
                            value="{{ old('deskripsi') }}"
                            maxlength="2000"
                        >
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="stspAtasan"
                        class="ccsp-reference-label"
                    >
                        ATASAN
                    </label>

                    <div class="ccsp-reference-control">
                        <input
                            type="text"
                            name="atasan"
                            id="stspAtasan"
                            class="ccsp-reference-input"
                            value="{{ old('atasan') }}"
                            maxlength="150"
                        >
                    </div>
                </div>

                <div class="ccsp-reference-row is-top">
                    <label
                        for="stspFile"
                        class="ccsp-reference-label"
                    >
                        LINK
                    </label>

                    <div class="ccsp-reference-control">
                        <input
                            type="file"
                            name="file_dokumen"
                            id="stspFile"
                            class="
                                ccsp-reference-upload-input
                                js-file-input
                            "
                            accept="application/pdf,.pdf"
                            required
                        >

                        <label
                            for="stspFile"
                            class="ccsp-reference-upload"
                        >
                            <span class="ccsp-reference-upload-icon">
                                📄
                            </span>
                            <span
                                class="
                                    ccsp-reference-upload-name
                                    js-file-name
                                "
                            >
                                Pilih file PDF
                            </span>
                        </label>
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label class="ccsp-reference-label">
                        STATUS
                    </label>

                    <div class="ccsp-reference-control">
                        <input
                            type="text"
                            class="ccsp-reference-input"
                            value="AKTIF"
                            disabled
                        >
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Form edit --}}
<div class="ccsp-modal" id="editStSpModal" aria-hidden="true">
    <div
        class="ccsp-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="editStSpTitle"
    >
        <form
            method="POST"
            action=""
            enctype="multipart/form-data"
            id="editStSpForm"
        >
            @csrf
            @method('PUT')

            <header class="ccsp-reference-toolbar">
                <button
                    type="button"
                    class="
                        ccsp-reference-close
                        js-close-stsp
                    "
                    aria-label="Tutup"
                >
                    ×
                </button>

                <h2
                    class="ccsp-reference-title"
                    id="editStSpTitle"
                >
                    Edit ST &amp; SP Form
                </h2>

                <div class="ccsp-reference-actions">
                    <button
                        type="button"
                        class="
                            ccsp-form-cancel
                            js-close-stsp
                        "
                    >
                        Cancel
                    </button>

                    <button type="submit" class="ccsp-form-save">
                        Save
                    </button>
                </div>
            </header>

            <div class="ccsp-page-tabbar">
                <div class="ccsp-page-tab">Page 1</div>
            </div>

            <div class="ccsp-reference-body">
                <div class="ccsp-reference-row">
                    <label class="ccsp-reference-label">
                        NO
                    </label>

                    <div class="ccsp-reference-control">
                        <input
                            type="text"
                            id="editStspNo"
                            class="ccsp-reference-input"
                            disabled
                        >
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="editStspNrp"
                        class="ccsp-reference-label"
                    >
                        NRP*
                    </label>
                    <div class="ccsp-reference-control">
                        <input
                            type="text"
                            name="nrp"
                            id="editStspNrp"
                            class="ccsp-reference-input"
                            maxlength="50"
                            required
                        >
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="editStspPelanggaran"
                        class="ccsp-reference-label"
                    >
                        JENIS PELANGGARAN
                    </label>
                    <div class="ccsp-reference-control">
                        <select
                            name="jenis_pelanggaran"
                            id="editStspPelanggaran"
                            class="ccsp-reference-select"
                            required
                        >
                            @foreach (
                                [
                                    'KEHADIRAN',
                                    'KEDISIPLINAN',
                                    'KESELAMATAN KERJA',
                                    'PELANGGARAN SOP',
                                    'ETIKA KERJA',
                                    'KINERJA',
                                    'LAINNYA',
                                ] as $pelanggaran
                            )
                                <option value="{{ $pelanggaran }}">
                                    {{ $pelanggaran }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="editStspTanggal"
                        class="ccsp-reference-label"
                    >
                        TANGGAL
                    </label>
                    <div class="ccsp-reference-control">
                        <input
                            type="date"
                            name="tanggal"
                            id="editStspTanggal"
                            class="
                                ccsp-reference-input
                                js-stsp-date
                            "
                            required
                        >
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="editStspExpired"
                        class="ccsp-reference-label"
                    >
                        EXPIRED_DATE
                    </label>
                    <div class="ccsp-reference-control">
                        <input
                            type="date"
                            id="editStspExpired"
                            class="
                                ccsp-reference-input
                                js-stsp-expired
                            "
                            disabled
                        >
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="editStspTempat"
                        class="ccsp-reference-label"
                    >
                        TEMPAT KEJADIAN
                    </label>
                    <div class="ccsp-reference-control">
                        <input
                            type="text"
                            name="tempat_kejadian"
                            id="editStspTempat"
                            class="ccsp-reference-input"
                            maxlength="255"
                        >
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="editStspJenis"
                        class="ccsp-reference-label"
                    >
                        JENIS
                    </label>
                    <div class="ccsp-reference-control">
                        <select
                            name="jenis"
                            id="editStspJenis"
                            class="ccsp-reference-select"
                            required
                        >
                            @foreach ($allJenisList as $jenis)
                                <option value="{{ $jenis }}">
                                    {{ $jenis }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="editStspDeskripsi"
                        class="ccsp-reference-label"
                    >
                        DESKRIPSI
                    </label>
                    <div class="ccsp-reference-control">
                        <input
                            type="text"
                            name="deskripsi"
                            id="editStspDeskripsi"
                            class="ccsp-reference-input"
                            maxlength="2000"
                        >
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="editStspAtasan"
                        class="ccsp-reference-label"
                    >
                        ATASAN
                    </label>
                    <div class="ccsp-reference-control">
                        <input
                            type="text"
                            name="atasan"
                            id="editStspAtasan"
                            class="ccsp-reference-input"
                            maxlength="150"
                        >
                    </div>
                </div>

                <div class="ccsp-reference-row is-top">
                    <label
                        for="editStspFile"
                        class="ccsp-reference-label"
                    >
                        LINK
                    </label>
                    <div class="ccsp-reference-control">
                        <input
                            type="file"
                            name="file_dokumen"
                            id="editStspFile"
                            class="
                                ccsp-reference-upload-input
                                js-file-input
                            "
                            accept="application/pdf,.pdf"
                        >

                        <label
                            for="editStspFile"
                            class="ccsp-reference-upload"
                        >
                            <span class="ccsp-reference-upload-icon">
                                📄
                            </span>
                            <span
                                class="
                                    ccsp-reference-upload-name
                                    js-file-name
                                "
                            >
                                Kosongkan jika tetap memakai PDF lama
                            </span>
                        </label>
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label class="ccsp-reference-label">
                        STATUS
                    </label>
                    <div class="ccsp-reference-control">
                        <input
                            type="text"
                            id="editStspStatus"
                            class="ccsp-reference-input"
                            disabled
                        >
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const createModal =
        document.getElementById('createStSpModal');

    const editModal =
        document.getElementById('editStSpModal');

    const updateUrl = @json(
        route(
            'cc-st-sp.st-sp.update',
            ['stSpRecord' => '__ID__']
        )
    );

    function openModal(modal) {
        if (!modal) {
            return;
        }

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

        if (!document.querySelector('.ccsp-modal.is-open')) {
            document.body.classList.remove('ccsp-modal-open');
        }
    }

    function updateExpired(dateInput) {
        const body = dateInput?.closest(
            '.ccsp-reference-body'
        );

        const expiredInput = body?.querySelector(
            '.js-stsp-expired'
        );

        if (!expiredInput || !dateInput.value) {
            return;
        }

        const date = new Date(
            dateInput.value + 'T00:00:00'
        );

        date.setDate(date.getDate() + 180);

        const year = date.getFullYear();
        const month = String(
            date.getMonth() + 1
        ).padStart(2, '0');
        const day = String(
            date.getDate()
        ).padStart(2, '0');

        expiredInput.value = `${year}-${month}-${day}`;
    }

    document
        .querySelectorAll('.js-stsp-date')
        .forEach(function (input) {
            input.addEventListener(
                'change',
                function () {
                    updateExpired(input);
                }
            );

            updateExpired(input);
        });

    document
        .getElementById('openCreateStSp')
        ?.addEventListener('click', function () {
            updateExpired(
                document.getElementById('stspTanggal')
            );

            openModal(createModal);
        });

    document
        .querySelectorAll('.js-close-stsp')
        .forEach(function (button) {
            button.addEventListener('click', function () {
                closeModal(
                    button.closest('.ccsp-modal')
                );
            });
        });

    document
        .querySelectorAll('.ccsp-modal')
        .forEach(function (modal) {
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal(modal);
                }
            });
        });

    document
        .querySelectorAll('.js-edit-stsp')
        .forEach(function (button) {
            button.addEventListener('click', function () {
                const form =
                    document.getElementById('editStSpForm');

                form.action = updateUrl.replace(
                    '__ID__',
                    encodeURIComponent(button.dataset.id)
                );

                document.getElementById('editStspNo').value =
                    button.dataset.id || '';

                document.getElementById('editStspNrp').value =
                    button.dataset.nrp || '';

                document.getElementById(
                    'editStspPelanggaran'
                ).value = button.dataset.pelanggaran || '';

                document.getElementById(
                    'editStspTanggal'
                ).value = button.dataset.tanggal || '';

                document.getElementById(
                    'editStspTempat'
                ).value = button.dataset.tempat || '';

                document.getElementById(
                    'editStspJenis'
                ).value = button.dataset.jenis || '';

                document.getElementById(
                    'editStspDeskripsi'
                ).value = button.dataset.deskripsi || '';

                document.getElementById(
                    'editStspAtasan'
                ).value = button.dataset.atasan || '';

                document.getElementById(
                    'editStspStatus'
                ).value = button.dataset.status || 'AKTIF';

                updateExpired(
                    document.getElementById(
                        'editStspTanggal'
                    )
                );

                openModal(editModal);
            });
        });

    document
        .querySelectorAll('.js-file-input')
        .forEach(function (input) {
            input.addEventListener('change', function () {
                const target = input
                    .closest('.ccsp-reference-control')
                    ?.querySelector('.js-file-name');

                if (target) {
                    target.textContent =
                        input.files?.[0]?.name
                        || 'Pilih file PDF';
                }
            });
        });

    document
        .querySelectorAll('.js-delete-stsp')
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (
                    !window.confirm(
                        'Hapus data ST/SP beserta file PDF?'
                    )
                ) {
                    event.preventDefault();
                }
            });
        });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }

        document
            .querySelectorAll('.ccsp-modal.is-open')
            .forEach(closeModal);
    });

    @if ($errors->any())
        openModal(createModal);
    @endif
});
</script>
