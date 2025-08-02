<?php

namespace App\Repository;

use App\Entity\FieldServiceSoftware;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FieldServiceSoftware>
 */
class FieldServiceSoftwareRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FieldServiceSoftware::class);
    }

    public function saveSoftware(FieldServiceSoftware $ware): void
    {
        $this->getEntityManager()->persist($ware);
        $this->getEntityManager()->flush();
    }

    public function getSoftware(FieldServiceSoftware $serviceTitan): ?FieldServiceSoftware
    {
        return $this->findOneBy(
            ['name' => $serviceTitan->getName()]
        );
    }

    public function initializeSoftware(): void
    {
        $this->verifySoftware(FieldServiceSoftware::serviceTitan());
        $this->verifySoftware(FieldServiceSoftware::fieldEdge());
        $this->verifySoftware(FieldServiceSoftware::successWare());
        $this->verifySoftware(FieldServiceSoftware::other());
    }

    private function verifySoftware(FieldServiceSoftware $software): void
    {
        if ($existing = $this->getSoftware($software)) {
            $existing->updateFromReference($software);
            $this->saveSoftware($existing);

            return;
        }
        $this->saveSoftware(
            $software
        );
    }

    /**
     * @return FieldServiceSoftware[]
     */
    public function getAllSoftware(): array
    {
        return $this->findAll();
    }
}
