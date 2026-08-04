<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StSpRecord extends Model
{
    use HasFactory;

    protected $table = 'st_sp_records';

    protected $fillable = [
        'nrp',
        'nama',
        'jabatan',
        'jenis_pelanggaran',
        'tanggal',
        'expired_date',
        'tempat_kejadian',
        'jenis',
        'deskripsi',
        'atasan',
        'file_path',
        'file_nama_asli',
        'status',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'expired_date' => 'date',
    ];
}
