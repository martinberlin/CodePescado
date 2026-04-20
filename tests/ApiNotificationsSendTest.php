<?php
namespace App\Tests;

use App\Entity\ApiClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ApiNotificationsSendTest extends WebTestCase
{
    public function testApiNotificationsSendSucceedsWithValidBearerToken(): void
    {
        $client = static::createClient();

        // Check that an active ApiClient with the known key exists in the test DB
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $apiKey = 'test-api-key-00000000000000000000';
        $name = 'test-client';
        $repo = $em->getRepository(ApiClient::class);
        $apiClient = $repo->findOneBy(['apiKey' => $apiKey]);
        $this->assertTrue($apiClient instanceof ApiClient);

        $client->request(
            method: 'POST',
            uri: '/api/notifications/send',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$apiKey,
            ],
            content: json_encode([
                'channel' => 'log',
                'recipient' => 'martin@example.com',
                'subject' => 'Test log channel',
                'body' => 'Test body',
            ], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseStatusCodeSame(200);
        $content = $client->getResponse()->getContent();
        self::assertNotFalse($content);

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertTrue($data['ok']);
        $this->assertSame('sent', $data['status']);
        $this->assertSame('log', $data['channel']);
    }
}
