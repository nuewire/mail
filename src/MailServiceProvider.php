<?php

declare(strict_types=1);

namespace Nuewire\Mail;

use Nuewire\Mail\Livewire\Settings;
use Nuewire\Mail\Support\ConnectionTester;
use Nuewire\Mail\Support\EncryptedJsonSettingsStore;
use Nuewire\Mail\Support\MailConfigFactory;
use Nuewire\Mail\Support\RuntimeMailConfigurator;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Psr\Log\LoggerInterface;

final class MailServiceProvider extends ServiceProvider
{
    private const CONFIG_KEY = 'nuewire.mail';

    public function register(): void
    {
        $this->replaceConfigRecursivelyFrom(__DIR__.'/../config/nuewire/mail.php', self::CONFIG_KEY);
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

        $this->registerPlatformNavigation();
        $this->registerAclPermissions();

        $this->app->make(RuntimeMailConfigurator::class)->apply();
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nuewire-mail');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'nuewire-mail');

        $this->registerLivewireComponent();

        $this->publishes([
            __DIR__.'/../config/nuewire/mail.php' => config_path('nuewire/mail.php'),
        ], 'nuewire-mail-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/nuewire/mail'),
        ], 'nuewire-mail-views');

        $this->publishes([
            __DIR__.'/../resources/lang' => lang_path('vendor/nuewire/mail'),
        ], 'nuewire-mail-translations');
    }

    private function registerLivewireComponent(): void
    {
        $livewire = $this->app->make('livewire');

        if (method_exists($livewire, 'addComponent')) {
            $livewire->addComponent('nuewire::mail', null, Settings::class);

            return;
        }

        Livewire::component('nuewire::mail', Settings::class);
    }

    private function registerPlatformNavigation(): void
    {
        $registryClass = 'Nuewire\\Platform\\Navigation\\NavigationRegistry';

        $this->app->afterResolving($registryClass, static function (object $registry): void {
            if (! method_exists($registry, 'register')) {
                return;
            }

            $registry->register('mail', [
                'label' => ['id' => 'Email', 'en' => 'Mail'],
                'description' => ['id' => 'Atur pengiriman email.', 'en' => 'Configure email delivery.'],
                'group' => ['id' => 'Pengaturan', 'en' => 'Settings'],
                'component' => 'nuewire::mail',
                'permission' => 'mail.view',
                'icon' => 'M',
                'order' => 30,
            ]);
        });
    }

    private function registerAclPermissions(): void
    {
        $registryClass = 'Nuewire\\Acl\\Registry\\PermissionRegistry';

        $this->app->afterResolving($registryClass, static function (object $registry): void {
            if (! method_exists($registry, 'registerMany')) {
                return;
            }

            $registry->registerMany([
                'mail.view' => ['id' => 'Melihat pengaturan email', 'en' => 'View mail settings'],
                'mail.manage' => ['id' => 'Mengubah pengaturan email', 'en' => 'Manage mail settings'],
            ], 'mail');
        });
    }
}
