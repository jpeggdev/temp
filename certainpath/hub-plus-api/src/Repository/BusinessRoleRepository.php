<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\BusinessRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BusinessRole>
 */
class BusinessRoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BusinessRole::class);
    }

    public function initializeBusinessRoles(): void
    {
        $this->verifyRole(BusinessRole::ownerGm());
        $this->verifyRole(BusinessRole::manager());
        $this->verifyRole(BusinessRole::HRRecruiting());
        $this->verifyRole(BusinessRole::financeBackOffice());
        $this->verifyRole(BusinessRole::technician());
        $this->verifyRole(BusinessRole::callCenter());
        $this->verifyRole(BusinessRole::sales());
        $this->verifyRole(BusinessRole::superAdmin());
        $this->verifyRole(BusinessRole::marketing());
        $this->verifyRole(BusinessRole::coach());
    }

    private function verifyRole(BusinessRole $role): void
    {
        if ($existing = $this->getRole($role)) {
            $existing->updateFromReference($role);
            $this->saveRole($existing);

            return;
        }
        $this->saveRole($role);
    }

    public function getRole(BusinessRole $role): ?BusinessRole
    {
        return $this->findOneBy([
            'internalName' => $role->getInternalName(),
        ]);
    }

    private function saveRole(BusinessRole $existing): void
    {
        $this->getEntityManager()->persist($existing);
        $this->getEntityManager()->flush();
    }
}
