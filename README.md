# Btekno Mail

Mail driver settings for Laravel and Livewire.

## Install

```bash
composer require btekno/mail
php artisan optimize:clear
```

## Component

```blade
<livewire:btekno::mail />
```

With `btekno/platform`, the page appears automatically under admin settings.

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
storage/app/private/.btekno/emails.json
```

## Usage

The selected mailer becomes Laravel's default when the option is enabled.

```php
Mail::to('user@example.com')->send(new InvoiceReady());
```

Use the package mailer explicitly:

```php
Mail::mailer('btekno')->to('user@example.com')->send(new InvoiceReady());
```

The initial driver is `log`.

## Access

The component requires authentication by default.

```env
BTEKNO_MAIL_GATE=manage-email-settings
```

## Publish

```bash
php artisan vendor:publish --tag=btekno-mail-config
php artisan vendor:publish --tag=btekno-mail-views
php artisan vendor:publish --tag=btekno-mail-translations
```

Config path:

```text
config/btekno/mail.php
```

Restart queue or Octane workers after changing drivers.
