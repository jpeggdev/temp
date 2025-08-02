<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventCheckout;
use App\Entity\EventSession;
use App\Enum\EventCheckoutSessionStatus;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\CreateEventCheckoutSessionRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Response\CreateEventCheckoutSessionResponseDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\CreateEventCheckoutSessionException;
use App\Repository\EventCheckoutRepository;
use App\Repository\EventSession\EventSessionRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class CreateEventCheckoutSessionService
{
    public function __construct(
        private EntityManagerInterface $em,
        private EventCheckoutRepository $eventCheckoutSessionRepository,
        private EventSessionRepository $eventSessionRepository,
    ) {
    }

    public function createSession(
        CreateEventCheckoutSessionRequestDTO $dto,
        Company $company,
        Employee $employee,
    ): CreateEventCheckoutSessionResponseDTO {
        $eventSession = $this->eventSessionRepository->findOneByUuid($dto->eventSessionUuid);

        if (!$eventSession instanceof EventSession) {
            throw new CreateEventCheckoutSessionException('Event Session not found for the provided UUID.');
        }

        $this->eventCheckoutSessionRepository
            ->cancelActiveSessionsForEmployeeAndSession($employee, $company, $eventSession);

        $newSession = new EventCheckout();
        $newSession->setCompany($company);
        $newSession->setEventSession($eventSession);
        $newSession->setCreatedBy($employee);
        $newSession->setContactName($employee->getFirstName().' '.$employee->getLastName());
        $newSession->setContactEmail($employee->getUser()->getEmail());
        $newSession->setStatus(EventCheckoutSessionStatus::IN_PROGRESS);

        $this->em->persist($newSession);
        $this->em->flush();

        return new CreateEventCheckoutSessionResponseDTO(
            id: $newSession->getId(),
            uuid: $newSession->getUuid(),
            reservationExpiresAt: $newSession
            ->getReservationExpiresAt()
            ?->format(\DateTimeInterface::ATOM) ?? ''
        );
    }
}
