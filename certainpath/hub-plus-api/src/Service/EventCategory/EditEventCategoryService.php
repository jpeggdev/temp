<?php

declare(strict_types=1);

namespace App\Service\EventCategory;

use App\DTO\LoggedInUserDTO;
use App\DTO\Request\EventCategory\EditEventCategoryDTO;
use App\DTO\Response\EventCategory\EditEventCategoryResponseDTO;
use App\Entity\EventCategory;
use App\Exception\EventCategoryAlreadyExistsException;
use App\Repository\EventCategoryRepository;
use App\Service\Event\EventAuthorizationServiceInterface;

readonly class EditEventCategoryService
{
    public function __construct(
        private EventCategoryRepository $eventCategoryRepository,
        private EventAuthorizationServiceInterface $eventAuthorizationService,
    ) {
    }

    public function editEventCategory(
        EventCategory $eventCategory,
        EditEventCategoryDTO $editEventCategoryDTO,
        LoggedInUserDTO $loggedInUserDTO,
    ): EditEventCategoryResponseDTO {
        $this->eventAuthorizationService->eventAuthorization($loggedInUserDTO, 'edit');

        if ($eventCategory->getName() !== $editEventCategoryDTO->name) {
            $existingEventCategory = $this->eventCategoryRepository->findOneByName($editEventCategoryDTO->name);
            if (null !== $existingEventCategory && $existingEventCategory->getId() !== $eventCategory->getId()) {
                throw new EventCategoryAlreadyExistsException(sprintf('Event category with name "%s" already exists', $editEventCategoryDTO->name));
            }
        }

        $eventCategory->setName($editEventCategoryDTO->name);
        $eventCategory->setDescription($editEventCategoryDTO->description);
        $eventCategory->setIsActive($editEventCategoryDTO->isActive);
        $this->eventCategoryRepository->save($eventCategory, true);

        return EditEventCategoryResponseDTO::fromEntity($eventCategory);
    }
}
