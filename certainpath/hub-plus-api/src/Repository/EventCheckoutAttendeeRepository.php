<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventCheckoutAttendee;
use App\Entity\EventSession;
use App\Enum\EventCheckoutSessionStatus;
use App\ValueObject\UtcStamp;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventCheckoutAttendee>
 */
class EventCheckoutAttendeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventCheckoutAttendee::class);
    }

    public function countActiveAttendeesForSession(EventSession $session): int
    {
        return (int) $this->createQueryBuilder('eca')
            ->select('COUNT(eca.id)')
            ->innerJoin('eca.eventCheckout', 'ec')
            ->andWhere('ec.eventSession = :session')
            ->andWhere('eca.isSelected = true')
            ->andWhere('eca.isWaitlist = false')
            ->andWhere('ec.finalizedAt IS NULL')
            ->andWhere('ec.reservationExpiresAt > :now')
            ->andWhere('ec.status = :status')
            // ->setParameter('now', new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
            ->setParameter('now', UtcStamp::now()->asUtcString())
            ->setParameter('status', EventCheckoutSessionStatus::IN_PROGRESS)
            ->setParameter('session', $session)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countActiveAttendeesForSessionByEmployee(
        EventSession $session,
        Employee $employee,
        Company $company,
    ): int {
        return (int) $this->createQueryBuilder('eca')
            ->select('COUNT(eca.id)')
            ->innerJoin('eca.eventCheckout', 'ec')
            ->andWhere('ec.eventSession = :session')
            ->andWhere('ec.company = :company')
            ->andWhere('eca.isSelected = true')
            ->andWhere('ec.finalizedAt IS NULL')
            ->andWhere('ec.reservationExpiresAt > :now')
            ->andWhere('ec.status = :status')
            ->andWhere('ec.createdBy = :employee')
            ->andWhere('eca.isWaitlist = false')
            ->setParameter('now', UtcStamp::now()->asUtcString())
            ->setParameter('session', $session)
            ->setParameter('company', $company)
            ->setParameter('status', EventCheckoutSessionStatus::IN_PROGRESS)
            ->setParameter('employee', $employee)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Counts the number of attendees in "in-progress" checkouts (excluding the provided checkout),
     * who have not yet expired, for the given event session.
     */
    public function countInProgressAttendees(
        int $sessionId,
        int $excludeCheckoutId,
        \DateTimeInterface $now,
    ): int {
        return (int) $this->createQueryBuilder('eca')
            ->select('COUNT(eca.id)')
            ->innerJoin('eca.eventCheckout', 'ec')
            ->andWhere('ec.eventSession = :sessionId')
            ->andWhere('ec.id <> :excludeCheckoutId')
            ->andWhere('ec.finalizedAt IS NULL')
            ->andWhere('ec.reservationExpiresAt > :now')
            ->andWhere('ec.status = :inProgress')
            ->andWhere('eca.isWaitlist = :isWaitlist')
            ->andWhere('eca.isSelected = :isSelected')
            ->setParameter('sessionId', $sessionId)
            ->setParameter('excludeCheckoutId', $excludeCheckoutId)
            ->setParameter('now', UtcStamp::create($now)->asUtcString())
            ->getQuery()
            ->setParameter('inProgress', EventCheckoutSessionStatus::IN_PROGRESS)
            ->setParameter('isWaitlist', false)
            ->setParameter('isSelected', true)
            ->getSingleScalarResult();
    }
}
