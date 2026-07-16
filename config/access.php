<?php

return [
    'allowed_emails' => array_values(
        array_filter(
            array_map(
                static fn (string $email): string =>
                    strtolower(trim($email)),

                explode(
                    ',',
                    (string) env('GOOGLE_ALLOWED_EMAILS', '')
                )
            )
        )
    ),

    'contact_email' => env(
        'CONTACT_EMAIL',
        'mpe.ppaba@ppa.co.id'
    ),
];