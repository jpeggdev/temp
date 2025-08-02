<?php

namespace App\QueryBuilder;

use App\Entity\Timezone;
use App\Module\EventRegistration\Feature\Shared\Timezone\DTO\Query\GetTimezonesDTO;
use App\QueryBuilder\Filter\IdFilterTrait;
use Doctrine\ORM\QueryBuilder;

readonly class TimezoneQueryBuilder extends AbstractQueryBuilder
{
    use IdFilterTrait;

    public const string ALIAS = 'tz';

    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select(self::ALIAS)
            ->from(Timezone::class, self::ALIAS);
    }

    public function createBaseCountQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('COUNT('.self::ALIAS.'.id)')
            ->from(Timezone::class, self::ALIAS);
    }

    public function createGetCountByDTOQueryBuilder(GetTimezonesDTO $queryDto): QueryBuilder
    {
        $queryBuilder = $this->createBaseCountQueryBuilder();

        return $this->applyFilters($queryBuilder, $queryDto);
    }

    public function createFindOneByIdQueryBuilder(int $id): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();

        return $this->applyIdFilter($queryBuilder, self::ALIAS, $id);
    }

    public function createFindAllByDTOQueryBuilder(
        GetTimezonesDTO $queryDTO,
    ): QueryBuilder {
        $queryBuilder = $this->createBaseQueryBuilder();

        return $this->applyFilters($queryBuilder, $queryDTO);
    }

    private function applyFilters(
        QueryBuilder $queryBuilder,
        GetTimezonesDTO $queryDTO,
    ): QueryBuilder {
        if ($searchTerm = $queryDTO->searchTerm) {
            $queryBuilder = $this->applySearchTermFilter($queryBuilder, $searchTerm);
        }

        return $queryBuilder;
    }

    private function applySearchTermFilter(
        QueryBuilder $queryBuilder,
        string $searchTerm,
    ): QueryBuilder {
        $queryBuilder
            ->andWhere('LOWER('.self::ALIAS.'.name) LIKE :searchTerm')
            ->setParameter('searchTerm', '%'.strtolower($searchTerm).'%');

        return $queryBuilder;
    }
}
