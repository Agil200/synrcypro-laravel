@extends('layouts.app')


@section('content')


@include('manpower.cc-st-sp.partials.styles')


<div class="ccsp-page">


<section class="ccsp-card">


<div class="ccsp-header">


<div>

<h1 class="ccsp-title">
TEST BNN
</h1>


<p class="ccsp-subtitle">
Input pemeriksaan BNN karyawan berdasarkan NRP
</p>


</div>



<button 
type="button"
class="ccsp-secondary"
onclick="window.location.href='{{ route('manpower') }}'">

Kembali

</button>


</div>





<form method="POST"
action="{{ route('bnn.store') }}">

@csrf



<div class="ccsp-reference-body">



<div class="ccsp-reference-row">

<label class="ccsp-reference-label">
NRP *
</label>


<div class="ccsp-reference-control">


<input
type="text"
id="nrp"
name="nrp"
class="ccsp-reference-input"
placeholder="Masukkan NRP"
onkeyup="cariNRP()"
required>


</div>

</div>





<div class="ccsp-reference-row">

<label class="ccsp-reference-label">
NAMA
</label>


<div class="ccsp-reference-control">

<input
type="text"
id="nama"
name="nama"
class="ccsp-reference-input"
readonly>

</div>

</div>





<div class="ccsp-reference-row">

<label class="ccsp-reference-label">
JENIS KELAMIN
</label>


<div class="ccsp-reference-control">

<input
type="text"
id="jenis_kelamin"
name="jenis_kelamin"
class="ccsp-reference-input"
readonly>

</div>

</div>






<div class="ccsp-reference-row">

<label class="ccsp-reference-label">
PERUSAHAAN
</label>


<div class="ccsp-reference-control">

<input
type="text"
id="perusahaan"
name="perusahaan"
class="ccsp-reference-input"
readonly>

</div>

</div>






<div class="ccsp-reference-row">

<label class="ccsp-reference-label">
DEPT
</label>


<div class="ccsp-reference-control">

<input
type="text"
id="dept"
name="dept"
class="ccsp-reference-input"
readonly>

</div>

</div>






<div class="ccsp-reference-row">

<label class="ccsp-reference-label">
POSISI
</label>


<div class="ccsp-reference-control">

<input
type="text"
id="posisi"
name="posisi"
class="ccsp-reference-input"
readonly>

</div>

</div>






<div class="ccsp-reference-row">

<label class="ccsp-reference-label">
USIA
</label>


<div class="ccsp-reference-control">

<input
type="text"
id="usia"
name="usia"
class="ccsp-reference-input"
readonly>

</div>

</div>







<div class="ccsp-reference-row">

<label class="ccsp-reference-label">
KONTAK
</label>


<div class="ccsp-reference-control">

<input
type="text"
id="kontak"
name="kontak"
class="ccsp-reference-input"
readonly>

</div>

</div>







<div class="ccsp-reference-row">

<label class="ccsp-reference-label">
NIK
</label>


<div class="ccsp-reference-control">

<input
type="text"
id="nik"
name="nik"
class="ccsp-reference-input"
readonly>

</div>

</div>








<div class="ccsp-reference-row">

<label class="ccsp-reference-label">
TANGGAL PEMERIKSAAN *
</label>


<div class="ccsp-reference-control">

<input
type="date"
name="tanggal_pemeriksaan"
class="ccsp-reference-input"
required>

</div>

</div>







<div class="ccsp-reference-row">

<label class="ccsp-reference-label">
AKOMODASI *
</label>


<div class="ccsp-reference-control">


<select
name="akomodasi"
class="ccsp-reference-input"
required>


<option value="DIANTAR DI MESS">
DIANTAR DI MESS
</option>


<option value="BERANGKAT SENDIRI">
BERANGKAT SENDIRI
</option>


<option value="BANGKO">
BANGKO
</option>


</select>


</div>

</div>



</div>





<div class="ccsp-form-footer">


<button
type="submit"
class="ccsp-primary">

SIMPAN DATA BNN

</button>


</div>



</form>



</section>


</div>





<script>

function cariNRP(){

    let nrp = document.getElementById('nrp').value;


    if(nrp.length < 3){

        return;

    }



    fetch("{{ url('/manpower/bnn/cari') }}/" + nrp)


    .then(response => response.json())


    .then(data => {


        console.log(data);



        if(data.status === false){

            alert(data.message);

            return;

        }



        document.getElementById('nama').value = data.nama ?? '';

        document.getElementById('jenis_kelamin').value = data.jenis_kelamin ?? '';

        document.getElementById('perusahaan').value = data.perusahaan ?? '';

        document.getElementById('dept').value = data.dept ?? '';

        document.getElementById('posisi').value = data.posisi ?? '';

        document.getElementById('usia').value = data.usia ?? '';

        document.getElementById('kontak').value = data.kontak ?? '';

        document.getElementById('nik').value = data.nik ?? '';



    })


    .catch(error => {


        console.error(
            "Error cari NRP:",
            error
        );


    });



}


</script>



@endsection