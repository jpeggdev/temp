<?php

namespace App\Repository;

use App\Entity\EventInstructor;
use App\Module\EventRegistration\Feature\EventInstructorManagement\DTO\Request\SearchEventInstructorsRequestDTO;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventInstructor>
 */
class EventInstructorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventInstructor::class);
    }

    public function findOneByEmail(string $email): ?EventInstructor
    {
        return $this->createQueryBuilder('ei')
            ->andWhere('ei.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneById(int $id): ?EventInstructor
    {
        return $this->find($id);
    }

    /**
     * @return EventInstructor[]
     */
    public function findInstructorsByQuery(SearchEventInstructorsRequestDTO $dto): array
    {
        $qb = $this->createQueryBuilder('ei');
        $this->applyFilters($qb, $dto);

        $qb->orderBy('ei.'.$dto->sortBy, $dto->sortOrder)
            ->setFirstResult(($dto->page - 1) * $dto->pageSize)
            ->setMaxResults($dto->pageSize);

        return $qb->getQuery()->getResult();
    }

    public function countInstructorsByQuery(SearchEventInstructorsRequestDTO $dto): int
    {
        $qb = $this->createQueryBuilder('ei')
            ->select('COUNT(ei.id)');

        $this->applyFilters($qb, $dto);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function applyFilters(QueryBuilder $qb, SearchEventInstructorsRequestDTO $dto): void
    {
        if ($dto->searchTerm) {
            $lowerSearchTerm = '%'.strtolower($dto->searchTerm).'%';

            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('LOWER(ei.name)', ':searchTerm'),
                    $qb->expr()->like('LOWER(ei.email)', ':searchTerm'),
                    $qb->expr()->like('LOWER(ei.phone)', ':searchTerm')
                )
            )
                ->setParameter('searchTerm', $lowerSearchTerm);
        }
    }
}
