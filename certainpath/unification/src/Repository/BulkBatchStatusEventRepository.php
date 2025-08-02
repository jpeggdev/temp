<?php

namespace App\Repository;

use App\Entity\BulkBatchStatusEvent;
use App\QueryBuilder\BatchStatusBulkEventQueryBuilder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

class BulkBatchStatusEventRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly BatchStatusBulkEventQueryBuilder $batchStatusBulkEventQueryBuilder,

    )
    {
        parent::__construct($registry, BulkBatchStatusEvent::class);
    }

    public function findAllByYearAndWeek(
        int $year,
        int $week,
        string $sortOrder = 'DESC'
    ): ArrayCollection {
        $result = $this->batchStatusBulkEventQueryBuilder
            ->createFindAllByYearAndWeekQueryBuilder($year, $week, $sortOrder)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    public function findLatestByYearAndWeek(int $year, int $week): ?BulkBatchStatusEvent
    {
        return $this->batchStatusBulkEventQueryBuilder
            ->createFindAllByYearAndWeekQueryBuilder($year, $week, 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
