# Nuewire Mail

Mail driver settings for Laravel and Livewire.

## Install

```bash
composer require nuewire/mail
php artisan optimize:clear
```

## Component

```blade
<livewire:nuewire::mail />
```

With `nuewire/platform` 2, the page appears under **Settings → Configuration → Email** at `/admin/settings/email`.

## Drivers

- SMTP
- Sendmail
- Amazon SES
- Mailgun
- Postmark
- Resend
- Log
- Array

## Storage

Settings are encrypted at:

```text
storage/app/private/.nuewire/emails.json
```

## Usage

The selected mailer becomes Laravel's default when the option is enabled.

```php
Mail::to('user@example.com')->send(new InvoiceReady());
```

Use the package mailer explicitly:

```php
Mail::mailer('nuewire')->to('user@example.com')->send(new InvoiceReady());
```

The initial driver is `log`.

## Access

The component requires authentication by default.

```env
NUEWIRE_MAIL_GATE=manage-email-settings
```

## Publish

```bash
php artisan vendor:publish --tag=nuewire-mail-config
php artisan vendor:publish --tag=nuewire-mail-views
php artisan vendor:publish --tag=nuewire-mail-translations
```

Config path:

```text
config/nuewire/mail.php
```

Restart queue or Octane workers after changing drivers.
