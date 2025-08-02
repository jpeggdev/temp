<?php

namespace App\Repository;

use App\Entity\CompanyDataImportJob;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CompanyDataImportJob>
 */
class CompanyDataImportJobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompanyDataImportJob::class);
    }

    public function updateProgress(int $importId, string $progress): void
    {
        $qb = $this->createQueryBuilder('c')
            ->update()
            ->set('c.progress', ':progress')
            ->where('c.id = :id')
            ->setParameter('progress', $progress)
            ->setParameter('id', $importId);

        $qb->getQuery()->execute();
    }

    public function updateProgressPercent(
        int $importId,
        string $progress,
        float $percent,
        ?string $status = null,
        ?bool $readyForUnificationProcessing = null,
    ): void {
        $qb = $this->createQueryBuilder('c')
            ->update()
            ->set('c.progress', ':progress')
            ->set('c.progressPercent', ':percent')
            ->set('c.updatedAt', ':updatedAt')
            ->where('c.id = :id')
            ->setParameter('progress', $progress)
            ->setParameter('percent', $percent)
            ->setParameter('updatedAt', new \DateTime())
            ->setParameter('id', $importId);

        if (null !== $status) {
            $qb->set('c.status', ':status');
            $qb->setParameter('status', $status);
        }

        if (null !== $readyForUnificationProcessing) {
            $qb->set('c.readyForUnificationProcessing', ':ready');
            $qb->setParameter('ready', $readyForUnificationProcessing);
        }

        $qb->getQuery()->execute();
    }

    public function updateRowCount(int $importId, int $rowCount): void
    {
        $this->createQueryBuilder('c')
            ->update()
            ->set('c.rowCount', ':rowCount')
            ->where('c.id = :id')
            ->setParameter('rowCount', $rowCount)
            ->setParameter('id', $importId)
            ->getQuery()
            ->execute();
    }
}
