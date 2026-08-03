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
        'status_helm',
        'status_sepatu',
        'status_rompi',
        'status_kacamata',
        'status_ear_plug',
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
        return array_column(
            $this->items_with_status,
            'label'
        );
    }

    /**
     * Daftar barang yang dipilih beserta posisi terakhirnya.
     */
    public function getItemsWithStatusAttribute(): array
    {
        return array_values(array_filter([
            $this->item_helm
                ? [
                    'key' => 'helm',
                    'label' => 'Helm',
                    'status' => $this->status_helm,
                ]
                : null,

            $this->item_sepatu_safety
                ? [
                    'key' => 'sepatu_safety',
                    'label' => 'Sepatu Safety',
                    'status' => $this->status_sepatu,
                ]
                : null,

            $this->item_rompi
                ? [
                    'key' => 'rompi',
                    'label' => 'Rompi',
                    'status' => $this->status_rompi,
                ]
                : null,

            $this->item_kacamata
                ? [
                    'key' => 'kacamata',
                    'label' => 'Kacamata',
                    'status' => $this->status_kacamata,
                ]
                : null,

            $this->item_ear_plug
                ? [
                    'key' => 'ear_plug',
                    'label' => 'Ear Plug',
                    'status' => $this->status_ear_plug,
                ]
                : null,
        ]));
    }
}