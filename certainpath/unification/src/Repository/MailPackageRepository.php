<?php

namespace App\Repository;

use App\Entity\MailPackage;
use Doctrine\Persistence\ManagerRegistry;

class MailPackageRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MailPackage::class);
    }

    public function saveMailPackage(MailPackage $mailPackage): MailPackage
    {
        /** @var MailPackage $saved */
        $saved = $this->save($mailPackage);
        return $saved;
    }

    public function findById(int $id): ?MailPackage
    {
        return $this->findOneBy(
            ['id' => $id]
        );
    }
}
