<?php

declare(strict_types=1);

namespace App\Service\Resource;

use App\DTO\Request\Resource\GetResourcesRequestDTO;
use App\DTO\Response\Resource\GetResourcesResponseDTO;
use App\Entity\Resource;
use App\Repository\ResourceRepository;

readonly class GetResourcesService
{
    public function __construct(
        private ResourceRepository $resourceRepository,
    ) {
    }

    /**
     * @return array{
     *     resources: GetResourcesResponseDTO[],
     *     totalCount: int
     * }
     */
    public function getResources(GetResourcesRequestDTO $queryDto): array
    {
        $resources = $this->resourceRepository->findResourcesByQuery($queryDto);
        $totalCount = $this->resourceRepository->getTotalCount($queryDto);

        $resourceDtos = array_map(
            fn (Resource $resource) => GetResourcesResponseDTO::fromEntity($resource),
            $resources
        );

        return [
            'resources' => $resourceDtos,
            'totalCount' => $totalCount,
        ];
    }

    public function getResourceBySlug(string $slug): ?Resource
    {
        $resource = $this->resourceRepository->findBySlug($slug);
        if (!$resource) {
            return null;
        }

        return $resource;
    }
}
