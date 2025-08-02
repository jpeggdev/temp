<?php

namespace App\Tests\Repository;

use App\Entity\PostageProcessedFile;
use App\Module\Stochastic\Feature\PostageUploadsSftp\Repository\PostageProcessedFileRepository;
use App\Tests\AbstractKernelTestCase;

class PostageProcessedFileRepositoryTest extends AbstractKernelTestCase
{
    private PostageProcessedFileRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        /** @var PostageProcessedFileRepository $repo */
        $repo = $this->getRepository(PostageProcessedFileRepository::class);
        $this->repository = $repo;
    }

    public function testFindByFilenameAndHashReturnsNullWhenNotFound(): void
    {
        $result = $this->repository->findByFilenameAndHash('nonexistent.txt', 'fake-hash');

        self::assertNull($result);
    }

    public function testFindByFilenameAndHashReturnsProcessedFileWhenFound(): void
    {
        // Arrange: Create and persist a PostageProcessedFile
        $filename = '20250523200000_46003456_EPS_CM_HR_IVZS.txt';
        $fileHash = 'd85b6b61f9a7a4b7b1f3c7e8c7d9e1a2';

        $processedFile = new PostageProcessedFile();
        $processedFile->setFilename($filename);
        $processedFile->setFileHash($fileHash);
        $processedFile->setRecordsProcessed(15);
        $processedFile->setStatus('SUCCESS');

        $this->getEntityManager()->persist($processedFile);
        $this->getEntityManager()->flush();

        // Act: Find by filename and hash
        $result = $this->repository->findByFilenameAndHash($filename, $fileHash);

        // Assert: Should return the processed file
        self::assertInstanceOf(PostageProcessedFile::class, $result);
        self::assertSame($filename, $result->getFilename());
        self::assertSame($fileHash, $result->getFileHash());
        self::assertSame(15, $result->getRecordsProcessed());
        self::assertSame('SUCCESS', $result->getStatus());
    }

    public function testIsFileProcessedReturnsFalseWhenNotProcessed(): void
    {
        $result = $this->repository->isFileProcessed('unprocessed.txt', 'hash123');

        self::assertFalse($result);
    }

    public function testIsFileProcessedReturnsTrueWhenAlreadyProcessed(): void
    {
        // Arrange: Create and persist a processed file
        $filename = 'processed.txt';
        $fileHash = 'abc123def456';

        $processedFile = new PostageProcessedFile();
        $processedFile->setFilename($filename);
        $processedFile->setFileHash($fileHash);
        $processedFile->setRecordsProcessed(10);
        $processedFile->setStatus('SUCCESS');

        $this->getEntityManager()->persist($processedFile);
        $this->getEntityManager()->flush();

        // Act: Check if file is processed
        $result = $this->repository->isFileProcessed($filename, $fileHash);

        // Assert: Should return true
        self::assertTrue($result);
    }

    public function testMarkFileAsProcessedCreatesNewRecord(): void
    {
        // Arrange
        $filename = 'new-file.txt';
        $fileHash = 'new-hash-123';
        $metadata = [
            'recordsProcessed' => 25,
            'status' => 'SUCCESS',
            'processingTimeSeconds' => 5.2
        ];

        // Act: Mark file as processed
        $processedFile = $this->repository->markFileAsProcessed($filename, $fileHash, $metadata);

        // Assert: Should create and return new PostageProcessedFile
        self::assertInstanceOf(PostageProcessedFile::class, $processedFile);
        self::assertSame($filename, $processedFile->getFilename());
        self::assertSame($fileHash, $processedFile->getFileHash());
        self::assertSame(25, $processedFile->getRecordsProcessed());
        self::assertSame('SUCCESS', $processedFile->getStatus());

        // Verify it's persisted to database
        $found = $this->repository->findByFilenameAndHash($filename, $fileHash);
        self::assertNotNull($found);
        self::assertSame($processedFile->getId(), $found->getId());
    }

    public function testMarkFileAsProcessedWithFailureStatus(): void
    {
        // Arrange
        $filename = 'failed-file.txt';
        $fileHash = 'failed-hash';
        $metadata = [
            'recordsProcessed' => 0,
            'status' => 'FAILED',
            'errorMessage' => 'Column mapping error'
        ];

        // Act: Mark file as failed
        $processedFile = $this->repository->markFileAsProcessed($filename, $fileHash, $metadata);

        // Assert: Should handle failure case
        self::assertSame('FAILED', $processedFile->getStatus());
        self::assertSame(0, $processedFile->getRecordsProcessed());
        self::assertSame('Column mapping error', $processedFile->getErrorMessage());
    }

    public function testGetProcessingStatisticsReturnsCorrectCounts(): void
    {
        // Arrange: Create mix of successful and failed processed files
        $successFile1 = new PostageProcessedFile();
        $successFile1->setFilename('success1.txt');
        $successFile1->setFileHash('hash1');
        $successFile1->setRecordsProcessed(10);
        $successFile1->setStatus('SUCCESS');

        $successFile2 = new PostageProcessedFile();
        $successFile2->setFilename('success2.txt');
        $successFile2->setFileHash('hash2');
        $successFile2->setRecordsProcessed(15);
        $successFile2->setStatus('SUCCESS');

        $failedFile = new PostageProcessedFile();
        $failedFile->setFilename('failed.txt');
        $failedFile->setFileHash('hash3');
        $failedFile->setRecordsProcessed(0);
        $failedFile->setStatus('FAILED');
        $failedFile->setErrorMessage('Parse error');

        $em = $this->getEntityManager();
        $em->persist($successFile1);
        $em->persist($successFile2);
        $em->persist($failedFile);
        $em->flush();

        // Act: Get processing statistics
        $stats = $this->repository->getProcessingStatistics();

        // Assert: Should return correct counts
        self::assertIsArray($stats);
        self::assertSame(3, $stats['total_files']);
        self::assertSame(2, $stats['success_count']);
        self::assertSame(1, $stats['failed_count']);
        self::assertSame(25, $stats['total_records_processed']); // 10 + 15 + 0
    }

    public function testUniqueConstraintPreventsDuplicateFilenameAndHash(): void
    {
        // Arrange: Create first processed file
        $filename = 'duplicate-test.txt';
        $fileHash = 'same-hash';

        $firstFile = new PostageProcessedFile();
        $firstFile->setFilename($filename);
        $firstFile->setFileHash($fileHash);
        $firstFile->setRecordsProcessed(5);
        $firstFile->setStatus('SUCCESS');

        $this->getEntityManager()->persist($firstFile);
        $this->getEntityManager()->flush();

        // Act & Assert: Attempt to create duplicate should fail
        $duplicateFile = new PostageProcessedFile();
        $duplicateFile->setFilename($filename);
        $duplicateFile->setFileHash($fileHash);
        $duplicateFile->setRecordsProcessed(10);
        $duplicateFile->setStatus('SUCCESS');

        $this->getEntityManager()->persist($duplicateFile);

        $this->expectException(\Exception::class); // Database constraint violation
        $this->getEntityManager()->flush();
    }
}
