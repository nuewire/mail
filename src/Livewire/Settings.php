<?php

declare(strict_types=1);

namespace Nuewire\Mail\Livewire;

use Nuewire\Mail\Support\ConnectionTester;
use Nuewire\Mail\Support\EncryptedJsonSettingsStore;
use Nuewire\Mail\Support\RuntimeMailConfigurator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Throwable;

final class Settings extends Component
{
    public string $locale = 'id';
    public string $driver = 'log';
    public bool $setAsDefault = true;

    public string $fromAddress = '';
    public string $fromName = '';
    public string $replyToAddress = '';
    public string $replyToName = '';
    public string $testRecipient = '';

    public string $smtpHost = '127.0.0.1';
    public string $smtpPort = '2525';
    public string $smtpScheme = 'smtp';
    public string $smtpUsername = '';
    public string $smtpPassword = '';
    public string $smtpTimeout = '30';
    public bool $hasSmtpPassword = false;

    public string $sendmailPath = '/usr/sbin/sendmail -bs -i';

    public string $sesKey = '';
    public string $sesSecret = '';
    public string $sesToken = '';
    public string $sesRegion = 'us-east-1';
    public bool $hasSesSecret = false;
    public bool $hasSesToken = false;

    public string $mailgunDomain = '';
    public string $mailgunSecret = '';
    public string $mailgunEndpoint = '';
    public string $mailgunScheme = 'https';
    public bool $hasMailgunSecret = false;

    public string $postmarkToken = '';
    public string $postmarkMessageStreamId = '';
    public bool $hasPostmarkToken = false;

    public string $resendKey = '';
    public bool $hasResendKey = false;

    public string $logChannel = '';

    public string $status = '';
    public string $statusType = 'success';

    public function mount(?string $locale = null): void
    {
        $this->authorizeAccess();
        $this->locale = $this->resolveLocale($locale);

        try {
            $this->hydrateFromSettings(app(EncryptedJsonSettingsStore::class)->read());
        } catch (Throwable) {
            $this->hydrateFromSettings(app(EncryptedJsonSettingsStore::class)->defaults());
            $this->setStatus('status.read_failed', 'error');
        }

        $email = Auth::user()?->email;

        if (is_string($email)) {
            $this->testRecipient = $email;
        }
    }

    public function updatedLocale(string $locale): void
    {
        $this->locale = $this->resolveLocale($locale);

        if ((bool) config('nuewire.mail.remember_locale', true)) {
            try {
                session()->put((string) config('nuewire.mail.locale_session_key'), $this->locale);
            } catch (Throwable) {
                // Session middleware is optional for embedded components.
            }
        }
    }

    public function save(
        EncryptedJsonSettingsStore $store,
        RuntimeMailConfigurator $configurator,
    ): void {
        $this->authorizeAccess('mail.manage');

        try {
            $settings = $this->settingsFromForm($store, false);
            $settings['updated_by'] = Auth::id();

            $store->write($settings);
            $configurator->apply($settings);
            $this->hydrateFromSettings($settings);
            $this->setStatus('status.saved');
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);
            $this->setStatus('status.save_failed', 'error');
        }
    }

    public function sendTest(
        EncryptedJsonSettingsStore $store,
        ConnectionTester $tester,
    ): void {
        $this->authorizeAccess('mail.manage');

        try {
            $settings = $this->settingsFromForm($store, true);
            $tester->send(
                $settings,
                trim($this->testRecipient),
                $this->translate('test.subject'),
                $this->translate('test.body', ['driver' => strtoupper($this->driver)]),
            );

            $key = in_array($this->driver, ['log', 'array'], true)
                ? 'status.test_processed'
                : 'status.test_sent';

            $this->setStatus($key);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);
            $this->setStatus('status.test_failed', 'error', [
                'message' => $this->safeExceptionMessage($exception),
            ]);
        }
    }

    public function useLog(
        EncryptedJsonSettingsStore $store,
        RuntimeMailConfigurator $configurator,
    ): void {
        $this->authorizeAccess('mail.manage');
        $this->driver = 'log';
        $this->save($store, $configurator);
    }

    public function render()
    {
        return view('nuewire-mail::livewire.settings', [
            'driverOptions' => $this->driverOptions(),
            'localeOptions' => $this->localeOptions(),
            'activeDriver' => (string) config('nuewire.mail.active_driver', 'log'),
            'activeMailer' => (string) config('nuewire.mail.active_mailer', 'nuewire'),
        ]);
    }

    /** @return array<string, mixed> */
    private function settingsFromForm(EncryptedJsonSettingsStore $store, bool $testing): array
    {
        try {
            $existing = $store->read();
        } catch (Throwable) {
            $existing = $store->defaults();
        }

        $secrets = [
            'smtp_password' => (string) data_get($existing, 'drivers.smtp.password', ''),
            'ses_secret' => (string) data_get($existing, 'drivers.ses.secret', ''),
            'ses_token' => (string) data_get($existing, 'drivers.ses.token', ''),
            'mailgun_secret' => (string) data_get($existing, 'drivers.mailgun.secret', ''),
            'postmark_token' => (string) data_get($existing, 'drivers.postmark.token', ''),
            'resend_key' => (string) data_get($existing, 'drivers.resend.key', ''),
        ];

        $rules = [
            'driver' => ['required', Rule::in(array_keys($this->driverOptions()))],
            'setAsDefault' => ['boolean'],
            'fromAddress' => ['required', 'email', 'max:320'],
            'fromName' => ['nullable', 'string', 'max:255'],
            'replyToAddress' => ['nullable', 'email', 'max:320'],
            'replyToName' => ['nullable', 'string', 'max:255'],
        ];

        if ($testing) {
            $rules['testRecipient'] = ['required', 'email', 'max:320'];
        }

        $rules += match ($this->driver) {
            'smtp' => [
                'smtpHost' => ['required', 'string', 'max:255'],
                'smtpPort' => ['required', 'integer', 'between:1,65535'],
                'smtpScheme' => ['required', Rule::in(['smtp', 'smtps'])],
                'smtpUsername' => ['nullable', 'string', 'max:255'],
                'smtpPassword' => ['nullable', 'string', 'max:4096'],
                'smtpTimeout' => ['required', 'integer', 'between:1,300'],
            ],
            'sendmail' => [
                'sendmailPath' => ['required', 'string', 'max:2048'],
            ],
            'ses' => [
                'sesKey' => ['nullable', 'string', 'max:255', 'required_with:sesSecret'],
                'sesSecret' => [trim($this->sesKey) !== '' && $secrets['ses_secret'] === '' ? 'required' : 'nullable', 'string', 'max:4096'],
                'sesToken' => ['nullable', 'string', 'max:8192'],
                'sesRegion' => ['required', 'string', 'max:100'],
            ],
            'mailgun' => [
                'mailgunDomain' => ['required', 'string', 'max:255'],
                'mailgunSecret' => [$secrets['mailgun_secret'] === '' ? 'required' : 'nullable', 'string', 'max:4096'],
                'mailgunEndpoint' => ['nullable', 'string', 'max:255'],
                'mailgunScheme' => ['required', Rule::in(['https', 'http'])],
            ],
            'postmark' => [
                'postmarkToken' => [$secrets['postmark_token'] === '' ? 'required' : 'nullable', 'string', 'max:4096'],
                'postmarkMessageStreamId' => ['nullable', 'string', 'max:255'],
            ],
            'resend' => [
                'resendKey' => [$secrets['resend_key'] === '' ? 'required' : 'nullable', 'string', 'max:4096'],
            ],
            'log' => [
                'logChannel' => ['nullable', 'string', 'max:255'],
            ],
            'array' => [],
            default => [],
        };

        $this->validate($rules, $this->validationMessages(), $this->validationAttributes());

        $settings = array_replace_recursive($store->defaults(), $existing);
        $settings['active'] = $this->driver;
        $settings['set_as_default'] = $this->setAsDefault;
        $settings['from'] = [
            'address' => strtolower(trim($this->fromAddress)),
            'name' => trim($this->fromName),
        ];
        $settings['reply_to'] = [
            'address' => strtolower(trim($this->replyToAddress)),
            'name' => trim($this->replyToName),
        ];
        $settings['drivers']['smtp'] = [
            'host' => trim($this->smtpHost),
            'port' => (int) $this->smtpPort,
            'scheme' => $this->smtpScheme,
            'username' => trim($this->smtpUsername),
            'password' => $this->smtpPassword !== '' ? $this->smtpPassword : $secrets['smtp_password'],
            'timeout' => (int) $this->smtpTimeout,
        ];
        $settings['drivers']['sendmail'] = [
            'path' => trim($this->sendmailPath),
        ];
        $settings['drivers']['ses'] = [
            'key' => trim($this->sesKey),
            'secret' => $this->sesSecret !== '' ? $this->sesSecret : $secrets['ses_secret'],
            'token' => $this->sesToken !== '' ? $this->sesToken : $secrets['ses_token'],
            'region' => trim($this->sesRegion),
        ];
        $settings['drivers']['mailgun'] = [
            'domain' => trim($this->mailgunDomain),
            'secret' => $this->mailgunSecret !== '' ? $this->mailgunSecret : $secrets['mailgun_secret'],
            'endpoint' => trim($this->mailgunEndpoint),
            'scheme' => $this->mailgunScheme,
        ];
        $settings['drivers']['postmark'] = [
            'token' => $this->postmarkToken !== '' ? $this->postmarkToken : $secrets['postmark_token'],
            'message_stream_id' => trim($this->postmarkMessageStreamId),
        ];
        $settings['drivers']['resend'] = [
            'key' => $this->resendKey !== '' ? $this->resendKey : $secrets['resend_key'],
        ];
        $settings['drivers']['log'] = [
            'channel' => trim($this->logChannel),
        ];
        $settings['drivers']['array'] = [];

        return $settings;
    }

    /** @param array<string, mixed> $settings */
    private function hydrateFromSettings(array $settings): void
    {
        $available = array_keys($this->driverOptions());
        $this->driver = in_array($settings['active'] ?? null, $available, true)
            ? (string) $settings['active']
            : 'log';
        $this->setAsDefault = (bool) ($settings['set_as_default'] ?? true);
        $this->fromAddress = (string) data_get($settings, 'from.address', '');
        $this->fromName = (string) data_get($settings, 'from.name', '');
        $this->replyToAddress = (string) data_get($settings, 'reply_to.address', '');
        $this->replyToName = (string) data_get($settings, 'reply_to.name', '');

        $this->smtpHost = (string) data_get($settings, 'drivers.smtp.host', '127.0.0.1');
        $this->smtpPort = (string) data_get($settings, 'drivers.smtp.port', 2525);
        $this->smtpScheme = (string) data_get($settings, 'drivers.smtp.scheme', 'smtp');
        $this->smtpUsername = (string) data_get($settings, 'drivers.smtp.username', '');
        $this->smtpTimeout = (string) data_get($settings, 'drivers.smtp.timeout', 30);
        $this->hasSmtpPassword = trim((string) data_get($settings, 'drivers.smtp.password', '')) !== '';

        $this->sendmailPath = (string) data_get($settings, 'drivers.sendmail.path', '/usr/sbin/sendmail -bs -i');

        $this->sesKey = (string) data_get($settings, 'drivers.ses.key', '');
        $this->sesRegion = (string) data_get($settings, 'drivers.ses.region', 'us-east-1');
        $this->hasSesSecret = trim((string) data_get($settings, 'drivers.ses.secret', '')) !== '';
        $this->hasSesToken = trim((string) data_get($settings, 'drivers.ses.token', '')) !== '';

        $this->mailgunDomain = (string) data_get($settings, 'drivers.mailgun.domain', '');
        $this->mailgunEndpoint = (string) data_get($settings, 'drivers.mailgun.endpoint', '');
        $this->mailgunScheme = (string) data_get($settings, 'drivers.mailgun.scheme', 'https');
        $this->hasMailgunSecret = trim((string) data_get($settings, 'drivers.mailgun.secret', '')) !== '';

        $this->postmarkMessageStreamId = (string) data_get($settings, 'drivers.postmark.message_stream_id', '');
        $this->hasPostmarkToken = trim((string) data_get($settings, 'drivers.postmark.token', '')) !== '';
        $this->hasResendKey = trim((string) data_get($settings, 'drivers.resend.key', '')) !== '';
        $this->logChannel = (string) data_get($settings, 'drivers.log.channel', '');

        $this->clearSecrets();
    }

    private function clearSecrets(): void
    {
        $this->smtpPassword = '';
        $this->sesSecret = '';
        $this->sesToken = '';
        $this->mailgunSecret = '';
        $this->postmarkToken = '';
        $this->resendKey = '';
    }

    private function authorizeAccess(string $permission = 'mail.view'): void
    {
        $guard = config('nuewire.mail.authorization.guard');
        $auth = is_string($guard) && $guard !== '' ? Auth::guard($guard) : Auth::guard();
        $requireAuth = (bool) config('nuewire.mail.authorization.require_authenticated_user', true);
        $user = $auth->user();

        if (app()->bound('nuewire.acl.enabled')) {
            if ($user === null || ! method_exists($user, 'can')) {
                abort(403);
            }

            try {
                abort_unless($user->can($permission), 403);
            } catch (Throwable) {
                abort(403);
            }
        }

        if ($requireAuth && ! $auth->check()) {
            abort(403);
        }

        $gate = config('nuewire.mail.authorization.gate');

        if (is_string($gate) && $gate !== '') {
            $user = $auth->user();

            if ($user === null || Gate::forUser($user)->denies($gate)) {
                abort(403);
            }
        }
    }

    /** @return array<string, string> */
    private function driverOptions(): array
    {
        $drivers = ['smtp', 'sendmail', 'ses', 'mailgun', 'postmark', 'resend', 'log', 'array'];
        $options = [];

        foreach ($drivers as $driver) {
            $options[$driver] = $this->translate("drivers.{$driver}");
        }

        return $options;
    }

    /** @return array<string, string> */
    private function localeOptions(): array
    {
        $options = [];

        foreach ($this->supportedLocales() as $locale) {
            $options[$locale] = $this->translate("language.{$locale}");
        }

        return $options;
    }

    /** @return list<string> */
    private function supportedLocales(): array
    {
        $configured = config('nuewire.mail.supported_locales', ['id', 'en']);
        $locales = is_array($configured) ? $configured : ['id', 'en'];
        $locales = array_values(array_filter(array_map(
            static fn (mixed $locale): string => strtolower(trim((string) $locale)),
            $locales,
        )));

        return $locales !== [] ? array_values(array_unique($locales)) : ['id', 'en'];
    }

    private function resolveLocale(?string $requested): string
    {
        if (is_string($requested) && trim($requested) !== '') {
            return $this->normalizeLocale($requested);
        }

        if ((bool) config('nuewire.mail.remember_locale', true)) {
            try {
                $stored = session()->get((string) config('nuewire.mail.locale_session_key'));

                if (is_string($stored) && $stored !== '') {
                    return $this->normalizeLocale($stored);
                }
            } catch (Throwable) {
                // Session middleware is optional.
            }
        }

        return $this->normalizeLocale((string) config('nuewire.mail.locale', 'id'));
    }

    private function normalizeLocale(string $locale): string
    {
        $locale = strtolower(str_replace('_', '-', trim($locale)));
        $locale = explode('-', $locale)[0] ?: 'id';

        return in_array($locale, $this->supportedLocales(), true) ? $locale : 'id';
    }

    /** @param array<string, scalar> $replace */
    private function translate(string $key, array $replace = []): string
    {
        return Lang::get("nuewire-mail::mail.{$key}", $replace, $this->locale);
    }

    /** @param array<string, scalar> $replace */
    private function setStatus(string $key, string $type = 'success', array $replace = []): void
    {
        $this->status = $this->translate($key, $replace);
        $this->statusType = $type;
    }

    private function safeExceptionMessage(Throwable $exception): string
    {
        $message = trim($exception->getMessage());

        if ($message === '' || str_contains(strtolower($message), 'key')) {
            return $this->translate('status.connection_error');
        }

        return mb_substr($message, 0, 240);
    }

    /** @return array<string, string> */
    private function validationMessages(): array
    {
        return [
            'required' => $this->translate('validation.required'),
            'required_with' => $this->translate('validation.required'),
            'email' => $this->translate('validation.email'),
            'string' => $this->translate('validation.string'),
            'integer' => $this->translate('validation.integer'),
            'between' => $this->translate('validation.between'),
            'max' => $this->translate('validation.max'),
            'in' => $this->translate('validation.in'),
            'boolean' => $this->translate('validation.boolean'),
        ];
    }

    /** @return array<string, string> */
    private function validationAttributes(): array
    {
        $keys = [
            'driver', 'fromAddress', 'fromName', 'replyToAddress', 'replyToName', 'testRecipient',
            'smtpHost', 'smtpPort', 'smtpScheme', 'smtpUsername', 'smtpPassword', 'smtpTimeout',
            'sendmailPath', 'sesKey', 'sesSecret', 'sesToken', 'sesRegion', 'mailgunDomain',
            'mailgunSecret', 'mailgunEndpoint', 'mailgunScheme', 'postmarkToken',
            'postmarkMessageStreamId', 'resendKey', 'logChannel',
        ];
        $attributes = [];

        foreach ($keys as $key) {
            $attributes[$key] = $this->translate("validation.attributes.{$key}");
        }

        return $attributes;
    }
}
