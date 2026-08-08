<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AtrImport extends Model
{
    use HasFactory;

    protected $table = 'atr_imports';

    protected $fillable = [
        'file_name',
        'stored_path',
        'file_hash',
        'status',
        'import_mode',
        'replaces_import_id',
        'total_rows',
        'valid_rows',
        'invalid_rows',
        'imported_rows',
        'period_min',
        'period_max',
        'periods',
        'errors',
        'uploaded_by',
        'imported_at',
        'cancellation_reason',
        'cancelled_at',
        'cancelled_by',
    ];

    protected $casts = [
        'total_rows' => 'integer',
        'valid_rows' => 'integer',
        'invalid_rows' => 'integer',
        'imported_rows' => 'integer',
        'replaces_import_id' => 'integer',
        'period_min' => 'date',
        'period_max' => 'date',
        'periods' => 'array',
        'errors' => 'array',
        'imported_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function records(): HasMany
    {
        return $this->hasMany(AtrRecord::class, 'atr_import_id');
    }

    /** Snapshot lama yang direvisi oleh import ini. */
    public function replacesImport(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replaces_import_id');
    }

    /** Import yang menggantikan snapshot ini. */
    public function replacementImport(): HasOne
    {
        return $this->hasOne(self::class, 'replaces_import_id');
    }
}