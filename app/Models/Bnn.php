<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Bnn extends Model
{


    protected $table = 'bnn_tests';



    protected $fillable = [

        'nrp',
        'nama',
        'jenis_kelamin',
        'perusahaan',
        'dept',
        'posisi',
        'usia',
        'kontak',
        'nik',
        'tanggal_pemeriksaan',
        'akomodasi'

    ];


}