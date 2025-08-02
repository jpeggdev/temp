<?php

namespace App\Repository;

use App\Entity\Phone;
use Doctrine\Persistence\ManagerRegistry;

class PhoneRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Phone::class);
    }
}
