<?php

declare(strict_types=1);

namespace Nuewire\Mail\Tests;

use Nuewire\Mail\MailServiceProvider;
use Illuminate\Support\ServiceProvider;

final class ConfigurationTest extends TestCase
{
    public function test_configuration_uses_the_nested_nuewire_key(): void
    {
        self::assertSame('id', config('nuewire.mail.locale'));
        self::assertSame(
            storage_path('app/private/.nuewire/emails.json'),
            config('nuewire.mail.settings_path'),
        );
    }

    public function test_configuration_is_published_to_the_nuewire_directory(): void
    {
        $paths = ServiceProvider::pathsToPublish(
            MailServiceProvider::class,
            'nuewire-mail-config',
        );

        self::assertContains(config_path('nuewire/mail.php'), array_values($paths));
    }
}
