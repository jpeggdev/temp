<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventSessionManagement\Service;

use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventSessionManagement\DTO\Response\GetSingleEventSessionResponseDTO;

final readonly class GetSingleEventSessionService
{
    public function getSession(EventSession $eventSession): GetSingleEventSessionResponseDTO
    {
        return GetSingleEventSessionResponseDTO::fromEntity($eventSession);
    }
}
