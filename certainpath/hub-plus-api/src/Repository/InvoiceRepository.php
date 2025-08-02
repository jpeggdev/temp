<?php

namespace App\Repository;

use App\Entity\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Invoice>
 */
class InvoiceRepository extends ServiceEntityRepository
{
    public const MAX_SYNC_ATTEMPTS = 3;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    public function save(Invoice $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findInvoicesForTransactionSync(): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.canBeSynced = true')
            ->andWhere('i.syncedAt IS NOT NULL')
            ->andWhere('i.syncAttempts < :maxAttempts')
            ->setParameter('maxAttempts', self::MAX_SYNC_ATTEMPTS)
            ->getQuery()
            ->getResult();
    }
}
