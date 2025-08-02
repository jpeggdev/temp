<?php

declare(strict_types=1);

namespace App\DTO\Request\EventCategory;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class EditEventCategoryDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Event category name is required')]
        public string $name,
        public string $description,
        #[Assert\NotNull(message: 'isActive must be specified')]
        public bool $isActive,
    ) {
    }
}
