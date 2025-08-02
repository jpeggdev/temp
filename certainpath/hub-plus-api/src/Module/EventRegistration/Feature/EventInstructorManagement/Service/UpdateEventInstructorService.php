<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventInstructorManagement\Service;

use App\Entity\EventInstructor;
use App\Module\EventRegistration\Feature\EventInstructorManagement\DTO\Request\UpdateEventInstructorRequestDTO;
use App\Module\EventRegistration\Feature\EventInstructorManagement\DTO\Response\UpdateEventInstructorResponseDTO;
use App\Module\EventRegistration\Feature\EventInstructorManagement\Exception\UpdateEventInstructorException;
use App\Repository\EventInstructorRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class UpdateEventInstructorService
{
    public function __construct(
        private EntityManagerInterface $em,
        private EventInstructorRepository $eventInstructorRepository,
    ) {
    }

    public function updateInstructor(
        EventInstructor $instructor,
        UpdateEventInstructorRequestDTO $dto,
    ): UpdateEventInstructorResponseDTO {
        $existingInstructorWithEmail = $this->eventInstructorRepository->findOneByEmail($dto->email);
        if (
            null !== $existingInstructorWithEmail
            && $existingInstructorWithEmail->getId() !== $instructor->getId()
        ) {
            throw new UpdateEventInstructorException(sprintf('Instructor with email "%s" already exists.', $dto->email));
        }

        $instructor
            ->setName($dto->name)
            ->setEmail($dto->email)
            ->setPhone($dto->phone);

        $this->em->persist($instructor);
        $this->em->flush();

        return new UpdateEventInstructorResponseDTO(
            id: $instructor->getId(),
            name: $instructor->getName() ?? '',
            email: $instructor->getEmail() ?? '',
            phone: $instructor->getPhone()
        );
    }
}
