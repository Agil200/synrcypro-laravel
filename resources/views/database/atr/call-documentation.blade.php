@php
    $monthLabel = $monthLabel ?? 'Juni 2026';
@endphp

<div class="db-page-title">
    <h1>Dokumentasi Pemanggilan</h1>
    <p>
        Dokumentasi karyawan dengan nilai ATR di bawah standar.
    </p>
</div>

<section class="db-panel">
    <div class="db-filter-grid">
        <div class="db-field">
            <label for="atrCallPeriodFilter">Bulan</label>

            <select
                class="db-select"
                id="atrCallPeriodFilter"
            >
                <option value="{{ $monthLabel }}">
                    {{ $monthLabel }}
                </option>
            </select>
        </div>

        <div class="db-field">
            <label for="atrCallRoleFilter">
                Posisi / Jabatan
            </label>

            <select
                class="db-select"
                id="atrCallRoleFilter"
            >
                <option value="">Semua Posisi</option>

                @foreach (
                    $employees
                        ->pluck('jabatan')
                        ->filter()
                        ->unique()
                        ->sort()
                    as $role
                )
                    <option value="{{ strtolower($role) }}">
                        {{ $role }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="db-field">
            <label for="atrCallSearch">
                Cari Karyawan
            </label>

            <input
                class="db-input"
                id="atrCallSearch"
                type="search"
                placeholder="Cari NRP atau nama…"
                autocomplete="off"
            >
        </div>
    </div>
</section>

<div
    class="db-call-grid"
    id="atrCallGrid"
>
    @forelse ($employees as $employee)
        @php
            $called = (bool) data_get(
                $employee,
                'called',
                false
            );

            $employeeId = (string) data_get(
                $employee,
                'id',
                data_get($employee, 'nrp', '')
            );

            $employeeName = (string) data_get(
                $employee,
                'nama',
                '-'
            );

            $employeeNrp = (string) data_get(
                $employee,
                'nrp',
                '-'
            );

            $employeeRole = (string) data_get(
                $employee,
                'jabatan',
                '-'
            );

            $employeeRosterGroup = (string) data_get(
                $employee,
                'roster_group',
                '-'
            );

            $employeePicPrimary = (string) data_get(
                $employee,
                'pic_primary',
                '-'
            );

            $employeePicBackup = (string) data_get(
                $employee,
                'pic_backup',
                '-'
            );

            $picEffectiveFrom = (string) data_get(
                $employee,
                'pic_effective_from',
                '-'
            );

            $picEffectiveTo = (string) data_get(
                $employee,
                'pic_effective_to',
                ''
            );

            $employeePicEffective =
                $picEffectiveTo !== ''
                    ? $picEffectiveFrom . ' – ' . $picEffectiveTo
                    : 'Mulai ' . $picEffectiveFrom;

            $employeePeriod = (string) data_get(
                $employee,
                'period',
                $monthLabel
            );

            $employeeScore = (string) data_get(
                $employee,
                'atr',
                '-'
            );

            $employeeSick = (string) data_get(
                $employee,
                's',
                0
            );

            $employeePermission = (string) data_get(
                $employee,
                'i',
                0
            );

            $employeeAlpha = (string) data_get(
                $employee,
                'a',
                0
            );
        @endphp

        <article
            class="db-call-card"
            data-atr-call-card
            data-employee-id="{{ $employeeId }}"
            data-nrp="{{ $employeeNrp }}"
            data-name="{{ $employeeName }}"
            data-role="{{ $employeeRole }}"
            data-roster-group="{{ $employeeRosterGroup }}"
            data-pic-primary="{{ $employeePicPrimary }}"
            data-pic-backup="{{ $employeePicBackup }}"
            data-pic-effective="{{ $employeePicEffective }}"
            data-period="{{ $employeePeriod }}"
            data-score="{{ $employeeScore }}"
            data-sick="{{ $employeeSick }}"
            data-permission="{{ $employeePermission }}"
            data-alpha="{{ $employeeAlpha }}"
        >
            <div class="db-call-body">
                <strong class="db-call-name">
                    {{ $employeeName }}
                </strong>

                <p>
                    {{ $employeeRole }}<br>
                    NRP: {{ $employeeNrp }}<br>
                    Roster: {{ $employeeRosterGroup }}
                </p>

                <strong class="db-call-score">
                    {{ $employeeScore }}
                </strong>
            </div>

            @if ($called)
                <span class="db-call-action done">
                    ✓ Sudah Dipanggil
                </span>
            @else
                <button
                    type="button"
                    class="db-call-action db-call-action-button"
                    data-call-open
                >
                    🔔 Lakukan Pemanggilan
                </button>
            @endif
        </article>
    @empty
        <div class="db-call-empty">
            Data karyawan belum tersedia.
        </div>
    @endforelse
</div>

<p
    class="db-call-empty"
    id="atrCallFilterEmpty"
    hidden
>
    Data tidak ditemukan berdasarkan filter.
</p>

{{-- Backdrop modal --}}
<div
    class="atr-call-backdrop"
    id="atrCallBackdrop"
    aria-hidden="true"
></div>

{{-- Modal dokumentasi pemanggilan --}}
<section
    class="atr-call-modal"
    id="atrCallModal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="atrCallModalTitle"
    aria-hidden="true"
>
    <div class="atr-call-modal-header">
        <h2
            class="atr-call-modal-title"
            id="atrCallModalTitle"
        >
            <span aria-hidden="true">🔔</span>
            Dokumentasi Pemanggilan
        </h2>

        <button
            type="button"
            class="atr-call-close"
            id="atrCallClose"
            aria-label="Tutup modal"
        >
            &times;
        </button>
    </div>

    <form
        class="atr-call-modal-body"
        id="atrCallForm"
        enctype="multipart/form-data"
        data-submit-url="{{ $atrDocumentationEndpoint ?? '' }}"
    >
        @csrf

        <input
            type="hidden"
            name="employee_id"
            id="atrCallEmployeeId"
        >

        <input
            type="hidden"
            name="nrp"
            id="atrCallEmployeeNrp"
        >

        <input
            type="hidden"
            name="period"
            id="atrCallEmployeePeriodInput"
        >

        <input
            type="hidden"
            name="roster_group"
            id="atrCallRosterGroupInput"
        >

        <input
            type="hidden"
            name="pic_primary"
            id="atrCallPicPrimaryInput"
        >

        <input
            type="hidden"
            name="pic_backup"
            id="atrCallPicBackupInput"
        >

        <div class="atr-call-info">
            <div class="atr-call-info-row">
                <span>Operator</span>
                <strong id="atrCallEmployeeName">-</strong>
            </div>

            <div class="atr-call-info-row">
                <span>Roster / Unit</span>
                <strong id="atrCallRosterGroup">-</strong>
            </div>

            <div class="atr-call-info-row">
                <span>PIC Roster</span>
                <strong id="atrCallPicPrimary">-</strong>
            </div>

            <div class="atr-call-info-row">
                <span>PIC Pendamping</span>
                <strong id="atrCallPicBackup">-</strong>
            </div>

            <div class="atr-call-info-row">
                <span>Berlaku PIC</span>
                <strong id="atrCallPicEffective">-</strong>
            </div>

            <div class="atr-call-info-row">
                <span>Periode ATR</span>
                <strong id="atrCallEmployeePeriod">-</strong>
            </div>

            <div class="atr-call-info-row">
                <span>ATR · S/I/A</span>

                <strong
                    class="atr-call-score"
                    id="atrCallEmployeeScore"
                >
                    -
                </strong>
            </div>
        </div>

        <label
            class="atr-call-upload-label"
            for="atrCallFile"
        >
            Upload Bukti Pemanggilan
        </label>

        <input
            type="file"
            name="document"
            id="atrCallFile"
            accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf"
            hidden
        >

        <label
            class="atr-call-dropzone"
            id="atrCallDropzone"
            for="atrCallFile"
        >
            <span>
                <span
                    class="atr-call-upload-icon"
                    aria-hidden="true"
                >
                    📷
                </span>

                <strong>
                    Klik untuk pilih foto / dokumen
                </strong>

                <small>
                    JPG, PNG, PDF — maksimal 5MB
                </small>

                <span
                    class="atr-call-file-name"
                    id="atrCallFileName"
                ></span>
            </span>
        </label>

        <p
            class="atr-call-error"
            id="atrCallError"
            role="alert"
        ></p>

        <div class="atr-call-actions">
            <button
                type="submit"
                class="atr-call-save"
                id="atrCallSave"
            >
                ✓ Simpan Dokumentasi
            </button>

            <button
                type="button"
                class="atr-call-cancel"
                id="atrCallCancel"
            >
                Batal
            </button>
        </div>

        <p class="atr-call-timestamp-note">
            ◷ Timestamp upload otomatis dicatat sistem
        </p>
    </form>
</section>

<style>
    .db-call-action-button {
        width: 100%;
        border: 0;
        cursor: pointer;
        font-family: inherit;
    }

    .db-call-action-button:hover {
        filter: brightness(.94);
    }

    .db-call-score {
        color: #f59e0b;
        font-size: 21px;
    }

    .db-call-name {
        display: block;
        font-size: 14px;
    }

    .db-call-empty {
        grid-column: 1 / -1;
        margin: 0;
        padding: 24px;
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        color: #64748b;
        background: #fff;
        text-align: center;
    }

    .atr-call-backdrop {
        position: fixed;
        z-index: 9000;
        inset: 0;
        visibility: hidden;
        opacity: 0;
        background: rgba(15, 23, 42, .62);
        backdrop-filter: blur(2px);
        pointer-events: none;
        transition:
            opacity .2s ease,
            visibility .2s ease;
    }

    .atr-call-backdrop.is-open {
        visibility: visible;
        opacity: 1;
        pointer-events: auto;
    }

    .atr-call-modal {
        position: fixed;
        top: 50%;
        left: 50%;
        z-index: 9001;
        width: min(385px, calc(100vw - 28px));
        max-height: calc(100vh - 28px);
        overflow: auto;
        visibility: hidden;
        opacity: 0;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 26px 80px rgba(15, 23, 42, .34);
        transform: translate(-50%, -46%) scale(.97);
        pointer-events: none;
        transition:
            opacity .2s ease,
            transform .2s ease,
            visibility .2s ease;
    }

    .atr-call-modal.is-open {
        visibility: visible;
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
        pointer-events: auto;
    }

    .atr-call-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 18px 20px 12px;
    }

    .atr-call-modal-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        color: #172033;
        font-size: 18px;
        font-weight: 900;
    }

    .atr-call-close {
        width: 34px;
        height: 34px;
        border: 0;
        border-radius: 50%;
        color: #94a3b8;
        background: transparent;
        cursor: pointer;
        font-size: 25px;
        line-height: 1;
    }

    .atr-call-close:hover {
        color: #0f172a;
        background: #f1f5f9;
    }

    .atr-call-modal-body {
        padding: 0 20px 19px;
    }

    .atr-call-info {
        padding: 13px;
        border-radius: 10px;
        background: #f8fafc;
    }

    .atr-call-info-row {
        display: grid;
        grid-template-columns: 90px minmax(0, 1fr);
        gap: 10px;
        align-items: start;
        margin-bottom: 6px;
        color: #64748b;
        font-size: 12px;
    }

    .atr-call-info-row:last-child {
        margin-bottom: 0;
    }

    .atr-call-info-row strong {
        color: #273247;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .atr-call-info-row .atr-call-score {
        color: #ef3340;
    }

    .atr-call-upload-label {
        display: block;
        margin: 16px 0 8px;
        color: #64748b;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .atr-call-dropzone {
        display: grid;
        min-height: 108px;
        place-items: center;
        padding: 16px;
        border: 1px dashed #cbd5e1;
        border-radius: 11px;
        color: #64748b;
        background: #f8fafc;
        cursor: pointer;
        text-align: center;
        transition:
            border-color .16s ease,
            background .16s ease;
    }

    .atr-call-dropzone:hover,
    .atr-call-dropzone.has-file {
        border-color: #ef3340;
        background: #fff7f8;
    }

    .atr-call-dropzone > span {
        display: grid;
        gap: 5px;
        justify-items: center;
    }

    .atr-call-upload-icon {
        font-size: 27px;
    }

    .atr-call-dropzone strong {
        color: #64748b;
        font-size: 12px;
    }

    .atr-call-dropzone small {
        color: #94a3b8;
        font-size: 10px;
    }

    .atr-call-file-name {
        max-width: 290px;
        color: #172033;
        font-size: 10px;
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .atr-call-error {
        min-height: 16px;
        margin: 7px 0 0;
        color: #dc2626;
        font-size: 11px;
        font-weight: 700;
    }

    .atr-call-actions {
        display: grid;
        gap: 7px;
        margin-top: 5px;
    }

    .atr-call-save,
    .atr-call-cancel {
        min-height: 36px;
        border-radius: 5px;
        cursor: pointer;
        font-family: inherit;
        font-size: 12px;
        font-weight: 900;
    }

    .atr-call-save {
        border: 1px solid #ea3343;
        color: #fff;
        background: #ea3343;
    }

    .atr-call-save:hover {
        background: #d92536;
    }

    .atr-call-save:disabled {
        cursor: wait;
        opacity: .65;
    }

    .atr-call-cancel {
        border: 1px solid #64748b;
        color: #64748b;
        background: #fff;
    }

    .atr-call-timestamp-note {
        margin: 12px 0 0;
        color: #777;
        font-size: 9px;
        text-align: center;
    }

    body.atr-call-modal-open {
        overflow: hidden;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal =
        document.getElementById('atrCallModal');

    const backdrop =
        document.getElementById('atrCallBackdrop');

    const closeButton =
        document.getElementById('atrCallClose');

    const cancelButton =
        document.getElementById('atrCallCancel');

    const form =
        document.getElementById('atrCallForm');

    const fileInput =
        document.getElementById('atrCallFile');

    const dropzone =
        document.getElementById('atrCallDropzone');

    const fileName =
        document.getElementById('atrCallFileName');

    const errorText =
        document.getElementById('atrCallError');

    const saveButton =
        document.getElementById('atrCallSave');

    const employeeId =
        document.getElementById('atrCallEmployeeId');

    const employeeNrp =
        document.getElementById('atrCallEmployeeNrp');

    const employeePeriodInput =
        document.getElementById('atrCallEmployeePeriodInput');

    const employeeName =
        document.getElementById('atrCallEmployeeName');

    const rosterGroupInput =
        document.getElementById('atrCallRosterGroupInput');

    const picPrimaryInput =
        document.getElementById('atrCallPicPrimaryInput');

    const picBackupInput =
        document.getElementById('atrCallPicBackupInput');

    const rosterGroup =
        document.getElementById('atrCallRosterGroup');

    const picPrimary =
        document.getElementById('atrCallPicPrimary');

    const picBackup =
        document.getElementById('atrCallPicBackup');

    const picEffective =
        document.getElementById('atrCallPicEffective');

    const employeePeriod =
        document.getElementById('atrCallEmployeePeriod');

    const employeeScore =
        document.getElementById('atrCallEmployeeScore');

    const searchInput =
        document.getElementById('atrCallSearch');

    const roleFilter =
        document.getElementById('atrCallRoleFilter');

    const filterEmpty =
        document.getElementById('atrCallFilterEmpty');

    let activeCard = null;

    function setError(message) {
        if (errorText) {
            errorText.textContent = message || '';
        }
    }

    function resetFile() {
        if (fileInput) {
            fileInput.value = '';
        }

        if (fileName) {
            fileName.textContent = '';
        }

        dropzone?.classList.remove('has-file');
    }

    function openModal(card) {
        if (!modal || !backdrop || !form || !card) {
            return;
        }

        activeCard = card;
        form.reset();
        resetFile();
        setError('');

        const scoreSummary =
            (card.dataset.score || '-') +
            ' · S:' +
            (card.dataset.sick || '0') +
            ' I:' +
            (card.dataset.permission || '0') +
            ' A:' +
            (card.dataset.alpha || '0');

        employeeId.value =
            card.dataset.employeeId || '';

        employeeNrp.value =
            card.dataset.nrp || '';

        employeePeriodInput.value =
            card.dataset.period || '';

        employeeName.textContent =
            card.dataset.name || '-';

        rosterGroupInput.value =
            card.dataset.rosterGroup || '';

        picPrimaryInput.value =
            card.dataset.picPrimary || '';

        picBackupInput.value =
            card.dataset.picBackup || '';

        rosterGroup.textContent =
            card.dataset.rosterGroup || '-';

        picPrimary.textContent =
            card.dataset.picPrimary || '-';

        picBackup.textContent =
            card.dataset.picBackup || '-';

        picEffective.textContent =
            card.dataset.picEffective || '-';

        employeePeriod.textContent =
            card.dataset.period || '-';

        employeeScore.textContent =
            scoreSummary;

        modal.classList.add('is-open');
        backdrop.classList.add('is-open');

        modal.setAttribute('aria-hidden', 'false');
        backdrop.setAttribute('aria-hidden', 'false');

        document.body.classList.add(
            'atr-call-modal-open'
        );

        window.setTimeout(function () {
            closeButton?.focus();
        }, 50);
    }

    function closeModal() {
        if (!modal || !backdrop) {
            return;
        }

        modal.classList.remove('is-open');
        backdrop.classList.remove('is-open');

        modal.setAttribute('aria-hidden', 'true');
        backdrop.setAttribute('aria-hidden', 'true');

        document.body.classList.remove(
            'atr-call-modal-open'
        );

        const previousCard = activeCard;
        activeCard = null;

        previousCard
            ?.querySelector('[data-call-open]')
            ?.focus();
    }

    function validateFile(file) {
        if (!file) {
            return 'Pilih bukti pemanggilan terlebih dahulu.';
        }

        const allowedTypes = [
            'image/jpeg',
            'image/png',
            'application/pdf'
        ];

        if (!allowedTypes.includes(file.type)) {
            return 'Format file harus JPG, PNG, atau PDF.';
        }

        if (file.size > 5 * 1024 * 1024) {
            return 'Ukuran file maksimal 5MB.';
        }

        return '';
    }

    function applyFilters() {
        const keyword =
            (searchInput?.value || '')
                .trim()
                .toLowerCase();

        const selectedRole =
            (roleFilter?.value || '')
                .trim()
                .toLowerCase();

        let visibleCount = 0;

        document
            .querySelectorAll('[data-atr-call-card]')
            .forEach(function (card) {
                const haystack = [
                    card.dataset.nrp,
                    card.dataset.name,
                    card.dataset.role,
                    card.dataset.rosterGroup,
                    card.dataset.picPrimary,
                    card.dataset.picBackup
                ]
                    .join(' ')
                    .toLowerCase();

                const matchesKeyword =
                    keyword === '' ||
                    haystack.includes(keyword);

                const matchesRole =
                    selectedRole === '' ||
                    (card.dataset.role || '')
                        .toLowerCase() === selectedRole;

                const visible =
                    matchesKeyword && matchesRole;

                card.hidden = !visible;

                if (visible) {
                    visibleCount += 1;
                }
            });

        if (filterEmpty) {
            filterEmpty.hidden = visibleCount !== 0;
        }
    }

    document.addEventListener('click', function (event) {
        const openButton =
            event.target.closest('[data-call-open]');

        if (!openButton) {
            return;
        }

        const card =
            openButton.closest('[data-atr-call-card]');

        openModal(card);
    });

    [closeButton, cancelButton, backdrop]
        .forEach(function (element) {
            element?.addEventListener(
                'click',
                closeModal
            );
        });

    document.addEventListener('keydown', function (event) {
        if (
            event.key === 'Escape' &&
            modal?.classList.contains('is-open')
        ) {
            closeModal();
        }
    });

    fileInput?.addEventListener('change', function () {
        const selectedFile =
            fileInput.files?.[0] || null;

        setError('');

        if (!selectedFile) {
            resetFile();
            return;
        }

        fileName.textContent = selectedFile.name;
        dropzone?.classList.add('has-file');
    });

    searchInput?.addEventListener(
        'input',
        applyFilters
    );

    roleFilter?.addEventListener(
        'change',
        applyFilters
    );

    form?.addEventListener('submit', async function (event) {
        event.preventDefault();

        if (!activeCard) {
            return;
        }

        const selectedFile =
            fileInput?.files?.[0] || null;

        const validationError =
            validateFile(selectedFile);

        if (validationError) {
            setError(validationError);
            return;
        }

        const submitUrl =
            form.dataset.submitUrl || '';

        if (!submitUrl) {
            setError(
                'Endpoint penyimpanan belum diaktifkan. ' +
                'Modal dan pemilihan file sudah berfungsi.'
            );
            return;
        }

        saveButton.disabled = true;
        saveButton.textContent = 'Menyimpan...';

        try {
            const response = await fetch(
                submitUrl,
                {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        Accept: 'application/json'
                    }
                }
            );

            const payload = await response.json();

            if (!response.ok) {
                throw new Error(
                    payload.message ||
                    'Dokumentasi gagal disimpan.'
                );
            }

            const actionButton =
                activeCard.querySelector(
                    '[data-call-open]'
                );

            if (actionButton) {
                const doneLabel =
                    document.createElement('span');

                doneLabel.className =
                    'db-call-action done';

                doneLabel.textContent =
                    '✓ Sudah Dipanggil';

                actionButton.replaceWith(doneLabel);
            }

            closeModal();
        } catch (error) {
            setError(
                error.message ||
                'Dokumentasi gagal disimpan.'
            );
        } finally {
            saveButton.disabled = false;
            saveButton.textContent =
                '✓ Simpan Dokumentasi';
        }
    });
});
</script>