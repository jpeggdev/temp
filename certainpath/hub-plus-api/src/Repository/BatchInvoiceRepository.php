<?php

namespace App\Repository;

use App\Entity\BatchInvoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BatchInvoice>
 */
class BatchInvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BatchInvoice::class);
    }

    public function findOneByBatchReference(string $batchReference): ?BatchInvoice
    {
        return $this->findOneBy([
            'batchReference' => $batchReference,
        ]);
    }

    public function saveBatchInvoice(BatchInvoice $batchInvoice): BatchInvoice
    {
        $this->getEntityManager()->persist($batchInvoice);
        $this->getEntityManager()->flush();

        return $batchInvoice;
    }
}
