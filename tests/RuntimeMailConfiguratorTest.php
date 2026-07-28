<?php

declare(strict_types=1);

namespace Btekno\Mail\Tests;

use Btekno\Mail\Support\EncryptedJsonSettingsStore;
use Btekno\Mail\Support\RuntimeMailConfigurator;

final class RuntimeMailConfiguratorTest extends TestCase
{
    public function test_it_registers_and_selects_the_btekno_mailer(): void
    {
        $settings = app(EncryptedJsonSettingsStore::class)->defaults();
        $settings['active'] = 'resend';
        $settings['drivers']['resend']['key'] = 're_test';
        $settings['set_as_default'] = true;

        app(RuntimeMailConfigurator::class)->apply($settings);

        self::assertSame('btekno', config('mail.default'));
        self::assertSame('resend', config('mail.mailers.btekno.transport'));
        self::assertSame('re_test', config('mail.mailers.btekno.key'));
        self::assertSame('resend', config('btekno.mail.active_driver'));
    }
}
