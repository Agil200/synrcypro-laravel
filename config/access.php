<?php

$parseEmails = static function (?string $value): array {
    return array_values(array_filter(array_map(
        static fn (string $email): string => strtolower(trim($email)),
        explode(',', (string) $value)
    )));
};

return [
    /*
    |--------------------------------------------------------------------------
    | Bootstrap Super Administrator
    |--------------------------------------------------------------------------
    */
    'super_admin_emails' => $parseEmails(
        env('SYNRGYPRO_SUPER_ADMIN_EMAILS', '')
    ),

    /*
    |--------------------------------------------------------------------------
    | Login Google Legacy Allowlist
    |--------------------------------------------------------------------------
    | User yang sudah didaftarkan melalui User Management juga dapat login.
    | GOOGLE_SHEETS_ALLOWED_EMAIL tetap dibaca sebagai fallback agar login lama
    | tidak terputus pada tahap migrasi.
    */
    'login_allowed_emails' => $parseEmails(
        env(
            'SYNRGYPRO_ALLOWED_EMAILS',
            env('GOOGLE_SHEETS_ALLOWED_EMAIL', '')
        )
    ),

    'contact_email' => env(
        'SYNRGYPRO_CONTACT_EMAIL',
        'mpe.ppaba@ppa.co.id'
    ),

    /*
    |--------------------------------------------------------------------------
    | Gate Permission
    |--------------------------------------------------------------------------
    */
    'permissions' => [
        'admin-all.view',
        'users.view',
        'users.create',
        'users.update',
        'users.change-status',
        'users.assign-role',
        'roles.view',
        'roles.update',
        'audit-logs.view',
        'suggestion-system.view',
        'ifuts.view',
        'ifuts.update',
        'ifuts.comment',
        'mcu-fu.view',
        'mcu-fu.update',
        'mcu-fu.audit',
        'stock-opname.view',
        'stock-opname.create',
        'stock-opname.manage-master',
        'e-arsip.view',
        'e-arsip.manage',
    ],
];
