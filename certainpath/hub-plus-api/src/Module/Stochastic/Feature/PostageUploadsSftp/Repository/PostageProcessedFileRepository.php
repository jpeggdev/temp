<?php

namespace App\Module\Stochastic\Feature\PostageUploadsSftp\Repository;

use App\Entity\PostageProcessedFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PostageProcessedFile>
 */
class PostageProcessedFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostageProcessedFile::class);
    }

    public function findByFilenameAndHash(string $filename, string $hash): ?PostageProcessedFile
    {
        return $this->findOneBy([
            'filename' => $filename,
            'fileHash' => $hash,
        ]);
    }

    public function isFileProcessed(string $filename, string $hash): bool
    {
        return $this->findByFilenameAndHash($filename, $hash) !== null;
    }

    public function markFileAsProcessed(string $filename, string $hash, array $metadata): PostageProcessedFile
    {
        $processedFile = new PostageProcessedFile();
        $processedFile->setFilename($filename);
        $processedFile->setFileHash($hash);

        // Extract metadata into entity properties
        if (isset($metadata['recordsProcessed'])) {
            $processedFile->setRecordsProcessed((int) $metadata['recordsProcessed']);
        }

        if (isset($metadata['status'])) {
            $processedFile->setStatus($metadata['status']);
        }

        if (isset($metadata['errorMessage'])) {
            $processedFile->setErrorMessage($metadata['errorMessage']);
        }

        // Store remaining metadata in JSON field
        $remainingMetadata = array_diff_key($metadata, array_flip([
            'recordsProcessed', 'status', 'errorMessage'
        ]));

        if (!empty($remainingMetadata)) {
            $processedFile->setMetadata($remainingMetadata);
        }

        $this->getEntityManager()->persist($processedFile);
        $this->getEntityManager()->flush();

        return $processedFile;
    }

    public function getProcessingStatistics(): array
    {
        $qb = $this->createQueryBuilder('p');

        // Get total count
        $totalFiles = $qb->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // Get success count
        $successCount = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.status = :status')
            ->setParameter('status', 'SUCCESS')
            ->getQuery()
            ->getSingleScalarResult();

        // Get failed count
        $failedCount = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.status = :status')
            ->setParameter('status', 'FAILED')
            ->getQuery()
            ->getSingleScalarResult();

        // Get total records processed
        $totalRecords = $this->createQueryBuilder('p')
            ->select('SUM(p.recordsProcessed)')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total_files' => (int) $totalFiles,
            'success_count' => (int) $successCount,
            'failed_count' => (int) $failedCount,
            'total_records_processed' => (int) ($totalRecords ?? 0),
        ];
    }
}
