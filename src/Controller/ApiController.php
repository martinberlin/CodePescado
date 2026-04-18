<?php
namespace App\Controller;

use App\Channel\NotificationChannelRegistry;
use App\Repository\NotificationLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * This could be also done quasi automatically using API platform but I guess that is not the plan for this example
 */
#[Route('/api')]
class ApiController extends AbstractController
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

    /**
     * NOTE: This route should be protected with a Bearer token defined in .env for the test and implemented in security.yaml
     * Without proper bearer token you should get 401 status: Full authentication is required to access this resource.
     * @param Request $request
     * @param NotificationChannelRegistry $channelRegistry
     * @return Response
     */
    #[Route('/notifications ', name: 'api_notifications', methods: ['GET'])]
    public function apiNotifications(Request $request,
                                     NotificationLogRepository $notificationLogRepository,
                                     NotificationChannelRegistry $channelRegistry): Response
    {
        // Filters to be added in next commits, for now return all Notifications like if $request would have no GET params
        // Pending to implement custom repository methjod mentioned in Step 2
        $notifications = $notificationLogRepository->findAll();

        // This uses Symfony serializer
        return $this->json(
            ['notifications' => $notifications]
            );
    }

}
