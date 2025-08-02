<?php

namespace App\Repository;

use App\Entity\Prospect;
use App\Entity\ProspectDetails;
use Doctrine\Persistence\ManagerRegistry;

class ProspectDetailsRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, ProspectDetails::class);
    }

    public function saveProspectDetails(ProspectDetails $prospectDetails): ProspectDetails
    {
        /** @var ProspectDetails $saved */
        $saved = $this->save($prospectDetails);
        return $saved;
    }

    public function findOneByProspect(Prospect $prospect): ?ProspectDetails
    {
        return $this->findOneBy(['prospect' => $prospect]);
    }
}
