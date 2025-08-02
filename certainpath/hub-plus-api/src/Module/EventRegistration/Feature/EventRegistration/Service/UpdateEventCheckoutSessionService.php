<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service;

use App\Entity\EventCheckout;
use App\Entity\EventCheckoutAttendee;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\UpdateEventCheckoutSessionRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Response\AttendeeResponseDTO;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Response\UpdateEventCheckoutSessionResponseDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\PaymentException;
use App\Repository\EmployeeRepository;
use App\Repository\EventEnrollmentRepository;
use App\Repository\EventEnrollmentWaitlistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Lock\Exception\LockAcquiringException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\SemaphoreStore;

final readonly class UpdateEventCheckoutSessionService
{
    private LockFactory $lockFactory;

    public function __construct(
        private EntityManagerInterface $em,
        private ApplySeatingAndReservationService $applySeatingAndReservationService,
        private EmployeeRepository $employeeRepository,
        private EventEnrollmentRepository $eventEnrollmentRepository,
        private EventEnrollmentWaitlistRepository $eventEnrollmentWaitlistRepository,
    ) {
        $store = new SemaphoreStore();
        $this->lockFactory = new LockFactory($store);
    }

    public function updateSession(
        EventCheckout $eventCheckout,
        UpdateEventCheckoutSessionRequestDTO $dto,
    ): UpdateEventCheckoutSessionResponseDTO {
        $eventSession = $eventCheckout->getEventSession();
        if (!$eventSession) {
            throw new PaymentException('No EventSession found for this checkout.');
        }

        $lockKey = sprintf('update_event_checkout_%s', $eventSession->getUuid());
        $lock = $this->lockFactory->createLock($lockKey);

        if (!$lock->acquire(true)) {
            throw new LockAcquiringException(sprintf('Could not acquire lock for key: %s', $lockKey));
        }

        try {
            $this->em->beginTransaction();

            try {
                $this->validateNotEnrolledOrWaitlisted($dto, $eventCheckout);

                $eventCheckout->setContactName($dto->contactName);
                $eventCheckout->setContactEmail($dto->contactEmail);
                $eventCheckout->setContactPhone($dto->contactPhone);
                $eventCheckout->setGroupNotes($dto->groupNotes);

                if (!empty($dto->attendees)) {
                    $this->reconcileAttendees($eventCheckout, $dto->attendees);
                } else {
                    foreach ($eventCheckout->getEventCheckoutAttendees() as $existingAttendee) {
                        $eventCheckout->removeEventCheckoutAttendee($existingAttendee);
                        $this->em->remove($existingAttendee);
                    }
                }

                $this->applySeatingAndReservationService->apply($eventCheckout);

                $this->em->persist($eventCheckout);
                $this->em->flush();
                $this->em->commit();

                $attendeeResponseDTOs = [];
                foreach ($eventCheckout->getEventCheckoutAttendees() as $attendee) {
                    $attendeeResponseDTOs[] = new AttendeeResponseDTO(
                        id: $attendee->getId(),
                        firstName: $attendee->getFirstName(),
                        lastName: $attendee->getLastName(),
                        email: $attendee->getEmail(),
                        specialRequests: $attendee->getSpecialRequests()
                    );
                }

                return new UpdateEventCheckoutSessionResponseDTO(
                    id: $eventCheckout->getId(),
                    uuid: $eventCheckout->getUuid(),
                    contactName: $eventCheckout->getContactName() ?? '',
                    contactEmail: $eventCheckout->getContactEmail() ?? '',
                    contactPhone: $eventCheckout->getContactPhone(),
                    groupNotes: $eventCheckout->getGroupNotes(),
                    attendees: $attendeeResponseDTOs
                );
            } catch (\Throwable $e) {
                $this->em->rollBack();
                throw $e;
            }
        } finally {
            $lock->release();
        }
    }

    private function validateNotEnrolledOrWaitlisted(
        UpdateEventCheckoutSessionRequestDTO $dto,
        EventCheckout $eventCheckout,
    ): void {
        $eventSession = $eventCheckout->getEventSession();
        $sessionId = $eventSession->getId();
        $company = $eventCheckout->getCompany();

        foreach ($dto->attendees as $attendeeDto) {
            $email = $attendeeDto->email;
            if (!$email) {
                continue;
            }
            $employeeMatch = $this->employeeRepository->findOneMatchingEmailAndCompany($email, $company);

            if ($employeeMatch) {
                $existingEnrollment = $this->eventEnrollmentRepository
                    ->findOneByEventSessionAndEmployee($sessionId, $employeeMatch->getId());
                if ($existingEnrollment) {
                    throw new PaymentException(sprintf('Employee with email %s is already enrolled in this session.', $email));
                }
                $existingWaitlisted = $this->eventEnrollmentWaitlistRepository
                    ->findOneByEventSessionAndEmployee($sessionId, $employeeMatch->getId());
                if ($existingWaitlisted) {
                    throw new PaymentException(sprintf('Employee with email %s is already waitlisted for this session.', $email));
                }
            } else {
                $existingEnrollment = $this->eventEnrollmentRepository
                    ->findOneByEventSessionAndEmail($sessionId, $email);
                if ($existingEnrollment) {
                    throw new PaymentException(sprintf('Attendee with email %s is already enrolled in this session.', $email));
                }
                $existingWaitlisted = $this->eventEnrollmentWaitlistRepository
                    ->findOneByEventSessionAndEmail($sessionId, $email);
                if ($existingWaitlisted) {
                    throw new PaymentException(sprintf('Attendee with email %s is already waitlisted for this session.', $email));
                }
            }
        }
    }

    private function reconcileAttendees(EventCheckout $session, array $attendeesData): void
    {
        $existingById = [];
        $existingByEmail = [];

        foreach ($session->getEventCheckoutAttendees() as $existingAttendee) {
            $existingById[(string) $existingAttendee->getId()] = $existingAttendee;
            $email = $existingAttendee->getEmail();
            if ($email) {
                $existingByEmail[strtolower($email)] = $existingAttendee;
            }
        }

        $matchedAttendees = [];

        foreach ($attendeesData as $dto) {
            $updated = false;

            if ($dto->id && isset($existingById[$dto->id])) {
                $attendee = $existingById[$dto->id];
                $this->applyAttendeeData($attendee, $dto);
                $matchedAttendees[] = $attendee;
                $updated = true;
            }

            if (!$updated) {
                $lowerEmail = $dto->email ? strtolower($dto->email) : null;
                if ($lowerEmail && isset($existingByEmail[$lowerEmail])) {
                    $attendee = $existingByEmail[$lowerEmail];
                    if (!\in_array($attendee, $matchedAttendees, true)) {
                        $this->applyAttendeeData($attendee, $dto);
                        $matchedAttendees[] = $attendee;
                    }
                    $updated = true;
                }
            }

            if (!$updated) {
                $attendee = new EventCheckoutAttendee();
                $attendee->setEventCheckout($session);
                $this->applyAttendeeData($attendee, $dto);
                $this->em->persist($attendee);
                $session->addEventCheckoutAttendee($attendee);
                $matchedAttendees[] = $attendee;
            }
        }

        foreach ($session->getEventCheckoutAttendees() as $existingAttendee) {
            if (!\in_array($existingAttendee, $matchedAttendees, true)) {
                $session->removeEventCheckoutAttendee($existingAttendee);
                $this->em->remove($existingAttendee);
            }
        }
    }

    private function applyAttendeeData(EventCheckoutAttendee $attendee, object $dto): void
    {
        $attendee->setFirstName($dto->firstName);
        $attendee->setLastName($dto->lastName);
        $attendee->setEmail($dto->email);
        $attendee->setIsSelected($dto->isSelected ?? true);
        if (isset($dto->specialRequests)) {
            $attendee->setSpecialRequests($dto->specialRequests);
        }
    }
}
