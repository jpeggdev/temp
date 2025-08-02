<?php

namespace App\Repository;

use App\Entity\File;
use Doctrine\Persistence\ManagerRegistry;

class FileRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, File::class);
    }
}
