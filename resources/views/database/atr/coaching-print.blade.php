@php
    $employeeSignature = $coaching->attachments->firstWhere('type', 'EMPLOYEE_SIGNATURE');
    $coachSignature = $coaching->attachments->firstWhere('type', 'COACH_SIGNATURE');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Coaching and Counseling - {{ $coaching->atrRecord->nrp }}</title>
<style>
@page{size:A4;margin:10mm}*{box-sizing:border-box}body{font-family:Arial,sans-serif;color:#111;margin:0}.sheet{border:2px solid #111;width:100%}.header{height:130px;display:grid;grid-template-columns:150px 1fr 150px;align-items:center;border-bottom:2px solid #111}.logo{font-size:58px;text-align:center}.title{text-align:center}.title small{font-weight:700}.title h1{font-size:26px;margin:14px 0 0}.row{display:grid;grid-template-columns:160px 1fr;border-bottom:1.5px solid #111;min-height:40px}.row .label,.row .value{padding:10px 12px}.row .label{font-weight:700}.material-head{display:grid;grid-template-columns:1.1fr .9fr;background:#ddd;border-bottom:1.5px solid #111;font-weight:700;text-align:center}.material-head div{padding:10px}.material-head div:first-child{border-right:1.5px solid #111}.material-body{display:grid;grid-template-columns:1.1fr .9fr;min-height:130px;border-bottom:1.5px solid #111}.materials{border-right:1.5px solid #111;padding:10px 14px}.material-item{padding:8px 0;border-bottom:1px solid #ccc}.material-item:last-child{border-bottom:0}.check{display:inline-block;width:20px;height:20px;border:1.5px solid #111;margin-left:20px;text-align:center;line-height:17px;font-weight:700}.signature{padding:12px;text-align:center}.notes{min-height:280px;padding:12px;border-bottom:1.5px solid #111;white-space:pre-wrap}.made{background:#ddd;text-align:center;font-weight:700;padding:10px;border-bottom:1.5px solid #111}.sign-box{min-height:100px;padding:14px;border-bottom:1.5px solid #111}.footer-row{display:grid;grid-template-columns:160px 1fr;min-height:42px;border-bottom:1.5px solid #111}.footer-row div{padding:10px 12px}.doc{padding:8px 12px;text-align:right;font-size:12px}.meta{font-size:11px;color:#555;margin:8px 0 12px}.print-btn{position:fixed;right:18px;top:18px;background:#1677ff;color:#fff;border:0;border-radius:8px;padding:10px 15px;font-weight:700;cursor:pointer}@media print{.print-btn{display:none}.meta{display:none}}
</style>
</head>
<body>
<button class="print-btn" onclick="window.print()">CETAK / SIMPAN PDF</button>
<div class="meta">Dokumen sistem SYNRGYPRO · Dibuat {{ now()->format('d M Y H:i') }}</div>
<div class="sheet">
    <div class="header">
        <div class="logo">◉</div>
        <div class="title"><small>PT. PUTRA PERKASA ABADI</small><h1>COACHING AND COUNSELING</h1></div>
        <div class="logo">✚</div>
    </div>
    <div class="row"><div class="label">NAMA</div><div class="value">: {{ $coaching->atrRecord->employee_name }}</div></div>
    <div class="row"><div class="label">NRP / JABATAN</div><div class="value">: {{ $coaching->atrRecord->nrp }} / {{ $coaching->atrRecord->job_title }}</div></div>
    <div class="row"><div class="label">TANGGAL / SHIFT</div><div class="value">: {{ $coaching->coaching_date->format('d-m-Y') }} / {{ $coaching->shift }}</div></div>
    <div class="row"><div class="label">LOKASI</div><div class="value">: {{ $coaching->location }}</div></div>
    <div class="row"><div class="label">WAKTU</div><div class="value">: {{ substr($coaching->coaching_time,0,5) }}</div></div>
    <div class="material-head"><div>MATERI</div><div>TANDA TANGAN</div></div>
    <div class="material-body">
        <div class="materials">
            <div class="material-item">PRIBADI <span class="check">{{ $coaching->material_personal ? '✓' : '' }}</span></div>
            <div class="material-item">KELUARGA <span class="check">{{ $coaching->material_family ? '✓' : '' }}</span></div>
            <div class="material-item">PEKERJAAN <span class="check">{{ $coaching->material_work ? '✓' : '' }}</span></div>
        </div>
        <div class="signature">Tanda tangan karyawan<br><br>@if($employeeSignature)<img src="{{ route('database.atr.attachments.show', [$coaching, $employeeSignature]) }}" style="max-width:180px;max-height:80px"><br>@else<br><br><br>@endif
_______________________</div>
    </div>
    <div class="notes"><strong>KETERANGAN:</strong><br><br>{{ $coaching->notes }}</div>
    <div class="made">DIBUAT OLEH</div>
    <div class="sign-box"><strong>TANDA TANGAN:</strong><br><br>@if($coachSignature)<img src="{{ route('database.atr.attachments.show', [$coaching, $coachSignature]) }}" style="max-width:180px;max-height:80px"><br>@else<br><br>@endif
_______________________</div>
    <div class="footer-row"><div><strong>NAMA</strong></div><div>: {{ $coaching->created_by_name }}</div></div>
    <div class="doc">NO Dokumen : {{ $coaching->document_number }}</div>
</div>
</body>
</html>
