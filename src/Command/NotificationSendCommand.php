<?php

namespace App\Command;

use App\Channel\NotificationChannelRegistry;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:notification:send',
    description: 'Sends a notification through a configured channel (supports --dry-run).'
)]
final class NotificationSendCommand extends Command
{
    public function __construct(
        private readonly NotificationChannelRegistry $channels,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('channel', null, InputOption::VALUE_REQUIRED, 'Notification channel name ("email","log")')
            ->addOption('recipient', null, InputOption::VALUE_REQUIRED, 'Recipient (email address)')
            ->addOption('subject', null, InputOption::VALUE_REQUIRED, 'Message subject.')
            ->addOption('body', null, InputOption::VALUE_REQUIRED, 'Message body.')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Validate without sending.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $channel = (string) $input->getOption('channel');
        $recipient = (string) $input->getOption('recipient');
        $subject = (string) $input->getOption('subject');
        $body = (string) $input->getOption('body');
        $dryRun = (bool) $input->getOption('dry-run');
        // Would look nicer in a array and foreach loop, but let's keep it simple:
        if (trim($channel) === '') {
            $output->writeln('<error>Missing required option: --channel</error>');
            return Command::INVALID;
        }
        if (trim($recipient) === '') {
            $output->writeln('<error>Missing required option: --recipient</error>');
            return Command::INVALID;
        }
        if (trim($subject) === '') {
            $output->writeln('<error>Missing required option: --subject</error>');
            return Command::INVALID;
        }
        if (trim($body) === '') {
            $output->writeln('<error>Missing required option: --body</error>');
            return Command::INVALID;
        }

        // Lookup
        try {
            $transport = $this->channels->get($channel);
        } catch (\Throwable $e) {
            $output->writeln(sprintf('<error>Unknown channel "%s".</error>', $channel));
            $output->writeln(sprintf('Known channels: %s', implode(', ', $this->channels->getNames())));
            return Command::FAILURE;
        }

        // Show intent first (helpful for --dry-run)
        $output->writeln(sprintf('Channel:   <info>%s</info>', $channel));
        $output->writeln(sprintf('Recipient: <info>%s</info>', $recipient));
        $output->writeln(sprintf('Subject:   <info>%s</info>', $subject));
        $output->writeln(sprintf('Dry-run:   <info>%s</info>', $dryRun ? 'yes' : 'no'));

        if ($dryRun) {
            $output->writeln('<comment>Dry-run enabled: not sending, not writing a NotificationLog.</comment>');
            return Command::SUCCESS;
        }

        $startedAt = new DateTimeImmutable();

        try {
            $transport->send($recipient, $subject, $body);
            $output->writeln('<info>Notification sent.</info>');
            return Command::SUCCESS;
           } catch (\Throwable $e) {
            // Leave a log message ?
            $this->logger->error($e->getMessage().PHP_EOL.' attempting to send on Channel: '.$channel);
            }

            $output->writeln('<error>Failed to send notification.</error>');
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return Command::FAILURE;
    }
}
