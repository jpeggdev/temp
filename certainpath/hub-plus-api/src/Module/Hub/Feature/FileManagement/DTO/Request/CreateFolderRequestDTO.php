<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateFolderRequestDTO
{
    public function __construct(
        #[Assert\Length(max: 255, maxMessage: 'Folder name cannot exceed 255 characters.')]
        #[Assert\Regex(pattern: '/^[^\/\\\\<>:"|?*]+$/', message: 'Folder name contains invalid characters.')]
        public ?string $name,
        #[Assert\Uuid(message: 'Parent folder UUID must be a valid UUID.')]
        public ?string $parentFolderUuid = null,
    ) {
    }
}
