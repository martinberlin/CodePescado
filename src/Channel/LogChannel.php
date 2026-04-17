<?php
namespace App\Channel;

use Psr\Log\LoggerInterface;

final class LogChannel implements NotificationChannelInterface
{
    public function __construct(private readonly LoggerInterface $logger) {}

    public function send(string $recipient, string $subject, string $body): bool
    {
        $this->logger->info('Notification (LogChannel)', [
            'subject' => $subject,
            'recipient' => $recipient,
            'body' => $body,
        ]);

        return true;
    }

    public function getName(): string
    {
        return 'log';
    }
}
