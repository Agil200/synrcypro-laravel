@php
    use Carbon\Carbon;
    $periodValue = $period ? Carbon::parse($period)->format('Y-m') : '';
@endphp

<style>
:root{
    --atrc-navy:#10213d;
    --atrc-text:#233650;
    --atrc-muted:#718099;
    --atrc-border:#d9e2ee;
    --atrc-bg:#f7f9fc;
    --atrc-blue:#1677ff;
    --atrc-red:#e6384b;
    --atrc-green:#0b8a50;
    --atrc-amber:#a76a00;
}
.atrc-title{display:flex;justify-content:space-between;gap:18px;align-items:flex-start;margin-bottom:12px}
.atrc-title h1{font-size:24px;margin:0;color:var(--atrc-navy)}
.atrc-title p{margin:4px 0 0;color:#60708a;font-size:12px}
.atrc-title-badge{flex:none;display:inline-flex;align-items:center;gap:7px;padding:8px 11px;border:1px solid #d8e5f5;border-radius:999px;background:#f5f9ff;color:#315b8c;font-size:9px;font-weight:900}
.atrc-flash{padding:11px 14px;border-radius:10px;margin-bottom:10px;font-size:11px}
.atrc-flash.success{background:#e5f8ed;color:#087b42;border:1px solid #bcebd0}
.atrc-flash.error{background:#ffe8eb;color:#b11d32;border:1px solid #ffc8d0}

.atrc-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:12px}
.atrc-kpi{background:#fff;border:1px solid var(--atrc-border);border-radius:14px;box-shadow:0 5px 16px rgba(15,35,65,.05);padding:14px 15px;display:flex;align-items:center;gap:12px;min-height:82px}
.atrc-kpi i{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-style:normal;font-size:20px;flex:none}
.atrc-kpi.total i{background:#e9f2ff;color:#205ea8}
.atrc-kpi.pending i{background:#ffe4e8;color:#c82c3f}
.atrc-kpi.done i{background:#dff7ea;color:#087b42}
.atrc-kpi.recall i{background:#fff0cf;color:#946200}
.atrc-kpi small{display:block;color:#718099;font-size:8px;font-weight:900;letter-spacing:.25px}
.atrc-kpi strong{display:block;font-size:24px;line-height:1.05;color:#14213a;margin-top:2px}
.atrc-kpi em{display:block;font-style:normal;color:#91a0b3;font-size:8px;margin-top:3px}

.atrc-panel{background:#fff;border:1px solid var(--atrc-border);border-radius:14px;box-shadow:0 6px 18px rgba(15,35,65,.06);margin-bottom:12px;overflow:hidden}
.atrc-page-shell{height:100%;min-height:0;display:flex;flex-direction:column;overflow:hidden}
.atrc-control-zone{flex:0 0 auto;position:relative;z-index:4}
.atrc-data-zone{flex:1 1 auto;min-height:0;display:flex;overflow:hidden}
.atrc-queue-panel{flex:1 1 auto;min-height:0;margin-bottom:0;display:flex;flex-direction:column;overflow:hidden}
.atrc-queue-panel>.atrc-queue-head,
.atrc-queue-panel>.atrc-pages{flex:0 0 auto}
.atrc-queue-panel>.atrc-grid{flex:1 1 auto;min-height:0;overflow:auto;align-content:start;overscroll-behavior:contain;scrollbar-gutter:stable}
.atrc-queue-panel>.atrc-empty{flex:1 1 auto;min-height:0;display:grid;place-items:center;align-content:center}

.atrc-filter{display:grid;grid-template-columns:190px minmax(210px,1fr) minmax(240px,1.4fr) 175px auto;gap:9px;padding:14px}
.atrc-field label{display:block;font-size:9px;font-weight:900;color:#2e415c;margin-bottom:5px;text-transform:uppercase;letter-spacing:.18px}
.atrc-input,.atrc-select{width:100%;height:39px;border:1px solid #cbd7e6;border-radius:9px;padding:0 11px;background:#fff;color:#243851;outline:none}
.atrc-input:focus,.atrc-select:focus{border-color:#6aa8ff;box-shadow:0 0 0 3px rgba(22,119,255,.08)}
.atrc-actions{display:flex;gap:6px;align-items:end}
.atrc-btn{height:39px;padding:0 14px;border:0;border-radius:9px;font-size:10px;font-weight:900;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;cursor:pointer}
.atrc-btn.primary{background:var(--atrc-blue);color:#fff}
.atrc-btn.light{background:#eef3f8;color:#2d405b}

.atrc-status-tabs{display:flex;align-items:center;gap:6px;padding:0 14px 13px;flex-wrap:wrap}
.atrc-status-tab{display:inline-flex;align-items:center;gap:5px;height:30px;padding:0 10px;border:1px solid #d8e2ee;border-radius:999px;background:#fff;color:#54667f;text-decoration:none;font-size:8px;font-weight:900}
.atrc-status-tab:hover{background:#f5f8fc}
.atrc-status-tab.active{background:#edf5ff;border-color:#98c4ff;color:#125fae}
.atrc-status-tab.pending.active{background:#fff0f2;border-color:#ffc2cb;color:#bc263a}
.atrc-status-tab.done.active{background:#ebf9f1;border-color:#b7e5ca;color:#087b42}
.atrc-status-tab.recall.active{background:#fff7e6;border-color:#f4d28f;color:#926000}

.atrc-queue-head{display:flex;justify-content:space-between;align-items:center;gap:15px;padding:13px 15px;border-bottom:1px solid #e7edf4;background:#fbfcfe}
.atrc-queue-head h2{font-size:14px;color:#172640;margin:0}
.atrc-queue-head p{font-size:9px;color:#718099;margin:3px 0 0}
.atrc-queue-count{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border-radius:999px;background:#eef4fb;color:#31577e;font-size:8px;font-weight:900;white-space:nowrap}

.atrc-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:11px;padding:12px}
.atrc-card{border:1px solid #dfe7f1;border-radius:14px;background:#fff;position:relative;overflow:hidden;box-shadow:0 3px 12px rgba(24,46,78,.035)}
.atrc-card::before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;background:#e6384b}
.atrc-card.called{border-color:#bfe8cf;background:#fbfffc}
.atrc-card.called::before{background:#16a061}
.atrc-card.recall{border-color:#f2d49b;background:#fffdf8}
.atrc-card.recall::before{background:#e6a11c}
.atrc-card-body{padding:14px 14px 12px 17px}
.atrc-card-top{display:flex;gap:10px;align-items:flex-start;padding-right:0}
.atrc-avatar{width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#fff1c6;color:#875e00;font-weight:900;flex:none;font-size:12px}
.atrc-person-main{min-width:0;flex:1}
.atrc-person-main h3{font-size:12px;margin:0;color:#172640;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.atrc-person-main p{font-size:8px;color:#6e7e94;margin:3px 0 0}
.atrc-person-main .atrc-nrp{font-weight:800;color:#7a889b}
.atrc-score{margin-left:auto;text-align:right;flex:none}
.atrc-score strong{display:block;font-size:22px;color:#e6384b;line-height:1}
.atrc-score small{display:block;font-size:7px;color:#8c99aa;margin-top:3px}
.atrc-status-row{display:flex;justify-content:space-between;align-items:center;gap:8px;margin-top:11px}
.atrc-status{display:inline-flex;align-items:center;gap:5px;padding:5px 8px;border-radius:999px;font-size:7px;font-weight:900;letter-spacing:.2px}
.atrc-status.done{background:#dff7ea;color:#078446}
.atrc-status.recall{background:#fff0cf;color:#946200}
.atrc-status.pending{background:#ffe5e9;color:#bd2639}
.atrc-period-chip{font-size:8px;font-weight:900;color:#51627a}

.atrc-detail{margin-top:10px;background:#f7f9fc;border-radius:10px;padding:9px 10px;font-size:9px}
.atrc-card.called .atrc-detail{background:#f1faf5}
.atrc-card.recall .atrc-detail{background:#fff8ea}
.atrc-line{display:flex;justify-content:space-between;gap:10px;margin-bottom:6px}
.atrc-line:last-child{margin-bottom:0}
.atrc-line span{color:#718099}
.atrc-line b{color:#233650;text-align:right}
.atrc-sia{display:flex;gap:5px;justify-content:flex-end}
.atrc-sia span{min-width:26px;padding:3px 6px;border-radius:7px;background:#fff;border:1px solid #dfe7f1;color:#32465f;text-align:center;font-size:8px;font-weight:900}
.atrc-sia span b{display:block;font-size:9px;text-align:center}
.atrc-sia span small{display:block;font-size:6px;color:#8996a8}

.atrc-call-meta{margin-top:9px;padding:8px 9px;border-radius:9px;background:#eef9f2;color:#236242;font-size:8px;font-weight:700;line-height:1.45}
.atrc-card.recall .atrc-call-meta{background:#fff6e4;color:#8a5b00}
.atrc-card-actions{display:flex;gap:6px;margin-top:10px;flex-wrap:wrap}
.atrc-call{flex:1;min-width:160px;height:36px;border:0;border-radius:8px;background:#e6384b;color:#fff;font-size:9px;font-weight:900;cursor:pointer}
.atrc-call:hover{background:#d72e40}.atrc-roster-config{flex:1;min-width:160px;height:36px;border:1px solid #e5a42b;border-radius:8px;background:#fff5df;color:#8a5900;font-size:9px;font-weight:900;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center}.atrc-roster-config:hover{background:#ffedc5}
.atrc-print{height:34px;padding:0 11px;border-radius:8px;background:#e6f7ed;color:#0a8347;text-decoration:none;font-size:9px;font-weight:900;display:inline-flex;align-items:center;justify-content:center;border:1px solid #c8ebd7}
.atrc-print:hover{background:#dcf4e6}
.atrc-cancel-doc{height:34px;padding:0 10px;border:1px solid #e6384b;border-radius:8px;background:#fff;color:#c52739;font-size:9px;font-weight:900;cursor:pointer}
.atrc-cancel-doc:hover{background:#fff0f2}
.atrc-empty{padding:46px 20px;text-align:center;color:#7b899c}
.atrc-empty-icon{font-size:28px;margin-bottom:7px}
.atrc-empty strong{display:block;color:#40536d;font-size:12px;margin-bottom:4px}
.atrc-empty span{font-size:9px}
.atrc-pages{padding:10px 15px;border-top:1px solid #e7edf4;background:#fff}

.atrc-modal{position:fixed;inset:0;background:rgba(7,18,35,.63);display:none;align-items:center;justify-content:center;z-index:5000;padding:16px}
.atrc-modal.open{display:flex}
.atrc-dialog{background:#fff;border-radius:17px;width:min(720px,100%);max-height:94vh;overflow:auto;box-shadow:0 25px 70px rgba(0,0,0,.28)}
.atrc-modal-head{display:flex;justify-content:space-between;align-items:center;padding:17px 19px;border-bottom:1px solid #e1e8f1}
.atrc-modal-head h2{font-size:17px;margin:0;color:#172640}
.atrc-close{border:0;background:none;font-size:22px;color:#8a98aa;cursor:pointer}
.atrc-modal-body{padding:17px}
.atrc-summary{background:#f5f8fc;border-radius:11px;padding:12px;margin-bottom:14px;display:grid;grid-template-columns:repeat(2,1fr);gap:7px}
.atrc-summary div{display:flex;justify-content:space-between;gap:10px;font-size:10px}
.atrc-summary span{color:#77869b}
.atrc-summary b{color:#1c304c;text-align:right}
.atrc-form-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:11px}
.atrc-form-field.full{grid-column:1/-1}
.atrc-form-field label{display:block;font-size:9px;font-weight:900;color:#344760;margin-bottom:5px;text-transform:uppercase}
.atrc-form-field input,.atrc-form-field select,.atrc-form-field textarea{width:100%;border:1px solid #cbd7e6;border-radius:9px;padding:10px;font:inherit;font-size:11px}
.atrc-form-field textarea{min-height:120px;resize:vertical}
.atrc-materials{display:flex;gap:10px;flex-wrap:wrap}
.atrc-material{border:1px solid #d7e1ed;border-radius:9px;padding:9px 12px;font-size:10px;font-weight:800}
.atrc-modal-actions{display:flex;gap:8px;justify-content:flex-end;padding:14px 19px;border-top:1px solid #e1e8f1}
.atrc-save{height:39px;border:0;border-radius:9px;padding:0 17px;background:#e6384b;color:#fff;font-weight:900;font-size:10px;cursor:pointer}.atrc-save:disabled,.atrc-danger-submit:disabled{opacity:.62;cursor:not-allowed}.atrc-save.is-loading,.atrc-danger-submit.is-loading{position:relative;padding-left:34px}.atrc-save.is-loading::before,.atrc-danger-submit.is-loading::before{content:"";position:absolute;left:14px;width:12px;height:12px;border:2px solid rgba(255,255,255,.45);border-top-color:#fff;border-radius:50%;animation:atrc-spin .7s linear infinite}@keyframes atrc-spin{to{transform:rotate(360deg)}}
.atrc-cancel{height:39px;border:1px solid #cbd7e6;border-radius:9px;padding:0 17px;background:#fff;color:#344760;font-weight:900;font-size:10px;cursor:pointer}
.atrc-readonly{background:#f3f6fa!important;color:#263952;font-weight:800;cursor:not-allowed}
.atrc-signature-field{grid-column:1/-1}
.atrc-signature-box{border:1px solid #cbd7e6;border-radius:11px;background:#fff;overflow:hidden}
.atrc-signature-canvas{display:block;width:100%;height:155px;background:#fff;cursor:crosshair;touch-action:none}
.atrc-signature-tools{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:7px 9px;border-top:1px solid #e4eaf2;background:#f8fafc}
.atrc-signature-hint{font-size:9px;color:#75849a}
.atrc-signature-clear{height:28px;padding:0 10px;border:1px solid #cbd7e6;border-radius:7px;background:#fff;color:#344760;font-size:9px;font-weight:900;cursor:pointer}
.atrc-signature-clear:hover{background:#eef3f8}
.atrc-cancel-dialog{width:min(500px,100%)}
.atrc-cancel-warning{padding:12px;border:1px solid #ffd2d8;background:#fff4f5;color:#8e2431;border-radius:10px;font-size:10px;line-height:1.55;margin-bottom:12px}
.atrc-cancel-textarea{width:100%;min-height:110px;border:1px solid #cbd7e6;border-radius:9px;padding:10px;font:inherit;font-size:11px;resize:vertical}
.atrc-danger-submit{height:39px;border:0;border-radius:9px;padding:0 17px;background:#e6384b;color:#fff;font-weight:900;font-size:10px;cursor:pointer}

@media(max-width:1100px){
    .atrc-page-shell{height:auto;overflow:visible}
    .atrc-data-zone{display:block;overflow:visible}
    .atrc-queue-panel{min-height:460px}
    .atrc-queue-panel>.atrc-grid{max-height:520px}
    .atrc-kpis{grid-template-columns:repeat(2,1fr)}
    .atrc-grid{grid-template-columns:repeat(2,1fr)}
    .atrc-filter{grid-template-columns:1fr 1fr}
    .atrc-actions{grid-column:1/-1}
}
@media(max-height:760px) and (min-width:1101px){
    .atrc-page-shell{height:auto;overflow:visible}
    .atrc-data-zone{display:block;overflow:visible}
    .atrc-queue-panel{min-height:420px}
    .atrc-queue-panel>.atrc-grid{max-height:460px}
}
@media(max-width:700px){
    .atrc-title{display:block}
    .atrc-title-badge{margin-top:8px}
    .atrc-kpis{grid-template-columns:1fr}
    .atrc-grid{grid-template-columns:1fr}
    .atrc-filter{grid-template-columns:1fr}
    .atrc-actions{grid-column:auto}
    .atrc-form-grid,.atrc-summary{grid-template-columns:1fr}
    .atrc-queue-head{align-items:flex-start;flex-direction:column}
}
</style>

<div class="atrc-page-shell">
<div class="atrc-control-zone">
<div class="atrc-title">
    <div>
        <h1>Dokumentasi Pemanggilan</h1>
        <p>Work queue khusus karyawan ATR berstatus PEMANGGILAN · Coaching &amp; Counseling No. Dokumen {{ config('atr.document_number') }}.</p>
    </div>
    <div class="atrc-title-badge">ATR PRODUKSI · WORK QUEUE</div>
</div>

@if (session('success'))<div class="atrc-flash success">{{ session('success') }}</div>@endif
@if ($errors->any())<div class="atrc-flash error">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

<div class="atrc-kpis">
    <article class="atrc-kpi total">
        <i>☎</i>
        <div>
            <small>TOTAL PEMANGGILAN</small>
            <strong>{{ number_format($callStats['total']) }}</strong>
            <em>Seluruh karyawan ATR yang wajib ditindaklanjuti.</em>
        </div>
    </article>
    <article class="atrc-kpi pending">
        <i>!</i>
        <div>
            <small>BELUM DIPANGGIL</small>
            <strong>{{ number_format($callStats['belum']) }}</strong>
            <em>Belum memiliki dokumentasi aktif.</em>
        </div>
    </article>
    <article class="atrc-kpi done">
        <i>✓</i>
        <div>
            <small>SUDAH DIPANGGIL</small>
            <strong>{{ number_format($callStats['sudah']) }}</strong>
            <em>Coaching &amp; Counseling sudah terdokumentasi.</em>
        </div>
    </article>
    <article class="atrc-kpi recall">
        <i>↻</i>
        <div>
            <small>PERLU ULANG</small>
            <strong>{{ number_format($callStats['ulang'] ?? 0) }}</strong>
            <em>Dokumentasi sebelumnya dibatalkan.</em>
        </div>
    </article>
</div>

<section class="atrc-panel">
    <form class="atrc-filter" method="GET" action="{{ route('database.atr.calls') }}">
        <div class="atrc-field">
            <label>Periode</label>
            <select class="atrc-select" name="period">
                @foreach($periodOptions as $option)
                    <option value="{{ $option->format('Y-m') }}" @selected($periodValue === $option->format('Y-m'))>
                        {{ $option->locale('id')->translatedFormat('F Y') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="atrc-field">
            <label>Posisi</label>
            <select class="atrc-select" name="position">
                <option value="">Semua Posisi</option>
                @foreach($positionOptions as $position)
                    <option value="{{ $position }}" @selected(request('position')===$position)>{{ $position }}</option>
                @endforeach
            </select>
        </div>

        <div class="atrc-field">
            <label>Cari Karyawan</label>
            <input class="atrc-input" name="search" value="{{ request('search') }}" placeholder="Cari NRP atau nama karyawan...">
        </div>

        <div class="atrc-field">
            <label>Status Pemanggilan</label>
            <select class="atrc-select" name="call_status">
                <option value="">Semua Status</option>
                <option value="belum" @selected(request('call_status')==='belum')>Belum Dipanggil</option>
                <option value="sudah" @selected(request('call_status')==='sudah')>Sudah Dipanggil</option>
                <option value="ulang" @selected(request('call_status')==='ulang')>Perlu Ulang</option>
            </select>
        </div>

        <div class="atrc-actions">
            <button class="atrc-btn primary">TERAPKAN</button>
            <a class="atrc-btn light" href="{{ route('database.atr.calls') }}">RESET</a>
        </div>
    </form>

    <div class="atrc-status-tabs">
        <a class="atrc-status-tab {{ request('call_status','')==='' ? 'active' : '' }}"
           href="{{ route('database.atr.calls', array_merge(request()->except(['call_status','page']), ['call_status' => ''])) }}">
            SEMUA <strong>{{ number_format($callStats['total']) }}</strong>
        </a>
        <a class="atrc-status-tab pending {{ request('call_status')==='belum' ? 'active' : '' }}"
           href="{{ route('database.atr.calls', array_merge(request()->except(['call_status','page']), ['call_status' => 'belum'])) }}">
            ! BELUM <strong>{{ number_format($callStats['belum']) }}</strong>
        </a>
        <a class="atrc-status-tab done {{ request('call_status')==='sudah' ? 'active' : '' }}"
           href="{{ route('database.atr.calls', array_merge(request()->except(['call_status','page']), ['call_status' => 'sudah'])) }}">
            ✓ SUDAH <strong>{{ number_format($callStats['sudah']) }}</strong>
        </a>
        <a class="atrc-status-tab recall {{ request('call_status')==='ulang' ? 'active' : '' }}"
           href="{{ route('database.atr.calls', array_merge(request()->except(['call_status','page']), ['call_status' => 'ulang'])) }}">
            ↻ PERLU ULANG <strong>{{ number_format($callStats['ulang'] ?? 0) }}</strong>
        </a>
    </div>
</section>
</div>

<div class="atrc-data-zone">
<section class="atrc-panel atrc-queue-panel">
    <div class="atrc-queue-head">
        <div>
            <h2>Daftar Karyawan Pemanggilan</h2>
            <p>Hanya karyawan dengan status ATR <strong>PEMANGGILAN</strong>. AMAN dan MONITORING tetap dipantau melalui Ringkasan ATR.</p>
        </div>
        <div class="atrc-queue-count">{{ number_format($records->total()) }} DATA DITEMUKAN</div>
    </div>

    @if ($records->isEmpty())
        <div class="atrc-empty">
            <div class="atrc-empty-icon">✓</div>
            <strong>Tidak ada antrean pemanggilan pada filter ini</strong>
            <span>Ubah periode/posisi/status, atau pastikan terdapat import ATR aktif.</span>
        </div>
    @else
        <div class="atrc-grid">
            @foreach ($records as $record)
                @php
                    $activeCoaching = $record->latestCoaching;
                    $cancelledCoaching = $activeCoaching
                        ? null
                        : $record->latestCancelledCoaching;

                    $cardClass = $activeCoaching
                        ? 'called'
                        : ($cancelledCoaching ? 'recall' : '');

                    $picRoster = $activeCoaching?->created_by_name ?: '-';
                @endphp

                <article class="atrc-card {{ $cardClass }}">
                    <div class="atrc-card-body">
                        <div class="atrc-card-top">
                            <div class="atrc-avatar">{{ strtoupper(substr($record->employee_name,0,2)) }}</div>

                            <div class="atrc-person-main">
                                <h3>{{ $record->employee_name }}</h3>
                                <p>{{ $record->position ?: '-' }}</p>
                                <p class="atrc-nrp">NRP {{ $record->nrp }}</p>
                            </div>

                            <div class="atrc-score">
                                <strong>{{ number_format((float)$record->atr,1) }}%</strong>
                                <small>ATR</small>
                            </div>
                        </div>

                        <div class="atrc-status-row">
                            @if ($activeCoaching)
                                <span class="atrc-status done">✓ SELESAI</span>
                            @elseif ($cancelledCoaching)
                                <span class="atrc-status recall">↻ PERLU ULANG</span>
                            @else
                                <span class="atrc-status pending">! MENUNGGU PEMANGGILAN</span>
                            @endif

                            <span class="atrc-period-chip">{{ $record->period->locale('id')->translatedFormat('F Y') }}</span>
                        </div>

                        <div class="atrc-detail">
                            <div class="atrc-line">
                                <span>S / I / A</span>
                                <div class="atrc-sia">
                                    <span><b>{{ $record->sick }}</b><small>SAKIT</small></span>
                                    <span><b>{{ $record->permission }}</b><small>IZIN</small></span>
                                    <span><b>{{ $record->alpha }}</b><small>ALPA</small></span>
                                </div>
                            </div>
                            <div class="atrc-line"><span>Jabatan</span><b>{{ $record->job_title ?: '-' }}</b></div>
                            <div class="atrc-line"><span>Site</span><b>{{ $record->site ?: '-' }}</b></div>
                            <div class="atrc-line">
                                <span>PIC Roster</span>
                                <b style="{{ ($record->pic_roster_resolved ?? false) ? '' : 'color:#b36a00' }}">
                                    {{ $record->pic_roster_name_resolved ?? 'PIC BELUM TERDAFTAR' }}
                                </b>
                            </div>
                        </div>

                        @if ($activeCoaching)
                            <div class="atrc-call-meta">
                                ✓ Dokumentasi lengkap · selesai
                                <strong>{{ $activeCoaching->completed_at?->locale('id')->translatedFormat('d M Y H:i') ?? '-' }}</strong>
                                · PIC <strong>{{ $picRoster }}</strong>
                            </div>
                        @elseif ($cancelledCoaching)
                            <div class="atrc-call-meta">
                                ↻ Dokumentasi sebelumnya dibatalkan.
                                Karyawan masuk antrean <strong>PEMANGGILAN ULANG</strong>.
                            </div>
                        @endif

                        <div class="atrc-card-actions">
                            @if (!$activeCoaching)
                                @if (!($record->pic_roster_resolved ?? false))
                                    <a
                                        class="atrc-roster-config"
                                        href="{{ route('database.atr.pic-roster', [
                                            'test_position' => $record->position,
                                        ]) }}"
                                    >ATUR PIC ROSTER</a>
                                @else
                                <button
                                    type="button"
                                    class="atrc-call"
                                    data-call="{{ json_encode([
                                        'id' => $record->id,
                                        'nrp' => $record->nrp,
                                        'name' => $record->employee_name,
                                        'position' => $record->position,
                                        'job' => $record->job_title,
                                        'pic' => $record->pic_roster_name_resolved ?? 'PIC BELUM TERDAFTAR',
                                        'period' => $record->period->locale('id')->translatedFormat('F Y'),
                                        'atr' => number_format((float) $record->atr, 1) . '%',
                                        'sia' => $record->sick . ' / ' . $record->permission . ' / ' . $record->alpha,
                                    ], JSON_UNESCAPED_UNICODE) }}"
                                >
                                    {{ $cancelledCoaching ? 'LAKUKAN PEMANGGILAN ULANG' : 'LAKUKAN PEMANGGILAN' }}
                                </button>

                                @if ($cancelledCoaching)
                                    <a
                                        target="_blank"
                                        class="atrc-print"
                                        href="{{ route('database.atr.coaching.print', $cancelledCoaching) }}"
                                    >DOKUMEN LAMA</a>
                                @endif
                                @endif
                            @else
                                <a
                                    target="_blank"
                                    class="atrc-print"
                                    href="{{ route('database.atr.coaching.print', $activeCoaching) }}"
                                >CETAK</a>

                                @php($evidence = $activeCoaching->attachments->firstWhere('type', 'EVIDENCE'))
                                @if ($evidence)
                                    <a
                                        target="_blank"
                                        class="atrc-print"
                                        href="{{ route('database.atr.attachments.show', [$activeCoaching, $evidence]) }}"
                                    >BUKTI</a>
                                @endif

                                <button
                                    type="button"
                                    class="atrc-cancel-doc"
                                    data-cancel-coaching
                                    data-cancel-url="{{ route('database.atr.coaching.cancel', $activeCoaching) }}"
                                    data-cancel-name="{{ $record->employee_name }}"
                                >BATALKAN</button>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        @if ($records->hasPages())
            <div class="atrc-pages">{{ $records->links() }}</div>
        @endif
    @endif
</section>
</div>
</div>

<div class="atrc-modal" id="atrcModal" aria-hidden="true">
    <div class="atrc-dialog">
        <div class="atrc-modal-head"><h2>🔔 Coaching &amp; Counseling ATR</h2><button class="atrc-close" type="button" id="atrcClose">×</button></div>
        <form method="POST" action="{{ route('database.atr.calls.store') }}" enctype="multipart/form-data" id="atrcCoachingForm">
            @csrf
            <input type="hidden" name="atr_record_id" id="atrcRecordId" value="{{ old('atr_record_id') }}">
            <div class="atrc-modal-body">
                <div class="atrc-summary">
                    <div><span>Nama</span><b id="atrcName">-</b></div><div><span>NRP / Posisi</span><b id="atrcNrpJob">-</b></div>
                    <div><span>Periode</span><b id="atrcPeriod">-</b></div><div><span>ATR · S/I/A</span><b id="atrcAtr">-</b></div>
                </div>
                <div class="atrc-form-grid">
                    <div class="atrc-form-field"><label>Tanggal</label><input type="date" name="coaching_date" value="{{ old('coaching_date', now()->format('Y-m-d')) }}" required></div>
                    <div class="atrc-form-field"><label>Shift</label><select name="shift" required><option value="DAY" @selected(old('shift')==='DAY')>DAY</option><option value="NIGHT" @selected(old('shift')==='NIGHT')>NIGHT</option><option value="NON SHIFT" @selected(old('shift')==='NON SHIFT')>NON SHIFT</option></select></div>
                    <div class="atrc-form-field"><label>Lokasi</label><input name="location" value="{{ old('location') }}" placeholder="Contoh: Office Produksi" required></div>
                    <div class="atrc-form-field"><label>Waktu</label><input type="time" name="coaching_time" value="{{ old('coaching_time', now()->format('H:i')) }}" required></div>
                    <div class="atrc-form-field full"><label>Materi</label><div class="atrc-materials"><label class="atrc-material"><input type="checkbox" name="material_personal" value="1" @checked(old('material_personal'))> PRIBADI</label><label class="atrc-material"><input type="checkbox" name="material_family" value="1" @checked(old('material_family'))> KELUARGA</label><label class="atrc-material"><input type="checkbox" name="material_work" value="1" @checked(old('material_work'))> PEKERJAAN</label></div></div>
                    <div class="atrc-form-field full"><label>Keterangan / Hasil Coaching</label><textarea name="notes" placeholder="Tuliskan penyebab, pembahasan, komitmen, dan tindak lanjut..." required>{{ old('notes') }}</textarea></div>
                    <div class="atrc-form-field">
                        <label>PIC Roster Otomatis</label>
                        <input
                            class="atrc-readonly"
                            name="created_by_name"
                            id="atrcCreatedBy"
                            value="{{ old('created_by_name') }}"
                            readonly
                        >
                    </div>

                    <div class="atrc-form-field">
                        <label>Nama Pimpinan *</label>
                        <input
                            name="leader_name"
                            id="atrcLeaderName"
                            value="{{ old('leader_name') }}"
                            placeholder="Ketik nama pimpinan yang menandatangani"
                            autocomplete="off"
                            required
                        >
                    </div>

                    <div class="atrc-form-field full">
                        <label>Bukti Pemanggilan *</label>
                        <input
                            type="file"
                            name="evidence"
                            accept=".jpg,.jpeg,.png,.pdf"
                            required
                        >
                    </div>

                    <div class="atrc-form-field atrc-signature-field">
                        <label>Tanda Tangan Karyawan *</label>
                        <div class="atrc-signature-box">
                            <canvas
                                id="atrcCreatorSignatureCanvas"
                                class="atrc-signature-canvas"
                                aria-label="Area tanda tangan karyawan"
                            ></canvas>
                            <div class="atrc-signature-tools">
                                <span class="atrc-signature-hint">Tanda tangan langsung menggunakan mouse, stylus, atau layar sentuh.</span>
                                <button type="button" class="atrc-signature-clear" id="atrcCreatorSignatureClear">HAPUS</button>
                            </div>
                        </div>
                        <input type="hidden" name="creator_signature_data" id="atrcCreatorSignatureData">
                    </div>

                    <div class="atrc-form-field atrc-signature-field">
                        <label>Tanda Tangan Pimpinan *</label>
                        <div class="atrc-signature-box">
                            <canvas
                                id="atrcLeaderSignatureCanvas"
                                class="atrc-signature-canvas"
                                aria-label="Area tanda tangan pimpinan"
                            ></canvas>
                            <div class="atrc-signature-tools">
                                <span class="atrc-signature-hint">Tanda tangan langsung menggunakan mouse, stylus, atau layar sentuh.</span>
                                <button type="button" class="atrc-signature-clear" id="atrcLeaderSignatureClear">HAPUS</button>
                            </div>
                        </div>
                        <input type="hidden" name="leader_signature_data" id="atrcLeaderSignatureData">
                    </div>
                </div>
            </div>
            <div class="atrc-modal-actions">
                <button type="button" class="atrc-cancel" id="atrcCancel">BATAL</button>
                <button type="submit" class="atrc-save" id="atrcSaveCoaching">
                    SIMPAN &amp; SELESAIKAN PEMANGGILAN
                </button>
            </div>
        </form>
    </div>
</div>

<div class="atrc-modal" id="atrcCancelModal" aria-hidden="true">
    <div class="atrc-dialog atrc-cancel-dialog">
        <div class="atrc-modal-head">
            <h2>⚠ Konfirmasi Pembatalan Dokumentasi</h2>
            <button class="atrc-close" type="button" id="atrcCancelDocClose">×</button>
        </div>

        <form method="POST" id="atrcCancelDocForm">
            @csrf
            <div class="atrc-modal-body">
                <div class="atrc-cancel-warning">
                    <strong>Apakah Anda yakin ingin membatalkan dokumentasi ini?</strong><br><br>
                    Dokumen, bukti, tanda tangan, dan riwayat lama
                    <strong>tidak akan dihapus</strong>.
                    Status karyawan akan berubah menjadi
                    <strong>PERLU ULANG</strong>.<br><br>
                    Alasan pembatalan wajib diisi dan akan masuk ke
                    <strong>audit trail serta PDF dokumen lama</strong>.
                </div>

                <div class="atrc-form-field full">
                    <label>Karyawan</label>
                    <input class="atrc-readonly" id="atrcCancelDocName" readonly>
                </div>

                <div class="atrc-form-field full" style="margin-top:11px">
                    <label>Alasan Pembatalan *</label>
                    <textarea
                        class="atrc-cancel-textarea"
                        name="cancel_reason"
                        minlength="5"
                        maxlength="500"
                        placeholder="Contoh: bukti pemanggilan salah / data coaching perlu diperbaiki..."
                        required
                    ></textarea>
                </div>
            </div>

            <div class="atrc-modal-actions">
                <button type="button" class="atrc-cancel" id="atrcCancelDocBack">KEMBALI</button>
                <button type="submit" class="atrc-danger-submit" id="atrcCancelDocSubmit">YA, BATALKAN DOKUMENTASI</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('atrcModal');
    const form = document.getElementById('atrcCoachingForm');
    const createdBy = document.getElementById('atrcCreatedBy');

    const close = () => {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
    };

    function createSignaturePad(canvasId, hiddenId, clearButtonId) {
        const canvas = document.getElementById(canvasId);
        const hidden = document.getElementById(hiddenId);
        const clearButton = document.getElementById(clearButtonId);
        const ctx = canvas.getContext('2d');

        let drawing = false;
        let hasInk = false;

        function setupCanvas() {
            const rect = canvas.getBoundingClientRect();

            if (rect.width <= 0 || rect.height <= 0) {
                return;
            }

            const ratio = Math.max(window.devicePixelRatio || 1, 1);

            canvas.width = Math.round(rect.width * ratio);
            canvas.height = Math.round(rect.height * ratio);

            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            ctx.lineWidth = 2.2;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = '#111827';

            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, rect.width, rect.height);

            hasInk = false;
            hidden.value = '';
        }

        function pointFromEvent(event) {
            const rect = canvas.getBoundingClientRect();

            return {
                x: event.clientX - rect.left,
                y: event.clientY - rect.top,
            };
        }

        function start(event) {
            event.preventDefault();

            if (event.pointerType === 'mouse' && event.button !== 0) {
                return;
            }

            drawing = true;
            hasInk = true;

            try {
                canvas.setPointerCapture(event.pointerId);
            } catch (error) {
                // Browser lama dapat mengabaikan pointer capture.
            }

            const point = pointFromEvent(event);

            ctx.beginPath();
            ctx.moveTo(point.x, point.y);
        }

        function move(event) {
            if (!drawing) {
                return;
            }

            event.preventDefault();

            const point = pointFromEvent(event);
            ctx.lineTo(point.x, point.y);
            ctx.stroke();
        }

        function stop(event) {
            if (!drawing) {
                return;
            }

            drawing = false;

            try {
                canvas.releasePointerCapture(event.pointerId);
            } catch (error) {
                // Tidak masalah jika pointer capture sudah dilepas.
            }
        }

        function clear() {
            setupCanvas();
        }

        function exportPng() {
            if (!hasInk) {
                hidden.value = '';
                return false;
            }

            hidden.value = canvas.toDataURL('image/png');
            return true;
        }

        canvas.addEventListener('pointerdown', start);
        canvas.addEventListener('pointermove', move);
        canvas.addEventListener('pointerup', stop);
        canvas.addEventListener('pointercancel', stop);
        canvas.addEventListener('pointerleave', function (event) {
            if (event.pointerType === 'mouse') {
                stop(event);
            }
        });

        clearButton?.addEventListener('click', clear);

        return {
            reset: setupCanvas,
            exportPng,
            hasSignature: () => hasInk,
        };
    }

    const creatorPad = createSignaturePad(
        'atrcCreatorSignatureCanvas',
        'atrcCreatorSignatureData',
        'atrcCreatorSignatureClear'
    );

    const leaderPad = createSignaturePad(
        'atrcLeaderSignatureCanvas',
        'atrcLeaderSignatureData',
        'atrcLeaderSignatureClear'
    );

    function openForRecord(data) {
        document.getElementById('atrcRecordId').value = data.id;
        document.getElementById('atrcName').textContent = data.name;
        document.getElementById('atrcNrpJob').textContent =
            data.nrp + ' / ' + (data.position || '-');
        document.getElementById('atrcPeriod').textContent = data.period;
        document.getElementById('atrcAtr').textContent =
            data.atr + ' · ' + data.sia;

        // PIC Roster berasal dari Master PIC Roster. Controller menghitung ulang saat submit.
        createdBy.value = data.pic || 'PIC BELUM TERDAFTAR';

        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');

        /*
         * Canvas harus diinisialisasi sesudah modal terlihat agar ukuran
         * getBoundingClientRect() benar.
         */
        window.requestAnimationFrame(function () {
            creatorPad.reset();
            leaderPad.reset();
        });
    }

    document.querySelectorAll('[data-call]').forEach(function (button) {
        button.addEventListener('click', function () {
            const data = JSON.parse(this.dataset.call);
            openForRecord(data);
        });
    });

    const saveButton = document.getElementById('atrcSaveCoaching');
    let coachingSubmitting = false;

    form?.addEventListener('submit', function (event) {
        if (coachingSubmitting) {
            event.preventDefault();
            return;
        }

        const creatorOk = creatorPad.exportPng();
        const leaderOk = leaderPad.exportPng();

        if (!creatorOk || !leaderOk) {
            event.preventDefault();

            let message = 'Tanda tangan wajib dilengkapi:';

            if (!creatorOk) {
                message += '\n- Tanda Tangan Karyawan';
            }

            if (!leaderOk) {
                message += '\n- Tanda Tangan Pimpinan';
            }

            window.alert(message);
            return;
        }

        coachingSubmitting = true;

        if (saveButton) {
            saveButton.disabled = true;
            saveButton.classList.add('is-loading');
            saveButton.textContent = 'MENYIMPAN DOKUMENTASI...';
        }
    });

    const cancelDocModal = document.getElementById('atrcCancelModal');
    const cancelDocForm = document.getElementById('atrcCancelDocForm');
    const cancelDocName = document.getElementById('atrcCancelDocName');

    function closeCancelDoc() {
        cancelDocModal?.classList.remove('open');
        cancelDocModal?.setAttribute('aria-hidden', 'true');
        cancelDocForm?.reset();

        if (!cancelSubmitting && cancelDocSubmit) {
            cancelDocSubmit.disabled = false;
            cancelDocSubmit.classList.remove('is-loading');
            cancelDocSubmit.textContent = 'YA, BATALKAN DOKUMENTASI';
        }
    }

    document.querySelectorAll('[data-cancel-coaching]').forEach(function (button) {
        button.addEventListener('click', function () {
            cancelDocForm.action = this.dataset.cancelUrl || '';
            cancelDocName.value = this.dataset.cancelName || '-';
            cancelDocModal.classList.add('open');
            cancelDocModal.setAttribute('aria-hidden', 'false');
        });
    });

    const cancelDocSubmit = document.getElementById('atrcCancelDocSubmit');
    let cancelSubmitting = false;

    cancelDocForm?.addEventListener('submit', function (event) {
        if (cancelSubmitting) {
            event.preventDefault();
            return;
        }

        cancelSubmitting = true;

        if (cancelDocSubmit) {
            cancelDocSubmit.disabled = true;
            cancelDocSubmit.classList.add('is-loading');
            cancelDocSubmit.textContent = 'MEMBATALKAN...';
        }
    });

    document.getElementById('atrcCancelDocClose')?.addEventListener('click', closeCancelDoc);
    document.getElementById('atrcCancelDocBack')?.addEventListener('click', closeCancelDoc);

    cancelDocModal?.addEventListener('click', function (event) {
        if (event.target === cancelDocModal) {
            closeCancelDoc();
        }
    });

    document.getElementById('atrcClose')?.addEventListener('click', close);
    document.getElementById('atrcCancel')?.addEventListener('click', close);

    modal?.addEventListener('click', function (event) {
        if (event.target === modal) {
            close();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            close();
            closeCancelDoc();
        }
    });

    @if($errors->any() && old('atr_record_id'))
        const oldRecordId = {{ (int) old('atr_record_id') }};
        const oldButton = Array.from(document.querySelectorAll('[data-call]'))
            .find(function (button) {
                try {
                    return JSON.parse(button.dataset.call).id === oldRecordId;
                } catch (error) {
                    return false;
                }
            });

        oldButton?.click();
    @endif
});
</script>