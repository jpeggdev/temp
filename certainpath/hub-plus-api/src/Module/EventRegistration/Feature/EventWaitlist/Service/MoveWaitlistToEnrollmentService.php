<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\Service;

use App\Entity\EventEnrollment;
use App\Entity\EventEnrollmentWaitlist;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventWaitlist\DTO\Request\MoveWaitlistToEnrollmentRequestDTO;
use App\Module\EventRegistration\Feature\EventWaitlist\DTO\Response\MoveWaitlistToEnrollmentResponseDTO;
use App\Module\EventRegistration\Feature\EventWaitlist\Exception\WaitlistItemNotFoundException;
use App\Repository\EventEnrollmentRepository;
use App\Repository\EventEnrollmentWaitlistRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class MoveWaitlistToEnrollmentService
{
    public function __construct(
        private EventEnrollmentWaitlistRepository $waitlistRepository,
        private EventEnrollmentRepository $enrollmentRepository,
        private EntityManagerInterface $em,
    ) {
    }

    public function promoteItem(
        EventSession $session,
        MoveWaitlistToEnrollmentRequestDTO $dto,
    ): MoveWaitlistToEnrollmentResponseDTO {
        $waitlistItem = $this->waitlistRepository->findOneByIdAndSession(
            $dto->eventWaitlistId,
            $session
        );
        if (!$waitlistItem instanceof EventEnrollmentWaitlist) {
            throw new WaitlistItemNotFoundException(sprintf('Waitlist item %d was not found for this session.', $dto->eventWaitlistId));
        }

        $enrollment = new EventEnrollment();
        $enrollment->setEventSession($session);
        $enrollment->setEnrolledAt(new \DateTimeImmutable('now'));
        $enrollment->setEmployee($waitlistItem->getEmployee());
        $enrollment->setFirstName($waitlistItem->getFirstName());
        $enrollment->setLastName($waitlistItem->getLastName());
        $enrollment->setEmail($waitlistItem->getEmail());
        $enrollment->setSpecialRequests($waitlistItem->getSpecialRequests());
        $enrollment->setEventCheckout($waitlistItem->getOriginalCheckout());

        $waitlistItem->setPromotedAt(new \DateTimeImmutable('now'));
        $waitlistItem->setWaitlistPosition(null);

        $this->enrollmentRepository->save($enrollment);

        $this->em->flush();

        $this->reIndexWaitlist($session);

        $this->em->flush();

        return MoveWaitlistToEnrollmentResponseDTO::fromEntity($enrollment);
    }

    /**
     * Re-index the waitlist for a session by enumerating existing waitlist items
     * in ascending order. Any items that were just promoted are excluded by
     * checking for a NULL promotedAt.
     */
    private function reIndexWaitlist(EventSession $session): void
    {
        $allActive = $this->waitlistRepository->findAllBySessionOrderByPosition($session);

        foreach ($allActive as $index => $item) {
            $item->setWaitlistPosition($index + 1);
        }
    }
}
