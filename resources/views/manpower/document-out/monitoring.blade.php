@php
    try {
        $labelBulan = \Carbon\Carbon::createFromFormat(
            'Y-m',
            $bulan
        )->locale('id')->translatedFormat('F Y');
    } catch (\Throwable $exception) {
        $labelBulan = $bulan;
    }
@endphp

<style>
    .docout-page {
        --red: #d71920;
        --red-dark: #b31319;
        --border: #dce2e8;
        --text: #172033;
        --muted: #687386;
        display: grid;
        gap: 14px;
        min-width: 0;
    }

    .docout-card {
        overflow: hidden;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .06);
    }

    .docout-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 20px;
        border-bottom: 1px solid var(--border);
    }

    .docout-title {
        margin: 0 0 5px;
        color: var(--text);
        font-size: 21px;
        font-weight: 800;
    }

    .docout-subtitle {
        margin: 0;
        color: var(--muted);
        font-size: 13px;
    }

    .docout-button {
        display: inline-flex;
        min-height: 40px;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 0 15px;
        border: 0;
        border-radius: 8px;
        color: #fff;
        background: var(--red);
        font-family: inherit;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
    }

    .docout-button:hover {
        background: var(--red-dark);
    }

    .docout-toolbar {
        display: grid;
        grid-template-columns:
            minmax(240px, 390px)
            repeat(2, minmax(130px, 170px));
        gap: 12px;
        align-items: end;
        padding: 16px 20px;
    }

    .docout-field {
        display: grid;
        gap: 7px;
    }

    .docout-field label {
        color: #30394a;
        font-size: 12px;
        font-weight: 800;
    }

    .docout-input {
        width: 100%;
        min-height: 40px;
        padding: 9px 11px;
        border: 1px solid #cfd6de;
        border-radius: 8px;
        outline: none;
        color: var(--text);
        background: #fff;
        font-family: inherit;
        font-size: 13px;
    }

    .docout-input:focus {
        border-color: var(--red);
        box-shadow: 0 0 0 3px rgba(215, 25, 32, .1);
    }

    .docout-stat {
        min-height: 70px;
        padding: 11px 14px;
        border: 1px solid var(--border);
        border-radius: 9px;
        background: #f8fafc;
    }

    .docout-stat span {
        display: block;
        margin-bottom: 5px;
        color: var(--muted);
        font-size: 11px;
        font-weight: 700;
    }

    .docout-stat strong {
        color: var(--text);
        font-size: 21px;
        font-weight: 900;
    }

    .docout-alert {
        margin: 14px 20px 0;
        padding: 12px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
    }

    .docout-success {
        border: 1px solid #b7dfc5;
        color: #166534;
        background: #ecfdf3;
    }

    .docout-error {
        border: 1px solid #f0b4b7;
        color: #991b1b;
        background: #fff1f2;
    }

    .docout-error ul {
        margin: 7px 0 0;
        padding-left: 18px;
    }

    .docout-table-wrap {
        overflow-x: auto;
        padding: 0 20px 16px;
    }

    .docout-table {
        width: 100%;
        min-width: 1050px;
        border-collapse: separate;
        border-spacing: 0;
        color: #293244;
        font-size: 12px;
    }

    .docout-table th,
    .docout-table td {
        padding: 11px 10px;
        border-bottom: 1px solid #e5e9ee;
        text-align: left;
        vertical-align: middle;
    }

    .docout-table th {
        background: #f5f7f9;
        font-size: 11px;
        font-weight: 900;
        white-space: nowrap;
    }

    .docout-file {
        display: inline-flex;
        max-width: 180px;
        align-items: center;
        gap: 6px;
        color: #1468c3;
        font-weight: 800;
        text-decoration: none;
    }

    .docout-file span:last-child {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .docout-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .docout-action {
        display: inline-flex;
        min-height: 31px;
        align-items: center;
        justify-content: center;
        padding: 0 10px;
        border: 1px solid #d4dae1;
        border-radius: 7px;
        color: #283244;
        background: #fff;
        font-family: inherit;
        font-size: 11px;
        font-weight: 800;
        cursor: pointer;
    }

    .docout-danger {
        border-color: #f0b4b7;
        color: #b91c1c;
        background: #fff5f5;
    }

    .docout-empty {
        padding: 40px 20px !important;
        color: var(--muted);
        text-align: center !important;
    }

    .docout-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 13px 20px 16px;
        color: var(--muted);
        font-size: 12px;
    }

    .docout-pagination {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .docout-page-link {
        display: inline-flex;
        min-width: 34px;
        height: 34px;
        align-items: center;
        justify-content: center;
        padding: 0 10px;
        border: 1px solid #d8dee5;
        border-radius: 7px;
        color: #30394a;
        background: #fff;
        font-weight: 800;
        text-decoration: none;
    }

    .docout-disabled {
        opacity: .45;
        pointer-events: none;
    }

    .docout-modal {
        position: fixed;
        z-index: 1000;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(17, 24, 39, .58);
    }

    .docout-modal.is-open {
        display: flex;
    }

    .docout-dialog {
        width: min(760px, 100%);
        max-height: calc(100vh - 40px);
        overflow: auto;
        border-radius: 13px;
        background: #fff;
        box-shadow: 0 28px 80px rgba(0, 0, 0, .26);
    }

    .docout-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 17px 20px;
        border-bottom: 1px solid var(--border);
    }

    .docout-modal-header h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 900;
    }

    .docout-close {
        display: grid;
        width: 34px;
        height: 34px;
        place-items: center;
        border: 0;
        border-radius: 50%;
        background: #eef1f4;
        font-size: 20px;
        cursor: pointer;
    }

    .docout-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        padding: 20px;
    }

    .docout-full {
        grid-column: 1 / -1;
    }

    .docout-help {
        margin: 0;
        color: var(--muted);
        font-size: 11px;
    }

    .docout-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 9px;
        padding: 15px 20px;
        border-top: 1px solid var(--border);
        background: #fafbfc;
    }

    .docout-secondary {
        display: inline-flex;
        min-height: 39px;
        align-items: center;
        justify-content: center;
        padding: 0 15px;
        border: 1px solid #d4dae1;
        border-radius: 8px;
        color: #30394a;
        background: #fff;
        font-family: inherit;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
    }

    @media (max-width: 850px) {
        .docout-toolbar {
            grid-template-columns: 1fr 1fr;
        }

        .docout-toolbar form {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 650px) {
        .docout-header,
        .docout-footer {
            align-items: stretch;
            flex-direction: column;
        }

        .docout-toolbar,
        .docout-form-grid {
            grid-template-columns: 1fr;
        }

        .docout-full {
            grid-column: auto;
        }

        .docout-button {
            width: 100%;
        }
    }
</style>

<div class="docout-page">
    <section class="docout-card">
        <div class="docout-header">
            <div>
                <h1 class="docout-title">
                    Monitoring Surat Keluar
                </h1>

                <p class="docout-subtitle">
                    Pantau dokumen keluar dan simpan dokumen fisik
                    dalam bentuk PDF.
                </p>
            </div>

            <button
                type="button"
                class="docout-button"
                id="openCreateDocumentOut"
            >
                ＋ Input Dokumen Keluar
            </button>
        </div>

        @if (session('success'))
            <div class="docout-alert docout-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="docout-alert docout-error">
                Data belum dapat disimpan.

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="docout-toolbar">
            <form
                method="GET"
                action="{{ route('document-out.index') }}"
                class="docout-field"
            >
                <label for="bulanSurat">Pilih Bulan</label>

                <input
                    type="month"
                    name="bulan"
                    id="bulanSurat"
                    class="docout-input"
                    value="{{ $bulan }}"
                    onchange="this.form.submit()"
                >
            </form>

            <div class="docout-stat">
                <span>Surat {{ $labelBulan }}</span>
                <strong>
                    {{ number_format($statistik['bulanDipilih']) }}
                </strong>
            </div>

            <div class="docout-stat">
                <span>Total Seluruh Surat</span>
                <strong>
                    {{ number_format($statistik['total']) }}
                </strong>
            </div>
        </div>

        <div class="docout-table-wrap">
            <table class="docout-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Tujuan Surat</th>
                        <th>Nama</th>
                        <th>NRP</th>
                        <th>Jenis Dokumen</th>
                        <th>Nomor Surat</th>
                        <th>File PDF</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($suratKeluar as $item)
                        <tr>
                            <td>
                                {{
                                    ($suratKeluar->firstItem() ?? 1)
                                    + $loop->index
                                }}
                            </td>

                            <td>
                                {{
                                    $item->tanggal_surat
                                        ?->format('d/m/Y')
                                }}
                            </td>

                            <td>{{ $item->tujuan_surat }}</td>
                            <td>{{ $item->nama }}</td>
                            <td>{{ $item->nrp ?: '-' }}</td>
                            <td>{{ $item->jenis_surat }}</td>
                            <td>{{ $item->nomor_surat ?: '-' }}</td>

                            <td>
                                <a
                                    href="{{
                                        route(
                                            'document-out.file',
                                            $item
                                        )
                                    }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="docout-file"
                                    title="{{
                                        $item->file_nama_asli
                                    }}"
                                >
                                    <span>📄</span>
                                    <span>
                                        {{
                                            $item->file_nama_asli
                                        }}
                                    </span>
                                </a>
                            </td>

                            <td>
                                <div class="docout-actions">
                                    <button
                                        type="button"
                                        class="
                                            docout-action
                                            js-edit-document-out
                                        "
                                        data-id="{{ $item->id }}"
                                        data-tanggal="{{
                                            $item->tanggal_surat
                                                ?->format('Y-m-d')
                                        }}"
                                        data-nomor="{{
                                            $item->nomor_surat
                                        }}"
                                        data-tujuan="{{
                                            $item->tujuan_surat
                                        }}"
                                        data-nama="{{ $item->nama }}"
                                        data-nrp="{{ $item->nrp }}"
                                        data-jenis="{{
                                            $item->jenis_surat
                                        }}"
                                    >
                                        Edit
                                    </button>

                                    <form
                                        method="POST"
                                        action="{{
                                            route(
                                                'document-out.destroy',
                                                $item
                                            )
                                        }}"
                                        class="js-delete-document-out"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="
                                                docout-action
                                                docout-danger
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
                            <td
                                colspan="9"
                                class="docout-empty"
                            >
                                Belum ada surat keluar pada
                                {{ $labelBulan }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="docout-footer">
            <span>
                Menampilkan
                {{ $suratKeluar->firstItem() ?? 0 }}
                sampai
                {{ $suratKeluar->lastItem() ?? 0 }}
                dari
                {{ $suratKeluar->total() }}
                data
            </span>

            @if ($suratKeluar->hasPages())
                <nav class="docout-pagination">
                    <a
                        href="{{
                            $suratKeluar->previousPageUrl()
                                ?: '#'
                        }}"
                        class="
                            docout-page-link
                            {{
                                $suratKeluar->onFirstPage()
                                    ? 'docout-disabled'
                                    : ''
                            }}
                        "
                    >
                        ‹
                    </a>

                    <span>
                        Halaman
                        {{ $suratKeluar->currentPage() }}
                        dari
                        {{ $suratKeluar->lastPage() }}
                    </span>

                    <a
                        href="{{
                            $suratKeluar->nextPageUrl()
                                ?: '#'
                        }}"
                        class="
                            docout-page-link
                            {{
                                $suratKeluar->hasMorePages()
                                    ? ''
                                    : 'docout-disabled'
                            }}
                        "
                    >
                        ›
                    </a>
                </nav>
            @endif
        </div>
    </section>
</div>


{{-- Modal tambah --}}
<div
    class="docout-modal"
    id="createDocumentOutModal"
    aria-hidden="true"
>
    <div class="docout-dialog">
        <div class="docout-modal-header">
            <h2>Input Dokumen Keluar</h2>

            <button
                type="button"
                class="docout-close js-close-docout"
            >
                ×
            </button>
        </div>

        <form
            method="POST"
            action="{{ route('document-out.store') }}"
            enctype="multipart/form-data"
        >
            @csrf

            <div class="docout-form-grid">
                <div class="docout-field">
                    <label for="tanggalSurat">
                        Tanggal Surat
                    </label>

                    <input
                        type="date"
                        name="tanggal_surat"
                        id="tanggalSurat"
                        class="docout-input"
                        value="{{
                            old(
                                'tanggal_surat',
                                now()->format('Y-m-d')
                            )
                        }}"
                        required
                    >
                </div>

                <div class="docout-field">
                    <label for="nomorSurat">
                        Nomor Surat (Opsional)
                    </label>

                    <input
                        type="text"
                        name="nomor_surat"
                        id="nomorSurat"
                        class="docout-input"
                        value="{{ old('nomor_surat') }}"
                        maxlength="150"
                        placeholder="Kosongkan jika tidak ada nomor"
                    >

                    <p class="docout-help">
                        Nomor surat tidak wajib diisi.
                    </p>
                </div>

                <div class="docout-field docout-full">
                    <label for="tujuanSurat">
                        Tujuan Surat Keluar
                    </label>

                    <select
                        name="tujuan_surat"
                        id="tujuanSurat"
                        class="docout-input"
                        required
                    >
                        <option value="">
                            -- Pilih tujuan surat --
                        </option>

                        @foreach ($daftarTujuan as $tujuan)
                            <option
                                value="{{ $tujuan }}"
                                @selected(
                                    old('tujuan_surat') === $tujuan
                                )
                            >
                                {{ $tujuan }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="docout-field">
                    <label for="namaPenerima">Nama</label>

                    <input
                        type="text"
                        name="nama"
                        id="namaPenerima"
                        class="docout-input"
                        value="{{ old('nama') }}"
                        maxlength="150"
                        required
                    >
                </div>

                <div class="docout-field">
                    <label for="nrpPenerima">NRP</label>

                    <input
                        type="text"
                        name="nrp"
                        id="nrpPenerima"
                        class="docout-input"
                        value="{{ old('nrp') }}"
                        maxlength="50"
                    >
                </div>

                <div class="docout-field docout-full">
                    <label for="jenisSurat">
                        Jenis Dokumen Keluar
                    </label>

                    <select
                        name="jenis_surat"
                        id="jenisSurat"
                        class="docout-input"
                        required
                    >
                        <option value="">
                            -- Pilih jenis dokumen --
                        </option>

                        @foreach ($daftarJenisDokumen as $jenisDokumen)
                            <option
                                value="{{ $jenisDokumen }}"
                                @selected(
                                    old('jenis_surat') === $jenisDokumen
                                )
                            >
                                {{ $jenisDokumen }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="docout-field docout-full">
                    <label for="fileSurat">
                        Upload Dokumen Fisik (PDF)
                    </label>

                    <input
                        type="file"
                        name="file_surat"
                        id="fileSurat"
                        class="docout-input"
                        accept="application/pdf,.pdf"
                        required
                    >

                    <p class="docout-help">
                        Hanya PDF, maksimal 10 MB.
                    </p>
                </div>
            </div>

            <div class="docout-modal-footer">
                <button
                    type="button"
                    class="
                        docout-secondary
                        js-close-docout
                    "
                >
                    Batal
                </button>

                <button
                    type="submit"
                    class="docout-button"
                >
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal edit --}}
<div
    class="docout-modal"
    id="editDocumentOutModal"
    aria-hidden="true"
>
    <div class="docout-dialog">
        <div class="docout-modal-header">
            <h2>Edit Dokumen Keluar</h2>

            <button
                type="button"
                class="docout-close js-close-docout"
            >
                ×
            </button>
        </div>

        <form
            method="POST"
            action=""
            enctype="multipart/form-data"
            id="editDocumentOutForm"
        >
            @csrf
            @method('PUT')

            <div class="docout-form-grid">
                <div class="docout-field">
                    <label for="editTanggalSurat">
                        Tanggal Surat
                    </label>

                    <input
                        type="date"
                        name="tanggal_surat"
                        id="editTanggalSurat"
                        class="docout-input"
                        required
                    >
                </div>

                <div class="docout-field">
                    <label for="editNomorSurat">
                        Nomor Surat (Opsional)
                    </label>

                    <input
                        type="text"
                        name="nomor_surat"
                        id="editNomorSurat"
                        class="docout-input"
                        maxlength="150"
                        placeholder="Kosongkan jika tidak ada nomor"
                    >

                    <p class="docout-help">
                        Nomor surat tidak wajib diisi.
                    </p>
                </div>

                <div class="docout-field docout-full">
                    <label for="editTujuanSurat">
                        Tujuan Surat Keluar
                    </label>

                    <select
                        name="tujuan_surat"
                        id="editTujuanSurat"
                        class="docout-input"
                        required
                    >
                        <option value="">
                            -- Pilih tujuan surat --
                        </option>

                        @foreach ($daftarTujuan as $tujuan)
                            <option value="{{ $tujuan }}">
                                {{ $tujuan }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="docout-field">
                    <label for="editNamaPenerima">
                        Nama
                    </label>

                    <input
                        type="text"
                        name="nama"
                        id="editNamaPenerima"
                        class="docout-input"
                        maxlength="150"
                        required
                    >
                </div>

                <div class="docout-field">
                    <label for="editNrpPenerima">NRP</label>

                    <input
                        type="text"
                        name="nrp"
                        id="editNrpPenerima"
                        class="docout-input"
                        maxlength="50"
                    >
                </div>

                <div class="docout-field docout-full">
                    <label for="editJenisSurat">
                        Jenis Dokumen Keluar
                    </label>

                    <select
                        name="jenis_surat"
                        id="editJenisSurat"
                        class="docout-input"
                        required
                    >
                        <option value="">
                            -- Pilih jenis dokumen --
                        </option>

                        @foreach ($daftarJenisDokumen as $jenisDokumen)
                            <option value="{{ $jenisDokumen }}">
                                {{ $jenisDokumen }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="docout-field docout-full">
                    <label for="editFileSurat">
                        Ganti File PDF
                    </label>

                    <input
                        type="file"
                        name="file_surat"
                        id="editFileSurat"
                        class="docout-input"
                        accept="application/pdf,.pdf"
                    >

                    <p class="docout-help">
                        Kosongkan jika tetap memakai PDF lama.
                    </p>
                </div>
            </div>

            <div class="docout-modal-footer">
                <button
                    type="button"
                    class="
                        docout-secondary
                        js-close-docout
                    "
                >
                    Batal
                </button>

                <button
                    type="submit"
                    class="docout-button"
                >
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const createModal = document.getElementById(
            'createDocumentOutModal'
        );

        const editModal = document.getElementById(
            'editDocumentOutModal'
        );

        const updateUrl = @json(
            route(
                'document-out.update',
                ['suratKeluar' => '__ID__']
            )
        );

        function openModal(modal) {
            if (!modal) {
                return;
            }

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
        }

        function closeModal(modal) {
            if (!modal) {
                return;
            }

            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
        }

        document.getElementById(
            'openCreateDocumentOut'
        )?.addEventListener('click', function () {
            openModal(createModal);
        });

        document.querySelectorAll(
            '.js-close-docout'
        ).forEach(function (button) {
            button.addEventListener('click', function () {
                closeModal(
                    button.closest('.docout-modal')
                );
            });
        });

        document.querySelectorAll(
            '.docout-modal'
        ).forEach(function (modal) {
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal(modal);
                }
            });
        });

        document.querySelectorAll(
            '.js-edit-document-out'
        ).forEach(function (button) {
            button.addEventListener('click', function () {
                const form = document.getElementById(
                    'editDocumentOutForm'
                );

                form.action = updateUrl.replace(
                    '__ID__',
                    encodeURIComponent(button.dataset.id)
                );

                document.getElementById(
                    'editTanggalSurat'
                ).value = button.dataset.tanggal || '';

                document.getElementById(
                    'editNomorSurat'
                ).value = button.dataset.nomor || '';

                document.getElementById(
                    'editTujuanSurat'
                ).value = button.dataset.tujuan || '';

                document.getElementById(
                    'editNamaPenerima'
                ).value = button.dataset.nama || '';

                document.getElementById(
                    'editNrpPenerima'
                ).value = button.dataset.nrp || '';

                document.getElementById(
                    'editJenisSurat'
                ).value = button.dataset.jenis || '';

                openModal(editModal);
            });
        });

        document.querySelectorAll(
            '.js-delete-document-out'
        ).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (
                    !window.confirm(
                        'Hapus data dan file PDF dokumen ini?'
                    )
                ) {
                    event.preventDefault();
                }
            });
        });

        document.addEventListener(
            'keydown',
            function (event) {
                if (event.key !== 'Escape') {
                    return;
                }

                document.querySelectorAll(
                    '.docout-modal.is-open'
                ).forEach(function (modal) {
                    closeModal(modal);
                });
            }
        );

        @if ($errors->any())
            openModal(createModal);
        @endif
    });
</script>