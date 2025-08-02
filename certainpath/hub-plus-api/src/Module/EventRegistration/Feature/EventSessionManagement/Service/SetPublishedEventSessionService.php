<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventSessionManagement\Service;

use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventSessionManagement\DTO\Response\SetPublishedEventSessionResponseDTO;
use App\Repository\EventSession\EventSessionRepository;

readonly class SetPublishedEventSessionService
{
    public function __construct(private EventSessionRepository $repo)
    {
    }

    public function setPublished(EventSession $session, bool $isPublished): SetPublishedEventSessionResponseDTO
    {
        $session->setIsPublished($isPublished);
        $this->repo->save($session, true);

        return SetPublishedEventSessionResponseDTO::fromEntity($session);
    }
}
