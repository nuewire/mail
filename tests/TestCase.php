<?php

declare(strict_types=1);

namespace Nuewire\Mail\Tests;

use Nuewire\Mail\MailServiceProvider;
use Livewire\LivewireServiceProvider;
use Nuewire\Support\SupportServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            SupportServiceProvider::class,
            LivewireServiceProvider::class,
            MailServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $app['config']->set('app.name', 'Nuewire Test');
        $app['config']->set('mail.default', 'log');
        $app['config']->set('mail.from.address', 'hello@example.com');
        $app['config']->set('mail.from.name', 'Nuewire Test');
        $app['config']->set('nuewire.mail.locale', 'id');
        $app['config']->set('nuewire.mail.authorization.require_authenticated_user', false);
        $app['config']->set(
            'nuewire.mail.settings_path',
            $app->storagePath('app/private/.nuewire/emails.json'),
        );
    }
}
