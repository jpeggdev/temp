<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Service\GetLoggedInUserDTOService;
use App\Service\PermissionService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, null>
 */
abstract class RoleSuperAdminVoter extends Voter
{
    public function __construct(
        protected readonly GetLoggedInUserDTOService $getLoggedInUserDTOService,
        protected readonly PermissionService $permissionService,
    ) {
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user) {
            return false;
        }

        $loggedInUser = $this->getLoggedInUserDTOService->getLoggedInUserDTO();
        $currentEmployee = $loggedInUser->getActiveEmployee();

        return $this->permissionService->hasRole($currentEmployee, 'ROLE_SUPER_ADMIN');
    }
}
