<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApdPickup extends Model
{
    use HasFactory;

    protected $fillable = [
        'apd_request_id',
        'tanggal_pengambilan',
        'diambil_oleh',
        'petugas',
        'photo_path',
        'photo_original_name',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_pengambilan' => 'date',
    ];

    public function apdRequest(): BelongsTo
    {
        return $this->belongsTo(ApdRequest::class);
    }
}
