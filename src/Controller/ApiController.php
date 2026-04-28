<?php

namespace App\Controller;

use App\Channel\NotificationChannelRegistry;
use App\Entity\NotificationLog;
use App\Repository\NotificationLogRepository;
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
        $channels = $channelRegistry->getNames();

        return new JsonResponse([
            'channels' => $channels,
            'count' => count($channels),
        ]);
    }

    /**
     * Brief: Helper method to avoid validating all the time if DATE instanceof DateTimeImmutable
     * @param Request $request
     * @param string $param
     * @param \DateTimeZone $tz
     * @param array $errors
     * @return \DateTimeImmutable|null
     */
    private function parseOptionalDateTimeImmutable(
        Request       $request,
        string        $param,
        \DateTimeZone $tz,
        array         &$errors,
    ): ?\DateTimeImmutable
    {
        $raw = trim($request->query->getString($param, ''));
        if ($raw === '') {
            return null;
        }

        $dt = date_create_immutable($raw, $tz);
        if ($dt === false) {
            $errors[$param] = sprintf('%s could not be converted to DateTimeImmutable', $param);
            return null;
        }

        return $dt;
    }

    /**
     * NOTE: This route should be protected with a Bearer token defined in .env for the test and implemented in security.yaml
     * Added pagination: Check NotificationLogRepository
     * @param Request $request
     * @param NotificationChannelRegistry $channelRegistry
     * @return Response
     */
    #[Route('/notifications', name: 'api_notifications', methods: ['GET'])]
    public function apiNotifications(
        Request                     $request,
        NotificationLogRepository   $notificationLogRepository,
        NotificationChannelRegistry $channelRegistry,
    ): JsonResponse
    {
        $errors = [];
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = (int)$request->query->get('limit', 20);
        if ($limit < 1) {
            $errors['limit'] = 'limit must be >= 1';
        }
        $limit = min(max($limit, 1), 100);

        $channel = $request->query->getString('channel', '');
        if ($channel !== '' && !$channelRegistry->has($channel)) {
            $errors['channel'] = sprintf(
                'Unknown channel: %s Available channels: [%s]',
                $channel,
                implode(', ', $channelRegistry->getNames())
            );
        }

        $tz = new \DateTimeZone('Europe/Madrid');
        $from = $this->parseOptionalDateTimeImmutable($request, 'from', $tz, $errors);
        $to = $this->parseOptionalDateTimeImmutable($request, 'to', $tz, $errors);

        if ($from !== null && $to !== null && $from > $to) {
            $errors['from'] = 'from must be <= to';
        }
        if ($from !== null && $to !== null && $from > $to) {
            $errors['from'] = 'from must be <= to';
        }

        if ($errors !== []) {
            return $this->json([
                'message' => 'Validation failed',
                'errors' => $errors,
            ], 422);
        }

        $result = $notificationLogRepository->searchPaginated(
            channel: $channel !== '' ? $channel : null,
            from: $from,
            to: $to,
            page: $page,
            limit: $limit
        );

        return $this->json([
            'notifications' => $result->items,
            'meta' => [
                'page' => $result->page,
                'limit' => $result->limit,
                'total' => $result->total,
                'pages' => (int)ceil($result->total / max(1, $result->limit))
            ],
            'filters' => [
                'channel' => $channel !== '' ? $channel : null,
                'from' => $from?->format(\DateTimeInterface::ATOM),
                'to' => $to?->format(\DateTimeInterface::ATOM)
            ],
        ]);
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
