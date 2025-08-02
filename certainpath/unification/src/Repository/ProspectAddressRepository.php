<?php

namespace App\Repository;

use App\Entity\ProspectAddress;
use Doctrine\Persistence\ManagerRegistry;

class ProspectAddressRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProspectAddress::class);
    }
}
