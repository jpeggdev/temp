<?php

namespace App\Repository;

use App\Entity\EventDiscount;
use App\Module\EventRegistration\Feature\EventDiscountManagement\DTO\Query\GetEventDiscountsDTO;
use App\Module\EventRegistration\Feature\EventDiscountManagement\Exception\EventDiscountNotFoundException;
use App\QueryBuilder\EventDiscountQueryBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventDiscount>
 */
class EventDiscountRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EventDiscountQueryBuilder $eventDiscountQueryBuilder,
    ) {
        parent::__construct($registry, EventDiscount::class);
    }

    public function softDelete(EventDiscount $entity, bool $flush = false): void
    {
        $entity
            ->setIsActive(false)
            ->setDeletedAt(new \DateTimeImmutable('now'));

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function save(EventDiscount $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneById(int $id): ?EventDiscount
    {
        return $this->eventDiscountQueryBuilder
            ->createFindOneByIdQueryBuilder($id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByIdOrFail(int $id): EventDiscount
    {
        $result = $this->findOneById($id);

        if (!$result) {
            throw new EventDiscountNotFoundException();
        }

        return $result;
    }

    /**
     * @return ArrayCollection<int, EventDiscount>
     */
    public function findAllByDTO(GetEventDiscountsDTO $queryDto): ArrayCollection
    {
        $firstResult = ($queryDto->page - 1) * $queryDto->pageSize;
        $sortBy = EventDiscountQueryBuilder::ALIAS.'.'.$queryDto->sortBy;

        $result = $this->eventDiscountQueryBuilder
            ->createFindAllByDTOQueryBuilder($queryDto)
            ->setMaxResults($queryDto->pageSize)
            ->setFirstResult($firstResult)
            ->orderBy($sortBy, $queryDto->sortOrder)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    public function getCountByDTO(GetEventDiscountsDTO $queryDto): int
    {
        return $this->eventDiscountQueryBuilder
            ->createGetCountByDTOQueryBuilder($queryDto)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findOneByCode(string $code): ?EventDiscount
    {
        return $this->createQueryBuilder('ed')
            ->andWhere('ed.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
