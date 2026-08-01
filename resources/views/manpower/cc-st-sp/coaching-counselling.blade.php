@php
    $labelBulan = \Carbon\Carbon::createFromFormat('Y-m', $bulan)
        ->locale('id')
        ->translatedFormat('F Y');
@endphp

@include('manpower.cc-st-sp.partials.styles')

<div class="ccsp-page">
    <section class="ccsp-card">
        <div class="ccsp-header">
            <div>
                <h1 class="ccsp-title">Coaching &amp; Counselling</h1>
                <p class="ccsp-subtitle">
                    Monitoring kegiatan coaching dan counselling berdasarkan NRP.
                </p>
            </div>

            <button type="button" class="ccsp-primary" id="openCreateCc">
                ＋ Input Coaching &amp; Counselling
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
                action="{{ route('cc-st-sp.coaching.index') }}"
                class="ccsp-field"
            >
                <label for="ccBulan">Pilih Bulan</label>
                <input
                    type="month"
                    name="bulan"
                    id="ccBulan"
                    class="ccsp-input"
                    value="{{ $bulan }}"
                    onchange="this.form.submit()"
                >
            </form>

            <form
                method="GET"
                action="{{ route('cc-st-sp.coaching.index') }}"
                class="ccsp-field"
            >
                <input type="hidden" name="bulan" value="{{ $bulan }}">

                <label for="ccSearch">
                    Cari NRP / Materi / Pembuat
                </label>

                <input
                    type="search"
                    name="search"
                    id="ccSearch"
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
                <span>Total Seluruh Data</span>
                <strong>{{ number_format($statistik['total']) }}</strong>
            </div>
        </div>

        <div class="ccsp-table-wrap">
            <table class="ccsp-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NRP</th>
                        <th>Materi</th>
                        <th>Perihal</th>
                        <th>Tanggal</th>
                        <th>Shift</th>
                        <th>Keterangan</th>
                        <th>Dibuat Oleh</th>
                        <th>File PDF</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($records as $item)
                        <tr>
                            <td>
                                {{
                                    ($records->firstItem() ?? 1)
                                    + $loop->index
                                }}
                            </td>
                            <td>{{ $item->nrp }}</td>
                            <td>{{ $item->materi }}</td>
                            <td>{{ $item->perihal ?: '-' }}</td>
                            <td>{{ $item->tanggal?->format('d/m/Y') }}</td>
                            <td>{{ $item->shift }}</td>
                            <td>{{ $item->keterangan ?: '-' }}</td>
                            <td>{{ $item->dibuat_oleh ?: '-' }}</td>
                            <td>
                                <a
                                    href="{{
                                        route(
                                            'cc-st-sp.coaching.file',
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
                                <div class="ccsp-actions">
                                    <button
                                        type="button"
                                        class="ccsp-action js-edit-cc"
                                        data-id="{{ $item->id }}"
                                        data-nrp="{{ $item->nrp }}"
                                        data-materi="{{ $item->materi }}"
                                        data-perihal="{{ $item->perihal }}"
                                        data-tanggal="{{
                                            $item->tanggal?->format('Y-m-d')
                                        }}"
                                        data-shift="{{ $item->shift }}"
                                        data-keterangan="{{
                                            $item->keterangan
                                        }}"
                                        data-dibuat-oleh="{{
                                            $item->dibuat_oleh
                                        }}"
                                    >
                                        Edit
                                    </button>

                                    <form
                                        method="POST"
                                        action="{{
                                            route(
                                                'cc-st-sp.coaching.destroy',
                                                $item
                                            )
                                        }}"
                                        class="js-delete-cc"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="ccsp-action ccsp-danger"
                                        >
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="ccsp-empty">
                                Belum ada data pada {{ $labelBulan }}.
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

<datalist id="materiCcList">
    <option value="Kedisiplinan">
    <option value="Kinerja">
    <option value="Keselamatan Kerja">
    <option value="Kehadiran">
    <option value="Etika Kerja">
</datalist>

<datalist id="perihalCcList">
    <option value="Pembinaan">
    <option value="Evaluasi">
    <option value="Klarifikasi">
    <option value="Tindak Lanjut">
</datalist>

{{-- Form tambah --}}
<div class="ccsp-modal" id="createCcModal" aria-hidden="true" hidden>
    <div
        class="ccsp-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="createCcTitle"
    >
        <form
            method="POST"
            action="{{ route('cc-st-sp.coaching.store') }}"
            enctype="multipart/form-data"
        >
            @csrf

            <header class="ccsp-reference-toolbar">
                <button
                    type="button"
                    class="ccsp-reference-close js-close-cc"
                    aria-label="Tutup"
                >
                    ×
                </button>

                <h2
                    class="ccsp-reference-title"
                    id="createCcTitle"
                >
                    COACHING &amp; COUNSELLING Form
                </h2>

                <div class="ccsp-reference-actions">
                    <button
                        type="button"
                        class="ccsp-form-cancel js-close-cc"
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
                    <label
                        for="ccNrp"
                        class="ccsp-reference-label"
                    >
                        NRP*
                    </label>

                    <div class="ccsp-reference-control">
                        <input
                            type="text"
                            name="nrp"
                            id="ccNrp"
                            class="ccsp-reference-input"
                            value="{{ old('nrp') }}"
                            maxlength="50"
                            required
                        >
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="ccMateri"
                        class="ccsp-reference-label"
                    >
                        MATERI*
                    </label>

                    <div class="ccsp-reference-control">
                        <div class="ccsp-reference-input-group">
                            <input
                                type="text"
                                name="materi"
                                id="ccMateri"
                                class="ccsp-reference-input"
                                list="materiCcList"
                                value="{{ old('materi') }}"
                                maxlength="255"
                                required
                            >
                            <span class="ccsp-reference-addon">＋</span>
                        </div>
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="ccPerihal"
                        class="ccsp-reference-label"
                    >
                        PERIHAL
                    </label>

                    <div class="ccsp-reference-control">
                        <div class="ccsp-reference-input-group">
                            <input
                                type="text"
                                name="perihal"
                                id="ccPerihal"
                                class="ccsp-reference-input"
                                list="perihalCcList"
                                value="{{ old('perihal') }}"
                                maxlength="255"
                            >
                            <span class="ccsp-reference-addon">＋</span>
                        </div>
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="ccTanggal"
                        class="ccsp-reference-label"
                    >
                        TANGGAL*
                    </label>

                    <div class="ccsp-reference-control">
                        <input
                            type="date"
                            name="tanggal"
                            id="ccTanggal"
                            class="ccsp-reference-input"
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
                        for="ccShift"
                        class="ccsp-reference-label"
                    >
                        SHIFT*
                    </label>

                    <div class="ccsp-reference-control">
                        <div class="ccsp-reference-input-group">
                            <select
                                name="shift"
                                id="ccShift"
                                class="ccsp-reference-select"
                                required
                            >
                                <option value="">Pilih shift</option>
                                @foreach (
                                    [
                                        'DAY',
                                        'NIGHT',
                                        'SHIFT 1',
                                        'SHIFT 2',
                                    ] as $shift
                                )
                                    <option
                                        value="{{ $shift }}"
                                        @selected(
                                            old('shift') === $shift
                                        )
                                    >
                                        {{ $shift }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="ccsp-reference-addon">＋</span>
                        </div>
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="ccKeterangan"
                        class="ccsp-reference-label"
                    >
                        KETERANGAN
                    </label>

                    <div class="ccsp-reference-control">
                        <input
                            type="text"
                            name="keterangan"
                            id="ccKeterangan"
                            class="ccsp-reference-input"
                            value="{{ old('keterangan') }}"
                            maxlength="1000"
                        >
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="ccDibuatOleh"
                        class="ccsp-reference-label"
                    >
                        DIBUAT OLEH
                    </label>

                    <div class="ccsp-reference-control">
                        <input
                            type="text"
                            name="dibuat_oleh"
                            id="ccDibuatOleh"
                            class="ccsp-reference-input"
                            value="{{
                                old(
                                    'dibuat_oleh',
                                    auth()->user()?->name
                                )
                            }}"
                            maxlength="150"
                        >
                    </div>
                </div>

                <div class="ccsp-reference-row is-top">
                    <label
                        for="ccFile"
                        class="ccsp-reference-label"
                    >
                        LINK*
                    </label>

                    <div class="ccsp-reference-control">
                        <input
                            type="file"
                            name="file_dokumen"
                            id="ccFile"
                            class="ccsp-reference-upload-input js-file-input"
                            accept="application/pdf,.pdf"
                            required
                        >

                        <label
                            for="ccFile"
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
            </div>
        </form>
    </div>
</div>

{{-- Form edit --}}
<div class="ccsp-modal" id="editCcModal" aria-hidden="true" hidden>
    <div
        class="ccsp-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="editCcTitle"
    >
        <form
            method="POST"
            action=""
            enctype="multipart/form-data"
            id="editCcForm"
        >
            @csrf
            @method('PUT')

            <header class="ccsp-reference-toolbar">
                <button
                    type="button"
                    class="ccsp-reference-close js-close-cc"
                    aria-label="Tutup"
                >
                    ×
                </button>

                <h2
                    class="ccsp-reference-title"
                    id="editCcTitle"
                >
                    Edit COACHING &amp; COUNSELLING Form
                </h2>

                <div class="ccsp-reference-actions">
                    <button
                        type="button"
                        class="ccsp-form-cancel js-close-cc"
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
                    <label
                        for="editCcNrp"
                        class="ccsp-reference-label"
                    >
                        NRP*
                    </label>
                    <div class="ccsp-reference-control">
                        <input
                            type="text"
                            name="nrp"
                            id="editCcNrp"
                            class="ccsp-reference-input"
                            maxlength="50"
                            required
                        >
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="editCcMateri"
                        class="ccsp-reference-label"
                    >
                        MATERI*
                    </label>
                    <div class="ccsp-reference-control">
                        <div class="ccsp-reference-input-group">
                            <input
                                type="text"
                                name="materi"
                                id="editCcMateri"
                                class="ccsp-reference-input"
                                list="materiCcList"
                                maxlength="255"
                                required
                            >
                            <span class="ccsp-reference-addon">＋</span>
                        </div>
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="editCcPerihal"
                        class="ccsp-reference-label"
                    >
                        PERIHAL
                    </label>
                    <div class="ccsp-reference-control">
                        <div class="ccsp-reference-input-group">
                            <input
                                type="text"
                                name="perihal"
                                id="editCcPerihal"
                                class="ccsp-reference-input"
                                list="perihalCcList"
                                maxlength="255"
                            >
                            <span class="ccsp-reference-addon">＋</span>
                        </div>
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="editCcTanggal"
                        class="ccsp-reference-label"
                    >
                        TANGGAL*
                    </label>
                    <div class="ccsp-reference-control">
                        <input
                            type="date"
                            name="tanggal"
                            id="editCcTanggal"
                            class="ccsp-reference-input"
                            required
                        >
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="editCcShift"
                        class="ccsp-reference-label"
                    >
                        SHIFT*
                    </label>
                    <div class="ccsp-reference-control">
                        <div class="ccsp-reference-input-group">
                            <select
                                name="shift"
                                id="editCcShift"
                                class="ccsp-reference-select"
                                required
                            >
                                @foreach (
                                    [
                                        'DAY',
                                        'NIGHT',
                                        'SHIFT 1',
                                        'SHIFT 2',
                                    ] as $shift
                                )
                                    <option value="{{ $shift }}">
                                        {{ $shift }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="ccsp-reference-addon">＋</span>
                        </div>
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="editCcKeterangan"
                        class="ccsp-reference-label"
                    >
                        KETERANGAN
                    </label>
                    <div class="ccsp-reference-control">
                        <input
                            type="text"
                            name="keterangan"
                            id="editCcKeterangan"
                            class="ccsp-reference-input"
                            maxlength="1000"
                        >
                    </div>
                </div>

                <div class="ccsp-reference-row">
                    <label
                        for="editCcDibuatOleh"
                        class="ccsp-reference-label"
                    >
                        DIBUAT OLEH
                    </label>
                    <div class="ccsp-reference-control">
                        <input
                            type="text"
                            name="dibuat_oleh"
                            id="editCcDibuatOleh"
                            class="ccsp-reference-input"
                            maxlength="150"
                        >
                    </div>
                </div>

                <div class="ccsp-reference-row is-top">
                    <label
                        for="editCcFile"
                        class="ccsp-reference-label"
                    >
                        LINK
                    </label>
                    <div class="ccsp-reference-control">
                        <input
                            type="file"
                            name="file_dokumen"
                            id="editCcFile"
                            class="ccsp-reference-upload-input js-file-input"
                            accept="application/pdf,.pdf"
                        >

                        <label
                            for="editCcFile"
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
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const createModal = document.getElementById('createCcModal');
    const editModal = document.getElementById('editCcModal');

    const updateUrl = @json(
        route(
            'cc-st-sp.coaching.update',
            ['coachingCounselling' => '__ID__']
        )
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

    document
        .getElementById('openCreateCc')
        ?.addEventListener('click', function () {
            openModal(createModal);
        });

    document
        .querySelectorAll('.js-close-cc')
        .forEach(function (button) {
            button.addEventListener('click', function () {
                closeModal(button.closest('.ccsp-modal'));
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
        .querySelectorAll('.js-edit-cc')
        .forEach(function (button) {
            button.addEventListener('click', function () {
                const form = document.getElementById('editCcForm');

                form.action = updateUrl.replace(
                    '__ID__',
                    encodeURIComponent(button.dataset.id)
                );

                document.getElementById('editCcNrp').value =
                    button.dataset.nrp || '';

                document.getElementById('editCcMateri').value =
                    button.dataset.materi || '';

                document.getElementById('editCcPerihal').value =
                    button.dataset.perihal || '';

                document.getElementById('editCcTanggal').value =
                    button.dataset.tanggal || '';

                document.getElementById('editCcShift').value =
                    button.dataset.shift || '';

                document.getElementById('editCcKeterangan').value =
                    button.dataset.keterangan || '';

                document.getElementById('editCcDibuatOleh').value =
                    button.dataset.dibuatOleh || '';

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
        .querySelectorAll('.js-delete-cc')
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (
                    !window.confirm(
                        'Hapus data Coaching & Counselling beserta PDF?'
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
