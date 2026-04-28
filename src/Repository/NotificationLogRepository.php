<?php

namespace App\Repository;

use App\Channel\NotificationChannelRegistry;
use App\Entity\NotificationLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NotificationLog>
 */
class NotificationLogRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry                              $registry,
        private readonly NotificationChannelRegistry $channelRegistry)
    {
        parent::__construct($registry, NotificationLog::class);
    }

    public function findByChannelAndDateRange(
        string              $channel,
        \DateTimeInterface  $from,
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

    public function searchPaginated(
        ?string             $channel,
        ?\DateTimeImmutable $from,
        ?\DateTimeImmutable $to,
        int                 $page,
        int                 $limit,
    ): PaginatedResult
    {
        $qb = $this->createQueryBuilder('n')
            ->orderBy('n.createdAt', 'DESC');

        if ($channel !== null) {
            $qb->andWhere('n.channel = :channel')
                ->setParameter('channel', $channel);
        }

        if ($from !== null) {
            $qb->andWhere('n.createdAt >= :from')
                ->setParameter('from', $from);
        }

        if ($to !== null) {
            $qb->andWhere('n.createdAt <= :to')
                ->setParameter('to', $to);
        }

        $offset = ($page - 1) * $limit;

        $qb->setFirstResult($offset)
            ->setMaxResults($limit);

        $paginator = new Paginator($qb->getQuery(), fetchJoinCollection: false);

        // Paginator implements Countable for total rows
        $total = count($paginator);

        // Convert iterator to array: https://www.php.net/manual/en/function.iterator-to-array.php
        $items = iterator_to_array($paginator->getIterator(), false);

        return new PaginatedResult($items, $total, $page, $limit);
    }

}
