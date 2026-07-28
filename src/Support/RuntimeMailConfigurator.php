<?php

declare(strict_types=1);

namespace Btekno\Mail\Support;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Mail\MailManager;
use Psr\Log\LoggerInterface;
use Throwable;

final class RuntimeMailConfigurator
{
    public function __construct(
        private readonly Container $container,
        private readonly Repository $config,
        private readonly EncryptedJsonSettingsStore $store,
        private readonly MailConfigFactory $factory,
        private readonly LoggerInterface $logger,
    ) {
    }

    /** @param array<string, mixed>|null $settings */
    public function apply(?array $settings = null): void
    {
        $settings ??= $this->safeSettings();
        $mailer = (string) $this->config->get('btekno.mail.mailer', 'btekno');
        $fallback = (string) $this->config->get('btekno.mail.host_default_mailer', 'log');

        $this->config->set("mail.mailers.{$mailer}", $this->factory->make($settings));
        $this->config->set('btekno.mail.active_driver', (string) ($settings['active'] ?? 'log'));
        $this->config->set('btekno.mail.active_mailer', $mailer);

        if ((bool) ($settings['set_as_default'] ?? true)) {
            $this->config->set('mail.default', $mailer);
        } elseif ($this->config->get('mail.default') === $mailer) {
            $this->config->set('mail.default', $fallback);
        }

        $this->forgetResolvedMailers();
    }

    /** @return array<string, mixed> */
    private function safeSettings(): array
    {
        try {
            return $this->store->read();
        } catch (Throwable $exception) {
            $this->logger->warning('Btekno mail settings could not be loaded. Log mailer is active.', [
                'exception' => $exception::class,
            ]);

            return $this->store->defaults();
        }
    }

    private function forgetResolvedMailers(): void
    {
        if (! $this->container->bound('mail.manager')) {
            return;
        }

        $manager = $this->container->make('mail.manager');

        if ($manager instanceof MailManager) {
            $manager->forgetMailers();
        }
    }
}
