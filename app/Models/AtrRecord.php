<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AtrRecord extends Model
{
    protected $fillable = [
        'atr_import_id',
        'period',
        'nrp',
        'employee_name',
        'job_title',
        'site',
        'atr',
        'sick',
        'permission',
        'alpha',
        'status',
        'source_row',
    ];

    protected function casts(): array
    {
        return [
            'period' => 'date',
            'atr' => 'decimal:2',
            'sick' => 'integer',
            'permission' => 'integer',
            'alpha' => 'integer',
        ];
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(AtrImport::class, 'atr_import_id');
    }

    public function coachingCounselings(): HasMany
    {
        return $this->hasMany(AtrCoachingCounseling::class);
    }

    public function latestCoaching(): HasOne
    {
        return $this->hasOne(AtrCoachingCounseling::class)->latestOfMany();
    }

    public function totalAbsence(): int
    {
        return $this->sick + $this->permission + $this->alpha;
    }
}
