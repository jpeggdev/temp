<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Service;

use App\Entity\File;
use App\Module\Hub\Feature\FileManagement\DTO\Response\GetFileSystemNodeDetailsResponseDTO;
use App\Module\Hub\Feature\FileManagement\Exception\NodeOperationException;
use App\Repository\EventFileRepository;
use App\Repository\EventRepository\EventRepository;
use App\Repository\FileRepository;
use App\Repository\FilesystemNodeRepository;
use App\Repository\ResourceContentBlockRepository;
use App\Repository\ResourceRepository;
use App\Service\AmazonS3Service;

readonly class GetFileSystemNodeDetailsService
{
    public function __construct(
        private FilesystemNodeRepository $filesystemNodeRepository,
        private FileRepository $fileRepository,
        private EventRepository $eventRepository,
        private EventFileRepository $eventFileRepository,
        private ResourceRepository $resourceRepository,
        private ResourceContentBlockRepository $resourceContentBlockRepository,
        private AmazonS3Service $amazonS3Service,
    ) {
    }

    public function getNodeDetails(string $uuid): array
    {
        $node = $this->filesystemNodeRepository->findOneByUuid($uuid);

        if (!$node) {
            throw new NodeOperationException('Filesystem node not found.');
        }

        $duplicates = null;
        $usages = null;
        $presignedUrl = null;

        if ($node instanceof File) {
            if ($node->getMd5Hash()) {
                $duplicates = $this->findDuplicates($node);
            }

            $usages = $this->findUsages($node);

            if (str_starts_with($node->getMimeType() ?? '', 'image/')) {
                $presignedUrl = $this->generatePresignedUrl($node);
            }
        }

        return [
            'data' => GetFileSystemNodeDetailsResponseDTO::fromEntity($node, $duplicates, $usages, $presignedUrl),
        ];
    }

    /**
     * Find duplicate files based on MD5 hash.
     */
    private function findDuplicates(File $file): array
    {
        $duplicateFiles = $this->fileRepository->findDuplicatesByHash(
            $file->getMd5Hash(),
            $file->getId()
        );

        $result = [
            'count' => count($duplicateFiles),
            'files' => [],
        ];

        foreach ($duplicateFiles as $duplicateFile) {
            $result['files'][] = [
                'uuid' => $duplicateFile->getUuid(),
                'name' => $duplicateFile->getName(),
                'path' => $duplicateFile->getParent() ? $duplicateFile->getParent()->getPath() : '/',
                'fileSize' => $duplicateFile->getFileSize(),
                'createdAt' => $duplicateFile->getCreatedAt()->format(\DateTimeInterface::ATOM),
            ];
        }

        return $result;
    }

    /**
     * Find where the file is being used.
     */
    private function findUsages(File $file): array
    {
        $usages = [
            'count' => 0,
            'events' => [],
            'resources' => [],
        ];

        $eventsAsThumbnail = $this->eventRepository->findBy(['thumbnail' => $file]);
        foreach ($eventsAsThumbnail as $event) {
            $usages['events'][] = [
                'uuid' => $event->getUuid(),
                'name' => $event->getEventName(),
                'usageType' => 'thumbnail',
            ];
            ++$usages['count'];
        }

        $eventFiles = $this->eventFileRepository->findBy(['file' => $file]);
        foreach ($eventFiles as $eventFile) {
            $event = $eventFile->getEvent();
            if ($event) {
                $usages['events'][] = [
                    'uuid' => $event->getUuid(),
                    'name' => $event->getEventName(),
                    'usageType' => 'attachment',
                ];
                ++$usages['count'];
            }
        }

        $resourcesAsThumbnail = $this->resourceRepository->findBy(['thumbnail' => $file]);
        foreach ($resourcesAsThumbnail as $resource) {
            $usages['resources'][] = [
                'uuid' => $resource->getUuid(),
                'name' => $resource->getTitle(),
                'usageType' => 'thumbnail',
            ];
            ++$usages['count'];
        }

        $resourceContentBlocks = $this->resourceContentBlockRepository->findBy(['file' => $file]);
        foreach ($resourceContentBlocks as $contentBlock) {
            $resource = $contentBlock->getResource();
            if ($resource) {
                $usages['resources'][] = [
                    'uuid' => $resource->getUuid(),
                    'name' => $resource->getTitle(),
                    'usageType' => 'content',
                    'blockType' => $contentBlock->getType(),
                ];
                ++$usages['count'];
            }
        }

        return $usages;
    }

    /**
     * Generate a presigned URL for viewing/downloading the file.
     */
    private function generatePresignedUrl(File $file): ?string
    {
        try {
            return $this->amazonS3Service->generatePresignedUrl(
                $file->getBucketName(),
                $file->getObjectKey()
            );
        } catch (\Exception $e) {
            return null;
        }
    }
}
