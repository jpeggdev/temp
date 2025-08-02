<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\LoggedInUserDTO;
use App\DTO\Response\GetMyUserProfileResponseDTO;

class GetMyUserProfileService
{
    public function getMyUserProfile(LoggedInUserDTO $loggedInUserDTO): GetMyUserProfileResponseDTO
    {
        $employee = $loggedInUserDTO->getActiveEmployee();

        return new GetMyUserProfileResponseDTO(
            firstName: $employee->getFirstName(),
            lastName: $employee->getLastName(),
            workEmail: $employee->getWorkEmail(),
            employeeUuid: $employee->getUuid()
        );
    }
}
