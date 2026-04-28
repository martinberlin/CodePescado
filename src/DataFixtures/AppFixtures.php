<?php
namespace App\DataFixtures;

use App\Entity\ApiClient;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class AppFixtures extends Fixture
{
    // It's duplicated in services.yaml now but not super important right now
    public const TEST_CLIENT_NAME = 'Test Client';
    public const TEST_API_KEY = 'test-api-key-00000000000000000000';

    public function load(ObjectManager $manager): void
    {
        // Avoid duplicates if fixtures are loaded twice.
        $existing = $manager->getRepository(ApiClient::class)->findOneBy(['apiKey' => self::TEST_API_KEY]);
        if ($existing instanceof ApiClient) {
            echo "This is already in ApiClient\n";
            return;
        }

        $client = new ApiClient();
        $client->setName(self::TEST_CLIENT_NAME);
        $client->setApiKey(self::TEST_API_KEY);
        $client->setIsActive(true);

        $manager->persist($client);
        $manager->flush();
    }
}
