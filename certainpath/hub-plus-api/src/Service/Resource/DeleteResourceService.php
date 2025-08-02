<?php

declare(strict_types=1);

namespace App\Service\Resource;

use App\Entity\Resource;
use App\Repository\ResourceRepository;

readonly class DeleteResourceService
{
    public function __construct(
        private ResourceRepository $resourceRepository,
    ) {
    }

    public function deleteResource(Resource $resource): void
    {
        $this->resourceRepository->remove($resource, flush: true);
    }
}
