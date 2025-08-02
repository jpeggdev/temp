<?php

namespace App\Module\CraftMigration\Service;

use Doctrine\DBAL\Exception;

readonly class CraftMigrationService
{
    public function __construct(
        private CraftMigrationResourcePageService $resourcePageService,
        private CraftMigrationCategoryService $categoryService,
        private CraftMigrationTagService $tagService,
    ) {
    }

    /**
     * @throws Exception
     */
    public function importContentFromCraftDatabase(
        bool $skipCategories = false,
        bool $skipTags = false,
        ?int $batchSize = null,
        bool $resume = false,
    ): void {
        if (!$skipCategories) {
            $this->categoryService->importCategories();
        }
        if (!$skipTags) {
            $this->tagService->importTags();
        }
        $this->resourcePageService->processResourcePageElements($batchSize, $resume);
    }
}
