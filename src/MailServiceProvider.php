<?php

declare(strict_types=1);

namespace Btekno\Mail;

use Btekno\Mail\Livewire\Settings;
use Btekno\Mail\Support\ConnectionTester;
use Btekno\Mail\Support\EncryptedJsonSettingsStore;
use Btekno\Mail\Support\MailConfigFactory;
use Btekno\Mail\Support\RuntimeMailConfigurator;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Psr\Log\LoggerInterface;

final class MailServiceProvider extends ServiceProvider
{
    private const CONFIG_KEY = 'btekno.mail';

    public function register(): void
    {
        $this->replaceConfigRecursivelyFrom(__DIR__.'/../config/btekno/mail.php', self::CONFIG_KEY);
        /** @var Repository $config */
        $config = $this->app->make('config');

        if ($config->get(self::CONFIG_KEY.'.host_default_mailer') === null) {
            $config->set(self::CONFIG_KEY.'.host_default_mailer', (string) $config->get('mail.default', 'log'));
        }

        $this->app->singleton(MailConfigFactory::class);

        $this->app->singleton(EncryptedJsonSettingsStore::class, function ($app): EncryptedJsonSettingsStore {
            return new EncryptedJsonSettingsStore(
                $app,
                $app->make(Filesystem::class),
                (string) $app['config']->get(self::CONFIG_KEY.'.settings_path'),
            );
        });

        $this->app->singleton(RuntimeMailConfigurator::class, function ($app): RuntimeMailConfigurator {
            return new RuntimeMailConfigurator(
                $app,
                $app['config'],
                $app->make(EncryptedJsonSettingsStore::class),
                $app->make(MailConfigFactory::class),
                $app->make(LoggerInterface::class),
            );
        });

        $this->app->singleton(ConnectionTester::class, function ($app): ConnectionTester {
            return new ConnectionTester(
                $app->make('mail.manager'),
                $app->make(MailConfigFactory::class),
            );
        });

        $this->app->make(RuntimeMailConfigurator::class)->apply();
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'btekno-mail');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'btekno-mail');

        $this->registerLivewireComponent();
        $this->registerPlatformNavigation();

        $this->publishes([
            __DIR__.'/../config/btekno/mail.php' => config_path('btekno/mail.php'),
        ], 'btekno-mail-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/btekno-mail'),
        ], 'btekno-mail-views');

        $this->publishes([
            __DIR__.'/../resources/lang' => lang_path('vendor/btekno-mail'),
        ], 'btekno-mail-translations');
    }

    private function registerLivewireComponent(): void
    {
        $livewire = $this->app->make('livewire');

        if (method_exists($livewire, 'addNamespace')) {
            Livewire::resolveMissingComponent(
                static fn (string $name): ?string => $name === 'btekno::mail'
                    ? Settings::class
                    : null,
            );

            return;
        }

        Livewire::component('btekno::mail', Settings::class);
    }

    private function registerPlatformNavigation(): void
    {
        $registryClass = 'Btekno\\Platform\\Navigation\\NavigationRegistry';

        if (! $this->app->bound($registryClass)) {
            return;
        }

        $this->app->make($registryClass)->register('mail', [
            'label' => ['id' => 'Email', 'en' => 'Mail'],
            'description' => ['id' => 'Atur pengiriman email.', 'en' => 'Configure email delivery.'],
            'group' => ['id' => 'Pengaturan', 'en' => 'Settings'],
            'component' => 'btekno::mail',
            'icon' => 'M',
            'order' => 30,
        ]);
    }
}
