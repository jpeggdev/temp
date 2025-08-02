<?php

namespace App\QueryBuilder\Filter;

use Doctrine\ORM\QueryBuilder;

trait CodeFilterTrait
{
    protected function applyCodeFilter(
        QueryBuilder $queryBuilder,
        string $alias,
        string $code,
    ): QueryBuilder {
        $queryBuilder
            ->andWhere($alias.'.code = :code')
            ->setParameter('code', $code);

        return $queryBuilder;
    }
}
