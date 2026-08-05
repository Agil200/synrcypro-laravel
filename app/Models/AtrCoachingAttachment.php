<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtrCoachingAttachment extends Model
{
    protected $fillable = [
        'atr_coaching_counseling_id',
        'type',
        'original_name',
        'stored_path',
        'mime_type',
        'size_bytes',
    ];

    public function coachingCounseling(): BelongsTo
    {
        return $this->belongsTo(AtrCoachingCounseling::class);
    }
}
