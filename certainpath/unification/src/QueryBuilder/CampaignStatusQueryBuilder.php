<?php

namespace App\QueryBuilder;

use App\Entity\CampaignStatus;
use Doctrine\ORM\QueryBuilder;

readonly class CampaignStatusQueryBuilder extends AbstractQueryBuilder
{
    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('cs')
            ->from(CampaignStatus::class, 'cs');
    }

    public function createFindByIdQueryBuilder(int $id): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        return $this->applyIdFilter($queryBuilder, $id);
    }

    public function createFetchAllQueryBuilder(string $sortOrder = 'DESC'): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder->orderBy('cs.id', $sortOrder);

        return $queryBuilder;
    }

    public function createFindByNameQueryBuilder(string $name): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        return $this->applyNameFilter($queryBuilder, $name);
    }

    private function applyIdFilter(
        QueryBuilder $queryBuilder,
        int $id
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('cs.id = :id')
            ->setParameter('id', $id);
    }

    private function applyNameFilter(
        QueryBuilder $queryBuilder,
        string $name
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('cs.name = :name')
            ->setParameter('name', $name);
    }
}
