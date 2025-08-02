<?php

namespace App\Module\CraftMigration\Repository;

use App\Module\CraftMigration\Repository\External\CraftMigrationAssetRepository;
use App\Module\CraftMigration\Repository\External\CraftMigrationCategoryRepository;
use App\Module\CraftMigration\Repository\External\CraftMigrationElementRepository;
use App\Module\CraftMigration\Repository\External\CraftMigrationEntryRepository;
use App\Module\CraftMigration\Repository\External\CraftMigrationMatrixRepository;
use App\Module\CraftMigration\Repository\External\CraftMigrationTagRepository;
use Doctrine\DBAL\Connection;

class CraftMigrationRepository
{
    private CraftMigrationAssetRepository $assets;
    private CraftMigrationCategoryRepository $categories;
    private CraftMigrationEntryRepository $entries;
    private CraftMigrationMatrixRepository $matrices;
    private CraftMigrationElementRepository $elements;
    private CraftMigrationTagRepository $tags;

    public function __construct(
        private readonly Connection $craftMigrationConnection,
    ) {
    }

    public function getAssets(): CraftMigrationAssetRepository
    {
        return $this->assets ??= new CraftMigrationAssetRepository($this->craftMigrationConnection);
    }

    public function getCategories(): CraftMigrationCategoryRepository
    {
        return $this->categories ??= new CraftMigrationCategoryRepository($this->craftMigrationConnection);
    }

    public function getEntries(): CraftMigrationEntryRepository
    {
        return $this->entries ??= new CraftMigrationEntryRepository($this->craftMigrationConnection);
    }

    public function getMatrices(): CraftMigrationMatrixRepository
    {
        return $this->matrices ??= new CraftMigrationMatrixRepository($this->craftMigrationConnection);
    }

    public function getElements(): CraftMigrationElementRepository
    {
        return $this->elements ??= new CraftMigrationElementRepository($this->craftMigrationConnection);
    }

    public function getTags(): CraftMigrationTagRepository
    {
        return $this->tags ??= new CraftMigrationTagRepository($this->craftMigrationConnection);
    }

    /**
     * Get the database connection for transaction management.
     */
    public function getConnection(): Connection
    {
        return $this->craftMigrationConnection;
    }
}
