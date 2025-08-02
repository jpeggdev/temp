<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service;

use App\Entity\Employee;
use App\Entity\EventCheckout;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Response\ResetEventCheckoutSessionReservationExpirationResponseDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\ResetEventCheckoutSessionReservationExpirationException;
use App\Repository\EventCheckoutRepository;

readonly class ResetEventCheckoutSessionReservationExpirationService
{
    public function __construct(
        private EventCheckoutRepository $eventCheckoutSessionRepository,
        private ApplySeatingAndReservationService $applySeatingAndReservationService,
    ) {
    }

    public function resetReservationExpiration(
        string $eventCheckoutSessionUuid,
        Employee $employee,
    ): ResetEventCheckoutSessionReservationExpirationResponseDTO {
        $eventCheckoutSession = $this->eventCheckoutSessionRepository->findOneBy([
            'uuid' => $eventCheckoutSessionUuid,
            'createdBy' => $employee->getId(),
        ]);

        if (!$eventCheckoutSession instanceof EventCheckout) {
            throw new ResetEventCheckoutSessionReservationExpirationException('Event Checkout Session not found for the provided UUID.');
        }

        $this->applySeatingAndReservationService->apply($eventCheckoutSession);
        $this->eventCheckoutSessionRepository->save($eventCheckoutSession, true);

        return new ResetEventCheckoutSessionReservationExpirationResponseDTO(
            id: $eventCheckoutSession->getId(),
            uuid: $eventCheckoutSession->getUuid(),
            reservationExpiresAt: $eventCheckoutSession->getReservationExpiresAt()?->format(\DateTimeInterface::ATOM)
        );
    }
}
