<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtrCoachingHistory extends Model
{
    protected $fillable = [
        'atr_coaching_counseling_id',
        'action',
        'from_status',
        'to_status',
        'actor_user_id',
        'actor_name',
        'notes',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function coaching(): BelongsTo
    {
        return $this->belongsTo(
            AtrCoachingCounseling::class,
            'atr_coaching_counseling_id'
        );
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}