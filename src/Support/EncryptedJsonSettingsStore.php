<?php

declare(strict_types=1);

namespace Btekno\Mail\Support;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Filesystem\Filesystem;
use JsonException;
use RuntimeException;
use Throwable;

final class EncryptedJsonSettingsStore
{
    public function __construct(
        private readonly Container $container,
        private readonly Filesystem $files,
        private readonly string $path,
    ) {
    }

    /** @return array<string, mixed> */
    public function read(): array
    {
        if (! $this->files->exists($this->path)) {
            return $this->defaults();
        }

        return $this->withLock(LOCK_SH, function (): array {
            try {
                $envelope = json_decode($this->files->get($this->path), true, 512, JSON_THROW_ON_ERROR);

                if (! is_array($envelope) || ! is_string($envelope['ciphertext'] ?? null)) {
                    throw new RuntimeException('Invalid Btekno mail settings envelope.');
                }

                $payload = $this->encrypter()->decryptString($envelope['ciphertext']);
                $settings = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);

                if (! is_array($settings)) {
                    throw new RuntimeException('Invalid Btekno mail settings payload.');
                }

                return array_replace_recursive($this->defaults(), $settings);
            } catch (JsonException $exception) {
                throw new RuntimeException('Invalid JSON in Btekno mail settings.', 0, $exception);
            } catch (DecryptException $exception) {
                throw new RuntimeException(
                    'Btekno mail settings could not be decrypted. Check APP_KEY and APP_PREVIOUS_KEYS.',
                    0,
                    $exception,
                );
            } catch (RuntimeException $exception) {
                throw $exception;
            } catch (Throwable $exception) {
                throw new RuntimeException('Btekno mail settings could not be read.', 0, $exception);
            }
        });
    }

    /** @param array<string, mixed> $settings */
    public function write(array $settings): void
    {
        $this->ensureDirectory(dirname($this->path));

        $this->withLock(LOCK_EX, function () use ($settings): void {
            try {
                $payload = json_encode($settings, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
                $envelope = json_encode([
                    'version' => 1,
                    'ciphertext' => $this->encrypter()->encryptString($payload),
                    'updated_at' => now()->toIso8601String(),
                ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;

                $temporary = $this->path.'.tmp.'.bin2hex(random_bytes(8));

                if ($this->files->put($temporary, $envelope, true) === false) {
                    throw new RuntimeException('Btekno mail settings could not be written.');
                }

                @chmod($temporary, 0600);

                if (PHP_OS_FAMILY === 'Windows' && $this->files->exists($this->path)) {
                    $this->files->delete($this->path);
                }

                if (! @rename($temporary, $this->path)) {
                    $this->files->delete($temporary);
                    throw new RuntimeException('Btekno mail settings could not be moved into place.');
                }

                @chmod($this->path, 0600);
            } catch (JsonException $exception) {
                throw new RuntimeException('Btekno mail settings could not be encoded.', 0, $exception);
            }
        });
    }

    public function path(): string
    {
        return $this->path;
    }

    public function exists(): bool
    {
        return $this->files->exists($this->path);
    }

    /** @return array<string, mixed> */
    public function defaults(): array
    {
        $fromAddress = (string) config('mail.from.address', 'hello@example.com');
        $fromName = (string) config('mail.from.name', config('app.name', 'Laravel'));

        return [
            'version' => 1,
            'active' => 'log',
            'set_as_default' => (bool) config('btekno.mail.set_as_default', true),
            'from' => [
                'address' => $fromAddress,
                'name' => $fromName,
            ],
            'reply_to' => [
                'address' => '',
                'name' => '',
            ],
            'drivers' => [
                'smtp' => [
                    'host' => '127.0.0.1',
                    'port' => 2525,
                    'scheme' => 'smtp',
                    'username' => '',
                    'password' => '',
                    'timeout' => 30,
                ],
                'sendmail' => [
                    'path' => '/usr/sbin/sendmail -bs -i',
                ],
                'ses' => [
                    'key' => '',
                    'secret' => '',
                    'token' => '',
                    'region' => 'us-east-1',
                ],
                'mailgun' => [
                    'domain' => '',
                    'secret' => '',
                    'endpoint' => '',
                    'scheme' => 'https',
                ],
                'postmark' => [
                    'token' => '',
                    'message_stream_id' => '',
                ],
                'resend' => [
                    'key' => '',
                ],
                'log' => [
                    'channel' => '',
                ],
                'array' => [],
            ],
        ];
    }

    private function encrypter(): Encrypter
    {
        return $this->container->make(Encrypter::class);
    }

    /**
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    private function withLock(int $operation, callable $callback): mixed
    {
        $directory = dirname($this->path);
        $this->ensureDirectory($directory);
        $lockPath = $this->lockPath();
        $handle = @fopen($lockPath, 'c+');

        if ($handle === false) {
            throw new RuntimeException('Btekno mail settings lock could not be opened.');
        }

        try {
            if (! flock($handle, $operation)) {
                throw new RuntimeException('Btekno mail settings lock could not be acquired.');
            }

            return $callback();
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
            @chmod($lockPath, 0600);
        }
    }

    private function lockPath(): string
    {
        $name = pathinfo($this->path, PATHINFO_FILENAME);

        return dirname($this->path).'/.'.$name.'.lock';
    }

    private function ensureDirectory(string $directory): void
    {
        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0700, true, true);
        }

        @chmod($directory, 0700);
    }
}
