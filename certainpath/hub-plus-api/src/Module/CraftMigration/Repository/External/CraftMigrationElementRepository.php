<?php

namespace App\Module\CraftMigration\Repository\External;

use App\Module\CraftMigration\CraftMigrationConstants;
use App\Module\CraftMigration\DTO\Fields\FieldDTO;
use App\Module\CraftMigration\DTO\Fields\ResourceFileDTO;
use App\Module\CraftMigration\DTO\Fields\SeriesEntriesDTO;
use App\Module\CraftMigration\SQL\CraftMigrationQueries;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

readonly class CraftMigrationElementRepository
{
    public function __construct(
        private Connection $craftMigrationConnection,
    ) {
    }

    /**
     * @throws Exception
     */
    public function getRelatedElementsByElementId(int $elementId): array
    {
        return $this
            ->craftMigrationConnection
            ->fetchAllAssociative(
                CraftMigrationQueries::ELEMENT_RELATED_CONTENT,
                [
                    'elementId' => $elementId,
                    'fieldFeatureImage' => CraftMigrationConstants::FIELD_FEATURE_IMAGE,
                ]
            );
    }

    /**
     * Returns fields for a given element ID.
     *
     * @return FieldDTO[]
     *
     * @throws Exception
     */
    public function getFields(int $elementId): array
    {
        $fieldsData = $this
            ->craftMigrationConnection
            ->fetchAllAssociative(
                CraftMigrationQueries::ELEMENT_FIELDS,
                [
                    'elementId' => $elementId,
                    'fieldContentBlocks' => CraftMigrationConstants::FIELD_CONTENT_BLOCKS,
                    'fieldResourceFile' => CraftMigrationConstants::FIELD_RESOURCE_FILE,
                    'fieldSeriesEntries' => CraftMigrationConstants::FIELD_SERIES_ENTRIES,
                ]
            );

        return array_map(fn (array $data) => FieldDTO::fromArray($data), $fieldsData);
    }

    /**
     * Returns series entries for a given element ID.
     *
     * @return ?SeriesEntriesDTO[]
     *
     * @throws Exception
     */
    public function getSeriesEntries(int $elementId): ?array
    {
        $seriesEntriesData = $this
            ->craftMigrationConnection
            ->fetchAllAssociative(
                CraftMigrationQueries::ELEMENT_SERIES_ENTRIES,
                [
                    'elementId' => $elementId,
                    'fieldSeriesEntries' => CraftMigrationConstants::FIELD_SERIES_ENTRIES,
                ]
            );

        if (!$seriesEntriesData) {
            return null;
        }

        return array_map(fn (array $data) => SeriesEntriesDTO::fromArray($data), $seriesEntriesData);
    }

    /**
     * Returns the resource file for a given element ID.
     *
     * @throws Exception
     */
    public function getResourceFile(int $elementId): ?ResourceFileDTO
    {
        $fileData = $this
            ->craftMigrationConnection
            ->fetchAssociative(
                CraftMigrationQueries::ELEMENT_RESOURCE_FILE,
                [
                    'elementId' => $elementId,
                    'fieldResourceFile' => CraftMigrationConstants::FIELD_RESOURCE_FILE,
                ]
            );

        if (!$fileData) {
            return null;
        }

        return ResourceFileDTO::fromArray($fileData);
    }

    /**
     * Get the database connection for bulk operations.
     */
    public function getConnection(): Connection
    {
        return $this->craftMigrationConnection;
    }
}
