@php
    use Illuminate\Support\Str;
@endphp

<style>
.pr-title{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;margin-bottom:12px}
.pr-title h1{margin:0;color:#10213d;font-size:24px}.pr-title p{margin:4px 0 0;color:#65758c;font-size:11px}
.pr-badge{padding:7px 10px;border-radius:999px;border:1px solid #cfe0f4;background:#f4f8fd;color:#315a88;font-size:8px;font-weight:900}
.pr-flash{padding:10px 12px;border-radius:10px;margin-bottom:10px;font-size:10px}.pr-flash.ok{background:#e7f8ee;border:1px solid #bdebcf;color:#087b42}.pr-flash.err{background:#ffecef;border:1px solid #ffc8d0;color:#b11d32}
.pr-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:12px}.pr-kpi{background:#fff;border:1px solid #dbe4ef;border-radius:13px;padding:13px 14px;box-shadow:0 5px 15px rgba(15,35,65,.05)}.pr-kpi small{display:block;color:#74839a;font-size:8px;font-weight:900}.pr-kpi strong{display:block;color:#152640;font-size:24px;margin-top:3px}.pr-kpi em{display:block;font-style:normal;color:#95a1b1;font-size:8px}
.pr-panel{background:#fff;border:1px solid #dbe4ef;border-radius:13px;box-shadow:0 5px 16px rgba(15,35,65,.05);margin-bottom:12px;overflow:hidden}.pr-head{display:flex;justify-content:space-between;align-items:center;padding:12px 14px;border-bottom:1px solid #e5ebf2;background:#fbfcfe}.pr-head h2{font-size:13px;margin:0;color:#182b48}.pr-head p{margin:3px 0 0;color:#7a8799;font-size:8px}
.pr-body{padding:13px}.pr-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.pr-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}.pr-form .full{grid-column:1/-1}.pr-field label{display:block;font-size:8px;font-weight:900;color:#344961;margin-bottom:4px;text-transform:uppercase}.pr-input,.pr-select{width:100%;height:37px;border:1px solid #cbd8e7;border-radius:8px;padding:0 10px;background:#fff;color:#213852}.pr-btn{height:36px;border:0;border-radius:8px;padding:0 12px;font-size:9px;font-weight:900;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center}.pr-btn.blue{background:#1677ff;color:#fff}.pr-btn.green{background:#0d9555;color:#fff}.pr-btn.red{background:#fff0f2;color:#bd2639;border:1px solid #efb8c0}.pr-btn.gray{background:#eef3f8;color:#31475f}.pr-btn.amber{background:#fff2d7;color:#865500;border:1px solid #f2d094}
.pr-test{display:grid;grid-template-columns:1fr auto;gap:8px}.pr-result{margin-top:10px;border-radius:10px;padding:11px;border:1px solid #dce5ef;background:#f8fafc}.pr-result.ok{border-color:#bce8cf;background:#f0faf4}.pr-result.warn{border-color:#f2d499;background:#fff9ec}.pr-result-grid{display:grid;grid-template-columns:150px 1fr;gap:5px 10px;font-size:9px}.pr-result-grid span{color:#718198}.pr-result-grid b{color:#203650}
.pr-table-wrap{overflow:auto}.pr-table{width:100%;border-collapse:collapse;font-size:9px}.pr-table th{background:#f4f7fb;color:#617088;text-transform:uppercase;font-size:7px;letter-spacing:.2px;text-align:left;padding:8px;border-bottom:1px solid #dde5ef}.pr-table td{padding:9px 8px;border-bottom:1px solid #edf1f6;vertical-align:top;color:#253950}.pr-table tr:last-child td{border-bottom:0}.pr-code{font-weight:900;color:#193b68}.pr-pill{display:inline-flex;padding:4px 7px;border-radius:999px;font-size:7px;font-weight:900}.pr-pill.on{background:#e4f7ec;color:#087d45}.pr-pill.off{background:#eef1f5;color:#6f7d90}.pr-pill.exact{background:#e9f2ff;color:#1c63ac}.pr-pill.keyword{background:#fff2d8;color:#8a5b00}.pr-actions{display:flex;gap:5px;flex-wrap:wrap}
.pr-modal{position:fixed;inset:0;background:rgba(8,18,34,.63);display:none;align-items:center;justify-content:center;z-index:5100;padding:16px}.pr-modal.open{display:flex}.pr-dialog{width:min(650px,100%);max-height:92vh;overflow:auto;background:#fff;border-radius:15px;box-shadow:0 25px 70px rgba(0,0,0,.3)}.pr-modal-head{display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-bottom:1px solid #e4eaf1}.pr-modal-head h3{margin:0;font-size:15px;color:#172b48}.pr-close{border:0;background:none;font-size:20px;color:#8390a2;cursor:pointer}.pr-modal-body{padding:15px}.pr-modal-actions{padding:12px 15px;border-top:1px solid #e4eaf1;display:flex;justify-content:flex-end;gap:7px}
.pr-diagnostics{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px}.pr-diag{padding:9px;border:1px solid #dfe7f0;border-radius:9px;background:#fafbfd}.pr-diag strong{display:block;font-size:9px;color:#1e3554}.pr-diag span{font-size:8px;color:#718199}.pr-diag.bad{border-color:#f2cd86;background:#fff9ec}
@media(max-width:1050px){.pr-kpis{grid-template-columns:repeat(2,1fr)}.pr-grid{grid-template-columns:1fr}.pr-diagnostics{grid-template-columns:repeat(2,1fr)}}@media(max-width:650px){.pr-kpis{grid-template-columns:1fr}.pr-form{grid-template-columns:1fr}.pr-form .full{grid-column:auto}.pr-diagnostics{grid-template-columns:1fr}}
</style>

<div class="pr-title">
    <div>
        <h1>Pengaturan PIC Roster</h1>
        <p>Master Auto PIC ATR. Posisi karyawan dibaca melalui rule EXACT / KEYWORD, sehingga model unit baru tidak perlu menambah source code.</p>
    </div>
    <div class="pr-badge">MASTER AUTO PIC · ATR PRODUKSI</div>
</div>

@if(session('success'))
    <div class="pr-flash ok">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="pr-flash err">
        @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
@endif

<div class="pr-kpis">
    <div class="pr-kpi"><small>TOTAL KELOMPOK</small><strong>{{ $picRosterStats['total_groups'] }}</strong><em>Master kelompok unit.</em></div>
    <div class="pr-kpi"><small>KELOMPOK AKTIF</small><strong>{{ $picRosterStats['active_assignments'] }}</strong><em>Siap digunakan engine.</em></div>
    <div class="pr-kpi"><small>PIC AKTIF</small><strong>{{ $picRosterStats['active_pics'] }}</strong><em>Nama PIC unik aktif.</em></div>
    <div class="pr-kpi"><small>POSISI BELUM TERPETAKAN</small><strong>{{ $picRosterStats['unmapped_positions'] }}</strong><em>Perlu dibuat rule.</em></div>
</div>

<section class="pr-panel">
    <div class="pr-head">
        <div><h2>Simulator Auto PIC</h2><p>Uji posisi apa pun sebelum rule digunakan pada Dokumentasi Pemanggilan.</p></div>
    </div>
    <div class="pr-body">
        <form class="pr-test" method="GET" action="{{ route('database.atr.pic-roster') }}">
            <input class="pr-input" name="test_position" value="{{ $testPosition }}" placeholder="Contoh: OPERATOR PC 1250 / OPERATOR DZ D375 / OPERATOR WATER TRUCK HD">
            <button class="pr-btn blue">TEST POSISI</button>
        </form>

        @if($testResult)
            <div class="pr-result {{ $testResult['matched'] ? 'ok' : 'warn' }}">
                <div class="pr-result-grid">
                    <span>Posisi</span><b>{{ $testResult['position'] }}</b>
                    <span>Hasil</span><b>{{ $testResult['matched'] ? 'PIC DITEMUKAN' : 'PIC BELUM TERDAFTAR' }}</b>
                    <span>Kelompok</span><b>{{ $testResult['group_label'] ?? '-' }}</b>
                    <span>PIC Utama</span><b>{{ $testResult['pic_primary'] }}</b>
                    <span>PIC Backup</span><b>{{ $testResult['pic_backup'] ?? '-' }}</b>
                    <span>Rule Terpakai</span><b>{{ $testResult['rule_type'] ?? '-' }} · {{ $testResult['rule_pattern'] ?? '-' }} · Priority {{ $testResult['priority'] ?? '-' }}</b>
                </div>
            </div>
        @endif
    </div>
</section>

<div class="pr-grid">
    <section class="pr-panel">
        <div class="pr-head">
            <div><h2>Kelompok PIC</h2><p>PIC utama/backup dan masa berlaku roster.</p></div>
            <button type="button" class="pr-btn blue" data-open="group-create">TAMBAH KELOMPOK</button>
        </div>
        <div class="pr-table-wrap">
            <table class="pr-table">
                <thead><tr><th>Kelompok</th><th>PIC</th><th>Efektif</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                @foreach($rosterGroups as $group)
                    <tr>
                        <td><span class="pr-code">{{ $group->code }}</span><br>{{ $group->label }}</td>
                        <td><strong>{{ $group->pic_primary }}</strong><br><span style="color:#8190a3">Backup: {{ $group->pic_backup ?: '-' }}</span></td>
                        <td>{{ $group->effective_from?->format('d-m-Y') ?? '-' }}<br>s/d {{ $group->effective_to?->format('d-m-Y') ?? 'seterusnya' }}</td>
                        <td><span class="pr-pill {{ $group->is_active ? 'on' : 'off' }}">{{ $group->is_active ? 'AKTIF' : 'NONAKTIF' }}</span></td>
                        <td>
                            <div class="pr-actions">
                                <button type="button" class="pr-btn gray"
                                    data-edit-group='@json([
                                        "id"=>$group->id,"code"=>$group->code,"label"=>$group->label,
                                        "pic_primary"=>$group->pic_primary,"pic_backup"=>$group->pic_backup,
                                        "effective_from"=>$group->effective_from?->format("Y-m-d"),
                                        "effective_to"=>$group->effective_to?->format("Y-m-d"),
                                        "url"=>route("database.atr.pic-roster.groups.update",$group),
                                    ])'>EDIT</button>
                                <form method="POST" action="{{ route('database.atr.pic-roster.groups.toggle',$group) }}">
                                    @csrf
                                    <button class="pr-btn {{ $group->is_active ? 'red' : 'green' }}">{{ $group->is_active ? 'NONAKTIFKAN' : 'AKTIFKAN' }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="pr-panel">
        <div class="pr-head">
            <div><h2>Rule Auto PIC</h2><p>EXACT untuk override khusus; KEYWORD untuk keluarga unit seperti PC/DZ/DOZER.</p></div>
            <button type="button" class="pr-btn blue" data-open="rule-create">TAMBAH RULE</button>
        </div>
        <div class="pr-table-wrap">
            <table class="pr-table">
                <thead><tr><th>Rule</th><th>Kelompok</th><th>Priority</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                @foreach($rosterRules as $rule)
                    <tr>
                        <td><span class="pr-pill {{ strtolower($rule->match_type) }}">{{ $rule->match_type }}</span><br><strong>{{ $rule->pattern }}</strong></td>
                        <td>{{ $rule->group?->label ?? '-' }}</td>
                        <td>{{ $rule->priority }}</td>
                        <td><span class="pr-pill {{ $rule->is_active ? 'on' : 'off' }}">{{ $rule->is_active ? 'AKTIF' : 'NONAKTIF' }}</span></td>
                        <td>
                            <div class="pr-actions">
                                <button type="button" class="pr-btn gray"
                                    data-edit-rule='@json([
                                        "id"=>$rule->id,"group_id"=>$rule->atr_pic_roster_group_id,
                                        "match_type"=>$rule->match_type,"pattern"=>$rule->pattern,
                                        "priority"=>$rule->priority,
                                        "url"=>route("database.atr.pic-roster.rules.update",$rule),
                                    ])'>EDIT</button>
                                <form method="POST" action="{{ route('database.atr.pic-roster.rules.toggle',$rule) }}">
                                    @csrf
                                    <button class="pr-btn {{ $rule->is_active ? 'red' : 'green' }}">{{ $rule->is_active ? 'NONAKTIFKAN' : 'AKTIFKAN' }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>

<section class="pr-panel">
    <div class="pr-head">
        <div><h2>Diagnosis Posisi ATR Aktif</h2><p>Posisi yang belum mendapat PIC harus diselesaikan sebelum dokumentasi pemanggilan.</p></div>
        <span class="pr-pill {{ $unmappedPositions->isEmpty() ? 'on' : 'keyword' }}">{{ $unmappedPositions->count() }} BELUM TERPETAKAN</span>
    </div>
    <div class="pr-body">
        @if($rosterDiagnostics->isEmpty())
            <div style="text-align:center;color:#7d8999;padding:20px">Belum ada posisi dari import ATR aktif.</div>
        @else
            <div class="pr-diagnostics">
                @foreach($rosterDiagnostics as $diag)
                    <div class="pr-diag {{ $diag['matched'] ? '' : 'bad' }}">
                        <strong>{{ $diag['position'] }}</strong>
                        @if($diag['matched'])
                            <span>{{ $diag['group_label'] }} → {{ $diag['pic_primary'] }} · Rule {{ $diag['rule_pattern'] }}</span>
                        @else
                            <span>PIC BELUM TERDAFTAR · buat rule EXACT/KEYWORD.</span>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

<section class="pr-panel">
    <div class="pr-head"><div><h2>Riwayat Perubahan PIC Roster</h2><p>30 aktivitas terakhir untuk audit pengaturan.</p></div></div>
    <div class="pr-table-wrap">
        <table class="pr-table">
            <thead><tr><th>Waktu</th><th>Aksi</th><th>Kelompok / Rule</th><th>Oleh</th><th>Catatan</th></tr></thead>
            <tbody>
            @forelse($rosterHistories as $history)
                <tr>
                    <td>{{ $history->created_at?->format('d-m-Y H:i') }}</td>
                    <td><strong>{{ $history->action }}</strong></td>
                    <td>{{ $history->group?->label ?? '-' }} @if($history->rule) · {{ $history->rule->pattern }} @endif</td>
                    <td>{{ $history->actor?->name ?? $history->actor?->email ?? $history->actor_name ?? '-' }}</td>
                    <td>{{ $history->notes ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;color:#7d8999">Belum ada riwayat perubahan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

{{-- CREATE/EDIT GROUP MODAL --}}
<div class="pr-modal" id="groupModal">
    <div class="pr-dialog">
        <div class="pr-modal-head"><h3 id="groupModalTitle">Tambah Kelompok PIC</h3><button type="button" class="pr-close" data-close>×</button></div>
        <form method="POST" id="groupForm" action="{{ route('database.atr.pic-roster.groups.store') }}">
            @csrf
            <div class="pr-modal-body">
                <div class="pr-form">
                    <div class="pr-field"><label>Kode Kelompok</label><input class="pr-input" name="code" id="groupCode" placeholder="EXCAVATOR" required></div>
                    <div class="pr-field"><label>Nama Kelompok</label><input class="pr-input" name="label" id="groupLabel" placeholder="Excavator / PC" required></div>
                    <div class="pr-field"><label>PIC Utama</label><input class="pr-input" name="pic_primary" id="groupPrimary" required></div>
                    <div class="pr-field"><label>PIC Backup</label><input class="pr-input" name="pic_backup" id="groupBackup"></div>
                    <div class="pr-field"><label>Efektif Dari</label><input class="pr-input" type="date" name="effective_from" id="groupFrom"></div>
                    <div class="pr-field"><label>Efektif Sampai</label><input class="pr-input" type="date" name="effective_to" id="groupTo"></div>
                </div>
            </div>
            <div class="pr-modal-actions"><button type="button" class="pr-btn gray" data-close>BATAL</button><button class="pr-btn blue">SIMPAN</button></div>
        </form>
    </div>
</div>

{{-- CREATE/EDIT RULE MODAL --}}
<div class="pr-modal" id="ruleModal">
    <div class="pr-dialog">
        <div class="pr-modal-head"><h3 id="ruleModalTitle">Tambah Rule Auto PIC</h3><button type="button" class="pr-close" data-close>×</button></div>
        <form method="POST" id="ruleForm" action="{{ route('database.atr.pic-roster.rules.store') }}">
            @csrf
            <div class="pr-modal-body">
                <div class="pr-form">
                    <div class="pr-field"><label>Kelompok</label><select class="pr-select" name="atr_pic_roster_group_id" id="ruleGroup" required>@foreach($rosterGroups as $group)<option value="{{ $group->id }}">{{ $group->label }}</option>@endforeach</select></div>
                    <div class="pr-field"><label>Tipe Match</label><select class="pr-select" name="match_type" id="ruleType"><option value="KEYWORD">KEYWORD</option><option value="EXACT">EXACT</option></select></div>
                    <div class="pr-field full"><label>Pattern</label><input class="pr-input" name="pattern" id="rulePattern" placeholder="PC / DZ / DOZER / OPERATOR PC 1250" required></div>
                    <div class="pr-field"><label>Priority</label><input class="pr-input" type="number" min="1" max="9999" name="priority" id="rulePriority" value="100" required></div>
                    <div class="pr-field"><label>Catatan</label><div style="font-size:8px;color:#738198;padding-top:10px">Angka lebih kecil = rule diperiksa lebih dahulu. EXACT selalu menang atas KEYWORD.</div></div>
                </div>
            </div>
            <div class="pr-modal-actions"><button type="button" class="pr-btn gray" data-close>BATAL</button><button class="pr-btn blue">SIMPAN</button></div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const groupModal=document.getElementById('groupModal');
    const ruleModal=document.getElementById('ruleModal');
    const groupForm=document.getElementById('groupForm');
    const ruleForm=document.getElementById('ruleForm');

    function openModal(m){m?.classList.add('open')}
    function closeAll(){document.querySelectorAll('.pr-modal.open').forEach(m=>m.classList.remove('open'))}
    document.querySelectorAll('[data-close]').forEach(b=>b.addEventListener('click',closeAll));
    document.querySelectorAll('.pr-modal').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)closeAll()}));

    document.querySelector('[data-open="group-create"]')?.addEventListener('click',function(){
        groupForm.action=@json(route('database.atr.pic-roster.groups.store'));
        ['groupCode','groupLabel','groupPrimary','groupBackup','groupFrom','groupTo'].forEach(id=>document.getElementById(id).value='');
        document.getElementById('groupModalTitle').textContent='Tambah Kelompok PIC';
        openModal(groupModal);
    });

    document.querySelectorAll('[data-edit-group]').forEach(btn=>btn.addEventListener('click',function(){
        const d=JSON.parse(btn.dataset.editGroup);
        groupForm.action=d.url;
        document.getElementById('groupCode').value=d.code||'';
        document.getElementById('groupLabel').value=d.label||'';
        document.getElementById('groupPrimary').value=d.pic_primary||'';
        document.getElementById('groupBackup').value=d.pic_backup||'';
        document.getElementById('groupFrom').value=d.effective_from||'';
        document.getElementById('groupTo').value=d.effective_to||'';
        document.getElementById('groupModalTitle').textContent='Edit Kelompok PIC';
        openModal(groupModal);
    }));

    document.querySelector('[data-open="rule-create"]')?.addEventListener('click',function(){
        ruleForm.action=@json(route('database.atr.pic-roster.rules.store'));
        document.getElementById('ruleType').value='KEYWORD';
        document.getElementById('rulePattern').value='';
        document.getElementById('rulePriority').value='100';
        document.getElementById('ruleModalTitle').textContent='Tambah Rule Auto PIC';
        openModal(ruleModal);
    });

    document.querySelectorAll('[data-edit-rule]').forEach(btn=>btn.addEventListener('click',function(){
        const d=JSON.parse(btn.dataset.editRule);
        ruleForm.action=d.url;
        document.getElementById('ruleGroup').value=d.group_id;
        document.getElementById('ruleType').value=d.match_type;
        document.getElementById('rulePattern').value=d.pattern||'';
        document.getElementById('rulePriority').value=d.priority||100;
        document.getElementById('ruleModalTitle').textContent='Edit Rule Auto PIC';
        openModal(ruleModal);
    }));

    document.addEventListener('keydown',e=>{if(e.key==='Escape')closeAll()});
});
</script>