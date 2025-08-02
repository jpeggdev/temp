<?php

declare(strict_types=1);

namespace App\DTO\Request\ResourceTag;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUpdateResourceTagDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Tag name cannot be empty')]
        public string $name,
    ) {
    }
}
