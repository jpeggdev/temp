<?php

namespace App\QueryBuilder;

use App\Entity\CampaignIterationStatus;
use Doctrine\ORM\QueryBuilder;

readonly class CampaignIterationStatusQueryBuilder extends AbstractQueryBuilder
{
    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('cis')
            ->from(CampaignIterationStatus::class, 'cis');
    }

    public function createFetchAllQueryBuilder(string $sortOrder = 'DESC'): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder->orderBy('cis.id', $sortOrder);

        return $queryBuilder;
    }

    public function createFindOneByNameQueryBuilder(string $name): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        return $this->applyNameFilter($queryBuilder, $name);
    }

    private function applyNameFilter(
        QueryBuilder $queryBuilder,
        string $name
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('cis.name = :name')
            ->setParameter('name', $name);
    }
}
