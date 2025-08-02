<?php

namespace App\Module\CraftMigration\Repository\External;

use App\Module\CraftMigration\CraftMigrationConstants;
use App\Module\CraftMigration\DTO\Elements\EntryDTO;
use App\Module\CraftMigration\SQL\CraftMigrationQueries;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

readonly class CraftMigrationEntryRepository
{
    public function __construct(
        private Connection $craftMigrationConnection,
    ) {
    }

    /**
     * Fetches all entries from the Craft migration database.
     *
     * @return EntryDTO[] an array of EntryDTO objects
     *
     * @throws Exception
     */
    public function getResources(): array
    {
        $resourcesData = $this
            ->craftMigrationConnection
            ->fetchAllAssociative(
                CraftMigrationQueries::RESOURCES,
                ['excludeEntryType' => CraftMigrationConstants::ENTRY_TYPE_EXCLUDE]
            );

        return array_map(fn ($data) => EntryDTO::fromArray($data), $resourcesData);
    }

    /**
     * Fetches entries with pagination for memory-efficient streaming.
     *
     * @return EntryDTO[] an array of EntryDTO objects
     *
     * @throws Exception
     */
    public function getResourcesPaginated(int $offset, int $limit): array
    {
        $queryBuilder = $this->craftMigrationConnection->createQueryBuilder();

        $queryBuilder
            ->select('e.fieldLayoutId', 'et.name as type', 'es.slug', 'es.uri', 'en.postDate', 'e.enabled', 'c.*')
            ->from('elements', 'e')
            ->innerJoin('e', 'elements_sites', 'es', 'e.id = es.elementId')
            ->innerJoin('e', 'entries', 'en', 'en.id = e.id')
            ->innerJoin('en', 'entrytypes', 'et', 'en.typeId = et.id')
            ->innerJoin('e', 'content', 'c', 'e.id = c.elementId')
            ->where('e.canonicalId IS NULL')
            ->andWhere('e.dateDeleted IS NULL')
            ->andWhere('e.draftId IS NULL')
            ->andWhere('e.revisionId IS NULL')
            ->andWhere('es.uri LIKE :uriPattern')
            ->andWhere('et.id <> :excludeEntryType')
            ->orderBy('e.id')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->setParameter('uriPattern', '%resources/%')
            ->setParameter('excludeEntryType', CraftMigrationConstants::ENTRY_TYPE_EXCLUDE);

        $resourcesData = $queryBuilder->executeQuery()->fetchAllAssociative();

        return array_map(fn ($data) => EntryDTO::fromArray($data), $resourcesData);
    }

    /**
     * Gets the total count of resources for progress tracking.
     *
     * @throws Exception
     */
    public function getResourceCount(): int
    {
        $sql = 'SELECT COUNT(*) as count FROM ('.CraftMigrationQueries::RESOURCES.') as resources';

        $result = $this
            ->craftMigrationConnection
            ->fetchAssociative(
                $sql,
                ['excludeEntryType' => CraftMigrationConstants::ENTRY_TYPE_EXCLUDE]
            );

        return (int) $result['count'];
    }
}
