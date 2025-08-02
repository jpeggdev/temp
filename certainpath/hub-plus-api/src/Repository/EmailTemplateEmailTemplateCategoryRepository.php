<?php

namespace App\Repository;

use App\Entity\EmailTemplateEmailTemplateCategoryMapping;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmailTemplateEmailTemplateCategoryMapping>
 */
class EmailTemplateEmailTemplateCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailTemplateEmailTemplateCategoryMapping::class);
    }
}
