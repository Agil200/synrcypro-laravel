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
    */

    'google' => [

        'client_id' => trim(
            (string) env(
                'GOOGLE_CLIENT_ID'
            )
        ),

        'client_secret' => trim(
            (string) env(
                'GOOGLE_CLIENT_SECRET'
            )
        ),

        'redirect' => trim(
            (string) env(
                'GOOGLE_REDIRECT_URI'
            )
        ),

    ],

    /*
    |--------------------------------------------------------------------------
    | Gemini AI - SYNRGY Assistant
    |--------------------------------------------------------------------------
    |
    | Digunakan untuk chatbot AI pada Dashboard Operator SYNRGYPRO.
    | API Key disimpan di .env dan jangan ditaruh di Blade/JavaScript.
    |
    */

    'gemini' => [

        'key' => trim(
            (string) env(
                'GEMINI_API_KEY'
            )
        ),

        'model' => trim(
            (string) env(
                'GEMINI_MODEL',
                'gemini-3.6-flash'
            )
        ),

        'url' => rtrim(
            trim(
                (string) env(
                    'GEMINI_API_URL',
                    'https://generativelanguage.googleapis.com/v1beta/interactions'
                )
            ),
            '/'
        ),

    ],


    /*
    |--------------------------------------------------------------------------
    | MINA Knowledge Base - Google Spreadsheet
    |--------------------------------------------------------------------------
    |
    | Struktur Sheet:
    | A=ID, B=KATEGORI, C=KEYWORDS, D=PERTANYAAN,
    | E=JAWABAN, F=LINK, G=SUMBER, H=STATUS
    |
    */

    'knowledge_base' => [
        'spreadsheet_id' => trim(
            (string) env('GOOGLE_KNOWLEDGE_SHEET_ID', '')
        ),

        'range' => trim(
            (string) env(
                'GOOGLE_KNOWLEDGE_RANGE',
                'SYNRGY_AI_KNOWLEDGE!A:H'
            )
        ),

        'api_key' => trim(
            (string) env('GOOGLE_API_KEY', '')
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Sheets OAuth
    |--------------------------------------------------------------------------
    */

    'google_sheets' => [

        /*
        |--------------------------------------------------------------------------
        | OAuth Credentials
        |--------------------------------------------------------------------------
        */

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
        | Spreadsheet Pusat File Google Drive
        |--------------------------------------------------------------------------
        |
        | Secara bawaan membaca sheet pertama. Nilai range dapat diganti menjadi
        | "'ARSIP_DRIVE'!A:Z" melalui .env jika tab khusus sudah dibuat.
        |
        */

        'drive_files_spreadsheet_id' => trim(
            (string) env(
                'GOOGLE_SHEETS_DRIVE_FILES_SPREADSHEET_ID',
                '1lpHhFKkpYBUAmAHcfW2EWCn_g98CB-sicTQKr8Tgrpc'
            )
        ),

        'drive_files_range' => trim(
            (string) env(
                'GOOGLE_SHEETS_DRIVE_FILES_RANGE',
                'A:Z'
            )
        ),

        'drive_files_source_url' => trim(
            (string) env(
                'GOOGLE_SHEETS_DRIVE_FILES_SOURCE_URL',
                'https://docs.google.com/spreadsheets/d/1lpHhFKkpYBUAmAHcfW2EWCn_g98CB-sicTQKr8Tgrpc/edit?usp=sharing'
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

        /*
        |--------------------------------------------------------------------------
        | Spreadsheet Monitoring Test BNN
        |--------------------------------------------------------------------------
        |
        | Spreadsheet ID:
        | 1enc9LxoaGo-ZNjxJ53UY24N3y-TTHn-W8P4UzmtXADU
        |
        | Sheet GID:
        | 615934612
        |
        */

        'test_bnn_spreadsheet_id' => trim(
            (string) env(
                'GOOGLE_SHEETS_TEST_BNN_SPREADSHEET_ID',
                '1enc9LxoaGo-ZNjxJ53UY24N3y-TTHn-W8P4UzmtXADU'
            )
        ),

        'test_bnn_range' => trim(
            (string) env(
                'GOOGLE_SHEETS_TEST_BNN_RANGE',
                "'DAFTAR TEST BNN'!A:AZ"
            )
        ),

        'test_bnn_sheet_gid' => (int) env(
            'GOOGLE_SHEETS_TEST_BNN_SHEET_GID',
            615934612
        ),

        'test_bnn_columns' => trim(
            (string) env(
                'GOOGLE_SHEETS_TEST_BNN_COLUMNS',
                'A:AZ'
            )
        ),

        'test_bnn_cache_ttl_seconds' => max(
            60,
            (int) env(
                'GOOGLE_SHEETS_TEST_BNN_CACHE_TTL_SECONDS',
                300
            )
        ),

        /*
        |--------------------------------------------------------------------------
        | Spreadsheet Monitoring Sepatu Safety
        |--------------------------------------------------------------------------
        |
        | Menggunakan koneksi OAuth yang sama dengan GoogleSheetsService.
        | GID mengacu pada tab DATABASE di spreadsheet monitoring sepatu.
        |
        */

        'safety_shoe_spreadsheet_id' => trim(
            (string) env(
                'GOOGLE_SHEETS_SAFETY_SHOE_SPREADSHEET_ID',
                '1cn4kiRslpyK7BxtHk5blqfzn7U1xXznixH4fculPIJM'
            )
        ),

        'safety_shoe_sheet_gid' => (int) env(
            'GOOGLE_SHEETS_SAFETY_SHOE_SHEET_GID',
            65848559
        ),

        'safety_shoe_columns' => trim(
            (string) env(
                'GOOGLE_SHEETS_SAFETY_SHOE_COLUMNS',
                'A:K'
            )
        ),

        'safety_shoe_cache_ttl_seconds' => max(
            60,
            (int) env(
                'GOOGLE_SHEETS_SAFETY_SHOE_CACHE_TTL_SECONDS',
                60
            )
        ),

    ],

];
