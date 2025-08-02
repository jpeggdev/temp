<?php

namespace App\Repository;

use App\Entity\Company;
use App\Exceptions\NotFoundException\CompanyNotFoundException;
use App\QueryBuilder\CompanyQueryBuilder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

class CompanyRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly CompanyQueryBuilder $companyQueryBuilder,
    ) {
        parent::__construct($registry, Company::class);
    }

    public function saveCompany(Company $company): Company
    {
        /** @var Company $saved */
        $saved = $this->save($company);
        return $saved;
    }

    public function findOneById(int $id): ?Company
    {
        return $this->companyQueryBuilder
            ->createFindByIdQueryBuilder($id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByIdentifier(string $identifier): ?Company
    {
        return $this->companyQueryBuilder
            ->createFindByIdentifierQueryBuilder($identifier)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws CompanyNotFoundException
     */
    public function findOneByIdentifierOrFail(string $identifier): Company
    {
        $result = $this->findOneByIdentifier($identifier);

        if (!$result) {
            throw new CompanyNotFoundException();
        }

        return $result;
    }

    public function fetchAll(): ArrayCollection
    {
        return new ArrayCollection($this->createQueryBuilder('c')
            ->orderBy('c.identifier', 'ASC')
            ->getQuery()
            ->getResult());
    }

    public function findActiveByIdentifierOrCreate(string $identifier): ?Company
    {
        $activeCompany = $this->companyQueryBuilder
            ->createFindByIdentifierQueryBuilder($identifier)
            ->getQuery()
            ->getOneOrNullResult();

        // if the company record doesn't exist: create it and then return it
        if (!$activeCompany) {
            $company = new Company();
            $company->setIdentifier($identifier);
            $company->setName($identifier);
            $company->setActive(true);
            $this->saveCompany($company);
        }

        return $this->companyQueryBuilder
            ->createFindActiveByIdentifierQueryBuilder($identifier)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return ArrayCollection<int, Company>
     */
    public function fetchAllActive(string $sortOrder = 'ASC'): ArrayCollection
    {
        $result = $this->companyQueryBuilder
            ->createFetchAllActive($sortOrder)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    public function getCompaniesNeedingDMERUpdate(): ArrayCollection
    {
        $date = date_create_immutable('First day of this month');
        $results = $this->companyQueryBuilder
            ->createFetchAllActive()
            ->leftJoin('co.reports', 'r')
            ->andWhere('r.lastRun < :date OR r.lastRun IS NULL')
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($results);
    }
}
