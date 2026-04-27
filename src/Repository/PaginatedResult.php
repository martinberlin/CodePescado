<?php
namespace App\Repository;

final class PaginatedResult
{
    public function __construct(
        public readonly array $items,
        public readonly int $total,
        public readonly int $page,
        public readonly int $limit,
    ) {}
}
