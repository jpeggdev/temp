<?php

declare(strict_types=1);

namespace App\Repository\EventRepository;

use App\DTO\Request\Event\EventLookupRequestDTO;
use App\DTO\Request\Event\GetEventSearchResultsQueryDTO;
use App\DTO\Request\Event\GetEventsRequestDTO;
use App\Entity\Employee;
use App\Entity\Event;
use App\Entity\EventCategory;
use App\Exception\EventNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    use EventQueryBuilderTrait;

    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, Event::class);
    }

    public function save(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findEventById(int $id): ?Event
    {
        return $this->find($id);
    }

    public function countByEventCategory(EventCategory $eventCategory): int
    {
        return $this->count(['eventCategory' => $eventCategory]);
    }

    public function findOneById(int $id): ?Event
    {
        $qb = $this->createQueryBuilder('e');
        $this->applyIdFilter($qb, $id);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findOneByUuid(string $uuid): ?Event
    {
        $qb = $this->createQueryBuilder('e');
        $this->applyUuidFilter($qb, $uuid);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findOneByIdOrFail(int $id): Event
    {
        $event = $this->findOneById($id);
        if (null === $event) {
            throw new EventNotFoundException();
        }

        return $event;
    }

    public function findPublishedEventsByQuery(GetEventSearchResultsQueryDTO $dto, ?Employee $employee = null): array
    {
        $qb = $this->createQueryBuilder('e')
            ->select('DISTINCT e')
            ->andWhere('e.isPublished = true')
            ->leftJoin('e.eventSessions', 's');

        $this->applyPaginationAndSorting(
            $qb,
            $dto->page ?? 1,
            $dto->pageSize,
            $dto->sortBy,
            $dto->sortOrder,
        )->applyPublishedSearchFilters($qb, $dto, $employee);

        return $qb->getQuery()->getResult();
    }

    public function getPublishedTotalCount(GetEventSearchResultsQueryDTO $dto, ?Employee $employee = null): int
    {
        $qb = $this->createQueryBuilder('e')
            ->select('COUNT(DISTINCT e.id)')
            ->andWhere('e.isPublished = true')
            ->leftJoin('e.eventSessions', 's');

        $this->applyPublishedSearchFilters($qb, $dto, $employee);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function applyPublishedSearchFilters(QueryBuilder $qb, GetEventSearchResultsQueryDTO $dto, ?Employee $employee): static
    {
        $this->applySearchTermFilter($qb, $dto->searchTerm);
        $this->applyTradeIdsFilter($qb, $dto->tradeIds);
        $this->applyCategoryIdsFilter($qb, $dto->categoryIds);
        $this->applyEmployeeRoleIdsFilter($qb, $dto->employeeRoleIds);
        $this->applyTagIdsFilter($qb, $dto->tagIds);
        $this->applyEventTypeIdsFilter($qb, $dto->eventTypeIds);

        if ($dto->showFavorites && null !== $employee) {
            $this->applyShowFavoritesFilter($qb, $employee);
        }

        if ($dto->onlyPastEvents) {
            $qb->andWhere('s.endDate < :now');
            $qb->setParameter('now', new \DateTimeImmutable());
        }

        if ($dto->startDate) {
            $qb->andWhere('s.startDate >= :startDate');
            $qb->setParameter('startDate', new \DateTimeImmutable($dto->startDate));
        }

        if ($dto->endDate) {
            $qb->andWhere('s.startDate <= :endDate');
            $qb->setParameter('endDate', new \DateTimeImmutable($dto->endDate));
        }

        return $this;
    }

    public function findEventsByQuery(GetEventsRequestDTO $dto): array
    {
        $qb = $this->createQueryBuilder('e');
        $this->applyPaginationAndSorting(
            $qb,
            $dto->page ?? 1,
            $dto->pageSize,
            $dto->sortBy,
            $dto->sortOrder,
            'eventName',
            'asc'
        )->applyUnrestrictedEventFilters($qb, $dto);

        return $qb->getQuery()->getResult();
    }

    public function getTotalCount(GetEventsRequestDTO $dto): int
    {
        $qb = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)');

        $this->applyUnrestrictedEventFilters($qb, $dto);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function applyUnrestrictedEventFilters(QueryBuilder $qb, GetEventsRequestDTO $dto): static
    {
        $this->applySearchTermFilter($qb, $dto->searchTerm)
            ->applyTradeIdsFilter($qb, $dto->tradeIds)
            ->applyCategoryIdsFilter($qb, $dto->categoryIds)
            ->applyEmployeeRoleIdsFilter($qb, $dto->employeeRoleIds)
            ->applyTagIdsFilter($qb, $dto->tagIds)
            ->applyEventTypeIdsFilter($qb, $dto->eventTypeIds);

        return $this;
    }

    public function findEventsByLookup(EventLookupRequestDTO $dto): array
    {
        $qb = $this->createQueryBuilder('e');
        $this->applyPaginationAndSorting(
            $qb,
            $dto->page ?? 1,
            $dto->pageSize,
            $dto->sortBy,
            $dto->sortOrder
        )->applyLookupFilters($qb, $dto);

        return $qb->getQuery()->getResult();
    }

    public function getLookupTotalCount(EventLookupRequestDTO $dto): int
    {
        $qb = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)');

        $this->applyLookupFilters($qb, $dto);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function applyLookupFilters(QueryBuilder $qb, EventLookupRequestDTO $dto): static
    {
        $this->applySearchTermFilter($qb, $dto->searchTerm);

        return $this;
    }
}
