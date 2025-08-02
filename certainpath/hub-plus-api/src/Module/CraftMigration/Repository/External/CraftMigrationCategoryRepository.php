<?php

namespace App\Module\CraftMigration\Repository\External;

use App\Module\CraftMigration\CraftMigrationConstants;
use App\Module\CraftMigration\DTO\Elements\CategoryDTO;
use App\Module\CraftMigration\SQL\CraftMigrationQueries;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

readonly class CraftMigrationCategoryRepository
{
    public function __construct(
        private Connection $craftMigrationConnection,
    ) {
    }

    /**
     * @return CategoryDTO[]
     *
     * @throws Exception
     */
    public function getAllCategories(): array
    {
        $categoriesData = $this
            ->craftMigrationConnection
            ->fetchAllAssociative(
                CraftMigrationQueries::CATEGORIES,
                ['categoryGroupTopics' => CraftMigrationConstants::CATEGORY_GROUP_TOPICS]
            );

        return array_map(static fn ($data) => CategoryDTO::fromArray($data), $categoriesData);
    }

    /**
     * @return CategoryDTO[]
     *
     * @throws Exception
     */
    public function getCategoriesByElementIdGroupId(int $elementId, int $groupId): array
    {
        $categoryData = $this
            ->craftMigrationConnection
            ->fetchAllAssociative(
                CraftMigrationQueries::ELEMENT_CATEGORIES,
                ['elementId' => $elementId, 'groupId' => $groupId]
            );

        return array_map(static fn ($data) => CategoryDTO::fromArray($data), $categoryData);
    }

    /**
     * Get the database connection for bulk operations.
     */
    public function getConnection(): Connection
    {
        return $this->craftMigrationConnection;
    }
}
