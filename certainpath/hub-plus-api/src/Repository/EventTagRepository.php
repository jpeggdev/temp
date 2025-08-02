<?php

namespace App\Repository;

use App\DTO\Request\EventTag\GetEventTagsRequestDTO;
use App\Entity\EventTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventTag>
 */
class EventTagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventTag::class);
    }

    public function save(EventTag $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByName(string $name): ?EventTag
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return EventTag[]
     */
    public function findTagsByQuery(GetEventTagsRequestDTO $dto): array
    {
        $qb = $this->createQueryBuilder('t');
        $this->applyFilters($qb, $dto);

        $qb->orderBy('t.'.$dto->sortBy, $dto->sortOrder)
            ->setFirstResult(($dto->page - 1) * $dto->pageSize)
            ->setMaxResults($dto->pageSize);

        return $qb->getQuery()->getResult();
    }

    public function countTagsByQuery(GetEventTagsRequestDTO $dto): int
    {
        $qb = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)');
        $this->applyFilters($qb, $dto);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function applyFilters(QueryBuilder $qb, GetEventTagsRequestDTO $dto): void
    {
        if ($dto->name) {
            $qb->andWhere('LOWER(t.name) LIKE LOWER(:searchName)')
                ->setParameter('searchName', '%'.strtolower($dto->name).'%');
        }
    }
}
