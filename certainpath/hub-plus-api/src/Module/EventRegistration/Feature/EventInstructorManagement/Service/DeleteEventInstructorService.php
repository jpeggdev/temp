<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventInstructorManagement\Service;

use App\Entity\EventInstructor;
use App\Module\EventRegistration\Feature\EventInstructorManagement\DTO\Response\DeleteEventInstructorResponseDTO;
use Doctrine\ORM\EntityManagerInterface;

readonly class DeleteEventInstructorService
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function deleteInstructor(EventInstructor $eventInstructor): DeleteEventInstructorResponseDTO
    {
        $deletedInstructorId = $eventInstructor->getId();
        $this->em->remove($eventInstructor);
        $this->em->flush();

        return new DeleteEventInstructorResponseDTO(
            id: $deletedInstructorId,
            message: sprintf('Instructor (ID %d) successfully deleted.', $deletedInstructorId)
        );
    }
}
