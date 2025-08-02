<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\SavedQuery;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Query;

class SavedQueryRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SavedQuery::class);
    }

    public function getSavedQueriesForCompany(Company $company): Query
    {
        return $this->createQueryBuilder('sq')
            ->join('sq.company', 'a')
            ->where('a = :company')
            ->setParameter('company', $company)
            ->orderBy('sq.id', 'DESC')
            ->getQuery();
    }

    public function getSavedQueriesPaginator(Company $company, int $offset): Paginator
    {
        $query = $this->getSavedQueriesForCompany($company)
            ->setMaxResults(self::RESULTS_PER_PAGE)
            ->setFirstResult($offset);

        return new Paginator($query);
    }
}
