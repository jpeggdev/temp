<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class RenameTagRequestDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Tag name should not be blank.')]
        #[Assert\Length(max: 255, maxMessage: 'Tag name cannot exceed 255 characters.')]
        public string $name,
        #[Assert\Length(max: 255, maxMessage: 'Color value cannot exceed 255 characters.')]
        public ?string $color = null,
    ) {
    }
}
