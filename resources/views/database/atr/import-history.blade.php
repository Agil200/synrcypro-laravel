<style>
.atri-title{margin-bottom:12px}.atri-title h1{font-size:24px;margin:0;color:#10213d}.atri-title p{margin:3px 0 0;color:#60708a;font-size:12px}.atri-card{background:#fff;border:1px solid #d9e2ee;border-radius:14px;box-shadow:0 6px 18px rgba(15,35,65,.06);overflow:hidden}.atri-head{display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-bottom:1px solid #e1e8f1}.atri-head h2{font-size:15px;margin:0;color:#172640}.atri-head small{display:block;margin-top:4px;color:#74839a;font-size:9px}.atri-btn{height:36px;border-radius:9px;padding:0 14px;background:#1677ff;color:#fff;text-decoration:none;font-size:10px;font-weight:800;display:inline-flex;align-items:center}.atri-wrap{overflow:auto}.atri-table{width:100%;border-collapse:collapse;min-width:1000px}.atri-table th{background:#f6f8fb;padding:10px;font-size:8px;color:#596a80;text-transform:uppercase;text-align:left}.atri-table td{padding:10px;border-top:1px solid #e7edf4;font-size:9px;color:#314761}.atri-name{font-weight:800;color:#172640}.atri-badge{display:inline-flex;padding:5px 8px;border-radius:999px;font-size:8px;font-weight:900}.atri-badge.COMPLETED{background:#dff7ea;color:#078446}.atri-badge.PROCESSING{background:#fff1c9;color:#9d6900}.atri-badge.FAILED{background:#ffe0e4;color:#c61f35}.atri-empty{padding:45px;text-align:center;color:#7b899c}.atri-flash{padding:11px 14px;border-radius:10px;margin-bottom:10px;font-size:11px}.atri-flash.success{background:#e5f8ed;color:#087b42;border:1px solid #bcebd0}.atri-pages{padding:12px 15px;border-top:1px solid #e7edf4}
</style>

<div class="atri-title">
    <h1>Riwayat Import ATR</h1>
    <p>Audit file XLSX yang telah dipreview dan diimpor ke database Laravel.</p>
</div>

@if (session('success'))
    <div class="atri-flash success">{{ session('success') }}</div>
@endif

<section class="atri-card">
    <div class="atri-head">
        <div><h2>Riwayat Import</h2><small>Setiap import tersimpan sebagai snapshot agar histori tidak tertimpa.</small></div>
        <a class="atri-btn" href="{{ route('database.atr.upload') }}">UPLOAD BARU</a>
    </div>

    @if ($imports->isEmpty())
        <div class="atri-empty">Belum ada file ATR yang diimpor.</div>
    @else
        <div class="atri-wrap">
            <table class="atri-table">
                <thead><tr><th>Nama File</th><th>Periode</th><th>Total</th><th>Valid</th><th>Invalid</th><th>Diimpor</th><th>Uploader</th><th>Waktu</th><th>Status</th></tr></thead>
                <tbody>
                @foreach ($imports as $import)
                    <tr>
                        <td class="atri-name">{{ $import->file_name }}</td>
                        <td>
                            @if ($import->period_min)
                                {{ $import->period_min->format('Y-m') }}
                                @if ($import->period_max && !$import->period_max->equalTo($import->period_min))
                                    s/d {{ $import->period_max->format('Y-m') }}
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ number_format($import->total_rows) }}</td>
                        <td>{{ number_format($import->valid_rows) }}</td>
                        <td>{{ number_format($import->invalid_rows) }}</td>
                        <td>{{ number_format($import->imported_rows) }}</td>
                        <td>{{ $import->uploader?->name ?? $import->uploader?->email ?? '-' }}</td>
                        <td>{{ $import->imported_at?->format('d M Y H:i') ?? $import->created_at->format('d M Y H:i') }}</td>
                        <td><span class="atri-badge {{ $import->status }}">{{ $import->status }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="atri-pages">{{ $imports->links() }}</div>
    @endif
</section>
