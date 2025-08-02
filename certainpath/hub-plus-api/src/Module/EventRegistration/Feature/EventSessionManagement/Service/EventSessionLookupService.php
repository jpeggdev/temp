<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventSessionManagement\Service;

use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventSessionManagement\DTO\Request\EventSessionLookupRequestDTO;
use App\Module\EventRegistration\Feature\EventSessionManagement\DTO\Response\EventSessionLookupResponseDTO;
use App\Repository\EventSession\EventSessionRepository;

readonly class EventSessionLookupService
{
    public function __construct(
        private EventSessionRepository $sessionRepository,
    ) {
    }

    public function lookupSessions(EventSessionLookupRequestDTO $dto): array
    {
        $sessions = $this->sessionRepository->findSessionsByLookup($dto);
        $totalCount = $this->sessionRepository->getLookupTotalCount($dto);

        $sessionDTOs = \array_map(
            static fn (EventSession $s) => EventSessionLookupResponseDTO::fromEntity($s),
            $sessions
        );

        return [
            'sessions' => $sessionDTOs,
            'totalCount' => $totalCount,
        ];
    }
}
