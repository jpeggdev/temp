<?php

namespace App\Repository;

use App\DTO\Request\ResourceCategory\GetResourceCategoriesRequestDTO;
use App\Entity\ResourceCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ResourceCategory>
 */
class ResourceCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResourceCategory::class);
    }

    public function save(ResourceCategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByName(string $name): ?ResourceCategory
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Returns an array of ResourceCategory entities based on the given query DTO.
     *
     * @return ResourceCategory[]
     */
    public function findCategoriesByQuery(GetResourceCategoriesRequestDTO $dto): array
    {
        $qb = $this->createQueryBuilder('c');

        $this->applyFilters($qb, $dto);

        $qb->orderBy('c.'.$dto->sortBy, $dto->sortOrder)
            ->setFirstResult(($dto->page - 1) * $dto->pageSize)
            ->setMaxResults($dto->pageSize);

        return $qb->getQuery()->getResult();
    }

    public function countCategoriesByQuery(GetResourceCategoriesRequestDTO $dto): int
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)');

        $this->applyFilters($qb, $dto);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function applyFilters(QueryBuilder $qb, GetResourceCategoriesRequestDTO $dto): void
    {
        if ($dto->name) {
            $qb->andWhere('LOWER(c.name) LIKE LOWER(:searchName)')
                ->setParameter('searchName', '%'.strtolower($dto->name).'%');
        }
    }
}
