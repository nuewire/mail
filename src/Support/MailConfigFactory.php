<?php

declare(strict_types=1);

namespace Nuewire\Mail\Support;

use InvalidArgumentException;

final class MailConfigFactory
{
    /** @param array<string, mixed> $settings
     *  @return array<string, mixed>
     */
    public function make(array $settings): array
    {
        $driver = (string) ($settings['active'] ?? 'log');
        $provider = (array) data_get($settings, "drivers.{$driver}", []);

        $config = match ($driver) {
            'smtp' => [
                'transport' => 'smtp',
                'scheme' => $this->nullable((string) ($provider['scheme'] ?? 'smtp')),
                'host' => (string) ($provider['host'] ?? '127.0.0.1'),
                'port' => (int) ($provider['port'] ?? 2525),
                'username' => $this->nullable((string) ($provider['username'] ?? '')),
                'password' => $this->nullable((string) ($provider['password'] ?? '')),
                'timeout' => (int) ($provider['timeout'] ?? 30),
            ],
            'sendmail' => [
                'transport' => 'sendmail',
                'path' => (string) ($provider['path'] ?? '/usr/sbin/sendmail -bs -i'),
            ],
            'ses' => array_filter([
                'transport' => 'ses',
                'key' => $this->nullable((string) ($provider['key'] ?? '')),
                'secret' => $this->nullable((string) ($provider['secret'] ?? '')),
                'token' => $this->nullable((string) ($provider['token'] ?? '')),
                'region' => (string) ($provider['region'] ?? 'us-east-1'),
            ], static fn (mixed $value): bool => $value !== null),
            'mailgun' => array_filter([
                'transport' => 'mailgun',
                'domain' => (string) ($provider['domain'] ?? ''),
                'secret' => (string) ($provider['secret'] ?? ''),
                'endpoint' => $this->nullable((string) ($provider['endpoint'] ?? '')),
                'scheme' => (string) ($provider['scheme'] ?? 'https'),
            ], static fn (mixed $value): bool => $value !== null),
            'postmark' => array_filter([
                'transport' => 'postmark',
                'token' => (string) ($provider['token'] ?? ''),
                'message_stream_id' => $this->nullable((string) ($provider['message_stream_id'] ?? '')),
            ], static fn (mixed $value): bool => $value !== null),
            'resend' => [
                'transport' => 'resend',
                'key' => (string) ($provider['key'] ?? ''),
            ],
            'log' => array_filter([
                'transport' => 'log',
                'channel' => $this->nullable((string) ($provider['channel'] ?? '')),
            ], static fn (mixed $value): bool => $value !== null),
            'array' => ['transport' => 'array'],
            default => throw new InvalidArgumentException("Unsupported email driver [{$driver}]."),
        };

        $config['from'] = [
            'address' => (string) data_get($settings, 'from.address', 'hello@example.com'),
            'name' => (string) data_get($settings, 'from.name', ''),
        ];

        $replyAddress = trim((string) data_get($settings, 'reply_to.address', ''));

        if ($replyAddress !== '') {
            $config['reply_to'] = [
                'address' => $replyAddress,
                'name' => (string) data_get($settings, 'reply_to.name', ''),
            ];
        }

        return $config;
    }

    private function nullable(string $value): ?string
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
