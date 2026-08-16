<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Apps Script Web App URL
    |--------------------------------------------------------------------------
    |
    | Gunakan URL deployment /exec yang sama dengan CONFIG.WEB_APP_URL
    | pada Apps Script Suggestion System.
    |
    */

    'url' => env(
        'SUGGESTION_APPS_SCRIPT_URL',
        ''
    ),

    /*
    |--------------------------------------------------------------------------
    | Shared secret Laravel <-> Apps Script
    |--------------------------------------------------------------------------
    |
    | WAJIB sama dengan Script Property:
    | SYNRGYPRO_LARAVEL_BRIDGE_SECRET
    |
    */

    'secret' => env(
        'SUGGESTION_APPS_SCRIPT_SECRET',
        ''
    ),

    'timeout' => (int) env(
        'SUGGESTION_APPS_SCRIPT_TIMEOUT',
        20
    ),
];
