<?php

declare(strict_types=1);

namespace Nuewire\Mail\Tests;

use Nuewire\Mail\Support\EncryptedJsonSettingsStore;
use Nuewire\Mail\Support\RuntimeMailConfigurator;

final class RuntimeMailConfiguratorTest extends TestCase
{
    public function test_it_registers_and_selects_the_nuewire_mailer(): void
    {
        $settings = app(EncryptedJsonSettingsStore::class)->defaults();
        $settings['active'] = 'resend';
        $settings['drivers']['resend']['key'] = 're_test';
        $settings['set_as_default'] = true;

        app(RuntimeMailConfigurator::class)->apply($settings);

        self::assertSame('nuewire', config('mail.default'));
        self::assertSame('resend', config('mail.mailers.nuewire.transport'));
        self::assertSame('re_test', config('mail.mailers.nuewire.key'));
        self::assertSame('resend', config('nuewire.mail.active_driver'));
    }
}
