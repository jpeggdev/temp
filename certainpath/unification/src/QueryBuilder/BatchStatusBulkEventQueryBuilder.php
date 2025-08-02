<?php

namespace App\QueryBuilder;

use App\Entity\BulkBatchStatusEvent;
use Doctrine\ORM\QueryBuilder;

readonly class BatchStatusBulkEventQueryBuilder extends AbstractQueryBuilder
{
    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('bbs')
            ->from(BulkBatchStatusEvent::class, 'bbs');
    }

    public function createFindAllByYearAndWeekQueryBuilder(
        int $year,
        int $week,
        string $sortOrder = 'DESC'
    ): QueryBuilder {
        $queryBuilder =  $this->createBaseQueryBuilder();
        $queryBuilder = $this->applyYearFilter($queryBuilder, $year);
        $queryBuilder->orderBy('bbs.id', $sortOrder);
        return $this->applyWeekFilter($queryBuilder, $week);
    }

    private function applyYearFilter(
        QueryBuilder $queryBuilder,
        int $year
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('bbs.year = :year')
            ->setParameter('year', $year);
    }

    private function applyWeekFilter(
        QueryBuilder $queryBuilder,
        int $week
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('bbs.week = :week')
            ->setParameter('week', $week);
    }
}
