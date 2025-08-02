<?php

declare(strict_types=1);

namespace App\Service\Resource;

use App\DTO\Response\Resource\GetResourceResponseDTO;
use App\Entity\Employee;
use App\Entity\File;
use App\Entity\Resource;
use App\Entity\ResourceCategoryMapping;
use App\Entity\ResourceContentBlock;
use App\Entity\ResourceEmployeeRoleMapping;
use App\Entity\ResourceRelation;
use App\Entity\ResourceTagMapping;
use App\Entity\ResourceTradeMapping;
use App\Exception\ResourceNotFoundException;
use App\Repository\ResourceFavoriteRepository;
use App\Service\AmazonS3Service;

/**
 * Fetches a Resource's data, plus determines if the current Employee has favorited it.
 */
readonly class GetResourceService
{
    public function __construct(
        private ResourceFavoriteRepository $resourceFavoriteRepository,
        private AmazonS3Service $amazonS3Service,
    ) {
    }

    /**
     * @throws ResourceNotFoundException
     */
    public function getResource(Resource $resource, Employee $employee): GetResourceResponseDTO
    {
        $tagIds = $this->extractTagIds($resource);
        $tags = $this->extractTags($resource);

        $tradeIds = $this->extractTradeIds($resource);
        $trades = $this->extractTrades($resource);

        $roleIds = $this->extractRoleIds($resource);
        $roles = $this->extractRoles($resource);

        $categoryIds = $this->extractCategoryIds($resource);
        $categories = $this->extractCategories($resource);

        $contentBlocks = $this->extractContentBlocks($resource);

        $type = $resource->getType();
        $typeName = $type?->getName() ?? null;

        $createdAt = $resource->getCreatedAt()->format(\DateTimeInterface::ATOM);
        $updatedAt = $resource->getUpdatedAt()->format(\DateTimeInterface::ATOM);

        $favorite = $this->resourceFavoriteRepository->findOneBy([
            'resource' => $resource,
            'employee' => $employee,
        ]);
        $isFavorited = (null !== $favorite);

        $relatedResources = $this->extractRelatedResources($resource);
        $legacyUrl = $resource->getLegacyUrl();

        // Get thumbnail with presigned URL
        $thumbnailFile = $resource->getThumbnail();
        $thumbnailUrl = $this->getPresignedUrlForFile($thumbnailFile) ?? $resource->getThumbnailUrl();

        return new GetResourceResponseDTO(
            id: $resource->getId(),
            uuid: $resource->getUuid(),
            title: $resource->getTitle(),
            slug: $resource->getSlug(),
            description: $resource->getDescription(),
            tagline: $resource->getTagline(),
            contentUrl: $resource->getContentUrl(),
            thumbnailUrl: $thumbnailUrl,
            thumbnailFileId: $thumbnailFile?->getId() ?? null,
            thumbnailFileUuid: $thumbnailFile?->getUuid() ?? null,
            isPublished: $resource->isPublished() ?? false,
            publishStartDate: $resource->getPublishStartDate()?->format(\DateTimeInterface::ATOM),
            publishEndDate: $resource->getPublishEndDate()?->format(\DateTimeInterface::ATOM),
            typeId: $type?->getId() ?? 0,
            icon: $type?->getIcon(),
            primaryIcon: $type?->getPrimaryIcon(),
            backgroundColor: $type?->getBackgroundColor(),
            textColor: $type?->getTextColor(),
            borderColor: $type?->getBorderColor(),
            tagIds: $tagIds,
            tradeIds: $tradeIds,
            roleIds: $roleIds,
            categoryIds: $categoryIds,
            contentBlocks: $contentBlocks,
            tags: $tags,
            categories: $categories,
            typeName: $typeName,
            viewCount: $resource->getViewCount(),
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            trades: $trades,
            roles: $roles,
            isFavorited: $isFavorited,
            relatedResources: $relatedResources,
            legacyUrl: $legacyUrl,
        );
    }

    private function getPresignedUrlForFile(?File $file): ?string
    {
        if (!$file || !$file->getBucketName() || !$file->getObjectKey()) {
            return null;
        }

        try {
            return $this->amazonS3Service->generatePresignedUrl(
                $file->getBucketName(),
                $file->getObjectKey()
            );
        } catch (\Exception $e) {
            // Log error if needed
            return null;
        }
    }

    private function extractRelatedResources(Resource $resource): array
    {
        $relations = $resource->getResourceRelations();
        $output = [];

        /** @var ResourceRelation $relation */
        foreach ($relations as $relation) {
            $related = $relation->getRelatedResource();
            if (!$related) {
                continue;
            }

            $output[] = [
                'id' => $related->getId(),
                'title' => $related->getTitle(),
            ];
        }

        return $output;
    }

    private function extractTagIds(Resource $resource): array
    {
        return array_values(array_filter(
            $resource->getResourceTagMappings()->map(
                fn (ResourceTagMapping $m) => $m->getResourceTag()?->getId()
            )->toArray()
        ));
    }

    private function extractTags(Resource $resource): array
    {
        $mapped = $resource->getResourceTagMappings()->map(
            function (ResourceTagMapping $m) {
                $tag = $m->getResourceTag();
                if (!$tag) {
                    return null;
                }

                return [
                    'id' => $tag->getId(),
                    'name' => $tag->getName(),
                ];
            }
        )->filter(fn ($item) => null !== $item);

        return array_values($mapped->toArray());
    }

    private function extractTradeIds(Resource $resource): array
    {
        return array_values(array_filter(
            $resource->getResourceTradeMappings()->map(
                fn (ResourceTradeMapping $m) => $m->getTrade()?->getId()
            )->toArray()
        ));
    }

    private function extractTrades(Resource $resource): array
    {
        $mapped = $resource->getResourceTradeMappings()->map(
            function (ResourceTradeMapping $m) {
                $trade = $m->getTrade();
                if (!$trade) {
                    return null;
                }

                return [
                    'id' => $trade->getId(),
                    'name' => $trade->getName(),
                ];
            }
        )->filter(fn ($item) => null !== $item);

        return array_values($mapped->toArray());
    }

    private function extractRoleIds(Resource $resource): array
    {
        return array_values(array_filter(
            $resource->getResourceEmployeeRoleMappings()->map(
                fn (ResourceEmployeeRoleMapping $m) => $m->getEmployeeRole()?->getId()
            )->toArray()
        ));
    }

    private function extractRoles(Resource $resource): array
    {
        $mapped = $resource->getResourceEmployeeRoleMappings()->map(
            function (ResourceEmployeeRoleMapping $m) {
                $role = $m->getEmployeeRole();
                if (!$role) {
                    return null;
                }

                return [
                    'id' => $role->getId(),
                    'name' => $role->getName(),
                ];
            }
        )->filter(fn ($item) => null !== $item);

        return array_values($mapped->toArray());
    }

    private function extractCategoryIds(Resource $resource): array
    {
        return array_values(array_filter(
            $resource->getResourceCategoryMappings()->map(
                fn (ResourceCategoryMapping $m) => $m->getResourceCategory()?->getId()
            )->toArray()
        ));
    }

    private function extractCategories(Resource $resource): array
    {
        $mapped = $resource->getResourceCategoryMappings()->map(
            function (ResourceCategoryMapping $m) {
                $cat = $m->getResourceCategory();
                if (!$cat) {
                    return null;
                }

                return [
                    'id' => $cat->getId(),
                    'name' => $cat->getName(),
                ];
            }
        )->filter(fn ($item) => null !== $item);

        return array_values($mapped->toArray());
    }

    private function extractContentBlocks(Resource $resource): array
    {
        return $resource->getResourceContentBlocks()->map(
            function (ResourceContentBlock $b) {
                $file = $b->getFile();
                $content = $b->getContent();

                // If there's a file, generate presigned URL and use it as content
                if ($file) {
                    $presignedUrl = $this->getPresignedUrlForFile($file);
                    if ($presignedUrl) {
                        $content = $presignedUrl;
                    }
                }

                return [
                    'id' => $b->getUuid(),
                    'type' => $b->getType(),
                    'content' => $content,
                    'order_number' => $b->getSortOrder(),
                    'fileId' => $file?->getId(),
                    'fileUuid' => $file?->getUuid(),
                    'title' => $b->getTitle(),
                    'short_description' => $b->getShortDescription(),
                ];
            }
        )->toArray();
    }
}
