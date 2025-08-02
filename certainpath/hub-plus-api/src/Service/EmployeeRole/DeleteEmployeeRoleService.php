<?php

declare(strict_types=1);

namespace App\Service\EmployeeRole;

use App\Entity\EmployeeRole;
use Doctrine\ORM\EntityManagerInterface;

readonly class DeleteEmployeeRoleService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function deleteRole(EmployeeRole $employeeRole): void
    {
        $this->entityManager->remove($employeeRole);
        $this->entityManager->flush();
    }
}
