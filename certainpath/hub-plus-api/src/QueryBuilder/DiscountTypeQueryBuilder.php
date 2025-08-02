<?php

namespace App\QueryBuilder;

use App\Entity\DiscountType;
use App\QueryBuilder\Filter\IdFilterTrait;
use App\QueryBuilder\Filter\NameFilterTrait;
use Doctrine\ORM\QueryBuilder;

readonly class DiscountTypeQueryBuilder extends AbstractQueryBuilder
{
    use IdFilterTrait;
    use NameFilterTrait;

    public const string ALIAS = 'dt';

    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select(self::ALIAS)
            ->from(DiscountType::class, self::ALIAS);
    }

    public function createBaseCountQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('COUNT('.self::ALIAS.'.id)')
            ->from(DiscountType::class, self::ALIAS);
    }

    public function createFindOneByIdQueryBuilder(int $id): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();

        return $this->applyIdFilter($queryBuilder, self::ALIAS, $id);
    }

    public function createFindOneByNameQueryBuilder(string $name): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();

        return $this->applyNameFilter($queryBuilder, self::ALIAS, $name);
    }
}
