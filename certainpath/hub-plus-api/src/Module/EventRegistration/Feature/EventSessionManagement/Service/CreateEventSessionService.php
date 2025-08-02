<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventSessionManagement\Service;

use App\Entity\EventSession;
use App\Exception\EventSession\CreateUpdateEventSessionException;
use App\Module\EventRegistration\Feature\EventSessionManagement\DTO\Request\CreateUpdateEventSessionRequestDTO;
use App\Module\EventRegistration\Feature\EventSessionManagement\DTO\Response\CreateUpdateEventSessionResponseDTO;
use App\Repository\EventInstructorRepository;
use App\Repository\EventRepository\EventRepository;
use App\Repository\EventSession\EventSessionRepository;
use App\Repository\EventVenueRepository;
use App\Repository\TimezoneRepository;

final readonly class CreateEventSessionService
{
    public function __construct(
        private EventRepository $eventRepository,
        private EventSessionRepository $eventSessionRepository,
        private EventInstructorRepository $eventInstructorRepository,
        private EventVenueRepository $eventVenueRepository,
        private TimezoneRepository $timezoneRepository,
    ) {
    }

    public function createSession(
        CreateUpdateEventSessionRequestDTO $dto,
    ): CreateUpdateEventSessionResponseDTO {
        $event = $this->eventRepository->findOneByUuid($dto->eventUuid);
        if (!$event) {
            throw new CreateUpdateEventSessionException('Event not found: '.$dto->eventUuid);
        }

        $instructor = null;
        if (null !== $dto->instructorId) {
            $instructor = $this->eventInstructorRepository->findOneById($dto->instructorId);
            if (!$instructor) {
                throw new CreateUpdateEventSessionException('Instructor not found: '.$dto->instructorId);
            }
        }

        $venue = null;
        if (null !== $dto->venueId) {
            $venue = $this->eventVenueRepository->findOneById($dto->venueId);
            if (!$venue) {
                throw new CreateUpdateEventSessionException('Venue not found: '.$dto->venueId);
            }
        }

        $timezone = null;
        if (null !== $dto->timezoneId) {
            $timezone = $this->timezoneRepository->findOneById($dto->timezoneId);
            if (!$timezone) {
                throw new CreateUpdateEventSessionException('Timezone not found: '.$dto->timezoneId);
            }
        }

        $session = new EventSession();
        $session
            ->setEvent($event)
            ->setStartDate($dto->startDate)
            ->setEndDate($dto->endDate)
            ->setMaxEnrollments($dto->maxEnrollments ?? 0)
            ->setVirtualLink($dto->virtualLink)
            ->setNotes($dto->notes)
            ->setIsPublished($dto->isPublished ?? false)
            ->setName($dto->name)
            ->setInstructor($instructor)
            ->setVenue($venue)
            ->setTimezone($timezone)
            ->setIsVirtualOnly($dto->isVirtualOnly ?? false)
        ;

        $this->eventSessionRepository->save($session, true);

        return CreateUpdateEventSessionResponseDTO::fromEntity($session);
    }
}
