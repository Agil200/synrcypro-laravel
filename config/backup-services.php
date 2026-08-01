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
    ],

];