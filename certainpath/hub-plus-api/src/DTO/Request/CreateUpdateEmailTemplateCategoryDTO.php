<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUpdateEmailTemplateCategoryDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Category name cannot be empty')]
        public string $name,
        #[Assert\NotBlank(message: 'Displayed name cannot be empty')]
        public string $displayedName,
        public ?string $description,
        #[Assert\NotNull(message: 'Color ID cannot be null')]
        public int $colorId,
    ) {
    }
}
