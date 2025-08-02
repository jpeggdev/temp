<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\Service;

use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventWaitlist\DTO\Request\EventWaitlistItemsQueryDTO;
use App\Module\EventRegistration\Feature\EventWaitlist\DTO\Response\EventWaitlistItemResponseDTO;
use App\Repository\EventEnrollmentWaitlistRepository;

readonly class GetEventWaitlistItemsService
{
    public function __construct(
        private EventEnrollmentWaitlistRepository $eventEnrollmentWaitlistRepository,
    ) {
    }

    /**
     * Returns array of waitlist items and totalCount in:
     * [
     *   'items' => EventWaitlistItemResponseDTO[],
     *   'totalCount' => int
     * ]
     */
    public function getEventWaitlistItems(
        EventSession $eventSession,
        EventWaitlistItemsQueryDTO $queryDto,
    ): array {
        $waitlistEntities = $this->eventEnrollmentWaitlistRepository
            ->findWaitlistItemsForSession($eventSession, $queryDto);

        $items = array_map(
            fn ($waitlistEntity) => EventWaitlistItemResponseDTO::fromEntity($waitlistEntity),
            $waitlistEntities
        );

        $totalCount = $this->eventEnrollmentWaitlistRepository
            ->countWaitlistItemsForSession($eventSession, $queryDto);

        return [
            'items' => $items,
            'totalCount' => $totalCount,
        ];
    }
}
