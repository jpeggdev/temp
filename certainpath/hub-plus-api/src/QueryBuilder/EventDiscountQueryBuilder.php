<?php

namespace App\QueryBuilder;

use App\Entity\EventDiscount;
use App\Module\EventRegistration\Feature\EventDiscountManagement\DTO\Query\GetEventDiscountsDTO;
use App\QueryBuilder\Filter\CodeFilterTrait;
use App\QueryBuilder\Filter\IdFilterTrait;
use App\QueryBuilder\Filter\IsActiveFilterTrait;
use Doctrine\ORM\QueryBuilder;

readonly class EventDiscountQueryBuilder extends AbstractQueryBuilder
{
    use IdFilterTrait;
    use IsActiveFilterTrait;
    use CodeFilterTrait;

    public const string ALIAS = 'ed';

    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select(self::ALIAS)
            ->from(EventDiscount::class, self::ALIAS);
    }

    public function createBaseCountQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('COUNT('.self::ALIAS.'.id)')
            ->from(EventDiscount::class, self::ALIAS);
    }

    public function createGetCountByDTOQueryBuilder(GetEventDiscountsDTO $queryDto): QueryBuilder
    {
        $queryBuilder = $this->createBaseCountQueryBuilder();

        return $this->applyFilters($queryBuilder, $queryDto);
    }

    public function createFindOneByIdQueryBuilder(int $id): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();

        return $this->applyIdFilter($queryBuilder, self::ALIAS, $id);
    }

    public function createFindOneByCodeQueryBuilder(string $code): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();

        return $this->applyCodeFilter($queryBuilder, self::ALIAS, $code);
    }

    public function createFindAllActiveQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();

        return $this->applyIsActiveFilter($queryBuilder, self::ALIAS, true);
    }

    public function createFindAllByDTOQueryBuilder(GetEventDiscountsDTO $queryDTO): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();

        return $this->applyFilters($queryBuilder, $queryDTO);
    }

    private function applyFilters(
        QueryBuilder $queryBuilder,
        GetEventDiscountsDTO $queryDTO,
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
            ->andWhere('LOWER('.self::ALIAS.'.code) LIKE :searchTerm')
            ->setParameter('searchTerm', '%'.strtolower($searchTerm).'%');

        return $queryBuilder;
    }
}
