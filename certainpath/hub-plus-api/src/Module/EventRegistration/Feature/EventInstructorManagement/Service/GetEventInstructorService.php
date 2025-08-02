<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventInstructorManagement\Service;

use App\Entity\EventInstructor;
use App\Module\EventRegistration\Feature\EventInstructorManagement\DTO\Response\GetEventInstructorResponseDTO;

readonly class GetEventInstructorService
{
    public function getInstructor(EventInstructor $instructor): GetEventInstructorResponseDTO
    {
        return new GetEventInstructorResponseDTO(
            id: $instructor->getId(),
            name: $instructor->getName() ?? '',
            email: $instructor->getEmail() ?? '',
            phone: $instructor->getPhone()
        );
    }
}
