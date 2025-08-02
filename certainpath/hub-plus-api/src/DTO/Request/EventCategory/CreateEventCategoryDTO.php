<?php

declare(strict_types=1);

namespace App\DTO\Request\EventCategory;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateEventCategoryDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Event category name should not be blank.')]
        #[Assert\Length(
            max: 255,
            maxMessage: 'Event category name cannot be longer than {{ limit }} characters.'
        )]
        public string $name,
        public ?string $description = null,
        #[Assert\Type(
            type: 'boolean',
            message: 'The value {{ value }} is not a valid {{ type }}.'
        )]
        public bool $isActive = true,
    ) {
    }
}
