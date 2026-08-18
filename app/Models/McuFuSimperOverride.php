<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class McuFuSimperOverride extends Model
{
    protected $fillable = [
        'nrp',
        'nama',
        'expired_sim_dlt',
        'note',
        'updated_by_name',
        'updated_by_email',
    ];

    protected $casts = [
        'expired_sim_dlt' => 'date',
    ];
}