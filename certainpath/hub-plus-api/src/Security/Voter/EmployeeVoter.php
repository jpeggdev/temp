<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\DTO\LoggedInUserDTO;
use App\Entity\Employee;
use App\Service\GetLoggedInUserDTOService;
use App\Service\PermissionService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Employee>
 */
class EmployeeVoter extends Voter
{
    public const string EDIT = 'EMPLOYEE_EDIT';

    public function __construct(
        private readonly GetLoggedInUserDTOService $getLoggedInUserDTOService,
        private readonly PermissionService $permissionService,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::EDIT === $attribute && $subject instanceof Employee;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var Employee $targetEmployee */
        $targetEmployee = $subject;

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

        if (
            !$targetEmployee->getCompany()
            || $targetEmployee->getCompany()->getId() !== $activeCompany->getId()
        ) {
            return false;
        }

        if (!$this->permissionService->hasPermission($currentEmployee, 'CAN_MANAGE_USERS')) {
            return false;
        }

        return true;
    }
}
