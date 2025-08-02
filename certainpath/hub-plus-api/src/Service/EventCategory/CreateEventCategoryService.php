<?php

declare(strict_types=1);

namespace App\Service\EventCategory;

use App\DTO\LoggedInUserDTO;
use App\DTO\Request\EventCategory\CreateEventCategoryDTO;
use App\DTO\Response\EventCategory\CreateEventCategoryResponseDTO;
use App\Entity\EventCategory;
use App\Exception\EventCategoryAlreadyExistsException;
use App\Repository\EventCategoryRepository;
use App\Service\Event\EventAuthorizationServiceInterface;

readonly class CreateEventCategoryService
{
    public function __construct(
        private EventCategoryRepository $eventCategoryRepository,
        private EventAuthorizationServiceInterface $eventAuthorizationService,
    ) {
    }

    public function createEventCategory(CreateEventCategoryDTO $createEventCategoryDTO, LoggedInUserDTO $loggedInUserDTO): CreateEventCategoryResponseDTO
    {
        $this->eventAuthorizationService->eventAuthorization($loggedInUserDTO, 'create');
        $existingEventCategory = $this->eventCategoryRepository->findOneByName($createEventCategoryDTO->name);
        if (null !== $existingEventCategory) {
            throw new EventCategoryAlreadyExistsException(sprintf('Event category with name "%s" already exists', $createEventCategoryDTO->name));
        }

        $eventCategory = new EventCategory();
        $eventCategory->setName($createEventCategoryDTO->name);
        $eventCategory->setDescription($createEventCategoryDTO->description);
        $eventCategory->setIsActive($createEventCategoryDTO->isActive);

        $this->eventCategoryRepository->save($eventCategory, true);

        return CreateEventCategoryResponseDTO::fromEntity($eventCategory);
    }
}
