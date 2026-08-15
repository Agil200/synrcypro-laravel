<?php

return [
    'support' => [
        'email' => 'mpe.ppaba@ppa.co.id',
        'subject' => 'SYNRGYPRO Support',
        'body' => "--\nBest Regards,\n\nMPE - Production Department",
    ],

    'modules' => [
        [
            'key' => 'suggestion',
            'name' => 'Suggestion System',
            'description' => 'Monitoring usulan dan workflow QCC, GL, serta SH.',
            'source' => 'Google Sheets',
            'status' => 'disiapkan',
            'color' => '#ef7d00',
        ],
        [
            'key' => 'ifuts',
            'name' => 'IFUTS Produksi',
            'description' => 'Dashboard, monitoring, dan input kolom milik Produksi.',
            'source' => 'Sheet PRODUKSI',
            'status' => 'disiapkan',
            'color' => '#09879a',
        ],
        [
            'key' => 'mcu-fu',
            'name' => 'MCU & FU Internal',
            'description' => 'Input MCU serta tindak lanjut internal Departemen.',
            'source' => 'Google Sheets',
            'status' => 'disiapkan',
            'color' => '#0aa768',
        ],
        [
            'key' => 'stock-opname',
            'name' => 'Stock Opname Gudang',
            'description' => 'Dashboard dan input opname barang internal Departemen.',
            'source' => 'Database Laravel',
            'status' => 'disiapkan',
            'color' => '#1778da',
        ],
        [
            'key' => 'e-arsip',
            'name' => 'E-Arsip',
            'description' => 'Akses cepat dokumen Produksi yang tersimpan di Drive.',
            'source' => 'Google Drive',
            'status' => 'aktif',
            'color' => '#5946b8',
        ],
    ],

    'e_archive' => [
        [
            'title' => 'Prosedur Departemen Produksi – Site BA',
            'url' => 'https://drive.google.com/drive/folders/1X01OjcwoWZkItRwpK8J8nZ1I5We2xECv?hl=ID',
        ],
        [
            'title' => 'Kumpulan Form Admin',
            'url' => 'https://drive.google.com/drive/folders/1umQAn1zufRo_D-ohTesPVfN12vl8R0tV',
        ],
    ],

'suggestion_system' => [
    'title' => 'Suggestion System',

    'spreadsheet_id' => '1xKBJb4H9s2NIHUJtwSBuFoxVTHrmUQMfBxC2YsYtIDk',

    'spreadsheet_url' => 'https://docs.google.com/spreadsheets/d/1xKBJb4H9s2NIHUJtwSBuFoxVTHrmUQMfBxC2YsYtIDk/edit?gid=1285318989#gid=1285318989',

    'ranges' => [
        'database' => "'DATABASE_SS'!A:AZ",
        'access_atasan' => "'ACCESS_ATASAN'!A:AZ",
    ],

    'tabs' => [
        'DATABASE_SS',
        'ATTACHMENTS',
        'ACCESS_ATASAN',
        'DASHBOARD_SS',
    ],
        'access_rules' => [
            [
                'role' => 'ADMIN',
                'scope' => 'Tim QCC',
                'description' => 'Dapat melihat seluruh data dan workflow Suggestion System.',
            ],
            [
                'role' => 'GL',
                'scope' => 'Group Leader',
                'description' => 'Hanya menu/verifikasi GL yang ditampilkan.',
            ],
            [
                'role' => 'SH',
                'scope' => 'Section Head',
                'description' => 'Hanya menu/persetujuan SH yang ditampilkan.',
            ],
        ],
        'workflow' => [
            'Submitted',
            'Verifikasi GL / Tim QCC',
            'Persetujuan SH',
            'Selesai',
        ],
    ],
];
