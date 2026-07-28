<?php

declare(strict_types=1);

return [
    'locale' => env('NUEWIRE_MAIL_LOCALE', 'id'),
    'supported_locales' => ['id', 'en'],
    'remember_locale' => (bool) env('NUEWIRE_MAIL_REMEMBER_LOCALE', true),
    'locale_session_key' => 'nuewire.mail.locale',

    'mailer' => 'nuewire',
    'set_as_default' => true,

    'settings_path' => storage_path('app/private/.nuewire/emails.json'),

    'authorization' => [
        'require_authenticated_user' => (bool) env('NUEWIRE_MAIL_REQUIRE_AUTH', true),
        'gate' => env('NUEWIRE_MAIL_GATE'),
        'guard' => env('NUEWIRE_MAIL_GUARD'),
    ],
];
