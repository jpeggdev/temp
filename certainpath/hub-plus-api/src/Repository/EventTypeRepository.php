<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\Request\Event\GetEventSearchResultsQueryDTO;
use App\DTO\Request\EventType\GetEventTypesRequestDTO;
use App\Entity\Employee;
use App\Entity\EventType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventType>
 */
class EventTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventType::class);
    }

    public function save(EventType $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(EventType $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Example: find one event type by name.
     */
    public function findOneByName(string $name): ?EventType
    {
        return $this->createQueryBuilder('et')
            ->andWhere('et.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Example method: find event types with pagination/filters from a DTO
     * (not directly related to your event-count logic, but left here
     * because it was in your original snippet).
     *
     * @return EventType[]
     */
    public function findEventTypesByQuery(GetEventTypesRequestDTO $dto): array
    {
        $qb = $this->createQueryBuilder('et');
        $this->applyFilters($qb, $dto);

        $qb->orderBy('et.'.$dto->sortBy, $dto->sortOrder)
            ->setFirstResult(($dto->page - 1) * $dto->pageSize)
            ->setMaxResults($dto->pageSize);

        return $qb->getQuery()->getResult();
    }

    public function countEventTypesByQuery(GetEventTypesRequestDTO $dto): int
    {
        $qb = $this->createQueryBuilder('et')
            ->select('COUNT(et.id)');
        $this->applyFilters($qb, $dto);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Return each EventType along with how many Events match the same filters
     * used by the user (searchTerm, categoryIds, etc.).
     *
     * This uses a subquery approach: "COUNT(e2.id) WHERE e2.eventType = et.id AND e2 meets all filters".
     */
    public function findAllWithFilteredEventCounts(
        GetEventSearchResultsQueryDTO $dto,
        ?Employee $employee = null,
    ): array {
        // Build sub-DQL for counting events that match the user's filter
        // for each "EventType" row in this repository.

        // Basic subquery
        $subDql = <<<DQL
            SELECT COUNT(e2.id)
            FROM App\Entity\Event e2
            WHERE e2.isPublished = true
              AND e2.eventType = et
        DQL;

        // Filter by searchTerm
        if (!empty($dto->searchTerm)) {
            $subDql .= <<<DQL
                AND TSMATCH(e2.searchVector, WEBCSEARCH_TO_TSQUERY('english', :searchTerm)) = true
            DQL;
        }

        // If user selected one or more categories
        if (!empty($dto->categoryIds)) {
            $subDql .= <<<DQL
                AND e2.eventCategory IN (:catIds)
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

        // We don't filter by eventType here,
        // because we *want counts for ALL event types* no matter what.
        // If you DO want to show only eventType rows that are in the user's selected set,
        // you'd do that differently.

        // Now build the main query: "Select each EventType, plus subDQL as eventCount"
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select([
            'et.id AS id',
            'et.name AS name',
            '('.$subDql.') AS eventCount',
        ])
            ->from(EventType::class, 'et')
            ->orderBy('et.name', 'ASC');

        // Bind parameters
        if (!empty($dto->searchTerm)) {
            $qb->setParameter('searchTerm', $dto->searchTerm);
        }

        if (!empty($dto->categoryIds)) {
            $qb->setParameter('catIds', $dto->categoryIds);
        }

        if ($dto->showFavorites && null !== $employee) {
            $qb->setParameter('employeeId', $employee->getId());
        }

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Apply filters for findEventTypesByQuery.
     * (Not directly related to the "counts" logic, but included from your snippet.).
     */
    private function applyFilters(QueryBuilder $qb, GetEventTypesRequestDTO $dto): void
    {
        if ($dto->name) {
            $qb->andWhere('LOWER(et.name) LIKE LOWER(:searchName)')
                ->setParameter('searchName', '%'.strtolower($dto->name).'%');
        }

        if (null !== $dto->isActive) {
            $qb->andWhere('et.isActive = :isActive')
                ->setParameter('isActive', $dto->isActive);
        }
    }
}
