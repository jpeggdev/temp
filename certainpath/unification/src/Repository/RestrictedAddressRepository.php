<?php

namespace App\Repository;

use App\DTO\Query\PaginationDTO;
use App\DTO\Request\Address\RestrictedAddressQueryDTO;
use App\Entity\RestrictedAddress;
use App\QueryBuilder\RestrictedAddressQueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

class RestrictedAddressRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly RestrictedAddressQueryBuilder $restrictedAddressQueryBuilder,
    ) {
        parent::__construct($registry, RestrictedAddress::class);
    }

    public function saveRestrictedAddress(RestrictedAddress $restrictedAddress): RestrictedAddress
    {
        /** @var RestrictedAddress $saved */
        $saved = $this->save($restrictedAddress);
        return $saved;
    }

    public function findOneByExternalId(string $externalId): ?RestrictedAddress
    {
        return $this->restrictedAddressQueryBuilder
            ->createFindOneByExternalIdQueryBuilder($externalId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countByRestrictedAddressQueryDTO(RestrictedAddressQueryDTO $queryDto): Query
    {
        return $this->restrictedAddressQueryBuilder
            ->createCountByRestrictedAddressQueryDTOQueryBuilder($queryDto)
            ->getQuery();
    }

    public function findByRestrictedAddressQueryDTO(
        RestrictedAddressQueryDTO $queryDto,
        PaginationDTO $paginationDto,
    ): Query {
        return $this->restrictedAddressQueryBuilder
            ->createFindByRestrictedAddressQueryDTOQueryBuilder($queryDto, $paginationDto)
            ->getQuery();
    }

    public function getRestrictedAddressesForPostProcessing(int $limit = 10): Query
    {
        return $this->restrictedAddressQueryBuilder
            ->createGetRestrictedAddressesForPostProcessing($limit)
            ->getQuery();
    }
}
