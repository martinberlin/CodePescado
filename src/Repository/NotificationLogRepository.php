<?php

namespace App\Repository;

use App\Channel\NotificationChannelRegistry;
use App\Entity\NotificationLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NotificationLog>
 */
class NotificationLogRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly NotificationChannelRegistry $channelRegistry)
    {
        parent::__construct($registry, NotificationLog::class);
    }

public function findByChannelAndDateRange(
    string $channel,
    \DateTimeInterface $from,
    ?\DateTimeInterface $to)
{
    // 1st Validate that $from and $to are a valid period
    if ($from > $to) {
        throw new \InvalidArgumentException("from date should be greater than to date");
    }
    // 2nd validate that $channel exists
    if (!$this->channelRegistry->has($channel)) {
        throw new \InvalidArgumentException(sprintf(
            'Unknown channel "%s". Available channels: [%s]',
            $channel,
            implode(', ', $this->channelRegistry->getNames())
        ));
    }
    return $this->createQueryBuilder('nl')
        ->andWhere('nl.channel = :channel')
        ->andWhere('nl.createdAt >= :from')
        ->andWhere('nl.createdAt <= :to')
        ->setParameter('channel', $channel)
        ->setParameter('from', $from)
        ->setParameter('to', $to)
        ->orderBy('nl.createdAt', 'ASC')
        ->getQuery()
        ->getResult();
}

}
