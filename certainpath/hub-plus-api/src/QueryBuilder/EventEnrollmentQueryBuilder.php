<?php

namespace App\QueryBuilder;

use App\Entity\EventEnrollment;
use App\QueryBuilder\Filter\IdFilterTrait;
use Doctrine\ORM\QueryBuilder;

readonly class EventEnrollmentQueryBuilder extends AbstractQueryBuilder
{
    use IdFilterTrait;

    public const string ALIAS = 'ee';

    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select(self::ALIAS)
            ->from(EventEnrollment::class, self::ALIAS);
    }

    public function createBaseCountQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('COUNT('.self::ALIAS.'.id)')
            ->from(EventEnrollment::class, self::ALIAS);
    }

    public function createGetAllByEventSessionId(int $eventSessionId): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();

        return $this->applyEventSessionIdFilter($queryBuilder, $eventSessionId);
    }

    private function applyEventSessionIdFilter(
        QueryBuilder $queryBuilder,
        int $eventSessionId,
    ): QueryBuilder {
        if (!$this->aliasExists($queryBuilder, 'es')) {
            $queryBuilder->innerJoin(self::ALIAS.'.eventSession', 'es');
        }

        return $queryBuilder
            ->andWhere('es.id = :eventSessionId')
            ->setParameter('eventSessionId', $eventSessionId);
    }
}
