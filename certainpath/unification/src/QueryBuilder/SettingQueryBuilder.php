<?php

namespace App\QueryBuilder;

use App\Entity\Setting;
use Doctrine\ORM\QueryBuilder;

readonly class SettingQueryBuilder extends AbstractQueryBuilder
{
    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('s')
            ->from(Setting::class, 's');
    }

    public function createFindSettingByNameQueryBuilder(
        string $name,
        string $sortOrder = 'ASC',
        int $limit = 10
    ): QueryBuilder {
        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder = $this->applyNameFilter($queryBuilder, $name);
        $queryBuilder->orderBy('s.id', $sortOrder);
        $queryBuilder->setMaxResults($limit);

        return $queryBuilder;
    }

    public function applyNameFilter(
        QueryBuilder $queryBuilder,
        string $name
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('s.name = :name')
            ->setParameter('name', $name);
    }
}
