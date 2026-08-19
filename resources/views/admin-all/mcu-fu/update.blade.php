@extends('admin-all.layout')

@section('title', 'Update MCU & Follow Up — SYNRGYPRO')

@push('styles')
<style>
    /*
    |--------------------------------------------------------------------------
    | FIXED SHELL — UPDATE MCU & FOLLOW UP
    |--------------------------------------------------------------------------
    | Header/sidebar/footer tetap milik layout.
    | Judul + filter + table header tetap.
    | HANYA body data tabel yang scroll.
    |--------------------------------------------------------------------------
    */

    .aa-main {
        overflow: hidden;
    }

    .aa-main > .aa-content {
        height: 100%;
        min-height: 0;
        overflow: hidden;
    }

    .mfu-page {
        display: flex;
        width: 100%;
        height: 100%;
        min-height: 0;
        flex-direction: column;
        gap: 8px;
        overflow: hidden;
    }

    .mfu-page > .aa-page-title,
    .mfu-page > .mfu-alert,
    .mfu-page > .mfu-filter-shell {
        flex: 0 0 auto;
    }

    .mfu-filter-shell {
        display: grid;
        grid-template-columns:
            190px
            110px
            130px
            150px
            minmax(220px, 1fr)
            70px
            66px
            auto;
        align-items: end;
        gap: 7px;
        padding: 8px;
        border: 1px solid #d7e0e8;
        border-radius: 9px;
        background: #fff;
        box-shadow: 0 3px 12px rgba(31, 47, 65, .04);
    }

    .mfu-field {
        display: grid;
        gap: 3px;
        min-width: 0;
    }

    .mfu-field label {
        color: #607285;
        font-size: 7px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .mfu-field input,
    .mfu-field select {
        width: 100%;
        min-width: 0;
        min-height: 31px;
        padding: 5px 8px;
        border: 1px solid #cbd6e1;
        border-radius: 6px;
        color: #17324a;
        background: #fff;
        font-size: 8px;
        outline: none;
    }

    .mfu-button {
        display: inline-flex;
        min-height: 31px;
        align-items: center;
        justify-content: center;
        padding: 6px 9px;
        border: 1px solid #cbd6e1;
        border-radius: 6px;
        color: #1d354c;
        background: #fff;
        font-size: 7px;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
        cursor: pointer;
    }

    .mfu-button.primary {
        border-color: #0f78ef;
        color: #fff;
        background: #0f78ef;
    }

    .mfu-filter-result {
        justify-self: end;
        align-self: center;
        color: #607285;
        font-size: 7px;
        font-weight: 900;
        white-space: nowrap;
    }

    .mfu-alert {
        padding: 8px 10px;
        border-radius: 7px;
        font-size: 8px;
        font-weight: 800;
    }

    .mfu-alert.success {
        border: 1px solid #a9e3c6;
        color: #12643b;
        background: #edfff5;
    }

    .mfu-alert.error {
        border: 1px solid #f0c2c5;
        color: #9b1c25;
        background: #fff1f2;
    }

    .mfu-table-toolbar {
        display: flex;
        flex: 0 0 auto;
        min-height: 38px;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 6px 8px;
        border-bottom: 1px solid #e1e7ec;
        background: #fff;
    }

    .mfu-rows-form {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #516579;
        font-size: 7px;
        font-weight: 800;
    }

    .mfu-rows-form select {
        min-width: 66px;
        min-height: 28px;
        padding: 4px 7px;
        border: 1px solid #cbd6e1;
        border-radius: 6px;
        color: #18344c;
        background: #fff;
        font-size: 8px;
        font-weight: 900;
        outline: none;
    }

    .mfu-auto-sync {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 6px;
        border-radius: 999px;
        color: #0b6a43;
        background: #eaf9f1;
        font-size: 6px;
        font-weight: 900;
        white-space: nowrap;
    }

    .mfu-auto-sync.paused {
        color: #9a5a0c;
        background: #fff4dd;
    }

    .mfu-auto-sync-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: currentColor;
    }

    .mfu-toolbar-count {
        color: #65768a;
        font-size: 7px;
        font-weight: 900;
        white-space: nowrap;
    }

    .mfu-status-mcu {
        display: inline-flex;
        width: max-content;
        min-width: 62px;
        align-items: center;
        justify-content: center;
        padding: 5px 8px;
        border-radius: 999px;
        font-size: 7px;
        font-weight: 900;
    }

    .mfu-status-mcu.done {
        color: #0c6c40;
        background: #e8f8ef;
        border: 1px solid #bce8cf;
    }

    .mfu-status-mcu.not-yet {
        color: #a32630;
        background: #ffecee;
        border: 1px solid #f1c8cc;
    }

    .mfu-dialog-person {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 5px;
        margin-top: 3px;
        color: #617286;
        font-size: 7px;
        font-weight: 800;
    }

    .mfu-dialog-person strong {
        color: #18344c;
        font-size: 8px;
    }

    .mfu-table-card {
        display: flex;
        min-height: 0;
        flex: 1 1 auto;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid #d9e0e7;
        border-radius: 9px;
        background: #fff;
    }

    .mfu-table-wrap {
        min-height: 0;
        flex: 1 1 auto;
        overflow: auto;
        scrollbar-gutter: stable;
        overscroll-behavior: contain;
    }

    .mfu-table {
        width: 100%;
        min-width: 1180px;
        border-collapse: collapse;
        font-size: 8px;
    }

    .mfu-table th {
        position: sticky;
        top: 0;
        z-index: 2;
        padding: 8px;
        border-bottom: 1px solid #d5dee7;
        color: #fff;
        background: #173b63;
        font-size: 7px;
        text-align: left;
        white-space: nowrap;
    }

    .mfu-table td {
        padding: 7px 8px;
        border-bottom: 1px solid #edf1f4;
        vertical-align: middle;
    }

    .mfu-table tbody tr:hover td {
        background: #f8fbfd;
    }

    .mfu-name strong {
        display: block;
        color: #122d45;
        font-size: 8px;
    }

    .mfu-name span {
        display: block;
        margin-top: 2px;
        color: #718297;
        font-size: 7px;
    }

    .mfu-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 6px;
        border-radius: 999px;
        color: #33536d;
        background: #eef4f8;
        font-size: 6.5px;
        font-weight: 900;
    }

    .mfu-expiry {
        display: grid;
        gap: 3px;
    }

    .mfu-expiry strong {
        color: #19364f;
        font-size: 8px;
    }

    .mfu-status {
        display: inline-flex;
        width: max-content;
        padding: 3px 6px;
        border-radius: 999px;
        font-size: 6px;
        font-weight: 900;
    }

    .mfu-status.safe {
        color: #117044;
        background: #e9f9f0;
    }

    .mfu-status.warning {
        color: #a05b08;
        background: #fff4dd;
    }

    .mfu-status.expired {
        color: #a52631;
        background: #ffe9eb;
    }

    .mfu-status.no-data {
        color: #64748b;
        background: #edf1f5;
    }

    .mfu-edit-btn {
        min-width: 72px;
        border: 0;
        border-radius: 6px;
        padding: 7px 9px;
        color: #fff;
        background: #0f78ef;
        font-size: 7px;
        font-weight: 900;
        cursor: pointer;
    }

    .mfu-pagination {
        flex: 0 0 auto;
        padding: 6px 8px;
        border-top: 1px solid #e1e7ec;
        background: #fff;
        font-size: 8px;
    }

    /*
     * Fix SVG pagination Laravel yang sebelumnya membesar memenuhi halaman.
     */
    .mfu-pagination svg {
        width: 14px !important;
        height: 14px !important;
    }

    .mfu-pagination nav > div:first-child {
        display: none;
    }

    .mfu-pagination nav > div:last-child {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .mfu-pagination nav span,
    .mfu-pagination nav a {
        font-size: 8px;
    }

    /*
    |--------------------------------------------------------------------------
    | Dialog / Unified Form
    |--------------------------------------------------------------------------
    */

    .mfu-dialog {
        width: min(900px, calc(100vw - 40px));
        max-height: calc(100vh - 42px);
        padding: 0;
        border: 0;
        border-radius: 12px;
        box-shadow: 0 22px 70px rgba(15, 32, 49, .28);
    }

    .mfu-dialog::backdrop {
        background: rgba(15, 30, 45, .58);
    }

    .mfu-dialog-shell {
        display: flex;
        max-height: calc(100vh - 42px);
        flex-direction: column;
        overflow: hidden;
        background: #f6f8fa;
    }

    .mfu-dialog-head {
        display: flex;
        flex: 0 0 auto;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        border-bottom: 1px solid #d8e0e7;
        background: #fff;
    }

    .mfu-dialog-head h2 {
        margin: 0;
        color: #102d47;
        font-size: 14px;
    }

    .mfu-dialog-head p {
        margin: 2px 0 0;
        color: #718196;
        font-size: 7px;
    }

    .mfu-close {
        width: 30px;
        height: 30px;
        border: 1px solid #d3dce5;
        border-radius: 7px;
        color: #475d71;
        background: #fff;
        font-size: 15px;
        cursor: pointer;
    }

    .mfu-dialog-body {
        display: grid;
        min-height: 0;
        flex: 1 1 auto;
        gap: 9px;
        padding: 10px;
        overflow-y: auto;
    }

    .mfu-identity {
        display: grid;
        grid-template-columns: 1.4fr .7fr 1fr;
        gap: 7px;
    }

    .mfu-info-box {
        padding: 8px 10px;
        border: 1px solid #d8e0e7;
        border-radius: 8px;
        background: #fff;
    }

    .mfu-info-box span {
        display: block;
        color: #78879a;
        font-size: 6px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .mfu-info-box strong {
        display: block;
        margin-top: 3px;
        color: #15324b;
        font-size: 9px;
    }

    .mfu-validity {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 7px;
    }

    .mfu-section {
        overflow: hidden;
        border: 1px solid #d8e0e7;
        border-radius: 9px;
        background: #fff;
    }

    .mfu-section-title {
        padding: 8px 10px;
        border-bottom: 1px solid #e1e7ec;
        color: #16334c;
        background: #f7fafc;
        font-size: 8px;
        font-weight: 900;
    }

    .mfu-section-title.mcu {
        color: #155ca4;
        background: #eef6ff;
    }

    .mfu-section-title.fu {
        color: #157050;
        background: #eefaf5;
    }

    .mfu-section-title.simper {
        color: #9a5a0c;
        background: #fff7e8;
    }

    .mfu-form-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
        padding: 9px;
    }

    .mfu-form-grid.five {
        grid-template-columns:
            repeat(3, minmax(0, 1fr));
    }

    .mfu-form-field {
        display: grid;
        gap: 4px;
    }

    .mfu-form-field label {
        color: #637386;
        font-size: 6px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .mfu-form-field input,
    .mfu-form-field select {
        width: 100%;
        min-width: 0;
        min-height: 34px;
        padding: 6px 8px;
        border: 1px solid #cdd7e1;
        border-radius: 6px;
        color: #18344c;
        background: #fff;
        font-size: 8px;
        outline: none;
    }

    .mfu-simper-note {
        padding: 0 9px 9px;
        color: #66788b;
        font-size: 7px;
        line-height: 1.45;
    }

    .mfu-simper-note strong {
        color: #a05b08;
    }

    .mfu-dialog-footer {
        display: flex;
        flex: 0 0 auto;
        align-items: center;
        justify-content: flex-end;
        gap: 7px;
        padding: 9px 12px;
        border-top: 1px solid #d8e0e7;
        background: #fff;
    }

    .mfu-readonly {
        display: flex;
        min-height: 34px;
        align-items: center;
        padding: 6px 8px;
        border: 1px solid #d7e0e8;
        border-radius: 6px;
        color: #314c63;
        background: #f5f8fa;
        font-size: 8px;
        font-weight: 900;
    }

    .mfu-save[disabled] {
        opacity: .45;
        cursor: not-allowed;
    }

    .mfu-change-count {
        display: none;
        padding: 4px 7px;
        border-radius: 999px;
        color: #0f5c9d;
        background: #eaf4ff;
        font-size: 7px;
        font-weight: 900;
    }

    .mfu-change-count.show {
        display: inline-flex;
    }

    .mfu-save {
        min-height: 33px;
        padding: 7px 14px;
        border: 0;
        border-radius: 7px;
        color: #fff;
        background: #0f78ef;
        font-size: 8px;
        font-weight: 900;
        cursor: pointer;
    }

    /*
    |--------------------------------------------------------------------------
    | Employee Lifecycle
    |--------------------------------------------------------------------------
    */

    .mfu-employee-button {
        border-color: #157050;
        color: #fff;
        background: #157050;
    }

    .mfu-employee-button.status {
        border-color: #d18416;
        color: #7b4a07;
        background: #fff7e8;
    }

    .mfu-lookup-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 92px;
        gap: 7px;
        padding: 9px;
    }

    .mfu-lookup-result {
        margin: 0 9px 9px;
        padding: 8px 10px;
        border: 1px solid #d8e0e7;
        border-radius: 8px;
        color: #54677a;
        background: #f8fafc;
        font-size: 7px;
        line-height: 1.5;
    }

    .mfu-lookup-result.found {
        border-color: #a9e3c6;
        color: #12643b;
        background: #edfff5;
    }

    .mfu-lookup-result.missing {
        border-color: #efcf99;
        color: #8b5708;
        background: #fff8e9;
    }

    .mfu-lookup-result.error {
        border-color: #f0c2c5;
        color: #9b1c25;
        background: #fff1f2;
    }

    .mfu-employee-manual[hidden],
    .mfu-employee-found[hidden] {
        display: none !important;
    }

    .mfu-employee-hint {
        padding: 0 9px 9px;
        color: #66788b;
        font-size: 7px;
        line-height: 1.5;
    }

    .mfu-readonly-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 7px;
        padding: 9px;
    }

    .mfu-lifecycle-submit:disabled {
        cursor: not-allowed;
        opacity: .45;
    }

    @media (max-width: 1100px) {
        .mfu-filter-shell {
            grid-template-columns:
                180px 105px 125px 145px
                minmax(180px, 1fr)
                68px 64px;
            overflow-x: auto;
        }

        .mfu-filter-result {
            display: none;
        }
    }

    @media (max-width: 700px) {
        .mfu-identity,
        .mfu-validity,
        .mfu-form-grid,
        .mfu-form-grid.five {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('admin-content')
@php
    $monthNames = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    $service = app(
        \App\Services\McuFuInternalService::class
    );
@endphp

<div class="mfu-page">

    <div class="aa-page-title">
        <div>
            <h1>Update MCU &amp; Follow Up</h1>
            <p>
                MCU, Follow Up, dan masa berlaku SIM/SIB DLT dalam satu halaman.
            </p>
        </div>

        <div class="aa-title-actions">
            <button
                type="button"
                class="mfu-button mfu-employee-button"
                data-open-employee-add
            >
                + TAMBAH KARYAWAN
            </button>

            <button
                type="button"
                class="mfu-button mfu-employee-button status"
                data-open-employee-lifecycle
            >
                STATUS / MUTASI
            </button>

            <a
                href="{{ route('admin-all.mcu-fu.index') }}"
                class="mfu-button"
            >
                DASHBOARD
            </a>

            <a
                href="{{ route('admin-all.mcu-fu.history') }}"
                class="mfu-button"
            >
                RIWAYAT UPDATE
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mfu-alert success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mfu-alert error">
            {{ session('error') }}
        </div>
    @endif

    @if (!empty($error))
        <div class="mfu-alert error">
            {{ $error }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mfu-alert error">
            {{ $errors->first() }}
        </div>
    @endif


    {{-- ============================================================
         TAMBAH KARYAWAN — PIPELINE UPDATE_DATA_KARYAWAN
         ============================================================ --}}
    <dialog
        class="mfu-dialog"
        id="employeeAddDialog"
    >
        <form
            method="POST"
            action="{{ route('admin-all.mcu-fu.employee.store') }}"
            class="mfu-dialog-shell"
            data-employee-add-form
        >
            @csrf

            <div class="mfu-dialog-head">
                <div>
                    <h2>Tambah Karyawan</h2>
                    <p>
                        NRP dicek ke MASTER_DATABASE. Input manual hanya dibuka jika NRP belum ditemukan.
                    </p>
                </div>

                <button
                    type="button"
                    class="mfu-close"
                    data-close-dialog
                >
                    ×
                </button>
            </div>

            <div class="mfu-dialog-body">
                <div class="mfu-section">
                    <div class="mfu-section-title">
                        CEK NRP MASTER_DATABASE
                    </div>

                    <div class="mfu-lookup-row">
                        <div class="mfu-form-field">
                            <label>NRP</label>
                            <input
                                type="text"
                                name="nrp"
                                id="employeeAddNrp"
                                maxlength="40"
                                autocomplete="off"
                                required
                            >
                        </div>

                        <button
                            type="button"
                            class="mfu-button primary"
                            data-lookup-add
                        >
                            CEK NRP
                        </button>
                    </div>

                    <div
                        class="mfu-lookup-result"
                        data-add-lookup-result
                    >
                        Masukkan NRP lalu klik CEK NRP.
                    </div>
                </div>

                <div
                    class="mfu-section mfu-employee-found"
                    data-add-found
                    hidden
                >
                    <div class="mfu-section-title">
                        SUDAH TERDAFTAR
                    </div>

                    <div class="mfu-readonly-grid">
                        <div class="mfu-info-box">
                            <span>Nama</span>
                            <strong data-add-found-name>-</strong>
                        </div>
                        <div class="mfu-info-box">
                            <span>Jabatan</span>
                            <strong data-add-found-position>-</strong>
                        </div>
                        <div class="mfu-info-box">
                            <span>Site</span>
                            <strong data-add-found-site>-</strong>
                        </div>
                        <div class="mfu-info-box">
                            <span>Status</span>
                            <strong data-add-found-status>-</strong>
                        </div>
                    </div>

                    <div class="mfu-employee-hint">
                        NRP sudah ada di MASTER_DATABASE sehingga tidak boleh ditambah ulang.
                        Gunakan tombol <strong>STATUS / MUTASI</strong> jika ingin mengubah lifecycle.
                    </div>
                </div>

                <div
                    class="mfu-section mfu-employee-manual"
                    data-add-manual
                    hidden
                >
                    <div class="mfu-section-title mcu">
                        DATA KARYAWAN BARU
                    </div>

                    <div class="mfu-form-grid">
                        <div class="mfu-form-field">
                            <label>Nama Lengkap</label>
                            <input
                                type="text"
                                name="nama"
                                maxlength="150"
                                data-add-required
                            >
                        </div>

                        <div class="mfu-form-field">
                            <label>Jabatan</label>
                            <input
                                type="text"
                                name="jabatan"
                                maxlength="150"
                            >
                        </div>

                        <div class="mfu-form-field">
                            <label>Departemen</label>
                            <input
                                type="text"
                                name="departemen"
                                value="PRODUKSI"
                                maxlength="100"
                                data-add-required
                            >
                        </div>

                        <div class="mfu-form-field">
                            <label>Site</label>
                            <input
                                type="text"
                                name="site"
                                value="BUKIT ASAM"
                                maxlength="100"
                                list="employeeSiteOptions"
                                data-add-required
                            >
                        </div>

                        <div class="mfu-form-field">
                            <label>Status Karyawan</label>
                            <select
                                name="status_karyawan"
                                data-add-required
                            >
                                <option value="NEW HIRE">
                                    NEW HIRE
                                </option>
                                <option value="EXISTING DATA">
                                    EXISTING DATA
                                </option>
                            </select>
                        </div>

                        <div class="mfu-form-field">
                            <label>Catatan</label>
                            <input
                                type="text"
                                name="catatan"
                                maxlength="500"
                                placeholder="Opsional"
                            >
                        </div>
                    </div>

                    <div class="mfu-employee-hint">
                        Data tidak ditulis langsung ke MASTER_DATABASE.
                        Sistem mengirim lewat <strong>UPDATE_DATA_KARYAWAN</strong>,
                        lalu MASTER_DATABASE tetap menjadi sumber final.
                    </div>
                </div>
            </div>

            <input
                type="hidden"
                name="_return_per_page"
                value="{{ $perPage ?? 20 }}"
            >

            <div class="mfu-dialog-footer">
                <button
                    type="button"
                    class="mfu-button"
                    data-close-dialog
                >
                    BATAL
                </button>

                <button
                    type="submit"
                    class="mfu-save mfu-lifecycle-submit"
                    data-add-submit
                    disabled
                >
                    SIMPAN KARYAWAN
                </button>
            </div>
        </form>
    </dialog>

    {{-- ============================================================
         STATUS / MUTASI — PIPELINE UPDATE_STATUS_KARYAWAN
         ============================================================ --}}
    <dialog
        class="mfu-dialog"
        id="employeeLifecycleDialog"
    >
        <form
            method="POST"
            action="{{ route('admin-all.mcu-fu.employee.lifecycle') }}"
            class="mfu-dialog-shell"
            data-employee-lifecycle-form
        >
            @csrf

            <div class="mfu-dialog-head">
                <div>
                    <h2>Status / Mutasi Karyawan</h2>
                    <p>
                        Cari dengan NRP. Nama/Jabatan/Site diambil otomatis dari MASTER_DATABASE.
                    </p>
                </div>

                <button
                    type="button"
                    class="mfu-close"
                    data-close-dialog
                >
                    ×
                </button>
            </div>

            <div class="mfu-dialog-body">
                <div class="mfu-section">
                    <div class="mfu-section-title">
                        CARI KARYAWAN
                    </div>

                    <div class="mfu-lookup-row">
                        <div class="mfu-form-field">
                            <label>NRP</label>
                            <input
                                type="text"
                                name="nrp"
                                id="employeeLifecycleNrp"
                                maxlength="40"
                                autocomplete="off"
                                required
                            >
                        </div>

                        <button
                            type="button"
                            class="mfu-button primary"
                            data-lookup-lifecycle
                        >
                            CARI
                        </button>
                    </div>

                    <div
                        class="mfu-lookup-result"
                        data-lifecycle-lookup-result
                    >
                        Masukkan NRP lalu klik CARI.
                    </div>
                </div>

                <div
                    class="mfu-section"
                    data-lifecycle-found
                    hidden
                >
                    <div class="mfu-section-title">
                        DATA MASTER
                    </div>

                    <div class="mfu-readonly-grid">
                        <div class="mfu-info-box">
                            <span>Nama</span>
                            <strong data-life-name>-</strong>
                        </div>
                        <div class="mfu-info-box">
                            <span>Jabatan</span>
                            <strong data-life-position>-</strong>
                        </div>
                        <div class="mfu-info-box">
                            <span>Site Saat Ini</span>
                            <strong data-life-site>-</strong>
                        </div>
                        <div class="mfu-info-box">
                            <span>Status Saat Ini</span>
                            <strong data-life-status>-</strong>
                        </div>
                    </div>
                </div>

                <div
                    class="mfu-section"
                    data-lifecycle-fields
                    hidden
                >
                    <div class="mfu-section-title fu">
                        PERUBAHAN LIFECYCLE
                    </div>

                    <div class="mfu-form-grid">
                        <div class="mfu-form-field">
                            <label>Status Karyawan</label>
                            <select
                                name="status_baru"
                                id="employeeLifecycleStatus"
                                required
                            >
                                <option value="">
                                    -- PILIH STATUS --
                                </option>
                                <option value="NEW HIRE">
                                    NEW HIRE
                                </option>
                                <option value="EXISTING DATA">
                                    EXISTING DATA
                                </option>
                                <option value="RESIGN">
                                    RESIGN
                                </option>
                                <option value="MUTASI">
                                    MUTASI
                                </option>
                                <option value="TERMINATED">
                                    TERMINATED
                                </option>
                            </select>
                        </div>

                        <div class="mfu-form-field">
                            <label id="employeeLifecycleSiteLabel">
                                Site Baru / Tujuan
                            </label>
                            <input
                                type="text"
                                name="site_baru"
                                id="employeeLifecycleSite"
                                maxlength="100"
                                list="employeeSiteOptions"
                                placeholder="Kosongkan jika tidak berubah"
                            >
                        </div>

                        <div class="mfu-form-field">
                            <label>Tanggal Efektif</label>
                            <input
                                type="date"
                                name="tanggal_efektif"
                                value="{{ now()->format('Y-m-d') }}"
                                required
                            >
                        </div>

                        <div
                            class="mfu-form-field"
                            style="grid-column: span 3;"
                        >
                            <label>Catatan</label>
                            <input
                                type="text"
                                name="catatan"
                                maxlength="500"
                                placeholder="Contoh: Mutasi Bukit Asam ke BIB"
                            >
                        </div>
                    </div>

                    <div class="mfu-employee-hint">
                        <strong>MUTASI / RESIGN / TERMINATED</strong>
                        langsung dieliminasi dari data operasional aktif MCU &amp; FU.
                        Data lama dan history tidak dihapus.
                    </div>
                </div>
            </div>

            <div class="mfu-dialog-footer">
                <button
                    type="button"
                    class="mfu-button"
                    data-close-dialog
                >
                    BATAL
                </button>

                <button
                    type="submit"
                    class="mfu-save mfu-lifecycle-submit"
                    data-lifecycle-submit
                    disabled
                >
                    SIMPAN STATUS
                </button>
            </div>
        </form>
    </dialog>

    <datalist id="employeeSiteOptions">
        <option value="BUKIT ASAM"></option>
        <option value="BIB"></option>
    </datalist>

    <form
        method="GET"
        action="{{ route('admin-all.mcu-fu.update') }}"
        class="mfu-filter-shell"
    >
        <div class="mfu-field">
            <label for="dateType">
                Filter Berdasarkan
            </label>

            <select
                name="date_type"
                id="dateType"
            >
                <option
                    value="jadwal_mcu"
                    @selected(($filters['date_type'] ?? '') === 'jadwal_mcu')
                >
                    MCU — Jadwal MCU
                </option>

                <option
                    value="exp_mcu"
                    @selected(($filters['date_type'] ?? '') === 'exp_mcu')
                >
                    MCU — EXP MCU
                </option>

                <option
                    value="follow_up"
                    @selected(($filters['date_type'] ?? '') === 'follow_up')
                >
                    Follow Up — Jadwal FU
                </option>

                <option
                    value="simper"
                    @selected(($filters['date_type'] ?? '') === 'simper')
                >
                    EXP SIM / SIB DLT
                </option>
            </select>
        </div>

        <div class="mfu-field">
            <label for="filterYear">
                Tahun
            </label>

            <select
                name="year"
                id="filterYear"
            >
                <option value="">
                    Semua
                </option>

                @foreach ($years as $year)
                    <option
                        value="{{ $year }}"
                        @selected((int) ($filters['year'] ?? 0) === (int) $year)
                    >
                        {{ $year }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mfu-field">
            <label for="filterMonth">
                Bulan
            </label>

            <select
                name="month"
                id="filterMonth"
            >
                <option value="">
                    Semua Bulan
                </option>

                @foreach ($monthNames as $number => $label)
                    <option
                        value="{{ $number }}"
                        @selected((int) ($filters['month'] ?? 0) === $number)
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mfu-field">
            <label for="simperExp">
                SIM/SIB EXP
            </label>

            <select
                name="simper_exp"
                id="simperExp"
            >
                <option value="">
                    Semua Status
                </option>

                <option
                    value="H-40"
                    @selected(($filters['simper_exp'] ?? '') === 'H-40')
                >
                    H-40
                </option>

                <option
                    value="H-30"
                    @selected(($filters['simper_exp'] ?? '') === 'H-30')
                >
                    H-30
                </option>

                <option
                    value="H-14"
                    @selected(($filters['simper_exp'] ?? '') === 'H-14')
                >
                    H-14
                </option>

                <option
                    value="H-7"
                    @selected(($filters['simper_exp'] ?? '') === 'H-7')
                >
                    H-7
                </option>

                <option
                    value="EXPIRED"
                    @selected(($filters['simper_exp'] ?? '') === 'EXPIRED')
                >
                    EXPIRED
                </option>
            </select>
        </div>

        <div class="mfu-field">
            <label for="filterSearch">
                Nama / NRP
            </label>

            <input
                type="text"
                name="q"
                id="filterSearch"
                value="{{ request('q') }}"
                placeholder="Cari nama atau NRP..."
            >
        </div>

        <input
            type="hidden"
            name="per_page"
            value="{{ $perPage ?? 20 }}"
        >

        {{-- Preserve dashboard drill-down when user searches again --}}
        @foreach (
            [
                'hasil_mcu',
                'status_mcu',
                'status_fu',
                'jabatan',
                'fu_stage',
                'follow_up_value',
            ] as $hiddenFilter
        )
            @if (
                request($hiddenFilter) !== null &&
                request($hiddenFilter) !== ''
            )
                <input
                    type="hidden"
                    name="{{ $hiddenFilter }}"
                    value="{{ request($hiddenFilter) }}"
                >
            @endif
        @endforeach

        <button
            type="submit"
            class="mfu-button primary"
        >
            SEARCH
        </button>

        <a
            href="{{ route('admin-all.mcu-fu.update') }}"
            class="mfu-button"
        >
            RESET
        </a>

        <div class="mfu-filter-result">
            {{ number_format($data->total()) }} DATA
        </div>
    </form>

    <div class="mfu-table-card">

        <div class="mfu-table-toolbar">
            <form
                method="GET"
                action="{{ route('admin-all.mcu-fu.update') }}"
                class="mfu-rows-form"
            >
                @foreach (request()->except(['per_page', 'page']) as $queryName => $queryValue)
                    @if (!is_array($queryValue))
                        <input
                            type="hidden"
                            name="{{ $queryName }}"
                            value="{{ $queryValue }}"
                        >
                    @endif
                @endforeach

                <span>Show</span>

                <select
                    name="per_page"
                    onchange="this.form.submit()"
                    aria-label="Rows per page"
                >
                    @foreach ([20, 50, 100] as $size)
                        <option
                            value="{{ $size }}"
                            @selected((int) ($perPage ?? 20) === $size)
                        >
                            {{ $size }}
                        </option>
                    @endforeach
                </select>

                <span>rows</span>
            </form>

            <div
                style="display:flex;align-items:center;gap:6px;"
            >
                <span
                    class="mfu-auto-sync"
                    data-auto-sync-status
                    title="Auto sync 120 detik. Pause otomatis saat form edit dibuka atau ada perubahan belum disimpan."
                >
                    <span class="mfu-auto-sync-dot"></span>
                    <span data-auto-sync-label>AUTO SYNC 120s</span>
                </span>

                <div class="mfu-toolbar-count">
                    Showing
                    {{ number_format($data->firstItem() ?? 0) }}
                    –
                    {{ number_format($data->lastItem() ?? 0) }}
                    of
                    {{ number_format($data->total()) }}
                    data
                </div>
            </div>
        </div>

        <div class="mfu-table-wrap">
            <table class="mfu-table">
                <thead>
                    <tr>
                        <th>ROW</th>
                        <th>NRP / NAMA</th>
                        <th>JABATAN</th>
                        <th>EXP MCU</th>
                        <th>EXP SIM/SIB DLT</th>
                        <th>HASIL MCU</th>
                        <th>STATUS MCU</th>
                        <th>FOLLOW UP</th>
                        <th>STATUS FU</th>
                        <th>ACTION</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($data as $row)
                        @php
                            $mcuExpiry =
                                $service->expiryMeta(
                                    $row['exp_mcu']
                                );

                            $simperExpiry =
                                $service->expiryMeta(
                                    $row['expired_sim_dlt']
                                );

                            $mcuClass = match (
                                $mcuExpiry['status']
                            ) {
                                'SAFE' => 'safe',
                                'WARNING' => 'warning',
                                'EXPIRED' => 'expired',
                                default => 'no-data',
                            };

                            $simperClass = match (
                                $simperExpiry['status']
                            ) {
                                'SAFE' => 'safe',
                                'WARNING' => 'warning',
                                'EXPIRED' => 'expired',
                                default => 'no-data',
                            };

                            $missingSheetSimper =
                                $service->isMissingSimper(
                                    $row['expired_sim_dlt_sheet']
                                    ?? ''
                                );

                            $dialogId =
                                'mfu-dialog-' .
                                $row['sheet_row'];
                        @endphp

                        <tr>
                            <td>
                                <span class="mfu-badge">
                                    {{ $row['sheet_row'] }}
                                </span>
                            </td>

                            <td class="mfu-name">
                                <strong>
                                    {{ $row['nama'] ?: '-' }}
                                </strong>
                                <span>
                                    {{ $row['nrp'] ?: '-' }}
                                </span>
                            </td>

                            <td>
                                {{ $row['jabatan'] ?: '-' }}
                            </td>

                            <td>
                                <div class="mfu-expiry">
                                    <strong>
                                        {{ $mcuExpiry['date'] ?: '-' }}
                                    </strong>

                                    <span class="mfu-status {{ $mcuClass }}">
                                        {{ $mcuExpiry['label'] }}
                                    </span>
                                </div>
                            </td>

                            <td>
                                <div class="mfu-expiry">
                                    <strong>
                                        {{ $simperExpiry['date'] ?: '-' }}
                                    </strong>

                                    <span class="mfu-status {{ $simperClass }}">
                                        {{ $simperExpiry['label'] }}
                                    </span>

                                    <span class="mfu-badge">
                                        {{ $row['expired_sim_dlt_source'] ?? '-' }}
                                    </span>
                                </div>
                            </td>

                            <td>
                                <span class="mfu-badge">
                                    {{ $row['hasil_mcu'] ?: '-' }}
                                </span>
                            </td>

                            <td>
                                <span class="mfu-badge">
                                    {{ $row['status_mcu'] ?: '-' }}
                                </span>
                            </td>

                            <td>
                                {{ collect([
                                    $row['follow_up_1'],
                                    $row['follow_up_2'],
                                    $row['follow_up_3'],
                                ])->filter()->implode(' / ') ?: '-' }}
                            </td>

                            <td>
                                <span class="mfu-badge">
                                    {{ $row['status_fu'] ?: '-' }}
                                </span>
                            </td>

                            <td>
                                <button
                                    type="button"
                                    class="mfu-edit-btn"
                                    onclick="document.getElementById('{{ $dialogId }}').showModal()"
                                >
                                    EDIT DATA
                                </button>

                                <dialog
                                    id="{{ $dialogId }}"
                                    class="mfu-dialog"
                                >
                                    <form
                                        method="POST"
                                        action="{{ route('admin-all.mcu-fu.update.save', $row['sheet_row']) }}"
                                        class="mfu-dialog-shell"
                                    >
                                        @csrf
                                        @method('PUT')

                                        <input type="hidden" name="_return_date_type" value="{{ request('date_type') }}">
                                        <input type="hidden" name="_return_year" value="{{ request('year') }}">
                                        <input type="hidden" name="_return_month" value="{{ request('month') }}">
                                        <input type="hidden" name="_return_simper_exp" value="{{ request('simper_exp') }}">
                                        <input type="hidden" name="_return_q" value="{{ request('q') }}">
                                        <input type="hidden" name="_return_hasil_mcu" value="{{ request('hasil_mcu') }}">
                                        <input type="hidden" name="_return_status_mcu" value="{{ request('status_mcu') }}">
                                        <input type="hidden" name="_return_status_fu" value="{{ request('status_fu') }}">
                                        <input type="hidden" name="_return_jabatan" value="{{ request('jabatan') }}">
                                        <input type="hidden" name="_return_fu_stage" value="{{ request('fu_stage') }}">
                                        <input type="hidden" name="_return_follow_up_value" value="{{ request('follow_up_value') }}">
                                        <input type="hidden" name="_return_page" value="{{ request('page') }}">
                                        <input type="hidden" name="_return_per_page" value="{{ $perPage ?? 20 }}">

                                        <div class="mfu-dialog-head">
                                            <div>
                                                <h2>
                                                    Update MCU &amp; Follow Up
                                                </h2>

                                                <div class="mfu-dialog-person">
                                                    <strong>
                                                        {{ $row['nama'] ?: '-' }}
                                                    </strong>

                                                    <span>•</span>

                                                    <span>
                                                        NRP {{ $row['nrp'] ?: '-' }}
                                                    </span>

                                                    <span>•</span>

                                                    <span>
                                                        Row {{ $row['sheet_row'] }}
                                                    </span>
                                                </div>
                                            </div>

                                            <button
                                                type="button"
                                                class="mfu-close"
                                                onclick="document.getElementById('{{ $dialogId }}').close()"
                                            >
                                                ×
                                            </button>
                                        </div>

                                        <div class="mfu-dialog-body">

                                            <div class="mfu-identity">
                                                <div class="mfu-info-box">
                                                    <span>Nama</span>
                                                    <strong>
                                                        {{ $row['nama'] ?: '-' }}
                                                    </strong>
                                                </div>

                                                <div class="mfu-info-box">
                                                    <span>NRP</span>
                                                    <strong>
                                                        {{ $row['nrp'] ?: '-' }}
                                                    </strong>
                                                </div>

                                                <div class="mfu-info-box">
                                                    <span>Jabatan</span>
                                                    <strong>
                                                        {{ $row['jabatan'] ?: '-' }}
                                                    </strong>
                                                </div>
                                            </div>

                                            <div class="mfu-validity">
                                                <div class="mfu-info-box">
                                                    <span>EXP MCU</span>
                                                    <strong>
                                                        {{ $mcuExpiry['date'] ?: 'Belum ada data' }}
                                                    </strong>
                                                    <div style="margin-top:5px;">
                                                        <span class="mfu-status {{ $mcuClass }}">
                                                            {{ $mcuExpiry['label'] }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="mfu-info-box">
                                                    <span>EXP SIM/SIB DLT</span>
                                                    <strong>
                                                        {{ $simperExpiry['date'] ?: 'Belum ada data' }}
                                                    </strong>
                                                    <div style="display:flex;gap:5px;flex-wrap:wrap;margin-top:5px;">
                                                        <span class="mfu-status {{ $simperClass }}">
                                                            {{ $simperExpiry['label'] }}
                                                        </span>
                                                        <span class="mfu-badge">
                                                            {{ $row['expired_sim_dlt_source'] ?? '-' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mfu-section">
                                                <div class="mfu-section-title mcu">
                                                    MCU
                                                </div>

                                                <div class="mfu-form-grid">
                                                    <div class="mfu-form-field">
                                                        <label>EXP MCU</label>
                                                        <input
                                                            type="date"
                                                            name="exp_mcu"
                                                            value="{{ $service->htmlDate($row['exp_mcu']) }}"
                                                        >
                                                    </div>

                                                    <div class="mfu-form-field">
                                                        <label>JADWAL MCU</label>
                                                        <input
                                                            type="date"
                                                            name="jadwal_mcu"
                                                            value="{{ $service->htmlDate($row['jadwal_mcu']) }}"
                                                        >
                                                    </div>

                                                    <div class="mfu-form-field">
                                                        <label>HASIL MCU</label>
                                                        <select name="hasil_mcu">
                                                            <option value="">
                                                                -- HASIL MCU --
                                                            </option>

                                                            @if (
                                                                $row['hasil_mcu'] &&
                                                                !in_array(
                                                                    $row['hasil_mcu'],
                                                                    $options['hasil_mcu'],
                                                                    true
                                                                )
                                                            )
                                                                <option
                                                                    value="{{ $row['hasil_mcu'] }}"
                                                                    selected
                                                                >
                                                                    {{ $row['hasil_mcu'] }}
                                                                </option>
                                                            @endif

                                                            @foreach ($options['hasil_mcu'] as $option)
                                                                <option
                                                                    value="{{ $option }}"
                                                                    @selected($row['hasil_mcu'] === $option)
                                                                >
                                                                    {{ $option }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="mfu-form-field">
                                                        <label>STATUS MCU</label>

                                                        <div class="mfu-readonly">
                                                            <span
                                                                class="mfu-status-mcu {{ strtoupper(trim((string) $row['status_mcu'])) === 'DONE' ? 'done' : 'not-yet' }}"
                                                            >
                                                                {{ $row['status_mcu'] ?: '-' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mfu-section">
                                                <div class="mfu-section-title fu">
                                                    FOLLOW UP
                                                </div>

                                                <div class="mfu-form-grid five">
                                                    @foreach (
                                                        [
                                                            'follow_up_1' => 'FOLLOW UP 1',
                                                            'follow_up_2' => 'FOLLOW UP 2',
                                                            'follow_up_3' => 'FOLLOW UP 3',
                                                        ] as $field => $label
                                                    )
                                                        <div class="mfu-form-field">
                                                            <label>{{ $label }}</label>

                                                            <select name="{{ $field }}">
                                                                <option value="">
                                                                    -- {{ $label }} --
                                                                </option>

                                                                @if (
                                                                    $row[$field] &&
                                                                    !in_array(
                                                                        $row[$field],
                                                                        $options['follow_up'],
                                                                        true
                                                                    )
                                                                )
                                                                    <option
                                                                        value="{{ $row[$field] }}"
                                                                        selected
                                                                    >
                                                                        {{ $row[$field] }}
                                                                    </option>
                                                                @endif

                                                                @foreach ($options['follow_up'] as $option)
                                                                    <option
                                                                        value="{{ $option }}"
                                                                        @selected($row[$field] === $option)
                                                                    >
                                                                        {{ $option }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    @endforeach

                                                    <div class="mfu-form-field">
                                                        <label>JADWAL FU</label>
                                                        <input
                                                            type="date"
                                                            name="jadwal_fu"
                                                            value="{{ $service->htmlDate($row['jadwal_fu']) }}"
                                                        >
                                                    </div>

                                                    <div class="mfu-form-field">
                                                        <label>STATUS FU</label>

                                                        <select name="status_fu">
                                                            <option value="">
                                                                -- STATUS FU --
                                                            </option>

                                                            @if (
                                                                $row['status_fu'] &&
                                                                !in_array(
                                                                    $row['status_fu'],
                                                                    $options['status_fu'],
                                                                    true
                                                                )
                                                            )
                                                                <option
                                                                    value="{{ $row['status_fu'] }}"
                                                                    selected
                                                                >
                                                                    {{ $row['status_fu'] }}
                                                                </option>
                                                            @endif

                                                            @foreach ($options['status_fu'] as $option)
                                                                <option
                                                                    value="{{ $option }}"
                                                                    @selected($row['status_fu'] === $option)
                                                                >
                                                                    {{ $option }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mfu-section">
                                                <div class="mfu-section-title simper">
                                                    EXP SIM / SIB DLT
                                                </div>

                                                @if ($missingSheetSimper)
                                                    <div class="mfu-form-grid">
                                                        <div class="mfu-form-field">
                                                            <label>
                                                                EXP SIM/SIB DLT MANUAL
                                                            </label>

                                                            <input
                                                                type="date"
                                                                name="manual_expired_sim_dlt"
                                                                value="{{ $service->htmlDate($row['expired_sim_dlt_manual'] ?? '') }}"
                                                            >
                                                        </div>

                                                        <div
                                                            class="mfu-form-field"
                                                            style="grid-column: span 2;"
                                                        >
                                                            <label>
                                                                Catatan Manual
                                                            </label>

                                                            <input
                                                                type="text"
                                                                name="manual_simper_note"
                                                                placeholder="Contoh: belum tersedia pada database SHE"
                                                            >
                                                        </div>
                                                    </div>

                                                    <div class="mfu-simper-note">
                                                        <strong>
                                                            Data Spreadsheet belum tersedia.
                                                        </strong>
                                                        Input manual disimpan di database SYNRGYPRO sebagai fallback.
                                                        Website tidak menimpa formula kolom E MCU&amp;FU.
                                                    </div>
                                                @else
                                                    <div class="mfu-form-grid">
                                                        <div class="mfu-info-box">
                                                            <span>Data Spreadsheet</span>
                                                            <strong>
                                                                {{ $row['expired_sim_dlt_sheet'] ?: '-' }}
                                                            </strong>
                                                        </div>

                                                        <div
                                                            class="mfu-info-box"
                                                            style="grid-column: span 2;"
                                                        >
                                                            <span>Sumber</span>
                                                            <strong>
                                                                Otomatis dari Spreadsheet / Database SIM-SIB DLT
                                                            </strong>
                                                        </div>
                                                    </div>

                                                    <div class="mfu-simper-note">
                                                        EXP SIM/SIB DLT sudah tersedia dari Spreadsheet sehingga
                                                        field ini dibuat read only. Manual hanya dipakai jika sumber
                                                        Spreadsheet kosong.
                                                    </div>
                                                @endif
                                            </div>

                                        </div>

                                        <div class="mfu-dialog-footer">
                                            <span
                                                class="mfu-change-count"
                                                data-change-counter
                                            >
                                                0 PERUBAHAN
                                            </span>

                                            <button
                                                type="button"
                                                class="mfu-button"
                                                onclick="document.getElementById('{{ $dialogId }}').close()"
                                            >
                                                BATAL
                                            </button>

                                            <button
                                                type="submit"
                                                class="mfu-save"
                                                data-save-button
                                                data-employee-name="{{ $row['nama'] }}"
                                                disabled
                                            >
                                                SIMPAN UPDATE
                                            </button>
                                        </div>
                                    </form>
                                </dialog>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="10"
                                style="padding:26px;text-align:center;color:#718195;"
                            >
                                Data tidak ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mfu-pagination">
            <div
                style="
                    display:flex;
                    align-items:center;
                    justify-content:space-between;
                    gap:10px;
                "
            >
                <div>
                    Showing
                    {{ number_format($data->firstItem() ?? 0) }}
                    to
                    {{ number_format($data->lastItem() ?? 0) }}
                    of
                    {{ number_format($data->total()) }}
                    results
                </div>

                <div
                    style="
                        display:flex;
                        align-items:center;
                        gap:6px;
                    "
                >
                    @if ($data->onFirstPage())
                        <span
                            class="mfu-button"
                            style="opacity:.45;pointer-events:none;"
                        >
                            ‹ PREVIOUS
                        </span>
                    @else
                        <a
                            href="{{ $data->previousPageUrl() }}"
                            class="mfu-button"
                        >
                            ‹ PREVIOUS
                        </a>
                    @endif

                    <span class="mfu-badge">
                        PAGE {{ $data->currentPage() }}
                        / {{ max(1, $data->lastPage()) }}
                    </span>

                    @if ($data->hasMorePages())
                        <a
                            href="{{ $data->nextPageUrl() }}"
                            class="mfu-button"
                        >
                            NEXT ›
                        </a>
                    @else
                        <span
                            class="mfu-button"
                            style="opacity:.45;pointer-events:none;"
                        >
                            NEXT ›
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.mfu-dialog form').forEach(function (form) {
        const saveButton = form.querySelector('[data-save-button]');
        const counter = form.querySelector('[data-change-counter]');

        if (!saveButton || !counter) {
            return;
        }

        const tracked = Array.from(
            form.querySelectorAll(
                'input[name]:not([type="hidden"]), select[name], textarea[name]'
            )
        );

        tracked.forEach(function (field) {
            field.dataset.initialValue = field.value ?? '';
        });

        const refreshChanges = function () {
            const changed = tracked.filter(function (field) {
                return (field.value ?? '') !== (field.dataset.initialValue ?? '');
            });

            const count = changed.length;

            saveButton.disabled = count === 0;
            counter.textContent = count + ' PERUBAHAN';
            counter.classList.toggle('show', count > 0);

            saveButton.textContent = count > 0
                ? 'SIMPAN ' + count + ' PERUBAHAN'
                : 'SIMPAN UPDATE';

            return changed;
        };

        tracked.forEach(function (field) {
            field.addEventListener('input', refreshChanges);
            field.addEventListener('change', refreshChanges);
        });

        form.addEventListener('submit', function (event) {
            const changed = refreshChanges();

            if (changed.length === 0) {
                event.preventDefault();
                return;
            }

            const labels = changed.map(function (field) {
                const wrapper = field.closest('.mfu-form-field');
                const label = wrapper?.querySelector('label');

                return label
                    ? label.textContent.trim()
                    : field.name;
            });

            const employee = saveButton.dataset.employeeName || 'karyawan';

            const message =
                'Simpan ' +
                changed.length +
                ' perubahan untuk ' +
                employee +
                '?\n\n' +
                labels.join(', ');

            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });

        refreshChanges();
    });

    const autoSyncIntervalMs = 120 * 1000;
    const status = document.querySelector('[data-auto-sync-status]');
    const statusLabel = document.querySelector('[data-auto-sync-label]');
    let lastAutoSyncAt = Date.now();

    const hasOpenDialog = function () {
        return !!document.querySelector('.mfu-dialog[open]');
    };

    const hasUnsavedChanges = function () {
        return Array.from(
            document.querySelectorAll('[data-save-button]')
        ).some(function (button) {
            return !button.disabled;
        });
    };

    const userIsInteracting = function () {
        const active = document.activeElement;

        return !!active && active.matches(
            'input, select, textarea'
        );
    };

    const refreshAutoSyncBadge = function () {
        const paused =
            hasOpenDialog() ||
            hasUnsavedChanges();

        if (!status || !statusLabel) {
            return;
        }

        status.classList.toggle(
            'paused',
            paused
        );

        statusLabel.textContent = paused
            ? 'AUTO SYNC PAUSED'
            : 'AUTO SYNC 120s';
    };

    const safeAutoSync = function () {
        refreshAutoSyncBadge();

        if (
            document.hidden ||
            hasOpenDialog() ||
            hasUnsavedChanges() ||
            userIsInteracting()
        ) {
            return;
        }

        lastAutoSyncAt = Date.now();
        window.location.reload();
    };

    window.setInterval(
        safeAutoSync,
        autoSyncIntervalMs
    );

    document.addEventListener(
        'visibilitychange',
        function () {
            refreshAutoSyncBadge();

            if (
                !document.hidden &&
                (Date.now() - lastAutoSyncAt) >= autoSyncIntervalMs
            ) {
                window.setTimeout(
                    safeAutoSync,
                    350
                );
            }
        }
    );

    document.addEventListener(
        'input',
        refreshAutoSyncBadge
    );

    document.addEventListener(
        'change',
        refreshAutoSyncBadge
    );

    document.querySelectorAll('.mfu-edit-btn').forEach(function (button) {
        button.addEventListener(
            'click',
            function () {
                window.setTimeout(
                    refreshAutoSyncBadge,
                    50
                );
            }
        );
    });

    document.querySelectorAll('.mfu-dialog').forEach(function (dialog) {
        dialog.addEventListener(
            'close',
            refreshAutoSyncBadge
        );
    });

    refreshAutoSyncBadge();
});
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const lookupUrl = @json(
        route('admin-all.mcu-fu.employee.lookup')
    );

    const addDialog =
        document.getElementById(
            'employeeAddDialog'
        );

    const lifecycleDialog =
        document.getElementById(
            'employeeLifecycleDialog'
        );

    const openDialog = function (dialog) {
        if (!dialog) {
            return;
        }

        if (typeof dialog.showModal === 'function') {
            dialog.showModal();
        }
    };

    document
        .querySelector('[data-open-employee-add]')
        ?.addEventListener(
            'click',
            function () {
                openDialog(addDialog);
            }
        );

    document
        .querySelector('[data-open-employee-lifecycle]')
        ?.addEventListener(
            'click',
            function () {
                openDialog(lifecycleDialog);
            }
        );

    document
        .querySelectorAll('[data-close-dialog]')
        .forEach(function (button) {
            button.addEventListener(
                'click',
                function () {
                    button
                        .closest('dialog')
                        ?.close();
                }
            );
        });

    const lookup = async function (nrp) {
        const url = new URL(
            lookupUrl,
            window.location.origin
        );

        url.searchParams.set(
            'nrp',
            nrp
        );

        const response = await fetch(
            url.toString(),
            {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With':
                        'XMLHttpRequest',
                },
            }
        );

        const payload =
            await response.json();

        if (
            !response.ok
            || payload.ok === false
        ) {
            throw new Error(
                payload.message
                || 'Lookup NRP gagal.'
            );
        }

        return payload;
    };

    /*
    |--------------------------------------------------------------------------
    | Tambah Karyawan
    |--------------------------------------------------------------------------
    */

    const addNrp =
        document.getElementById(
            'employeeAddNrp'
        );

    const addResult =
        document.querySelector(
            '[data-add-lookup-result]'
        );

    const addFound =
        document.querySelector(
            '[data-add-found]'
        );

    const addManual =
        document.querySelector(
            '[data-add-manual]'
        );

    const addSubmit =
        document.querySelector(
            '[data-add-submit]'
        );

    const resetAddState = function () {
        if (addFound) {
            addFound.hidden = true;
        }

        if (addManual) {
            addManual.hidden = true;
        }

        if (addSubmit) {
            addSubmit.disabled = true;
        }

        addResult?.classList.remove(
            'found',
            'missing',
            'error'
        );
    };

    addNrp?.addEventListener(
        'input',
        resetAddState
    );

    document
        .querySelector('[data-lookup-add]')
        ?.addEventListener(
            'click',
            async function () {
                resetAddState();

                const nrp =
                    addNrp?.value.trim()
                    || '';

                if (nrp === '') {
                    if (addResult) {
                        addResult.textContent =
                            'NRP wajib diisi.';
                        addResult.classList.add(
                            'error'
                        );
                    }

                    return;
                }

                if (addResult) {
                    addResult.textContent =
                        'Mengecek MASTER_DATABASE...';
                }

                try {
                    const payload =
                        await lookup(nrp);

                    if (payload.found) {
                        const employee =
                            payload.employee
                            || {};

                        if (addResult) {
                            addResult.textContent =
                                'NRP ditemukan di MASTER_DATABASE. Data tidak boleh diduplikasi.';
                            addResult.classList.add(
                                'found'
                            );
                        }

                        if (addFound) {
                            addFound.hidden =
                                false;
                        }

                        document.querySelector(
                            '[data-add-found-name]'
                        ).textContent =
                            employee.nama || '-';

                        document.querySelector(
                            '[data-add-found-position]'
                        ).textContent =
                            employee.jabatan || '-';

                        document.querySelector(
                            '[data-add-found-site]'
                        ).textContent =
                            employee.site || '-';

                        document.querySelector(
                            '[data-add-found-status]'
                        ).textContent =
                            employee.status_karyawan
                            || '-';

                        return;
                    }

                    if (addResult) {
                        addResult.textContent =
                            'NRP belum ada di MASTER_DATABASE. Form input manual dibuka.';
                        addResult.classList.add(
                            'missing'
                        );
                    }

                    if (addManual) {
                        addManual.hidden = false;
                    }

                    if (addSubmit) {
                        addSubmit.disabled = false;
                    }
                } catch (error) {
                    if (addResult) {
                        addResult.textContent =
                            error.message;
                        addResult.classList.add(
                            'error'
                        );
                    }
                }
            }
        );

    /*
    |--------------------------------------------------------------------------
    | Status / Mutasi
    |--------------------------------------------------------------------------
    */

    const lifeNrp =
        document.getElementById(
            'employeeLifecycleNrp'
        );

    const lifeResult =
        document.querySelector(
            '[data-lifecycle-lookup-result]'
        );

    const lifeFound =
        document.querySelector(
            '[data-lifecycle-found]'
        );

    const lifeFields =
        document.querySelector(
            '[data-lifecycle-fields]'
        );

    const lifeSubmit =
        document.querySelector(
            '[data-lifecycle-submit]'
        );

    const lifeStatus =
        document.getElementById(
            'employeeLifecycleStatus'
        );

    const lifeSite =
        document.getElementById(
            'employeeLifecycleSite'
        );

    const resetLifeState = function () {
        if (lifeFound) {
            lifeFound.hidden = true;
        }

        if (lifeFields) {
            lifeFields.hidden = true;
        }

        if (lifeSubmit) {
            lifeSubmit.disabled = true;
        }

        lifeResult?.classList.remove(
            'found',
            'missing',
            'error'
        );
    };

    lifeNrp?.addEventListener(
        'input',
        resetLifeState
    );

    document
        .querySelector(
            '[data-lookup-lifecycle]'
        )
        ?.addEventListener(
            'click',
            async function () {
                resetLifeState();

                const nrp =
                    lifeNrp?.value.trim()
                    || '';

                if (nrp === '') {
                    if (lifeResult) {
                        lifeResult.textContent =
                            'NRP wajib diisi.';
                        lifeResult.classList.add(
                            'error'
                        );
                    }

                    return;
                }

                if (lifeResult) {
                    lifeResult.textContent =
                        'Membaca MASTER_DATABASE...';
                }

                try {
                    const payload =
                        await lookup(nrp);

                    if (!payload.found) {
                        if (lifeResult) {
                            lifeResult.textContent =
                                'NRP belum terdaftar. Gunakan + TAMBAH KARYAWAN terlebih dahulu.';
                            lifeResult.classList.add(
                                'missing'
                            );
                        }

                        return;
                    }

                    const employee =
                        payload.employee
                        || {};

                    if (lifeResult) {
                        lifeResult.textContent =
                            'NRP ditemukan. Identitas diambil otomatis dari MASTER_DATABASE.';
                        lifeResult.classList.add(
                            'found'
                        );
                    }

                    if (lifeFound) {
                        lifeFound.hidden = false;
                    }

                    if (lifeFields) {
                        lifeFields.hidden = false;
                    }

                    if (lifeSubmit) {
                        lifeSubmit.disabled = false;
                    }

                    document.querySelector(
                        '[data-life-name]'
                    ).textContent =
                        employee.nama || '-';

                    document.querySelector(
                        '[data-life-position]'
                    ).textContent =
                        employee.jabatan || '-';

                    document.querySelector(
                        '[data-life-site]'
                    ).textContent =
                        employee.site || '-';

                    document.querySelector(
                        '[data-life-status]'
                    ).textContent =
                        employee.status_karyawan
                        || '-';

                    if (lifeSite) {
                        lifeSite.value =
                            employee.site === '-'
                                ? ''
                                : (
                                    employee.site
                                    || ''
                                );
                    }
                } catch (error) {
                    if (lifeResult) {
                        lifeResult.textContent =
                            error.message;
                        lifeResult.classList.add(
                            'error'
                        );
                    }
                }
            }
        );

    lifeStatus?.addEventListener(
        'change',
        function () {
            const mutation =
                lifeStatus.value ===
                'MUTASI';

            if (lifeSite) {
                lifeSite.required =
                    mutation;

                lifeSite.placeholder =
                    mutation
                        ? 'Wajib isi site tujuan, contoh BIB'
                        : 'Kosongkan jika tidak berubah';
            }
        }
    );
});
</script>

@endsection