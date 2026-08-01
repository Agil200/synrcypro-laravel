@php
    $labelBulan = \Carbon\Carbon::createFromFormat('Y-m', $bulan)
        ->locale('id')
        ->translatedFormat('F Y');

    $statusOrder = [
        'SHE',
        'WAREHOUSE',
        'LOGISTIK',
        'READY',
        'DIAMBIL',
    ];
@endphp

<style>
    .apd-page {
        --apd-red: #d71920;
        --apd-red-dark: #b31319;
        --apd-navy: #172033;
        --apd-muted: #6a7485;
        --apd-border: #dce2e8;
        --apd-purple: #5146e5;
        display: grid;
        gap: 15px;
        min-width: 0;
    }

    .apd-card {
        overflow: hidden;
        border: 1px solid var(--apd-border);
        border-radius: 13px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .055);
    }

    .apd-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 15px;
        padding: 18px 20px;
        border-bottom: 1px solid var(--apd-border);
    }

    .apd-title {
        margin: 0 0 5px;
        color: var(--apd-navy);
        font-size: 21px;
        font-weight: 900;
    }

    .apd-subtitle {
        margin: 0;
        color: var(--apd-muted);
        font-size: 12px;
        line-height: 1.5;
    }

    .apd-header-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: flex-end;
    }

    .apd-primary,
    .apd-secondary,
    .apd-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-family: inherit;
        font-weight: 800;
        cursor: pointer;
        text-decoration: none;
    }

    .apd-primary {
        min-height: 40px;
        gap: 6px;
        padding: 0 14px;
        border: 0;
        color: #ffffff;
        background: var(--apd-red);
        font-size: 12px;
    }

    .apd-primary.purple {
        background: var(--apd-purple);
    }

    .apd-primary:hover {
        background: var(--apd-red-dark);
    }

    .apd-primary.purple:hover {
        background: #4136c9;
    }

    .apd-alert {
        margin: 14px 20px 0;
        padding: 12px 14px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
    }

    .apd-alert.success {
        border: 1px solid #b7dfc5;
        color: #166534;
        background: #ecfdf3;
    }

    .apd-alert.error {
        border: 1px solid #f0b4b7;
        color: #991b1b;
        background: #fff1f2;
    }

    .apd-alert ul {
        margin: 7px 0 0;
        padding-left: 18px;
    }

    .apd-toolbar {
        display: grid;
        grid-template-columns:
            minmax(185px, 240px)
            minmax(220px, 1fr)
            minmax(160px, 200px)
            repeat(4, minmax(105px, 135px));
        gap: 10px;
        align-items: end;
        padding: 15px 20px;
    }

    .apd-field {
        display: grid;
        gap: 6px;
    }

    .apd-field label {
        color: #344054;
        font-size: 11px;
        font-weight: 900;
    }

    .apd-input,
    .apd-select,
    .apd-textarea {
        width: 100%;
        min-height: 39px;
        padding: 8px 10px;
        border: 1px solid #cfd6de;
        border-radius: 8px;
        outline: none;
        color: var(--apd-navy);
        background: #ffffff;
        font-family: inherit;
        font-size: 12px;
    }

    .apd-textarea {
        min-height: 80px;
        resize: vertical;
    }

    .apd-input:focus,
    .apd-select:focus,
    .apd-textarea:focus {
        border-color: var(--apd-purple);
        box-shadow: 0 0 0 3px rgba(81, 70, 229, .10);
    }

    .apd-stat {
        min-height: 66px;
        padding: 10px 12px;
        border: 1px solid var(--apd-border);
        border-radius: 9px;
        background: #f8fafc;
    }

    .apd-stat span {
        display: block;
        margin-bottom: 4px;
        color: var(--apd-muted);
        font-size: 9px;
        font-weight: 800;
    }

    .apd-stat strong {
        color: var(--apd-navy);
        font-size: 20px;
        font-weight: 900;
    }

    .apd-table-wrap {
        overflow-x: auto;
        padding: 0 20px 16px;
    }

    .apd-table {
        width: 100%;
        min-width: 1280px;
        border-collapse: separate;
        border-spacing: 0;
        color: #293244;
        font-size: 11px;
    }

    .apd-table th,
    .apd-table td {
        padding: 10px 9px;
        border-bottom: 1px solid #e5e9ee;
        text-align: left;
        vertical-align: middle;
    }

    .apd-table th {
        background: #f5f7f9;
        color: #42506a;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .035em;
        white-space: nowrap;
    }

    .apd-table tbody tr:hover {
        background: #fafbfc;
    }

    .apd-items {
        display: flex;
        max-width: 260px;
        flex-wrap: wrap;
        gap: 4px;
    }

    .apd-chip {
        display: inline-flex;
        min-height: 21px;
        align-items: center;
        padding: 0 7px;
        border-radius: 999px;
        color: #475467;
        background: #eef2f6;
        font-size: 9px;
        font-weight: 800;
        white-space: nowrap;
    }

    .apd-chip.shoe {
        color: #3730a3;
        background: #e8e7ff;
    }

    .apd-progress {
        display: flex;
        min-width: 315px;
        align-items: center;
        gap: 3px;
    }

    .apd-progress-step {
        display: grid;
        min-width: 56px;
        min-height: 27px;
        place-items: center;
        padding: 0 6px;
        border: 1px solid #d9dee5;
        border-radius: 6px;
        color: #8a93a2;
        background: #ffffff;
        font-size: 8px;
        font-weight: 900;
    }

    .apd-progress-step.done {
        border-color: #a9d8bd;
        color: #146c43;
        background: #ebf9f1;
    }

    .apd-progress-step.current {
        border-color: #5146e5;
        color: #3730a3;
        background: #eeedff;
        box-shadow: inset 0 0 0 1px #5146e5;
    }

    .apd-status-select {
        min-width: 110px;
        height: 31px;
        padding: 0 7px;
        border: 1px solid #cfd6de;
        border-radius: 6px;
        background: #ffffff;
        font-size: 10px;
        font-weight: 800;
    }

    .apd-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .apd-action {
        min-height: 30px;
        padding: 0 9px;
        border: 1px solid #d4dae1;
        color: #344054;
        background: #ffffff;
        font-size: 10px;
    }

    .apd-action.danger {
        border-color: #f0b4b7;
        color: #b91c1c;
        background: #fff5f5;
    }

    .apd-empty {
        padding: 36px 20px !important;
        color: var(--apd-muted);
        text-align: center !important;
    }

    .apd-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 20px 15px;
        color: var(--apd-muted);
        font-size: 11px;
    }

    .apd-pagination {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .apd-page-link {
        display: inline-flex;
        min-width: 32px;
        height: 32px;
        align-items: center;
        justify-content: center;
        border: 1px solid #d8dee5;
        border-radius: 7px;
        color: #344054;
        background: #ffffff;
        font-weight: 900;
        text-decoration: none;
    }

    .apd-page-link.disabled {
        opacity: .4;
        pointer-events: none;
    }

    .apd-ready-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        padding: 15px 20px 18px;
    }

    .apd-ready-item {
        padding: 12px;
        border: 1px solid #deddfb;
        border-radius: 10px;
        background: #f7f7ff;
    }

    .apd-ready-item strong {
        display: block;
        margin-bottom: 5px;
        color: #2f2a82;
        font-size: 11px;
    }

    .apd-ready-item span {
        color: #687386;
        font-size: 9px;
        line-height: 1.45;
    }

    /* Modal */
    .apd-modal[hidden] {
        display: none !important;
    }

    .apd-modal {
        position: fixed !important;
        z-index: 99999 !important;
        inset: 0 !important;
        display: none !important;
        width: 100vw !important;
        height: 100vh !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 16px !important;
        overflow: hidden !important;
        background: rgba(17, 24, 39, .66) !important;
    }

    .apd-modal.is-open:not([hidden]) {
        display: flex !important;
    }

    .apd-dialog {
        display: flex;
        width: min(840px, calc(100vw - 32px));
        max-height: calc(100vh - 32px);
        flex-direction: column;
        overflow: hidden;
        border-radius: 13px;
        background: #ffffff;
        box-shadow: 0 28px 80px rgba(0, 0, 0, .30);
    }

    .apd-dialog.pickup {
        width: min(680px, calc(100vw - 32px));
    }

    .apd-dialog form {
        display: flex;
        min-height: 0;
        flex: 1;
        flex-direction: column;
        overflow: hidden;
    }

    .apd-modal-header {
        display: flex;
        flex: 0 0 auto;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        min-height: 60px;
        padding: 0 18px;
        border-bottom: 1px solid var(--apd-border);
        background: #ffffff;
    }

    .apd-modal-header h2 {
        margin: 0;
        color: var(--apd-navy);
        font-size: 18px;
        font-weight: 900;
    }

    .apd-close {
        display: grid;
        width: 34px;
        height: 34px;
        place-items: center;
        border: 0;
        border-radius: 50%;
        color: #4b5563;
        background: #eef1f4;
        font-size: 20px;
        cursor: pointer;
    }

    .apd-modal-body {
        min-height: 0;
        padding: 18px;
        overflow-y: auto;
    }

    .apd-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 13px;
    }

    .apd-full {
        grid-column: 1 / -1;
    }

    .apd-modal-footer {
        display: flex;
        flex: 0 0 auto;
        justify-content: flex-end;
        gap: 8px;
        padding: 13px 18px;
        border-top: 1px solid var(--apd-border);
        background: #fafbfc;
    }

    .apd-secondary {
        min-height: 39px;
        padding: 0 14px;
        border: 1px solid #d4dae1;
        color: #344054;
        background: #ffffff;
        font-size: 11px;
    }

    .apd-check-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 8px;
    }

    .apd-check-option,
    .apd-status-option {
        position: relative;
        display: block;
    }

    .apd-check-option input,
    .apd-status-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .apd-check-box,
    .apd-status-box {
        display: grid;
        min-height: 58px;
        place-items: center;
        padding: 8px;
        border: 1px solid #d5dbe3;
        border-radius: 9px;
        color: #596476;
        background: #ffffff;
        font-size: 10px;
        font-weight: 900;
        text-align: center;
        cursor: pointer;
    }

    .apd-check-option input:checked + .apd-check-box,
    .apd-status-option input:checked + .apd-status-box {
        border-color: #5146e5;
        color: #3730a3;
        background: #eeedff;
        box-shadow: inset 0 0 0 1px #5146e5;
    }

    .apd-check-option input:checked + .apd-check-box::before,
    .apd-status-option input:checked + .apd-status-box::before {
        margin-bottom: 2px;
        content: "✓";
        font-size: 14px;
    }

    .apd-status-options {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px;
    }

    /* Pickup reference card */
    .apd-pickup-brand {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 18px;
        padding-bottom: 16px;
        border-bottom: 1px solid #e6e9ee;
    }

    .apd-pickup-brand-mark {
        display: grid;
        width: 42px;
        height: 42px;
        place-items: center;
        border-radius: 50%;
        color: #ffffff;
        background: #d71920;
        font-size: 16px;
        font-weight: 900;
    }

    .apd-pickup-brand h3 {
        margin: 0;
        color: #283244;
        font-size: 16px;
        font-weight: 900;
    }

    .apd-pickup-brand p {
        margin: 3px 0 0;
        color: #7b8492;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .08em;
    }

    .apd-ready-select-wrap {
        padding: 12px;
        border-radius: 11px;
        background: #eef0ff;
    }

    .apd-ready-select-wrap label {
        display: block;
        margin-bottom: 7px;
        color: #5146e5;
        font-size: 10px;
        font-weight: 900;
    }

    .apd-ready-detail {
        display: none;
        margin-top: 9px;
        padding: 10px;
        border-radius: 8px;
        color: #ffffff;
        background: #777777;
        font-size: 10px;
        font-weight: 700;
    }

    .apd-ready-detail.show {
        display: block;
    }

    .apd-capture-title {
        margin: 17px 0 9px;
        color: #7b8492;
        font-size: 9px;
        font-weight: 900;
        text-align: center;
    }

    .apd-capture-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .apd-capture-button {
        display: grid;
        min-height: 88px;
        place-items: center;
        gap: 5px;
        padding: 10px;
        border: 1px dashed #aaa7ff;
        border-radius: 13px;
        color: #5146e5;
        background: #f6f6ff;
        font-family: inherit;
        font-size: 10px;
        font-weight: 900;
        cursor: pointer;
        text-align: center;
    }

    .apd-capture-button span:first-child {
        font-size: 24px;
    }

    .apd-photo-input {
        position: absolute;
        width: 1px;
        height: 1px;
        overflow: hidden;
        opacity: 0;
    }

    .apd-photo-preview {
        display: none;
        width: 100%;
        max-height: 230px;
        margin-top: 12px;
        border-radius: 10px;
        object-fit: contain;
        background: #edf0f5;
    }

    .apd-photo-preview.show {
        display: block;
    }

    .apd-camera-panel {
        display: none;
        margin-top: 12px;
        padding: 10px;
        border: 1px solid #dce2e8;
        border-radius: 10px;
        background: #111827;
    }

    .apd-camera-panel.show {
        display: block;
    }

    .apd-camera-panel video {
        display: block;
        width: 100%;
        max-height: 320px;
        border-radius: 8px;
        object-fit: contain;
        background: #000000;
    }

    .apd-camera-actions {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 9px;
    }

    .apd-pickup-submit {
        width: 100%;
        min-height: 44px;
        margin-top: 16px;
        border: 0;
        border-radius: 10px;
        color: #ffffff;
        background: linear-gradient(
            90deg,
            #5146e5,
            #382eb8
        );
        font-family: inherit;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
        box-shadow: 0 8px 18px rgba(81, 70, 229, .22);
    }

    body.apd-modal-open {
        overflow: hidden !important;
    }

    @media (max-width: 1280px) {
        .apd-toolbar {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .apd-ready-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 720px) {
        .apd-header,
        .apd-footer {
            align-items: stretch;
            flex-direction: column;
        }

        .apd-header-actions,
        .apd-primary {
            width: 100%;
        }

        .apd-toolbar,
        .apd-form-grid,
        .apd-ready-grid {
            grid-template-columns: 1fr;
        }

        .apd-full {
            grid-column: auto;
        }

        .apd-check-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .apd-status-options {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .apd-modal {
            padding: 0 !important;
        }

        .apd-dialog,
        .apd-dialog.pickup {
            width: 100vw;
            max-height: 100vh;
            min-height: 100vh;
            border-radius: 0;
        }
    }
</style>

<div class="apd-page">
    <section class="apd-card">
        <div class="apd-header">
            <div>
                <h1 class="apd-title">Monitoring APD</h1>
                <p class="apd-subtitle">
                    Pengajuan barang APD, pemantauan posisi Sepatu Safety,
                    dan dokumentasi pengambilan dengan kamera atau galeri.
                </p>
            </div>

            <div class="apd-header-actions">
                <button
                    type="button"
                    class="apd-primary"
                    id="openApdCreate"
                >
                    ＋ Input Pengajuan
                </button>

                <button
                    type="button"
                    class="apd-primary purple"
                    id="openApdPickup"
                >
                    📷 Pengambilan Ready
                </button>
            </div>
        </div>

        @if (session('success'))
            <div class="apd-alert success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="apd-alert error">
                Data belum dapat disimpan.
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="apd-toolbar">
            <form
                method="GET"
                action="{{ route('apd.index') }}"
                class="apd-field"
            >
                <input type="hidden" name="search" value="{{ $search }}">
                <input type="hidden" name="status" value="{{ $status }}">

                <label for="apdMonth">Pilih Bulan</label>
                <input
                    type="month"
                    name="bulan"
                    id="apdMonth"
                    class="apd-input"
                    value="{{ $bulan }}"
                    onchange="this.form.submit()"
                >
            </form>

            <form
                method="GET"
                action="{{ route('apd.index') }}"
                class="apd-field"
            >
                <input type="hidden" name="bulan" value="{{ $bulan }}">

                <label for="apdSearch">
                    Cari NRP / Nama / Jabatan
                </label>
                <input
                    type="search"
                    name="search"
                    id="apdSearch"
                    class="apd-input"
                    value="{{ $search }}"
                    placeholder="Ketik lalu Enter"
                >
            </form>

            <form
                method="GET"
                action="{{ route('apd.index') }}"
                class="apd-field"
            >
                <input type="hidden" name="bulan" value="{{ $bulan }}">
                <input type="hidden" name="search" value="{{ $search }}">

                <label for="apdStatusFilter">
                    Status Sepatu
                </label>
                <select
                    name="status"
                    id="apdStatusFilter"
                    class="apd-select"
                    onchange="this.form.submit()"
                >
                    <option value="">Semua status</option>
                    @foreach ($shoeStatuses as $shoeStatus)
                        <option
                            value="{{ $shoeStatus }}"
                            @selected($status === $shoeStatus)
                        >
                            {{ $shoeStatus }}
                        </option>
                    @endforeach
                </select>
            </form>

            <div class="apd-stat">
                <span>Pengajuan {{ $labelBulan }}</span>
                <strong>{{ number_format($stats['bulan']) }}</strong>
            </div>

            <div class="apd-stat">
                <span>Total Pengajuan</span>
                <strong>{{ number_format($stats['total']) }}</strong>
            </div>

            <div class="apd-stat">
                <span>Sepatu Ready</span>
                <strong>{{ number_format($stats['ready']) }}</strong>
            </div>

            <div class="apd-stat">
                <span>Sudah Diambil</span>
                <strong>{{ number_format($stats['diambil']) }}</strong>
            </div>
        </div>

        <div class="apd-table-wrap">
            <table class="apd-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>NRP</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>Ukuran Sepatu</th>
                        <th>Barang Diajukan</th>
                        <th>Posisi Sepatu</th>
                        <th>Update Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($records as $item)
                        @php
                            $currentStatusIndex = array_search(
                                $item->status_sepatu,
                                $statusOrder,
                                true
                            );
                        @endphp

                        <tr>
                            <td>
                                {{
                                    ($records->firstItem() ?? 1)
                                    + $loop->index
                                }}
                            </td>
                            <td>
                                {{
                                    $item->tanggal_pengajuan
                                        ?->format('d/m/Y')
                                }}
                            </td>
                            <td>{{ $item->nrp }}</td>
                            <td>{{ $item->nama }}</td>
                            <td>{{ $item->jabatan }}</td>
                            <td>{{ $item->ukuran_sepatu ?: '-' }}</td>
                            <td>
                                <div class="apd-items">
                                    @foreach ($item->items_label as $label)
                                        <span
                                            class="apd-chip
                                                {{
                                                    $label === 'Sepatu Safety'
                                                        ? 'shoe'
                                                        : ''
                                                }}"
                                        >
                                            ✓ {{ $label }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                @if ($item->item_sepatu_safety)
                                    <div class="apd-progress">
                                        @foreach ($statusOrder as $index => $step)
                                            <span
                                                class="apd-progress-step
                                                    {{
                                                        $currentStatusIndex !== false
                                                        && $index < $currentStatusIndex
                                                            ? 'done'
                                                            : ''
                                                    }}
                                                    {{
                                                        $item->status_sepatu === $step
                                                            ? 'current'
                                                            : ''
                                                    }}"
                                            >
                                                {{ $step }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if (
                                    $item->item_sepatu_safety
                                    && ! $item->pickup
                                )
                                    <form
                                        method="POST"
                                        action="{{
                                            route(
                                                'apd.status',
                                                $item
                                            )
                                        }}"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <select
                                            name="status_sepatu"
                                            class="apd-status-select"
                                            onchange="this.form.submit()"
                                        >
                                            @foreach (
                                                [
                                                    'SHE',
                                                    'WAREHOUSE',
                                                    'LOGISTIK',
                                                    'READY',
                                                ] as $step
                                            )
                                                <option
                                                    value="{{ $step }}"
                                                    @selected(
                                                        $item->status_sepatu
                                                            === $step
                                                    )
                                                >
                                                    {{ $step }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                @elseif ($item->pickup)
                                    <span class="apd-chip">
                                        ✓ DIAMBIL
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <div class="apd-actions">
                                    @if (! $item->pickup)
                                        <button
                                            type="button"
                                            class="
                                                apd-action
                                                js-edit-apd
                                            "
                                            data-id="{{ $item->id }}"
                                            data-tanggal="{{
                                                $item->tanggal_pengajuan
                                                    ?->format('Y-m-d')
                                            }}"
                                            data-nrp="{{ $item->nrp }}"
                                            data-nama="{{ $item->nama }}"
                                            data-jabatan="{{
                                                $item->jabatan
                                            }}"
                                            data-ukuran="{{
                                                $item->ukuran_sepatu
                                            }}"
                                            data-helm="{{
                                                $item->item_helm ? 1 : 0
                                            }}"
                                            data-sepatu="{{
                                                $item->item_sepatu_safety
                                                    ? 1
                                                    : 0
                                            }}"
                                            data-rompi="{{
                                                $item->item_rompi ? 1 : 0
                                            }}"
                                            data-kacamata="{{
                                                $item->item_kacamata
                                                    ? 1
                                                    : 0
                                            }}"
                                            data-earplug="{{
                                                $item->item_ear_plug
                                                    ? 1
                                                    : 0
                                            }}"
                                            data-status="{{
                                                $item->status_sepatu
                                            }}"
                                        >
                                            Edit
                                        </button>

                                        <form
                                            method="POST"
                                            action="{{
                                                route(
                                                    'apd.destroy',
                                                    $item
                                                )
                                            }}"
                                            class="js-delete-apd"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="
                                                    apd-action
                                                    danger
                                                "
                                            >
                                                Hapus
                                            </button>
                                        </form>
                                    @else
                                        <a
                                            href="{{
                                                route(
                                                    'apd.pickup.photo',
                                                    $item->pickup
                                                )
                                            }}"
                                            target="_blank"
                                            rel="noopener"
                                            class="apd-action"
                                        >
                                            Lihat Foto
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="apd-empty">
                                Belum ada pengajuan APD pada
                                {{ $labelBulan }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="apd-footer">
            <span>
                Menampilkan {{ $records->firstItem() ?? 0 }}
                sampai {{ $records->lastItem() ?? 0 }}
                dari {{ $records->total() }} data
            </span>

            @if ($records->hasPages())
                <nav class="apd-pagination">
                    <a
                        href="{{ $records->previousPageUrl() ?: '#' }}"
                        class="apd-page-link
                            {{
                                $records->onFirstPage()
                                    ? 'disabled'
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
                        class="apd-page-link
                            {{
                                $records->hasMorePages()
                                    ? ''
                                    : 'disabled'
                            }}"
                    >
                        ›
                    </a>
                </nav>
            @endif
        </div>
    </section>

    <section class="apd-card" id="ready-pickup">
        <div class="apd-header">
            <div>
                <h2 class="apd-title">
                    Sepatu Safety Ready
                </h2>
                <p class="apd-subtitle">
                    Hanya Sepatu Safety berstatus READY dan belum diambil
                    yang muncul pada antrean ini.
                </p>
            </div>

            <button
                type="button"
                class="apd-primary purple"
                data-open-apd-pickup
            >
                📷 Form Pengambilan
            </button>
        </div>

        <div class="apd-ready-grid">
            @forelse ($readyShoes as $ready)
                <div class="apd-ready-item">
                    <strong>
                        {{ $ready->nrp }} · {{ $ready->nama }}
                    </strong>
                    <span>
                        Jabatan: {{ $ready->jabatan }}<br>
                        Ukuran Sepatu: {{ $ready->ukuran_sepatu }}
                    </span>
                </div>
            @empty
                <div class="apd-empty apd-full">
                    Belum ada Sepatu Safety berstatus READY.
                </div>
            @endforelse
        </div>
    </section>

    <section class="apd-card">
        <div class="apd-header">
            <div>
                <h2 class="apd-title">
                    Riwayat Pengambilan
                </h2>
                <p class="apd-subtitle">
                    Setiap pengambilan disimpan bersama foto bukti.
                </p>
            </div>
        </div>

        <div class="apd-table-wrap">
            <table class="apd-table" style="min-width: 900px;">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>NRP</th>
                        <th>Nama</th>
                        <th>Ukuran</th>
                        <th>Diambil Oleh</th>
                        <th>Petugas</th>
                        <th>Bukti Foto</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pickups as $pickup)
                        <tr>
                            <td>
                                {{
                                    ($pickups->firstItem() ?? 1)
                                    + $loop->index
                                }}
                            </td>
                            <td>
                                {{
                                    $pickup->tanggal_pengambilan
                                        ?->format('d/m/Y')
                                }}
                            </td>
                            <td>
                                {{ $pickup->apdRequest?->nrp ?? '-' }}
                            </td>
                            <td>
                                {{ $pickup->apdRequest?->nama ?? '-' }}
                            </td>
                            <td>
                                {{
                                    $pickup->apdRequest
                                        ?->ukuran_sepatu
                                    ?? '-'
                                }}
                            </td>
                            <td>{{ $pickup->diambil_oleh }}</td>
                            <td>{{ $pickup->petugas ?: '-' }}</td>
                            <td>
                                <a
                                    href="{{
                                        route(
                                            'apd.pickup.photo',
                                            $pickup
                                        )
                                    }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="apd-action"
                                >
                                    Lihat Foto
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="apd-empty">
                                Belum ada riwayat pengambilan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

{{-- Modal Input Pengajuan --}}
<div
    class="apd-modal"
    id="apdCreateModal"
    aria-hidden="true"
    hidden
>
    <div class="apd-dialog">
        <form
            method="POST"
            action="{{ route('apd.store') }}"
        >
            @csrf

            <div class="apd-modal-header">
                <h2>Input Pengajuan APD</h2>
                <button
                    type="button"
                    class="apd-close js-close-apd"
                >
                    ×
                </button>
            </div>

            <div class="apd-modal-body">
                <div class="apd-form-grid">
                    <div class="apd-field">
                        <label for="apdTanggal">
                            Tanggal Pengajuan
                        </label>
                        <input
                            type="date"
                            name="tanggal_pengajuan"
                            id="apdTanggal"
                            class="apd-input"
                            value="{{
                                old(
                                    'tanggal_pengajuan',
                                    now()->format('Y-m-d')
                                )
                            }}"
                            required
                        >
                    </div>

                    <div class="apd-field">
                        <label for="apdNrp">NRP</label>
                        <input
                            type="text"
                            name="nrp"
                            id="apdNrp"
                            class="apd-input"
                            value="{{ old('nrp') }}"
                            maxlength="50"
                            required
                        >
                    </div>

                    <div class="apd-field">
                        <label for="apdNama">Nama</label>
                        <input
                            type="text"
                            name="nama"
                            id="apdNama"
                            class="apd-input"
                            value="{{ old('nama') }}"
                            maxlength="150"
                            required
                        >
                    </div>

                    <div class="apd-field">
                        <label for="apdJabatan">Jabatan</label>
                        <input
                            type="text"
                            name="jabatan"
                            id="apdJabatan"
                            class="apd-input"
                            value="{{ old('jabatan') }}"
                            maxlength="150"
                            required
                        >
                    </div>

                    <div class="apd-field apd-full">
                        <label>Barang APD yang diajukan</label>

                        <div class="apd-check-grid">
                            @foreach (
                                [
                                    'item_helm' => 'Helm',
                                    'item_sepatu_safety' =>
                                        'Sepatu Safety',
                                    'item_rompi' => 'Rompi',
                                    'item_kacamata' => 'Kacamata',
                                    'item_ear_plug' => 'Ear Plug',
                                ] as $field => $label
                            )
                                <label class="apd-check-option">
                                    <input
                                        type="checkbox"
                                        name="{{ $field }}"
                                        value="1"
                                        @checked(old($field))
                                        @if (
                                            $field
                                            === 'item_sepatu_safety'
                                        )
                                            class="js-safety-shoe-toggle"
                                        @endif
                                    >
                                    <span class="apd-check-box">
                                        {{ $label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div
                        class="apd-field"
                        data-shoe-field
                    >
                        <label for="apdUkuran">
                            Ukuran Sepatu yang Diajukan
                        </label>
                        <input
                            type="text"
                            name="ukuran_sepatu"
                            id="apdUkuran"
                            class="apd-input"
                            value="{{ old('ukuran_sepatu') }}"
                            placeholder="Contoh: 40"
                            maxlength="20"
                        >
                    </div>

                    <div
                        class="apd-field apd-full"
                        data-shoe-field
                    >
                        <label>
                            Posisi Sepatu Saat Ini
                        </label>

                        <div class="apd-status-options">
                            @foreach (
                                [
                                    'SHE',
                                    'WAREHOUSE',
                                    'LOGISTIK',
                                    'READY',
                                ] as $step
                            )
                                <label class="apd-status-option">
                                    <input
                                        type="radio"
                                        name="status_sepatu"
                                        value="{{ $step }}"
                                        @checked(
                                            old(
                                                'status_sepatu',
                                                'SHE'
                                            ) === $step
                                        )
                                    >
                                    <span class="apd-status-box">
                                        {{ $step }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="apd-modal-footer">
                <button
                    type="button"
                    class="apd-secondary js-close-apd"
                >
                    Batal
                </button>
                <button type="submit" class="apd-primary">
                    Simpan Pengajuan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Pengajuan --}}
<div
    class="apd-modal"
    id="apdEditModal"
    aria-hidden="true"
    hidden
>
    <div class="apd-dialog">
        <form
            method="POST"
            action=""
            id="apdEditForm"
        >
            @csrf
            @method('PUT')

            <div class="apd-modal-header">
                <h2>Edit Pengajuan APD</h2>
                <button
                    type="button"
                    class="apd-close js-close-apd"
                >
                    ×
                </button>
            </div>

            <div class="apd-modal-body">
                <div class="apd-form-grid">
                    <div class="apd-field">
                        <label for="editApdTanggal">
                            Tanggal Pengajuan
                        </label>
                        <input
                            type="date"
                            name="tanggal_pengajuan"
                            id="editApdTanggal"
                            class="apd-input"
                            required
                        >
                    </div>

                    <div class="apd-field">
                        <label for="editApdNrp">NRP</label>
                        <input
                            type="text"
                            name="nrp"
                            id="editApdNrp"
                            class="apd-input"
                            maxlength="50"
                            required
                        >
                    </div>

                    <div class="apd-field">
                        <label for="editApdNama">Nama</label>
                        <input
                            type="text"
                            name="nama"
                            id="editApdNama"
                            class="apd-input"
                            maxlength="150"
                            required
                        >
                    </div>

                    <div class="apd-field">
                        <label for="editApdJabatan">
                            Jabatan
                        </label>
                        <input
                            type="text"
                            name="jabatan"
                            id="editApdJabatan"
                            class="apd-input"
                            maxlength="150"
                            required
                        >
                    </div>

                    <div class="apd-field apd-full">
                        <label>Barang APD yang diajukan</label>

                        <div class="apd-check-grid">
                            @foreach (
                                [
                                    'item_helm' => 'Helm',
                                    'item_sepatu_safety' =>
                                        'Sepatu Safety',
                                    'item_rompi' => 'Rompi',
                                    'item_kacamata' => 'Kacamata',
                                    'item_ear_plug' => 'Ear Plug',
                                ] as $field => $label
                            )
                                <label class="apd-check-option">
                                    <input
                                        type="checkbox"
                                        name="{{ $field }}"
                                        id="edit_{{ $field }}"
                                        value="1"
                                        @if (
                                            $field
                                            === 'item_sepatu_safety'
                                        )
                                            class="js-safety-shoe-toggle"
                                        @endif
                                    >
                                    <span class="apd-check-box">
                                        {{ $label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div
                        class="apd-field"
                        data-shoe-field
                    >
                        <label for="editApdUkuran">
                            Ukuran Sepatu yang Diajukan
                        </label>
                        <input
                            type="text"
                            name="ukuran_sepatu"
                            id="editApdUkuran"
                            class="apd-input"
                            maxlength="20"
                        >
                    </div>

                    <div
                        class="apd-field apd-full"
                        data-shoe-field
                    >
                        <label>Posisi Sepatu Saat Ini</label>

                        <div class="apd-status-options">
                            @foreach (
                                [
                                    'SHE',
                                    'WAREHOUSE',
                                    'LOGISTIK',
                                    'READY',
                                ] as $step
                            )
                                <label class="apd-status-option">
                                    <input
                                        type="radio"
                                        name="status_sepatu"
                                        id="edit_status_{{ $step }}"
                                        value="{{ $step }}"
                                    >
                                    <span class="apd-status-box">
                                        {{ $step }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="apd-modal-footer">
                <button
                    type="button"
                    class="apd-secondary js-close-apd"
                >
                    Batal
                </button>
                <button type="submit" class="apd-primary">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Pengambilan: hanya Sepatu Safety READY --}}
<div
    class="apd-modal"
    id="apdPickupModal"
    aria-hidden="true"
    hidden
>
    <div class="apd-dialog pickup">
        <form
            method="POST"
            action="{{ route('apd.pickup.store') }}"
            enctype="multipart/form-data"
            id="apdPickupForm"
        >
            @csrf

            <div class="apd-modal-header">
                <h2>Serah Terima APD</h2>
                <button
                    type="button"
                    class="apd-close js-close-apd"
                >
                    ×
                </button>
            </div>

            <div class="apd-modal-body">
                <div class="apd-pickup-brand">
                    <span class="apd-pickup-brand-mark">
                        PPA
                    </span>
                    <div>
                        <h3>Serah Terima</h3>
                        <p>DOKUMENTASI DIGITAL</p>
                    </div>
                </div>

                <div class="apd-ready-select-wrap">
                    <label for="readyApdSelect">
                        DAFTAR TUNGGU SEPATU READY
                    </label>

                    <select
                        name="apd_request_id"
                        id="readyApdSelect"
                        class="apd-select"
                        required
                    >
                        <option value="">
                            -- Pilih Nama Karyawan --
                        </option>

                        @foreach ($readyShoes as $ready)
                            <option
                                value="{{ $ready->id }}"
                                data-name="{{ $ready->nama }}"
                                data-nrp="{{ $ready->nrp }}"
                                data-jabatan="{{
                                    $ready->jabatan
                                }}"
                                data-size="{{
                                    $ready->ukuran_sepatu
                                }}"
                                @selected(
                                    old('apd_request_id')
                                        == $ready->id
                                )
                            >
                                {{ $ready->nrp }}
                                — {{ $ready->nama }}
                                — Ukuran {{ $ready->ukuran_sepatu }}
                            </option>
                        @endforeach
                    </select>

                    <div
                        class="apd-ready-detail"
                        id="readyApdDetail"
                    ></div>
                </div>

                <div
                    class="apd-form-grid"
                    style="margin-top: 14px;"
                >
                    <div class="apd-field">
                        <label for="pickupDate">
                            Tanggal Pengambilan
                        </label>
                        <input
                            type="date"
                            name="tanggal_pengambilan"
                            id="pickupDate"
                            class="apd-input"
                            value="{{
                                old(
                                    'tanggal_pengambilan',
                                    now()->format('Y-m-d')
                                )
                            }}"
                            required
                        >
                    </div>

                    <div class="apd-field">
                        <label for="pickupBy">
                            Diambil Oleh
                        </label>
                        <input
                            type="text"
                            name="diambil_oleh"
                            id="pickupBy"
                            class="apd-input"
                            value="{{ old('diambil_oleh') }}"
                            maxlength="150"
                            required
                        >
                    </div>

                    <div class="apd-field">
                        <label for="pickupOfficer">
                            Petugas
                        </label>
                        <input
                            type="text"
                            name="petugas"
                            id="pickupOfficer"
                            class="apd-input"
                            value="{{
                                old(
                                    'petugas',
                                    auth()->user()?->name
                                )
                            }}"
                            maxlength="150"
                        >
                    </div>

                    <div class="apd-field">
                        <label for="pickupNote">
                            Keterangan
                        </label>
                        <input
                            type="text"
                            name="keterangan"
                            id="pickupNote"
                            class="apd-input"
                            value="{{ old('keterangan') }}"
                            maxlength="1000"
                        >
                    </div>
                </div>

                <p class="apd-capture-title">
                    AMBIL BUKTI FOTO
                </p>

                <input
                    type="file"
                    name="bukti_foto"
                    id="apdPhotoInput"
                    class="apd-photo-input"
                    accept="image/jpeg,image/png,image/webp"
                    required
                >

                <div class="apd-capture-grid">
                    <button
                        type="button"
                        class="apd-capture-button"
                        id="startApdCamera"
                    >
                        <span>📷</span>
                        <span>KAMERA</span>
                    </button>

                    <label
                        for="apdPhotoInput"
                        class="apd-capture-button"
                    >
                        <span>🖼</span>
                        <span>GALERI</span>
                    </label>
                </div>

                <div
                    class="apd-camera-panel"
                    id="apdCameraPanel"
                >
                    <video
                        id="apdCameraVideo"
                        autoplay
                        playsinline
                        muted
                    ></video>

                    <div class="apd-camera-actions">
                        <button
                            type="button"
                            class="apd-primary purple"
                            id="captureApdPhoto"
                        >
                            Ambil Foto
                        </button>

                        <button
                            type="button"
                            class="apd-secondary"
                            id="stopApdCamera"
                        >
                            Tutup Kamera
                        </button>
                    </div>
                </div>

                <canvas
                    id="apdCameraCanvas"
                    hidden
                ></canvas>

                <img
                    src=""
                    alt="Pratinjau foto bukti"
                    id="apdPhotoPreview"
                    class="apd-photo-preview"
                >

                <button
                    type="submit"
                    class="apd-pickup-submit"
                    @disabled($readyShoes->isEmpty())
                >
                    {{
                        $readyShoes->isEmpty()
                            ? 'BELUM ADA SEPATU READY'
                            : 'SIMPAN DOKUMENTASI'
                    }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const createModal =
        document.getElementById('apdCreateModal');
    const editModal =
        document.getElementById('apdEditModal');
    const pickupModal =
        document.getElementById('apdPickupModal');

    const updateUrl = @json(
        route('apd.update', ['apdRequest' => '__ID__'])
    );

    let cameraStream = null;

    function openModal(modal) {
        if (!modal) {
            return;
        }

        modal.hidden = false;
        modal.removeAttribute('hidden');
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('apd-modal-open');
    }

    function closeModal(modal) {
        if (!modal) {
            return;
        }

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        modal.hidden = true;
        modal.setAttribute('hidden', '');

        if (modal === pickupModal) {
            stopCamera();
        }

        if (!document.querySelector('.apd-modal.is-open')) {
            document.body.classList.remove('apd-modal-open');
        }
    }

    function syncShoeFields(container) {
        const toggle = container.querySelector(
            '.js-safety-shoe-toggle'
        );

        const shoeFields = container.querySelectorAll(
            '[data-shoe-field]'
        );

        const isEnabled = Boolean(toggle?.checked);

        shoeFields.forEach(function (field) {
            field.style.display = isEnabled ? 'grid' : 'none';

            field.querySelectorAll(
                'input, select'
            ).forEach(function (input) {
                if (
                    input.name === 'ukuran_sepatu'
                    || input.name === 'status_sepatu'
                ) {
                    input.disabled = !isEnabled;
                }
            });
        });
    }

    document
        .getElementById('openApdCreate')
        ?.addEventListener('click', function () {
            syncShoeFields(createModal);
            openModal(createModal);
        });

    document
        .getElementById('openApdPickup')
        ?.addEventListener('click', function () {
            openModal(pickupModal);
        });

    document
        .querySelectorAll('[data-open-apd-pickup]')
        .forEach(function (button) {
            button.addEventListener('click', function () {
                openModal(pickupModal);
            });
        });

    document
        .querySelectorAll('.js-close-apd')
        .forEach(function (button) {
            button.addEventListener('click', function () {
                closeModal(button.closest('.apd-modal'));
            });
        });

    document
        .querySelectorAll('.apd-modal')
        .forEach(function (modal) {
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal(modal);
                }
            });
        });

    document
        .querySelectorAll('.js-safety-shoe-toggle')
        .forEach(function (toggle) {
            toggle.addEventListener('change', function () {
                syncShoeFields(
                    toggle.closest('.apd-modal')
                );
            });
        });

    syncShoeFields(createModal);
    syncShoeFields(editModal);

    document
        .querySelectorAll('.js-edit-apd')
        .forEach(function (button) {
            button.addEventListener('click', function () {
                const form =
                    document.getElementById('apdEditForm');

                form.action = updateUrl.replace(
                    '__ID__',
                    encodeURIComponent(button.dataset.id)
                );

                document.getElementById(
                    'editApdTanggal'
                ).value = button.dataset.tanggal || '';

                document.getElementById(
                    'editApdNrp'
                ).value = button.dataset.nrp || '';

                document.getElementById(
                    'editApdNama'
                ).value = button.dataset.nama || '';

                document.getElementById(
                    'editApdJabatan'
                ).value = button.dataset.jabatan || '';

                document.getElementById(
                    'editApdUkuran'
                ).value = button.dataset.ukuran || '';

                document.getElementById(
                    'edit_item_helm'
                ).checked = button.dataset.helm === '1';

                document.getElementById(
                    'edit_item_sepatu_safety'
                ).checked = button.dataset.sepatu === '1';

                document.getElementById(
                    'edit_item_rompi'
                ).checked = button.dataset.rompi === '1';

                document.getElementById(
                    'edit_item_kacamata'
                ).checked = button.dataset.kacamata === '1';

                document.getElementById(
                    'edit_item_ear_plug'
                ).checked = button.dataset.earplug === '1';

                const statusInput = document.getElementById(
                    'edit_status_' + (button.dataset.status || 'SHE')
                );

                if (statusInput) {
                    statusInput.checked = true;
                }

                syncShoeFields(editModal);
                openModal(editModal);
            });
        });

    document
        .querySelectorAll('.js-delete-apd')
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (
                    !window.confirm(
                        'Hapus pengajuan APD ini?'
                    )
                ) {
                    event.preventDefault();
                }
            });
        });

    const readySelect =
        document.getElementById('readyApdSelect');
    const readyDetail =
        document.getElementById('readyApdDetail');
    const pickupBy =
        document.getElementById('pickupBy');

    function updateReadyDetail() {
        const option =
            readySelect?.selectedOptions?.[0];

        if (!option || !option.value) {
            readyDetail?.classList.remove('show');

            if (readyDetail) {
                readyDetail.textContent = '';
            }

            return;
        }

        if (readyDetail) {
            readyDetail.textContent =
                `${option.dataset.nrp} — `
                + `${option.dataset.name} — `
                + `${option.dataset.jabatan} — `
                + `Ukuran ${option.dataset.size}`;

            readyDetail.classList.add('show');
        }

        if (pickupBy && !pickupBy.value) {
            pickupBy.value = option.dataset.name || '';
        }
    }

    readySelect?.addEventListener(
        'change',
        updateReadyDetail
    );

    updateReadyDetail();

    const photoInput =
        document.getElementById('apdPhotoInput');
    const photoPreview =
        document.getElementById('apdPhotoPreview');

    function showPreview(file) {
        if (!file || !photoPreview) {
            return;
        }

        const reader = new FileReader();

        reader.addEventListener('load', function () {
            photoPreview.src = reader.result;
            photoPreview.classList.add('show');
        });

        reader.readAsDataURL(file);
    }

    photoInput?.addEventListener('change', function () {
        showPreview(photoInput.files?.[0]);
    });

    const cameraPanel =
        document.getElementById('apdCameraPanel');
    const cameraVideo =
        document.getElementById('apdCameraVideo');
    const cameraCanvas =
        document.getElementById('apdCameraCanvas');

    async function startCamera() {
        if (
            !navigator.mediaDevices
            || !navigator.mediaDevices.getUserMedia
        ) {
            /*
             * Fallback untuk browser/perangkat yang tidak mendukung
             * kamera langsung: buka pemilih kamera perangkat.
             */
            photoInput.setAttribute(
                'capture',
                'environment'
            );
            photoInput.click();
            photoInput.removeAttribute('capture');
            return;
        }

        try {
            cameraStream =
                await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: {
                            ideal: 'environment',
                        },
                    },
                    audio: false,
                });

            cameraVideo.srcObject = cameraStream;
            cameraPanel.classList.add('show');
        } catch (error) {
            alert(
                'Kamera tidak dapat dibuka. '
                + 'Silakan gunakan tombol Galeri.'
            );
        }
    }

    function stopCamera() {
        if (cameraStream) {
            cameraStream
                .getTracks()
                .forEach(function (track) {
                    track.stop();
                });

            cameraStream = null;
        }

        if (cameraVideo) {
            cameraVideo.srcObject = null;
        }

        cameraPanel?.classList.remove('show');
    }

    document
        .getElementById('startApdCamera')
        ?.addEventListener('click', startCamera);

    document
        .getElementById('stopApdCamera')
        ?.addEventListener('click', stopCamera);

    document
        .getElementById('captureApdPhoto')
        ?.addEventListener('click', function () {
            if (
                !cameraVideo
                || !cameraCanvas
                || !photoInput
            ) {
                return;
            }

            const width =
                cameraVideo.videoWidth || 1280;
            const height =
                cameraVideo.videoHeight || 720;

            cameraCanvas.width = width;
            cameraCanvas.height = height;

            const context =
                cameraCanvas.getContext('2d');

            context.drawImage(
                cameraVideo,
                0,
                0,
                width,
                height
            );

            cameraCanvas.toBlob(
                function (blob) {
                    if (!blob) {
                        return;
                    }

                    const file = new File(
                        [blob],
                        `apd-${Date.now()}.jpg`,
                        { type: 'image/jpeg' }
                    );

                    const transfer = new DataTransfer();
                    transfer.items.add(file);
                    photoInput.files = transfer.files;

                    showPreview(file);
                    stopCamera();
                },
                'image/jpeg',
                .88
            );
        });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }

        document
            .querySelectorAll('.apd-modal.is-open')
            .forEach(closeModal);
    });

    @if ($errors->any())
        @if (
            old('apd_request_id')
            || $openModal === 'pickup'
        )
            openModal(pickupModal);
        @else
            syncShoeFields(createModal);
            openModal(createModal);
        @endif
    @elseif ($openModal === 'create')
        syncShoeFields(createModal);
        openModal(createModal);
    @elseif ($openModal === 'pickup')
        openModal(pickupModal);
    @endif
});
</script>
