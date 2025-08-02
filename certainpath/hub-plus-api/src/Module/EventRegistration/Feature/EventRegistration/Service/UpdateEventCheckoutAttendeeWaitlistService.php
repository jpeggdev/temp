<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service;

use App\DTO\LoggedInUserDTO;
use App\Entity\EventCheckout;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\UpdateEventCheckoutAttendeeWaitlistRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Response\UpdateEventCheckoutAttendeeWaitlistResponseDTO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final readonly class UpdateEventCheckoutAttendeeWaitlistService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventSessionSeatCalculatorService $seatCalculatorService,
    ) {
    }

    public function updateWaitlistStatus(
        EventCheckout $eventCheckoutSession,
        UpdateEventCheckoutAttendeeWaitlistRequestDTO $requestDTO,
        LoggedInUserDTO $loggedInUserDTO,
    ): UpdateEventCheckoutAttendeeWaitlistResponseDTO {
        $eventSession = $eventCheckoutSession->getEventSession();
        if (null === $eventSession) {
            throw new BadRequestHttpException('Event session not found');
        }

        $seatData = $this->seatCalculatorService->calculate(
            eventSession: $eventSession,
            company: $loggedInUserDTO->getActiveCompany(),
            employee: $loggedInUserDTO->getActiveEmployee()
        );

        $exactConfirmedAttendees = $seatData['occupiedAttendeeSeatsByCurrentUser'];

        $allAttendees = $eventCheckoutSession->getEventCheckoutAttendees()->toArray();
        $attendeeMap = [];

        foreach ($allAttendees as $attendee) {
            $attendeeMap[$attendee->getId()] = $attendee;
        }

        $updatedStatuses = [];

        foreach ($allAttendees as $attendee) {
            $updatedStatuses[$attendee->getId()] = $attendee->isWaitlist();
        }

        foreach ($requestDTO->attendees as $attendeeUpdate) {
            $attendeeId = $attendeeUpdate->attendeeId;

            if (!isset($attendeeMap[$attendeeId])) {
                throw new BadRequestHttpException(sprintf('Attendee with ID %d not found in this checkout session', $attendeeId));
            }

            $updatedStatuses[$attendeeId] = $attendeeUpdate->isWaitlist;
        }

        $confirmedCount = 0;
        foreach ($updatedStatuses as $isWaitlist) {
            if (!$isWaitlist) {
                ++$confirmedCount;
            }
        }

        if ($confirmedCount !== $exactConfirmedAttendees) {
            throw new BadRequestHttpException(sprintf('You must have exactly %d confirmed attendee(s)', $exactConfirmedAttendees));
        }

        foreach ($updatedStatuses as $attendeeId => $isWaitlist) {
            $attendee = $attendeeMap[$attendeeId];
            $attendee->setIsWaitlist($isWaitlist);
        }

        $this->entityManager->flush();

        $responseAttendees = [];
        foreach ($allAttendees as $attendee) {
            $responseAttendees[] = [
                'id' => $attendee->getId(),
                'email' => $attendee->getEmail(),
                'firstName' => $attendee->getFirstName(),
                'lastName' => $attendee->getLastName(),
                'specialRequests' => $attendee->getSpecialRequests(),
                'isSelected' => $attendee->isSelected(),
                'isWaitlist' => $attendee->isWaitlist(),
            ];
        }

        return new UpdateEventCheckoutAttendeeWaitlistResponseDTO(
            attendees: $responseAttendees,
            success: true,
            message: 'Attendee waitlist status updated successfully'
        );
    }
}
