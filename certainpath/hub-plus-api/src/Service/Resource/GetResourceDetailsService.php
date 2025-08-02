<?php

declare(strict_types=1);

namespace App\Service\Resource;

use App\DTO\Response\Resource\GetResourceDetailsResponseDTO;
use App\Entity\Employee;
use App\Entity\File;
use App\Entity\Resource;
use App\Entity\ResourceCategoryMapping;
use App\Entity\ResourceContentBlock;
use App\Entity\ResourceEmployeeRoleMapping;
use App\Entity\ResourceTagMapping;
use App\Entity\ResourceTradeMapping;
use App\Repository\ResourceFavoriteRepository;
use App\Service\AmazonS3Service;

final readonly class GetResourceDetailsService
{
    public function __construct(
        private ResourceFavoriteRepository $resourceFavoriteRepository,
        private AmazonS3Service $amazonS3Service,
    ) {
    }

    public function getResourceDetails(Resource $resource, Employee $employee): GetResourceDetailsResponseDTO
    {
        $isFavorited = $this->isResourceFavoritedByEmployee($resource, $employee);
        $createdAt = $resource->getCreatedAt()->format(\DateTimeInterface::ATOM);
        $updatedAt = $resource->getUpdatedAt()->format(\DateTimeInterface::ATOM);
        $tags = $this->extractTags($resource);
        $trades = $this->extractTrades($resource);
        $roles = $this->extractRoles($resource);
        $categories = $this->extractCategories($resource);
        $contentBlocks = $this->extractContentBlocks($resource);
        $type = $resource->getType();
        $typeName = $type?->getName() ?? null;
        $legacyUrl = $resource->getLegacyUrl();

        $relatedResources = $this->extractPublishedRelatedResources($resource);

        // Get thumbnail file UUID and URL
        $thumbnailFile = $resource->getThumbnail();
        $thumbnailFileUuid = $thumbnailFile?->getUuid();
        $thumbnailUrl = $this->getPresignedUrlForFile($thumbnailFile) ?? $resource->getThumbnailUrl();

        return new GetResourceDetailsResponseDTO(
            id: $resource->getId(),
            uuid: $resource->getUuid(),
            title: $resource->getTitle(),
            slug: $resource->getSlug(),
            description: $resource->getDescription(),
            tagline: $resource->getTagline(),
            contentUrl: $resource->getContentUrl(),
            filename: $resource->getContentFilename(),
            thumbnailUrl: $thumbnailUrl,
            thumbnailFileUuid: $thumbnailFileUuid,
            typeName: $typeName,
            icon: $type?->getIcon(),
            primaryIcon: $type?->getPrimaryIcon(),
            backgroundColor: $type?->getBackgroundColor(),
            textColor: $type?->getTextColor(),
            borderColor: $type?->getBorderColor(),
            viewCount: $resource->getViewCount(),
            publishStartDate: $resource->getPublishStartDate()?->format(\DateTimeInterface::ATOM),
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            legacyUrl: $legacyUrl,
            categories: array_values($categories),
            trades: array_values($trades),
            roles: array_values($roles),
            tags: array_values($tags),
            contentBlocks: $contentBlocks,
            isFavorited: $isFavorited,
            relatedResources: $relatedResources,
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

    private function isResourceFavoritedByEmployee(Resource $resource, Employee $employee): bool
    {
        $favorite = $this->resourceFavoriteRepository->findOneBy([
            'resource' => $resource,
            'employee' => $employee,
        ]);

        return null !== $favorite;
    }

    private function extractPublishedRelatedResources(Resource $resource): array
    {
        $now = new \DateTimeImmutable();
        $publishedRelated = [];

        foreach ($resource->getResourceRelations() as $relation) {
            $related = $relation->getRelatedResource();
            if (!$related || !$this->isPublishedWithinWindow($related, $now)) {
                continue;
            }

            $type = $related->getType();
            $publishOrCreated = $related->getPublishStartDate() ?? $related->getCreatedAt();
            $thumbnailFile = $related->getThumbnail();

            $publishedRelated[] = [
                'title' => $related->getTitle(),
                'slug' => $related->getSlug(),
                'description' => $related->getDescription(),
                'thumbnailUrl' => $this->getPresignedUrlForFile($thumbnailFile) ?? $related->getThumbnailUrl(),
                'thumbnailFileUuid' => $thumbnailFile?->getUuid(),
                'primaryIcon' => $type?->getPrimaryIcon(),
                'resourceType' => $type?->getName(),
                'createdOrPublishStartDate' => $publishOrCreated,
                'viewCount' => $related->getViewCount(),
                'backgroundColor' => $type?->getBackgroundColor(),
                'textColor' => $type?->getTextColor(),
                'borderColor' => $type?->getBorderColor(),
            ];
        }

        return $publishedRelated;
    }

    private function isPublishedWithinWindow(Resource $res, \DateTimeImmutable $now): bool
    {
        if (!$res->isPublished()) {
            return false;
        }
        if ($res->getPublishStartDate() && $res->getPublishStartDate() > $now) {
            return false;
        }
        if ($res->getPublishEndDate() && $res->getPublishEndDate() < $now) {
            return false;
        }

        return true;
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

        return $mapped->toArray();
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

        return $mapped->toArray();
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

        return $mapped->toArray();
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

        return $mapped->toArray();
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
