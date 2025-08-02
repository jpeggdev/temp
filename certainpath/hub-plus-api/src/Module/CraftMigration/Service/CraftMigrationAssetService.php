<?php

namespace App\Module\CraftMigration\Service;

use App\Module\CraftMigration\DTO\Elements\AssetDTO;
use App\Module\CraftMigration\DTO\Elements\FileDTO;
use App\Module\CraftMigration\DTO\Fields\ResourceFileDTO;
use App\Module\CraftMigration\Repository\CraftMigrationRepository;
use Doctrine\DBAL\Exception;

readonly class CraftMigrationAssetService
{
    public function __construct(
        private CraftMigrationRepository $repository,
        private string $tempDirectory,
    ) {
    }

    /**
     * @throws Exception
     */
    public function getFeatureImage(int $elementId): ?AssetDTO
    {
        return $this->repository->getAssets()->getFeatureImage($elementId);
    }

    public function getVolumeFilename(string $filename): string
    {
        return str_replace(sprintf('%s/%s', $this->tempDirectory, 'attachments'), '', $filename);
    }

    public function getFileFromAsset(AssetDTO|ResourceFileDTO $asset): FileDTO
    {
        return FileDTO::fromArray([
            'baseFilename' => basename($asset->filename),
            'localFilename' => sprintf(
                '%s/%s/%s/%s%s',
                $this->tempDirectory,
                'attachments',
                $asset->settings['customSubfolder'],
                $asset->path,
                $asset->filename
            ),
            'remoteFilename' => $asset->filename,
            'volumeFilename' => sprintf(
                '%s/%s%s',
                $asset->settings['customSubfolder'],
                $asset->path,
                $asset->filename
            ),
        ]);
    }

    /**
     * Bulk load feature images for multiple element IDs to eliminate N+1 query problem.
     *
     * @param int[] $elementIds
     *
     * @return array<int, AssetDTO|null> Array indexed by elementId containing AssetDTO objects or null
     *
     * @throws Exception
     */
    public function getBulkFeatureImagesByElementIds(array $elementIds): array
    {
        if (empty($elementIds)) {
            return [];
        }

        // Create placeholders for the IN clause
        $placeholders = str_repeat('?,', count($elementIds) - 1).'?';

        $sql = "
            SELECT
                r.sourceId as elementId,
                a.filename,
                vf.path,
                v.settings
            FROM relations r
            INNER JOIN assets a ON a.id = r.targetId
            INNER JOIN fields f ON f.id = r.fieldId
            INNER JOIN volumes v ON v.id = a.volumeId
            INNER JOIN volumefolders vf ON vf.id = a.folderId
            WHERE f.handle = 'featureImage'
            AND r.sourceId IN ($placeholders)
        ";

        $results = $this->repository->getAssets()->getConnection()->fetchAllAssociative($sql, $elementIds);

        // Group results by elementId
        $grouped = [];
        foreach ($results as $row) {
            $elementId = (int) $row['elementId'];
            $grouped[$elementId] = AssetDTO::fromArray($row);
        }

        // Ensure all requested elementIds have an entry (even if null)
        foreach ($elementIds as $elementId) {
            if (!isset($grouped[$elementId])) {
                $grouped[$elementId] = null;
            }
        }

        return $grouped;
    }
}
