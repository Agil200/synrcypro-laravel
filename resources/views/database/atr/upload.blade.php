<style>
.atru-title{margin-bottom:12px}.atru-title h1{font-size:24px;margin:0;color:#10213d}.atru-title p{margin:3px 0 0;color:#60708a;font-size:12px}
.atru-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:12px}.atru-card{background:#fff;border:1px solid #d9e2ee;border-radius:14px;box-shadow:0 6px 18px rgba(15,35,65,.06);overflow:hidden}.atru-head{padding:14px 16px;border-bottom:1px solid #e1e8f1}.atru-head h2{font-size:15px;margin:0;color:#172640}.atru-head small{display:block;margin-top:4px;color:#74839a;font-size:9px}.atru-body{padding:16px}
.atru-drop{border:2px dashed #c8d5e5;border-radius:12px;min-height:190px;display:flex;align-items:center;justify-content:center;text-align:center;background:#f8fbff;cursor:pointer;transition:.2s}.atru-drop:hover{border-color:#1677ff;background:#f1f7ff}.atru-drop strong{display:block;font-size:16px;color:#23354f}.atru-drop span{display:block;font-size:11px;color:#71819a;margin-top:5px}.atru-drop b{font-size:30px;display:block;margin-bottom:8px}
.atru-btn{border:0;border-radius:9px;height:39px;padding:0 16px;font-size:11px;font-weight:800;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;cursor:pointer}.atru-btn.primary{background:#1677ff;color:#fff}.atru-btn.green{background:#149b59;color:#fff}.atru-btn.light{background:#eef3f8;color:#2c3e58}.atru-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}
.atru-info{display:grid;gap:9px}.atru-info-row{display:flex;justify-content:space-between;gap:15px;padding:10px 0;border-bottom:1px dashed #dde5ef;font-size:11px}.atru-info-row span{color:#75849a}.atru-info-row strong{color:#172640;text-align:right}.atru-note{margin-top:12px;padding:11px;border-radius:10px;background:#eef6ff;color:#24518e;font-size:10px;line-height:1.55}
.atru-flash{padding:11px 14px;border-radius:10px;margin-bottom:10px;font-size:11px}.atru-flash.success{background:#e5f8ed;color:#087b42;border:1px solid #bcebd0}.atru-flash.error{background:#ffe8eb;color:#b11d32;border:1px solid #ffc8d0}
.atru-preview{margin-top:12px;background:#fff;border:1px solid #d9e2ee;border-radius:14px;box-shadow:0 6px 18px rgba(15,35,65,.06);overflow:hidden}.atru-preview-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;padding:12px}.atru-stat{border:1px solid #dfe7f1;border-radius:10px;padding:12px;text-align:center}.atru-stat strong{display:block;font-size:22px;color:#172640}.atru-stat small{font-size:8px;color:#74839a;font-weight:800}.atru-stat.bad strong{color:#d7263d}.atru-stat.good strong{color:#129653}
.atru-table-wrap{overflow:auto}.atru-table{width:100%;border-collapse:collapse;min-width:850px}.atru-table th{background:#f6f8fb;padding:9px;font-size:8px;color:#596a80;text-transform:uppercase;text-align:left}.atru-table td{padding:9px;border-top:1px solid #e7edf4;font-size:9px;color:#314761}.atru-errors{padding:12px}.atru-error-item{background:#fff1f3;border:1px solid #ffd0d7;border-radius:9px;padding:9px 11px;margin-bottom:7px;font-size:10px;color:#a7192e}.atru-error-item strong{display:block;margin-bottom:3px}.atru-commit{padding:14px 16px;border-top:1px solid #e1e8f1;display:flex;align-items:center;justify-content:space-between;gap:12px}.atru-commit p{margin:0;font-size:10px;color:#687992}
@media(max-width:950px){.atru-grid{grid-template-columns:1fr}.atru-preview-stats{grid-template-columns:repeat(2,1fr)}}@media(max-width:540px){.atru-preview-stats{grid-template-columns:1fr}.atru-commit{align-items:stretch;flex-direction:column}}
</style>

<div class="atru-title">
    <h1>Upload Data ATR</h1>
    <p>Upload Excel dua sheet: DATABASE_KARYAWAN dan ATR_SOURCE. Data disimpan ke database Laravel.</p>
</div>

@if (session('success'))
    <div class="atru-flash success">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="atru-flash error">
        @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
@endif

<div class="atru-grid">
    <section class="atru-card">
        <div class="atru-head">
            <h2>1. Pilih File Excel ATR</h2>
            <small>Format wajib XLSX dan mengikuti template resmi modul ATR Produksi.</small>
        </div>
        <div class="atru-body">
            <form method="POST" action="{{ route('database.atr.upload.preview') }}" enctype="multipart/form-data" id="atrUploadForm">
                @csrf
                <input type="file" name="atr_file" id="atrFile" accept=".xlsx" hidden required>
                <label for="atrFile" class="atru-drop" id="atrDrop">
                    <div>
                        <b>📁</b>
                        <strong id="atrFileName">Klik untuk pilih file ATR</strong>
                        <span>XLSX maksimal {{ number_format(config('atr.upload.max_kilobytes', 10240) / 1024) }} MB</span>
                    </div>
                </label>
                <div class="atru-actions">
                    <button type="submit" class="atru-btn primary">PREVIEW DATA</button>
                    <a href="{{ route('database.atr.template') }}" class="atru-btn light">DOWNLOAD TEMPLATE</a>
                </div>
            </form>
        </div>
    </section>

    <section class="atru-card">
        <div class="atru-head">
            <h2>2. Ketentuan File</h2>
            <small>Departemen otomatis PRODUKSI.</small>
        </div>
        <div class="atru-body">
            <div class="atru-info">
                <div class="atru-info-row"><span>Sheet 1</span><strong>DATABASE_KARYAWAN</strong></div>
                <div class="atru-info-row"><span>Header Sheet 1</span><strong>NRP, NAMA, JABATAN, SITE</strong></div>
                <div class="atru-info-row"><span>Sheet 2</span><strong>ATR_SOURCE</strong></div>
                <div class="atru-info-row"><span>Header Sheet 2</span><strong>PERIODE, NRP, ATR, S, I, A</strong></div>
                <div class="atru-info-row"><span>Format Periode</span><strong>YYYY-MM</strong></div>
                <div class="atru-info-row"><span>Format ATR</span><strong>0–100 atau tanda -</strong></div>
            </div>
            <div class="atru-note">
                Laravel akan mencocokkan NRP dari ATR_SOURCE ke DATABASE_KARYAWAN, menambahkan nama, jabatan, dan site, lalu menentukan status AMAN, MONITORING, PEMANGGILAN, atau NO DATA secara otomatis.
            </div>
        </div>
    </section>
</div>

@if (is_array($preview))
    <section class="atru-preview">
        <div class="atru-head">
            <h2>Preview Import — {{ $preview['original_name'] ?? '-' }}</h2>
            <small>Periksa hasil validasi sebelum menekan tombol Import Sekarang.</small>
        </div>

        <div class="atru-preview-stats">
            <div class="atru-stat"><strong>{{ number_format($preview['employee_count'] ?? 0) }}</strong><small>DATABASE KARYAWAN</small></div>
            <div class="atru-stat"><strong>{{ number_format($preview['total_rows'] ?? 0) }}</strong><small>TOTAL BARIS ATR</small></div>
            <div class="atru-stat good"><strong>{{ number_format($preview['valid_rows'] ?? 0) }}</strong><small>VALID</small></div>
            <div class="atru-stat bad"><strong>{{ number_format($preview['invalid_rows'] ?? 0) }}</strong><small>INVALID</small></div>
        </div>

        @if (!empty($preview['errors']))
            <div class="atru-errors">
                @foreach ($preview['errors'] as $error)
                    <div class="atru-error-item">{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @if (!empty($preview['row_errors']))
            <div class="atru-errors">
                @foreach (array_slice($preview['row_errors'], 0, 30) as $rowError)
                    <div class="atru-error-item">
                        <strong>Baris {{ $rowError['row'] }} — NRP {{ $rowError['nrp'] ?: '-' }}</strong>
                        {{ implode(' ', $rowError['messages']) }}
                    </div>
                @endforeach
                @if (count($preview['row_errors']) > 30)
                    <div class="atru-error-item">Masih ada {{ count($preview['row_errors']) - 30 }} error lainnya.</div>
                @endif
            </div>
        @endif

        @if (!empty($preview['preview_rows']))
            <div class="atru-table-wrap">
                <table class="atru-table">
                    <thead><tr><th>Periode</th><th>NRP</th><th>Nama</th><th>Jabatan</th><th>Site</th><th>ATR</th><th>S</th><th>I</th><th>A</th><th>Status</th></tr></thead>
                    <tbody>
                    @foreach ($preview['preview_rows'] as $row)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($row['period'])->format('Y-m') }}</td>
                            <td>{{ $row['nrp'] }}</td>
                            <td>{{ $row['employee_name'] }}</td>
                            <td>{{ $row['job_title'] }}</td>
                            <td>{{ $row['site'] }}</td>
                            <td>{{ $row['atr'] === null ? '-' : number_format($row['atr'], 1) . '%' }}</td>
                            <td>{{ $row['sick'] }}</td><td>{{ $row['permission'] }}</td><td>{{ $row['alpha'] }}</td>
                            <td>{{ str_replace('_', ' ', $row['status']) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="atru-commit">
            <p>
                @if (($preview['invalid_rows'] ?? 0) > 0)
                    Import dikunci sampai seluruh baris invalid diperbaiki.
                @else
                    File siap diimpor ke database Laravel sebagai snapshot baru.
                @endif
            </p>
            <form method="POST" action="{{ route('database.atr.upload.commit') }}">
                @csrf
                <input type="hidden" name="preview_token" value="{{ $preview['preview_token'] ?? '' }}">
                <button class="atru-btn green" type="submit" @disabled(($preview['invalid_rows'] ?? 0) > 0 || ($preview['valid_rows'] ?? 0) === 0)>
                    IMPORT SEKARANG
                </button>
            </form>
        </div>
    </section>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('atrFile');
    const label = document.getElementById('atrFileName');
    if (input && label) {
        input.addEventListener('change', function () {
            label.textContent = this.files && this.files[0]
                ? this.files[0].name
                : 'Klik untuk pilih file ATR';
        });
    }
});
</script>
