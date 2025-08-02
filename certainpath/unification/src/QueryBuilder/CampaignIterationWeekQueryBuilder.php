<?php

namespace App\QueryBuilder;

use App\Entity\CampaignIterationWeek;
use App\ValueObjects\CampaignIterationWeekObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;

readonly class CampaignIterationWeekQueryBuilder extends AbstractQueryBuilder
{
    public function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('ciw')
            ->from(CampaignIterationWeek::class, 'ciw');
    }

    public function createBulkInsertCampaignIterationWeeksSQLQuery(
        ArrayCollection $campaignIterationWeeksObjects
    ): array {
        $sql = 'INSERT INTO campaign_iteration_week (
            campaign_iteration_id,
            week_number,
            start_date,
            end_date,
            created_at,
            updated_at,
            is_mailing_drop_week
        ) VALUES ';

        $values = [];
        $params = [];

        /** @var CampaignIterationWeekObject $iterationWeek */
        foreach ($campaignIterationWeeksObjects as $index => $iterationWeek) {
            $values[] = sprintf(
                '(
                    :campaign_iteration_id_%d,
                    :week_number_%d,
                    :start_date_%d,
                    :end_date_%d,
                    :created_at_%d,
                    :updated_at_%d,
                    :is_mailing_drop_week_%d
                )',
                ...array_fill(0, 7, $index)
            );
            $params[sprintf('campaign_iteration_id_%d', $index)] = $iterationWeek->campaignIterationId;
            $params[sprintf('week_number_%d', $index)] = $iterationWeek->weekNumber;
            $params[sprintf('start_date_%d', $index)] = $iterationWeek->startDate;
            $params[sprintf('end_date_%d', $index)] = $iterationWeek->endDate;
            $params[sprintf('created_at_%d', $index)] = $iterationWeek->createdAt->format('Y-m-d H:i:s');
            $params[sprintf('updated_at_%d', $index)] = $iterationWeek->updatedAt->format('Y-m-d H:i:s');
            $params[sprintf('is_mailing_drop_week_%d', $index)] = (int) $iterationWeek->isMailingDropWeek;
        }

        $sql .= implode(', ', $values);

        return [$sql, $params];
    }

    public function createFindByIdQueryBuilder(int $id): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        return $this->applyIdFilter($queryBuilder, $id);
    }

    public function createFindAllByCampaignIterationIdQueryBuilder(
        int $campaignIterationId,
        string $sortOrder = 'ASC'
    ): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder = $this->applyCampaignIterationIdFilter($queryBuilder, $campaignIterationId);
        $queryBuilder->orderBy('ciw.weekNumber', $sortOrder);

        return $queryBuilder;
    }

    private function applyIdFilter(QueryBuilder $queryBuilder, int $id): QueryBuilder
    {
        return $queryBuilder
            ->andWhere('ciw.id = :id')
            ->setParameter('id', $id);
    }

    private function applyCampaignIterationIdFilter(
        QueryBuilder $queryBuilder,
        int $campaignIterationId
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('ciw.campaignIteration = :campaignIterationId')
            ->setParameter('campaignIterationId', $campaignIterationId);
    }
}
