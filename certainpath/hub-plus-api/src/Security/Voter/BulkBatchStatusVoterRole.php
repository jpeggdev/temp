<?php

declare(strict_types=1);

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class BulkBatchStatusVoterRole extends RoleSuperAdminVoter
{
    public const string BULK_UPDATE = 'BULK_UPDATE_BATCH_STATUS';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::BULK_UPDATE === $attribute;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if (!parent::voteOnAttribute($attribute, $subject, $token)) {
            return false;
        }

        $loggedInUser = $this->getLoggedInUserDTOService->getLoggedInUserDTO();
        $activeCompany = $loggedInUser->getActiveCompany();

        if (!$activeCompany->isCertainPath()) {
            return false;
        }

        return true;
    }
}
