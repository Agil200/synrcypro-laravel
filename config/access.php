<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Daftar Email Google yang Diizinkan
    |--------------------------------------------------------------------------
    */

    'allowed_emails' => array_values(
        array_unique(
            array_filter(
                array_map(
                    static fn (string $email): string =>
                        strtolower(trim($email)),

                    explode(
                        ',',
                        (string) env(
                            'GOOGLE_ALLOWED_EMAILS',
                            ''
                        )
                    )
                )
            )
        )
    ),

    /*
    |--------------------------------------------------------------------------
    | Email Kontak SYNRGYPRO
    |--------------------------------------------------------------------------
    */

    'contact_email' => env(
        'CONTACT_EMAIL',
        'mpe.ppaba@ppa.co.id'
    ),
];