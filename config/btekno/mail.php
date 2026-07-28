<?php

declare(strict_types=1);

return [
    'locale' => env('BTEKNO_MAIL_LOCALE', 'id'),
    'supported_locales' => ['id', 'en'],
    'remember_locale' => (bool) env('BTEKNO_MAIL_REMEMBER_LOCALE', true),
    'locale_session_key' => 'btekno.mail.locale',

    'mailer' => 'btekno',
    'set_as_default' => true,

    'settings_path' => storage_path('app/private/.btekno/emails.json'),

    'authorization' => [
        'require_authenticated_user' => (bool) env('BTEKNO_MAIL_REQUIRE_AUTH', true),
        'gate' => env('BTEKNO_MAIL_GATE'),
        'guard' => env('BTEKNO_MAIL_GUARD'),
    ],
];
