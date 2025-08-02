<?php

namespace App\Repository;

use App\Entity\FileSystemNodeTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FileSystemNodeTag>
 */
class FileSystemNodeTagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FileSystemNodeTag::class);
    }

    /**
     * Get all tags with their usage count.
     *
     * @return array Array of tags with counts
     *
     * @throws Exception
     */
    public function findAllTagsWithCount(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT
                t.id,
                t.name,
                t.color,
                COUNT(m.id) as count
            FROM
                file_system_node_tag t
            LEFT JOIN
                file_system_node_tag_mapping m ON t.id = m.file_system_node_tag_id
            GROUP BY
                t.id, t.name, t.color
            ORDER BY
                t.name ASC
        ';

        return $conn->executeQuery($sql)->fetchAllAssociative();
    }
}
