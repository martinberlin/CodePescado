<?php

namespace App\Controller;

use App\Channel\NotificationChannelRegistry;
use App\Entity\NotificationLog;
use App\Repository\NotificationLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use function PHPUnit\Framework\throwException;

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
    public function apiNotifications(Request                     $request,
                                     NotificationLogRepository   $notificationLogRepository,
                                     NotificationChannelRegistry $channelRegistry): Response
    {
        // Check request GET params
        if (count($request->query)) {
            // Validate
            $errors = [];
            $validate = ['channel', 'from', 'to'];
            foreach ($validate as $v) {
                if ($request->query->has($v) === false) {
                    $errors[$v] = 'Missing parameter: ' . $v;
                    continue;
                }
            }
            // Filter parameters set. Note: Since is an example we will use a fixed local TimeZone
            $from = date_create_immutable($request->query->get('from'), new \DateTimeZone('Europe/Madrid'));
            $to   = date_create_immutable($request->query->get('to'), new \DateTimeZone('Europe/Madrid'));
            // Validate also date conversion
            if ($from === false) {
                $errors['from'] = 'parameter could not be converted to DateTimeImmutable';
            }
            if ($to === false) {
                $errors['to'] = 'parameter could not be converted to DateTimeImmutable';
            }
            if (!isset($errors['channel']) &&
                !$channelRegistry->has($request->query->get('channel'))) {
                $errors['channel'] = sprintf(
                    'Unknown channel: %s Available channels: [%s]',
                    $request->query->get('channel'),
                    implode(', ', $channelRegistry->getNames())
                );
            }
            if ($errors !== []) {
                return new JsonResponse([
                    'message' => 'Validation failed',
                    'errors' => $errors,
                ], 422);
            }
            $notifications = $notificationLogRepository->findByChannelAndDateRange(
                $request->query->get('channel'),
                $from,
                $to
            );
        } else {
            // List all notifications
            $notifications = $notificationLogRepository->findAll();
        }
        // This uses Symfony serializer
        return $this->json(
            ['notifications' => $notifications]
        );
    }

    #[Route('/notifications/send', name: 'api_notifications_send', methods: ['POST'])]
    public function apiNotificationsSend(Request                     $request,
                                         NotificationLogRepository   $notificationLogRepository,
                                         NotificationChannelRegistry $channelRegistry): Response
    {
        // decode JSON and validate the request payload, check that channel exists and rest are non-empty
        try {
            $parsed = json_decode($request->getContent(), $associative = true, $depth = 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            // Return Exception as the requested 422 status
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => [
                    'payload' => 'Invalid JSON payload: ' . $e->getMessage(),
                ],
            ], 422);
        }
        // Validate payload and return field-keyed error map on validation failures
        $errors = [];
        $validate = ['channel', 'recipient', 'subject', 'body'];
        foreach ($validate as $v) {
            if (!array_key_exists($v, $parsed)) {
                $errors[$v] = 'Missing parameter: ' . $v;
                continue;
            }
            if (trim($parsed[$v]) === '' || !is_string($parsed[$v])) {
                $errors[$v] = 'Should be a non empty string';
            }
        }
        if (!$channelRegistry->has($parsed['channel']) && !isset($errors[$parsed['channel']])) {
            $errors['channel'] = sprintf(
                'Unknown channel: %s Available channels: [%s]',
                $parsed['channel'],
                implode(', ', $channelRegistry->getNames())
            );
        }
        if ($errors !== []) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $errors,
            ], 422);
        }
        // Look up the channel and send
        $channel = $channelRegistry->get($parsed['channel']);
        $sent = false;
        $errorMessage = null;

        try {
            $sent = $channel->send($parsed['recipient'], $parsed['subject'], $parsed['body']);
            if (!$sent) {
                $errorMessage = 'send action failed';
            }
        } catch (\Throwable $e) {
            $sent = false;
            $errorMessage = $e->getMessage();
        }

        // Persist log. EM can be fetched like this or injecting it on the function directly
        $em = $notificationLogRepository->getEntityManager();
        $log = new NotificationLog();
        $log->setSubject($parsed['subject']);
        $log->setBody($parsed['body']);
        // Missing recipient. Adding it in a new migration
        $log->setRecipient($parsed['recipient']);
        $log->setChannel($parsed['channel']);
        $log->setStatus($sent ? 'sent' : 'failed');
        $log->setErrorMessage($errorMessage);
        $em->persist($log);
        $em->flush();

        return new JsonResponse([
            'ok' => $sent,
            'status' => $log->getStatus(),
            'error' => $log->getErrorMessage(),
            'channel' => $log->getChannel(),
            'recipient' => $log->getRecipient(),
            'createdAt' => $log->getCreatedAt()->format(\DateTimeInterface::W3C),
        ], $sent ? 200 : 500);
    }

}
