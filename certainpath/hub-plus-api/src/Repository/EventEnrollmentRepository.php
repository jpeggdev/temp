<?php

namespace App\Repository;

use App\Entity\EventEnrollment;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventWaitlist\DTO\Request\EventEnrollmentsQueryDTO;
use App\QueryBuilder\EventEnrollmentQueryBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventEnrollment>
 */
class EventEnrollmentRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EventEnrollmentQueryBuilder $eventEnrollmentQueryBuilder,
    ) {
        parent::__construct($registry, EventEnrollment::class);
    }

    public function save(EventEnrollment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(EventEnrollment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function countEnrollmentsForSession(EventSession $session): int
    {
        return (int) $this->createQueryBuilder('ee')
            ->select('COUNT(ee.id)')
            ->andWhere('ee.eventSession = :session')
            ->setParameter('session', $session)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return ArrayCollection<int, EventEnrollment>
     */
    public function findAllByEventSessionId(int $eventSessionId): ArrayCollection
    {
        $result = $this->eventEnrollmentQueryBuilder
            ->createGetAllByEventSessionId($eventSessionId)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    public function findOneByEventSessionAndEmployee(int $eventSessionId, int $employeeId): ?EventEnrollment
    {
        return $this->createQueryBuilder('ee')
            ->andWhere('ee.eventSession = :sessionId')
            ->andWhere('ee.employee = :employeeId')
            ->setParameter('sessionId', $eventSessionId)
            ->setParameter('employeeId', $employeeId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByEventSessionAndEmail(int $eventSessionId, string $email): ?EventEnrollment
    {
        return $this->createQueryBuilder('ee')
            ->andWhere('ee.eventSession = :sessionId')
            ->andWhere('ee.email = :email')
            ->setParameter('sessionId', $eventSessionId)
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findEnrollmentsForSessionByQueryDTO(
        EventSession $eventSession,
        EventEnrollmentsQueryDTO $queryDto,
    ): array {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.eventCheckout', 'ec')
            ->leftJoin('ec.company', 'c')
            ->where('e.eventSession = :sessionId')
            ->setParameter('sessionId', $eventSession->getId());

        if ($queryDto->searchTerm) {
            $qb->andWhere('LOWER(e.firstName) LIKE :term OR LOWER(e.lastName) LIKE :term OR LOWER(e.email) LIKE :term')
                ->setParameter('term', '%'.mb_strtolower($queryDto->searchTerm).'%');
        }

        $sortBy = $queryDto->sortBy ?? 'enrolledAt';
        $sortOrder = $queryDto->sortOrder ?? 'ASC';
        $qb->orderBy('e.'.$sortBy, $sortOrder);

        $offset = ($queryDto->page - 1) * $queryDto->pageSize;
        $qb->setFirstResult($offset)
            ->setMaxResults($queryDto->pageSize);

        return $qb->getQuery()->getResult();
    }

    public function countEnrollmentsForSessionByQueryDTO(
        EventSession $eventSession,
        EventEnrollmentsQueryDTO $queryDto,
    ): int {
        $qb = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->leftJoin('e.eventCheckout', 'ec')
            ->leftJoin('ec.company', 'c')
            ->where('e.eventSession = :sessionId')
            ->setParameter('sessionId', $eventSession->getId());

        if ($queryDto->searchTerm) {
            $qb->andWhere('LOWER(e.firstName) LIKE :term OR LOWER(e.lastName) LIKE :term OR LOWER(e.email) LIKE :term')
                ->setParameter('term', '%'.mb_strtolower($queryDto->searchTerm).'%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findOneByIdAndSession(int $enrollmentId, int $sessionId): ?EventEnrollment
    {
        return $this->createQueryBuilder('e')
            ->where('e.id = :enrollmentId')
            ->andWhere('e.eventSession = :sessionId')
            ->setParameter('enrollmentId', $enrollmentId)
            ->setParameter('sessionId', $sessionId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
