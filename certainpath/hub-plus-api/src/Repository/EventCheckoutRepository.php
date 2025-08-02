<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventCheckout;
use App\Entity\EventSession;
use App\Enum\EventCheckoutSessionStatus;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\EventCheckoutNotFoundException;
use App\ValueObject\UtcStamp;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventCheckout>
 */
class EventCheckoutRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventCheckout::class);
    }

    public function save(EventCheckout $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Finds the in-progress checkout session for a given employee, event session, and company.
     */
    public function findInProgressSession(
        Employee $employee,
        EventSession $eventSession,
        Company $company,
    ): ?EventCheckout {
        return $this->createQueryBuilder('ecs')
            ->andWhere('ecs.createdBy = :employee')
            ->andWhere('ecs.eventSession = :eventSession')
            ->andWhere('ecs.status = :status')
            ->andWhere('ecs.company = :company')
            ->setParameter('employee', $employee)
            ->setParameter('eventSession', $eventSession)
            ->setParameter('status', EventCheckoutSessionStatus::IN_PROGRESS)
            ->setParameter('company', $company)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findEarliestActiveCheckoutForUserAndSession(
        EventSession $session,
        Employee $employee,
        Company $company,
    ): ?EventCheckout {
        return $this->createQueryBuilder('ec')
            ->andWhere('ec.eventSession = :session')
            ->andWhere('ec.company = :company')
            ->andWhere('ec.createdBy = :employee')
            ->andWhere('ec.finalizedAt IS NULL')
            ->andWhere('ec.reservationExpiresAt > :now')
            ->andWhere('ec.status = :status')
            ->setParameter('session', $session)
            ->setParameter('company', $company)
            ->setParameter('employee', $employee)
            ->setParameter('now', UtcStamp::now()->asUtcString())
            ->setParameter('status', EventCheckoutSessionStatus::IN_PROGRESS)
            ->orderBy('ec.reservationExpiresAt', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Cancels all in-progress sessions for a given employee and event session.
     * Adjust if you also need to filter by `company`.
     */
    public function cancelActiveSessionsForEmployeeAndSession(
        Employee $employee,
        Company $company,
        EventSession $eventSession,
    ): void {
        $qb = $this->createQueryBuilder('ecs');
        $qb->update()
            ->set('ecs.status', ':canceledStatus')
            ->where('ecs.createdBy = :employee')
            ->andWhere('ecs.company = :company')
            ->andWhere('ecs.eventSession = :eventSession')
            ->andWhere('ecs.status = :inProgressStatus')
            ->setParameter('canceledStatus', EventCheckoutSessionStatus::CANCELED)
            ->setParameter('employee', $employee)
            ->setParameter('company', $company)
            ->setParameter('eventSession', $eventSession)
            ->setParameter('inProgressStatus', EventCheckoutSessionStatus::IN_PROGRESS)
            ->getQuery()
            ->execute();
    }

    public function findOneByUuid(string $uuid): ?EventCheckout
    {
        return $this->createQueryBuilder('ec')
            ->andWhere('ec.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByConfirmationNumber(string $confirmationNumber): ?EventCheckout
    {
        return $this->createQueryBuilder('ec')
            ->andWhere('ec.confirmationNumber = :confirmationNumber')
            ->setParameter('confirmationNumber', $confirmationNumber)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByUuidOrFail(string $uuid): EventCheckout
    {
        $eventCheckout = $this->findOneByUuid($uuid);
        if (!$eventCheckout) {
            throw new EventCheckoutNotFoundException("Event Checkout not found for UUID: $uuid");
        }

        return $eventCheckout;
    }
}
