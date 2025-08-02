<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class GetPresignedUrlsRequestDTO
{
    /**
     * @param string[] $fileUuids
     */
    public function __construct(
        #[Assert\NotNull(message: 'File UUIDs should not be null.')]
        #[Assert\All([
            new Assert\NotBlank(message: 'File UUID should not be blank.'),
            new Assert\Uuid(message: 'Each file UUID must be a valid UUID.')
        ])]
        public array $fileUuids,
    ) {
    }
}
