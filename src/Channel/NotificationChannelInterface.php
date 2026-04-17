<?php
namespace App\Channel;

// This interface was provided by the example in the PDF
interface NotificationChannelInterface
{
    /**
     * Send a notification.
     *
     * @param string $recipient The recipient identifier (email, phone, etc.)
     * @param string $subject
     * @param string $body
     * @return bool Whether sending succeeded
     */
    public function send(string $recipient, string $subject, string $body):
    bool;

    /**
     * Return the unique channel name, e.g. "email", "sms", "slack".
     */
    public function getName(): string;
}
