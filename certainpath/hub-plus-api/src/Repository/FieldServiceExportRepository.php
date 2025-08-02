<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\FieldServiceExport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FieldServiceExport>
 */
class FieldServiceExportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FieldServiceExport::class);
    }

    public function save(FieldServiceExport $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return array{
     *     exports: FieldServiceExport[],
     *     totalCount: int
     * }
     */
    public function findExports(
        ?string $intacctId,
        ?int $page,
        ?int $pageSize,
        ?string $sortOrder = 'ASC',
    ): array {
        $offset = ($page - 1) * $pageSize;

        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.fieldServiceExportAttachments', 'a')
            ->addSelect('a')
            ->where('e.intacctId = :intacctId')
            ->setParameter('intacctId', $intacctId);

        $qb->orderBy('e.createdAt', $sortOrder)
            ->setMaxResults($pageSize)
            ->setFirstResult($offset);

        $exports = $qb->getQuery()->getResult();

        $totalCountQb = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.intacctId = :intacctId')
            ->setParameter('intacctId', $intacctId);

        $totalCount = (int) $totalCountQb->getQuery()->getSingleScalarResult();

        return [
            'exports' => $exports,
            'totalCount' => $totalCount,
        ];
    }
}
