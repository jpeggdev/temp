<?php

namespace App\Repository;

use App\Entity\Employee;
use App\Entity\EmployeePermission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmployeePermission>
 */
class EmployeePermissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmployeePermission::class);
    }

    /**
     * @return array<EmployeePermission>
     */
    public function findByEmployeeAndPermissionInternalName(Employee $employee, string $permissionInternalName): array
    {
        return $this->createQueryBuilder('ep')
            ->join('ep.permission', 'p')
            ->where('ep.employee = :employee')
            ->andWhere('p.internalName = :permissionInternalName')
            ->setParameter('employee', $employee)
            ->setParameter('permissionInternalName', $permissionInternalName)
            ->getQuery()
            ->getResult();
    }
}
