<?php

namespace App\QueryBuilder;

use App\DTO\Query\Location\LocationsQueryDTO;
use App\Entity\Location;
use Doctrine\ORM\QueryBuilder;

readonly class LocationQueryBuilder extends AbstractQueryBuilder
{
    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('l')
            ->from(Location::class, 'l');
    }

    public function createFetchAllByDTOQueryBuilder(LocationsQueryDTO $locationsQueryDTO): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        return $this->applyFilters($queryBuilder, $locationsQueryDTO);
    }

    public function createFindOneByIdQueryBuilder(int $id): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        return $this->applyIdFilter($queryBuilder, $id);
    }

    public function createFindOneByNameQueryBuilder(string $name): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        return $this->applyNameFilter($queryBuilder, $name);
    }

    private function applyIdFilter(
        QueryBuilder $queryBuilder,
        int $id
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('l.id = :id')
            ->setParameter('id', $id);
    }

    private function applyNameFilter(
        QueryBuilder $queryBuilder,
        string $name
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('l.name = :name')
            ->setParameter('name', $name);
    }

    private function applySearchTermFilter(
        QueryBuilder $queryBuilder,
        string $searchTerm
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('l.name LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%');
    }

    private function applyIsActiveFilter(
        QueryBuilder $queryBuilder,
        bool $isActive
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('l.isActive = :isActive')
            ->setParameter('isActive', $isActive);
    }

    private function applyCompanyIdentifierFilter(
        QueryBuilder $queryBuilder,
        string $companyIdentifier
    ): QueryBuilder {
        if (!$this->aliasExists($queryBuilder, 'co')) {
            $queryBuilder->innerJoin('l.company', 'co');
        }

        return $queryBuilder
            ->andWhere('co.identifier = :identifier')
            ->setParameter('identifier', $companyIdentifier);
    }

    private function applyFilters(
        QueryBuilder $queryBuilder,
        LocationsQueryDTO $locationsQueryDTO
    ): QueryBuilder
    {
        $queryBuilder = $this->applyIsActiveFilter($queryBuilder, $locationsQueryDTO->isActive);

        if ($searchTerm = $locationsQueryDTO->searchTerm) {
            $queryBuilder = $this->applySearchTermFilter($queryBuilder, $searchTerm);
        }

        if ($companyIdentifier = $locationsQueryDTO->companyIdentifier) {
            $queryBuilder = $this->applyCompanyIdentifierFilter($queryBuilder, $companyIdentifier);
        }

        return $queryBuilder;
    }
}
