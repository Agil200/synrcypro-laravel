<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{


public function up()
{


Schema::create('bnn_tests', function (Blueprint $table) {


$table->id();


$table->string('nrp');


$table->string('nama');


$table->string('jenis_kelamin')
->nullable();


$table->string('perusahaan')
->nullable();


$table->string('dept')
->nullable();


$table->string('posisi')
->nullable();


$table->integer('usia')
->nullable();


$table->string('kontak')
->nullable();


$table->string('nik')
->nullable();



$table->date(
'tanggal_pemeriksaan'
);



$table->enum(
'akomodasi',
[
'DIANTAR DI MESS',
'BERANGKAT SENDIRI',
'BANGKO'
]
);



$table->timestamps();


});


}



public function down()
{

Schema::dropIfExists(
'bnn_tests'
);

}


};