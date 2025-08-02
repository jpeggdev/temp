<?php

namespace App\Module\CraftMigration\Service;

use Psr\Log\LoggerInterface;

readonly class CraftMigrationProgressService
{
    private const string PROGRESS_FILE = 'craft_migration_progress.json';

    public function __construct(
        private LoggerInterface $logger,
        private string $progressDir = '/tmp',
    ) {
    }

    /**
     * Save migration progress to persistent storage.
     */
    public function saveProgress(array $progressData): void
    {
        $progressFile = $this->getProgressFile();

        $progressData['timestamp'] = time();
        $progressData['datetime'] = date('Y-m-d H:i:s');

        $json = json_encode($progressData, JSON_PRETTY_PRINT);

        if (false === file_put_contents($progressFile, $json)) {
            $this->logger->error(sprintf('Failed to save progress to %s', $progressFile));

            return;
        }

        $this->logger->info(sprintf(
            'Progress saved: %d/%d entries processed',
            $progressData['processed'],
            $progressData['total']
        ));
    }

    /**
     * Load migration progress from persistent storage.
     */
    public function loadProgress(): ?array
    {
        $progressFile = $this->getProgressFile();

        if (!file_exists($progressFile)) {
            return null;
        }

        $content = file_get_contents($progressFile);
        if (false === $content) {
            $this->logger->error(sprintf('Failed to read progress from %s', $progressFile));

            return null;
        }

        $progress = json_decode($content, true);
        if (null === $progress) {
            $this->logger->error('Invalid progress file format');

            return null;
        }

        $this->logger->info(sprintf(
            'Progress loaded: %d/%d entries processed (saved at %s)',
            $progress['processed'] ?? 0,
            $progress['total'] ?? 0,
            $progress['datetime'] ?? 'unknown'
        ));

        return $progress;
    }

    /**
     * Clear saved progress (typically called on successful completion).
     */
    public function clearProgress(): void
    {
        $progressFile = $this->getProgressFile();

        if (file_exists($progressFile)) {
            if (unlink($progressFile)) {
                $this->logger->info('Progress file cleared successfully');
            } else {
                $this->logger->error('Failed to clear progress file');
            }
        }
    }

    /**
     * Check if migration can be resumed.
     */
    public function canResume(): bool
    {
        $progress = $this->loadProgress();

        if (null === $progress) {
            return false;
        }

        // Consider resumable if less than 24 hours old and not completed
        $maxAge = 24 * 60 * 60; // 24 hours
        $age = time() - ($progress['timestamp'] ?? 0);

        $isRecent = $age < $maxAge;
        $isIncomplete = ($progress['processed'] ?? 0) < ($progress['total'] ?? 1);

        return $isRecent && $isIncomplete;
    }

    /**
     * Get the next batch offset for resuming.
     */
    public function getResumeOffset(): int
    {
        $progress = $this->loadProgress();

        if (null === $progress) {
            return 0;
        }

        return $progress['processed'] ?? 0;
    }

    /**
     * Get progress file path.
     */
    private function getProgressFile(): string
    {
        $this->logger->info(sprintf('Using progress file: %s', rtrim($this->progressDir, '/').'/'.self::PROGRESS_FILE));

        return rtrim($this->progressDir, '/').'/'.self::PROGRESS_FILE;
    }
}
