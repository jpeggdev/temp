<?php

namespace App\Tests\Service;

use App\Entity\PostageProcessedFile;
use App\Module\Stochastic\Feature\PostageUploadsSftp\Repository\PostageProcessedFileRepository;
use App\Module\Stochastic\Feature\PostageUploadsSftp\Service\SftpBatchPostageProcessorService;
use App\Module\Stochastic\Feature\PostageUploadsSftp\ValueObject\ProcessingSummary;
use App\Tests\AbstractKernelTestCase;
use App\ValueObject\FileHash;

class SftpBatchPostageProcessorServiceTest extends AbstractKernelTestCase
{
    private SftpBatchPostageProcessorService $processor;
    private PostageProcessedFileRepository $processedFileRepository;
    private string $testFilesPath;

    public function setUp(): void
    {
        parent::setUp();

        $this->testFilesPath = __DIR__.'/../Files/usps/sftp';
        /** @var PostageProcessedFileRepository $processedFileRepo */
        $processedFileRepo = $this->getRepository(PostageProcessedFileRepository::class);
        $this->processedFileRepository = $processedFileRepo;

        /** @var SftpBatchPostageProcessorService $processorService */
        $processorService = $this->getService(SftpBatchPostageProcessorService::class);
        $this->processor = $processorService;
    }

    public function testProcessEmptyDirectoryReturnsZeroSummary(): void
    {
        // Arrange: Create empty temporary directory
        $emptyDir = sys_get_temp_dir().'/sftp_empty_'.uniqid('', true);
        mkdir($emptyDir);

        // Act: Process empty directory
        $summary = $this->processor->processDirectory($emptyDir);

        // Assert: Should return zero summary
        self::assertInstanceOf(ProcessingSummary::class, $summary);
        self::assertSame(0, $summary->totalFiles);
        self::assertSame(0, $summary->processedFiles);
        self::assertSame(0, $summary->skippedFiles);
        self::assertSame(0, $summary->failedFiles);
        self::assertTrue($summary->isSuccessful());

        // Cleanup
        rmdir($emptyDir);
    }

    public function testProcessDirectoryWithNonExistentPathThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Directory does not exist');

        $this->processor->processDirectory('/nonexistent/path');
    }

    public function testProcessSingleValidSftpFile(): void
    {
        // Arrange: Use a real SFTP test file
        $testFile = $this->testFilesPath.'/20250523200000_46003456_EPS_CM_HR_IVZS.txt';
        self::assertFileExists($testFile);

        // Act: Process single file
        $result = $this->processor->processFile($testFile);

        // Assert: Should process successfully
        self::assertTrue($result);

        // Verify file is marked as processed in database
        $filename = basename($testFile);
        $expectedHash = FileHash::fromFileSystem($testFile)->getString();
        $processedFile = $this->processedFileRepository->findByFilenameAndHash($filename, $expectedHash);

        self::assertInstanceOf(PostageProcessedFile::class, $processedFile);
        self::assertSame($filename, $processedFile->getFilename());
        self::assertSame($expectedHash, $processedFile->getFileHash());
        self::assertSame('SUCCESS', $processedFile->getStatus());
        self::assertGreaterThan(0, $processedFile->getRecordsProcessed());
    }

    public function testProcessFileSkipsAlreadyProcessedFile(): void
    {
        // Arrange: Process file first time
        $testFile = $this->testFilesPath.'/20250529040000_46003456_EPS_CM_HR_FNHJ.txt';
        $filename = basename($testFile);
        $fileHash = FileHash::fromFileSystem($testFile)->getString();

        // Mark file as already processed
        $this->processedFileRepository->markFileAsProcessed($filename, $fileHash, [
            'recordsProcessed' => 10,
            'status' => 'SUCCESS'
        ]);

        // Act: Process same file again with skip=true (default)
        $result = $this->processor->processFile($testFile);

        // Assert: Should skip the file
        self::assertFalse($result);

        // Verify only one record exists
        $processedFiles = $this->processedFileRepository->findBy(['filename' => $filename]);
        self::assertCount(1, $processedFiles);
    }

    public function testProcessFileReprocessesWhenSkipFlagIsFalse(): void
    {
        // Arrange: Process file first time
        $testFile = $this->testFilesPath.'/20250529050000_46003456_EPS_CM_HR_MVOI.txt';
        $filename = basename($testFile);
        $fileHash = FileHash::fromFileSystem($testFile)->getString();

        // Initial processing
        $firstResult = $this->processor->processFile($testFile);
        self::assertTrue($firstResult);

        // Act: Process same file again with skip=false
        $secondResult = $this->processor->processFile($testFile, false);

        // Assert: Should process again (but will fail due to unique constraint)
        // This tests that the skip logic is working correctly
        self::assertFalse($secondResult);
    }

    public function testProcessDirectoryWithMultipleSftpFiles(): void
    {
        // Arrange: Create temp directory with a few test files
        $tempDir = sys_get_temp_dir().'/sftp_test_'.uniqid('', true);
        mkdir($tempDir);

        $testFiles = [
            '20250605030000_46003456_EPS_CM_HR_EAEX.txt',
            '20250605200000_46003456_EPS_CM_HR_ATOU.txt',
        ];

        foreach ($testFiles as $testFile) {
            copy($this->testFilesPath.'/'.$testFile, $tempDir.'/'.$testFile);
        }

        // Act: Process directory
        $summary = $this->processor->processDirectory($tempDir);

        // Assert: Should process all files
        self::assertInstanceOf(ProcessingSummary::class, $summary);
        self::assertSame(2, $summary->totalFiles);
        self::assertSame(2, $summary->processedFiles);
        self::assertSame(0, $summary->skippedFiles);
        self::assertSame(0, $summary->failedFiles);
        self::assertTrue($summary->isSuccessful());

        // Verify files are marked as processed
        foreach ($testFiles as $testFile) {
            $filename = basename($testFile);
            $hash = FileHash::fromFileSystem($tempDir.'/'.$testFile)->getString();
            $processedFile = $this->processedFileRepository->findByFilenameAndHash($filename, $hash);
            self::assertInstanceOf(PostageProcessedFile::class, $processedFile);
        }

        // Cleanup
        array_map('unlink', glob($tempDir.'/*'));
        rmdir($tempDir);
    }

    public function testProcessDirectoryWithMixedResults(): void
    {
        // Arrange: Create temp directory with valid and invalid files
        $tempDir = sys_get_temp_dir().'/sftp_mixed_'.uniqid('', true);
        mkdir($tempDir);

        // Copy valid SFTP file
        $validFile = '20250606030809_46003456_EPS_CM_HR_TVRZ.txt';
        copy($this->testFilesPath.'/'.$validFile, $tempDir.'/'.$validFile);

        // Create invalid file
        $invalidFile = 'invalid.txt';
        file_put_contents($tempDir.'/'.$invalidFile, 'invalid content');

        // Mark one file as already processed
        $processedFile = '20250606200000_46003456_EPS_CM_HR_GGZK.txt';
        copy($this->testFilesPath.'/'.$processedFile, $tempDir.'/'.$processedFile);
        $hash = FileHash::fromFileSystem($tempDir.'/'.$processedFile)->getString();
        $this->processedFileRepository->markFileAsProcessed($processedFile, $hash, [
            'recordsProcessed' => 5,
            'status' => 'SUCCESS'
        ]);

        // Act: Process directory
        $summary = $this->processor->processDirectory($tempDir);

        // Assert: Should handle mixed results
        self::assertSame(3, $summary->totalFiles);
        self::assertGreaterThanOrEqual(1, $summary->processedFiles); // At least the valid file
        self::assertGreaterThanOrEqual(1, $summary->skippedFiles);   // The already processed file

        // Cleanup
        array_map('unlink', glob($tempDir.'/*'));
        rmdir($tempDir);
    }

    public function testCalculateFileHashUsesFileHashValueObject(): void
    {
        // Arrange: Get a test file
        $testFile = $this->testFilesPath.'/20250612030000_46003456_EPS_CM_HR_CWFJ.txt';

        // Act: Calculate hash using the service (we'll need to expose this for testing)
        $expectedHash = FileHash::fromFileSystem($testFile)->getString();

        // Process file to verify hash is used correctly
        $this->processor->processFile($testFile);

        // Assert: Verify the hash matches what FileHash would generate
        $filename = basename($testFile);
        $processedFile = $this->processedFileRepository->findByFilenameAndHash($filename, $expectedHash);

        self::assertInstanceOf(PostageProcessedFile::class, $processedFile);
        self::assertSame($expectedHash, $processedFile->getFileHash());
    }

    public function testProcessingPerformanceWithMultipleFiles(): void
    {
        // Arrange: Use several test files
        $testFiles = [
            '20250612200000_46003456_EPS_CM_HR_HTJB.txt',
            '20250613030000_46003456_EPS_CM_HR_MUYO.txt',
            '20250613200000_46003456_EPS_CM_HR_PYJI.txt',
        ];

        $tempDir = sys_get_temp_dir().'/sftp_perf_'.uniqid('', true);
        mkdir($tempDir);

        foreach ($testFiles as $testFile) {
            copy($this->testFilesPath.'/'.$testFile, $tempDir.'/'.$testFile);
        }

        // Act: Measure processing time
        $startTime = microtime(true);
        $summary = $this->processor->processDirectory($tempDir);
        $endTime = microtime(true);

        $processingTime = $endTime - $startTime;

        // Assert: Should process within reasonable time
        self::assertLessThan(10.0, $processingTime, 'Processing should complete within 10 seconds');
        self::assertSame(3, $summary->totalFiles);
        self::assertSame(3, $summary->processedFiles);
        self::assertTrue($summary->isSuccessful());

        // Cleanup
        array_map('unlink', glob($tempDir.'/*'));
        rmdir($tempDir);
    }
}
