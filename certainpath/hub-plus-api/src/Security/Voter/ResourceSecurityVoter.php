<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\DTO\LoggedInUserDTO;
use App\Entity\Resource;
use App\Service\GetLoggedInUserDTOService;
use App\Service\PermissionService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Resource|null>
 */
class ResourceSecurityVoter extends Voter
{
    public const string MANAGE = 'CAN_MANAGE_RESOURCES';

    public function __construct(
        private readonly GetLoggedInUserDTOService $getLoggedInUserDTOService,
        private readonly PermissionService $permissionService,
    ) {
    }

    /**
     * Determine if the voter should handle this attribute and subject.
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::MANAGE === $attribute && ($subject instanceof Resource || null === $subject);
    }

    /**
     * Perform the actual permission check.
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user) {
            return false;
        }

        $loggedInUser = $this->getLoggedInUserDTOService->getLoggedInUserDTO();
        if (!$loggedInUser instanceof LoggedInUserDTO) {
            return false;
        }

        $activeCompany = $loggedInUser->getActiveCompany();
        if (!$activeCompany->isCertainPath()) {
            return false;
        }

        $currentEmployee = $loggedInUser->getActiveEmployee();

        $hasSuperAdminRole = $this->permissionService->hasRole($currentEmployee, 'ROLE_SUPER_ADMIN');
        if (!$hasSuperAdminRole) {
            return false;
        }

        return true;
    }
}
