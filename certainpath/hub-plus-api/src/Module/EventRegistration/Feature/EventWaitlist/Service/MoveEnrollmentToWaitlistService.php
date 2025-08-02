<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\Service;

use App\Entity\Employee;
use App\Entity\EventEnrollment;
use App\Entity\EventEnrollmentWaitlist;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventWaitlist\DTO\Request\MoveEnrollmentToWaitlistRequestDTO;
use App\Module\EventRegistration\Feature\EventWaitlist\DTO\Response\MoveEnrollmentToWaitlistResponseDTO;
use App\Module\EventRegistration\Feature\EventWaitlist\Exception\EventEnrollmentNotFoundException;
use App\Repository\EventEnrollmentRepository;
use App\Repository\EventEnrollmentWaitlistRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class MoveEnrollmentToWaitlistService
{
    public function __construct(
        private EventEnrollmentRepository $enrollmentRepository,
        private EventEnrollmentWaitlistRepository $waitlistRepository,
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * Move an EventEnrollment -> Waitlist.
     * 1) Attempt to locate an existing waitlist record by employee.
     * 2) If not found and an email is set, attempt to find by email.
     * 3) If still not found, create a new waitlist record.
     * Finally, remove the enrollment and re-index.
     */
    public function demoteItem(
        EventSession $session,
        MoveEnrollmentToWaitlistRequestDTO $dto,
    ): MoveEnrollmentToWaitlistResponseDTO {
        $enrollment = $this->enrollmentRepository->findOneBy([
            'id' => $dto->enrollmentId,
            'eventSession' => $session->getId(),
        ]);

        if (!$enrollment instanceof EventEnrollment) {
            throw EventEnrollmentNotFoundException::forEnrollmentAndSession($dto->enrollmentId, $session->getId());
        }

        $employee = $enrollment->getEmployee();
        $existingWaitlistItem = null;

        if ($employee instanceof Employee) {
            $existingWaitlistItem = $this->waitlistRepository->findOneBy([
                'eventSession' => $session->getId(),
                'employee' => $employee->getId(),
            ]);
        }

        if (!$existingWaitlistItem && $enrollment->getEmail()) {
            $existingWaitlistItem = $this->waitlistRepository->findOneBy([
                'eventSession' => $session->getId(),
                'email' => $enrollment->getEmail(),
            ]);
        }

        if ($existingWaitlistItem instanceof EventEnrollmentWaitlist) {
            $existingWaitlistItem->setPromotedAt(null);
            $existingWaitlistItem->setWaitlistPosition(null);
            $existingWaitlistItem->setWaitlistedAt(new \DateTimeImmutable('now'));
        } else {
            $existingWaitlistItem = new EventEnrollmentWaitlist();
            $existingWaitlistItem->setEventSession($session);
            $existingWaitlistItem->setEmployee($employee);
            $existingWaitlistItem->setFirstName($enrollment->getFirstName());
            $existingWaitlistItem->setLastName($enrollment->getLastName());
            $existingWaitlistItem->setEmail($enrollment->getEmail());
            $existingWaitlistItem->setSpecialRequests($enrollment->getSpecialRequests());
            $existingWaitlistItem->setOriginalCheckout($enrollment->getEventCheckout());
            $existingWaitlistItem->setPromotedAt(null);
            $existingWaitlistItem->setWaitlistPosition(null);
            $existingWaitlistItem->setWaitlistedAt(new \DateTimeImmutable('now'));

            $this->waitlistRepository->save($existingWaitlistItem);
        }

        $this->enrollmentRepository->remove($enrollment);

        $this->em->flush();

        $this->reIndexWaitlist($session);

        $this->em->flush();

        return MoveEnrollmentToWaitlistResponseDTO::fromEntity($existingWaitlistItem);
    }

    private function reIndexWaitlist(EventSession $session): void
    {
        $allActive = $this->waitlistRepository->findAllBySessionOrderByPosition($session);

        foreach ($allActive as $index => $item) {
            $item->setWaitlistPosition($index + 1);
        }
    }
}
