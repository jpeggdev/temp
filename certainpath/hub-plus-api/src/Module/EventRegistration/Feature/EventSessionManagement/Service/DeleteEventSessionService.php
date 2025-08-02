<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventSessionManagement\Service;

use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventSessionManagement\DTO\Response\DeleteEventSessionResponseDTO;
use App\Repository\EventSession\EventSessionRepository;

readonly class DeleteEventSessionService
{
    public function __construct(private EventSessionRepository $repo)
    {
    }

    public function delete(EventSession $session): DeleteEventSessionResponseDTO
    {
        $id = $session->getId();
        $this->repo->remove($session, true);

        return new DeleteEventSessionResponseDTO(
            id: $id,
            message: 'Event session deleted successfully.'
        );
    }
}
