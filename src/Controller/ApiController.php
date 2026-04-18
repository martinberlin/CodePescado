<?php
namespace App\Controller;

use App\Channel\NotificationChannelRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * This could be also done quasi automatically using API platform but I guess that is not the plan for this example
 */
#[Route('/api')]
class ApiController
{
    /**
     * List all available notification channels. Public (no auth required)
     * @param Request $request
     * @return Response
     */
    #[Route('/channels ', name: 'api_channels', methods: ['GET'])]
    public function apiChannels(Request $request, NotificationChannelRegistry $channelRegistry): Response
    {
        $response = new JsonResponse();
        $channels = $channelRegistry->getNames();

        return new JsonResponse([
            'channels' => $channels,
            'count' => count($channels),
        ]);
    }


}
