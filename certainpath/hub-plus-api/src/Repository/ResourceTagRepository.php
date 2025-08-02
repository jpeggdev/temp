<?php

namespace App\Repository;

use App\DTO\Request\ResourceTag\GetResourceTagsRequestDTO;
use App\Entity\ResourceTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ResourceTag>
 */
class ResourceTagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResourceTag::class);
    }

    public function save(ResourceTag $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByName(string $name): ?ResourceTag
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return ResourceTag[]
     */
    public function findTagsByQuery(GetResourceTagsRequestDTO $dto): array
    {
        $qb = $this->createQueryBuilder('t');
        $this->applyFilters($qb, $dto);
        $qb->orderBy('t.'.$dto->sortBy, $dto->sortOrder)
            ->setFirstResult(($dto->page - 1) * $dto->pageSize)
            ->setMaxResults($dto->pageSize);

        return $qb->getQuery()->getResult();
    }

    public function countTagsByQuery(GetResourceTagsRequestDTO $dto): int
    {
        $qb = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)');
        $this->applyFilters($qb, $dto);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function applyFilters(QueryBuilder $qb, GetResourceTagsRequestDTO $dto): void
    {
        if ($dto->name) {
            $qb->andWhere('LOWER(t.name) LIKE LOWER(:searchName)')
                ->setParameter('searchName', '%'.strtolower($dto->name).'%');
        }
    }
}
