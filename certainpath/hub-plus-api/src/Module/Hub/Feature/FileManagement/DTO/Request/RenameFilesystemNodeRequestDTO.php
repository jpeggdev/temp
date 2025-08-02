<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class RenameFilesystemNodeRequestDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Name cannot be empty')]
        #[Assert\Length(min: 1, max: 255, maxMessage: 'Name cannot be longer than {{ limit }} characters')]
        #[Assert\Regex(pattern: '/^[^\/\\:*?"<>|]+$/', message: 'Name contains invalid characters')]
        public string $name,
    ) {
    }
}
