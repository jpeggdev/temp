<?php

namespace App\QueryBuilder;

use App\Entity\CampaignEvent;
use Doctrine\ORM\QueryBuilder;

readonly class CampaignEventQueryBuilder extends AbstractQueryBuilder
{
    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('ce')
            ->from(CampaignEvent::class, 'ce');
    }

    public function createFindOneByIdQueryBuilder(?int $getId): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        return $this->applyIdFilter($queryBuilder, $getId);
    }

    public function createFetchAllByCampaignIdentifierBuilder(string $identifier): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        return $this->applyCampaignIdentifierFilter($queryBuilder, $identifier);
    }

    public function createFindAllByCampaignIdQueryBuilder(int $campaignId): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        return $this->applyCampaignIdFilter($queryBuilder, $campaignId);
    }

    private function applyIdFilter(
        QueryBuilder $queryBuilder,
        int $id
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('ce.id = :id')
            ->setParameter('id', $id);
    }

    private function applyCampaignIdentifierFilter(
        QueryBuilder $queryBuilder,
        string $identifier
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('ce.campaignIdentifier = :identifier')
            ->setParameter('identifier', $identifier);
    }

    private function applyCampaignIdFilter(
        QueryBuilder $queryBuilder,
        int $campaignId
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('ce.campaign = :campaignId')
            ->setParameter('campaignId', $campaignId);
    }
}
