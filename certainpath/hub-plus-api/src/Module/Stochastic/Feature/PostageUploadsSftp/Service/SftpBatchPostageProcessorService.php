<?php

namespace App\Module\Stochastic\Feature\PostageUploadsSftp\Service;

use App\Module\Stochastic\Feature\PostageUploads\Service\UploadPostageExpenseService;
use App\Module\Stochastic\Feature\PostageUploadsSftp\Repository\PostageProcessedFileRepository;
use App\Module\Stochastic\Feature\PostageUploadsSftp\ValueObject\ProcessingSummary;
use App\ValueObject\FileHash;
use Psr\Log\LoggerInterface;

readonly class SftpBatchPostageProcessorService
{
    public function __construct(
        private UploadPostageExpenseService $uploadService,
        private PostageProcessedFileRepository $processedFileRepository,
        private LoggerInterface $logger
    ) {
    }

    public function processDirectory(string $directoryPath): ProcessingSummary
    {
        if (!is_dir($directoryPath)) {
            throw new \InvalidArgumentException("Directory does not exist: $directoryPath");
        }

        $startTime = microtime(true);

        // Find all .txt files in directory
        $files = glob($directoryPath.'/*.txt');
        if ($files === false) {
            $files = [];
        }
        $totalFiles = count($files);

        if ($totalFiles === 0) {
            return new ProcessingSummary(0, 0, 0, 0, 0, [], 0.0);
        }

        $this->logger->info('Starting batch processing of SFTP directory', [
            'directory' => $directoryPath,
            'totalFiles' => $totalFiles
        ]);

        $processedFiles = 0;
        $skippedFiles = 0;
        $failedFiles = 0;
        $totalRecords = 0;
        $errors = [];

        foreach ($files as $filePath) {
            try {
                $result = $this->processFile($filePath, true);

                if ($result === true) {
                    $processedFiles++;
                    // Get record count from processed file entry
                    $filename = basename($filePath);
                    $hash = $this->calculateFileHash($filePath);
                    $processedFile = $this->processedFileRepository->findByFilenameAndHash($filename, $hash);
                    if ($processedFile) {
                        $totalRecords += $processedFile->getRecordsProcessed();
                    }
                } elseif ($result === false) {
                    $skippedFiles++;
                } else {
                    $failedFiles++;
                }
            } catch (\Exception $e) {
                $failedFiles++;
                $errors[] = sprintf('File %s: %s', basename($filePath), $e->getMessage());
                $this->logger->error('Failed to process SFTP file', [
                    'file' => $filePath,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $processingTime = microtime(true) - $startTime;

        $this->logger->info('Completed batch processing of SFTP directory', [
            'directory' => $directoryPath,
            'totalFiles' => $totalFiles,
            'processedFiles' => $processedFiles,
            'skippedFiles' => $skippedFiles,
            'failedFiles' => $failedFiles,
            'totalRecords' => $totalRecords,
            'processingTimeSeconds' => $processingTime
        ]);

        return new ProcessingSummary(
            $totalFiles,
            $processedFiles,
            $skippedFiles,
            $failedFiles,
            $totalRecords,
            $errors,
            $processingTime
        );
    }

    public function processFile(string $filePath, bool $skipIfProcessed = true): bool
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File does not exist: $filePath");
        }

        $filename = basename($filePath);
        $fileHash = $this->calculateFileHash($filePath);

        // Check if file already processed
        if ($skipIfProcessed && $this->processedFileRepository->isFileProcessed($filename, $fileHash)) {
            $this->logger->debug('Skipping already processed file', [
                'filename' => $filename,
                'hash' => $fileHash
            ]);
            return false;
        }

        $this->logger->info('Processing SFTP file', [
            'filename' => $filename,
            'hash' => $fileHash
        ]);

        try {
            // Use existing UploadPostageExpenseService to process the file
            $this->uploadService->handleWithDirectFilePath($filePath);

            // Count records processed (estimate from file size for now)
            $recordsProcessed = $this->estimateRecordsProcessed($filePath);

            // Mark file as successfully processed
            $this->processedFileRepository->markFileAsProcessed($filename, $fileHash, [
                'recordsProcessed' => $recordsProcessed,
                'status' => 'SUCCESS',
                'processingTimeSeconds' => microtime(true)
            ]);

            $this->logger->info('Successfully processed SFTP file', [
                'filename' => $filename,
                'recordsProcessed' => $recordsProcessed
            ]);

            return true;
        } catch (\Exception $e) {
            // Mark file as failed
            try {
                $this->processedFileRepository->markFileAsProcessed($filename, $fileHash, [
                    'recordsProcessed' => 0,
                    'status' => 'FAILED',
                    'errorMessage' => $e->getMessage()
                ]);
            } catch (\Exception $dbException) {
                // If we can't even mark as failed, it might be a duplicate constraint violation
                $this->logger->warning('Could not mark file as failed in database', [
                    'filename' => $filename,
                    'originalError' => $e->getMessage(),
                    'dbError' => $dbException->getMessage()
                ]);
                return false; // Treat as skipped
            }

            $this->logger->error('Failed to process SFTP file', [
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);

            throw $e; // Re-throw for higher-level error handling
        }
    }

    private function calculateFileHash(string $filePath): string
    {
        return FileHash::fromFileSystem($filePath)->getString();
    }

    private function estimateRecordsProcessed(string $filePath): int
    {
        // Simple estimation: count non-header lines
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (empty($lines)) {
            return 0;
        }

        // Subtract 1 for header row
        return max(0, count($lines) - 1);
    }
}
