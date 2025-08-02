<?php

namespace App\Repository;

use App\Entity\BusinessRole;
use App\Entity\BusinessRolePermission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BusinessRolePermission>
 */
class BusinessRolePermissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BusinessRolePermission::class);
    }

    /**
     * @return array<BusinessRolePermission>
     */
    public function findByRoleAndPermissionInternalName(BusinessRole $role, string $permissionInternalName): array
    {
        return $this->createQueryBuilder('brp')
            ->join('brp.permission', 'p')
            ->where('brp.role = :role')
            ->andWhere('p.internalName = :permissionInternalName')
            ->setParameter('role', $role)
            ->setParameter('permissionInternalName', $permissionInternalName)
            ->getQuery()
            ->getResult();
    }
}
