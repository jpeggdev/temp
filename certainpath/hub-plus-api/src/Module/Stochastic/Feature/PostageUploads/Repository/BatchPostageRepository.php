<?php

namespace App\Module\Stochastic\Feature\PostageUploads\Repository;

use App\Entity\BatchPostage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BatchPostage>
 */
class BatchPostageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BatchPostage::class);
    }

    public function persistBatchPostage(BatchPostage $batchPostage): void
    {
        $this->getEntityManager()->persist($batchPostage);
    }

    public function flushEntityManager(): void
    {
        $this->getEntityManager()->flush();
    }

    public function findOneByReference(string $reference): ?BatchPostage
    {
        return $this->findOneBy(['reference' => $reference]);
    }

    /**
     * @return BatchPostage[]
     */
    public function all(): array
    {
        return $this->findAll();
    }
}
