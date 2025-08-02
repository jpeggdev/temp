<?php

declare(strict_types=1);

namespace App\DTO\Request\ResourceCategory;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUpdateResourceCategoryDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Category name cannot be empty')]
        public string $name,
    ) {
    }
}
