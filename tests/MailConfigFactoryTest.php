<?php

declare(strict_types=1);

namespace Nuewire\Mail\Tests;

use Nuewire\Mail\Support\EncryptedJsonSettingsStore;
use Nuewire\Mail\Support\MailConfigFactory;

final class MailConfigFactoryTest extends TestCase
{
    public function test_it_builds_resend_configuration(): void
    {
        $settings = app(EncryptedJsonSettingsStore::class)->defaults();
        $settings['active'] = 'resend';
        $settings['drivers']['resend']['key'] = 're_test';
        $settings['from']['address'] = 'mail@example.com';

        $config = app(MailConfigFactory::class)->make($settings);

        self::assertSame('resend', $config['transport']);
        self::assertSame('re_test', $config['key']);
        self::assertSame('mail@example.com', $config['from']['address']);
    }

    public function test_it_builds_smtp_configuration(): void
    {
        $settings = app(EncryptedJsonSettingsStore::class)->defaults();
        $settings['active'] = 'smtp';
        $settings['drivers']['smtp']['host'] = 'smtp.example.com';
        $settings['drivers']['smtp']['port'] = 587;

        $config = app(MailConfigFactory::class)->make($settings);

        self::assertSame('smtp', $config['transport']);
        self::assertSame('smtp.example.com', $config['host']);
        self::assertSame(587, $config['port']);
    }
}
