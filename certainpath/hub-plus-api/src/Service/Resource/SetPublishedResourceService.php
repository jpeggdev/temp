<?php

declare(strict_types=1);

namespace App\Service\Resource;

use App\DTO\Response\Resource\SetPublishedResourceResponseDTO;
use App\Entity\Resource;
use App\Repository\ResourceRepository;

readonly class SetPublishedResourceService
{
    public function __construct(
        private ResourceRepository $resourceRepository,
    ) {
    }

    /**
     * Fetches a Resource by UUID, sets "isPublished", saves, and returns a Response DTO.
     */
    public function setPublished(Resource $resource, bool $isPublished): SetPublishedResourceResponseDTO
    {
        $resource->setIsPublished($isPublished);
        $this->resourceRepository->save($resource, flush: true);

        return SetPublishedResourceResponseDTO::fromEntity($resource);
    }
}
