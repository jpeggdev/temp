<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Response\GetInProgressEventCheckoutSessionResponseDTO;
use App\Repository\EventCheckoutRepository;

readonly class GetInProgressEventCheckoutSessionService
{
    public function __construct(
        private EventCheckoutRepository $eventCheckoutSessionRepository,
    ) {
    }

    public function getInProgressSession(
        EventSession $eventSession,
        Company $company,
        Employee $employee,
    ): ?GetInProgressEventCheckoutSessionResponseDTO {
        $ecs = $this->eventCheckoutSessionRepository->findInProgressSession(
            $employee,
            $eventSession,
            $company
        );

        if (!$ecs) {
            return null;
        }

        $event = $eventSession->getEvent();
        $eventName = $event ? $event->getEventName() : null;

        return new GetInProgressEventCheckoutSessionResponseDTO(
            id: $ecs->getId(),
            uuid: $ecs->getUuid(),
            createdAt: $ecs->getCreatedAt()->format(\DateTimeInterface::ATOM),
            eventName: $eventName,
            eventSessionName: $eventSession->getName(),
            startDate: $eventSession->getStartDate()?->format(\DateTimeInterface::ATOM),
            endDate: $eventSession->getEndDate()?->format(\DateTimeInterface::ATOM)
        );
    }
}
