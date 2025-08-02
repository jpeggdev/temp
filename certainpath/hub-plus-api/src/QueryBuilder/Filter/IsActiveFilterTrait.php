<?php

namespace App\QueryBuilder\Filter;

use Doctrine\ORM\QueryBuilder;

trait IsActiveFilterTrait
{
    protected function applyIsActiveFilter(
        QueryBuilder $queryBuilder,
        string $alias,
        bool $isActive,
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere($alias.'.isActive = :isActive')
            ->setParameter('isActive', $isActive);
    }
}
