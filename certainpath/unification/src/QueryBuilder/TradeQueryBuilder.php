<?php

namespace App\QueryBuilder;

use App\Entity\Trade;
use Doctrine\ORM\QueryBuilder;

readonly class TradeQueryBuilder extends AbstractQueryBuilder
{
    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('t')
            ->from(Trade::class, 't');
    }

    public function createFindByIdQueryBuilder(int $id): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder = $this->applyIdFilter($queryBuilder, $id);
        $queryBuilder->setMaxResults(1);

        return $queryBuilder;
    }

    public function createFindByNameQueryBuilder($name): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder = $this->applyFindByNameFilter($queryBuilder, $name);
        $queryBuilder->setMaxResults(1);

        return $queryBuilder;
    }

    private function applyIdFilter(
        QueryBuilder $queryBuilder,
        int $id
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('t.id = :id')
            ->setParameter('id', $id);
    }

    private function applyFindByNameFilter(
        QueryBuilder $queryBuilder,
        string $name
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('t.name = :name')
            ->setParameter('name', $name);
    }
}
