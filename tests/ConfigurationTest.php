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

    public function test_views_and_translations_use_the_shared_vendor_directory(): void
    {
        $viewPaths = ServiceProvider::pathsToPublish(
            MailServiceProvider::class,
            'nuewire-mail-views',
        );

        $translationPaths = ServiceProvider::pathsToPublish(
            MailServiceProvider::class,
            'nuewire-mail-translations',
        );

        self::assertContains(
            resource_path('views/vendor/nuewire/mail'),
            array_values($viewPaths),
        );
        self::assertContains(
            lang_path('vendor/nuewire/mail'),
            array_values($translationPaths),
        );
    }
}
