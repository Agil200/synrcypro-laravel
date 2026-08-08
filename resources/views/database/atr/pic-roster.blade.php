@php
    $monthCount = $periodOptions->count();
@endphp

<style>
.prm-title{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:10px}
.prm-title h1{margin:0;color:#132641;font-size:23px}
.prm-title p{margin:3px 0 0;color:#6f8097;font-size:9px}
.prm-badge{padding:7px 10px;border:1px solid #cee0f3;border-radius:999px;background:#f4f8fd;color:#315a87;font-size:7px;font-weight:900;white-space:nowrap}

.prm-alert{padding:9px 11px;margin-bottom:9px;border-radius:9px;font-size:9px}
.prm-alert.ok{border:1px solid #b9e9cb;background:#edf9f2;color:#087b43}
.prm-alert.err{border:1px solid #ffc6ce;background:#fff0f2;color:#a91e31}

.prm-toolbar{display:grid;grid-template-columns:250px 1fr auto;gap:9px;align-items:end;padding:11px;background:#fff;border:1px solid #dbe4ef;border-radius:12px;box-shadow:0 4px 14px rgba(15,35,65,.045);margin-bottom:9px}
.prm-field label{display:block;margin-bottom:4px;color:#53677f;font-size:7px;font-weight:900;text-transform:uppercase}
.prm-select,.prm-input{width:100%;height:36px;padding:0 10px;border:1px solid #cad7e6;border-radius:8px;background:#fff;color:#203750;outline:none}
.prm-select:focus,.prm-input:focus{border-color:#65a7ff;box-shadow:0 0 0 3px rgba(22,119,255,.07)}
.prm-info{align-self:center;color:#6e7e92;font-size:8px;line-height:1.45}
.prm-btn{height:35px;border:0;border-radius:8px;padding:0 12px;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;font-size:8px;font-weight:900;cursor:pointer;white-space:nowrap}
.prm-btn.blue{background:#1677ff;color:#fff}.prm-btn.gray{background:#eef3f8;color:#334a64}.prm-btn.green{background:#0b9654;color:#fff}

.prm-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:9px}
.prm-kpi{padding:10px 12px;border:1px solid #dbe4ef;border-radius:11px;background:#fff;box-shadow:0 3px 11px rgba(15,35,65,.04)}
.prm-kpi span{display:block;color:#74849a;font-size:7px;font-weight:900;text-transform:uppercase}
.prm-kpi strong{display:block;margin-top:2px;color:#152942;font-size:20px}
.prm-kpi small{display:block;margin-top:1px;color:#99a4b3;font-size:7px}

.prm-note{display:flex;gap:8px;align-items:flex-start;padding:9px 11px;border:1px solid #cfe0f4;border-radius:9px;background:#f6f9fd;color:#48627f;font-size:8px;line-height:1.5;margin-bottom:9px}
.prm-note b{color:#173a66}

.prm-panel{background:#fff;border:1px solid #dbe4ef;border-radius:12px;overflow:hidden;box-shadow:0 5px 15px rgba(15,35,65,.045)}
.prm-head{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 12px;border-bottom:1px solid #e4eaf1;background:#fbfcfe}
.prm-head h2{margin:0;color:#18304f;font-size:12px}.prm-head p{margin:2px 0 0;color:#8492a4;font-size:7px}
.prm-count{padding:5px 8px;border-radius:999px;background:#f0f5fb;color:#45617f;font-size:7px;font-weight:900}

.prm-table-wrap{max-height:470px;overflow:auto;scrollbar-gutter:stable}
.prm-table{width:100%;min-width:1000px;border-collapse:collapse;font-size:8px}
.prm-table th{position:sticky;top:0;z-index:3;padding:8px;background:#f4f7fb;border-bottom:1px solid #dce5ef;color:#64748a;font-size:7px;text-transform:uppercase;text-align:left}
.prm-table td{padding:9px 8px;border-bottom:1px solid #edf1f6;vertical-align:middle;color:#2b4059}
.prm-category{font-weight:900;color:#17395f;font-size:9px}
.prm-sub{display:block;margin-top:3px;color:#8492a4;font-size:7px}
.prm-chips{display:flex;flex-wrap:wrap;gap:4px;max-width:500px}
.prm-chip{padding:4px 7px;border:1px solid #dce5ef;border-radius:999px;background:#f7f9fc;color:#3d5875;font-size:7px;font-weight:800}
.prm-status{display:inline-flex;padding:4px 7px;border-radius:999px;font-size:7px;font-weight:900}
.prm-status.ok{background:#e4f7ec;color:#087d45}.prm-status.wait{background:#fff3d8;color:#8b5a00}
.prm-pic{font-weight:900;color:#193957}

.prm-unmapped{margin-top:9px;border:1px solid #f0cf8d;border-radius:11px;background:#fff9ec;overflow:hidden}
.prm-unmapped .prm-head{background:#fff8e9}
.prm-unmapped-list{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:6px;padding:9px}
.prm-unmapped-item{padding:8px;border:1px solid #ecd7a9;border-radius:8px;background:#fff;font-size:8px;color:#624c20}
.prm-unmapped-item strong{display:block;color:#4f3a11}

.prm-modal{position:fixed;inset:0;z-index:5200;display:none;align-items:center;justify-content:center;padding:16px;background:rgba(7,17,31,.64)}
.prm-modal.open{display:flex}
.prm-dialog{width:min(610px,100%);background:#fff;border-radius:14px;box-shadow:0 25px 70px rgba(0,0,0,.3);overflow:hidden}
.prm-modal-head{display:flex;align-items:center;justify-content:space-between;padding:13px 15px;border-bottom:1px solid #e4eaf1}
.prm-modal-head h3{margin:0;color:#172c49;font-size:14px}
.prm-close{border:0;background:transparent;color:#8491a3;font-size:20px;cursor:pointer}
.prm-body{padding:14px}
.prm-summary{display:grid;grid-template-columns:1fr 1fr;gap:7px;margin-bottom:11px}
.prm-summary div{padding:8px 9px;border:1px solid #e0e7ef;border-radius:8px;background:#f9fbfd}
.prm-summary span{display:block;color:#8491a3;font-size:7px}.prm-summary b{display:block;margin-top:2px;color:#263e5a;font-size:9px}
.prm-form{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.prm-form .full{grid-column:1/-1}
.prm-help{margin-top:4px;color:#8492a4;font-size:7px;line-height:1.4}
.prm-actions{display:flex;justify-content:flex-end;gap:7px;padding:11px 14px;border-top:1px solid #e4eaf1}


.prm-diagnosis{margin-top:9px;background:#fff;border:1px solid #dbe4ef;border-radius:11px;overflow:hidden}
.prm-diagnosis>summary{list-style:none;display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 12px;background:#fbfcfe;cursor:pointer}
.prm-diagnosis>summary::-webkit-details-marker{display:none}
.prm-diagnosis-title strong{display:block;color:#19304e;font-size:10px}
.prm-diagnosis-title span{display:block;margin-top:2px;color:#8492a4;font-size:7px}
.prm-diagnosis-toggle{color:#4b6685;font-size:7px;font-weight:900}
.prm-diag-table-wrap{max-height:300px;overflow:auto}
.prm-diag-table{width:100%;min-width:820px;border-collapse:collapse;font-size:8px}
.prm-diag-table th{position:sticky;top:0;z-index:2;padding:7px 8px;background:#f4f7fb;border-bottom:1px solid #dce5ef;color:#64748a;font-size:7px;text-transform:uppercase;text-align:left}
.prm-diag-table td{padding:8px;border-bottom:1px solid #edf1f6;color:#30465f}
.prm-diag-ok{color:#087d45;font-weight:900}.prm-diag-bad{color:#a46a00;font-weight:900}

@media(max-width:900px){
    .prm-toolbar{grid-template-columns:1fr}
    .prm-kpis{grid-template-columns:repeat(2,1fr)}
    .prm-unmapped-list{grid-template-columns:1fr}
}
@media(max-width:600px){
    .prm-title{display:block}.prm-badge{display:inline-flex;margin-top:7px}
    .prm-kpis{grid-template-columns:1fr}
    .prm-form,.prm-summary{grid-template-columns:1fr}
    .prm-form .full{grid-column:auto}
}
</style>

<div class="prm-title">
    <div>
        <h1>Pengaturan PIC Roster</h1>
        <p>PIC ditetapkan per bulan dan kategori posisi. Upload ulang bulan yang sama tidak mengubah PIC.</p>
    </div>
    <div class="prm-badge">MASTER RELATIONSHIP · PIC BULANAN</div>
</div>

@if(session('success'))
    <div class="prm-alert ok">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="prm-alert err">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<form class="prm-toolbar" method="GET" action="{{ route('database.atr.pic-roster') }}">
    <div class="prm-field">
        <label>Periode Roster</label>
        <select class="prm-select" name="period" onchange="this.form.submit()">
            @forelse($periodOptions as $periodOption)
                @php
                    $optionDate = \Carbon\Carbon::parse($periodOption)->startOfMonth();
                    $optionValue = $optionDate->format('Y-m');
                @endphp
                <option
                    value="{{ $optionValue }}"
                    @selected($selectedPeriod === $optionValue)
                >
                    {{ $optionDate->locale('id')->translatedFormat('F Y') }}
                </option>
            @empty
                <option value="{{ $selectedPeriod }}">
                    {{ $selectedPeriodLabel }}
                </option>
            @endforelse
        </select>
    </div>

    <div class="prm-info">
        Periode aktif: <b>{{ $selectedPeriodLabel }}</b> ·
        {{ $monthCount }} periode ATR tersedia.
        Untuk bulan baru, isi PIC secara manual. Untuk bulan yang sama, gunakan <b>EDIT</b>.
    </div>

    <a class="prm-btn gray" href="{{ route('database.atr.pic-roster') }}">
        PERIODE TERBARU
    </a>
</form>

<div class="prm-kpis">
    <div class="prm-kpi">
        <span>Kategori Aktif</span>
        <strong>{{ number_format($picRosterStats['total_categories']) }}</strong>
        <small>Kategori yang muncul di ATR {{ $selectedPeriodLabel }}.</small>
    </div>
    <div class="prm-kpi">
        <span>Sudah Diisi</span>
        <strong>{{ number_format($picRosterStats['filled_categories']) }}</strong>
        <small>PIC Roster 1 sudah tersedia.</small>
    </div>
    <div class="prm-kpi">
        <span>Belum Diisi</span>
        <strong>{{ number_format($picRosterStats['unfilled_categories']) }}</strong>
        <small>Harus diisi sebelum pemanggilan.</small>
    </div>
    <div class="prm-kpi">
        <span>PIC Unik</span>
        <strong>{{ number_format($picRosterStats['active_pics']) }}</strong>
        <small>PIC 1 + PIC 2 periode ini.</small>
    </div>
</div>

<div class="prm-note">
    <div>ℹ</div>
    <div>
        <b>Contoh master relationship:</b>
        OPERATOR HD 785 dan OPERATOR CAT 777 dapat masuk ke kategori
        <b>OPERATOR HD</b>. Yang berubah setiap bulan hanya nama PIC-nya,
        bukan kategori posisi.
    </div>
</div>

<section class="prm-panel">
    <div class="prm-head">
        <div>
            <h2>PIC Roster — {{ $selectedPeriodLabel }}</h2>
            <p>Klik ISI PIC / EDIT pada kategori yang ingin diatur.</p>
        </div>
        <span class="prm-count">{{ $categoryRows->count() }} KATEGORI</span>
    </div>

    <div class="prm-table-wrap">
        <table class="prm-table">
            <thead>
                <tr>
                    <th>Kategori Posisi</th>
                    <th>Posisi ATR dalam Kategori</th>
                    <th>Karyawan</th>
                    <th>PIC Roster 1</th>
                    <th>PIC Roster 2</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($categoryRows as $row)
                @php
                    $positionsEncoded = base64_encode(
                        json_encode(
                            $row['positions']->all(),
                            JSON_UNESCAPED_UNICODE
                        )
                    );
                @endphp
                <tr>
                    <td>
                        <span class="prm-category">{{ $row['category'] }}</span>
                        <span class="prm-sub">{{ $row['group_code'] }}</span>
                    </td>
                    <td>
                        <div class="prm-chips">
                            @foreach($row['positions']->take(4) as $position)
                                <span class="prm-chip">{{ $position }}</span>
                            @endforeach
                            @if($row['positions']->count() > 4)
                                <span class="prm-chip">
                                    +{{ $row['positions']->count() - 4 }} lainnya
                                </span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <b>{{ number_format($row['employee_count']) }}</b>
                    </td>
                    <td>
                        <span class="prm-pic">{{ $row['pic_primary'] ?: '-' }}</span>
                    </td>
                    <td>{{ $row['pic_backup'] ?: '-' }}</td>
                    <td>
                        <span class="prm-status {{ $row['is_filled'] ? 'ok' : 'wait' }}">
                            {{ $row['is_filled'] ? 'SIAP' : 'BELUM DIISI' }}
                        </span>
                    </td>
                    <td>
                        <button
                            type="button"
                            class="prm-btn {{ $row['is_filled'] ? 'gray' : 'blue' }}"
                            data-edit-roster
                            data-group-id="{{ $row['group_id'] }}"
                            data-category="{{ $row['category'] }}"
                            data-period="{{ $selectedPeriod }}"
                            data-period-label="{{ $selectedPeriodLabel }}"
                            data-pic-primary="{{ $row['pic_primary'] ?? '' }}"
                            data-pic-backup="{{ $row['pic_backup'] ?? '' }}"
                            data-positions="{{ $positionsEncoded }}"
                        >
                            {{ $row['is_filled'] ? 'EDIT' : 'ISI PIC' }}
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:28px;color:#7d8999">
                        Belum ada kategori posisi pada periode ini.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

@if($unmappedPositions->isNotEmpty())
    <section class="prm-unmapped">
        <div class="prm-head">
            <div>
                <h2>Kategori Posisi Belum Terpetakan</h2>
                <p>Ini bukan masalah PIC bulanan; master kategori posisinya yang perlu ditambahkan.</p>
            </div>
            <span class="prm-status wait">
                {{ $unmappedPositions->count() }} POSISI
            </span>
        </div>

        <div class="prm-unmapped-list">
            @foreach($unmappedPositions as $item)
                <div class="prm-unmapped-item">
                    <strong>{{ $item['position'] }}</strong>
                    {{ number_format($item['total_records']) }} karyawan
                </div>
            @endforeach
        </div>
    </section>
@endif



<details class="prm-diagnosis">
    <summary>
        <div class="prm-diagnosis-title">
            <strong>Diagnosis Mapping Posisi</strong>
            <span>
                Cek Posisi ATR → Kategori → Rule tanpa mengubah data.
            </span>
        </div>
        <div class="prm-diagnosis-toggle">
            {{ $positionDiagnostics->where('matched', false)->count() }} BELUM MATCH · LIHAT DETAIL
        </div>
    </summary>

    <div class="prm-diag-table-wrap">
        <table class="prm-diag-table">
            <thead>
                <tr>
                    <th>Posisi ATR</th>
                    <th>Karyawan</th>
                    <th>Kategori</th>
                    <th>Rule</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            @forelse($positionDiagnostics as $diag)
                <tr>
                    <td><strong>{{ $diag['position'] }}</strong></td>
                    <td>{{ number_format($diag['total_records']) }}</td>
                    <td>{{ $diag['category'] ?: '-' }}</td>
                    <td>
                        @if($diag['matched'])
                            {{ $diag['rule_type'] }} · {{ $diag['rule_pattern'] }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($diag['matched'])
                            <span class="prm-diag-ok">✓ MATCH</span>
                        @else
                            <span class="prm-diag-bad">
                                ! BELUM MATCH
                                @if(!empty($diag['reason']))
                                    · {{ $diag['reason'] }}
                                @endif
                            </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:20px;color:#7d8999">
                        Belum ada posisi ATR untuk didiagnosis.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</details>

<div class="prm-modal" id="monthlyRosterModal" aria-hidden="true">
    <div class="prm-dialog">
        <div class="prm-modal-head">
            <h3 id="monthlyRosterTitle">Isi PIC Roster Bulanan</h3>
            <button type="button" class="prm-close" data-close>×</button>
        </div>

        <form
            method="POST"
            action="{{ route('database.atr.pic-roster.monthly.save') }}"
            id="monthlyRosterForm"
        >
            @csrf

            <input type="hidden" name="period" id="prmPeriod">
            <input
                type="hidden"
                name="atr_pic_roster_group_id"
                id="prmGroupId"
            >

            <div class="prm-body">
                <div class="prm-summary">
                    <div>
                        <span>Periode</span>
                        <b id="prmPeriodLabel">-</b>
                    </div>
                    <div>
                        <span>Kategori</span>
                        <b id="prmCategory">-</b>
                    </div>
                    <div class="full" style="grid-column:1/-1">
                        <span>Posisi ATR yang masuk</span>
                        <b id="prmPositions">-</b>
                    </div>
                </div>

                <div class="prm-form">
                    <div class="prm-field">
                        <label>PIC Roster 1 *</label>
                        <select
                            class="prm-select"
                            name="pic_primary"
                            id="prmPrimary"
                            required
                        >
                            <option value="">Pilih PIC Roster 1</option>
                            @foreach($picOptions as $pic)
                                <option value="{{ $pic }}">{{ $pic }}</option>
                            @endforeach
                        </select>
                        <div class="prm-help">
                            Wajib dipilih dari daftar agar nama roster konsisten.
                        </div>
                    </div>

                    <div class="prm-field">
                        <label>PIC Roster 2 — Opsional</label>
                        <select
                            class="prm-select"
                            name="pic_backup"
                            id="prmBackup"
                        >
                            <option value="">Tidak ada PIC Roster 2</option>
                            @foreach($picOptions as $pic)
                                <option value="{{ $pic }}">{{ $pic }}</option>
                            @endforeach
                        </select>
                        <div class="prm-help">
                            Opsional. Jika dipilih harus berbeda dari PIC Roster 1.
                        </div>
                    </div>
                </div>
            </div>

            <div class="prm-actions">
                <button type="button" class="prm-btn gray" data-close>
                    BATAL
                </button>
                <button class="prm-btn blue" type="submit">
                    SIMPAN PIC {{ mb_strtoupper($selectedPeriodLabel) }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('monthlyRosterModal');
    const title = document.getElementById('monthlyRosterTitle');
    const period = document.getElementById('prmPeriod');
    const groupId = document.getElementById('prmGroupId');
    const periodLabel = document.getElementById('prmPeriodLabel');
    const category = document.getElementById('prmCategory');
    const positions = document.getElementById('prmPositions');
    const primary = document.getElementById('prmPrimary');
    const backup = document.getElementById('prmBackup');

    function decodePositions(value) {
        if (!value) {
            return [];
        }

        try {
            const binary = atob(value);
            const bytes = Uint8Array.from(
                binary,
                char => char.charCodeAt(0)
            );

            return JSON.parse(
                new TextDecoder('utf-8').decode(bytes)
            );
        } catch (error) {
            return [];
        }
    }

    function openModal(button) {
        const positionList = decodePositions(
            button.dataset.positions
        );

        period.value = button.dataset.period || '';
        groupId.value = button.dataset.groupId || '';

        periodLabel.textContent =
            button.dataset.periodLabel || '-';

        category.textContent =
            button.dataset.category || '-';

        positions.textContent =
            positionList.length
                ? positionList.join(' · ')
                : '-';

        primary.value =
            button.dataset.picPrimary || '';

        backup.value =
            button.dataset.picBackup || '';

        title.textContent =
            primary.value
                ? 'Edit PIC Roster Bulanan'
                : 'Isi PIC Roster Bulanan';

        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');

        window.setTimeout(
            () => primary.focus(),
            50
        );
    }

    function closeModal() {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
    }

    document
        .querySelectorAll('[data-edit-roster]')
        .forEach(function (button) {
            button.addEventListener(
                'click',
                function () {
                    openModal(this);
                }
            );
        });

    document
        .querySelectorAll('[data-close]')
        .forEach(function (button) {
            button.addEventListener(
                'click',
                closeModal
            );
        });

    modal.addEventListener(
        'click',
        function (event) {
            if (event.target === modal) {
                closeModal();
            }
        }
    );

    document.addEventListener(
        'keydown',
        function (event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        }
    );
});
</script>