<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AtrPicRosterGroup extends Model
{
    protected $fillable = [
        'code',
        'label',
        'pic_primary',
        'pic_backup',
        'effective_from',
        'effective_to',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function rules(): HasMany
    {
        return $this->hasMany(
            AtrPicRosterRule::class,
            'atr_pic_roster_group_id'
        )->orderBy('priority')->orderByDesc('id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(
            AtrPicRosterHistory::class,
            'atr_pic_roster_group_id'
        )->latest('id');
    }
}