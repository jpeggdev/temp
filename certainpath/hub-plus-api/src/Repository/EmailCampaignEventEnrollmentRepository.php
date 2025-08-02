<?php

namespace App\Repository;

use App\Entity\EmailCampaignEventEnrollment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmailCampaignEventEnrollment>
 */
class EmailCampaignEventEnrollmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailCampaignEventEnrollment::class);
    }
}
