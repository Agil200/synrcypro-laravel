<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BastAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'nrp',
        'nama',
        'jabatan',
        'jenis_asset',
        'departemen',
        'no_asset',
        'serial_number',
        'tanggal_ambil',
        'file_pdf',
    ];
}