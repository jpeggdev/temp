<?php

namespace App\Repository;

use App\DTO\Query\PaginationDTO;
use App\Entity\EventVenue;
use App\Module\EventRegistration\Feature\EventVenueManagement\DTO\Query\EventVenueLookupQueryDTO;
use App\Module\EventRegistration\Feature\EventVenueManagement\DTO\Query\GetEventVenuesDTO;
use App\Module\EventRegistration\Feature\EventVenueManagement\Exception\EventVenueNotFoundException;
use App\QueryBuilder\EventVenueQueryBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventVenue>
 */
class EventVenueRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EventVenueQueryBuilder $eventVenueQueryBuilder,
    ) {
        parent::__construct($registry, EventVenue::class);
    }

    public function save(EventVenue $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function softDelete(EventVenue $entity, bool $flush = false): void
    {
        $entity
            ->setIsActive(false)
            ->setDeletedAt(new \DateTimeImmutable('now'));

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneById(int $id): ?EventVenue
    {
        return $this->eventVenueQueryBuilder
            ->createFindOneByIdQueryBuilder($id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByIdOrFail(int $id): EventVenue
    {
        $result = $this->findOneById($id);

        if (!$result) {
            throw new EventVenueNotFoundException();
        }

        return $result;
    }

    /**
     * @return ArrayCollection<int, EventVenue>
     */
    public function findAllByDTO(
        GetEventVenuesDTO $queryDto,
        PaginationDTO $paginationDTO,
    ): ArrayCollection {
        $firstResult = ($paginationDTO->page - 1) * $paginationDTO->pageSize;
        $sortBy = EventVenueQueryBuilder::ALIAS.'.'.$paginationDTO->sortBy;

        $result = $this->eventVenueQueryBuilder
            ->createFindAllByDTOQueryBuilder($queryDto)
            ->setMaxResults($paginationDTO->pageSize)
            ->setFirstResult($firstResult)
            ->orderBy($sortBy, $paginationDTO->sortOrder)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    public function getCountByDTO(GetEventVenuesDTO $queryDto): int
    {
        return $this->eventVenueQueryBuilder
            ->createGetCountByDTOQueryBuilder($queryDto)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Retrieves EventVenue objects matching lookup criteria (searchTerm, isActive),
     * and applies pagination & sorting.
     *
     * @return EventVenue[]
     */
    public function findVenuesByLookup(EventVenueLookupQueryDTO $dto): array
    {
        $qb = $this->createQueryBuilder('ev');

        if (null !== $dto->isActive) {
            $qb->andWhere('ev.isActive = :isActive')
                ->setParameter('isActive', $dto->isActive);
        }

        if ($dto->searchTerm) {
            $qb->andWhere('LOWER(ev.name) LIKE :searchTerm')
                ->setParameter('searchTerm', '%'.mb_strtolower($dto->searchTerm).'%');
        }

        $sortBy = $dto->sortBy ?: 'name';
        $sortOrder = $dto->sortOrder ?: 'asc';
        $qb->orderBy('ev.'.$sortBy, $sortOrder);

        $page = $dto->page ?: 1;
        $pageSize = $dto->pageSize ?: 10;
        $offset = ($page - 1) * $pageSize;
        $qb->setMaxResults($pageSize)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    /**
     * Counts how many EventVenue objects match the same filters (searchTerm, isActive).
     */
    public function getLookupTotalCount(EventVenueLookupQueryDTO $dto): int
    {
        $qb = $this->createQueryBuilder('ev')
            ->select('COUNT(ev.id)');

        if (null !== $dto->isActive) {
            $qb->andWhere('ev.isActive = :isActive')
                ->setParameter('isActive', $dto->isActive);
        }

        if ($dto->searchTerm) {
            $qb->andWhere('LOWER(ev.name) LIKE :searchTerm')
                ->setParameter('searchTerm', '%'.mb_strtolower($dto->searchTerm).'%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
