<?php

namespace App\Repository;

use App\DTO\Domain\ProspectFilterRulesDTO;
use App\DTO\Query\Prospect\ProspectQueryDTO;
use App\Entity\Address;
use App\Entity\Company;
use App\Entity\Prospect;
use App\Exceptions\EntityValidationException;
use App\Exceptions\NotFoundException\ProspectFilterRuleNotFoundException;
use App\QueryBuilder\ProspectQueryBuilder;
use App\Services\PaginatorService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProspectRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly ValidatorInterface $validator,
        private readonly PaginatorService $paginator,
        private readonly ProspectQueryBuilder $prospectsQueryBuilder,
    ) {
        parent::__construct($registry, Prospect::class);
    }

    /**
     * @throws EntityValidationException
     */
    public function validate(Prospect $prospect): void
    {
        $errors = $this->validator->validate($prospect);
        if (count($errors) > 0) {
            $errorMessages = [];

            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            throw new EntityValidationException(implode(' ', $errorMessages));
        }
    }

    public function saveProspect(Prospect $prospect): Prospect
    {
        /** @var Prospect $saved */
        $saved = $this->save($prospect);
        return $saved;
    }

    public function fetchAllByCompanyId(
        int $companyId,
        $sortOrder = 'ASC'
    ): ArrayCollection {
        $results = $this->prospectsQueryBuilder
            ->createFetchAllByCompanyIdQueryBuilder($companyId, $sortOrder)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($results);
    }

    /**
     * @throws ProspectFilterRuleNotFoundException
     */
    public function fetchAllByProspectFilterRulesDTO(
        ProspectFilterRulesDTO $dto,
        string $sortOrder = 'ASC',
        int $limit = null,
        int $offset = null
    ): ArrayCollection {
        $dtoCopy = clone $dto;
        $dtoCopy->postalCodes = array_keys($dto->postalCodes) ?? [];

        $prospects = $this->prospectsQueryBuilder
            ->createFetchAllByProspectFilterRulesDTOQueryBuilder($dtoCopy, $sortOrder, $limit, $offset)
            ->getQuery()
            ->getResult();

        if ($dto->postalCodes) {
            $filteredProspects = [];

            /** @var Prospect $prospect */
            foreach ($prospects as $prospect) {
                /** @var Address $preferredAddress */
                $preferredAddress = $prospect->getPreferredAddress();
                $postalCodeShort = $preferredAddress ? $preferredAddress->getPostalCodeShort() : '';

                if (isset($dto->postalCodes[$postalCodeShort])) {
                    $postalCodeShortLimit = $dto->postalCodes[$postalCodeShort];
                    $postalCodeShortCount[$postalCodeShort] = $postalCodeShortCount[$postalCodeShort] ?? 0;

                    if ($postalCodeShortCount[$postalCodeShort] < $postalCodeShortLimit) {
                        $filteredProspects[] = $prospect;
                        $postalCodeShortCount[$postalCodeShort]++;
                    }
                }
            }

            return new ArrayCollection($filteredProspects);
        }

        return new ArrayCollection($prospects);
    }

    /**
     * @throws ProspectFilterRuleNotFoundException
     */
    public function getProspectsAggregatedData(
        ProspectFilterRulesDTO $dto
    ): array {
        return $this->prospectsQueryBuilder
            ->createGetProspectsAggregatedDataQueryBuilder($dto)
            ->getQuery()
            ->getResult();
    }

    public function getFetchAllByCompanyIdQuery(int $companyId, string $sortOrder = 'ASC'): Query
    {
        return $this->prospectsQueryBuilder
            ->createFetchAllByCompanyIdQueryBuilder($companyId, $sortOrder)
            ->getQuery();
    }

    public function paginateAllByCompanyId(
        int $id,
        int $page = 1,
        int $perPage = 10,
        string $sortOrder = 'ASC'
    ): array {
        $query = $this->getFetchAllByCompanyIdQuery($id, $sortOrder);
        return $this->paginator->paginate($query, $page, $perPage);
    }

    public function getProspectsForPostProcessingQuery(
        int $limit = 10,
        Company $contextCompany = null,
        string $sortOrder = 'ASC'
    ): Query {
        return $this->prospectsQueryBuilder
            ->createGetProspectsForPostProcessingQueryBuilder($limit, $sortOrder, $contextCompany)
            ->getQuery();
    }

    public function getProspectsPaginator(
        Company $company,
        int $offset
    ): Paginator {
        $query = $this->getFetchAllByCompanyIdQuery($company->getId())
            ->setMaxResults(self::RESULTS_PER_PAGE)
            ->setFirstResult($offset);

        return new Paginator($query);
    }

    public function fetchAllByBatchId(
        int $batchId,
        string $sortOrder = 'ASC'
    ): ArrayCollection {
        $result = $this->prospectsQueryBuilder
            ->createFetchAllByBatchIdQueryBuilder($batchId, $sortOrder)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    public function paginateAllByBatchId(
        int $batchId,
        int $page = 1,
        int $perPage = 10,
        string $sortOrder = 'ASC'
    ): array {
        $query = $this->prospectsQueryBuilder
            ->createFetchAllByBatchIdQueryBuilder($batchId, $sortOrder)
            ->getQuery();

        return $this->paginator->paginate($query, $page, $perPage);
    }

    /**
     * @return Prospect[]
     */
    public function findNonCustomerProspectsByQuery(ProspectQueryDTO $queryDto): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.company', 'c')
            ->leftJoin('p.customer', 'cu')
            ->addSelect('c')
            ->where('cu.id IS NULL')
            ->setMaxResults($queryDto->pageSize)
            ->setFirstResult(($queryDto->page - 1) * $queryDto->pageSize)
            ->orderBy('p.' . $queryDto->sortBy, $queryDto->sortOrder);

        $this->applyFilters($qb, $queryDto);

        return $qb->getQuery()->getResult();
    }

    public function getTotalCount(ProspectQueryDTO $queryDto): int
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.company', 'c')
            ->leftJoin('p.customer', 'cu')
            ->select('COUNT(p.id)')
            ->where('cu.id IS NULL');

        $this->applyFilters($qb, $queryDto);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @throws ProspectFilterRuleNotFoundException
     */
    public function getCountByProspectFilterRulesDTO(ProspectFilterRulesDTO $dto): int
    {
        return $this->prospectsQueryBuilder
            ->createGetCountByProspectFilterRulesDTOQueryBuilder($dto)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function fetchProspectCities(
        string $intacctId,
        ?int $page,
        int $pageSize = 10,
        string $sortBy = 'city',
        string $sortOrder = 'ASC',
    ): ArrayCollection {
        $firstResult = ($page - 1) * $pageSize;

        $result = $this->prospectsQueryBuilder
            ->prepareFetchProspectCitiesQueryBuilder($intacctId)
            ->setFirstResult($firstResult)
            ->setMaxResults($pageSize)
            ->orderBy($sortBy, $sortOrder)
            ->getQuery()
            ->getResult();

        $cities = array_column($result, 'city');
        $cities = array_values(array_filter($cities));

        return new ArrayCollection($cities);
    }

    public function findOneByIdOrFail(int $id): Prospect
    {
        $prospect = $this->find($id);
        if (!$prospect) {
            throw new EntityNotFoundException('Prospect not found');
        }

        return $prospect;
    }

    private function applyFilters(
        QueryBuilder $qb,
        ProspectQueryDTO $queryDto
    ): void {
        if ($queryDto->searchTerm) {
            $qb->andWhere('LOWER(p.fullName) LIKE LOWER(:searchTerm)')
                ->setParameter('searchTerm', '%' . strtolower($queryDto->searchTerm) . '%');
        }

        if ($queryDto->intacctId) {
            $qb->andWhere('c.identifier = :identifier')
                ->setParameter('identifier', $queryDto->intacctId);
        }
    }
}
