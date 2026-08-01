<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ApdRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal_pengajuan',
        'nrp',
        'nama',
        'jabatan',
        'ukuran_sepatu',
        'item_helm',
        'item_sepatu_safety',
        'item_rompi',
        'item_kacamata',
        'item_ear_plug',
        'status_sepatu',
        'picked_up_at',
        'created_by',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'date',
        'item_helm' => 'boolean',
        'item_sepatu_safety' => 'boolean',
        'item_rompi' => 'boolean',
        'item_kacamata' => 'boolean',
        'item_ear_plug' => 'boolean',
        'picked_up_at' => 'datetime',
    ];

    public function pickup(): HasOne
    {
        return $this->hasOne(ApdPickup::class);
    }

    public function getItemsLabelAttribute(): array
    {
        return array_values(array_filter([
            $this->item_helm ? 'Helm' : null,
            $this->item_sepatu_safety ? 'Sepatu Safety' : null,
            $this->item_rompi ? 'Rompi' : null,
            $this->item_kacamata ? 'Kacamata' : null,
            $this->item_ear_plug ? 'Ear Plug' : null,
        ]));
    }
}
