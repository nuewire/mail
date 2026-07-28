<?php

declare(strict_types=1);

namespace Btekno\Mail\Tests;

use Btekno\Mail\MailServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            MailServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $app['config']->set('app.name', 'Btekno Test');
        $app['config']->set('mail.default', 'log');
        $app['config']->set('mail.from.address', 'hello@example.com');
        $app['config']->set('mail.from.name', 'Btekno Test');
        $app['config']->set('btekno.mail.locale', 'id');
        $app['config']->set('btekno.mail.authorization.require_authenticated_user', false);
        $app['config']->set(
            'btekno.mail.settings_path',
            $app->storagePath('app/private/.btekno/emails.json'),
        );
    }
}
