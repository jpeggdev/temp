<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Feature\CredentialManagement\Voter;

use App\Entity\Company;
use App\Module\ServiceTitan\Entity\ServiceTitanCredential;
use App\Service\GetLoggedInUserDTOService;
use App\Service\PermissionService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, ServiceTitanCredential|Company|null>
 */
class ServiceTitanCredentialVoter extends Voter
{
    public const string CREATE_SERVICETITAN_CREDENTIAL = 'CREATE_SERVICETITAN_CREDENTIAL';
    public const string VIEW_SERVICETITAN_CREDENTIAL = 'VIEW_SERVICETITAN_CREDENTIAL';
    public const string EDIT_SERVICETITAN_CREDENTIAL = 'EDIT_SERVICETITAN_CREDENTIAL';
    public const string DELETE_SERVICETITAN_CREDENTIAL = 'DELETE_SERVICETITAN_CREDENTIAL';
    public const string TEST_SERVICETITAN_CREDENTIAL = 'TEST_SERVICETITAN_CREDENTIAL';
    public const string LIST_SERVICETITAN_CREDENTIALS = 'LIST_SERVICETITAN_CREDENTIALS';

    public function __construct(
        private readonly GetLoggedInUserDTOService $getLoggedInUserDTOService,
        private readonly PermissionService $permissionService,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        $allowedAttributes = [
            self::CREATE_SERVICETITAN_CREDENTIAL,
            self::VIEW_SERVICETITAN_CREDENTIAL,
            self::EDIT_SERVICETITAN_CREDENTIAL,
            self::DELETE_SERVICETITAN_CREDENTIAL,
            self::TEST_SERVICETITAN_CREDENTIAL,
            self::LIST_SERVICETITAN_CREDENTIALS,
        ];

        $validSubject = $subject instanceof ServiceTitanCredential
            || $subject instanceof Company
            || null === $subject;

        return in_array($attribute, $allowedAttributes, true) && $validSubject;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user) {
            return false;
        }

        $loggedInUser = $this->getLoggedInUserDTOService->getLoggedInUserDTO();
        if (!$loggedInUser) {
            return false;
        }

        $currentEmployee = $loggedInUser->getActiveEmployee();
        $activeCompany = $loggedInUser->getActiveCompany();

        // For credential-specific operations, check if credential belongs to user's company
        if ($subject instanceof ServiceTitanCredential) {
            if (!$this->credentialBelongsToUserCompany($subject, $activeCompany)) {
                return false;
            }
        }

        // For company-specific operations, check if the specified company matches user's company
        if ($subject instanceof Company) {
            if ($subject->getId() !== $activeCompany->getId()) {
                return false;
            }
        }

        return match ($attribute) {
            self::CREATE_SERVICETITAN_CREDENTIAL => $this->canCreateCredential($currentEmployee),
            self::VIEW_SERVICETITAN_CREDENTIAL => $this->canViewCredential($currentEmployee),
            self::EDIT_SERVICETITAN_CREDENTIAL => $this->canEditCredential($currentEmployee),
            self::DELETE_SERVICETITAN_CREDENTIAL => $this->canDeleteCredential($currentEmployee),
            self::TEST_SERVICETITAN_CREDENTIAL => $this->canTestCredential($currentEmployee),
            self::LIST_SERVICETITAN_CREDENTIALS => $this->canListCredentials($currentEmployee),
            default => false,
        };
    }

    private function credentialBelongsToUserCompany(ServiceTitanCredential $credential, Company $userCompany): bool
    {
        return $credential->getCompany()?->getId() === $userCompany->getId();
    }

    private function canCreateCredential(mixed $employee): bool
    {
        return $this->permissionService->hasPermission($employee, 'CAN_MANAGE_SERVICETITAN_CREDENTIALS')
            || $this->permissionService->hasRole($employee, 'ROLE_SUPER_ADMIN')
            || $this->permissionService->hasRole($employee, 'ROLE_COMPANY_ADMIN');
    }

    private function canViewCredential(mixed $employee): bool
    {
        return $this->permissionService->hasPermission($employee, 'CAN_VIEW_SERVICETITAN_CREDENTIALS')
            || $this->permissionService->hasPermission($employee, 'CAN_MANAGE_SERVICETITAN_CREDENTIALS')
            || $this->permissionService->hasRole($employee, 'ROLE_SUPER_ADMIN')
            || $this->permissionService->hasRole($employee, 'ROLE_COMPANY_ADMIN');
    }

    private function canEditCredential(mixed $employee): bool
    {
        return $this->permissionService->hasPermission($employee, 'CAN_MANAGE_SERVICETITAN_CREDENTIALS')
            || $this->permissionService->hasRole($employee, 'ROLE_SUPER_ADMIN')
            || $this->permissionService->hasRole($employee, 'ROLE_COMPANY_ADMIN');
    }

    private function canDeleteCredential(mixed $employee): bool
    {
        return $this->permissionService->hasPermission($employee, 'CAN_MANAGE_SERVICETITAN_CREDENTIALS')
            || $this->permissionService->hasRole($employee, 'ROLE_SUPER_ADMIN')
            || $this->permissionService->hasRole($employee, 'ROLE_COMPANY_ADMIN');
    }

    private function canTestCredential(mixed $employee): bool
    {
        return $this->permissionService->hasPermission($employee, 'CAN_VIEW_SERVICETITAN_CREDENTIALS')
            || $this->permissionService->hasPermission($employee, 'CAN_MANAGE_SERVICETITAN_CREDENTIALS')
            || $this->permissionService->hasRole($employee, 'ROLE_SUPER_ADMIN')
            || $this->permissionService->hasRole($employee, 'ROLE_COMPANY_ADMIN');
    }

    private function canListCredentials(mixed $employee): bool
    {
        return $this->permissionService->hasPermission($employee, 'CAN_VIEW_SERVICETITAN_CREDENTIALS')
            || $this->permissionService->hasPermission($employee, 'CAN_MANAGE_SERVICETITAN_CREDENTIALS')
            || $this->permissionService->hasRole($employee, 'ROLE_SUPER_ADMIN')
            || $this->permissionService->hasRole($employee, 'ROLE_COMPANY_ADMIN');
    }
}
