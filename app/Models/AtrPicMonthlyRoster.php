<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtrPicMonthlyRoster extends Model
{
    protected $fillable = [
        'atr_pic_roster_group_id',
        'period',
        'pic_primary',
        'pic_backup',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'period' => 'date',
        'is_active' => 'boolean',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(
            AtrPicRosterGroup::class,
            'atr_pic_roster_group_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }
}