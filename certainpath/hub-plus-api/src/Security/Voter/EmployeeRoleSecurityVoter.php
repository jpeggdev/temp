<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\EmployeeRole;

class EmployeeRoleSecurityVoter extends RoleSuperAdminVoter
{
    public const string EMPLOYEE_ROLE_MANAGE = 'EMPLOYEE_ROLE_MANAGE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::EMPLOYEE_ROLE_MANAGE === $attribute
            && ($subject instanceof EmployeeRole || null === $subject);
    }
}
