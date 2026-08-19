@extends('admin-all.layout')

@section('admin-content')
@php
    $restoreReady = \Illuminate\Support\Facades\Route::has('admin-all.e-arsip.restore');
    $trashedArchives = $trashedArchives ?? collect();
    $trashTotal = method_exists($trashedArchives, 'total')
        ? $trashedArchives->total()
        : $trashedArchives->count();
@endphp

<style>
.aa-main{overflow:hidden!important}.aa-content{height:100%;min-height:0}
.eat-page{display:flex;width:100%;height:100%;min-height:0;flex-direction:column;gap:8px;overflow:hidden}
.eat-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex:0 0 auto}
.eat-head h1{margin:0;color:#071f3d;font-size:clamp(20px,2vw,27px)}
.eat-head p{margin:3px 0 0;color:#657588;font-size:8px}
.eat-btn{display:inline-flex;min-height:30px;align-items:center;justify-content:center;padding:6px 10px;border:1px solid #ccd7e1;border-radius:7px;color:#17304e;background:#fff;font-size:7px;font-weight:900;text-decoration:none}
.eat-btn.restore{border-color:#a9ddc1;color:#11643b;background:#effbf5}
.eat-btn.disabled{opacity:.5;cursor:not-allowed}
.eat-note{padding:9px 10px;border:1px solid #d8c5ef;border-radius:8px;color:#66438f;background:#faf6ff;font-size:8px;line-height:1.45;flex:0 0 auto}
.eat-kpis{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;flex:0 0 auto}
.eat-kpi{padding:10px;border:1px solid #dbe3e9;border-radius:9px;background:#fff}
.eat-kpi small{display:block;color:#6b7a8c;font-size:6px;font-weight:900;text-transform:uppercase}
.eat-kpi strong{display:block;margin-top:4px;color:#102c4a;font-size:22px}
.eat-card{display:flex;min-height:0;flex:1 1 auto;flex-direction:column;overflow:hidden;border:1px solid #d7e0e7;border-radius:9px;background:#fff}
.eat-card-head{display:flex;align-items:center;justify-content:space-between;padding:8px 10px;border-bottom:1px solid #e2e7eb;flex:0 0 auto}
.eat-card-head strong{font-size:9px;color:#17304e}.eat-card-head small{font-size:7px;color:#718194}
.eat-table-wrap{min-height:0;flex:1 1 auto;overflow:auto}
.eat-table{width:100%;border-collapse:collapse;table-layout:fixed;font-size:7px}
.eat-table thead{position:sticky;top:0;z-index:2}
.eat-table th{padding:8px;color:#fff;background:#173f68;font-size:6px;font-weight:900;text-align:left;text-transform:uppercase}
.eat-table td{padding:9px 8px;border-bottom:1px solid #edf1f4;color:#465a70;vertical-align:middle}
.eat-table td strong{display:block;color:#17304e;font-size:8px}.eat-table td small{display:block;margin-top:2px;color:#7a8999;font-size:6px}
.eat-empty{padding:34px 12px!important;color:#748496!important;text-align:center}
</style>

<div class="eat-page">
    <div class="eat-head">
        <div>
            <h1>Sampah E-Arsip</h1>
            <p>Registry terhapus dapat dikembalikan tanpa menyentuh Google Drive asli.</p>
        </div>
        <a href="{{ route('admin-all.e-arsip.index') }}" class="eat-btn">← DAFTAR ARSIP</a>
    </div>

    <div class="eat-note">
        <strong>Frontend siap.</strong>
        DELETE hanya memindahkan registry ke Sampah. Permanent delete OFF.
        Google Drive tetap aman. Backend Trash/Restore dibahas di chat backend.
    </div>

    <div class="eat-kpis">
        <div class="eat-kpi"><small>Di Sampah</small><strong>{{ number_format($trashTotal) }}</strong></div>
        <div class="eat-kpi"><small>Permanent Delete</small><strong>OFF</strong></div>
    </div>

    <section class="eat-card">
        <div class="eat-card-head">
            <strong>REGISTRY TERHAPUS</strong>
            <small>{{ number_format($trashTotal) }} data</small>
        </div>

        <div class="eat-table-wrap">
            <table class="eat-table">
                <thead>
                    <tr>
                        <th style="width:30%">Nama Arsip</th>
                        <th style="width:15%">Kategori</th>
                        <th style="width:20%">Dihapus Pada</th>
                        <th style="width:20%">Terakhir Oleh</th>
                        <th style="width:15%">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($trashedArchives as $archive)
                    <tr>
                        <td><strong>{{ $archive->name }}</strong><small>{{ $archive->drive_url }}</small></td>
                        <td>{{ $archive->category }}</td>
                        <td>{{ optional($archive->deleted_at)->format('d-m-Y H:i') ?? '-' }}</td>
                        <td>{{ $archive->updated_by ?: '-' }}</td>
                        <td>
                            @if($restoreReady)
                                <form method="POST" action="{{ route('admin-all.e-arsip.restore', $archive->getKey()) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="eat-btn restore">RESTORE</button>
                                </form>
                            @else
                                <button type="button" class="eat-btn restore disabled" disabled>RESTORE</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="eat-empty">Frontend Sampah siap. Data akan tampil setelah backend Trash/Restore disambungkan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
