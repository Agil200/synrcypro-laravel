<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AtrImport extends Model
{
    use HasFactory;

    protected $table = 'atr_imports';

    protected $fillable = [
        'file_name',
        'stored_path',
        'file_hash',
        'status',
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
    ];

    protected $casts = [
        'total_rows' => 'integer',
        'valid_rows' => 'integer',
        'invalid_rows' => 'integer',
        'imported_rows' => 'integer',
        'period_min' => 'date',
        'period_max' => 'date',
        'periods' => 'array',
        'errors' => 'array',
        'imported_at' => 'datetime',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function records(): HasMany
    {
        return $this->hasMany(AtrRecord::class, 'atr_import_id');
    }
}