<?php

namespace App\QueryBuilder;

use App\Entity\EmailTemplate;
use App\QueryBuilder\Filter\IdFilterTrait;
use App\QueryBuilder\Filter\IsActiveFilterTrait;
use Doctrine\ORM\QueryBuilder;

readonly class EmailTemplateQueryBuilder extends AbstractQueryBuilder
{
    use IdFilterTrait;
    use IsActiveFilterTrait;

    public const string ALIAS = 'et';

    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select(self::ALIAS)
            ->from(EmailTemplate::class, self::ALIAS);
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
}
