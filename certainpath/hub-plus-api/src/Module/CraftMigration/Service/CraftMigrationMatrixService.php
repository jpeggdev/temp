<?php

namespace App\Module\CraftMigration\Service;

use App\Module\CraftMigration\DTO\Elements\AssetDTO;
use App\Module\CraftMigration\DTO\MatrixBlocks\BaseContentBlockDTO;
use App\Module\CraftMigration\DTO\MatrixBlocks\ColumnContentDTO;
use App\Module\CraftMigration\DTO\MatrixBlocks\EmbedDTO;
use App\Module\CraftMigration\DTO\MatrixBlocks\EntryCardDTO;
use App\Module\CraftMigration\DTO\MatrixBlocks\HeadingDTO;
use App\Module\CraftMigration\DTO\MatrixBlocks\ImageDTO;
use App\Module\CraftMigration\DTO\MatrixBlocks\QuoteDTO;
use App\Module\CraftMigration\DTO\MatrixBlocks\RawHTMLDTO;
use App\Module\CraftMigration\DTO\MatrixBlocks\ResourceCourseDTO;
use App\Module\CraftMigration\DTO\MatrixBlocks\ResourceFileDTO;
use App\Module\CraftMigration\DTO\MatrixBlocks\ResourcePageDTO;
use App\Module\CraftMigration\DTO\MatrixBlocks\ResourceSeriesDTO;
use App\Module\CraftMigration\DTO\MatrixBlocks\ResourceVideoDTO;
use App\Module\CraftMigration\DTO\MatrixBlocks\ResourceYoutubeVideoDTO;
use App\Module\CraftMigration\DTO\MatrixBlocks\RichTextDTO;
use App\Module\CraftMigration\Repository\CraftMigrationRepository;
use App\Module\CraftMigration\SQL\CraftMigrationQueries;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;

readonly class CraftMigrationMatrixService
{
    public function __construct(
        private CraftMigrationRepository $repository,
        private CraftMigrationCategoryService $categoryService,
        private CraftMigrationAssetService $assetService,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @var array<string, array{class: class-string<BaseContentBlockDTO>, hasTradeCategories: bool}>
     */
    private const array BLOCK_TYPE_MAPPINGS = [
        'richText' => [
            'class' => RichTextDTO::class,
            'hasTradeCategories' => true,
            'sql' => CraftMigrationQueries::MATRIX_BLOCK_RICH_TEXT,
        ],
        'resourceCourse' => [
            'class' => ResourceCourseDTO::class,
            'hasTradeCategories' => true,
            'sql' => CraftMigrationQueries::MATRIX_BLOCK_RESOURCE_COURSE,
        ],
        'resourceFile' => [
            'class' => ResourceFileDTO::class,
            'hasTradeCategories' => true,
            'sql' => CraftMigrationQueries::MATRIX_BLOCK_RESOURCE_FILE,
        ],
        'resourcePage' => [
            'class' => ResourcePageDTO::class,
            'hasTradeCategories' => true,
            'sql' => CraftMigrationQueries::MATRIX_BLOCK_RESOURCE_PAGE,
        ],
        'resourceVideo' => [
            'class' => ResourceVideoDTO::class,
            'hasTradeCategories' => true,
            'sql' => CraftMigrationQueries::MATRIX_BLOCK_RESOURCE_VIDEO,
        ],
        'heading' => [
            'class' => HeadingDTO::class,
            'hasTradeCategories' => false,
            'sql' => CraftMigrationQueries::MATRIX_BLOCK_HEADING,
        ],
        'resourceSeries' => [
            'class' => ResourceSeriesDTO::class,
            'hasTradeCategories' => true,
            'sql' => CraftMigrationQueries::MATRIX_BLOCK_RESOURCE_SERIES,
        ],
        'quote' => [
            'class' => QuoteDTO::class,
            'hasTradeCategories' => true,
            'sql' => CraftMigrationQueries::MATRIX_BLOCK_QUOTE,
        ],
        'rawHtml' => [
            'class' => RawHTMLDTO::class,
            'hasTradeCategories' => false,
            'sql' => CraftMigrationQueries::MATRIX_BLOCK_RAW_HTML,
        ],
        'image' => [
            'class' => ImageDTO::class,
            'hasTradeCategories' => false,
            'sql' => CraftMigrationQueries::MATRIX_BLOCK_IMAGE,
        ],
        'embed' => [
            'class' => EmbedDTO::class,
            'hasTradeCategories' => false,
            'sql' => CraftMigrationQueries::MATRIX_BLOCK_EMBED,
        ],
        'columnContent' => [
            'class' => ColumnContentDTO::class,
            'hasTradeCategories' => false,
            'sql' => CraftMigrationQueries::MATRIX_BLOCK_COLUMN_CONTENT,
        ],
        'entryCards' => [
            'class' => EntryCardDTO::class,
            'hasTradeCategories' => false,
            'sql' => CraftMigrationQueries::MATRIX_BLOCK_ENTRY_CARD,
        ],
        'resourceYoutubeVideo' => [
            'class' => ResourceYoutubeVideoDTO::class,
            'hasTradeCategories' => true,
            'sql' => CraftMigrationQueries::MATRIX_BLOCK_RESOURCE_YOUTUBE,
        ],
    ];

    /**
     * @return BaseContentBlockDTO[]
     *
     * @throws Exception
     */
    public function getMatrixBlocks(int $elementId): array
    {
        $contentBlocks = [];
        $matrixBlocksContent = $this->repository->getMatrices()->getMatrixBlocksContentByOwner($elementId);

        foreach ($matrixBlocksContent as $matrixBlockData) {
            $contentBlock = $this->createContentBlock($matrixBlockData, $elementId);
            if (null !== $contentBlock) {
                $contentBlocks[] = $contentBlock;
            }
        }

        return $contentBlocks;
    }

    /**
     * Create a content block DTO from matrix block data.
     *
     * @throws Exception
     */
    private function createContentBlock(array $matrixBlockData, int $elementId): mixed
    {
        $typeName = $matrixBlockData['typeHandle'];
        $matrixBlockId = $matrixBlockData['id'];

        if (!isset(self::BLOCK_TYPE_MAPPINGS[$typeName])) {
            $this->logger->warning(
                sprintf(
                    'Unknown matrix block type: %s for element ID: %s',
                    $typeName,
                    $elementId
                )
            );

            return null;
        }

        $mapping = self::BLOCK_TYPE_MAPPINGS[$typeName];
        $detailedMatrixBlockData = $this->repository->getMatrices()->getMatrixBlockContent(
            $matrixBlockId,
            $mapping['sql']
        );
        if (
            'file' === $detailedMatrixBlockData['resourceType']
            || 'image' === $detailedMatrixBlockData['resourceType']
        ) {
            $asset = AssetDTO::fromArray($detailedMatrixBlockData);
            $detailedMatrixBlockData['content'] = $this->assetService->getFileFromAsset($asset)->localFilename;
        }
        $enrichedData = $this->enrichMatrixBlockContent(
            $detailedMatrixBlockData,
            $matrixBlockId,
            $mapping['hasTradeCategories']
        );

        $dtoClass = $mapping['class'];

        return $dtoClass::fromArray($enrichedData);
    }

    /**
     * Enrich matrix block content with trade categories if needed.
     *
     * @throws Exception
     */
    private function enrichMatrixBlockContent(
        array $matrixBlockData,
        int $matrixBlockId,
        bool $hasTradeCategories = true,
    ): array {
        if ($hasTradeCategories) {
            $matrixBlockData['tradeCategories'] = $this->categoryService->getTradeCategoriesByElementId($matrixBlockId);
        }

        return $matrixBlockData;
    }
}
