<?php

namespace App\Repository;

use App\Entity\Report;
use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Report>
 */
class ReportRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Report::class);
    }

    public function getLastDMERReportForCompany(Company $company): ?Report
    {
        return $this->createQueryBuilder('r')
            ->where('r.company = :company')
            ->setParameter('company', $company)
            ->andWhere('r.name = :name')
            ->setParameter('name', 'dmer')
            ->orderBy('r.lastRun', 'DESC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function saveReport(Report $report): Report
    {
        /** @var Report $saved */
        $saved = $this->save($report);
        return $saved;
    }
}
