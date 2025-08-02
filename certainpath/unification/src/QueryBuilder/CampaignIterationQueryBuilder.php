<?php

namespace App\QueryBuilder;

use App\Entity\CampaignIterationStatus;
use DateTime;
use Doctrine\ORM\QueryBuilder;
use App\Entity\CampaignIteration;

readonly class CampaignIterationQueryBuilder extends AbstractQueryBuilder
{
    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('ci')
            ->from(CampaignIteration::class, 'ci');
    }

    public function createFindByIdQueryBuilder(int $id): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        return $this->applyIdFilter($queryBuilder, $id);
    }

    public function createFindNextActiveByCampaignIdQueryBuilder(
        int $campaignId,
        DateTime $iterationStartDate = null,
        string $sortOrder = 'ASC'
    ): QueryBuilder {
        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder = $this->applyCampaignIdFilter($queryBuilder, $campaignId);
        $queryBuilder = $this->applyStatusesFilter(
            $queryBuilder,
            [CampaignIterationStatus::STATUS_ACTIVE]
        );

        if ($iterationStartDate) {
            $queryBuilder = $this->applyCampaignIterationStartDateEqualFilter(
                $queryBuilder,
                $iterationStartDate
            );
        }

        $queryBuilder->orderBy('ci.id', $sortOrder);
        $queryBuilder->setMaxResults(1);

        return $queryBuilder;
    }

    public function createFindAllByCampaignIdQueryBuilder(
        int $campaignId,
        string $sortOrder = 'ASC'
    ): QueryBuilder {
        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder = $this->applyCampaignIdFilter($queryBuilder, $campaignId);
        $queryBuilder->orderBy('ci.startDate', $sortOrder);

        return $queryBuilder;
    }

    public function createFindAllByCampaignIdAndStatusesQueryBuilder(
        int $campaignId,
        array $statuses,
        string $sortOrder = 'ASC'
    ): QueryBuilder {
        $queryBuilder = $this->createFindAllByCampaignIdQueryBuilder($campaignId, $sortOrder);
        $queryBuilder = $this->applyStatusesFilter($queryBuilder, $statuses);
        $queryBuilder->orderBy('ci.startDate', $sortOrder);

        return $queryBuilder;
    }

    public function createFindCurrentActiveByCampaignIdQueryBuilder(
        int $campaignId,
        string $sortOrder = 'ASC'
    ): QueryBuilder {
        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder = $this->applyCampaignIdFilter($queryBuilder, $campaignId);
        $queryBuilder = $this->applyStatusesFilter(
            $queryBuilder,
            [CampaignIterationStatus::STATUS_ACTIVE]
        );

        $queryBuilder->orderBy('ci.id', $sortOrder);
        $queryBuilder->setMaxResults(1);

        return $queryBuilder;
    }

    public function createFindNextPendingByCampaignIdQueryBuilder(
        int $campaignId,
        DateTime $iterationStartDate = null,
        string $sortOrder = 'ASC',
    ): QueryBuilder {
        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder = $this->applyCampaignIdFilter($queryBuilder, $campaignId);
        $queryBuilder = $this->applyStatusesFilter(
            $queryBuilder,
            [CampaignIterationStatus::STATUS_PENDING]
        );

        if ($iterationStartDate) {
            $queryBuilder = $this->applyCampaignIterationStartDateEqualFilter(
                $queryBuilder,
                $iterationStartDate
            );
        }

        $queryBuilder->orderBy('ci.id', $sortOrder);
        $queryBuilder->setMaxResults(1);

        return $queryBuilder;
    }

    public function createFindCampaignIterationsByCampaignIdAndDateBeforeOrEqual(
        int $campaignId,
        DateTime $iterationStartDate,
        string $sortOrder = 'ASC',
    ): QueryBuilder {
        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder = $this->applyCampaignIdFilter($queryBuilder, $campaignId);
        $queryBuilder = $this->applyCampaignIterationStartDateBeforeOrEqual($queryBuilder, $iterationStartDate);
        $queryBuilder->orderBy('ci.id', $sortOrder);

        return $queryBuilder;
    }

    public function applyIdFilter(
        QueryBuilder $queryBuilder,
        int $id
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('ci.id = :id')
            ->setParameter('id', $id);
    }

    public function applyCampaignIdFilter(
        $queryBuilder,
        $campaignId
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('ci.campaign = :campaignId')
            ->setParameter('campaignId', $campaignId);
    }

    private function applyCampaignIterationStartDateEqualFilter(
        QueryBuilder $queryBuilder,
        DateTime $campaignIterationStartDate
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('ci.startDate = :campaignIterationStartDate')
            ->setParameter('campaignIterationStartDate', $campaignIterationStartDate);
    }

    private function applyCampaignIterationStartDateBeforeOrEqual(
        QueryBuilder $queryBuilder,
        DateTime $campaignIterationStartDate
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('ci.startDate <= :campaignIterationStartDate')
            ->setParameter('campaignIterationStartDate', $campaignIterationStartDate);
    }

    public function applyStatusesFilter(
        QueryBuilder $queryBuilder,
        array $status
    ): QueryBuilder {
        if (!$this->aliasExists($queryBuilder, 'cis')) {
            $queryBuilder->innerJoin('ci.campaignIterationStatus', 'cis');
        }

        return $queryBuilder
            ->andWhere('cis.name IN (:status)')
            ->setParameter('status', $status);
    }
}
