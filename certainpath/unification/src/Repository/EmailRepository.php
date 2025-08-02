<?php

namespace App\Repository;

use App\Entity\Email;
use Doctrine\Persistence\ManagerRegistry;

class EmailRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Email::class);
    }
}
