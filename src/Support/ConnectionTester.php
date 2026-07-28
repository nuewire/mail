<?php

declare(strict_types=1);

namespace Btekno\Mail\Support;

use Illuminate\Mail\MailManager;
use Illuminate\Mail\Message;

final class ConnectionTester
{
    public function __construct(
        private readonly MailManager $mail,
        private readonly MailConfigFactory $factory,
    ) {
    }

    /** @param array<string, mixed> $settings */
    public function send(array $settings, string $recipient, string $subject, string $body): void
    {
        $config = $this->factory->make($settings);
        $mailer = $this->mail->build(['name' => 'btekno-test', ...$config]);

        $mailer->raw($body, function (Message $message) use ($recipient, $subject, $config): void {
            $message->to($recipient)->subject($subject);

            $from = $config['from'] ?? null;

            if (is_array($from) && is_string($from['address'] ?? null)) {
                $message->from($from['address'], (string) ($from['name'] ?? ''));
            }

            $replyTo = $config['reply_to'] ?? null;

            if (is_array($replyTo) && is_string($replyTo['address'] ?? null)) {
                $message->replyTo($replyTo['address'], (string) ($replyTo['name'] ?? ''));
            }
        });
    }
}
