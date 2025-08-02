<?php

namespace App\Module\CraftMigration\Service;

use App\DTO\Request\Resource\CreateResourceContentBlockDTO;
use App\DTO\Request\Resource\CreateUpdateResourceDTO;
use App\DTO\Response\Resource\GetCreateUpdateResourceMetadataResponseDTO;
use App\Entity\Resource;
use App\Entity\ResourceContentBlock;
use App\Module\CraftMigration\CraftMigrationConstants;
use App\Module\CraftMigration\DTO\Elements\AssetDTO;
use App\Module\CraftMigration\DTO\Elements\CategoryDTO;
use App\Module\CraftMigration\DTO\Elements\EntryDTO;
use App\Module\CraftMigration\DTO\Elements\FileDTO;
use App\Module\CraftMigration\DTO\Elements\TagDTO;
use App\Module\CraftMigration\DTO\Fields\FieldDTO;
use App\Module\CraftMigration\DTO\NewResourceDTO;
use App\Module\CraftMigration\DTO\NewResourceMetaDataDTO;
use App\Module\CraftMigration\DTO\Request\Resource\UpdateRelatedResourcesDTO;
use App\Module\CraftMigration\Repository\CraftMigrationRepository;
use App\Service\Resource\GetCreateUpdateResourceMetadataService;
use App\Service\Resource\GetResourcesService;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Clock\MonotonicClock;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

class CraftMigrationResourcePageService
{
    private readonly SymfonyStyle $io;
    private readonly GetCreateUpdateResourceMetadataResponseDTO $metaData;
    /** @var array<string, array{resource: string, type: string, path: string}> */
    private array $missingFiles = [];

    public function __construct(
        private readonly CraftMigrationRepository $repository,
        private readonly CraftMigrationAssetService $assetService,
        private readonly CraftMigrationCategoryService $categoryService,
        private readonly CraftMigrationTagService $tagService,
        private readonly GetCreateUpdateResourceMetadataService $metaDataService,
        private readonly CraftMigrationMatrixService $matrixService,
        private readonly CraftMigrationResourceService $resourceService,
        private readonly CraftMigrationElementService $elementService,
        private readonly HtmlToMarkdownConverterService $converterService,
        private readonly GetResourcesService $getResourcesService,
        private readonly CraftMigrationProgressService $progressService,
        private readonly LoggerInterface $logger,
    ) {
        $this->io = new SymfonyStyle(new ArgvInput(), new ConsoleOutput());
        $this->metaData = $this->metaDataService->getMetadata();
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function processResourcePageElements(?int $batchSize = null, bool $resume = false): void
    {
        $batchSize = $batchSize ?? CraftMigrationConstants::DEFAULT_BATCH_SIZE;
        $stopwatch = new MonotonicClock();
        $startTime = $stopwatch->now();

        $this->logger->info('Processing Resource Page Elements with batch processing...');
        $this->logger->info(sprintf('Batch size: %d', $batchSize));

        // Check if migration can be resumed
        $startOffset = 0;
        if ($resume && $this->progressService->canResume()) {
            $startOffset = $this->progressService->getResumeOffset();
            $this->logger->info(sprintf('Resuming migration from offset %d', $startOffset));
        } elseif ($resume) {
            $this->logger->info('Resume requested but no valid progress found - starting from beginning');
        }

        // Stream entries instead of loading all into memory at once
        $totalEntries = $this->repository->getEntries()->getResourceCount();
        $this->logger->info(sprintf('Total entries to process: %d', $totalEntries));

        $processedCount = $startOffset;
        $allNewResources = [];

        // Process entries in batches using streaming generator
        foreach ($this->io->progressIterate($this->streamEntriesInBatches($batchSize, $startOffset)) as $batchNumber => $batchResult) {
            $processedCount += count($batchResult);
            $allNewResources = array_merge($allNewResources, $batchResult);

            $this->logger->info(sprintf(
                'Completed batch %d - Processed %d/%d entries (%.1f%%)',
                $batchNumber + 1,
                $processedCount,
                $totalEntries,
                ($processedCount / $totalEntries) * 100
            ));

            // Save progress after each batch
            $this->progressService->saveProgress([
                'processed' => $processedCount,
                'total' => $totalEntries,
                'batch_number' => $batchNumber + 1,
                'batch_size' => $batchSize,
            ]);

            // Log progress every few batches
            if (($batchNumber + 1) % 5 === 0) {
                $elapsed = $stopwatch->now()->diff($startTime);
                $this->logger->info(sprintf(
                    'Progress update: %s elapsed, %d entries processed',
                    $elapsed->format('%H:%I:%S'),
                    $processedCount
                ));
            }
        }

        $this->logger->info('Processing Related Content...');
        $this->processRelatedContent($allNewResources);

        $endTime = $stopwatch->now();
        $duration = $endTime->diff($startTime);
        $this->logger->info(sprintf(
            'Processed %d Resource Pages in %s.',
            count($allNewResources),
            $duration->format('%H hour(s), %I minute(s), %S second(s)')
        ));

        // Clear progress file on successful completion
        $this->progressService->clearProgress();

        // Display missing files summary if any
        if (!empty($this->missingFiles)) {
            $this->displayMissingFilesSummary();
        }

        // Final memory cleanup
        unset($allNewResources);
    }

    /**
     * Process a resource page using bulk-loaded data to avoid N+1 queries.
     *
     * @param TagDTO[]      $tags
     * @param CategoryDTO[] $topicCategories
     * @param CategoryDTO[] $roleCategories
     * @param CategoryDTO[] $tradeCategories
     * @param FieldDTO[]    $fields
     *
     * @throws Exception
     * @throws \Exception
     */
    private function processResourcePageWithBulkData(
        EntryDTO $entry,
        array $tags,
        array $topicCategories,
        array $roleCategories,
        array $tradeCategories,
        ?AssetDTO $featureImage,
        array $fields,
        array $relatedContent,
    ): NewResourceDTO {
        $newResource = new NewResourceDTO();
        $newResource->elementId = $entry->id;
        $existingResource = $this->getResourcesService->getResourceBySlug($entry->slug);
        $existingContentBlocks = [];
        if ($existingResource) {
            $newResource->resourceId = $existingResource->getId();
            $existingContentBlocks = $this->getExistingContentBlocks($existingResource);
        }

        $thumbnail = null;
        if (null !== $featureImage) {
            $featureImageFile = $this->assetService->getFileFromAsset($featureImage);
            $featureImageFile->fileId = $existingResource?->getThumbnail()?->getId();
            $thumbnail = $this->resourceService->createOrUpdateFile($featureImageFile);
            if ($thumbnail === null) {
                $this->logger->error(sprintf(
                    '   Failed to create or update feature image: %s',
                    $featureImageFile->baseFilename
                ));
                // Track missing file
                $this->missingFiles[$featureImageFile->baseFilename] = [
                    'resource' => sprintf('%s (ID: %d)', $entry->title, $entry->id),
                    'type' => 'feature_image',
                    'path' => $featureImageFile->localFilename ?? 'unknown',
                ];
            } else {
                $this->logger->debug(sprintf('   Feature Image URL: [%s]', $thumbnail->getUrl()));
            }
        }

        $content_url = null;
        switch ($entry->type) {
            case 'Course':
                $content_url = $entry->fields['field_courseUrl'];
                break;
            case 'Podcast':
                $content_url = $entry->fields['field_podcast'];
                break;
            case 'Video':
                $content_url = $entry->fields['field_video'];
                break;
        }
        $legacy_url = $entry->uri;

        $this->logger->debug(sprintf('   Enabled: [%s]', $entry->enabled ? 'Yes' : 'No'));

        $newResource->createUpdateResourceDTO = new CreateUpdateResourceDTO(
            title: $entry->title,
            slug: $entry->slug,
            tagline: null,
            description: $entry->fields['field_searchDescription'] ?? '',
            type: $this->getEntryTypeId($entry->type),
            content_url: $content_url,
            thumbnail_url: $thumbnail?->getUrl(),
            thumbnailFileId: $thumbnail?->getId(),
            publish_start_date: $entry->postDate,
            publish_end_date: null,
            legacy_url: $legacy_url,
            is_published: $entry->enabled,
            thumbnailFileUuid: $thumbnail?->getUuid(),
            tagIds: $this->processResourceTags($tags),
            tradeIds: $this->processTradeCategories($tradeCategories),
            roleIds: $this->processRoleCategories($roleCategories),
            categoryIds: $this->processResourceCategories($topicCategories),
        );

        $newContentBlocks = [];
        foreach ($fields as $field) {
            switch ($field->handle) {
                case 'resourceFile':
                    $newContentBlocks = $this->processResourceFiles($field, $existingContentBlocks, $entry);
                    break;
                case 'seriesEntries':
                    $newContentBlocks = $this->processSeriesEntries($field);
                    break;
                case 'contentBlocks':
                    $newContentBlocks = $this->processContentBlocks(
                        $field,
                        $existingContentBlocks,
                        $newResource->createUpdateResourceDTO->description,
                        $entry
                    );
                    break;
                default:
                    $this->logger->debug(sprintf(
                        '   Skipping field type [%s] for elementId [%d]',
                        $field->handle,
                        $entry->id
                    ));
            }
            $newResource->createUpdateResourceDTO->contentBlocks = array_merge(
                $newResource->createUpdateResourceDTO->contentBlocks,
                $newContentBlocks
            );
        }

        $this->logger->debug(sprintf('   Title: [%s]', $newResource->createUpdateResourceDTO->title));
        $this->logger->debug(sprintf('   Slug: [%s]', $newResource->createUpdateResourceDTO->slug));
        $this->logger->debug(sprintf('   Type: [%d]', $newResource->createUpdateResourceDTO->type));
        if (null !== $newResource->createUpdateResourceDTO->content_url) {
            $this->logger->debug(sprintf('   Content URL: [%s]', $newResource->createUpdateResourceDTO->content_url));
        }
        $this->logger->debug(sprintf(
            '   Search Description: [%s]',
            $newResource->createUpdateResourceDTO->description
        ));

        $newResource->relatedElementIds = $this->flatten($relatedContent);
        if (count($newResource->relatedElementIds) > 0) {
            $this->logger->debug(
                sprintf('   Related content [%s]', implode(', ', $newResource->relatedElementIds))
            );
        } else {
            $this->logger->debug('   No related content found');
        }

        $newResource->resourceId = $this->resourceService->createOrUpdateResource(
            $newResource->createUpdateResourceDTO
        );
        $this->logger->debug('---------------------------------------------------------------------------------');

        // Note: We don't unset createUpdateResourceDTO here anymore,
        // it's handled by the calling code after metadata creation

        // Clear other large objects to help with memory management
        unset(
            $existingContentBlocks,
            $fields,
            $tags,
            $topicCategories,
            $roleCategories,
            $tradeCategories,
            $featureImage
        );

        return $newResource;
    }

    /**
     * Processes resource files for a given field.
     *
     * @param ResourceContentBlock[] $existingContentBlocks
     *
     * @return CreateResourceContentBlockDTO[]
     *
     * @throws Exception
     */
    public function processResourceFiles(FieldDTO $field, ?array $existingContentBlocks, EntryDTO $entry): array
    {
        $resourceFile = $this->elementService->getResourceFile($field->elementId);
        foreach ($existingContentBlocks as $existingContentBlock) {
            if ($existingContentBlock['original_filename'] === $resourceFile->filename) {
                $this->logger->debug(sprintf(
                    '   Found existing Resource File [%s]',
                    $existingContentBlock['original_filename']
                ));

                return [
                    new CreateResourceContentBlockDTO(
                        null,
                        'file',
                        $existingContentBlock['content'],
                        $field->sortOrder,
                        null,
                        $existingContentBlock['fileId'],
                        null,
                        $existingContentBlock['original_filename'],
                        null
                    ),
                ];
            }
        }
        $originalFilename = $resourceFile->filename;
        $this->logger->debug(sprintf(
            '   Processing resource file for field [%s] with element ID [%d] [%s]',
            $field->handle,
            $field->elementId,
            $resourceFile->filename
        ));
        if ($resourceFile) {
            $resourceFile = $this->assetService->getFileFromAsset($resourceFile);
            $file = $this->resourceService->createOrUpdateFile($resourceFile);

            if ($file === null) {
                $this->logger->error(sprintf(
                    '   Failed to create or update resource file: %s',
                    $originalFilename
                ));
                // Track missing file
                $this->missingFiles[$originalFilename] = [
                    'resource' => sprintf('%s (ID: %d)', $entry->title, $entry->id),
                    'type' => 'resource_file',
                    'path' => $resourceFile->localFilename ?? 'unknown',
                ];
                return [];
            }

            return [
                new CreateResourceContentBlockDTO(
                    null,
                    'file',
                    $file->getUrl() ?? '',
                    $field->sortOrder,
                    null,
                    $file->getId() ?? null,
                    $file->getUuid() ?? null,
                    $originalFilename,
                    null
                ),
            ];
        }

        return [];
    }

    private function generateResourceUrl(string $slug): string
    {
        if (str_starts_with($slug, 'resources')) {
            return sprintf('/hub/%s', $slug);
        } else {
            return sprintf('/hub/resources/%s', $slug);
        }
    }

    private function getExistingContentBlocks(?Resource $resource): array
    {
        if ($resource) {
            return $resource->getResourceContentBlocks()->map(
                function (ResourceContentBlock $b) {
                    return [
                        'id' => $b->getUuid(),
                        'type' => $b->getType(),
                        'content' => $b->getContent(),
                        'order_number' => $b->getSortOrder(),
                        'original_filename' => $b->getFile()?->getOriginalFilename(),
                        'fileId' => $b->getFile()?->getId(),
                        'title' => $b->getTitle(),
                        'short_description' => $b->getShortDescription(),
                    ];
                }
            )->toArray();
        } else {
            return [];
        }
    }

    /**
     * Processes series entries for a given field.
     *
     * @return CreateResourceContentBlockDTO[]
     *
     * @throws Exception
     */
    private function processSeriesEntries(FieldDTO $field): array
    {
        $outContentBlocks = [];
        $seriesEntries = $this->elementService->getSeriesEntries($field->elementId);
        if (!$seriesEntries) {
            $this->logger->debug(sprintf(
                '   No series entries found for field [%s] with element ID [%d]',
                $field->handle,
                $field->elementId
            ));

            return [];
        }
        foreach ($seriesEntries as $entry) {
            $this->logger->debug(sprintf(
                '   Processing series entry [%s] slug [%s]',
                $entry->title,
                $entry->slug
            ));

            $outContentBlocks[] = new CreateResourceContentBlockDTO(
                null,
                'link',
                $this->generateResourceUrl($entry->slug),
                $field->sortOrder,
                null,
                null,
                null,
                $entry->title,
                null
            );
        }

        return $outContentBlocks;
    }

    /**
     * Processes content blocks for a given field.
     *
     * @param ResourceContentBlock[] $existingContentBlocks
     *
     * @return CreateResourceContentBlockDTO[]
     *
     * @throws Exception
     */
    private function processContentBlocks(
        FieldDTO $field,
        array $existingContentBlocks,
        string $description,
        EntryDTO $entry
    ): array {
        $outContentBlocks = [];
        $contentBlocks = $this->matrixService->getMatrixBlocks($field->elementId);
        if (count($contentBlocks) > 0) {
            $this->logger->debug(sprintf('   Content blocks [%d]', count($contentBlocks)));
            foreach ($contentBlocks as $contentBlockNum => $contentBlock) {
                $this->logger->debug(sprintf(
                    '   Processing content block %d/%d [%s:%d]',
                    $contentBlockNum + 1,
                    count($contentBlocks),
                    $contentBlock->typeName,
                    $contentBlock->id
                ));
                $skipFile = false;
                switch ($contentBlock->resourceType) {
                    case 'file':
                    case 'image':
                        $filename = $contentBlock->content;
                        $this->logger->debug(sprintf('      File exists: [%s]', file_exists($filename) ? 'Yes' : 'No'));
                        $this->logger->debug(sprintf(
                            '      File URL: %s',
                            $filename
                        ));

                        $foundExisting = false;
                        foreach ($existingContentBlocks as $existingContentBlock) {
                            if ($existingContentBlock['original_filename'] === basename($filename)) {
                                $contentBlock->content = $existingContentBlock['content'];
                                $contentBlock->fileId = $existingContentBlock['fileId'];
                                $foundExisting = true;
                                $this->logger->debug(sprintf(
                                    '      Using existing file ID: %d [%s]',
                                    $contentBlock->fileId,
                                    $contentBlock->content
                                ));
                                break;
                            }
                        }
                        if (!$foundExisting && null !== $contentBlock->content) {
                            $asset = FileDTO::fromArray([
                                'baseFilename' => basename($contentBlock->content),
                                'localFilename' => $contentBlock->content,
                                'volumeFilename' => $this->assetService->getVolumeFilename($contentBlock->content),
                            ]);
                            $file = $this->resourceService->createOrUpdateFile($asset);
                            if ($file !== null) {
                                $contentBlock->fileId = $this->resourceService->getFileId($file->getUuid());
                            } else {
                                $this->logger->error(sprintf(
                                    '      Failed to create or update content block file: %s',
                                    basename($contentBlock->content)
                                ));
                                // Track missing file
                                $this->missingFiles[basename($contentBlock->content)] = [
                                    'resource' => sprintf('%s (ID: %d)', $entry->title ?? 'Unknown', $entry->id ?? 0),
                                    'type' => 'content_block_file',
                                    'path' => $asset->localFilename ?? 'unknown',
                                ];
                                $skipFile = true;
                            }
                        }
                        break;

                    case 'text':
                        if (
                            0 === $contentBlockNum
                            && $this->converterService->convertHtmlToMarkdown(
                                $contentBlock->content
                            ) === $description
                        ) {
                            $this->logger->debug(
                                '      Skipping description content block because it matches the search description.'
                            );
                            $skipFile = true;
                        } else {
                            $contentBlock->content =
                                $this->converterService->convertHtmlToMarkdown($contentBlock->content ?? '');
                        }
                        break;

                    case 'link':
                        if (!str_starts_with($contentBlock->content, 'http')) {
                            $contentBlock->content = $this->generateResourceUrl($contentBlock->content);
                        }
                        $this->logger->debug(sprintf(
                            '      Link URL: %s',
                            $contentBlock->content
                        ));
                        break;
                }
                if (!$skipFile) {
                    $newContentBlock = new CreateResourceContentBlockDTO(
                        null,
                        $contentBlock->resourceType,
                        $contentBlock->content ?? '',
                        $contentBlock->sortOrder,
                        null,
                        $contentBlock->fileId,
                        null,
                        $contentBlock->title ?? null,
                        $contentBlock->shortDescription ?? null
                    );
                    $outContentBlocks[] = $newContentBlock;
                }
            }
        }

        return $outContentBlocks;
    }

    /**
     * @param TagDTO[] $tags
     */
    private function processResourceTags(array $tags): array
    {
        $outTags = [];
        foreach ($tags as $tag) {
            foreach ($this->metaData->resourceTags as $resourceTag) {
                if ($tag->name === $resourceTag['name']) {
                    $this->logger->debug(sprintf('   Found matching tag: %s', $tag->name));
                    $outTags[] = $resourceTag['id'];
                    break;
                }
            }
        }

        return $outTags;
    }

    /**
     * @param CategoryDTO[] $categories
     */
    private function processResourceCategories(array $categories): array
    {
        $outCategories = [];
        foreach ($categories as $category) {
            foreach ($this->metaData->resourceCategories as $resourceCategory) {
                if ($category->name === $resourceCategory['name']) {
                    $this->logger->debug(sprintf('   Found matching category: %s', $category->name));
                    $outCategories[] = $resourceCategory['id'];
                    break;
                }
            }
        }

        return $outCategories;
    }

    /**
     * @param CategoryDTO[] $categories
     */
    private function processRoleCategories(array $categories): array
    {
        $outCategories = [];
        foreach ($categories as $category) {
            foreach ($this->metaData->employeeRoles as $role) {
                if (str_starts_with($category->name, $role['name'])) {
                    $this->logger->debug(sprintf('   Found matching category: %s', $category->name));
                    $outCategories[] = $role['id'];
                    break;
                }
            }
        }

        return $outCategories;
    }

    /**
     * @param CategoryDTO[] $categories
     */
    private function processTradeCategories(array $categories): array
    {
        $outTrades = [];
        foreach ($categories as $category) {
            foreach ($this->metaData->trades as $trade) {
                if ($category->name === $trade['name']) {
                    $this->logger->debug(sprintf('   Found matching trade: %s', $category->name));
                    $outTrades[] = $trade['id'];
                    break;
                }
            }
        }

        return $outTrades;
    }

    /**
     * Display a summary of all missing files encountered during migration.
     */
    private function displayMissingFilesSummary(): void
    {
        $this->logger->warning(sprintf(
            'Migration completed with %d missing files:',
            count($this->missingFiles)
        ));

        $this->io->section('Missing Files Summary');
        $this->io->warning(sprintf(
            'The following %d files could not be downloaded or uploaded during migration:',
            count($this->missingFiles)
        ));

        $tableData = [];
        foreach ($this->missingFiles as $filename => $details) {
            $tableData[] = [
                $filename,
                $details['type'],
                $details['resource'],
                $details['path'],
            ];
        }

        $this->io->table(
            ['Filename', 'Type', 'Resource', 'Path'],
            $tableData
        );

        $this->io->note([
            'These files were likely missing from the S3 bucket or could not be accessed.',
            'The migration continued successfully, but these resources may have incomplete content.',
            'Consider manually uploading these files or checking the source data.',
        ]);
    }

    private function flatten(array $array): array
    {
        $return = [];
        array_walk_recursive($array, function ($a) use (&$return) {
            $return[] = $a;
        });

        return $return;
    }

    /**
     * Stream entries in batches with bulk data loading to eliminate N+1 queries and reduce memory usage.
     *
     * @return \Generator<int, NewResourceMetaDataDTO[]>
     *
     * @throws Exception
     * @throws \Exception
     */
    private function streamEntriesInBatches(int $batchSize, int $startOffset = 0): \Generator
    {
        $offset = $startOffset;
        $batchNumber = intval($startOffset / $batchSize);

        while (true) {
            // Stream entries in chunks instead of loading all at once
            $entryChunk = $this->repository->getEntries()->getResourcesPaginated($offset, $batchSize);

            if (empty($entryChunk)) {
                break; // No more entries to process
            }

            $this->logger->debug(sprintf(
                'Streaming batch %d with %d entries (offset: %d)',
                $batchNumber + 1,
                count($entryChunk),
                $offset
            ));

            // Start transaction for this batch to ensure data consistency
            $connection = $this->repository->getConnection();
            $connection->beginTransaction();

            try {
                // Pre-load all related data for this batch to eliminate N+1 queries
                $elementIds = array_column($entryChunk, 'id');

                $this->logger->debug('Bulk loading related data...');
                $bulkTags = $this->tagService->getBulkTagsByElementIds($elementIds);
                $bulkTopicCategories = $this->categoryService->getBulkTopicCategoriesByElementIds($elementIds);
                $bulkRoleCategories = $this->categoryService->getBulkRoleCategoriesByElementIds($elementIds);
                $bulkTradeCategories = $this->categoryService->getBulkTradeCategoriesByElementIds($elementIds);
                $bulkFeatureImages = $this->assetService->getBulkFeatureImagesByElementIds($elementIds);
                $bulkFields = $this->elementService->getBulkFieldsByElementIds($elementIds);
                $bulkRelatedContent = $this->elementService->getBulkRelatedContentByElementIds($elementIds);

                $batchResults = [];

                // Process each entry in the batch using pre-loaded data
                foreach ($entryChunk as $entry) {
                    $this->logger->debug(sprintf('Processing entry %d with pre-loaded data', $entry->id));

                    $processedResource = $this->processResourcePageWithBulkData(
                        $entry,
                        $bulkTags[$entry->id] ?? [],
                        $bulkTopicCategories[$entry->id] ?? [],
                        $bulkRoleCategories[$entry->id] ?? [],
                        $bulkTradeCategories[$entry->id] ?? [],
                        $bulkFeatureImages[$entry->id] ?? null,
                        $bulkFields[$entry->id] ?? [],
                        $bulkRelatedContent[$entry->id] ?? []
                    );

                    // Create metadata while DTO is still available, then store in result
                    $metadata = new NewResourceMetaDataDTO(
                        resourceId: $processedResource->resourceId,
                        slug: $processedResource->createUpdateResourceDTO?->slug,
                        elementId: $processedResource->elementId,
                        relatedElementIds: $processedResource->relatedElementIds,
                        relatedResourceIds: $processedResource->createUpdateResourceDTO?->relatedResourceIds ?? []
                    );

                    // Free up memory by removing large DTO after metadata creation
                    unset($processedResource->createUpdateResourceDTO, $processedResource);

                    $batchResults[] = $metadata;
                }

                // Commit transaction for this batch
                $connection->commit();
                $this->logger->debug(sprintf('Batch %d committed successfully', $batchNumber + 1));
            } catch (\Throwable $e) {
                // Rollback transaction on any error
                $connection->rollBack();
                $this->logger->error(sprintf(
                    'Batch %d failed, transaction rolled back: %s',
                    $batchNumber + 1,
                    $e->getMessage()
                ));
                throw $e;
            }

            // Free batch data from memory immediately after processing
            unset($bulkTags, $bulkTopicCategories, $bulkRoleCategories, $bulkTradeCategories);
            unset($bulkFeatureImages, $bulkFields, $bulkRelatedContent, $entryChunk);

            yield $batchNumber => $batchResults;

            $offset += $batchSize;
            ++$batchNumber;

            // Force garbage collection periodically for better memory management
            if (0 === $batchNumber % 10) {
                gc_collect_cycles();
                $this->logger->debug('Forced garbage collection after 10 batches');
            }
        }
    }

    /**
     * Process related content using efficient O(n) hash map lookup instead of O(n²) nested loops.
     *
     * @param NewResourceMetaDataDTO[] $newResources
     */
    private function processRelatedContent(array $newResources): void
    {
        // Build lookup map first - O(n) operation
        $elementIdToResourceId = [];
        foreach ($newResources as $resource) {
            $elementIdToResourceId[$resource->elementId] = $resource->resourceId;
        }

        // Process related content with O(n) lookup - much more efficient than nested loops
        foreach ($this->io->progressIterate($newResources) as $index => $resource) {
            if (0 === count($resource->relatedElementIds)) {
                continue;
            }

            // Use hash map lookup instead of nested loop
            foreach ($resource->relatedElementIds as $relatedElementId) {
                if (isset($elementIdToResourceId[$relatedElementId])) {
                    $newResources[$index]->relatedResourceIds[] = $elementIdToResourceId[$relatedElementId];
                }
            }

            $this->logger->debug(sprintf(
                'Element ID: [%s], Related Content IDs: [%s]',
                $resource->elementId,
                implode(', ', $resource->relatedElementIds)
            ));

            $updateRelatedResourcesDTO = new UpdateRelatedResourcesDTO(
                slug: $resource->slug,
                relatedResourceIds: $resource->relatedResourceIds,
            );

            if (-1 === $this->resourceService->createOrUpdateResource($updateRelatedResourcesDTO)) {
                $this->logger->error(sprintf(
                    'Failed to update related content for element ID: [%s]',
                    $resource->elementId
                ));
            }
        }

        // Free up memory after processing
        unset($elementIdToResourceId);
    }

    /**
     * @throws \Exception
     */
    private function getEntryTypeId(string $entryTypeName): int
    {
        return match ($entryTypeName) {
            'Course', 'File', 'Guide', 'Hub Application Link', 'Resource Page', 'Series' => 1,
            'Video' => 2,
            'Podcast' => 3,
            default => throw new \Exception(sprintf('Unknown entry type name: %s', $entryTypeName)),
        };
    }
}
