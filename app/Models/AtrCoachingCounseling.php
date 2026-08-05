<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AtrCoachingCounseling extends Model
{
    protected $fillable = [
        'atr_record_id',
        'document_number',
        'coaching_date',
        'shift',
        'location',
        'coaching_time',
        'material_personal',
        'material_family',
        'material_work',
        'notes',
        'created_by_name',
        'created_by_user_id',
        'status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'coaching_date' => 'date',
            'material_personal' => 'boolean',
            'material_family' => 'boolean',
            'material_work' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function atrRecord(): BelongsTo
    {
        return $this->belongsTo(AtrRecord::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(AtrCoachingAttachment::class);
    }
}
