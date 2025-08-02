<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\CompanyDTO;
use App\DTO\Request\Company\CompanyQueryDTO;
use App\DTO\Response\Company\CompanyListResponseDTO;
use App\DTO\StochasticRosterDTO;
use App\Entity\Company;
use App\Exception\NotFoundException\CompanyNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Company>
 */
class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }

    public function save(Company $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function ingestCompanyFromAccountApplication(
        CompanyDTO $company,
    ): void {
        $existingCompanyEntity = $this->findOneBy(['intacctId' => $company->account]);
        if ($existingCompanyEntity) {
            $entityToPersist = $existingCompanyEntity;
        } else {
            $entityToPersist = new Company();
        }
        $this->setCompanyFieldsFromAccount($entityToPersist, $company);
        $this->getEntityManager()->persist($entityToPersist);
        $this->getEntityManager()->flush();
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function ingestCompanyFromStochasticRoster(
        StochasticRosterDTO $company,
    ): void {
        $existingCompanyEntity = $this->findOneBy(['intacctId' => $company->intacctId]);
        if ($existingCompanyEntity) {
            $entityToPersist = $existingCompanyEntity;
        } else {
            $entityToPersist = new Company();
        }
        $this->setCompanyFieldsFromStochastic($entityToPersist, $company);
        $this->getEntityManager()->persist($entityToPersist);
        $this->getEntityManager()->flush();
    }

    /**
     * Get paginated and sorted list of companies, including employee-related companies.
     *
     * @param array       $employeeCompanyIds  list of company IDs related to the employee
     * @param bool        $includeAllCompanies if true, fetch all companies
     * @param string|null $search              search term for company name
     *
     * @return array<Company>
     */
    public function findAllCompaniesWithEmployeeRelatedQueryBuilder(
        array $employeeCompanyIds,
        int $page = 1,
        int $limit = 100,
        bool $includeAllCompanies = false,
        ?string $search = null,
        ?bool $marketingEnabled = null,
    ): array {
        $offset = ($page - 1) * $limit;

        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.companyName', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if (null !== $search) {
            $qb->andWhere('LOWER(c.companyName) LIKE LOWER(:search) OR LOWER(c.intacctId) LIKE LOWER(:search)')
                ->setParameter('search', '%'.strtolower($search).'%');
        }

        if ($includeAllCompanies) {
            return $qb->getQuery()->getResult();
        }

        if ($marketingEnabled) {
            $qb->andWhere('c.marketingEnabled = :marketingEnabled')
                ->setParameter('marketingEnabled', true);
        }

        $qb->orWhere('c.id IN (:employeeCompanyIds)')
            ->setParameter('employeeCompanyIds', $employeeCompanyIds);

        return $qb->getQuery()->getResult();
    }

    /**
     * Finds companies by the query parameters and filters based on active company.
     *
     * @return Company[]
     */
    public function findCompaniesByQuery(CompanyQueryDTO $queryDto): array
    {
        $qb = $this->createQueryBuilder('c')
            ->setMaxResults($queryDto->pageSize)
            ->setFirstResult(($queryDto->page - 1) * $queryDto->pageSize)
            ->orderBy('c.'.$queryDto->sortBy, $queryDto->sortOrder);

        $this->applyFilters($qb, $queryDto);

        return $qb->getQuery()->getResult();
    }

    /**
     * Gets the total count of companies based on the query parameters and active company.
     */
    public function getTotalCount(CompanyQueryDTO $queryDto): int
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)');

        $this->applyFilters($qb, $queryDto);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Finds a company by matching the domain extracted from websiteUrl with the given email domain.
     *
     * @param string $emailDomain the domain extracted from the sender's email
     *
     * @return Company|null the matched company or null if not found
     *
     * @throws NonUniqueResultException
     */
    public function findOneByEmailDomain(string $emailDomain): ?Company
    {
        $qb = $this->createQueryBuilder('c');

        $qb->where('LOWER(REPLACE(REPLACE(c.websiteUrl, \'https://\', \'\'), \'http://\', \'\')) LIKE :domain')
            ->setParameter('domain', '%'.$emailDomain);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Applies filters from the CompanyQueryDTO to the query builder.
     */
    private function applyFilters(QueryBuilder $qb, CompanyQueryDTO $queryDto): void
    {
        if ($queryDto->searchTerm) {
            $qb->andWhere('LOWER(c.companyName) LIKE LOWER(:searchTerm)')
                ->setParameter('searchTerm', '%'.strtolower($queryDto->searchTerm).'%');
        }
    }

    /**
     * @throws \DateMalformedStringException
     */
    private function setCompanyFieldsFromAccount(
        Company $companyEntity,
        CompanyDTO $company,
    ): void {
        $companyEntity->setCompanyName($company->company);
        $companyEntity->setIntacctId($company->account);
        $companyEntity->setCreatedAt(new \DateTime($company->start));
        $companyEntity->setUpdatedAt(new \DateTime());
    }

    /**
     * @throws \DateMalformedStringException
     */
    private function setCompanyFieldsFromStochastic(
        Company $entityToPersist,
        StochasticRosterDTO $company,
    ): void {
        $entityToPersist->setCompanyName($company->account);
        $entityToPersist->setIntacctId($company->intacctId);
        $entityToPersist->setUpdatedAt(new \DateTime());
        $entityToPersist->setMarketingEnabled(true);
    }

    public function findOneByIdentifier(string $identifier): ?Company
    {
        return $this->findOneBy(['intacctId' => $identifier]);
    }

    public function findOneByIdentifierOrFail(string $identifier): Company
    {
        $result = $this->findOneByIdentifier($identifier);

        if (!$result) {
            throw new CompanyNotFoundException('Company not found.');
        }

        return $result;
    }

    public function getCompanyFromDTO(CompanyListResponseDTO $dto): ?Company
    {
        return $this->findOneByIdentifier(
            $dto->intacctId
        );
    }

    public function saveCompany(Company $company): void
    {
        if ($company->hasWebsiteUrl()) {
            $this->checkDuplicateWebsiteUrl($company);
        }

        $this->save($company, true);
    }

    private function checkDuplicateWebsiteUrl(Company $company): void
    {
        $otherCompanyWithSameWebsite = $this->findOneBy(
            [
                'websiteUrl' => $company->getWebsiteUrl(),
            ]
        );

        if (
            $otherCompanyWithSameWebsite
            && $otherCompanyWithSameWebsite->getId() !== $company->getId()
            && $otherCompanyWithSameWebsite->getIntacctId() !== $company->getIntacctId()
        ) {
            $company->setWebsiteUrl(
                $company->getWebsiteUrl().'/?other='.$otherCompanyWithSameWebsite->getIntacctId().'&ts='.time()
            );
        }
    }

    public function findAllWithIntacctId(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.intacctId IS NOT NULL')
            ->andWhere('c.intacctId != :empty')
            ->setParameter('empty', '')
            ->getQuery()
            ->getResult()
        ;
    }
}
