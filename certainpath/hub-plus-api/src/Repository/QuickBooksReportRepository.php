<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\QuickBooksReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QuickBooksReport>
 */
class QuickBooksReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuickBooksReport::class);
    }

    public function save(QuickBooksReport $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return array{
     *     reports: QuickBooksReport[],
     *     totalCount: int
     * }
     */
    public function findReports(
        ?string $intacctId,
        ?string $reportType,
        ?int $page,
        ?int $pageSize,
        ?string $sortOrder = 'ASC',
    ): array {
        $offset = ($page - 1) * $pageSize;

        $qb = $this->createQueryBuilder('r')
            ->where('r.intacctId = :intacctId')
            ->setParameter('intacctId', $intacctId);

        if ($reportType) {
            $qb->andWhere('r.reportType = :reportType')
                ->setParameter('reportType', $reportType);
        }

        $qb->orderBy('r.date', $sortOrder)
            ->setMaxResults($pageSize)
            ->setFirstResult($offset);

        $reports = $qb->getQuery()->getResult();

        $totalCountQb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.intacctId = :intacctId')
            ->setParameter('intacctId', $intacctId);

        if ($reportType) {
            $totalCountQb->andWhere('r.reportType = :reportType')
                ->setParameter('reportType', $reportType);
        }

        $totalCount = (int) $totalCountQb->getQuery()->getSingleScalarResult();

        return [
            'reports' => $reports,
            'totalCount' => $totalCount,
        ];
    }
}
