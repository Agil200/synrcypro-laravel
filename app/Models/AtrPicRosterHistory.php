<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtrPicRosterHistory extends Model
{
    protected $fillable = [
        'atr_pic_roster_group_id',
        'atr_pic_roster_rule_id',
        'action',
        'actor_user_id',
        'actor_name',
        'notes',
        'before_data',
        'after_data',
    ];

    protected function casts(): array
    {
        return [
            'before_data' => 'array',
            'after_data' => 'array',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(
            AtrPicRosterGroup::class,
            'atr_pic_roster_group_id'
        );
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(
            AtrPicRosterRule::class,
            'atr_pic_roster_rule_id'
        );
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}