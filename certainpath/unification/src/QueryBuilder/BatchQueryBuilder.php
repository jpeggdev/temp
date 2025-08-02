<?php

namespace App\QueryBuilder;

use App\Entity\Batch;
use App\Entity\BatchStatus;
use Carbon\Carbon;
use Doctrine\ORM\QueryBuilder;

readonly class BatchQueryBuilder extends BatchProspectQueryBuilder
{
    public function createFindByIdQueryBuilder(int $id): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        return $this->applyIdFilter($queryBuilder, $id);
    }

    public function createFetchAllByCampaignIdQueryBuilder(
        int $campaignId,
        string $sortOrder = 'ASC'
    ): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder = $this->applyCampaignIdFilter($queryBuilder, $campaignId);
        $queryBuilder->orderBy('b.id', $sortOrder);

        return $queryBuilder;
    }

    public function createFetchAllByCampaignIdAndStatusIdQueryBuilder(
        int $campaignId,
        ?int $statusId = null,
        string $sortOrder = 'ASC'
    ): QueryBuilder
    {
        $queryBuilder = $this->createFetchAllByCampaignIdQueryBuilder($campaignId, $sortOrder);

        if ($statusId) {
            $queryBuilder = $this->applyStatusIdFilter($queryBuilder, $statusId);
        }

        return $queryBuilder;
    }

    public function createFetchAllByCampaignIterationIdQueryBuilder(
        int $campaignIterationId,
        string $sortOrder = 'ASC'
    ): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder = $this->applyCampaignIterationIdFilter($queryBuilder, $campaignIterationId);
        $queryBuilder->orderBy('b.id', $sortOrder);

        return $queryBuilder;
    }

    public function createFetchAllByWeekStartAndEndDateQueryBuilder(
        Carbon $startDate,
        Carbon $endDate,
        string $sortOrder = 'ASC'
    ): QueryBuilder
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('b, c')
            ->from(Batch::class, 'b')
            ->join('b.campaign', 'c');

        $queryBuilder = $this->applyWeekStartAndEndDateFilter($queryBuilder, $startDate, $endDate);
        $queryBuilder->orderBy('b.id', $sortOrder);

        return $queryBuilder;
    }

    public function createFetchAllByStatusAndWeekStartAndEndDatesQueryBuilder(
        BatchStatus $status,
        Carbon $weekStartDate,
        Carbon $weekEndDate
    ): QueryBuilder {
        $queryBuilder =  $this->createFetchAllByWeekStartAndEndDateQueryBuilder(
            $weekStartDate,
            $weekEndDate
        );

        return $this->applyStatusFilter($queryBuilder, $status);
    }

    public function createGetBatchProspectsCountQueryBuilder(int $batchId): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('SIZE(b.prospects) AS prospectCount')
            ->from(Batch::class, 'b')
            ->where('b.id = :batchId')
            ->setParameter('batchId', $batchId);
    }

    private function applyIdFilter(
        QueryBuilder $queryBuilder,
        int $id): QueryBuilder
    {
        return $queryBuilder
            ->andWhere('b.id = :id')
            ->setParameter('id', $id);
    }

    private function applyCampaignIdFilter(
        QueryBuilder $queryBuilder,
        int $campaignId
    ): QueryBuilder {
        return $queryBuilder
            ->leftJoin('b.campaign', 'ca')
            ->andWhere('ca.id = :campaignId')
            ->setParameter('campaignId', $campaignId);
    }

    private function applyCampaignIterationIdFilter(
        QueryBuilder $queryBuilder,
        int $campaignIterationId
    ): QueryBuilder {
        return $queryBuilder
            ->leftJoin('b.campaignIteration', 'ci')
            ->andWhere('ci.id = :campaignIterationId')
            ->setParameter('campaignIterationId', $campaignIterationId);
    }

    private function applyWeekStartAndEndDateFilter(
        QueryBuilder $queryBuilder,
        Carbon $weekStartDate,
        Carbon $weekEndDate
    ): QueryBuilder
    {
        return $queryBuilder
            ->join('b.campaignIterationWeek', 'ciw')
            ->andWhere('ciw.startDate <= :endDate')
            ->andWhere('ciw.endDate >= :startDate')
            ->setParameter('startDate', $weekStartDate)
            ->setParameter('endDate', $weekEndDate);
    }

    private function applyStatusFilter(
        QueryBuilder $queryBuilder,
        BatchStatus $status
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('b.batchStatus = :status')
            ->setParameter('status', $status);
    }

    private function applyStatusIdFilter(
        QueryBuilder $queryBuilder,
        int $statusId
    ): QueryBuilder {
        if (!$this->aliasExists($queryBuilder, 'bs')) {
            $queryBuilder->innerJoin('b.batchStatus', 'bs');
        }

        return $queryBuilder
            ->andWhere('bs.id = :batchStatusId')
            ->setParameter('batchStatusId', $statusId);
    }
}