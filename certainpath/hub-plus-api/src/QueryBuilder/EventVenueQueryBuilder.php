<?php

namespace App\QueryBuilder;

use App\Entity\EventVenue;
use App\Module\EventRegistration\Feature\EventVenueManagement\DTO\Query\GetEventVenuesDTO;
use App\QueryBuilder\Filter\IdFilterTrait;
use App\QueryBuilder\Filter\IsActiveFilterTrait;
use Doctrine\ORM\QueryBuilder;

readonly class EventVenueQueryBuilder extends AbstractQueryBuilder
{
    use IdFilterTrait;
    use IsActiveFilterTrait;

    public const string ALIAS = 'ev';

    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select(self::ALIAS)
            ->from(EventVenue::class, self::ALIAS);
    }

    public function createBaseCountQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('COUNT('.self::ALIAS.'.id)')
            ->from(EventVenue::class, self::ALIAS);
    }

    public function createGetCountByDTOQueryBuilder(GetEventVenuesDTO $queryDto): QueryBuilder
    {
        $queryBuilder = $this->createBaseCountQueryBuilder();

        return $this->applyFilters($queryBuilder, $queryDto);
    }

    public function createFindOneByIdQueryBuilder(int $id): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();

        return $this->applyIdFilter($queryBuilder, self::ALIAS, $id);
    }

    public function createFindAllActiveQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();

        return $this->applyIsActiveFilter($queryBuilder, self::ALIAS, true);
    }

    public function createFindAllByDTOQueryBuilder(GetEventVenuesDTO $queryDTO): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();

        return $this->applyFilters($queryBuilder, $queryDTO);
    }

    private function applyFilters(
        QueryBuilder $queryBuilder,
        GetEventVenuesDTO $queryDTO,
    ): QueryBuilder {
        $queryBuilder = $this->applyIsActiveFilter($queryBuilder, self::ALIAS, $queryDTO->isActive);

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
