<?php

namespace App\QueryBuilder\Filter;

use Doctrine\ORM\QueryBuilder;

trait NameFilterTrait
{
    protected function applyNameFilter(
        QueryBuilder $queryBuilder,
        string $alias,
        string $name,
    ): QueryBuilder {
        $queryBuilder
            ->andWhere($alias.'.name = :name')
            ->setParameter('name', $name);

        return $queryBuilder;
    }
}
