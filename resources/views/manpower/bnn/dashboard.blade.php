<div class="mp-dashboard">


<section class="mp-dashboard-card mp-dashboard-hero">

<div>

<h1>
Dashboard Test BNN
</h1>

<p>
Monitoring Pemeriksaan BNN Karyawan
</p>

</div>


<form method="GET">


<input
type="month"
name="bulan"
value="{{ $bulan ?? date('Y-m') }}"
class="mp-dashboard-month"
onchange="this.form.submit()"
>


</form>


</section>





<section class="mp-dashboard-summary">


<div class="mp-dashboard-card mp-summary-card">

<div class="mp-summary-label">
TOTAL TEST BNN
</div>


<div class="mp-summary-value">

{{ number_format($summary['total'] ?? 0) }}

</div>


</div>





<div class="mp-dashboard-card mp-summary-card month">


<div class="mp-summary-label">
BULAN INI
</div>


<div class="mp-summary-value">

{{ number_format($summary['month'] ?? 0) }}

</div>


</div>





<div class="mp-dashboard-card mp-summary-card active">


<div class="mp-summary-label">
SUDAH TEST
</div>


<div class="mp-summary-value">

{{ number_format($summary['done'] ?? 0) }}

</div>


</div>





<div class="mp-dashboard-card mp-summary-card expired">


<div class="mp-summary-label">
BELUM TEST
</div>


<div class="mp-summary-value">

{{ number_format($summary['pending'] ?? 0) }}

</div>


</div>



</section>







<section class="mp-dashboard-card mp-dashboard-section">


<div class="mp-section-header">

<h2>
Statistik Akomodasi BNN
</h2>


</div>



<div class="mp-feature-grid">



<div class="mp-feature-card green">

<div class="mp-feature-icon">
🚐
</div>


<div>
<b>
DIANTAR DI MESS
</b>
</div>


<div class="mp-feature-value">

{{ $akomodasi['mess'] ?? 0 }}

</div>


</div>






<div class="mp-feature-card blue">

<div class="mp-feature-icon">
🚗
</div>


<div>
<b>
BERANGKAT SENDIRI
</b>
</div>


<div class="mp-feature-value">

{{ $akomodasi['sendiri'] ?? 0 }}

</div>


</div>






<div class="mp-feature-card orange">

<div class="mp-feature-icon">
🏢
</div>


<div>
<b>
BANGKO
</b>
</div>


<div class="mp-feature-value">

{{ $akomodasi['bangko'] ?? 0 }}

</div>


</div>



</div>


</section>









<section class="mp-dashboard-card mp-dashboard-section">


<div class="mp-section-header">

<h2>
Trend Pemeriksaan BNN
</h2>


</div>



<div class="mp-trend-body">


@php
$max = ($maxTrend ?? 1);
if($max == 0){
    $max = 1;
}
@endphp



@if(isset($trend) && count($trend)>0)


@foreach($trend as $item)



<div class="mp-trend-row">


<span class="mp-trend-label">

{{ $item['bulan'] ?? '-' }}

</span>




<div class="mp-trend-track">


<span
class="mp-trend-segment peringatan"
style="
width: {{ (($item['total'] ?? 0) / $max) * 100 }}%;
">
</span>


</div>




<span class="mp-trend-value">

{{ $item['total'] ?? 0 }}

</span>


</div>



@endforeach


@else


<div class="mp-dashboard-empty">

Belum ada data trend BNN

</div>


@endif



</div>



</section>










<section class="mp-dashboard-card mp-dashboard-section">


<div class="mp-section-header">

<h2>
Aktivitas Terbaru BNN
</h2>


</div>





@if(isset($recent) && count($recent)>0)



@foreach($recent as $item)



<div class="mp-activity-item">


<span class="mp-activity-icon red">
BNN
</span>




<div class="mp-activity-main">


<strong>

{{ $item->nama ?? '-' }}

</strong>



<span>

NRP :
{{ $item->nrp ?? '-' }}

|

{{ $item->akomodasi ?? '-' }}

</span>


</div>




<div class="mp-activity-date">


@if($item->tanggal_pemeriksaan)

{{ 
\Carbon\Carbon::parse(
$item->tanggal_pemeriksaan
)->format('d-m-Y')
}}


@else

-

@endif


</div>



</div>



@endforeach



@else


<div class="mp-dashboard-empty">

Belum ada aktivitas BNN

</div>


@endif



</section>



</div>