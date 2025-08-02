<?php

namespace App\Tests\Integration;

use App\Entity\BatchPostage;
use App\Entity\PostageProcessedFile;
use App\Module\Stochastic\Feature\PostageUploads\Repository\BatchPostageRepository;
use App\Module\Stochastic\Feature\PostageUploadsSftp\Repository\PostageProcessedFileRepository;
use App\Module\Stochastic\Feature\PostageUploadsSftp\Service\SftpBatchPostageProcessorService;
use App\Tests\AbstractKernelTestCase;
use App\ValueObject\FileHash;

class SftpBatchProcessingIntegrationTest extends AbstractKernelTestCase
{
    private SftpBatchPostageProcessorService $processor;
    private PostageProcessedFileRepository $processedFileRepository;

    private string $testFilesPath;

    public function setUp(): void
    {
        parent::setUp();

        $this->testFilesPath = __DIR__.'/../Files/usps/sftp';
        $this->processor = $this->getService(SftpBatchPostageProcessorService::class);
        /** @var PostageProcessedFileRepository $processedFileRepo */
        $processedFileRepo = $this->getRepository(PostageProcessedFileRepository::class);
        $this->processedFileRepository = $processedFileRepo;
        /** @var BatchPostageRepository $batchPostageRepo */
        $batchPostageRepo = $this->getRepository(BatchPostageRepository::class);
        $this->batchPostageRepository = $batchPostageRepo;
    }

    /**
     * @throws \Exception
     */
    public function testEndToEndSftpFileProcessing(): void
    {
        // Arrange: Use a real SFTP test file
        $testFile = $this->testFilesPath.'/20250523200000_46003456_EPS_CM_HR_IVZS.txt';
        self::assertFileExists($testFile);

        // Act: Process the file
        $result = $this->processor->processFile($testFile);

        // Assert: File processing succeeded
        self::assertTrue($result);

        // Verify audit trail was created
        $filename = basename($testFile);
        $fileHash = FileHash::fromFileSystem($testFile)->getString();
        $processedFile = $this->processedFileRepository->findByFilenameAndHash($filename, $fileHash);

        self::assertInstanceOf(PostageProcessedFile::class, $processedFile);
        self::assertSame($filename, $processedFile->getFilename());
        self::assertSame($fileHash, $processedFile->getFileHash());
        self::assertSame('SUCCESS', $processedFile->getStatus());
        self::assertGreaterThan(0, $processedFile->getRecordsProcessed());

        // Verify actual postage records were created in BatchPostage
        $batchPostageRecords = $this->batchPostageRepository->findAll();
        self::assertNotEmpty($batchPostageRecords, 'BatchPostage records should be created');

        // Verify specific records match SFTP file data
        $testRecord = $this->batchPostageRepository->findOneByReference('00522180');
        self::assertInstanceOf(BatchPostage::class, $testRecord);
        self::assertSame('00522180', $testRecord->getReference()); // Job ID
        self::assertSame(2030, $testRecord->getQuantitySent()); // Number of Pieces
        self::assertSame('529.89', $testRecord->getCost()); // Transaction Amount
    }

    public function testSftpColumnMappingWorksCorrectly(): void
    {
        // Process the test file
        $testFile = $this->testFilesPath.'/20250529040000_46003456_EPS_CM_HR_FNHJ.txt';
        $result = $this->processor->processFile($testFile);

        self::assertTrue($result);

        // Verify that SFTP columns mapped correctly to BatchPostage fields
        $batchPostageRecords = $this->batchPostageRepository->findAll();
        self::assertNotEmpty($batchPostageRecords);

        // Each record should have proper data from SFTP columns
        foreach ($batchPostageRecords as $record) {
            self::assertNotEmpty($record->getReference(), 'Job ID should map to reference');
            self::assertGreaterThanOrEqual(0, $record->getQuantitySent(), 'Number of Pieces should map to quantitySent');
            self::assertNotEmpty($record->getCost(), 'Transaction Amount should map to cost');
            self::assertIsNumeric($record->getCost(), 'Cost should be numeric');
        }
    }

    public function testBatchProcessingPreventsDuplicates(): void
    {
        // Arrange: Process file first time
        $testFile = $this->testFilesPath.'/20250529050000_46003456_EPS_CM_HR_MVOI.txt';

        // Act: Process same file twice
        $firstResult = $this->processor->processFile($testFile);
        $secondResult = $this->processor->processFile($testFile);

        // Assert: First succeeds, second is skipped
        self::assertTrue($firstResult);
        self::assertFalse($secondResult); // Skipped due to duplicate detection

        // Verify only one processed file record
        $filename = basename($testFile);
        $fileHash = FileHash::fromFileSystem($testFile)->getString();
        $processedFiles = $this->processedFileRepository->findBy([
            'filename' => $filename,
            'fileHash' => $fileHash
        ]);

        self::assertCount(1, $processedFiles, 'Should have only one processed file record');
    }

    /**
     * @throws \Exception
     */
    public function testProcessingStatistics(): void
    {
        // Arrange: Process multiple files
        $testFiles = [
            '20250605030000_46003456_EPS_CM_HR_EAEX.txt',
            '20250605200000_46003456_EPS_CM_HR_ATOU.txt',
        ];

        // Act: Process files
        foreach ($testFiles as $testFile) {
            $filePath = $this->testFilesPath.'/'.$testFile;
            $this->processor->processFile($filePath);
        }

        // Act: Get statistics
        $stats = $this->processedFileRepository->getProcessingStatistics();

        // Assert: Statistics are correct
        self::assertIsArray($stats);
        self::assertSame(2, $stats['total_files']);
        self::assertSame(2, $stats['success_count']);
        self::assertSame(0, $stats['failed_count']);
        self::assertGreaterThan(0, $stats['total_records_processed']);
    }

    public function testDirectoryProcessingWithRealSftpFiles(): void
    {
        // Arrange: Create temp directory with multiple SFTP files
        $tempDir = sys_get_temp_dir().'/sftp_integration_'.uniqid('', true);
        mkdir($tempDir);

        $testFiles = [
            '20250606030809_46003456_EPS_CM_HR_TVRZ.txt',
            '20250606200000_46003456_EPS_CM_HR_GGZK.txt',
            '20250612030000_46003456_EPS_CM_HR_CWFJ.txt',
        ];

        foreach ($testFiles as $testFile) {
            copy($this->testFilesPath.'/'.$testFile, $tempDir.'/'.$testFile);
        }

        // Act: Process entire directory
        $summary = $this->processor->processDirectory($tempDir);

        // Assert: All files processed successfully
        self::assertTrue($summary->isSuccessful());
        self::assertSame(3, $summary->totalFiles);
        self::assertSame(3, $summary->processedFiles);
        self::assertSame(0, $summary->skippedFiles);
        self::assertSame(0, $summary->failedFiles);
        self::assertGreaterThan(0, $summary->totalRecords);

        // Verify all files are in processed file audit trail
        foreach ($testFiles as $testFile) {
            $filename = basename($testFile);
            $hash = FileHash::fromFileSystem($tempDir.'/'.$testFile)->getString();
            $processedFile = $this->processedFileRepository->findByFilenameAndHash($filename, $hash);
            self::assertInstanceOf(PostageProcessedFile::class, $processedFile);
            self::assertSame('SUCCESS', $processedFile->getStatus());
        }

        // Cleanup
        array_map('unlink', glob($tempDir.'/*'));
        rmdir($tempDir);
    }
}
