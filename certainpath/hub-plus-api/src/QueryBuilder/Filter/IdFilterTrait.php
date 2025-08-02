<?php

namespace App\QueryBuilder\Filter;

use Doctrine\ORM\QueryBuilder;

trait IdFilterTrait
{
    protected function applyIdFilter(
        QueryBuilder $queryBuilder,
        string $alias,
        int $id,
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere($alias.'.id = :id')
            ->setParameter('id', $id);
    }
}
