<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtrPicRosterRule extends Model
{
    protected $fillable = [
        'atr_pic_roster_group_id',
        'match_type',
        'pattern',
        'priority',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(
            AtrPicRosterGroup::class,
            'atr_pic_roster_group_id'
        );
    }
}