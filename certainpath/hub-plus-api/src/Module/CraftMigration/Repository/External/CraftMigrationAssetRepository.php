<?php

namespace App\Module\CraftMigration\Repository\External;

use App\Module\CraftMigration\DTO\Elements\AssetDTO;
use App\Module\CraftMigration\SQL\CraftMigrationQueries;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

readonly class CraftMigrationAssetRepository
{
    public function __construct(
        private Connection $craftMigrationConnection,
    ) {
    }

    /**
     * @throws Exception
     */
    public function getAsset(int $id): AssetDTO
    {
        $assetData = $this
            ->craftMigrationConnection
            ->fetchAssociative(
                CraftMigrationQueries::ASSET,
                ['id' => $id]
            );

        return AssetDTO::fromArray($assetData);
    }

    /**
     * @throws Exception
     */
    public function getFeatureImage(int $elementId): ?AssetDTO
    {
        $assetData = $this
            ->craftMigrationConnection
            ->fetchAssociative(
                CraftMigrationQueries::FEATURE_IMAGE,
                ['elementId' => $elementId]
            );

        if (!$assetData) {
            return null;
        }

        return AssetDTO::fromArray($assetData);
    }

    /**
     * @throws Exception
     */
    public function getAssetByElementId(int $elementId): AssetDTO
    {
        $assetData = $this
            ->craftMigrationConnection
            ->fetchAssociative(
                CraftMigrationQueries::CONTENT_BLOCK_ASSETS,
                ['elementId' => $elementId]
            );

        return AssetDTO::fromArray($assetData);
    }

    /**
     * Get the database connection for bulk operations.
     */
    public function getConnection(): Connection
    {
        return $this->craftMigrationConnection;
    }
}
