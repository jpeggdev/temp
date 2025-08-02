<?php

namespace App\QueryBuilder;

use App\Entity\EmailTemplateCategory;
use Doctrine\ORM\QueryBuilder;

readonly class EmailTemplateCategoryQueryBuilder extends AbstractQueryBuilder
{
    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('etc')
            ->from(EmailTemplateCategory::class, 'etc');
    }

    public function createFindOneByIdQueryBuilder(int $id): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();

        return $this->applyIdFilterQueryBuilder($queryBuilder, $id);
    }

    private function applyIdFilterQueryBuilder(QueryBuilder $queryBuilder, int $id): QueryBuilder
    {
        $queryBuilder->andWhere('etc.id = :id');
        $queryBuilder->setParameter('id', $id);

        return $queryBuilder;
    }
}
