<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EArchiveLink extends Model
{
    use SoftDeletes;

    protected $table = 'e_archive_links';

    protected $fillable = [
        'name',
        'category',
        'drive_url',
        'description',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];


    /*
    |--------------------------------------------------------------------------
    | Virtual attribute: link_type
    |--------------------------------------------------------------------------
    | Tidak menambah kolom database.
    | Jenis link otomatis dideteksi dari URL Google.
    */

    public function getLinkTypeAttribute(): string
    {
        $url = strtolower(
            trim(
                (string) $this->drive_url
            )
        );

        if (
            str_contains(
                $url,
                '/spreadsheets/'
            )
        ) {
            return 'SPREADSHEET';
        }

        if (
            str_contains(
                $url,
                '/document/'
            )
        ) {
            return 'DOCS';
        }

        if (
            str_contains(
                $url,
                '/forms/'
            )
        ) {
            return 'FORM';
        }

        if (
            str_contains(
                $url,
                '/presentation/'
            )
        ) {
            return 'SLIDES';
        }

        if (
            str_contains(
                $url,
                '/drive/folders/'
            )
        ) {
            return 'FOLDER';
        }

        return 'DRIVE';
    }
}
