<?php

namespace App\QueryBuilder;

use App\Entity\EventVoucher;
use App\Module\EventRegistration\Feature\EventVoucherManagement\DTO\Query\GetEventVouchersDTO;
use App\QueryBuilder\Filter\IdFilterTrait;
use App\QueryBuilder\Filter\IsActiveFilterTrait;
use Doctrine\ORM\QueryBuilder;

readonly class EventVoucherQueryBuilder extends AbstractQueryBuilder
{
    use IdFilterTrait;
    use IsActiveFilterTrait;

    public const string ALIAS = 'esv';

    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select(self::ALIAS)
            ->from(EventVoucher::class, self::ALIAS);
    }

    public function createBaseCountQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('COUNT('.self::ALIAS.'.id)')
            ->from(EventVoucher::class, self::ALIAS);
    }

    public function createGetCountByDTOQueryBuilder(GetEventVouchersDTO $queryDto): QueryBuilder
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

    public function createFindAllByDTOQueryBuilder(GetEventVouchersDTO $queryDTO): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();

        return $this->applyFilters($queryBuilder, $queryDTO);
    }

    public function createFindOneByNameQueryBuilder(string $code): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();

        return $this->applyNameFilterQueryBuilder($queryBuilder, $code);
    }

    private function applyFilters(
        QueryBuilder $queryBuilder,
        GetEventVouchersDTO $queryDTO,
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
        if (!$this->aliasExists($queryBuilder, 'co')) {
            $queryBuilder->innerJoin(self::ALIAS.'.company', 'co');
        }

        $queryBuilder
            ->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->like('LOWER('.self::ALIAS.'.name)', ':searchTerm'),
                    $queryBuilder->expr()->like('LOWER(co.intacctId)', ':searchTerm')
                )
            )
            ->setParameter('searchTerm', '%'.strtolower($searchTerm).'%');

        return $queryBuilder;
    }

    private function applyNameFilterQueryBuilder(
        QueryBuilder $queryBuilder,
        string $name,
    ): QueryBuilder {
        $queryBuilder
            ->andWhere(self::ALIAS.'.name = :name')
            ->setParameter('name', $name);

        return $queryBuilder;
    }
}
