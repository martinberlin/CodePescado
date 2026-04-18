<?php

namespace App\Command;

use App\Entity\ApiClient;
use App\Repository\ApiClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:seed-test-api-client',
    description: 'Seeds a test ApiClient with name / key defined in .env'
)]
final class SeedApiClientCommand extends Command
{
    public function __construct(
        private readonly ApiClientRepository $repo,
        private readonly EntityManagerInterface $em,
        private readonly string $testClient,
        private readonly string $testClientApiKey
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->testClient === '' || $this->testClientApiKey === '') {
            $output->writeln('Missing TEST_CLIENT or TEST_APIKEY in ENV');
            return Command::FAILURE;
        }

        $existing = $this->repo->findOneBy(['apiKey' => $this->testClientApiKey]);
        if ($existing) {
            $output->writeln('Test ApiClient already exists (by apiKey).');
            return Command::SUCCESS;
        }

        $existingByName = $this->repo->findOneBy(['name' => $this->testClient]);
        if ($existingByName) {
            $output->writeln('An ApiClient with this name already exists; not modifying it.');
            return Command::SUCCESS;
        }

        /** @var  $client
         * NOTE: This could be also done with a constructor in ApiClient where we just pass Name and ApiKey as params
         * */
        $client = new ApiClient();
        $client->setName($this->testClient);
        $client->setApiKey($this->testClientApiKey);
        $client->setIsActive(true); // Cannot be null
        $this->em->persist($client);
        $this->em->flush();

        $output->writeln(sprintf('Seeded ApiClient "%s".', $this->testClient));

        return Command::SUCCESS;
    }
}
