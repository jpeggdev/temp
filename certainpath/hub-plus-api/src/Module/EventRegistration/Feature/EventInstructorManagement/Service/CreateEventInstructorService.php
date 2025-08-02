<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventInstructorManagement\Service;

use App\Entity\EventInstructor;
use App\Module\EventRegistration\Feature\EventInstructorManagement\DTO\Request\CreateEventInstructorRequestDTO;
use App\Module\EventRegistration\Feature\EventInstructorManagement\DTO\Response\CreateEventInstructorResponseDTO;
use App\Module\EventRegistration\Feature\EventInstructorManagement\Exception\CreateEventInstructorException;
use App\Repository\EventInstructorRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class CreateEventInstructorService
{
    public function __construct(
        private EntityManagerInterface $em,
        private EventInstructorRepository $eventInstructorRepository,
    ) {
    }

    public function createInstructor(
        CreateEventInstructorRequestDTO $dto,
    ): CreateEventInstructorResponseDTO {
        $existingInstructor = $this->eventInstructorRepository->findOneByEmail($dto->email);
        if (null !== $existingInstructor) {
            throw new CreateEventInstructorException(sprintf('Instructor with email "%s" already exists.', $dto->email));
        }

        $newInstructor = new EventInstructor();
        $newInstructor
            ->setName($dto->name)
            ->setEmail($dto->email)
            ->setPhone($dto->phone);

        $this->em->persist($newInstructor);
        $this->em->flush();

        return new CreateEventInstructorResponseDTO(
            id: $newInstructor->getId(),
            name: $newInstructor->getName() ?? '',
            email: $newInstructor->getEmail() ?? '',
            phone: $newInstructor->getPhone()
        );
    }
}
