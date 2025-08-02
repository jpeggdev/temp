<?php

declare(strict_types=1);

namespace App\Module\Stochastic\Feature\DoNotMailManagement\Voter;

use App\Entity\EventVoucher;
use App\Security\Voter\RoleSuperAdminVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class BulkRestrictedAddressVoter extends RoleSuperAdminVoter
{
    public const string CREATE = 'BULK_RESTRICTED_ADDRESS_CREATE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        $allowedAttributes = [
            self::CREATE,
        ];

        $isValidSubject = $subject instanceof EventVoucher || null === $subject;

        return in_array($attribute, $allowedAttributes, true) && $isValidSubject;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if (parent::voteOnAttribute($attribute, $subject, $token)) {
            return true;
        }

        $loggedInUser = $this->getLoggedInUserDTOService->getLoggedInUserDTO();
        $currentEmployee = $loggedInUser->getActiveEmployee();

        if ($this->permissionService->hasRole($currentEmployee, 'ROLE_MARKETING')) {
            return true;
        }

        return false;
    }
}
