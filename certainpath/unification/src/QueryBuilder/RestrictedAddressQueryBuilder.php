<?php

namespace App\QueryBuilder;

use App\DTO\Query\PaginationDTO;
use App\DTO\Request\Address\RestrictedAddressQueryDTO;
use App\Entity\RestrictedAddress;
use Doctrine\ORM\QueryBuilder;

readonly class RestrictedAddressQueryBuilder extends AbstractQueryBuilder
{
    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('ra')
            ->from(RestrictedAddress::class, 'ra');
    }

    public function createFindOneByExternalIdQueryBuilder(string $externalId): QueryBuilder
    {
        $queryBuilder = $this->createBaseQueryBuilder();
        return $this->applyExternalIdFilter($queryBuilder, $externalId);
    }

    private function initCountByRestrictedAddressQueryDTOQueryBuilder(): QueryBuilder
    {
        return $this->createBaseQueryBuilder()
            ->select('COUNT(ra.id)');
    }

    public function createCountByRestrictedAddressQueryDTOQueryBuilder(
        RestrictedAddressQueryDTO $queryDto,
    ): QueryBuilder {
        $qb = $this->createBaseQueryBuilder()
            ->select('COUNT(ra.id)');

        $this->applyFilters($qb, $queryDto);

        return $qb;
    }

    public function createFindByRestrictedAddressQueryDTOQueryBuilder(
        RestrictedAddressQueryDTO $queryDto,
        PaginationDTO $paginationDto
    ): QueryBuilder {
        $qb = $this->createBaseQueryBuilder()
            ->setMaxResults($paginationDto->perPage)
            ->setFirstResult(($paginationDto->page - 1) * $paginationDto->perPage)
            ->orderBy('ra.' . $queryDto->sortBy, $queryDto->sortOrder);

        $this->applyFilters($qb, $queryDto);

        return $qb;
    }

    public function createGetRestrictedAddressesForPostProcessing(int $limit = 10): QueryBuilder
    {
        $qb = $this->createBaseQueryBuilder()
            ->setMaxResults($limit);

        return $this->applyProcessedAtNullFilter($qb);
    }

    private function applyExternalIdFilter(QueryBuilder $qb, string $externalId): QueryBuilder
    {
        return $qb
            ->andWhere('ra.externalId = :externalId')
            ->setParameter('externalId', $externalId);
    }

    private function applyProcessedAtNullFilter(QueryBuilder $qb): QueryBuilder
    {
        return $qb->andWhere('ra.processedAt IS NULL');
    }

    private function applyFilters(QueryBuilder $qb, RestrictedAddressQueryDTO $queryDto): void
    {
        if ($queryDto->externalId) {
            $qb->andWhere('LOWER(ra.externalId) LIKE LOWER(:extIdParam)')
                ->setParameter('extIdParam', '%' . strtolower($queryDto->externalId) . '%');
        }
        if ($queryDto->address1) {
            $qb->andWhere('LOWER(ra.address1) LIKE LOWER(:addr1Param)')
                ->setParameter('addr1Param', '%' . strtolower($queryDto->address1) . '%');
        }
        if ($queryDto->address2) {
            $qb->andWhere('LOWER(ra.address2) LIKE LOWER(:addr2Param)')
                ->setParameter('addr2Param', '%' . strtolower($queryDto->address2) . '%');
        }
        if ($queryDto->city) {
            $qb->andWhere('LOWER(ra.city) LIKE LOWER(:cityParam)')
                ->setParameter('cityParam', '%' . strtolower($queryDto->city) . '%');
        }
        if ($queryDto->stateCode) {
            $qb->andWhere('LOWER(ra.stateCode) LIKE LOWER(:stateCodeParam)')
                ->setParameter('stateCodeParam', '%' . strtolower($queryDto->stateCode) . '%');
        }
        if ($queryDto->postalCode) {
            $qb->andWhere('LOWER(ra.postalCode) LIKE LOWER(:postalCodeParam)')
                ->setParameter('postalCodeParam', '%' . strtolower($queryDto->postalCode) . '%');
        }
        if ($queryDto->countryCode) {
            $qb->andWhere('LOWER(ra.countryCode) LIKE LOWER(:countryCodeParam)')
                ->setParameter('countryCodeParam', '%' . strtolower($queryDto->countryCode) . '%');
        }

        if ($queryDto->isBusiness) {
            $qb->andWhere('ra.isBusiness = :isBusiness')
                ->setParameter('isBusiness', filter_var($queryDto->isBusiness, FILTER_VALIDATE_BOOL));
        }
        if ($queryDto->isVacant) {
            $qb->andWhere('ra.isVacant = :isVacant')
                ->setParameter('isVacant', filter_var($queryDto->isVacant, FILTER_VALIDATE_BOOL));
        }

        if ($queryDto->isVerified) {
            $isVerifiedBool = filter_var($queryDto->isVerified, FILTER_VALIDATE_BOOL);
            if ($isVerifiedBool === true) {
                $qb->andWhere('ra.verifiedAt IS NOT NULL');
            } else {
                $qb->andWhere('ra.verifiedAt IS NULL');
            }
        }
    }
}
