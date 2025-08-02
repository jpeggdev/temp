<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\DTO\LoggedInUserDTO;
use App\Entity\Company;
use App\Service\GetLoggedInUserDTOService;
use App\Service\PermissionService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Company|null>
 */
class CompanyVoter extends Voter
{
    public const string EDIT = 'COMPANY_EDIT';
    public const string VIEW_ALL = 'COMPANY_VIEW_ALL';

    public function __construct(
        private readonly GetLoggedInUserDTOService $getLoggedInUserDTOService,
        private readonly PermissionService $permissionService,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW_ALL], true)
            && ($subject instanceof Company || null === $subject);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        // Ensure we have a logged-in user
        $user = $token->getUser();
        if (!$user) {
            return false;
        }

        $loggedInUser = $this->getLoggedInUserDTOService->getLoggedInUserDTO();
        if (!$loggedInUser instanceof LoggedInUserDTO) {
            return false;
        }

        $activeCompany = $loggedInUser->getActiveCompany();
        $currentEmployee = $loggedInUser->getActiveEmployee();

        if (!$activeCompany->isCertainPath()) {
            return false;
        }

        if (!$this->permissionService->hasPermission($currentEmployee, 'CAN_MANAGE_COMPANIES_ALL')) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW_ALL:
                return true;

            case self::EDIT:
                if (!$subject instanceof Company) {
                    return false;
                }

                $hasEditCompanies = $this->permissionService->hasPermission(
                    $currentEmployee,
                    'CAN_EDIT_COMPANIES'
                );
                if (!$hasEditCompanies) {
                    return false;
                }

                return true;
        }

        return false;
    }
}
