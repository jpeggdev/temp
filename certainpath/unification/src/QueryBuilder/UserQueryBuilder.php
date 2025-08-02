<?php

namespace App\QueryBuilder;

use App\Entity\User;
use Doctrine\ORM\QueryBuilder;

readonly class UserQueryBuilder extends AbstractQueryBuilder
{
    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u');
    }

    public function createFindOneByIdentifierQueryBuilder(string $identifier): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        return $this->applyIdentifierFilter($queryBuilder, $identifier);
    }

    public function createFetchAllQueryBuilder(): QueryBuilder
    {
        return $this
            ->createBaseQueryBuilder()
            ->orderBy('u.identifier', 'ASC');
    }

    public function createFindRelatedUsersQueryBuilder(User $user): QueryBuilder
    {
        $companyIdentifiers = $user->getCompanies()->map(function ($company) {
            return $company->getIdentifier();
        })->toArray();

        $queryBuilder = $this->createBaseQueryBuilder();
        return $this->applyCompanyIdentifiersFilter($queryBuilder, $companyIdentifiers);
    }

    private function applyIdentifierFilter(
        QueryBuilder $queryBuilder,
        string $identifier
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('u.identifier = :identifier')
            ->setParameter('identifier', $identifier);
    }

    private function applyCompanyIdentifiersFilter(
        QueryBuilder $queryBuilder,
        array $companyIdentifiers
    ): QueryBuilder {
        return $queryBuilder
            ->join('u.companies', 'co')
            ->where('co.identifier IN(:companies)')
            ->setParameter('companies', $companyIdentifiers);
    }
}
