<?php

namespace App\Models;

use App\Services\AtrPicRosterService;

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
        'dept',
        'job_title',
        'position',
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

    /** Dokumentasi aktif terakhir saja. */
    public function latestCoaching(): HasOne
    {
        return $this->hasOne(AtrCoachingCounseling::class)
            ->where('status', 'COMPLETED')
            ->latestOfMany();
    }

    /** Dokumentasi yang terakhir dibatalkan, untuk badge PERLU ULANG. */
    public function latestCancelledCoaching(): HasOne
    {
        return $this->hasOne(AtrCoachingCounseling::class)
            ->where('status', 'CANCELLED')
            ->latestOfMany();
    }

    public function totalAbsence(): int
    {
        return (int) $this->sick
            + (int) $this->permission
            + (int) $this->alpha;
    }

    /**
     * PIC roster utama berdasarkan POSISI.
     * Mapping mengikuti menu Pengaturan PIC Roster yang aktif saat ini.
     */
    public function picRosterName(): string
    {
        return app(AtrPicRosterService::class)->resolveName(
            (string) $this->position,
            $this->period
        );
    }
}