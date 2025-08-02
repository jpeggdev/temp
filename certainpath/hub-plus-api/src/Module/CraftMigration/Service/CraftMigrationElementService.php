<?php

namespace App\Module\CraftMigration\Service;

use App\Module\CraftMigration\CraftMigrationConstants;
use App\Module\CraftMigration\DTO\Fields\FieldDTO;
use App\Module\CraftMigration\DTO\Fields\ResourceFileDTO;
use App\Module\CraftMigration\DTO\Fields\SeriesEntriesDTO;
use App\Module\CraftMigration\Repository\CraftMigrationRepository;
use Doctrine\DBAL\Exception;

readonly class CraftMigrationElementService
{
    public function __construct(
        private CraftMigrationRepository $repository,
    ) {
    }

    /**
     * @throws Exception
     */
    public function getRelatedContentByElementId(int $elementId): array
    {
        return $this->repository->getElements()->getRelatedElementsByElementId($elementId);
    }

    /**
     * Fetches fields for a given element ID.
     *
     * @return FieldDTO[]
     *
     * @throws Exception
     */
    public function getFields(int $elementId): array
    {
        return $this->repository->getElements()->getFields($elementId);
    }

    /**
     * Fetches series entries for a given element ID.
     *
     * @return ?SeriesEntriesDTO[]
     *
     * @throws Exception
     */
    public function getSeriesEntries(int $elementId): ?array
    {
        return $this->repository->getElements()->getSeriesEntries($elementId);
    }

    /**
     * Fetches a resource file for a given element ID.
     *
     * @throws Exception
     */
    public function getResourceFile(int $elementId): ?ResourceFileDTO
    {
        return $this->repository->getElements()->getResourceFile($elementId);
    }

    /**
     * Bulk load fields for multiple element IDs to eliminate N+1 query problem.
     *
     * @param int[] $elementIds
     *
     * @return array<int, FieldDTO[]> Array indexed by elementId containing arrays of FieldDTO objects
     *
     * @throws Exception
     */
    public function getBulkFieldsByElementIds(array $elementIds): array
    {
        if (empty($elementIds)) {
            return [];
        }

        // Create placeholders for the IN clause
        $placeholders = str_repeat('?,', count($elementIds) - 1).'?';

        $fieldIds = CraftMigrationConstants::FIELD_CONTENT_BLOCKS.', '.CraftMigrationConstants::FIELD_RESOURCE_FILE.', '.CraftMigrationConstants::FIELD_SERIES_ENTRIES;
        $sql = "
            SELECT
                e.id as elementId,
                f.id,
                f.handle as handle,
                f.name,
                flf.sortOrder
            FROM elements e
            INNER JOIN fieldlayoutfields flf on e.fieldLayoutId = flf.layoutId
            INNER JOIN fields f on flf.fieldId = f.id
            WHERE e.id IN ($placeholders)
            AND f.id in ($fieldIds)
            ORDER BY e.id, flf.sortOrder
        ";

        $results = $this->repository->getElements()->getConnection()->fetchAllAssociative($sql, $elementIds);

        // Group results by elementId
        $grouped = [];
        foreach ($results as $row) {
            $elementId = (int) $row['elementId'];
            if (!isset($grouped[$elementId])) {
                $grouped[$elementId] = [];
            }
            $grouped[$elementId][] = FieldDTO::fromArray($row);
        }

        // Ensure all requested elementIds have an entry (even if empty)
        foreach ($elementIds as $elementId) {
            if (!isset($grouped[$elementId])) {
                $grouped[$elementId] = [];
            }
        }

        return $grouped;
    }

    /**
     * Bulk load related content for multiple element IDs to eliminate N+1 query problem.
     *
     * @param int[] $elementIds
     *
     * @return array<int, array> Array indexed by elementId containing arrays of related element data
     *
     * @throws Exception
     */
    public function getBulkRelatedContentByElementIds(array $elementIds): array
    {
        if (empty($elementIds)) {
            return [];
        }

        // Create placeholders for the IN clause
        $placeholders = str_repeat('?,', count($elementIds) - 1).'?';

        $fieldId = CraftMigrationConstants::FIELD_FEATURE_IMAGE;
        $sql = "
            SELECT
                r.sourceId as elementId,
                r.targetId
            FROM relations r
            WHERE r.sourceId IN ($placeholders)
            AND r.fieldId = $fieldId
        ";

        $results = $this->repository->getElements()->getConnection()->fetchAllAssociative($sql, $elementIds);

        // Group results by elementId
        $grouped = [];
        foreach ($results as $row) {
            $elementId = (int) $row['elementId'];
            if (!isset($grouped[$elementId])) {
                $grouped[$elementId] = [];
            }
            $grouped[$elementId][] = ['elementId' => $row['targetId']];
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
