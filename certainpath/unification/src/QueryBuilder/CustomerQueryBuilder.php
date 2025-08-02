<?php

namespace App\QueryBuilder;

use App\Entity\Customer;
use Doctrine\ORM\QueryBuilder;

readonly class CustomerQueryBuilder extends AbstractQueryBuilder
{
    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('c')
            ->from(Customer::class, 'c');
    }

    public function prepareFetchCustomerCitiesQueryBuilder(
        string $intacctId,
        string $sortOrder = 'ASC'
    ): QueryBuilder {
        return $this->em->createQueryBuilder()
            ->select("
                DISTINCT CONCAT(
                    UPPER(SUBSTRING(
                        CASE 
                            WHEN a.city IS NOT NULL THEN LOWER(a.city) 
                            ELSE LOWER(p.city) 
                        END, 1, 1
                    )), 
                    SUBSTRING(
                        CASE 
                            WHEN a.city IS NOT NULL THEN LOWER(a.city) 
                            ELSE LOWER(p.city) 
                        END, 2
                    )
                ) AS city
            ")
            ->from(Customer::class, 'c')
            ->leftJoin('c.prospect', 'p')
            ->leftJoin('p.addresses', 'a')
            ->innerJoin('c.company', 'co')
            ->andWhere('co.identifier = :identifier')
            ->setParameter('identifier', $intacctId)
            ->orderBy('city', $sortOrder);
    }
}
