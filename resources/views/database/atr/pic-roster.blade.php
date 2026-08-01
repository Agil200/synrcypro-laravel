<div class="db-page-title">
    <h1>Pengaturan PIC Roster</h1>
    <p>
        Pengaturan penanggung jawab/Bapak Asuh berdasarkan
        kelompok jabatan atau unit roster.
    </p>
</div>

<div class="atr-sticky-zone pic-kpi-sticky">
<div class="pic-kpi-grid">
    <article class="pic-kpi">
        <span class="pic-kpi-icon">▦</span>
        <div>
            <small>Total Kelompok Roster</small>
            <strong>{{ $picRosterStats['total_groups'] ?? 0 }}</strong>
        </div>
    </article>

    <article class="pic-kpi">
        <span class="pic-kpi-icon green">✓</span>
        <div>
            <small>Penugasan Aktif</small>
            <strong>{{ $picRosterStats['active_assignments'] ?? 0 }}</strong>
        </div>
    </article>

    <article class="pic-kpi">
        <span class="pic-kpi-icon blue">👤</span>
        <div>
            <small>PIC Aktif</small>
            <strong>{{ $picRosterStats['active_pics'] ?? 0 }}</strong>
        </div>
    </article>

    <article class="pic-kpi">
        <span class="pic-kpi-icon red">!</span>
        <div>
            <small>Belum Ada PIC</small>
            <strong>{{ $picRosterStats['unassigned_groups'] ?? 0 }}</strong>
        </div>
    </article>
</div>
</div>

<section class="pic-shell">
    <div class="pic-toolbar">
        <div class="pic-tabs" role="tablist">
            <button type="button" class="pic-tab active" data-pic-tab="assignments">
                Penugasan PIC
            </button>
            <button type="button" class="pic-tab" data-pic-tab="mapping">
                Mapping Jabatan
            </button>
            <button type="button" class="pic-tab" data-pic-tab="history">
                Riwayat Rotasi
            </button>
        </div>

        <div class="pic-actions">
            <button type="button" class="pic-button secondary" id="picUploadButton">
                ↑ Upload Excel
            </button>

            <input type="file" id="picUploadInput" accept=".xlsx,.xls,.csv" hidden>

            <button type="button" class="pic-button" data-pic-modal-open="assignment">
                + Tambah Penugasan
            </button>

            <button type="button" class="pic-button warning" data-pic-modal-open="rotation">
                ↻ Rotasi PIC
            </button>
        </div>
    </div>

    <div class="pic-upload-info" id="picUploadInfo" hidden></div>

    <div class="pic-tab-panel active" data-pic-panel="assignments">
        <div class="pic-filter-row">
            <input
                type="search"
                class="db-input"
                id="picAssignmentSearch"
                placeholder="Cari roster atau PIC…"
            >

            <select class="db-select" id="picAssignmentStatus">
                <option value="">Semua Status</option>
                <option value="aktif">Aktif</option>
                <option value="selesai">Selesai</option>
            </select>
        </div>

        <div class="pic-table-wrap">
            <table class="pic-table">
                <thead>
                    <tr>
                        <th>Kelompok Roster</th>
                        <th>PIC Roster Utama</th>
                        <th>Pendamping/Pembina</th>
                        <th>Mulai Berlaku</th>
                        <th>Sampai</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($rosterGroups as $group)
                        <tr
                            data-assignment-row
                            data-search="{{ strtolower(
                                $group['label'] . ' ' .
                                $group['pic_primary'] . ' ' .
                                $group['pic_backup']
                            ) }}"
                            data-status="{{ strtolower($group['status']) }}"
                        >
                            <td>
                                <strong>{{ $group['label'] }}</strong>
                                <small>{{ $group['code'] }}</small>
                            </td>
                            <td>{{ $group['pic_primary'] }}</td>
                            <td>{{ $group['pic_backup'] }}</td>
                            <td>{{ $group['effective_from'] }}</td>
                            <td>{{ $group['effective_to'] ?? 'Sekarang' }}</td>
                            <td>
                                <span class="pic-status active">
                                    {{ $group['status'] }}
                                </span>
                            </td>
                            <td>
                                <button
                                    type="button"
                                    class="pic-link-button"
                                    data-pic-modal-open="rotation"
                                    data-roster="{{ $group['code'] }}"
                                    data-primary="{{ $group['pic_primary'] }}"
                                    data-backup="{{ $group['pic_backup'] }}"
                                >
                                    Rotasi
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <p class="pic-empty" id="picAssignmentEmpty" hidden>
            Data penugasan tidak ditemukan.
        </p>
    </div>

    <div class="pic-tab-panel" data-pic-panel="mapping">
        <div class="pic-section-head">
            <div>
                <h2>Mapping Jabatan/Unit</h2>
                <p>
                    Jabatan mentah dari file ATR diarahkan ke salah
                    satu dari 10 kelompok roster.
                </p>
            </div>

            <button type="button" class="pic-button" data-pic-modal-open="mapping">
                + Tambah Mapping
            </button>
        </div>

        <div class="pic-table-wrap">
            <table class="pic-table">
                <thead>
                    <tr>
                        <th>Jabatan dari Data</th>
                        <th>Kelompok Roster</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($positionMappings as $mapping)
                        <tr>
                            <td>{{ $mapping['raw_position'] }}</td>
                            <td>
                                <span class="pic-group-badge">
                                    {{ $mapping['roster_group'] }}
                                </span>
                            </td>
                            <td>
                                <span class="pic-status active">
                                    {{ $mapping['status'] }}
                                </span>
                            </td>
                            <td>
                                <button type="button" class="pic-link-button">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="pic-tab-panel" data-pic-panel="history">
        <div class="pic-section-head">
            <div>
                <h2>Riwayat Rotasi PIC</h2>
                <p>
                    Data lama tidak ditimpa agar histori dokumentasi
                    pemanggilan tetap benar.
                </p>
            </div>
        </div>

        <div class="pic-table-wrap">
            <table class="pic-table">
                <thead>
                    <tr>
                        <th>Kelompok Roster</th>
                        <th>PIC Sebelumnya</th>
                        <th>PIC Baru</th>
                        <th>Tanggal Efektif</th>
                        <th>Alasan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($rotationHistory as $history)
                        <tr>
                            <td>
                                <span class="pic-group-badge">
                                    {{ $history['roster_group'] }}
                                </span>
                            </td>
                            <td>{{ $history['old_primary'] }}</td>
                            <td>{{ $history['new_primary'] }}</td>
                            <td>{{ $history['effective_date'] }}</td>
                            <td>{{ $history['reason'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="pic-empty">
                                Belum ada riwayat rotasi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

<div class="pic-modal-backdrop" id="picModalBackdrop" aria-hidden="true"></div>

<section
    class="pic-modal"
    id="picModal"
    role="dialog"
    aria-modal="true"
    aria-hidden="true"
>
    <div class="pic-modal-head">
        <div>
            <small>Pengaturan PIC Roster</small>
            <h2 id="picModalTitle">Tambah Penugasan</h2>
        </div>

        <button type="button" class="pic-modal-close" id="picModalClose">
            &times;
        </button>
    </div>

    <form id="picModalForm">
        <div class="pic-modal-grid">
            <div class="db-field">
                <label for="picRosterGroup">Kelompok Roster</label>
                <select id="picRosterGroup" class="db-select">
                    @foreach ($rosterGroups as $group)
                        <option value="{{ $group['code'] }}">
                            {{ $group['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="db-field">
                <label for="picPrimary">PIC Roster Utama</label>
                <select id="picPrimary" class="db-select">
                    @foreach ($picOptions as $pic)
                        <option value="{{ $pic }}">{{ $pic }}</option>
                    @endforeach
                </select>
            </div>

            <div class="db-field">
                <label for="picBackup">Pendamping/Pembina</label>
                <select id="picBackup" class="db-select">
                    @foreach ($picOptions as $pic)
                        <option value="{{ $pic }}">{{ $pic }}</option>
                    @endforeach
                </select>
            </div>

            <div class="db-field">
                <label for="picEffectiveDate">Mulai Berlaku</label>
                <input type="date" id="picEffectiveDate" class="db-input">
            </div>

            <div class="db-field pic-full-field">
                <label for="picReason">Alasan / Catatan</label>
                <textarea
                    id="picReason"
                    class="pic-textarea"
                    placeholder="Contoh: rotasi pembagian roster"
                ></textarea>
            </div>
        </div>

        <p class="pic-modal-note">
            Fase 1 UI/UX: perubahan belum disimpan permanen.
        </p>

        <div class="pic-modal-actions">
            <button type="submit" class="pic-button">Simpan Preview</button>
            <button type="button" class="pic-button secondary" id="picModalCancel">
                Batal
            </button>
        </div>
    </form>
</section>

<style>
.pic-kpi-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:11px}
.pic-kpi{display:flex;align-items:center;gap:12px;padding:14px;border:1px solid var(--db-border);border-radius:12px;background:#fff;box-shadow:var(--db-shadow)}
.pic-kpi-icon{display:grid;width:42px;height:42px;flex:0 0 42px;place-items:center;border-radius:10px;color:#fff;background:#334155;font-size:18px;font-weight:900}
.pic-kpi-icon.green{background:#159447}.pic-kpi-icon.blue{background:#147df5}.pic-kpi-icon.red{background:#e51d2a}
.pic-kpi small{display:block;color:#64748b;font-size:8px;font-weight:900;text-transform:uppercase}.pic-kpi strong{display:block;margin-top:3px;font-size:24px}
.pic-shell{overflow:visible;border:1px solid var(--db-border);border-radius:12px;background:#fff;box-shadow:var(--db-shadow)}
.pic-toolbar{position:sticky;z-index:30;top:0;background:#fff;box-shadow:0 8px 16px -16px rgba(15,23,42,.7)}.pic-toolbar,.pic-section-head{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;padding:13px;border-bottom:1px solid var(--db-border)}
.pic-tabs,.pic-actions{display:flex;flex-wrap:wrap;gap:7px}
.pic-tab{min-height:34px;padding:0 12px;border:1px solid #cfd6de;border-radius:8px;color:#475569;background:#fff;cursor:pointer;font-size:10px;font-weight:900}
.pic-tab.active{border-color:#147df5;color:#fff;background:#147df5}
.pic-button{display:inline-flex;min-height:34px;align-items:center;justify-content:center;padding:0 12px;border:1px solid #147df5;border-radius:8px;color:#fff;background:#147df5;cursor:pointer;font-family:inherit;font-size:9px;font-weight:900}
.pic-button.secondary{border-color:#cbd5e1;color:#334155;background:#fff}.pic-button.warning{border-color:#f59e0b;background:#f59e0b}
.pic-upload-info{margin:12px 13px 0;padding:10px 12px;border:1px solid #bfdbfe;border-radius:8px;color:#1d4ed8;background:#eff6ff;font-size:10px;font-weight:800}
.pic-tab-panel{display:none;padding:13px}.pic-tab-panel.active{display:block}
.pic-filter-row{display:grid;grid-template-columns:minmax(220px,1fr) 180px;gap:9px;margin-bottom:10px}
.pic-table-wrap{height:clamp(320px,calc(100vh - 365px),620px);overflow:auto;border:1px solid #e2e8f0;border-radius:9px}
.pic-table{width:100%;min-width:920px;border-collapse:collapse}
.pic-table th,.pic-table td{padding:10px 11px;border-bottom:1px solid #e5e7eb;color:#334155;font-size:9px;text-align:left;vertical-align:middle}
.pic-table th{position:sticky;z-index:2;top:0;color:#475569;background:#f8fafc;font-size:8px;font-weight:900;text-transform:uppercase}
.pic-table td strong,.pic-table td small{display:block}.pic-table td small{margin-top:2px;color:#94a3b8;font-size:8px}
.pic-status,.pic-group-badge{display:inline-flex;min-height:21px;align-items:center;padding:2px 8px;border-radius:999px;font-size:8px;font-weight:900}
.pic-status.active{color:#087a45;background:#dcfce7}.pic-group-badge{color:#1d4ed8;background:#dbeafe}
.pic-link-button{border:0;color:#147df5;background:transparent;cursor:pointer;font-size:9px;font-weight:900}
.pic-section-head h2{margin:0 0 3px;font-size:13px}.pic-section-head p{margin:0;color:#64748b;font-size:9px}
.pic-empty{padding:25px;color:#64748b;text-align:center;font-size:10px;font-weight:700}
.pic-modal-backdrop{position:fixed;z-index:9100;inset:0;visibility:hidden;opacity:0;background:rgba(15,23,42,.62);backdrop-filter:blur(2px);pointer-events:none;transition:.2s ease}
.pic-modal-backdrop.is-open{visibility:visible;opacity:1;pointer-events:auto}
.pic-modal{position:fixed;z-index:9101;top:50%;left:50%;width:min(620px,calc(100vw - 30px));max-height:calc(100vh - 30px);padding:18px;overflow:auto;visibility:hidden;opacity:0;border-radius:16px;background:#fff;box-shadow:0 26px 80px rgba(15,23,42,.34);transform:translate(-50%,-46%) scale(.97);pointer-events:none;transition:.2s ease}
.pic-modal.is-open{visibility:visible;opacity:1;transform:translate(-50%,-50%) scale(1);pointer-events:auto}
.pic-modal-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:15px}.pic-modal-head small{color:#147df5;font-size:8px;font-weight:900;text-transform:uppercase}.pic-modal-head h2{margin:3px 0 0;font-size:19px}
.pic-modal-close{width:34px;height:34px;border:0;border-radius:50%;color:#64748b;background:#f1f5f9;cursor:pointer;font-size:24px}
.pic-modal-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.pic-full-field{grid-column:1/-1}
.pic-textarea{min-height:88px;padding:10px 12px;border:1px solid #cfd6de;border-radius:8px;resize:vertical;font-family:inherit}
.pic-modal-note{margin:11px 0;padding:9px 11px;border-radius:8px;color:#92400e;background:#fffbeb;font-size:9px;font-weight:800}
.pic-modal-actions{display:flex;justify-content:flex-end;gap:8px}body.pic-modal-open{overflow:hidden}
@media(max-width:1000px){.pic-kpi-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:680px){.pic-kpi-grid,.pic-filter-row,.pic-modal-grid{grid-template-columns:1fr}.pic-full-field{grid-column:auto}}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = Array.from(document.querySelectorAll('[data-pic-tab]'));
    const panels = Array.from(document.querySelectorAll('[data-pic-panel]'));

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            const target = tab.dataset.picTab;

            tabs.forEach(function (item) {
                item.classList.toggle('active', item === tab);
            });

            panels.forEach(function (panel) {
                panel.classList.toggle(
                    'active',
                    panel.dataset.picPanel === target
                );
            });
        });
    });

    const search = document.getElementById('picAssignmentSearch');
    const status = document.getElementById('picAssignmentStatus');
    const empty = document.getElementById('picAssignmentEmpty');

    function filterAssignments() {
        const keyword = (search?.value || '').trim().toLowerCase();
        const selectedStatus = (status?.value || '').trim().toLowerCase();
        let visible = 0;

        document.querySelectorAll('[data-assignment-row]').forEach(function (row) {
            const matchesKeyword =
                keyword === '' ||
                (row.dataset.search || '').includes(keyword);

            const matchesStatus =
                selectedStatus === '' ||
                row.dataset.status === selectedStatus;

            row.hidden = !(matchesKeyword && matchesStatus);

            if (!row.hidden) {
                visible += 1;
            }
        });

        if (empty) {
            empty.hidden = visible !== 0;
        }
    }

    search?.addEventListener('input', filterAssignments);
    status?.addEventListener('change', filterAssignments);

    const uploadButton = document.getElementById('picUploadButton');
    const uploadInput = document.getElementById('picUploadInput');
    const uploadInfo = document.getElementById('picUploadInfo');

    uploadButton?.addEventListener('click', function () {
        uploadInput?.click();
    });

    uploadInput?.addEventListener('change', function () {
        const file = uploadInput.files?.[0];

        if (!file || !uploadInfo) {
            return;
        }

        uploadInfo.hidden = false;
        uploadInfo.textContent =
            'File dipilih: ' + file.name +
            '. Preview import akan dibuat pada fase backend.';
    });

    const modal = document.getElementById('picModal');
    const backdrop = document.getElementById('picModalBackdrop');
    const closeButton = document.getElementById('picModalClose');
    const cancelButton = document.getElementById('picModalCancel');
    const modalTitle = document.getElementById('picModalTitle');
    const rosterField = document.getElementById('picRosterGroup');
    const primaryField = document.getElementById('picPrimary');
    const backupField = document.getElementById('picBackup');

    function openModal(button) {
        const mode = button.dataset.picModalOpen || 'assignment';

        modalTitle.textContent =
            mode === 'rotation'
                ? 'Rotasi PIC Roster'
                : mode === 'mapping'
                    ? 'Tambah Mapping Jabatan'
                    : 'Tambah Penugasan PIC';

        if (button.dataset.roster) {
            rosterField.value = button.dataset.roster;
        }

        if (button.dataset.primary) {
            primaryField.value = button.dataset.primary;
        }

        if (button.dataset.backup) {
            backupField.value = button.dataset.backup;
        }

        modal?.classList.add('is-open');
        backdrop?.classList.add('is-open');
        modal?.setAttribute('aria-hidden', 'false');
        backdrop?.setAttribute('aria-hidden', 'false');
        document.body.classList.add('pic-modal-open');
    }

    function closeModal() {
        modal?.classList.remove('is-open');
        backdrop?.classList.remove('is-open');
        modal?.setAttribute('aria-hidden', 'true');
        backdrop?.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('pic-modal-open');
    }

    document.addEventListener('click', function (event) {
        const opener = event.target.closest('[data-pic-modal-open]');

        if (opener) {
            openModal(opener);
        }
    });

    [closeButton, cancelButton, backdrop].forEach(function (element) {
        element?.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (event) {
        if (
            event.key === 'Escape' &&
            modal?.classList.contains('is-open')
        ) {
            closeModal();
        }
    });

    document.getElementById('picModalForm')
        ?.addEventListener('submit', function (event) {
            event.preventDefault();

            alert(
                'Preview UI berhasil. ' +
                'Penyimpanan permanen dibuat pada fase database.'
            );

            closeModal();
        });
});
</script>