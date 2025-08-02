<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventSessionManagement\Service;

use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventSessionManagement\DTO\Request\GetEventSessionsRequestDTO;
use App\Module\EventRegistration\Feature\EventSessionManagement\DTO\Response\GetEventSessionsResponseDTO;
use App\Repository\EventRepository\EventRepository;
use App\Repository\EventSession\EventSessionRepository;

final readonly class GetEventSessionsService
{
    public function __construct(
        private EventSessionRepository $repo,
        private EventRepository $eventRepository,
    ) {
    }

    /**
     * @return array{
     *   data: array{
     *     eventName: string|null,
     *     sessions: GetEventSessionsResponseDTO[]
     *   },
     *   totalCount: int
     * }
     */
    public function getSessions(GetEventSessionsRequestDTO $dto): array
    {
        $sessions = $this->repo->findSessionsByQuery($dto);
        $totalCount = $this->repo->getTotalCount($dto);

        $sessionDtos = array_map(
            static fn (EventSession $s) => GetEventSessionsResponseDTO::fromEntity($s),
            $sessions
        );

        $eventName = null;
        if ($dto->eventUuid) {
            $event = $this->eventRepository->findOneByUuid($dto->eventUuid);
            $eventName = $event?->getEventName() ?? null;
        }

        return [
            'data' => [
                'eventName' => $eventName,
                'sessions' => $sessionDtos,
            ],
            'totalCount' => $totalCount,
        ];
    }
}
