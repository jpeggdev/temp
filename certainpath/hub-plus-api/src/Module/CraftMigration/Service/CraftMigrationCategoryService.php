<?php

namespace App\Module\CraftMigration\Service;

use App\DTO\Request\ResourceCategory\CreateUpdateResourceCategoryDTO;
use App\Exception\CreateUpdateResourceCategoryException;
use App\Module\CraftMigration\CraftMigrationConstants;
use App\Module\CraftMigration\DTO\Elements\CategoryDTO;
use App\Module\CraftMigration\Repository\CraftMigrationRepository;
use App\Service\ResourceCategory\CreateResourceCategoryService;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;

readonly class CraftMigrationCategoryService
{
    public function __construct(
        private CraftMigrationRepository $repository,
        private CreateResourceCategoryService $resourceCategoryService,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Imports categories from Craft CMS to HubPlus.
     *
     * @return ?array The updated categories with database ids or null if no categories were found
     *
     * @throws Exception
     */
    public function importCategories(): ?array
    {
        $this->logger->info('Starting import of categories from Craft CMS...');
        $categories = $this->getCategoriesFromCraft();
        if (empty($categories)) {
            $this->logger->error('No categories found in Craft CMS to import.');

            return null;
        }

        $this->logger->debug(sprintf('   Found %d categories in Craft CMS to import.', count($categories)));

        return $this->saveCategoriesToHubPlus($categories);
    }

    /**
     * Fetches categories from Craft CMS.
     *
     * @return CategoryDTO[]
     *
     * @throws Exception
     */
    private function getCategoriesFromCraft(): array
    {
        return $this->repository->getCategories()->getAllCategories();
    }

    /**
     * Saves categories to HubPlus and returns the updated categories with database ids.
     *
     * @param CategoryDTO[] $categories
     *
     * @return CategoryDTO[] The updated categories with database ids
     */
    private function saveCategoriesToHubPlus(array $categories): array
    {
        $newCategories = [];
        foreach ($categories as $category) {
            try {
                $newCategory = new CreateUpdateResourceCategoryDTO($category->name);
                $resourceCategoryResponse = $this->resourceCategoryService->createCategory($newCategory, true);
            } catch (CreateUpdateResourceCategoryException $e) {
                $this->logger->error(sprintf('Error creating/updating category: %s', $e->getMessage()));
                continue;
            }
            $newCategories[] = new CategoryDTO($resourceCategoryResponse->id, $resourceCategoryResponse->name);
        }

        return $newCategories;
    }

    /**
     * Fetches all trade categories for an [[Element]] from Craft CMS.
     *
     * @return CategoryDTO[]
     *
     * @throws Exception
     */
    public function getTradeCategoriesByElementId(int $elementId): array
    {
        return $this->repository->getCategories()->getCategoriesByElementIdGroupId($elementId, CraftMigrationConstants::CATEGORY_GROUP_TRADES);
    }

    /**
     * Fetches all role categories for an [[Element]] from Craft CMS.
     *
     * @return CategoryDTO[]
     *
     * @throws Exception
     */
    public function getRoleCategoriesByElementId(int $elementId): array
    {
        return $this->repository->getCategories()->getCategoriesByElementIdGroupId($elementId, CraftMigrationConstants::CATEGORY_GROUP_ROLES);
    }

    /**
     * Fetches all topic categories for an [[Element]] from Craft CMS.
     *
     * @return CategoryDTO[]
     *
     * @throws Exception
     */
    public function getTopicCategoriesByElementId(int $elementId): array
    {
        return $this->repository->getCategories()->getCategoriesByElementIdGroupId($elementId, CraftMigrationConstants::CATEGORY_GROUP_TOPICS);
    }

    /**
     * Bulk load trade categories for multiple element IDs to eliminate N+1 query problem.
     *
     * @param int[] $elementIds
     *
     * @return array<int, CategoryDTO[]> Array indexed by elementId containing arrays of CategoryDTO objects
     *
     * @throws Exception
     */
    public function getBulkTradeCategoriesByElementIds(array $elementIds): array
    {
        return $this->getBulkCategoriesByElementIds($elementIds, CraftMigrationConstants::CATEGORY_GROUP_TRADES);
    }

    /**
     * Bulk load role categories for multiple element IDs to eliminate N+1 query problem.
     *
     * @param int[] $elementIds
     *
     * @return array<int, CategoryDTO[]> Array indexed by elementId containing arrays of CategoryDTO objects
     *
     * @throws Exception
     */
    public function getBulkRoleCategoriesByElementIds(array $elementIds): array
    {
        return $this->getBulkCategoriesByElementIds($elementIds, CraftMigrationConstants::CATEGORY_GROUP_ROLES);
    }

    /**
     * Bulk load topic categories for multiple element IDs to eliminate N+1 query problem.
     *
     * @param int[] $elementIds
     *
     * @return array<int, CategoryDTO[]> Array indexed by elementId containing arrays of CategoryDTO objects
     *
     * @throws Exception
     */
    public function getBulkTopicCategoriesByElementIds(array $elementIds): array
    {
        return $this->getBulkCategoriesByElementIds($elementIds, CraftMigrationConstants::CATEGORY_GROUP_TOPICS);
    }

    /**
     * Generic bulk loading method for categories by group ID.
     *
     * @param int[] $elementIds
     *
     * @return array<int, CategoryDTO[]> Array indexed by elementId containing arrays of CategoryDTO objects
     *
     * @throws Exception
     */
    private function getBulkCategoriesByElementIds(array $elementIds, int $groupId): array
    {
        if (empty($elementIds)) {
            return [];
        }

        // Create placeholders for the IN clause
        $placeholders = str_repeat('?,', count($elementIds) - 1).'?';

        $sql = "
            SELECT
                r.sourceId as elementId,
                c2.id,
                c.title as name
            FROM relations r
            INNER JOIN categories c2 ON c2.id = r.targetId
            INNER JOIN content c ON r.targetId = c.elementId
            WHERE c2.groupId = ?
            AND r.sourceId IN ($placeholders)
        ";

        $params = [$groupId, ...$elementIds];
        $results = $this->repository->getCategories()->getConnection()->fetchAllAssociative($sql, $params);

        // Group results by elementId
        $grouped = [];
        foreach ($results as $row) {
            $elementId = (int) $row['elementId'];
            if (!isset($grouped[$elementId])) {
                $grouped[$elementId] = [];
            }
            $grouped[$elementId][] = CategoryDTO::fromArray($row);
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
