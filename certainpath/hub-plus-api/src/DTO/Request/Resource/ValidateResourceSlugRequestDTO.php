<?php

declare(strict_types=1);

namespace App\DTO\Request\Resource;

use Symfony\Component\Validator\Constraints as Assert;

readonly class ValidateResourceSlugRequestDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $slug,
        #[Assert\Length(max: 36)]
        public ?string $resourceUuid = null,
    ) {
    }
}
