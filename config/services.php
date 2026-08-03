<?php

return [

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),

        'secret' => env('AWS_SECRET_ACCESS_KEY'),

        'region' => env(
            'AWS_DEFAULT_REGION',
            'us-east-1'
        ),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env(
                'SLACK_BOT_USER_OAUTH_TOKEN'
            ),

            'channel' => env(
                'SLACK_BOT_USER_DEFAULT_CHANNEL'
            ),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Login SYNRGYPRO
    |--------------------------------------------------------------------------
    */

    'google' => [
        'client_id' => trim(
            (string) env('GOOGLE_CLIENT_ID')
        ),

        'client_secret' => trim(
            (string) env('GOOGLE_CLIENT_SECRET')
        ),

        'redirect' => trim(
            (string) env('GOOGLE_REDIRECT_URI')
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Sheets OAuth
    |--------------------------------------------------------------------------
    */

    'google_sheets' => [
        'client_id' => trim(
            (string) env(
                'GOOGLE_SHEETS_CLIENT_ID',
                env('GOOGLE_CLIENT_ID')
            )
        ),

        'client_secret' => trim(
            (string) env(
                'GOOGLE_SHEETS_CLIENT_SECRET',
                env('GOOGLE_CLIENT_SECRET')
            )
        ),

        'redirect_uri' => trim(
            (string) env(
                'GOOGLE_SHEETS_REDIRECT_URI',
                'http://127.0.0.1:8000/google/oauth/callback'
            )
        ),

        'allowed_email' => trim(
            (string) env(
                'GOOGLE_SHEETS_ALLOWED_EMAIL',
                'mpe.ppaba@ppa.co.id'
            )
        ),

        /*
        |--------------------------------------------------------------------------
        | Spreadsheet MASTER_DATABASE
        |--------------------------------------------------------------------------
        */

        'master_database_spreadsheet_id' => trim(
            (string) env(
                'GOOGLE_SHEETS_MASTER_DATABASE_SPREADSHEET_ID'
            )
        ),

        'master_database_range' => trim(
            (string) env(
                'GOOGLE_SHEETS_MASTER_DATABASE_RANGE',
                "'MASTER_DATABASE'!A:AZ"
            )
        ),

        'master_database_cache_ttl_seconds' => max(
            60,
            (int) env(
                'DATABASE_MASTER_CACHE_TTL_SECONDS',
                3600
            )
        ),

                /*
        |--------------------------------------------------------------------------
        | Spreadsheet UPDATE_DATA_KARYAWAN
        |--------------------------------------------------------------------------
        |
        | Menyimpan perubahan identitas, kontak, tempat tinggal,
        | kamar mess, dan pas foto karyawan.
        |
        */

        'update_data_spreadsheet_id' => trim(
            (string) env(
                'GOOGLE_SHEETS_UPDATE_DATA_SPREADSHEET_ID',
                env(
                    'GOOGLE_SHEETS_MASTER_DATABASE_SPREADSHEET_ID'
                )
            )
        ),

        'update_data_range' => trim(
            (string) env(
                'GOOGLE_SHEETS_UPDATE_DATA_RANGE',
                "'UPDATE_DATA_KARYAWAN'!A:L"
            )
        ),

        /*
        |--------------------------------------------------------------------------
        | Spreadsheet UPDATE_STATUS_KARYAWAN
        |--------------------------------------------------------------------------
        |
        | Menyimpan perubahan MUTASI, PROMOSI, RESIGN, dan PHK.
        |
        */

        'update_status_spreadsheet_id' => trim(
            (string) env(
                'GOOGLE_SHEETS_UPDATE_STATUS_SPREADSHEET_ID',
                env(
                    'GOOGLE_SHEETS_MASTER_DATABASE_SPREADSHEET_ID'
                )
            )
        ),

        'update_status_range' => trim(
            (string) env(
                'GOOGLE_SHEETS_UPDATE_STATUS_RANGE',
                "'UPDATE_STATUS_KARYAWAN'!A:J"
            )
        ),

        /*
        |--------------------------------------------------------------------------
        | Spreadsheet Monitoring SHE
        |--------------------------------------------------------------------------
        */

        'she_spreadsheet_id' => trim(
            (string) env(
                'GOOGLE_SHEETS_SHE_SPREADSHEET_ID'
            )
        ),

        'she_range' => trim(
            (string) env(
                'GOOGLE_SHEETS_SHE_RANGE',
                "'Pengajuan'!A:Z"
            )
        ),

        /*
        |--------------------------------------------------------------------------
        | Spreadsheet Monitoring Internal Upload
        |--------------------------------------------------------------------------
        */

        'internal_upload_spreadsheet_id' => trim(
            (string) env(
                'GOOGLE_SHEETS_INTERNAL_UPLOAD_SPREADSHEET_ID'
            )
        ),

        'internal_upload_range' => trim(
            (string) env(
                'GOOGLE_SHEETS_INTERNAL_UPLOAD_RANGE',
                "'DATA PERPANJANGAN'!A:V"
            )
        ),

        /*
|--------------------------------------------------------------------------
| Spreadsheet Monitoring MCU & Follow Up
|--------------------------------------------------------------------------
*/

'mcu_spreadsheet_id' => trim(
    (string) env(
        'GOOGLE_SHEETS_MCU_SPREADSHEET_ID'
    )
),

'mcu_range' => trim(
    (string) env(
        'GOOGLE_SHEETS_MCU_RANGE',
        "'PRO'!A:I"
    )
),

'mcu_sheet_gid' => (int) env(
    'GOOGLE_SHEETS_MCU_SHEET_GID',
    1692836561
),

'mcu_columns' => trim(
    (string) env(
        'GOOGLE_SHEETS_MCU_COLUMNS',
        'A:I'
    )
),

'mcu_cache_ttl_seconds' => max(
    60,
    (int) env(
        'GOOGLE_SHEETS_MCU_CACHE_TTL_SECONDS',
        300
    )
),
    ],


];