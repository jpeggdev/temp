<?php

namespace App\Repository;

use App\DTO\Query\PaginationDTO;
use App\Entity\Timezone;
use App\Module\EventRegistration\Feature\Shared\Timezone\DTO\Query\GetTimezonesDTO;
use App\Module\EventRegistration\Feature\Shared\Timezone\Exception\TimezoneNotFoundException;
use App\QueryBuilder\TimezoneQueryBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Timezone>
 */
class TimezoneRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly TimezoneQueryBuilder $timezoneQueryBuilder,
    ) {
        parent::__construct($registry, Timezone::class);
    }

    public function getTotalCount(GetTimezonesDTO $queryDTO): int
    {
        return $this->timezoneQueryBuilder
            ->createGetCountByDTOQueryBuilder($queryDTO)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return ArrayCollection<int, Timezone>
     */
    public function findAllByDTO(
        GetTimezonesDTO $queryDTO,
        PaginationDTO $paginationDTO,
    ): ArrayCollection {
        $firstResult = ($paginationDTO->page - 1) * $paginationDTO->pageSize;
        $sortBy = TimezoneQueryBuilder::ALIAS.'.'.$paginationDTO->sortBy;

        $result = $this->timezoneQueryBuilder
            ->createFindAllByDTOQueryBuilder($queryDTO)
            ->setMaxResults($paginationDTO->pageSize)
            ->setFirstResult($firstResult)
            ->orderBy($sortBy, $paginationDTO->sortOrder)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    public function findOneById(int $id): ?Timezone
    {
        return $this->timezoneQueryBuilder
            ->createFindOneByIdQueryBuilder($id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByIdOrFail(int $id): Timezone
    {
        $result = $this->findOneById($id);

        if (!$result) {
            throw new TimezoneNotFoundException();
        }

        return $result;
    }
}
