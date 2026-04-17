<?php
namespace App\Channel;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class EmailChannel implements NotificationChannelInterface
{
    /**
     * Inject mailer and logger
     * @param MailerInterface $mailer
     * @param LoggerInterface $logger
     * @param string $fromAddress
     */
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        private readonly string $fromAddress = 'not-set@mydomain.com',
    ) {}

    public function send(string $recipient, string $subject, string $body): bool
    {
        try {
            $email = (new Email())
                ->from($this->fromAddress)
                ->to($recipient)
                ->subject($subject)
                ->text($body);

            $this->mailer->send($email);

            return true;
        } catch (TransportExceptionInterface|\Throwable $e) {
            $this->logger->error('EmailChannel failed to send notification.', [
                'recipient' => $recipient,
                'subject' => $subject,
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            return false;
        }
    }

    public function getName(): string
    {
        return 'email';
    }
}
