<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\DTO;

use JsonSerializable;

class ServiceTitanSyncSummaryDTO implements JsonSerializable
{
    public function __construct(
        private readonly int $totalSyncs,
        private readonly int $successfulSyncs,
        private readonly int $failedSyncs,
        private readonly int $runningSyncs,
        private readonly ?\DateTimeInterface $lastSuccessfulSync,
        private readonly ?\DateTimeInterface $lastFailedSync,
        private readonly float $averageSuccessRate,
        private readonly array $recentSyncHistory,
    ) {
    }

    public function getTotalSyncs(): int
    {
        return $this->totalSyncs;
    }

    public function getSuccessfulSyncs(): int
    {
        return $this->successfulSyncs;
    }

    public function getFailedSyncs(): int
    {
        return $this->failedSyncs;
    }

    public function getRunningSyncs(): int
    {
        return $this->runningSyncs;
    }

    public function getLastSuccessfulSync(): ?\DateTimeInterface
    {
        return $this->lastSuccessfulSync;
    }

    public function getLastFailedSync(): ?\DateTimeInterface
    {
        return $this->lastFailedSync;
    }

    public function getAverageSuccessRate(): float
    {
        return $this->averageSuccessRate;
    }

    public function getRecentSyncHistory(): array
    {
        return $this->recentSyncHistory;
    }

    public function jsonSerialize(): array
    {
        return [
            'totalSyncs' => $this->totalSyncs,
            'successfulSyncs' => $this->successfulSyncs,
            'failedSyncs' => $this->failedSyncs,
            'runningSyncs' => $this->runningSyncs,
            'lastSuccessfulSync' => $this->lastSuccessfulSync?->format(\DateTimeInterface::ATOM),
            'lastFailedSync' => $this->lastFailedSync?->format(\DateTimeInterface::ATOM),
            'averageSuccessRate' => $this->averageSuccessRate,
            'recentSyncHistory' => $this->recentSyncHistory,
        ];
    }
}
