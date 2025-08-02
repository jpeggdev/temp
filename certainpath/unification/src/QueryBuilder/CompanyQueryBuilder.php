<?php

namespace App\QueryBuilder;

use App\Entity\Company;
use Doctrine\ORM\QueryBuilder;

readonly class CompanyQueryBuilder extends AbstractQueryBuilder
{
    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('co')
            ->from(Company::class, 'co');
    }

    public function createFindByIdQueryBuilder(int $id): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        return $this->applyIdFilter($queryBuilder, $id);
    }

    public function createFindByIdentifierQueryBuilder(
        string $identifier,
        string $sortOrder = 'ASC'
    ): QueryBuilder {
        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder = $this->applyIdentifierFilter($queryBuilder, $identifier);
        $queryBuilder->orderBy('co.id', $sortOrder);

        return $queryBuilder;
    }

    public function createFindActiveByIdentifierQueryBuilder(
        string $identifier,
        string $sortOrder = 'ASC'
    ): QueryBuilder {
        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder = $this->applyIdentifierFilter($queryBuilder, $identifier);
        $queryBuilder = $this->applyIsActiveFilter($queryBuilder);
        $queryBuilder = $this->applyIsDeletedFilter($queryBuilder);
        $queryBuilder->orderBy('co.id', $sortOrder);

        return $queryBuilder;
    }

    public function createFetchAllActive(string $sortOrder = 'ASC'): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder = $this->applyIsActiveFilter($queryBuilder);
        $queryBuilder = $this->applyIsDeletedFilter($queryBuilder);
        $queryBuilder->orderBy('co.id', $sortOrder);

        return $queryBuilder;
    }

    private function applyIdFilter(QueryBuilder $queryBuilder, int $id): QueryBuilder
    {
        return $queryBuilder
            ->andWhere('co.id = :id')
            ->setParameter('id', $id);
    }

    public function applyIdentifierFilter(
        $queryBuilder,
        string $identifier
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('co.identifier = :identifier')
            ->setParameter('identifier', $identifier);
    }

    public function applyIsActiveFilter(QueryBuilder $queryBuilder, bool $isActive = true): QueryBuilder
    {
        return $queryBuilder
            ->andWhere('co.isActive = :isActive')
            ->setParameter('isActive', $isActive);
    }

    public function applyIsDeletedFilter($queryBuilder, bool $isDeleted = false): QueryBuilder
    {
        return $queryBuilder
            ->andWhere('co.isDeleted = :isDeleted')
            ->setParameter('isDeleted', $isDeleted);
    }
}
