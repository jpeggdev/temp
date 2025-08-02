<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\Request\Event\GetEventSearchResultsQueryDTO;
use App\DTO\Request\EventCategory\EventCategoryQueryDTO;
use App\Entity\Employee;
use App\Entity\EventCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventCategory>
 */
class EventCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventCategory::class);
    }

    public function remove(EventCategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     *  Key method for building event counts per category,
     *  matching user's filter criteria (searchTerm, eventType, etc.).
     */
    public function findAllWithFilteredEventCounts(
        GetEventSearchResultsQueryDTO $dto,
        ?Employee $employee = null,
    ): array {
        // Build subquery logic similarly to event types
        // "Count how many events belong to this category AND meet the filters."

        $subDql = <<<DQL
            SELECT COUNT(e2.id)
            FROM App\Entity\Event e2
            WHERE e2.isPublished = true
              AND e2.eventCategory = ec
        DQL;

        // If user typed something in search
        if (!empty($dto->searchTerm)) {
            $subDql .= <<<DQL
                AND TSMATCH(e2.searchVector, WEBCSEARCH_TO_TSQUERY('english', :searchTerm)) = true
            DQL;
        }

        // If user selected specific event types
        if (!empty($dto->eventTypeIds)) {
            $subDql .= <<<DQL
                AND e2.eventType IN (:typeIds)
            DQL;
        }

        // If user wants only favorites
        if ($dto->showFavorites && null !== $employee) {
            $subDql .= <<<DQL
                AND e2.id IN (
                  SELECT e3.id
                  FROM App\Entity\EventFavorite ef
                  JOIN ef.event e3
                  WHERE ef.employee = :employeeId
                )
            DQL;
        }

        // Build main query
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select([
            'ec.id AS id',
            'ec.name AS name',
            '('.$subDql.') AS eventCount',
        ])
            ->from(EventCategory::class, 'ec')
            ->orderBy('ec.name', 'ASC');

        // Bind parameters
        if (!empty($dto->searchTerm)) {
            $qb->setParameter('searchTerm', $dto->searchTerm);
        }

        if (!empty($dto->eventTypeIds)) {
            $qb->setParameter('typeIds', $dto->eventTypeIds);
        }

        if ($dto->showFavorites && null !== $employee) {
            $qb->setParameter('employeeId', $employee->getId());
        }

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @return EventCategory[]
     */
    public function findEventCategoriesByQuery(EventCategoryQueryDTO $queryDTO): array
    {
        $qb = $this->createBaseQueryBuilder();
        $this->applyFilters($qb, $queryDTO);
        $sortBy = $queryDTO->sortBy ?? 'name';
        $sortOrder = $queryDTO->sortOrder ?? 'ASC';
        $qb->orderBy('ec.'.$sortBy, $sortOrder);

        $qb->setFirstResult(($queryDTO->page - 1) * $queryDTO->pageSize)
            ->setMaxResults($queryDTO->pageSize);

        return $qb->getQuery()->getResult();
    }

    public function getTotalCount(EventCategoryQueryDTO $queryDTO): int
    {
        $qb = $this->createBaseQueryBuilder()
            ->select('COUNT(ec.id)');

        // Apply filters again for consistent count
        $this->applyFilters($qb, $queryDTO);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findOneByName(string $name): ?EventCategory
    {
        return $this->createQueryBuilder('ec')
            ->andWhere('ec.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(EventCategory $eventCategory, bool $flush = false): void
    {
        $this->getEntityManager()->persist($eventCategory);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    private function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('ec');
    }

    private function applyFilters(QueryBuilder $qb, EventCategoryQueryDTO $queryDTO): void
    {
        if ($queryDTO->searchTerm) {
            $qb->andWhere('LOWER(ec.name) LIKE LOWER(:searchTerm) OR LOWER(ec.description) LIKE LOWER(:searchTerm)')
                ->setParameter('searchTerm', '%'.strtolower($queryDTO->searchTerm).'%');
        }

        if (null !== $queryDTO->isActive) {
            $qb->andWhere('ec.isActive = :isActive')
                ->setParameter('isActive', $queryDTO->isActive);
        }
    }

    public function findActive(): array
    {
        return [];
    }

    public function findEventCategoryById(?int $eventCategoryId): ?EventCategory
    {
        return $this->findOneBy(['id' => $eventCategoryId]);
    }
}
