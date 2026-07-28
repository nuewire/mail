<?php

declare(strict_types=1);

namespace Btekno\Mail\Tests;

use Btekno\Mail\MailServiceProvider;
use Illuminate\Support\ServiceProvider;

final class ConfigurationTest extends TestCase
{
    public function test_configuration_uses_the_nested_btekno_key(): void
    {
        self::assertSame('id', config('btekno.mail.locale'));
        self::assertSame(
            storage_path('app/private/.btekno/emails.json'),
            config('btekno.mail.settings_path'),
        );
    }

    public function test_configuration_is_published_to_the_btekno_directory(): void
    {
        $paths = ServiceProvider::pathsToPublish(
            MailServiceProvider::class,
            'btekno-mail-config',
        );

        self::assertContains(config_path('btekno/mail.php'), array_values($paths));
    }
}
