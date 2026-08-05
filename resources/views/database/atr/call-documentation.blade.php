@php
    use Carbon\Carbon;
    $periodValue = $period ? Carbon::parse($period)->format('Y-m') : '';
@endphp

<style>
.atrc-title{margin-bottom:12px}.atrc-title h1{font-size:24px;margin:0;color:#10213d}.atrc-title p{margin:3px 0 0;color:#60708a;font-size:12px}.atrc-kpis{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:12px}.atrc-kpi{background:#fff;border:1px solid #d9e2ee;border-radius:13px;box-shadow:0 5px 16px rgba(15,35,65,.05);padding:15px;display:flex;align-items:center;gap:12px}.atrc-kpi i{width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-style:normal;font-size:20px}.atrc-kpi.total i{background:#e9f2ff}.atrc-kpi.done i{background:#dff7ea}.atrc-kpi.pending i{background:#ffe4e8}.atrc-kpi small{display:block;color:#718099;font-size:8px;font-weight:900}.atrc-kpi strong{font-size:24px;color:#14213a}.atrc-panel{background:#fff;border:1px solid #d9e2ee;border-radius:14px;box-shadow:0 6px 18px rgba(15,35,65,.06);margin-bottom:12px;overflow:hidden}.atrc-filter{display:grid;grid-template-columns:190px minmax(210px,1fr) minmax(240px,1.4fr) 160px auto;gap:9px;padding:14px}.atrc-field label{display:block;font-size:9px;font-weight:900;color:#2e415c;margin-bottom:5px}.atrc-input,.atrc-select{width:100%;height:39px;border:1px solid #cbd7e6;border-radius:9px;padding:0 11px;background:#fff}.atrc-actions{display:flex;gap:6px;align-items:end}.atrc-btn{height:39px;padding:0 14px;border:0;border-radius:9px;font-size:10px;font-weight:900;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;cursor:pointer}.atrc-btn.primary{background:#1677ff;color:#fff}.atrc-btn.light{background:#eef3f8;color:#2d405b}.atrc-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:11px;padding:12px}.atrc-card{border:1px solid #dfe7f1;border-radius:13px;padding:13px;background:#fff;position:relative}.atrc-card.called{border-color:#bfe8cf;background:#fbfffc}.atrc-person{display:flex;gap:10px;align-items:flex-start}.atrc-avatar{width:42px;height:42px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#fff1c6;color:#875e00;font-weight:900;flex:none}.atrc-person h3{font-size:12px;margin:0;color:#172640}.atrc-person p{font-size:9px;color:#6e7e94;margin:2px 0}.atrc-score{margin-left:auto;text-align:right}.atrc-score strong{font-size:22px;color:#e6384b}.atrc-score small{display:block;font-size:7px;color:#8c99aa}.atrc-detail{margin-top:11px;background:#f7f9fc;border-radius:9px;padding:9px;font-size:9px}.atrc-line{display:flex;justify-content:space-between;gap:10px;margin-bottom:5px}.atrc-line:last-child{margin-bottom:0}.atrc-line span{color:#718099}.atrc-line b{color:#233650;text-align:right}.atrc-card-actions{display:flex;gap:6px;margin-top:10px}.atrc-call{flex:1;height:34px;border:0;border-radius:8px;background:#e6384b;color:#fff;font-size:9px;font-weight:900;cursor:pointer}.atrc-print{height:34px;padding:0 10px;border-radius:8px;background:#e6f7ed;color:#0a8347;text-decoration:none;font-size:9px;font-weight:900;display:inline-flex;align-items:center}.atrc-status{position:absolute;right:10px;top:10px;padding:4px 7px;border-radius:999px;background:#dff7ea;color:#078446;font-size:7px;font-weight:900}.atrc-empty{padding:45px;text-align:center;color:#7b899c}.atrc-pages{padding:12px 15px;border-top:1px solid #e7edf4}.atrc-flash{padding:11px 14px;border-radius:10px;margin-bottom:10px;font-size:11px}.atrc-flash.success{background:#e5f8ed;color:#087b42;border:1px solid #bcebd0}.atrc-flash.error{background:#ffe8eb;color:#b11d32;border:1px solid #ffc8d0}
.atrc-modal{position:fixed;inset:0;background:rgba(7,18,35,.63);display:none;align-items:center;justify-content:center;z-index:5000;padding:16px}.atrc-modal.open{display:flex}.atrc-dialog{background:#fff;border-radius:17px;width:min(720px,100%);max-height:94vh;overflow:auto;box-shadow:0 25px 70px rgba(0,0,0,.28)}.atrc-modal-head{display:flex;justify-content:space-between;align-items:center;padding:17px 19px;border-bottom:1px solid #e1e8f1}.atrc-modal-head h2{font-size:17px;margin:0;color:#172640}.atrc-close{border:0;background:none;font-size:22px;color:#8a98aa;cursor:pointer}.atrc-modal-body{padding:17px}.atrc-summary{background:#f5f8fc;border-radius:11px;padding:12px;margin-bottom:14px;display:grid;grid-template-columns:repeat(2,1fr);gap:7px}.atrc-summary div{display:flex;justify-content:space-between;gap:10px;font-size:10px}.atrc-summary span{color:#77869b}.atrc-summary b{color:#1c304c;text-align:right}.atrc-form-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:11px}.atrc-form-field.full{grid-column:1/-1}.atrc-form-field label{display:block;font-size:9px;font-weight:900;color:#344760;margin-bottom:5px;text-transform:uppercase}.atrc-form-field input,.atrc-form-field select,.atrc-form-field textarea{width:100%;border:1px solid #cbd7e6;border-radius:9px;padding:10px;font:inherit;font-size:11px}.atrc-form-field textarea{min-height:120px;resize:vertical}.atrc-materials{display:flex;gap:10px;flex-wrap:wrap}.atrc-material{border:1px solid #d7e1ed;border-radius:9px;padding:9px 12px;font-size:10px;font-weight:800}.atrc-modal-actions{display:flex;gap:8px;justify-content:flex-end;padding:14px 19px;border-top:1px solid #e1e8f1}.atrc-save{height:39px;border:0;border-radius:9px;padding:0 17px;background:#e6384b;color:#fff;font-weight:900;font-size:10px;cursor:pointer}.atrc-cancel{height:39px;border:1px solid #cbd7e6;border-radius:9px;padding:0 17px;background:#fff;color:#344760;font-weight:900;font-size:10px;cursor:pointer}
@media(max-width:1100px){.atrc-grid{grid-template-columns:repeat(2,1fr)}.atrc-filter{grid-template-columns:1fr 1fr}.atrc-actions{grid-column:1/-1}}@media(max-width:700px){.atrc-kpis{grid-template-columns:1fr}.atrc-grid{grid-template-columns:1fr}.atrc-filter{grid-template-columns:1fr}.atrc-actions{grid-column:auto}.atrc-form-grid,.atrc-summary{grid-template-columns:1fr}}
</style>

<div class="atrc-title">
    <h1>Dokumentasi Pemanggilan</h1>
    <p>Digitalisasi form Coaching &amp; Counseling No. Dokumen {{ config('atr.document_number') }}.</p>
</div>

@if (session('success'))<div class="atrc-flash success">{{ session('success') }}</div>@endif
@if ($errors->any())<div class="atrc-flash error">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

<div class="atrc-kpis">
    <article class="atrc-kpi total"><i>☎</i><div><small>TOTAL PEMANGGILAN</small><strong>{{ number_format($callStats['total']) }}</strong></div></article>
    <article class="atrc-kpi pending"><i>!</i><div><small>BELUM DOKUMENTASI</small><strong>{{ number_format($callStats['belum']) }}</strong></div></article>
    <article class="atrc-kpi done"><i>✓</i><div><small>SUDAH SELESAI</small><strong>{{ number_format($callStats['sudah']) }}</strong></div></article>
</div>

<section class="atrc-panel">
    <form class="atrc-filter" method="GET" action="{{ route('database.atr.calls') }}">
        <div class="atrc-field"><label>Periode</label><select class="atrc-select" name="period">@foreach($periodOptions as $option)<option value="{{ $option->format('Y-m') }}" @selected($periodValue === $option->format('Y-m'))>{{ $option->locale('id')->translatedFormat('F Y') }}</option>@endforeach</select></div>
        <div class="atrc-field"><label>Jabatan</label><select class="atrc-select" name="job_title"><option value="">Semua Jabatan</option>@foreach($jobOptions as $job)<option value="{{ $job }}" @selected(request('job_title')===$job)>{{ $job }}</option>@endforeach</select></div>
        <div class="atrc-field"><label>Cari Karyawan</label><input class="atrc-input" name="search" value="{{ request('search') }}" placeholder="Cari NRP atau nama..."></div>
        <div class="atrc-field"><label>Status Dokumentasi</label><select class="atrc-select" name="call_status"><option value="">Semua Status</option><option value="belum" @selected(request('call_status')==='belum')>Belum</option><option value="sudah" @selected(request('call_status')==='sudah')>Sudah</option></select></div>
        <div class="atrc-actions"><button class="atrc-btn primary">TERAPKAN</button><a class="atrc-btn light" href="{{ route('database.atr.calls') }}">RESET</a></div>
    </form>
</section>

<section class="atrc-panel">
    @if ($records->isEmpty())
        <div class="atrc-empty">Tidak ada karyawan dengan status PEMANGGILAN pada filter ini.</div>
    @else
        <div class="atrc-grid">
            @foreach ($records as $record)
                <article class="atrc-card {{ $record->latestCoaching ? 'called' : '' }}">
                    @if($record->latestCoaching)<span class="atrc-status">SELESAI</span>@endif
                    <div class="atrc-person">
                        <div class="atrc-avatar">{{ strtoupper(substr($record->employee_name,0,2)) }}</div>
                        <div><h3>{{ $record->employee_name }}</h3><p>{{ $record->job_title ?: '-' }}</p><p>NRP: {{ $record->nrp }}</p></div>
                        <div class="atrc-score"><strong>{{ number_format((float)$record->atr,1) }}%</strong><small>ATR</small></div>
                    </div>
                    <div class="atrc-detail">
                        <div class="atrc-line"><span>Periode</span><b>{{ $record->period->locale('id')->translatedFormat('F Y') }}</b></div>
                        <div class="atrc-line"><span>Sakit / Izin / Alpa</span><b>{{ $record->sick }} / {{ $record->permission }} / {{ $record->alpha }}</b></div>
                        <div class="atrc-line"><span>Site</span><b>{{ $record->site ?: '-' }}</b></div>
                    </div>
                    <div class="atrc-card-actions">
                        <button type="button" class="atrc-call" data-call="{{ json_encode([
                            'id' => $record->id,
                            'nrp' => $record->nrp,
                            'name' => $record->employee_name,
                            'job' => $record->job_title,
                            'period' => $record->period->locale('id')->translatedFormat('F Y'),
                            'atr' => number_format((float) $record->atr, 1) . '%',
                            'sia' => $record->sick . ' / ' . $record->permission . ' / ' . $record->alpha,
                        ], JSON_UNESCAPED_UNICODE) }}">{{ $record->latestCoaching ? 'BUAT DOKUMENTASI BARU' : 'LAKUKAN PEMANGGILAN' }}</button>
                        @if($record->latestCoaching)
                            <a target="_blank" class="atrc-print" href="{{ route('database.atr.coaching.print',$record->latestCoaching) }}">CETAK</a>
                            @php($evidence = $record->latestCoaching->attachments->firstWhere('type', 'EVIDENCE'))
                            @if($evidence)
                                <a target="_blank" class="atrc-print" href="{{ route('database.atr.attachments.show', [$record->latestCoaching, $evidence]) }}">BUKTI</a>
                            @endif
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
        <div class="atrc-pages">{{ $records->links() }}</div>
    @endif
</section>

<div class="atrc-modal" id="atrcModal" aria-hidden="true">
    <div class="atrc-dialog">
        <div class="atrc-modal-head"><h2>🔔 Coaching &amp; Counseling ATR</h2><button class="atrc-close" type="button" id="atrcClose">×</button></div>
        <form method="POST" action="{{ route('database.atr.calls.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="atr_record_id" id="atrcRecordId" value="{{ old('atr_record_id') }}">
            <div class="atrc-modal-body">
                <div class="atrc-summary">
                    <div><span>Nama</span><b id="atrcName">-</b></div><div><span>NRP / Jabatan</span><b id="atrcNrpJob">-</b></div>
                    <div><span>Periode</span><b id="atrcPeriod">-</b></div><div><span>ATR · S/I/A</span><b id="atrcAtr">-</b></div>
                </div>
                <div class="atrc-form-grid">
                    <div class="atrc-form-field"><label>Tanggal</label><input type="date" name="coaching_date" value="{{ old('coaching_date', now()->format('Y-m-d')) }}" required></div>
                    <div class="atrc-form-field"><label>Shift</label><select name="shift" required><option value="DAY" @selected(old('shift')==='DAY')>DAY</option><option value="NIGHT" @selected(old('shift')==='NIGHT')>NIGHT</option><option value="NON SHIFT" @selected(old('shift')==='NON SHIFT')>NON SHIFT</option></select></div>
                    <div class="atrc-form-field"><label>Lokasi</label><input name="location" value="{{ old('location') }}" placeholder="Contoh: Office Produksi" required></div>
                    <div class="atrc-form-field"><label>Waktu</label><input type="time" name="coaching_time" value="{{ old('coaching_time', now()->format('H:i')) }}" required></div>
                    <div class="atrc-form-field full"><label>Materi</label><div class="atrc-materials"><label class="atrc-material"><input type="checkbox" name="material_personal" value="1" @checked(old('material_personal'))> PRIBADI</label><label class="atrc-material"><input type="checkbox" name="material_family" value="1" @checked(old('material_family'))> KELUARGA</label><label class="atrc-material"><input type="checkbox" name="material_work" value="1" @checked(old('material_work'))> PEKERJAAN</label></div></div>
                    <div class="atrc-form-field full"><label>Keterangan / Hasil Coaching</label><textarea name="notes" placeholder="Tuliskan penyebab, pembahasan, komitmen, dan tindak lanjut..." required>{{ old('notes') }}</textarea></div>
                    <div class="atrc-form-field"><label>Dibuat Oleh</label><input name="created_by_name" value="{{ old('created_by_name', auth()->user()?->name ?? auth()->user()?->email) }}" required></div>
                    <div class="atrc-form-field"><label>Bukti Pemanggilan *</label><input type="file" name="evidence" accept=".jpg,.jpeg,.png,.pdf" required></div>
                    <div class="atrc-form-field"><label>Tanda Tangan Karyawan</label><input type="file" name="employee_signature" accept=".jpg,.jpeg,.png"></div>
                    <div class="atrc-form-field"><label>Tanda Tangan Pembuat</label><input type="file" name="coach_signature" accept=".jpg,.jpeg,.png"></div>
                </div>
            </div>
            <div class="atrc-modal-actions"><button type="button" class="atrc-cancel" id="atrcCancel">BATAL</button><button type="submit" class="atrc-save">SIMPAN DOKUMENTASI</button></div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('atrcModal');
    const close = () => { modal.classList.remove('open'); modal.setAttribute('aria-hidden','true'); };
    document.querySelectorAll('[data-call]').forEach(function (button) {
        button.addEventListener('click', function () {
            const data = JSON.parse(this.dataset.call);
            document.getElementById('atrcRecordId').value = data.id;
            document.getElementById('atrcName').textContent = data.name;
            document.getElementById('atrcNrpJob').textContent = data.nrp + ' / ' + (data.job || '-');
            document.getElementById('atrcPeriod').textContent = data.period;
            document.getElementById('atrcAtr').textContent = data.atr + ' · ' + data.sia;
            modal.classList.add('open'); modal.setAttribute('aria-hidden','false');
        });
    });
    document.getElementById('atrcClose')?.addEventListener('click', close);
    document.getElementById('atrcCancel')?.addEventListener('click', close);
    modal?.addEventListener('click', function (event) { if (event.target === modal) close(); });
    document.addEventListener('keydown', function (event) { if (event.key === 'Escape') close(); });
    @if($errors->any() && old('atr_record_id'))
        const oldRecordId = {{ (int) old('atr_record_id') }};
        const oldButton = Array.from(document.querySelectorAll('[data-call]'))
            .find(function (button) {
                try { return JSON.parse(button.dataset.call).id === oldRecordId; }
                catch (error) { return false; }
            });
        oldButton?.click();
    @endif
});
</script>
