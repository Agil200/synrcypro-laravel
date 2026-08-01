<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Postmark
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Resend
    |--------------------------------------------------------------------------
    */

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Amazon SES
    |--------------------------------------------------------------------------
    */

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),

        'secret' => env('AWS_SECRET_ACCESS_KEY'),

        'region' => env(
            'AWS_DEFAULT_REGION',
            'us-east-1'
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Slack
    |--------------------------------------------------------------------------
    */

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
    | Digunakan oleh AuthController untuk login pengguna.
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
    | Digunakan untuk membaca Google Spreadsheet melalui akun
    | mpe.ppaba@ppa.co.id.
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
    ],

];