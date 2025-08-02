<?php

declare(strict_types=1);

namespace App\Service\Resource;

use App\DTO\Request\Resource\GetResourceSearchResultsQueryDTO;
use App\DTO\Response\Resource\GetResourceSearchResultsResponseDTO;
use App\Entity\Employee;
use App\Entity\Resource;
use App\Repository\ResourceRepository;
use App\Repository\ResourceTypeRepository;
use App\Service\AmazonS3Service;

readonly class GetResourceSearchResultsService
{
    public function __construct(
        private ResourceRepository $resourceRepository,
        private ResourceTypeRepository $resourceTypeRepository,
        private AmazonS3Service $amazonS3Service,
    ) {
    }

    /**
     * @return array{
     *     data: array{
     *         resources: GetResourceSearchResultsResponseDTO[],
     *         filters: array{
     *             resourceTypes: array<array{id:int,name:string,icon:string,resourceCount:int}>
     *         }
     *     },
     *     totalCount: int
     * }
     */
    public function getResources(GetResourceSearchResultsQueryDTO $queryDto, ?Employee $employee = null): array
    {
        $resources = $this->resourceRepository->findPublishedResourcesByQuery($queryDto, $employee);
        $totalCount = $this->resourceRepository->getPublishedTotalCount($queryDto, $employee);

        // Collect thumbnail files that need presigned URLs
        $thumbnailItems = [];
        foreach ($resources as $resource) {
            $thumbnailFile = $resource->getThumbnail();
            if ($thumbnailFile && $thumbnailFile->getBucketName() && $thumbnailFile->getObjectKey()) {
                $thumbnailItems[$resource->getId()] = [
                    'bucketName' => $thumbnailFile->getBucketName(),
                    'objectKey' => $thumbnailFile->getObjectKey(),
                ];
            }
        }

        // Generate presigned URLs for all thumbnails in parallel
        $presignedUrls = [];
        if (!empty($thumbnailItems)) {
            $presignedUrls = $this->amazonS3Service->generatePresignedUrls($thumbnailItems);
        }

        // Map resources to DTOs with presigned URLs
        $resourceDTOs = array_map(
            fn (Resource $resource) => GetResourceSearchResultsResponseDTO::fromEntity(
                $resource,
                $presignedUrls[$resource->getId()] ?? null
            ),
            $resources
        );

        // Get resource type facets
        $typeFacetRows = $this->resourceTypeRepository->findAllWithFilteredResourceCounts($queryDto, $employee);
        $resourceTypesFacet = array_map(
            static fn (array $row) => [
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'icon' => $row['icon'],
                'resourceCount' => (int) $row['resourceCount'],
            ],
            $typeFacetRows
        );

        return [
            'data' => [
                'resources' => $resourceDTOs,
                'filters' => [
                    'resourceTypes' => $resourceTypesFacet,
                ],
            ],
            'totalCount' => $totalCount,
        ];
    }
}
