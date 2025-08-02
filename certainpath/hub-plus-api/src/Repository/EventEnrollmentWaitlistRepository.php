<?php

namespace App\Repository;

use App\Entity\EventEnrollmentWaitlist;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventWaitlist\DTO\Request\EventWaitlistItemsQueryDTO;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventEnrollmentWaitlist>
 */
class EventEnrollmentWaitlistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventEnrollmentWaitlist::class);
    }

    public function save(EventEnrollmentWaitlist $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getMaxWaitlistPosition(EventSession $session): ?int
    {
        $qb = $this->createQueryBuilder('w')
            ->select('MAX(w.waitlistPosition)')
            ->where('w.eventSession = :session')
            ->andWhere('w.promotedAt IS NULL')
            ->setParameter('session', $session);

        $result = $qb->getQuery()->getSingleScalarResult();

        return null !== $result ? (int) $result : null;
    }

    public function findOneByEventSessionAndEmployee(int $eventSessionId, int $employeeId): ?EventEnrollmentWaitlist
    {
        return $this->createQueryBuilder('w')
            ->where('w.eventSession = :sessionId')
            ->andWhere('w.employee = :employeeId')
            ->setParameter('sessionId', $eventSessionId)
            ->setParameter('employeeId', $employeeId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneByEventSessionAndEmail(int $eventSessionId, string $email): ?EventEnrollmentWaitlist
    {
        return $this->createQueryBuilder('w')
            ->where('w.eventSession = :sessionId')
            ->andWhere('w.email = :email')
            ->setParameter('sessionId', $eventSessionId)
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Returns an array of EventEnrollmentWaitlist for a session,
     * filtered and sorted according to the user's query.
     */
    public function findWaitlistItemsForSession(
        EventSession $eventSession,
        EventWaitlistItemsQueryDTO $queryDto,
    ): array {
        $qb = $this->createQueryBuilder('w')
            ->leftJoin('w.originalCheckout', 'oc')
            ->leftJoin('oc.company', 'c')
            ->where('w.eventSession = :session')
            ->andWhere('w.promotedAt IS NULL')
            ->setParameter('session', $eventSession->getId());

        if ($queryDto->searchTerm) {
            $qb->andWhere('LOWER(w.firstName) LIKE :term OR LOWER(w.lastName) LIKE :term OR LOWER(w.email) LIKE :term')
                ->setParameter('term', '%'.mb_strtolower($queryDto->searchTerm).'%');
        }

        $sortBy = $queryDto->sortBy ?? 'waitlistPosition';
        $sortOrder = $queryDto->sortOrder ?? 'ASC';

        $qb->orderBy('w.'.$sortBy, $sortOrder);

        $qb->setFirstResult(($queryDto->page - 1) * $queryDto->pageSize)
            ->setMaxResults($queryDto->pageSize);

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns the total number of waitlist items for a session
     * (doesn't limit the query by page/pageSize).
     */
    public function countWaitlistItemsForSession(
        EventSession $eventSession,
        EventWaitlistItemsQueryDTO $queryDto,
    ): int {
        $qb = $this->createQueryBuilder('w')
            ->leftJoin('w.originalCheckout', 'oc')
            ->leftJoin('oc.company', 'c')
            ->select('COUNT(w.id)')
            ->where('w.eventSession = :session')
            ->andWhere('w.promotedAt IS NULL')
            ->setParameter('session', $eventSession->getId());

        if ($queryDto->searchTerm) {
            $qb->andWhere('LOWER(w.firstName) LIKE :term OR LOWER(w.lastName) LIKE :term OR LOWER(w.email) LIKE :term')
                ->setParameter('term', '%'.mb_strtolower($queryDto->searchTerm).'%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findAllBySessionOrderByPosition(EventSession $session): array
    {
        return $this->createQueryBuilder('w')
            ->where('w.eventSession = :sessionId')
            ->andWhere('w.promotedAt IS NULL')
            ->setParameter('sessionId', $session->getId())
            ->orderBy('w.waitlistPosition', 'ASC')
            ->addOrderBy('w.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByIdAndSession(int $id, EventSession $session): ?EventEnrollmentWaitlist
    {
        return $this->createQueryBuilder('w')
            ->where('w.id = :id')
            ->andWhere('w.eventSession = :sessionId')
            ->setParameter('id', $id)
            ->setParameter('sessionId', $session->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }
}
