<?php

namespace App\Repository;

use App\DTO\Query\Campaign\CampaignFileQueryDTO;
use App\Entity\Campaign;
use App\Entity\CampaignFile;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class CampaignFileRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CampaignFile::class);
    }

    /**
     * @return CampaignFile[]
     */
    public function findFilesByQuery(Campaign $campaign, CampaignFileQueryDTO $queryDto): array
    {
        $qb = $this->createQueryBuilder('cf')
            ->leftJoin('cf.file', 'f')
            ->addSelect('f')
            ->where('cf.campaign = :campaign')
            ->setParameter('campaign', $campaign)
            ->setMaxResults($queryDto->pageSize)
            ->setFirstResult(($queryDto->page - 1) * $queryDto->pageSize)
            ->orderBy('f.' . $queryDto->sortBy, $queryDto->sortOrder);

        $this->applyFilters($qb, $queryDto);

        return $qb->getQuery()->getResult();
    }

    public function getTotalCount(Campaign $campaign, CampaignFileQueryDTO $queryDto): int
    {
        $qb = $this->createQueryBuilder('cf')
            ->select('COUNT(cf.id)')
            ->leftJoin('cf.file', 'f')
            ->where('cf.campaign = :campaign')
            ->setParameter('campaign', $campaign);

        $this->applyFilters($qb, $queryDto);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function applyFilters(QueryBuilder $qb, CampaignFileQueryDTO $queryDto): void
    {
        if ($queryDto->searchTerm) {
            $qb->andWhere('LOWER(f.originalFilename) LIKE LOWER(:searchTerm)')
                ->setParameter('searchTerm', '%' . strtolower($queryDto->searchTerm) . '%');
        }
    }
}
