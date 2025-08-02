<?php

namespace App\Repository;

use App\DTO\Request\Campaign\CreateCampaignDTO;
use App\Entity\CompanyJobEvent;
use App\Entity\Company;
use Doctrine\Persistence\ManagerRegistry;

class CompanyJobEventRepository extends AbstractRepository
{
    public function __construct(
        private readonly ManagerRegistry $registry,
    ) {
        parent::__construct($this->registry, CompanyJobEvent::class);
    }

    public function addEvent(CompanyJobEvent $eventToSave): void
    {
        if (!$this->getEntityManager()->isOpen()) {
            $this->registry->resetManager();
        }
        $this->save($eventToSave);
    }

    public function getLastEventCompanyJob(CreateCampaignDTO $campaignDto): ?CompanyJobEvent
    {
        return $this->findOneBy(
            ['jobName' => $campaignDto->getIdentifier()],
            ['createdAt' => 'DESC', 'id' => 'DESC']
        );
    }

    public function getLastEventForCompanyJobName(
        Company $company,
        string $jobName
    ): ?CompanyJobEvent {
        return $this->findOneBy(
            [
                'company' => $company,
                'jobName' => $jobName,
            ],
            ['createdAt' => 'DESC', 'id' => 'DESC']
        );
    }

    public function getJobNamesForCompany(Company $company)
    {
        $qb = $this->createQueryBuilder('ce')
            ->select('ce.jobName')
            ->where('ce.company = :company')
            ->groupBy('ce.jobName')
            ->setParameter('company', $company);
        return $qb->getQuery()->getResult();
    }
}
