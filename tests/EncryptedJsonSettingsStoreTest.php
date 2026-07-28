<?php

declare(strict_types=1);

namespace Btekno\Mail\Tests;

use Btekno\Mail\Support\EncryptedJsonSettingsStore;

final class EncryptedJsonSettingsStoreTest extends TestCase
{
    public function test_it_encrypts_the_settings_file(): void
    {
        $store = app(EncryptedJsonSettingsStore::class);
        $settings = $store->defaults();
        $settings['active'] = 'resend';
        $settings['drivers']['resend']['key'] = 're_private_test_key';

        $store->write($settings);

        $contents = file_get_contents($store->path());

        self::assertIsString($contents);
        self::assertStringContainsString('ciphertext', $contents);
        self::assertStringNotContainsString('re_private_test_key', $contents);
        self::assertSame('re_private_test_key', $store->read()['drivers']['resend']['key']);
    }

    public function test_it_uses_the_shared_btekno_directory(): void
    {
        $store = app(EncryptedJsonSettingsStore::class);

        self::assertSame(
            $this->app->storagePath('app/private/.btekno/emails.json'),
            $store->path(),
        );
    }
}
