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

final readonly class UpdateEventSessionService
{
    public function __construct(
        private EventSessionRepository $eventSessionRepository,
        private EventRepository $eventRepository,
        private EventInstructorRepository $eventInstructorRepository,
        private EventVenueRepository $eventVenueRepository,
        private TimezoneRepository $timezoneRepository,
    ) {
    }

    public function updateSession(
        EventSession $session,
        CreateUpdateEventSessionRequestDTO $dto,
    ): CreateUpdateEventSessionResponseDTO {
        if (null !== $dto->eventUuid && $dto->eventUuid !== $session->getEvent()?->getUuid()) {
            $event = $this->eventRepository->findOneByUuid($dto->eventUuid);
            if (!$event) {
                throw new CreateUpdateEventSessionException('Event not found: '.$dto->eventUuid);
            }
            $session->setEvent($event);
        }

        if (null !== $dto->startDate) {
            $session->setStartDate($dto->startDate);
        }
        if (null !== $dto->endDate) {
            $session->setEndDate($dto->endDate);
        }

        if (null !== $dto->maxEnrollments) {
            $session->setMaxEnrollments($dto->maxEnrollments);
        }
        $session->setVirtualLink($dto->virtualLink);
        $session->setNotes($dto->notes);
        $session->setIsPublished($dto->isPublished);
        $session->setName($dto->name);

        if (null !== $dto->instructorId) {
            $instructor = $this->eventInstructorRepository->findOneById($dto->instructorId);
            if (!$instructor) {
                throw new CreateUpdateEventSessionException('Instructor not found with ID: '.$dto->instructorId);
            }
            $session->setInstructor($instructor);
        } else {
            $session->setInstructor(null);
        }

        if (null !== $dto->timezoneId) {
            $timezone = $this->timezoneRepository->findOneById($dto->timezoneId);
            if (!$timezone) {
                throw new CreateUpdateEventSessionException('Timezone not found: '.$dto->timezoneId);
            }
            $session->setTimezone($timezone);
        } else {
            $session->setTimezone(null);
        }

        $session->setIsVirtualOnly($dto->isVirtualOnly ?? false);

        if (true === $dto->isVirtualOnly) {
            $session->setVenue(null);
        } else {
            if (null !== $dto->venueId) {
                $venue = $this->eventVenueRepository->findOneById($dto->venueId);
                if (!$venue) {
                    throw new CreateUpdateEventSessionException('Venue not found: '.$dto->venueId);
                }
                $session->setVenue($venue);
            } else {
                $session->setVenue(null);
            }
        }

        $this->eventSessionRepository->save($session, true);

        return CreateUpdateEventSessionResponseDTO::fromEntity($session);
    }
}
