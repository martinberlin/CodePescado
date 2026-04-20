<?php
namespace App\Tests\ChannelRegistry;

use App\Channel\NotificationChannelRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class NotificationChannelRegistryTest extends KernelTestCase
{
    public function testRegistryHasEmailAndLogChannels(): void
    {
        self::bootKernel();
        $registry = self::getContainer()->get(NotificationChannelRegistry::class);

        $names = $registry->getNames();
        // Check that both Channels exist
        self::assertContains('email', $names);
        self::assertContains('log', $names);
    }
}
