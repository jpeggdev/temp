<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventInstructorManagement\DTO\Response;

use App\Entity\EventInstructor;

class SearchEventInstructorsResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?string $phone,
    ) {
    }

    public static function fromEntity(EventInstructor $instructor): self
    {
        return new self(
            id: $instructor->getId(),
            name: $instructor->getName() ?? '',
            email: $instructor->getEmail() ?? '',
            phone: $instructor->getPhone() ?? ''
        );
    }

    /**
     * @param EventInstructor[] $instructors
     *
     * @return SearchEventInstructorsResponseDTO[]
     */
    public static function fromEntities(array $instructors): array
    {
        return array_map(
            static fn (EventInstructor $instructor) => self::fromEntity($instructor),
            $instructors
        );
    }
}
