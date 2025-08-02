<?php

declare(strict_types=1);

namespace App\Service\EventCategory;

use App\DTO\LoggedInUserDTO;
use App\DTO\Response\EventCategory\DeleteEventCategoryResponseDTO;
use App\Entity\EventCategory;
use App\Exception\EventCategoryInUseException;
use App\Repository\EventCategoryRepository;
use App\Repository\EventRepository\EventRepository;
use App\Service\Event\EventAuthorizationServiceInterface;

readonly class DeleteEventCategoryService
{
    public function __construct(
        private EventCategoryRepository $eventCategoryRepository,
        private EventAuthorizationServiceInterface $eventAuthorizationService,
        private EventRepository $eventRepository,
    ) {
    }

    public function deleteEventCategory(EventCategory $eventCategory, LoggedInUserDTO $loggedInUserDTO): DeleteEventCategoryResponseDTO
    {
        $this->eventAuthorizationService->eventAuthorization($loggedInUserDTO, 'delete');

        $eventsUsingCategory = $this->eventRepository->countByEventCategory($eventCategory);
        if ($eventsUsingCategory > 0) {
            throw new EventCategoryInUseException('delete');
        }

        $this->eventCategoryRepository->remove($eventCategory, true);

        return DeleteEventCategoryResponseDTO::fromEntity($eventCategory);
    }
}
