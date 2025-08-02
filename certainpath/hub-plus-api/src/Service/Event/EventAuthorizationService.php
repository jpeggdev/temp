<?php

declare(strict_types=1);

namespace App\Service\Event;

use App\DTO\LoggedInUserDTO;
use App\Exception\UnauthorizedEventOperationException;

readonly class EventAuthorizationService implements EventAuthorizationServiceInterface
{
    public function eventAuthorization(LoggedInUserDTO $loggedInUserDTO, string $operation): void
    {
        $employee = $loggedInUserDTO->getActiveEmployee();
        $role = $employee->getRole();
        $roleName = $role ? $role->getInternalName() : 'null';

        if ('ROLE_EVENT_REGISTRATION_ADMIN' !== $roleName && 'ROLE_SUPER_ADMIN' !== $roleName) {
            throw new UnauthorizedEventOperationException($operation);
        }
    }
}
