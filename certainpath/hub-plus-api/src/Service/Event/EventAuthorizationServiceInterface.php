<?php

declare(strict_types=1);

namespace App\Service\Event;

use App\DTO\LoggedInUserDTO;

interface EventAuthorizationServiceInterface
{
    public function eventAuthorization(LoggedInUserDTO $loggedInUserDTO, string $operation): void;
}
