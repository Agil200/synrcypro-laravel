@php
    use Carbon\Carbon;

    $periodValue = $period ? Carbon::parse($period)->format('Y-m') : '';
    $monthLabel = $period
        ? Carbon::parse($period)->locale('id')->translatedFormat('F Y')
        : 'Belum Ada Data';

    $statusCards = [
        ['key' => 'aman', 'label' => 'AMAN', 'value' => $stats['aman'], 'class' => 'green', 'note' => 'ATR ≥ ' . $thresholds['aman'] . '%'],
        ['key' => 'monitoring', 'label' => 'MONITORING', 'value' => $stats['monitoring'], 'class' => 'yellow', 'note' => $thresholds['monitoring'] . '% – ' . ($thresholds['aman'] - 0.01) . '%'],
        ['key' => 'pemanggilan', 'label' => 'PEMANGGILAN', 'value' => $stats['pemanggilan'], 'class' => 'red', 'note' => 'ATR < ' . $thresholds['monitoring'] . '%'],
        ['key' => 'no_data', 'label' => 'NO DATA', 'value' => $stats['no_data'], 'class' => 'gray', 'note' => 'ATR belum tersedia'],
        ['key' => 'sakit', 'label' => 'TOTAL SAKIT', 'value' => $stats['sakit'], 'class' => 'orange', 'note' => 'Akumulasi S'],
        ['key' => 'izin', 'label' => 'TOTAL IZIN', 'value' => $stats['izin'], 'class' => 'blue', 'note' => 'Akumulasi I'],
        ['key' => 'alpa', 'label' => 'TOTAL ALPA', 'value' => $stats['alpa'], 'class' => 'rose', 'note' => 'Akumulasi A'],
    ];
@endphp

<style>
.atrx-title{margin-bottom:12px}.atrx-title h1{font-size:24px;margin:0;color:#10213d}.atrx-title p{margin:3px 0 0;color:#60708a;font-size:12px}
.atrx-panel{background:#fff;border:1px solid #d9e2ee;border-radius:14px;box-shadow:0 6px 18px rgba(15,35,65,.06);margin-bottom:12px;overflow:hidden}
.atrx-filter{display:grid;grid-template-columns:minmax(180px,1fr) minmax(210px,1fr) minmax(260px,1.4fr) auto;gap:10px;padding:14px}
.atrx-field label{display:block;font-size:10px;font-weight:800;color:#263952;margin-bottom:5px;text-transform:uppercase;letter-spacing:.35px}
.atrx-input,.atrx-select{width:100%;height:39px;border:1px solid #cbd7e6;border-radius:9px;padding:0 12px;background:#fff;color:#17233a;outline:none}.atrx-input:focus,.atrx-select:focus{border-color:#1677ff;box-shadow:0 0 0 3px rgba(22,119,255,.12)}
.atrx-actions{display:flex;gap:7px;align-items:end}.atrx-btn{height:39px;border:0;border-radius:9px;padding:0 16px;font-size:11px;font-weight:800;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center}.atrx-btn.primary{background:#1677ff;color:#fff}.atrx-btn.light{background:#eef3f9;color:#263952}.atrx-btn.red{background:#e73849;color:#fff}
.atrx-section-head{display:flex;justify-content:space-between;align-items:center;padding:14px 15px;border-bottom:1px solid #e2e9f2}.atrx-section-head h2{font-size:15px;margin:0;color:#15233a}.atrx-section-head small{font-size:10px;color:#718099}.atrx-chip{border:1px solid #d7e1ee;background:#f6f9fc;color:#34455e;border-radius:999px;padding:5px 9px;font-size:9px;font-weight:800}
.atrx-kpis{display:grid;grid-template-columns:repeat(7,minmax(125px,1fr));gap:8px;padding:12px}.atrx-kpi{position:relative;background:#fff;border:1px solid #dfe7f1;border-top:3px solid var(--accent);border-radius:11px;padding:14px 10px;text-align:center;min-height:91px}.atrx-kpi strong{display:block;font-size:25px;line-height:1;color:var(--accent)}.atrx-kpi b{display:block;font-size:9px;margin-top:8px;color:#60708a;letter-spacing:.35px}.atrx-kpi span{display:block;font-size:8px;margin-top:4px;color:#95a1b4}.atrx-kpi.green{--accent:#16a365}.atrx-kpi.yellow{--accent:#e9a400}.atrx-kpi.red{--accent:#e43549}.atrx-kpi.gray{--accent:#6f7f93}.atrx-kpi.orange{--accent:#f08a00}.atrx-kpi.blue{--accent:#2776eb}.atrx-kpi.rose{--accent:#df174d}
.atrx-progress{padding:16px}.atrx-progress-grid{display:grid;grid-template-columns:repeat(3,1fr);text-align:center;margin-bottom:10px}.atrx-progress-grid strong{display:block;font-size:20px;color:#14213b}.atrx-progress-grid div:nth-child(1) strong{color:#e43549}.atrx-progress-grid div:nth-child(2) strong{color:#129a55}.atrx-progress-grid small{display:block;font-size:8px;color:#697a91;font-weight:800}.atrx-track{height:7px;background:#e7edf5;border-radius:999px;overflow:hidden}.atrx-bar{height:100%;background:linear-gradient(90deg,#1caf65,#0b8f4f);border-radius:999px}.atrx-progress-label{text-align:right;font-size:9px;color:#697a91;margin-top:5px}
.atrx-table-wrap{overflow:auto}.atrx-table{width:100%;border-collapse:collapse;min-width:920px}.atrx-table th{background:#f6f8fb;color:#52627a;font-size:9px;text-transform:uppercase;letter-spacing:.45px;text-align:left;padding:10px;border-bottom:1px solid #dfe7f1}.atrx-table td{padding:10px;border-bottom:1px solid #e8edf4;font-size:10px;color:#29405f}.atrx-table tbody tr:hover{background:#f8fbff}.atrx-name{font-weight:800;color:#172640}.atrx-badge{display:inline-flex;padding:5px 8px;border-radius:999px;font-size:8px;font-weight:900}.atrx-badge.AMAN{background:#dff7ea;color:#078446}.atrx-badge.MONITORING{background:#fff1c9;color:#9d6900}.atrx-badge.PEMANGGILAN{background:#ffe0e4;color:#c61f35}.atrx-badge.NO_DATA{background:#e8edf3;color:#5d6b7e}.atrx-empty{padding:42px;text-align:center;color:#7b899c}.atrx-flash{padding:11px 14px;border-radius:10px;margin-bottom:10px;font-size:11px}.atrx-flash.success{background:#e5f8ed;color:#087b42;border:1px solid #bcebd0}.atrx-flash.error{background:#ffe8eb;color:#b11d32;border:1px solid #ffc8d0}
@media(max-width:1300px){.atrx-kpis{grid-template-columns:repeat(4,1fr)}}@media(max-width:900px){.atrx-filter{grid-template-columns:1fr 1fr}.atrx-actions{grid-column:1/-1}.atrx-kpis{grid-template-columns:repeat(2,1fr)}}@media(max-width:560px){.atrx-filter{grid-template-columns:1fr}.atrx-actions{grid-column:auto}.atrx-kpis{grid-template-columns:1fr}.atrx-progress-grid{grid-template-columns:1fr;gap:10px}}
</style>

<div class="atrx-title">
    <h1>Ringkasan ATR Karyawan</h1>
    <p>Monitoring ATR Departemen Produksi, statistik absensi, dan progres Coaching &amp; Counseling.</p>
</div>

@if (session('success'))
    <div class="atrx-flash success">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="atrx-flash error">{{ $errors->first() }}</div>
@endif

<section class="atrx-panel">
    <form method="GET" action="{{ route('database.atr.summary') }}" class="atrx-filter">
        <div class="atrx-field">
            <label>Periode</label>
            <select name="period" class="atrx-select">
                @forelse ($periodOptions as $option)
                    <option value="{{ $option->format('Y-m') }}" @selected($periodValue === $option->format('Y-m'))>
                        {{ $option->locale('id')->translatedFormat('F Y') }}
                    </option>
                @empty
                    <option value="">Belum ada periode</option>
                @endforelse
            </select>
        </div>
        <div class="atrx-field">
            <label>Posisi / Jabatan</label>
            <select name="job_title" class="atrx-select">
                <option value="">Semua Jabatan</option>
                @foreach ($jobOptions as $job)
                    <option value="{{ $job }}" @selected(request('job_title') === $job)>{{ $job }}</option>
                @endforeach
            </select>
        </div>
        <div class="atrx-field">
            <label>Cari Karyawan</label>
            <input class="atrx-input" name="search" value="{{ request('search') }}" placeholder="Cari NRP atau nama...">
        </div>
        <div class="atrx-actions">
            <button class="atrx-btn primary" type="submit">TERAPKAN</button>
            <a class="atrx-btn light" href="{{ route('database.atr.summary') }}">RESET</a>
        </div>
    </form>
</section>

<section class="atrx-panel">
    <div class="atrx-section-head">
        <div>
            <h2>Statistik ATR — {{ $monthLabel }}</h2>
            <small>Statistik otomatis mengikuti filter periode, jabatan, dan pencarian.</small>
        </div>
        <span class="atrx-chip">{{ number_format($stats['total']) }} DATA</span>
    </div>
    <div class="atrx-kpis">
        @foreach ($statusCards as $card)
            <article class="atrx-kpi {{ $card['class'] }}">
                <strong>{{ number_format($card['value']) }}</strong>
                <b>{{ $card['label'] }}</b>
                <span>{{ $card['note'] }}</span>
            </article>
        @endforeach
    </div>
</section>

<section class="atrx-panel">
    <div class="atrx-section-head">
        <div>
            <h2>Progress Pemanggilan</h2>
            <small>Hanya karyawan berstatus merah / PEMANGGILAN.</small>
        </div>
        <a class="atrx-btn red" href="{{ route('database.atr.calls', request()->query()) }}">BUKA PEMANGGILAN</a>
    </div>
    <div class="atrx-progress">
        <div class="atrx-progress-grid">
            <div><strong>{{ number_format($progress['belum']) }}</strong><small>BELUM</small></div>
            <div><strong>{{ number_format($progress['sudah']) }}</strong><small>SUDAH</small></div>
            <div><strong>{{ number_format($progress['total']) }}</strong><small>TOTAL PERLU</small></div>
        </div>
        <div class="atrx-track"><div class="atrx-bar" style="width: {{ $progress['percentage'] }}%"></div></div>
        <div class="atrx-progress-label">{{ $progress['percentage'] }}% selesai</div>
    </div>
</section>

<section class="atrx-panel">
    <div class="atrx-section-head">
        <div>
            <h2>Top Absensi S + I + A</h2>
            <small>Urutan berdasarkan total absensi tertinggi pada data yang sedang difilter.</small>
        </div>
    </div>
    @if ($topAbsences->isEmpty())
        <div class="atrx-empty">Belum ada data ATR. Silakan upload template ATR terlebih dahulu.</div>
    @else
        <div class="atrx-table-wrap">
            <table class="atrx-table">
                <thead><tr><th>#</th><th>NRP</th><th>Nama</th><th>Jabatan</th><th>S</th><th>I</th><th>A</th><th>Total</th><th>ATR</th><th>Status</th></tr></thead>
                <tbody>
                @foreach ($topAbsences as $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $row->nrp }}</td>
                        <td class="atrx-name">{{ $row->employee_name }}</td>
                        <td>{{ $row->job_title ?: '-' }}</td>
                        <td>{{ $row->sick }}</td>
                        <td>{{ $row->permission }}</td>
                        <td>{{ $row->alpha }}</td>
                        <td><strong>{{ $row->totalAbsence() }}</strong></td>
                        <td>{{ $row->atr === null ? '-' : number_format((float) $row->atr, 1) . '%' }}</td>
                        <td><span class="atrx-badge {{ $row->status }}">{{ str_replace('_', ' ', $row->status) }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
