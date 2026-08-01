<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoachingCounselling extends Model
{
    use HasFactory;

    protected $table = 'coaching_counsellings';

    protected $fillable = [
        'nrp',
        'materi',
        'perihal',
        'tanggal',
        'shift',
        'keterangan',
        'dibuat_oleh',
        'file_path',
        'file_nama_asli',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];
}