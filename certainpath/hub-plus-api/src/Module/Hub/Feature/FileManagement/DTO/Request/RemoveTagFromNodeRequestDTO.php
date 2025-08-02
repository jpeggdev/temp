<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class RemoveTagFromNodeRequestDTO
{
    public function __construct(
        #[Assert\NotNull(message: 'Tag ID should not be null.')]
        #[Assert\Positive(message: 'Tag ID must be a positive integer.')]
        public int $tagId,
        #[Assert\NotBlank(message: 'Filesystem node UUID should not be blank.')]
        #[Assert\Uuid(message: 'Filesystem node UUID must be a valid UUID.')]
        public string $filesystemNodeUuid,
    ) {
    }
}
