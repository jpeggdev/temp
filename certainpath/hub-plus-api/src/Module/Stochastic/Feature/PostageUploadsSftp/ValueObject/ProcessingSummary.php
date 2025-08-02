<?php

namespace App\Module\Stochastic\Feature\PostageUploadsSftp\ValueObject;

readonly class ProcessingSummary
{
    public function __construct(
        public int $totalFiles,
        public int $processedFiles,
        public int $skippedFiles,
        public int $failedFiles,
        public int $totalRecords = 0,
        public array $errors = [],
        public float $processingTimeSeconds = 0.0
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->failedFiles === 0;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors) || $this->failedFiles > 0;
    }

    public function getSuccessRate(): float
    {
        if ($this->totalFiles === 0) {
            return 1.0;
        }

        return $this->processedFiles / $this->totalFiles;
    }
}
