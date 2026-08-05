<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ambang ATR Produksi
    |--------------------------------------------------------------------------
    |
    | AMAN        : ATR >= aman_minimum
    | MONITORING  : monitoring_minimum <= ATR < aman_minimum
    | PEMANGGILAN : ATR < monitoring_minimum
    |
    */
    'aman_minimum' => (float) env('ATR_AMAN_MINIMUM', 98.5),
    'monitoring_minimum' => (float) env('ATR_MONITORING_MINIMUM', 95.0),

    'department' => 'PRODUKSI',
    'document_number' => 'PPA-PTBA-F-SHE-14D',

    'workbook' => [
        'database_sheet' => 'DATABASE_KARYAWAN',
        'source_sheet' => 'ATR_SOURCE',
        'database_headers' => [
            'NRP',
            'NAMA',
            'JABATAN',
            'SITE',
        ],
        'source_headers' => [
            'PERIODE',
            'NRP',
            'ATR',
            'S',
            'I',
            'A',
        ],
    ],

    'upload' => [
        'max_kilobytes' => 10240,
        'preview_rows' => 20,
        'allowed_extensions' => ['xlsx'],
    ],
];
