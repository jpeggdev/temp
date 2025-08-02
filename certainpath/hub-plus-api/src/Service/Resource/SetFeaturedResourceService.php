<?php

declare(strict_types=1);

namespace App\Service\Resource;

use App\DTO\Response\Resource\SetFeaturedResourceResponseDTO;
use App\Entity\Resource;
use App\Repository\ResourceRepository;

readonly class SetFeaturedResourceService
{
    public function __construct(
        private ResourceRepository $resourceRepository,
    ) {
    }

    public function setFeatured(Resource $resource, bool $isFeatured): SetFeaturedResourceResponseDTO
    {
        $resource->setIsFeatured($isFeatured);
        $this->resourceRepository->save($resource, flush: true);

        return SetFeaturedResourceResponseDTO::fromEntity($resource);
    }
}
