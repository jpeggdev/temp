<?php

declare(strict_types=1);

namespace App\Repository\EventSession;

use App\Entity\Event;
use App\Entity\EventSession;
use App\Exception\EventSessionNotFoundException;
use App\Module\EventRegistration\Feature\EventSessionManagement\DTO\Request\EventSessionLookupRequestDTO;
use App\Module\EventRegistration\Feature\EventSessionManagement\DTO\Request\GetEventSessionsRequestDTO;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventSession>
 */
class EventSessionRepository extends ServiceEntityRepository
{
    use EventSessionQueryBuilderTrait;

    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, EventSession::class);
    }

    public function save(EventSession $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function remove(EventSession $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findEventSessionById(int $id): ?EventSession
    {
        return $this->find($id);
    }

    public function findOneById(int $sessionId): ?EventSession
    {
        $qb = $this->createQueryBuilder('es');
        $this->applyIdFilter($qb, $sessionId);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findOneByUuid(string $uuid): ?EventSession
    {
        $qb = $this->createQueryBuilder('es');
        $this->applyUuidFilter($qb, $uuid);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findOneByIdOrFail(int $sessionId): EventSession
    {
        $result = $this->findOneById($sessionId);
        if (null === $result) {
            throw new EventSessionNotFoundException();
        }

        return $result;
    }

    public function findSessionsByQuery(GetEventSessionsRequestDTO $dto): array
    {
        $qb = $this->createQueryBuilder('es');
        $this->applyPaginationAndSorting(
            $qb,
            $dto->page ?? 1,
            $dto->pageSize,
            $dto->sortBy,
            $dto->sortOrder,
            'startDate',
            'asc'
        )->applyEventUuidFilter($qb, $dto->eventUuid)
            ->applyIsPublishedFilter($qb, $dto->isPublished);

        return $qb->getQuery()->getResult();
    }

    public function getTotalCount(GetEventSessionsRequestDTO $dto): int
    {
        $qb = $this->createQueryBuilder('es')
            ->select('COUNT(es.id)');

        $this->applyEventUuidFilter($qb, $dto->eventUuid)
            ->applyIsPublishedFilter($qb, $dto->isPublished);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findSessionsByLookup(EventSessionLookupRequestDTO $dto): array
    {
        $qb = $this->createQueryBuilder('es');

        $this->applyPaginationAndSorting(
            $qb,
            $dto->page ?? 1,
            $dto->pageSize,
            $dto->sortBy,
            $dto->sortOrder
        )
            ->applyEventIdFilter($qb, $dto->eventId)
            ->applyIsPublishedFilter($qb, $dto->isPublished)
            ->applySearchTermFilter($qb, $dto->searchTerm);

        return $qb->getQuery()->getResult();
    }

    public function getLookupTotalCount(EventSessionLookupRequestDTO $dto): int
    {
        $qb = $this->createQueryBuilder('es')
            ->select('COUNT(es.id)');

        $this->applyEventIdFilter($qb, $dto->eventId)
            ->applyIsPublishedFilter($qb, $dto->isPublished)
            ->applySearchTermFilter($qb, $dto->searchTerm);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Returns all published sessions for a given Event whose endDate is still in the future,
     * ordered by startDate DESC.
     *
     * @return EventSession[]
     */
    public function findFuturePublishedSessionsForEvent(Event $event, \DateTimeImmutable $nowUtc): array
    {
        return $this->createQueryBuilder('es')
            ->where('es.event = :event')
            ->andWhere('es.isPublished = true')
            ->andWhere('es.endDate > :now')
            ->orderBy('es.startDate', 'DESC')
            ->setParameter('event', $event)
            ->setParameter('now', $nowUtc)
            ->getQuery()
            ->getResult();
    }
}
