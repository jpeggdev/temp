<?php

namespace App\Module\CraftMigration\Repository\External;

use App\Module\CraftMigration\CraftMigrationConstants;
use App\Module\CraftMigration\SQL\CraftMigrationQueries;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

readonly class CraftMigrationMatrixRepository
{
    public function __construct(
        private Connection $craftMigrationConnection,
    ) {
    }

    /**
     * Get content for a single matrix block by its ID.
     *
     * @throws Exception
     */
    public function getMatrixBlockContent(int $matrixBlockId, string $sql): array
    {
        $parameters = ['elementId' => $matrixBlockId];

        // Add specific parameters for queries that need them
        if (str_contains($sql, ':matrixFieldResourceFile')) {
            $parameters['matrixFieldResourceFile'] = CraftMigrationConstants::MATRIX_FIELD_RESOURCE_FILE;
        }

        return $this
            ->craftMigrationConnection
            ->fetchAssociative($sql, $parameters);
    }

    /**
     * Get all matrix block content for a specific owner element.
     *
     * @return array[]
     *
     * @throws Exception
     */
    public function getMatrixBlocksContentByOwner(int $elementId): array
    {
        return $this
            ->craftMigrationConnection
            ->fetchAllAssociative(
                CraftMigrationQueries::MATRIX_BLOCKS_CONTENT_BY_OWNER,
                [
                    'elementId' => $elementId,
                    'matrixBlockTypes' => CraftMigrationConstants::MATRIX_BLOCK_TYPES,
                ],
                [
                    'matrixBlockTypes' => Connection::PARAM_INT_ARRAY,
                ]
            );
    }
}
