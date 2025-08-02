<?php

namespace App\Repository;

use App\DTO\Query\PaginationDTO;
use App\DTO\Request\Address\AddressQueryDTO;
use App\Entity\Address;
use App\Entity\Customer;
use App\Exceptions\NotFoundException\AddressNotFoundException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

class AddressRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Address::class);
    }

    public function saveAddress(Address $address): Address
    {
        /** @var Address $saved */
        $saved = $this->save($address);
        return $saved;
    }

    public function createBaseQueryBuilder(): QueryBuilder
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('a')
            ->from(Address::class, 'a');
    }

    public function countByAddressQueryDTO(
        AddressQueryDTO $queryDto,
    ): Query {
        $qb = $this->createQueryBuilder('address')
            ->select('COUNT(address.id)')
            ->join('address.company', 'company');
        $this->applyFilters($qb, $queryDto);

        return $qb->getQuery();
    }

    public function findByAddressQueryDTO(
        AddressQueryDTO $queryDto,
        PaginationDTO $paginationDto,
    ): Query {
        $qb = $this->createQueryBuilder('address')
            ->setMaxResults($paginationDto->perPage)
            ->setFirstResult(($paginationDto->page - 1) * $paginationDto->perPage)
            ->join('address.company', 'company')
            ->orderBy('address.' . $queryDto->sortBy, $queryDto->sortOrder);
        $this->applyFilters($qb, $queryDto);

        return $qb->getQuery();
    }

    public function findOneByIdOrFail(int $id): Address
    {
        $address = $this->find($id);
        if (!$address) {
            throw new EntityNotFoundException('Address not found');
        }

        return $address;
    }

    private function applyFilters(QueryBuilder $qb, AddressQueryDTO $queryDto): void
    {
        if ($queryDto->companyId) {
            $qb->andWhere('company.id = :companyId')
                ->setParameter('companyId', $queryDto->companyId);
        }
        if ($queryDto->companyIdentifier) {
            $qb->andWhere('company.identifier = :companyIdentifier')
                ->setParameter('companyIdentifier', $queryDto->companyIdentifier);
        }
        if ($queryDto->customerId) {
            $qb->join('address.customers', 'customers')
                ->andWhere('customers.id = :customerId')
                ->setParameter('customerId', $queryDto->customerId);
        }
        if ($queryDto->prospectId) {
            $qb->join('address.prospects', 'prospects')
                ->andWhere('prospects.id = :prospectId')
                ->setParameter('prospectId', $queryDto->prospectId);
        }
        if ($queryDto->externalId) {
            $qb->andWhere('LOWER(address.externalId) LIKE LOWER(:searchTerm)')
                ->setParameter('searchTerm', $queryDto->externalId);
        }
        if ($queryDto->address1) {
            $qb->andWhere('LOWER(address.address1) LIKE LOWER(:searchTerm)')
                ->setParameter('searchTerm', $queryDto->address1);
        }
        if ($queryDto->address2) {
            $qb->andWhere('LOWER(address.address2) LIKE LOWER(:searchTerm)')
                ->setParameter('searchTerm', $queryDto->address2);
        }
        if ($queryDto->city) {
            $qb->andWhere('LOWER(address.city) LIKE LOWER(:searchTerm)')
                ->setParameter('searchTerm', $queryDto->city);
        }
        if ($queryDto->stateCode) {
            $qb->andWhere('LOWER(address.stateCode) LIKE LOWER(:searchTerm)')
                ->setParameter('searchTerm', $queryDto->stateCode);
        }
        if ($queryDto->postalCode) {
            $qb->andWhere('LOWER(address.postalCode) LIKE LOWER(:searchTerm)')
                ->setParameter('searchTerm', $queryDto->postalCode);
        }
        if ($queryDto->countryCode) {
            $qb->andWhere('LOWER(address.countryCode) LIKE LOWER(:searchTerm)')
                ->setParameter('searchTerm', $queryDto->countryCode);
        }
        if ($queryDto->isDoNotMail) {
            $qb->andWhere('address.isDoNotMail = :isDoNotMail')
                ->setParameter('isDoNotMail', filter_var(
                    $queryDto->isDoNotMail,
                    FILTER_VALIDATE_BOOL
                ));
        }
        if ($queryDto->isBusiness) {
            $qb->andWhere('address.isBusiness = :isBusiness')
                ->setParameter('isBusiness', filter_var(
                    $queryDto->isBusiness,
                    FILTER_VALIDATE_BOOL
                ));
        }
        if ($queryDto->isVacant) {
            $qb->andWhere('address.isVacant = :isVacant')
                ->setParameter('isVacant', filter_var(
                    $queryDto->isVacant,
                    FILTER_VALIDATE_BOOL
                ));
        }
        if ($queryDto->isVerified) {
            $qb->andWhere('address.isVerified = :isVerified')
                ->setParameter('isVerified', filter_var(
                    $queryDto->isVerified,
                    FILTER_VALIDATE_BOOL
                ));
        }
    }

    public function fetchDistinctCities(
        ?int $page,
        int $pageSize = 10,
        string $sortBy = 'city',
        string $sortOrder = 'ASC',
        ?string $intacctId = null,
        ?string $searchTerm = null
    ): ArrayCollection {
        $firstResult = ($page - 1) * $pageSize;

        $queryBuilder = $this->getEntityManager()
            ->createQueryBuilder()
            ->select("DISTINCT LOWER(TRIM(a.city)) AS city")
            ->from(Address::class, 'a')
            ->andWhere('TRIM(a.city) != :empty')
            ->setParameter('empty', '');

        if ($intacctId) {
            $queryBuilder
                ->innerJoin('a.company', 'co')
                ->andWhere('co.identifier = :intacctId')
                ->setParameter('intacctId', $intacctId);
        }

        if ($searchTerm) {
            $queryBuilder
                ->andWhere('LOWER(TRIM(a.city)) LIKE LOWER(:searchTerm)')
                ->setParameter('searchTerm', '%' . trim($searchTerm) . '%');
        }

        $result = $queryBuilder
            ->setFirstResult($firstResult)
            ->setMaxResults($pageSize)
            ->orderBy($sortBy, $sortOrder)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    public function findOneByExternalId(string $externalId): ?Address
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder = $this->applyExternalIdFilter($queryBuilder, [$externalId]);

        return $queryBuilder
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws AddressNotFoundException
     */
    public function findOneByExternalIdOrFail(string $externalId): Address
    {
        $result = $this->findOneByExternalId($externalId);

        if (!$result) {
            throw new AddressNotFoundException();
        }

        return $result;
    }

    public function findAllByExternalIds(array $externalIds): ArrayCollection
    {
        if (empty($externalIds)) {
            return new ArrayCollection();
        }

        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder = $this->applyExternalIdFilter($queryBuilder, $externalIds);
        $result = $queryBuilder->getQuery()->getResult();

        return new ArrayCollection($result);
    }

    private function applyExternalIdFilter(
        QueryBuilder $queryBuilder,
        array $externalIds
    ): QueryBuilder {
        return $queryBuilder
            ->andWhere('a.externalId IN (:externalIds)')
            ->setParameter('externalIds', $externalIds);
    }
}
