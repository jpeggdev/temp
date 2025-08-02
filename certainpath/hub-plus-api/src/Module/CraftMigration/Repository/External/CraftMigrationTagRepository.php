<?php

namespace App\Module\CraftMigration\Repository\External;

use App\Module\CraftMigration\CraftMigrationConstants;
use App\Module\CraftMigration\DTO\Elements\TagDTO;
use App\Module\CraftMigration\SQL\CraftMigrationQueries;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

readonly class CraftMigrationTagRepository
{
    public function __construct(
        private Connection $craftMigrationConnection,
    ) {
    }

    /**
     * @throws Exception
     */
    public function getAllTags(): array
    {
        $tagsData = $this
            ->craftMigrationConnection
            ->fetchAllAssociative(
                CraftMigrationQueries::TAGS,
                ['tagGroupResources' => CraftMigrationConstants::TAG_GROUP_RESOURCES]
            );

        return array_map(static fn ($data) => TagDTO::fromArray($data), $tagsData);
    }

    /**
     * @throws Exception
     */
    public function getTagsByElementIdGroupId(int $elementId, int $groupId): array
    {
        $tagsData = $this
            ->craftMigrationConnection
            ->fetchAllAssociative(
                CraftMigrationQueries::ELEMENT_TAGS,
                ['groupId' => $groupId, 'elementId' => $elementId]
            );

        return array_map(static fn ($data) => TagDTO::fromArray($data), $tagsData);
    }

    /**
     * Get the database connection for bulk operations.
     */
    public function getConnection(): Connection
    {
        return $this->craftMigrationConnection;
    }
}
