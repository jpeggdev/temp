<?php

declare(strict_types=1);

namespace App\Service\Resource;

use App\DTO\Response\Resource\ValidateResourceSlugResponseDTO;
use App\Repository\ResourceRepository;

readonly class ValidateResourceSlugService
{
    public function __construct(
        private ResourceRepository $resourceRepository,
    ) {
    }

    public function slugExists(string $slug, ?string $resourceUuid = null): ValidateResourceSlugResponseDTO
    {
        $existingResource = $this->resourceRepository->findOneBy(['slug' => $slug]);

        $exists = false;
        $message = sprintf('The slug "%s" is available', $slug);

        if (null !== $existingResource) {
            if ($resourceUuid && $existingResource->getUuid() === $resourceUuid) {
                $message = sprintf(
                    'The slug "%s" is already used by THIS resource (that is fine).',
                    $slug
                );
            } else {
                $exists = true;
                $message = sprintf('The slug "%s" already exists', $slug);
            }
        }

        return new ValidateResourceSlugResponseDTO(
            slugExists: $exists,
            message: $message
        );
    }
}
