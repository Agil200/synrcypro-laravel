<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class McuFuInternalHistory extends Model
{
    protected $fillable = [
        'sheet_row',
        'nrp',
        'nama',
        'action',
        'before_data',
        'after_data',
        'user_name',
        'user_email',
    ];

    protected $casts = [
        'before_data' => 'array',
        'after_data' => 'array',
    ];
}
