<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\DTO;

use JsonSerializable;

class ServiceTitanDashboardDTO implements JsonSerializable
{
    /**
     * @param ServiceTitanCredentialSummaryDTO[] $credentials
     * @param ServiceTitanAlertDTO[] $alerts
     */
    public function __construct(
        private readonly array $credentials,
        private readonly array $alerts,
        private readonly ServiceTitanSyncSummaryDTO $syncSummary,
        private readonly ServiceTitanMetricsDTO $metrics,
        private readonly \DateTimeInterface $lastUpdated,
    ) {
    }

    /**
     * @return ServiceTitanCredentialSummaryDTO[]
     */
    public function getCredentials(): array
    {
        return $this->credentials;
    }

    /**
     * @return ServiceTitanAlertDTO[]
     */
    public function getAlerts(): array
    {
        return $this->alerts;
    }

    public function getSyncSummary(): ServiceTitanSyncSummaryDTO
    {
        return $this->syncSummary;
    }

    public function getMetrics(): ServiceTitanMetricsDTO
    {
        return $this->metrics;
    }

    public function getLastUpdated(): \DateTimeInterface
    {
        return $this->lastUpdated;
    }

    public function jsonSerialize(): array
    {
        return [
            'credentials' => $this->credentials,
            'alerts' => $this->alerts,
            'syncSummary' => $this->syncSummary,
            'metrics' => $this->metrics,
            'lastUpdated' => $this->lastUpdated->format(\DateTimeInterface::ATOM),
        ];
    }
}
