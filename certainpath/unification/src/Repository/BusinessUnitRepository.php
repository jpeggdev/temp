<?php

namespace App\Repository;

use App\Entity\BusinessUnit;
use Doctrine\Persistence\ManagerRegistry;

class BusinessUnitRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BusinessUnit::class);
    }
}
