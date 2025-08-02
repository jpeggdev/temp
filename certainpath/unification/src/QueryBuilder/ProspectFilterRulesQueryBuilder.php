<?php

namespace App\QueryBuilder;

use App\Entity\ProspectFilterRule;
use Doctrine\ORM\QueryBuilder;

readonly class ProspectFilterRulesQueryBuilder extends AbstractQueryBuilder
{
    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('pfr')
            ->from(ProspectFilterRule::class, 'pfr');
    }

    public function createFindByNameQueryBuilder(string $name): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        return $this->applyNameFilter($queryBuilder, $name);
    }

    public function createFindByNameAndValueQueryBuilder(string $name, mixed $value): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder = $this->applyNameFilter($queryBuilder, $name);
        return $this->applyValueFilter($queryBuilder, $value);
    }

    private function applyNameFilter(
        QueryBuilder $queryBuilder,
        string $name
    ): QueryBuilder {
        return $queryBuilder
            ->where('pfr.name = :name')
            ->setParameter('name', $name);
    }

    private function applyValueFilter(
        QueryBuilder $queryBuilder,
        mixed $value
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('pfr.value = :value')
            ->setParameter('value', $value);
    }
}
