<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratKeluar extends Model
{
    use HasFactory;

    protected $table = 'surat_keluar';

    protected $fillable = [
        'tanggal_surat',
        'nomor_surat',
        'tujuan_surat',
        'nama',
        'nrp',
        'jenis_surat',
        'file_path',
        'file_nama_asli',
        'created_by',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
    ];
}
