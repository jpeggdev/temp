<?php

namespace App\Module\CraftMigration\Service;

use App\DTO\Request\ResourceTag\CreateUpdateResourceTagDTO;
use App\Exception\CreateUpdateResourceTagException;
use App\Module\CraftMigration\CraftMigrationConstants;
use App\Module\CraftMigration\DTO\Elements\TagDTO;
use App\Module\CraftMigration\Repository\CraftMigrationRepository;
use App\Service\ResourceTag\CreateResourceTagService;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;

readonly class CraftMigrationTagService
{
    public function __construct(
        private CraftMigrationRepository $repository,
        private CreateResourceTagService $resourceTagService,
        private LoggerInterface $logger,
    ) {
    }

    public function importTags(): array
    {
        $this->logger->info('Starting import of tags from Craft CMS...');
        try {
            $tags = $this->getTagsFromCraft();
        } catch (Exception $e) {
            $this->logger->error(sprintf('   Error fetching tags from Craft: %s', $e->getMessage()));

            return [];
        }

        if (empty($tags)) {
            $this->logger->warning('   No tags found in Craft.');

            return [];
        }

        $this->logger->debug(sprintf('   Found %s tags in Craft.', count($tags)));

        return $this->saveTagsToHubPlus($tags);
    }

    /**
     * @throws Exception
     */
    private function getTagsFromCraft(): array
    {
        return $this->repository->getTags()->getAllTags();
    }

    /**
     * Saves tags to HubPlus and returns the updated tags with database ids.
     *
     * @param TagDTO[] $tags
     *
     * @return array The updated tags with database ids
     */
    private function saveTagsToHubPlus(array $tags): array
    {
        $newTags = [];
        foreach ($tags as $tag) {
            try {
                $newTag = new CreateUpdateResourceTagDTO($tag->name);
                $resourceTagResponse = $this->resourceTagService->createTag($newTag, true);
            } catch (CreateUpdateResourceTagException $e) {
                $this->logger->error(sprintf('   Error creating/updating tag: %s', $e->getMessage()));
                continue;
            }
            $newTag = new TagDTO($resourceTagResponse->id, $resourceTagResponse->name);
            $newTags[] = $newTag;
        }

        return $newTags;
    }

    /**
     * @throws Exception
     */
    public function getTagsByElementId(int $elementId): array
    {
        return $this->repository->getTags()->getTagsByElementIdGroupId($elementId, CraftMigrationConstants::TAG_GROUP_RESOURCES);
    }

    /**
     * Bulk load tags for multiple element IDs to eliminate N+1 query problem.
     *
     * @param int[] $elementIds
     *
     * @return array<int, TagDTO[]> Array indexed by elementId containing arrays of TagDTO objects
     *
     * @throws Exception
     */
    public function getBulkTagsByElementIds(array $elementIds): array
    {
        if (empty($elementIds)) {
            return [];
        }

        // Create placeholders for the IN clause
        $placeholders = str_repeat('?,', count($elementIds) - 1).'?';

        $sql = "
            SELECT 
                e.id as elementId,
                t.id,
                co.title as name
            FROM tags t
            INNER JOIN relations r ON r.targetId = t.id
            INNER JOIN elements e ON e.id = r.sourceId
            INNER JOIN content co ON co.elementId = r.targetId
            WHERE t.groupId = ?
            AND e.id IN ($placeholders)
        ";

        $params = [CraftMigrationConstants::TAG_GROUP_RESOURCES, ...$elementIds];
        $results = $this->repository->getTags()->getConnection()->fetchAllAssociative($sql, $params);

        // Group results by elementId
        $grouped = [];
        foreach ($results as $row) {
            $elementId = (int) $row['elementId'];
            if (!isset($grouped[$elementId])) {
                $grouped[$elementId] = [];
            }
            $grouped[$elementId][] = TagDTO::fromArray($row);
        }

        // Ensure all requested elementIds have an entry (even if empty)
        foreach ($elementIds as $elementId) {
            if (!isset($grouped[$elementId])) {
                $grouped[$elementId] = [];
            }
        }

        return $grouped;
    }
}
