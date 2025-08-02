<?php

namespace App\Repository;

use App\Entity\File;
use App\Entity\FilesystemNode;
use App\Entity\Folder;
use App\Module\Hub\Feature\FileManagement\DTO\Request\ListFolderContentsRequestDTO;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FilesystemNode>
 */
class FilesystemNodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FilesystemNode::class);
    }

    public function findOneByUuid(string $uuid): ?FilesystemNode
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }

    public function getFolderContents(?Folder $folder, ListFolderContentsRequestDTO $dto): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $whereClause = $folder ? 'f.parent_id = :folderId' : 'f.parent_id IS NULL';
        $params = [];

        if ($folder) {
            $params['folderId'] = $folder->getId();
        }

        if (!empty($dto->searchTerm)) {
            $whereClause .= " AND f.search_vector @@ websearch_to_tsquery('english', :searchTerm)";
            $params['searchTerm'] = $dto->searchTerm;
        }

        // Add file types filter
        if (!empty($dto->fileTypes)) {
            $fileTypePlaceholders = [];
            foreach ($dto->fileTypes as $index => $fileType) {
                $paramName = "fileType{$index}";
                $fileTypePlaceholders[] = ":{$paramName}";
                $params[$paramName] = $fileType;
            }
            $whereClause .= ' AND f.file_type IN ('.implode(', ', $fileTypePlaceholders).')';
        }

        // Add tags filter
        if (!empty($dto->tags)) {
            $tagPlaceholders = [];
            foreach ($dto->tags as $index => $tagId) {
                $paramName = "tagId{$index}";
                $tagPlaceholders[] = ":{$paramName}";
                $params[$paramName] = $tagId;
            }

            // Join with tag mappings
            $whereClause .= ' AND f.id IN (
            SELECT fsntm.file_system_node_id
            FROM file_system_node_tag_mapping fsntm
            WHERE fsntm.file_system_node_tag_id IN ('.implode(', ', $tagPlaceholders).')
        )';
        }

        // Process cursor if provided
        if ($dto->cursor) {
            try {
                $cursorData = json_decode(base64_decode($dto->cursor), true);
                if (is_array($cursorData) && isset($cursorData['uuid'])) {
                    $cursorConditions = $this->buildCursorCondition($cursorData, $dto);
                    $whereClause .= ' AND '.$cursorConditions['condition'];
                    $params = array_merge($params, $cursorConditions['params']);
                }
            } catch (\Exception $e) {
                // Invalid cursor - just ignore it
            }
        }

        $sortColumn = $this->getSortColumn($dto->sortBy);

        $sql = "
        SELECT f.*
        FROM filesystem_node f
        WHERE $whereClause
        ORDER BY
            CASE WHEN f.type = 'folder' THEN 0 ELSE 1 END,
            $sortColumn {$dto->sortOrder},
            f.uuid ASC  -- Always include UUID as final tie-breaker
        LIMIT {$dto->limit}
    ";

        $stmt = $conn->prepare($sql);

        // Bind all parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $rawResults = $stmt->executeQuery()->fetchAllAssociative();

        // Total count query - unaffected by cursor
        $countSql = '
        SELECT COUNT(*) as count
        FROM filesystem_node f
        WHERE '.($folder ? 'f.parent_id = :folderId' : 'f.parent_id IS NULL');

        if (!empty($dto->searchTerm)) {
            $countSql .= " AND f.search_vector @@ websearch_to_tsquery('english', :searchTerm)";
        }

        // Add file types filter to count query
        if (!empty($dto->fileTypes)) {
            $fileTypePlaceholders = [];
            foreach ($dto->fileTypes as $index => $fileType) {
                $paramName = "fileType{$index}";
                $fileTypePlaceholders[] = ":{$paramName}";
            }
            $countSql .= ' AND f.file_type IN ('.implode(', ', $fileTypePlaceholders).')';
        }

        // Add tags filter to count query
        if (!empty($dto->tags)) {
            $tagPlaceholders = [];
            foreach ($dto->tags as $index => $tagId) {
                $paramName = "tagId{$index}";
                $tagPlaceholders[] = ":{$paramName}";
            }

            $countSql .= ' AND f.id IN (
            SELECT fsntm.file_system_node_id
            FROM file_system_node_tag_mapping fsntm
            WHERE fsntm.file_system_node_tag_id IN ('.implode(', ', $tagPlaceholders).')
        )';
        }

        $countStmt = $conn->prepare($countSql);

        if ($folder) {
            $countStmt->bindValue('folderId', $folder->getId());
        }
        if (!empty($dto->searchTerm)) {
            $countStmt->bindValue('searchTerm', $dto->searchTerm);
        }

        // Bind file type parameters for count query
        if (!empty($dto->fileTypes)) {
            foreach ($dto->fileTypes as $index => $fileType) {
                $paramName = "fileType{$index}";
                $countStmt->bindValue($paramName, $fileType);
            }
        }

        // Bind tag parameters for count query
        if (!empty($dto->tags)) {
            foreach ($dto->tags as $index => $tagId) {
                $paramName = "tagId{$index}";
                $countStmt->bindValue($paramName, $tagId);
            }
        }

        $count = (int) $countStmt->executeQuery()->fetchOne();
        $entityManager = $this->getEntityManager();
        $items = [];

        foreach ($rawResults as $rawResult) {
            $items[] = $entityManager->find(
                'folder' === $rawResult['type'] ? Folder::class : File::class,
                $rawResult['id']
            );
        }

        // Calculate if there are more items - UPDATED
        // Using total count comparison instead of just checking against limit
        $hasMore = count($items) < $count && count($items) > 0;

        // Generate nextCursor if there are more items - UPDATED
        // Always generate a cursor if there are items and more to fetch
        $nextCursor = null;
        if (!empty($items) && $hasMore) {
            $lastItem = end($items);
            $cursorData = [
                'uuid' => $lastItem->getUuid(),
                'nodeType' => $lastItem instanceof Folder ? 0 : 1,
                'sortValue' => $this->extractSortValue($lastItem, $dto->sortBy),
            ];
            $nextCursor = base64_encode(json_encode($cursorData));
        }

        return [
            'items' => $items,
            'total' => $count,
            'hasMore' => $hasMore,
            'nextCursor' => $nextCursor,
        ];
    }

    private function buildCursorCondition(array $cursor, ListFolderContentsRequestDTO $dto): array
    {
        $sortColumn = $this->getSortColumn($dto->sortBy);
        $isAscending = 'ASC' === $dto->sortOrder;
        $comparisonOp = $isAscending ? '>' : '<';

        // For the uuid tie-breaker, we should use the same direction as the main sort
        $uuidComparisonOp = $isAscending ? '>' : '<';

        // FIXED: The comparison for node type should always be the same regardless of sort direction,
        // because we always want folders (type 0) before files (type 1)
        $condition = "(
            (CASE WHEN f.type = 'folder' THEN 0 ELSE 1 END > :cursorNodeType) OR
            (CASE WHEN f.type = 'folder' THEN 0 ELSE 1 END = :cursorNodeType AND (
                ($sortColumn $comparisonOp :cursorSortValue) OR
                ($sortColumn = :cursorSortValue AND f.uuid $uuidComparisonOp :cursorUuid)
            ))
        )";

        // Set parameters for the query
        $params = [
            'cursorUuid' => $cursor['uuid'],
            'cursorNodeType' => $cursor['nodeType'],
            'cursorSortValue' => $cursor['sortValue'],
        ];

        return [
            'condition' => $condition,
            'params' => $params,
        ];
    }

    private function getSortColumn(string $sortBy): string
    {
        return match ($sortBy) {
            'fileType' => 'file_type',
            'updatedAt' => 'updated_at',
            'fileSize' => 'file_size',
            default => 'name',
        };
    }

    // Extract the value used for sorting from a node
    public function extractSortValue(FilesystemNode $node, string $sortBy): mixed
    {
        return match ($sortBy) {
            'fileType' => $node->getFileType(),
            'updatedAt' => $node->getUpdatedAt()->format('Y-m-d H:i:s'),
            'fileSize' => $node instanceof File ? ($node->getFileSize() ?? 0) : 0,
            default => $node->getName(),
        };
    }

    /**
     * Get all file types with their count.
     *
     * @return array Array of file types with counts
     *
     * @throws Exception
     */
    public function findAllFileTypesWithCount(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT
                file_type,
                COUNT(*) as count
            FROM
                filesystem_node
            GROUP BY
                file_type
            ORDER BY
                count DESC
        ';

        return $conn->executeQuery($sql)->fetchAllAssociative();
    }
}
