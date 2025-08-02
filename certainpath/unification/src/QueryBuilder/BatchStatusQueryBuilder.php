<?php

namespace App\QueryBuilder;

use App\Entity\BatchStatus;
use Doctrine\ORM\QueryBuilder;

readonly class BatchStatusQueryBuilder extends AbstractQueryBuilder
{
    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('bs')
            ->from(BatchStatus::class, 'bs');
    }

    public function createFetchAllQueryBuilder(string $sortOrder = 'DESC'): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder->orderBy('bs.id', $sortOrder);

        return $queryBuilder;
    }

    public function createFindByIdQueryBuilder(int $id): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        return $this->applyIdFilter($queryBuilder, $id);
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
            ->andWhere('bs.id = :id')
            ->setParameter('id', $id);
    }

    private function applyNameFilter(
        QueryBuilder $queryBuilder,
        string $name
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('bs.name = :name')
            ->setParameter('name', $name);
    }
}
